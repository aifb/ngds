package org.geovocab.gadm;

import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStream;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.Properties;
import java.util.concurrent.CountDownLatch;
import java.util.logging.FileHandler;
import java.util.logging.Level;
import java.util.logging.LogManager;
import java.util.logging.Logger;
import java.util.logging.SimpleFormatter;

public class Main {
	
	private static Properties configFile;
	private static Logger logger;


	
	public static void setupLogs(){
		DateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd_HH:mm:ss");
		Date date = new Date();

		logger = Logger.getLogger("Main");
		FileHandler fh = null;
		try {
			fh = new FileHandler("log/" + dateFormat.format(date) + ".log", true);
		} catch (SecurityException e1) {
			logger.log(Level.SEVERE, "The log file must have writing permission.", e1);
			System.exit(1);
		} catch (IOException e1) {
			logger.log(Level.SEVERE, "The log file could not be loaded.", e1);
			System.exit(2);
		}

		logger.addHandler(fh);
		logger.setLevel(Level.ALL);
		SimpleFormatter formatter = new SimpleFormatter();
		fh.setFormatter(formatter);

		LogManager.getLogManager().addLogger(logger);
	}
	
	public static void readConfiguration(String filename){
		configFile = new Properties();
		InputStream is = null;
		try {
			is = new FileInputStream(filename);
		} catch (FileNotFoundException e) {
			logger.log(Level.SEVERE, "The configuration file 'config/config.properties' does not exist.", e);
			System.exit(3);
		}
		
		try{
			configFile.load(is);	
		} catch (IOException e) {
			logger.log(Level.SEVERE, "The configuration file could not be loaded.", e);
			System.exit(4);
		} finally {
			try {
				is.close();
			} catch (IOException e) {
				logger.log(Level.WARNING, "The configuration file could not be closed.", e);
			}	
		}
		
	}
	
	
	/**
	 * @param args
	 */
	public static void main(String[] args) {
		
		if (args.length != 1){
			
			System.err.println("Wrong arguments. Correct use is: java -jar gadm_mapper <config_file>. \n\n");
			System.exit(1);
		}
		
		String filename = args[0];
		
		// Setup logs
		setupLogs();		   

		// Read configuration
		logger.log(Level.INFO, "Reading configuration.");
		readConfiguration(filename);
		
		// Start the reader thread
		Reader reader = new Reader(configFile);
		reader.start();
		
		int updater_threads = Integer.parseInt(configFile.getProperty("UPDATER_THREADS"));
		
		// Create an object which will count the number of active threads
		CountDownLatch updater_latch = new CountDownLatch(updater_threads);
		ArrayList<Updater> updater_list = new ArrayList<Updater>();
		
		// Start the updater threads
		for (int i = 0; i < updater_threads; i++){
			Updater updater = new Updater(reader, configFile, updater_latch);
			updater.start();
			updater_list.add(updater);
		}
			
		
		
		

	}

}
