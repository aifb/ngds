package org.geovocab.gadm;

import java.io.BufferedReader;
import java.io.DataInputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.sql.SQLException;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.Properties;
import java.util.StringTokenizer;
import java.util.concurrent.CountDownLatch;
import java.util.logging.FileHandler;
import java.util.logging.Level;
import java.util.logging.LogManager;
import java.util.logging.Logger;
import java.util.logging.SimpleFormatter;

public class Main {
	
	private static String temp_folder;
	private static String base_url;
	private static String data_dir;
	private static String country_list;
	private static String prepare_script;
	private static String finalize_script;
	private static int downloader_threads;
	private static int updater_threads;
	private static Properties configFile;
	private static Properties lastModifiedFile = new Properties();
	private static CountryQueue unprocessed = new CountryQueue();
	private static CountryQueue downloaded = new CountryQueue();
	
	
	
	private static void setupLogs() throws IOException {
		
		DateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd_HH:mm:ss");
		Date date = new Date();
		
		Logger logger = Logger.getLogger("Main");
	    FileHandler fh = new FileHandler("log/" + dateFormat.format(date) + ".log", true);
	    
	    logger.addHandler(fh);
	    logger.setLevel(Level.ALL);
	    SimpleFormatter formatter = new SimpleFormatter();
	    fh.setFormatter(formatter);
		
		LogManager.getLogManager().addLogger(logger);
	   
	}

	
	/**
	 * @param args
	 */
	public static void main(String[] args) {
		
		// Set up the log files
		try {
			setupLogs();
		} catch (IOException e1) {}
		
		Logger logger = LogManager.getLogManager().getLogger("Main");
		
		// Read configuration
		try {
			configFile = new Properties();
			InputStream is = new FileInputStream("config/config.properties");
			
			try{
				configFile.load(is);	
			} finally {
				is.close();	
			}
			
			temp_folder =  configFile.getProperty("TEMP_FOLDER");
			base_url = configFile.getProperty("BASE_URL");
			data_dir = configFile.getProperty("DATA_DIR");
			country_list = data_dir + configFile.getProperty("COUNTRY_LIST");
			prepare_script = data_dir + configFile.getProperty("PREPARE_SCRIPT");
			finalize_script = data_dir + configFile.getProperty("FINALIZE_SCRIPT");
			downloader_threads = Integer.parseInt(configFile.getProperty("DOWNLOADER_THREADS"));
			updater_threads = Integer.parseInt(configFile.getProperty("UPDATER_THREADS"));
			
		} catch (IOException e1) {
			logger.log(Level.SEVERE, "Error reading configuration file.", e1);
			System.exit(2);
		}
		
		try {
			InputStream is = new FileInputStream("config/last_modified.properties");
			try {
				lastModifiedFile.load(is);
			} finally{
				is.close();
			}
		} catch (IOException e1) {
			logger.log(Level.WARNING, "Error reading last update dates.", e1);
		}
		
		// Prepare database
		logger.log(Level.INFO, "Loading required stored procedures.");
		File prepare_sql = new File(prepare_script);
		PostGIS postgis = new PostGIS(configFile);
		try {
			postgis.initPostGIS();
			postgis.executeFile(prepare_sql, false);
		} catch (ClassNotFoundException e) {
			logger.log(Level.SEVERE, "Class not found.", e);
			System.exit(3);
		} catch (SQLException e) {
			logger.log(Level.SEVERE, "Could not connect to PostgreSQL.", e);
			System.exit(4);
		} finally {
			try {
				postgis.stopPostGIS();
			} catch (SQLException e) {
				logger.log(Level.WARNING, "Unable to close connection to PostGIS.");
			}		
		}
			
		// Read the country list
		FileInputStream fstream = null;
		try {
			fstream = new FileInputStream(country_list);
		} catch (FileNotFoundException e) {
			logger.log(Level.SEVERE, "File '" + country_list + "' not found.", e);
			System.exit(4);
		}

		DataInputStream in = new DataInputStream(fstream);
		BufferedReader br = new BufferedReader(new InputStreamReader(in));
		String strLine;
		try {
			while ((strLine = br.readLine()) != null)   {

				StringTokenizer st = new StringTokenizer(strLine, "|");
				String code = st.nextToken();
				String country_name = st.nextToken();

				Country country = new Country();
				country.setCode(code);
				country.setName(country_name);
				country.setZipFile(code + "_adm.zip");
				country.setOutputDir(temp_folder + code + "/");
				country.setUrl(base_url + code + "_adm.zip");

				unprocessed.addCountry(country);

			}
		} catch (IOException e) {
			logger.log(Level.SEVERE, "Error reading '" + country_list + "'.", e);
			System.exit(4);
		} finally {
			try {
				br.close();
				in.close();
				fstream.close();
			} catch (IOException e) {
				logger.log(Level.WARNING, "Unable to close stream.", e);
			}
		}


		// Create an object which will count the number of active threads
		CountDownLatch downloader_latch = new CountDownLatch(downloader_threads);
		ArrayList<Downloader> downloader_list = new ArrayList<Downloader>();

		// Start the threads for downloading
		for (int i = 0; i < downloader_threads; i++){
			Downloader downloader = new Downloader(unprocessed, downloaded, lastModifiedFile, downloader_latch);
			downloader.start();
			downloader_list.add(downloader);
		}

		// Create an object which will count the number of active threads
		CountDownLatch updater_latch = new CountDownLatch(updater_threads);
		ArrayList<Updater> updater_list = new ArrayList<Updater>();

		// Start the updater threads. SQL Merge scripts must be transactional.
		for (int i = 0; i < updater_threads; i++){
			Updater updater = new Updater(downloaded, configFile, lastModifiedFile, updater_latch);
			updater.start();
			updater_list.add(updater);
		}
		
		logger.log(Level.INFO, "Waiting for all data to be downloaded.");

		// Wait until all data is downloaded
		try {
			downloader_latch.await();
		} catch (InterruptedException e) {
			logger.log(Level.WARNING, "Interrupted wait.", e);
		}
		
		logger.log(Level.INFO, "Waiting for the queue of downloaded objects to be emptied by the updater threads.");

		// Wait until the downloaded queue is emptied by the updater threads
		while (!downloaded.isEmpty()){
			try {
				synchronized (downloaded) {
					downloaded.wait();	
				}
			} catch (InterruptedException e) {
				logger.log(Level.WARNING, "Interrupted wait.", e);
			}
		}
		
		logger.log(Level.INFO, "Signal the updater threads to stop processing.");

		// Then signal the updaters to stop		
		java.util.Iterator<Updater> itr = updater_list.iterator();
		while (itr.hasNext()){
			Updater updater = itr.next();
			updater.interrupt();
		}
		
		logger.log(Level.INFO, "Waiting for the updater threads to finish.");

		// And wait for the updater threads to finish
		try {
			updater_latch.await();
		} catch (InterruptedException e) {
			logger.log(Level.WARNING, "Interrupted wait.", e);
		}

		// Execute final SQL script
		logger.log(Level.INFO, "Dropping stored procedures and restoring indexes.");
		File finalize_sql = new File(finalize_script);
		try {
			postgis.initPostGIS();
			postgis.executeFile(finalize_sql, true);
		} catch (ClassNotFoundException e) {
			logger.log(Level.SEVERE, "Class not found.", e);
			System.exit(3);
		} catch (SQLException e) {
			logger.log(Level.SEVERE, "Could not connect to PostgreSQL.", e);
			System.exit(4);
		} finally {
			try {
				postgis.stopPostGIS();
			} catch (SQLException e) {
				logger.log(Level.WARNING, "Unable to close connection to PostGIS.");
			}		
		}
		
		logger.log(Level.INFO, "Update finished.");

	}

}
