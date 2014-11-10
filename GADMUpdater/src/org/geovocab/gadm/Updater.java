package org.geovocab.gadm;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileOutputStream;
import java.io.FileWriter;
import java.io.IOException;
import java.io.InputStreamReader;
import java.sql.SQLException;
import java.util.Date;
import java.util.Properties;
import java.util.concurrent.CountDownLatch;
import java.util.logging.Level;
import java.util.logging.LogManager;
import java.util.logging.Logger;

public class Updater extends Thread {
	
	private CountryQueue downloaded;
	private CountDownLatch latch;
	private Properties config_file;
	private Properties last_modified;
	private boolean exit = false;
	private Logger logger;
	
	
	public Updater(CountryQueue dloaded, Properties cFile, Properties lModified, CountDownLatch l){
		downloaded = dloaded;
		config_file = cFile;
		last_modified = lModified;
		latch = l;		
		
		logger = LogManager.getLogManager().getLogger("Main");
	}
	
	public void exit(){
		exit = true;
	}
	
	public void run(){
		
		logger.log(Level.INFO, Thread.currentThread().getName() + ": Starting updater thread");
		String thread_name = Thread.currentThread().getName().replace('-', '_').toLowerCase();
		String table_basename = thread_name + "_temp_level_"; 
		
		// Connect to PostGIS
		PostGIS postgis = new PostGIS(config_file);
		try {
			postgis.initPostGIS();
			
			boolean success = true;
			// Create the temporal tables for this thread
			logger.log(Level.INFO, Thread.currentThread().getName() + ": Creating temporal tables.");
			for (int i = 0; i <= 4; i++){
				success = success && postgis.callProcedure("create_level_" + i, table_basename + i);	
			}
			
			if (!success){
				logger.log(Level.SEVERE, Thread.currentThread().getName() + ": Failed to create temporal tables.");
				exit = true;
			}
			
			while (!exit){

				while (downloaded.isEmpty() && !exit){
					try {
						synchronized(downloaded){
							downloaded.wait();
						}
					} catch (InterruptedException e) {
						logger.log(Level.INFO, Thread.currentThread().getName() + ": Shutting down updater thread");
						exit = true;
					}
				}

				Country country = downloaded.getCountry();

				if (country != null){
					downloaded.setProcessing(true);

					logger.log(Level.INFO, Thread.currentThread().getName() + ": Updating country " + country.getName());

					for (int level = 0; level <= 4; level++){

						String shapefile = country.getOutputDir() + country.getCode() + "_adm" + level + ".shp";
						File shp = new File(shapefile);

						if (success && shp.exists()){

							String table_name = table_basename + level;
							String sql_file = country.getOutputDir() + country.getCode() + "_adm" + level + ".sql";
							File sql = new File(sql_file);

							logger.log(Level.INFO, Thread.currentThread().getName() + ": Generating SQL script for " + shapefile);
							try {
								generatePgSQL(shp, sql, table_name);
							} catch (IOException e) {
								logger.log(Level.WARNING, Thread.currentThread().getName() + ":Could not generate SQL script for " + shapefile + ".", e);
							}
							
							logger.log(Level.INFO, Thread.currentThread().getName() + ": Executing " + sql_file);
							
							// Execute the SQL file
							success = success && postgis.executeFile(sql, false);

							if (success){
								// Execute the merge stored procedure
								logger.log(Level.INFO, Thread.currentThread().getName() + ": Merging data from " + shapefile + ".");
								success = success && postgis.callProcedure("merge_" + level, table_name);	
							}
						}

					}
					
					if (success){
						logger.log(Level.INFO, Thread.currentThread().getName() + ": Update OK. Saving update timestamp for " + country.getZipFile());
						Date now = new Date();
						last_modified.setProperty(country.getZipFile(), Long.toString(now.getTime()));
						FileOutputStream out = null;
						try {
							out = new FileOutputStream("config/last_modified.properties");
							last_modified.store(out, "");
						} catch (IOException e) {
							logger.log(Level.WARNING, Thread.currentThread().getName() + ":Update date could not be stored for '" + country.getZipFile() + "'.");	
						} finally {
							try {
								out.close();
							} catch (IOException e) {
								logger.log(Level.WARNING, Thread.currentThread().getName() + ":Unable to close stream.", e);
							}
						}	
					}

					File output_dir = new File(country.getOutputDir());

					logger.log(Level.INFO, Thread.currentThread().getName() + ": Deleting directory " + output_dir.getName());
					deleteDir(output_dir);

					downloaded.setProcessing(false);
				}
			}
			
			// Dropping the temporal tables for this thread
			logger.log(Level.INFO, Thread.currentThread().getName() + ": Dropping temporal tables for " + Thread.currentThread().getName());
			for (int i = 0; i <= 4; i++){
				postgis.callProcedure("drop_level", table_basename + i);	
			}
			
		} catch (ClassNotFoundException e) {
			logger.log(Level.SEVERE, Thread.currentThread().getName() + ":Class not found.", e);
			System.exit(3);
		} catch (SQLException e) {
			logger.log(Level.SEVERE, Thread.currentThread().getName() + ":An error has occurred while connecting to PostgreSQL.", e);
			System.exit(4);
		} finally {
			try {
				postgis.stopPostGIS();
			} catch (SQLException e) {
				logger.log(Level.WARNING, Thread.currentThread().getName() + ":Unable to close connection to PostGIS.");
			}
		}	
		
		logger.log(Level.INFO, Thread.currentThread().getName() + ": Updater thread shut down");
		latch.countDown();
		
	}
	
	public boolean deleteDir(File dir) {
        if (dir.isDirectory()) {
            String[] children = dir.list();
            for (int i=0; i<children.length; i++) {
                boolean success = deleteDir(new File(dir, children[i]));
                if (!success) {
                    return false;
                }
            }
        }
    
        return dir.delete();
    }
	
	public void generatePgSQL(File shp, File sql, String table_name) throws IOException {
		String cmd = "shp2pgsql -a -s 4326 -W LATIN1 -g geometry " + shp.getAbsolutePath() +" " + table_name;

		Runtime run = Runtime.getRuntime();
		Process pr = run.exec(cmd);
		
		BufferedReader buf = new BufferedReader(new InputStreamReader(pr.getInputStream()));
		String line = "";
		
		BufferedWriter out = new BufferedWriter(new FileWriter(sql.getAbsolutePath()));
		
		boolean interrupted = false;
		
		try {
			while ((line = buf.readLine()) != null) {
				out.write(line);
			}
			
			try {
				pr.waitFor();
			} catch (InterruptedException e) {
				interrupted = true;
			}
			
				
		} finally {
			out.close();
			buf.close();
			
			if (interrupted) 
				Thread.currentThread().interrupt();
		}
		
	}
}
