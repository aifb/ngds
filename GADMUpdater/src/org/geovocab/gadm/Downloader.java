package org.geovocab.gadm;

import java.io.BufferedInputStream;
import java.io.BufferedOutputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.InputStream;
import java.net.URL;
import java.net.URLConnection;
import java.util.Date;
import java.util.Properties;
import java.util.StringTokenizer;
import java.util.concurrent.CountDownLatch;
import java.util.logging.Level;
import java.util.logging.LogManager;
import java.util.logging.Logger;
import java.util.zip.ZipEntry;
import java.util.zip.ZipInputStream;

public class Downloader extends Thread {
	
	private CountryQueue countryQueue;
	private CountryQueue downloaded;
	private CountDownLatch latch;
	private Properties last_modified;
	private Logger logger;
	
	public Downloader(CountryQueue cQueue, CountryQueue dloaded, Properties lModified, CountDownLatch l){
		countryQueue = cQueue;
		downloaded = dloaded;
		last_modified = lModified;
		latch = l;
		logger = LogManager.getLogManager().getLogger("Main");
	}
	
	public void run(){
		
		logger.log(Level.INFO, Thread.currentThread().getName() + ": Starting downloader thread");
				
		while (!countryQueue.isEmpty()){

			Country country = countryQueue.getCountry();
			countryQueue.setProcessing(true);
			
			URL url = null;
			Date date = null;
			try {
				url = new URL(country.getUrl());
				URLConnection urlC = url.openConnection();
				 date=new Date(urlC.getLastModified());
			} catch (Exception e) {
				logger.log(Level.SEVERE, Thread.currentThread().getName() + ": " + e.getMessage(), e);
			}

			// Only download the country files if they were modified after the last update, otherwise skip it.
			Date last_modified_date = new Date();
			last_modified_date.setTime(Long.parseLong(last_modified.getProperty(country.getZipFile(), "0")));
			if (date.compareTo(last_modified_date) > 0){
				
				File output_dir = new File(country.getOutputDir());
		        output_dir.mkdir();
				
		        logger.log(Level.INFO, Thread.currentThread().getName() + ": Downloading file " + country.getZipFile() + " for " + country.getName() + "...");
				this.download(url, output_dir);
				logger.log(Level.INFO, Thread.currentThread().getName() + ": File downloaded for " + country.getName());
		        
				String zip_file_name = output_dir.getAbsolutePath() + "/" + country.getZipFile();
		        File zip_file = new File(zip_file_name);
		        
		        logger.log(Level.INFO, Thread.currentThread().getName() + ": Unzipping file " + country.getZipFile());
				this.unzip(zip_file, output_dir);
				logger.log(Level.INFO, Thread.currentThread().getName() + ": File " + country.getZipFile() + " unzipped");
				
				downloaded.addCountry(country);
				
			}else{
				logger.log(Level.INFO, Thread.currentThread().getName() + ": Country " + country.getName() + " is already up to date");	
			}
			
			countryQueue.setProcessing(false);
			
		}
		
		logger.log(Level.INFO, Thread.currentThread().getName() + ": Downloader thread shut down");
		latch.countDown();
		
	}
		
	public void download (URL url, File output_dir){
	      try
	      {	          
	          InputStream is = url.openStream();
	          FileOutputStream fos=null;
	          
	          String local_file=null;

	          StringTokenizer st=new StringTokenizer(url.getFile(), "/");
	          while (st.hasMoreTokens())
	        	  local_file=st.nextToken();
	          fos = new FileOutputStream(output_dir.getAbsolutePath() + "/" + local_file);
	          
	          int oneChar, count=0;
	          while ((oneChar=is.read()) != -1)
	          {
	             fos.write(oneChar);
	             count++;
	          }
	          
	          is.close();
	          fos.close();
	      }
	      catch (Exception e)
	      {
	    	  logger.log(Level.SEVERE, e.getMessage(), e);
	    	  e.printStackTrace();
	      }
	}
	
	public void unzip(File inFile, File outFolder) {
		int buffer = 1024;
		try {
			ZipInputStream in = new ZipInputStream(new BufferedInputStream(
					new FileInputStream(inFile)));
			ZipEntry entry;

			while ((entry = in.getNextEntry()) != null) {
				
				int count;
				byte data[] = new byte[buffer];

				BufferedOutputStream out = new BufferedOutputStream(
						new FileOutputStream(outFolder.getPath() + "/"
								+ entry.getName()), buffer);

				while ((count = in.read(data, 0, buffer)) != -1) {
					out.write(data, 0, count);
				}

				out.flush();
				out.close();
			}
			in.close();
		} catch (Exception e) {
			logger.log(Level.SEVERE, e.getMessage(), e);
			e.printStackTrace();
		}
	}
}
