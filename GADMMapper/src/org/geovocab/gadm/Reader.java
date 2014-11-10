package org.geovocab.gadm;

import java.io.BufferedReader;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;
import java.util.Properties;
import java.util.logging.Level;
import java.util.logging.LogManager;
import java.util.logging.Logger;

import com.hp.hpl.jena.datatypes.xsd.XSDDatatype;
import com.hp.hpl.jena.rdf.model.Model;
import com.hp.hpl.jena.rdf.model.ModelFactory;
import com.hp.hpl.jena.rdf.model.Property;
import com.hp.hpl.jena.rdf.model.Resource;
import com.hp.hpl.jena.rdf.model.Statement;
import com.hp.hpl.jena.util.FileManager;

public class Reader extends Thread {
	
	private Properties configFile;
	private Logger logger;
	private BufferedReader br;
	private boolean exit = false;
	private String filename;
	private String previousLine;
	
	public Reader(Properties cFile){
		
		configFile = cFile;
		logger = LogManager.getLogManager().getLogger("Main");
		previousLine = null;
		
	}
	
	public void run(){
		logger.log(Level.INFO, "Starting reader thread.");
		
		filename = configFile.getProperty("URI_LIST");
		
		try{

			// Read the URI list
			br = new BufferedReader(new FileReader(filename));
			
			// Continue receiving calls until the thread is indicated to exit. 
			// This is triggered when the thread is interrupted, a failure occurs or the end of file has been reached.
			while (!exit){
			
				synchronized (this){
					try {
						this.wait();	
					} catch (InterruptedException e) {
						logger.log(Level.INFO, "Shutting down reader thread.");
						exit = true;
					}
				}
				
			}
	
		} catch (FileNotFoundException e1) {
			logger.log(Level.WARNING, "File '" + filename + "' not found.", e1);
		} finally {
			try {
				br.close();
			} catch (IOException e) {
				logger.log(Level.WARNING, "Failed to close stream.", e);
			}
		}
		
		logger.log(Level.INFO, "Finishing reader thread.");
		
	}
	
	public synchronized String getLine(){
		
		// The thread has already decided to exit, the reader will be closed.
		if (exit) return null;
		
		// Otherwise, try to get a new line
		String currentLine = null;
		
		try {
			currentLine = br.readLine();
		} catch (IOException e) {
			logger.log(Level.SEVERE, "Failed to read file '" + filename + "'.", e);
			exit = true;
		}
		
		if (currentLine == null){
			exit = true;
		}
		
		this.notify();
		
		return currentLine;
	}
	
	public synchronized Model getModel(){
		
		// The thread has already decided to exit, the reader will be closed.
		if (exit) return null;
		
		// Otherwise, try to get a new line
		String currentLine = null;
		Model individualModel = ModelFactory.createDefaultModel();
		
		try {
			
			String previousUri;
			
			// Add the last triple read from the previous call
			if (previousLine == null){
				previousLine = br.readLine();
			}
			
			previousUri = previousLine.substring(1, previousLine.indexOf('>'));
			String currentUri = new String(previousUri);
			currentLine = new String(previousLine);
			
			
			// While the end of file has not been reached and the same resource is being described
			while (currentLine != null && currentUri.equals(previousUri)){
				
				// Add this triple also
				this.addTriple(currentLine, individualModel);
				
				// Read a new triple
				currentLine = br.readLine();
				
				// Update the comparison URIs
				previousUri = currentUri;
				currentUri = currentLine.substring(1, currentLine.indexOf('>'));
				
			}			
			
			previousLine = currentLine;
			
		} catch (IOException e) {
			logger.log(Level.SEVERE, "Failed to read file '" + filename + "'.", e);
			exit = true;
			
		} 
		
		if (currentLine == null){
			exit = true;
		}
		
		this.notify();
		
		return individualModel;
	}
	
	private void addTriple(String line, Model individualModel){
		
		String[] triple = line.split("\\s+", 3);
		
		int pos = triple[0].length()-1;
		String uri = triple[0].substring(1, pos);
		Resource res = individualModel.getResource(uri);
		
		Property p = individualModel.createProperty(triple[1].substring(1, triple[1].indexOf('>')));
		
		// Lang
		if ((pos = triple[2].indexOf("\"@")) != -1){
			String literal = triple[2].substring(1,pos);
			String lang = triple[2].substring(pos+2, pos+4);
			
			res.addProperty(p, literal, lang);
			
		// Datatype
		} else if ((pos = triple[2].indexOf("\"^^<")) != -1){
			String literal = triple[2].substring(1,pos);
			String datatypeString = triple[2].substring(pos+4, triple[2].indexOf('>'));

			if (datatypeString.equals("http://www.w3.org/2001/XMLSchema#double")){
				res.addProperty(p, literal, XSDDatatype.XSDdouble);
			} else {
				res.addProperty(p, literal);	
			}
			
		// Object property
		} else if (triple[2].charAt(0) == '<'){
			String object_uri = triple[2].substring(1, triple[2].indexOf('>')); 
			Resource object = individualModel.createResource(object_uri);
			res.addProperty(p, object);
			
		// The rest
		} else {
			pos = triple[2].lastIndexOf('\"');
			String literal = triple[2].substring(1, pos);

			res.addProperty(p, literal);
			
		}

	}

}
