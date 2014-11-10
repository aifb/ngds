package org.geovocab.gadm;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;
import java.sql.CallableStatement;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.Properties;
import java.util.logging.Level;
import java.util.logging.LogManager;
import java.util.logging.Logger;

public class PostGIS {
	
	private String db_server;
	private String db_port;
	private String db_database;
	private String db_username;
	private String db_password;
	private Connection conn;
	private Logger logger;
	
	public PostGIS(Properties cFile){
		logger = LogManager.getLogManager().getLogger("Main");
		
		Properties configFile = cFile;
		
		db_server = configFile.getProperty("DB_SERVER");
		db_port = configFile.getProperty("DB_PORT");
		db_database = configFile.getProperty("DB_DATABASE");
		db_username = configFile.getProperty("DB_USERNAME");
		db_password = configFile.getProperty("DB_PASSWORD");
	}

	public void initPostGIS() throws ClassNotFoundException, SQLException{
		/* 
		 * Load the JDBC driver and establish a connection. 
		 */
		Class.forName("org.postgresql.Driver"); 
		String url = "jdbc:postgresql://" + db_server + ":" + db_port + "/" + db_database; 
		conn = DriverManager.getConnection(url, db_username, db_password); 
		
		((org.postgresql.PGConnection)conn).addDataType("geometry",Class.forName("org.postgis.PGgeometry"));
	}
	

	public void stopPostGIS() throws SQLException{
        conn.close();
	}
	
	public boolean callProcedure(String name, String table_name){
		CallableStatement cs = null;
		int retry = 3;
		while (retry > 0){
			try {
				cs = conn.prepareCall("{ call " + name + "(?) }");
				
				if (cs != null){
					retry = 0;
				}
			} catch (SQLException e) {
				logger.log(Level.WARNING, "An error occurred while connecting to PostgreSQL. Retrying.", e);
				try {
					this.stopPostGIS();
					this.initPostGIS();
					retry--;
				} catch (SQLException e1) {
					logger.log(Level.SEVERE, "Could not restore the connection.", e);
					retry = 0;
					return false;
				} catch (ClassNotFoundException e1) {
					logger.log(Level.SEVERE, "Could not restore the connection.", e1);
					retry = 0;
					return false;
				}
			}		
		}
		
		try {
			cs.setString(1, table_name);
			cs.execute();
		} catch (SQLException e) {
			logger.log(Level.SEVERE, e.getMessage() + ".",e);
			return false;
		} finally {
			try {
				cs.close();
			} catch (SQLException e) {
				logger.log(Level.WARNING, "Unable to close SQL statement.", e);
			}
		}
		
		return true;
	    
	}
	
	public boolean executeFile(File f, boolean autoCommit) {
		
		int buffer = 1024;
		
		StringBuilder sql = new StringBuilder();
		

		BufferedReader br;
		try {
			br = new BufferedReader(new FileReader(f));

			Statement st = null;
			try {
				int retry = 3;
				while (retry > 0){
					try {
						conn.setAutoCommit(autoCommit);
						st = conn.createStatement();
						if (st != null){
							// Connection is ok, do not retry.
							retry = 0;	
						}
					} catch (SQLException e) {
						logger.log(Level.WARNING, "An error occurred while connecting to PostgreSQL. Retrying.", e);
						try {
							this.stopPostGIS();
							this.initPostGIS();
							retry--;
						} catch (SQLException e1) {
							logger.log(Level.SEVERE, "Could not restore the connection.", e);
							retry = 0;
							return false;
						} catch (ClassNotFoundException e1) {
							logger.log(Level.SEVERE, "Could not restore the connection.", e1);
							retry = 0;
							return false;
						}
					}
				}

				try{
					int count;
					char data[] = new char[buffer];

					while ((count = br.read(data, 0, buffer)) != -1) {

						String s = new String(data);
						data = new char[buffer];

						int endPos = 0;				

						while ((endPos = s.indexOf(';')) != -1){

							String left_split = s.substring(0,endPos+1);
							sql.append(left_split);

							String right_split = s.substring(endPos+1);

							// Detect if the ; is within an open parentheses
							int last_par_open = sql.lastIndexOf("(");
							int last_par_closed = sql.lastIndexOf(")");

							// Detect if the ; is within an open dollar string						
							int dollar_occurrences = 0;
							int index = 0;
							while (index < sql.length() && (index = sql.indexOf("$$", index)) >= 0) {
								dollar_occurrences++;
								index += 2; //length of '$$'
							}

							// If it is not, then we execute
							if ((last_par_open == -1 || last_par_open < last_par_closed) 
									&& (dollar_occurrences % 2 == 0)){
								st.execute(sql.toString());
								sql = new StringBuilder();
							}

							s = right_split;
						}

						sql.append(s);

					}
				} catch (IOException e) {
					logger.log(Level.SEVERE, e.getMessage() + ".",e);
					return false;
				} catch (SQLException e) {
					logger.log(Level.SEVERE, e.getMessage() + ".",e);
					return false;
				}

				if (!autoCommit){
					try{
						conn.commit();	
					} catch (SQLException e) {
						logger.log(Level.SEVERE, e.getMessage() + ".",e);
						logger.log(Level.INFO, "Rolling back");
						try {
							conn.rollback();
						} catch (SQLException e1) {
							logger.log(Level.SEVERE, "Rollback failed.", e1);
						}
						return false;
					}	
				}										
		        

			} finally {
				try {
					br.close();
				} catch (IOException e) {
					logger.log(Level.WARNING, "Unable to close stream.", e);
				}
		        try {
					st.close();
				} catch (SQLException e) {
					logger.log(Level.WARNING, "Unable to close SQL statement.", e);
				}	
			}
			
		} catch (FileNotFoundException e) {
			logger.log(Level.SEVERE, "File '" + f.getName() + "' not found.", e);
			return false;
		}
		
		return true;				
	}
	
}
