package org.geovocab.gadm;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.Properties;
import java.util.logging.Level;
import java.util.logging.LogManager;
import java.util.logging.Logger;

import com.hp.hpl.jena.ontology.Individual;
import com.hp.hpl.jena.ontology.OntClass;
import com.hp.hpl.jena.rdf.model.Literal;
import com.hp.hpl.jena.rdf.model.Model;
import com.hp.hpl.jena.rdf.model.Property;
import com.hp.hpl.jena.rdf.model.RDFNode;
import com.hp.hpl.jena.rdf.model.StmtIterator;
import com.hp.hpl.jena.util.FileManager;
import com.hp.hpl.jena.util.iterator.ExtendedIterator;

public class PostGIS {
	
	private String db_server;
	private String db_port;
	private String db_database;
	private String db_username;
	private String db_password;
	private java.sql.Connection conn;
	private Logger logger;
	private String vocab_namespace;
	
	public PostGIS(Properties cFile){
		logger = LogManager.getLogManager().getLogger("Main");
		
		Properties configFile = cFile;
		
		db_server = configFile.getProperty("DB_SERVER");
		db_port = configFile.getProperty("DB_PORT");
		db_database = configFile.getProperty("DB_DATABASE");
		db_username = configFile.getProperty("DB_USERNAME");
		db_password = configFile.getProperty("DB_PASSWORD");
		
		vocab_namespace = configFile.getProperty("VOCAB_NAMESPACE");
	}

	public Connection initPostGIS() throws ClassNotFoundException, SQLException{
		/* 
		 * Load the JDBC driver and establish a connection. 
		 */
		Class.forName("org.postgresql.Driver"); 
		String url = "jdbc:postgresql://" + db_server + ":" + db_port + "/" + db_database; 
		conn = DriverManager.getConnection(url, db_username, db_password); 
		
		((org.postgresql.PGConnection)conn).addDataType("geometry",Class.forName("org.postgis.PGgeometry"));
		return conn;
	}
	

	public void stopPostGIS() throws SQLException{
        conn.close();
	}
	
	public void saveEquivalence(String uri, String gadm_level, String gadm_id) {

		Statement stmt = null;
		try {
			stmt = conn.createStatement();
			String sql = "INSERT INTO owl_sameas (gadm_level, gadm_id, uri) " +
					"SELECT "+gadm_level+","+gadm_id+",'"+uri+"' WHERE NOT EXISTS (" +
							"SELECT * FROM owl_sameas WHERE gadm_level = "+gadm_level+" AND gadm_id = "+gadm_id+" AND uri = '"+uri+"' LIMIT 1" +
					") LIMIT 1;";
			stmt.executeUpdate(sql);

		} catch (SQLException e) {
			logger.log(Level.SEVERE, "Failed to insert row.", e);
		} finally {
			try {
				stmt.close();
			} catch (SQLException e) {
				logger.log(Level.WARNING, "Failed to close statement.",e);			
			}
		}

	}
	
}
