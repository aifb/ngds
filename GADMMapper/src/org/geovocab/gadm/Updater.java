package org.geovocab.gadm;

import java.io.UnsupportedEncodingException;
import java.net.URLDecoder;
import java.net.URLEncoder;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Properties;
import java.util.concurrent.CountDownLatch;
import java.util.logging.Level;
import java.util.logging.LogManager;
import java.util.logging.Logger;

import com.hp.hpl.jena.ontology.Individual;
import com.hp.hpl.jena.ontology.OntClass;
import com.hp.hpl.jena.ontology.OntModel;
import com.hp.hpl.jena.ontology.OntModelSpec;
import com.hp.hpl.jena.query.Query;
import com.hp.hpl.jena.query.QueryExecution;
import com.hp.hpl.jena.query.QueryExecutionFactory;
import com.hp.hpl.jena.query.QueryFactory;
import com.hp.hpl.jena.rdf.model.Literal;
import com.hp.hpl.jena.rdf.model.Model;
import com.hp.hpl.jena.rdf.model.ModelFactory;
import com.hp.hpl.jena.rdf.model.NodeIterator;
import com.hp.hpl.jena.rdf.model.Property;
import com.hp.hpl.jena.rdf.model.RDFNode;
import com.hp.hpl.jena.rdf.model.ResIterator;
import com.hp.hpl.jena.rdf.model.Resource;
import com.hp.hpl.jena.rdf.model.Statement;
import com.hp.hpl.jena.rdf.model.StmtIterator;
import com.hp.hpl.jena.util.FileManager;
import com.hp.hpl.jena.util.iterator.ExtendedIterator;
import com.hp.hpl.jena.vocabulary.RDFS;

public class Updater extends Thread {
	
	private Properties configFile;
	private Reader reader;
	private CountDownLatch updater_latch;
	private String mappings_file;
	private String fetch_mode;
	private String sparql_endpoint;
	private String service_endpoint;
	private String vocab_namespace;
	private PostGIS postgis;
	private Logger logger;
	
	public Updater(Reader r, Properties cFile, CountDownLatch cdl){
		reader = r;
		configFile = cFile;
		updater_latch = cdl;
		logger = LogManager.getLogManager().getLogger("Main");
		mappings_file = configFile.getProperty("MAPPINGS_FILE");
		vocab_namespace = configFile.getProperty("VOCAB_NAMESPACE");
		fetch_mode = configFile.getProperty("FETCH_MODE");
		service_endpoint = configFile.getProperty("SERVICE_ENDPOINT");
		sparql_endpoint = configFile.getProperty("SPARQL_ENDPOINT");;
		postgis = new PostGIS(configFile);
	}
	
	public void run(){
		logger.log(Level.INFO, "Starting updater thread.");
		
		try {
			// Start a connection to the database
			postgis.initPostGIS();
			
			// Load the model containing the mappings
			Model equivalences = ModelFactory.createOntologyModel();
			equivalences.read("file:"+mappings_file, "Turtle");
			
			if (fetch_mode.equals("FILE")){
				this.updateFromFile(equivalences);	
			} else {
				this.updateByLookup(equivalences);	
			}
			
			
			
		} catch (ClassNotFoundException e) {
			logger.log(Level.SEVERE, "Class not found.", e);
		} catch (SQLException e) {
			logger.log(Level.SEVERE, "Could not connect to PostGIS.", e);
		} finally {
			try {
				postgis.stopPostGIS();
			} catch (SQLException e) {
				logger.log(Level.WARNING, "Could not close the connection to PostGIS.", e);
			}
		}
		
		logger.log(Level.INFO, "Finishing updater thread.");
		updater_latch.countDown();
	}
	
	private void updateByLookup(Model equivalences){
		// Get the first URI
		String uri = reader.getLine();
		while (uri != null) {
			uri = uri.replace("\"", "").replace("<", "").replace(">", "");
			
			// Create a model that supports inference
			OntModel model = ModelFactory.createOntologyModel( new OntModelSpec(OntModelSpec.OWL_MEM_MICRO_RULE_INF));
			
			// Load the model in memory
			Model m = ModelFactory.createDefaultModel();
			if (fetch_mode.equals("SPARQL")) {
				m = this.getBySPARQL(uri);
			} else {
				m.read(uri, "Turtle");
			}
			
			model.add(m);
			
			// Load the mappings
			model.add(equivalences);
			
			Property rdfs_label = m.getProperty("http://www.w3.org/2000/01/rdf-schema#label");
			Property geo_lat = m.getProperty("http://www.w3.org/2003/01/geo/wgs84_pos#lat");
			Property geo_long = m.getProperty("http://www.w3.org/2003/01/geo/wgs84_pos#long");
			
			String latitude = "";
			String longitude = "";
			String label = "";
			String type = "";
			boolean found = false;
			
			// Get the resource
			Individual ind = model.getIndividual(uri);
			
			// And save it
			if (ind == null){
				logger.log(Level.INFO, uri + " not found.");	
			} else {
			
				ExtendedIterator<OntClass> iter = ind.listOntClasses(true);
				while (iter.hasNext() && !found){
					OntClass ont_class = iter.next();
					if (ont_class.getNameSpace().equals(vocab_namespace)){
						try {
							type = URLEncoder.encode(ont_class.getURI(), "UTF-8");
						} catch (UnsupportedEncodingException e) {}
						found = true;
					}
				}
						
				
				StmtIterator iter2 = ind.listProperties(rdfs_label);
				found = false;
				while (iter2.hasNext() && !found){
					Literal l = iter2.next().getObject().asLiteral();
					
					// TODO: Config label langugage
					if (l.getLanguage().equals("es") || l.getLanguage() == null){
						try {
							label = URLEncoder.encode(l.getString(), "UTF-8");
						} catch (UnsupportedEncodingException e) {}
						found = true;
					}
					
				}
				
				
				if (ind.hasProperty(geo_lat) && ind.hasProperty(geo_long)){
					latitude = ind.getProperty(geo_lat).getLiteral().getString();
					longitude = ind.getProperty(geo_long).getLiteral().getString();
				} else {
					Property geo_geometry = m.getProperty("http://www.w3.org/2003/01/geo/wgs84_pos#geometry");
					RDFNode node = ind.getPropertyValue(geo_geometry);
					if (node != null){
						String geo_uri = node.toString();
						Model geo_model = ModelFactory.createDefaultModel();
						geo_model.read(geo_uri, "Turtle");
		
						Resource geo_res = geo_model.getResource(geo_uri);
						latitude = geo_res.getProperty(geo_lat).getLiteral().getString();
						longitude = geo_res.getProperty(geo_long).getLiteral().getString();	
					}
				}
				
				
				if (!label.equals("") && !latitude.equals("") && !latitude.equals("") && !type.equals("")){
					
					// TODO: It should be possible to map the spatial:PP relation to the target vocabulary and compare from 
					// the top levels to the lower ones. For example. Get the regions that are spatial:PPi by Spain in target ontology, then for
					// each of those regions do a lookup to the LIDS with the spatial_pp parameter for Spain.
					String query_string = "?rdfs_label="+label+"&radius=0.5&geo_lat="+latitude+"&geo_long="+longitude+"&rdf_type="+type; 
					
					// TODO: Prepare the query to the LD Service
					Model service_results = ModelFactory.createDefaultModel();
					String results_uri = service_endpoint+query_string;
					service_results.read(results_uri, "RDF/XML");
					
					model.add(service_results);
					
					// Analyze the results
					Resource results = service_results.getResource(results_uri+"#results");
					StmtIterator res_iterator = results.listProperties(RDFS.seeAlso);
					int num_res = 0;
					String gadm_level = null;
					String gadm_id = null;
					String gadm_uri;
					while (res_iterator.hasNext()){
						num_res++;
						gadm_uri = res_iterator.next().getObject().toString();
						gadm_level = gadm_uri.substring(28, 29);
						gadm_id = gadm_uri.substring(30);
					}
					
					if (num_res == 1){
						logger.log(Level.INFO, "Saving " + uri + ".");
						postgis.saveEquivalence(uri, gadm_level, gadm_id);
					}
					else if (num_res > 0){
						logger.log(Level.INFO, "Ambiguous results for " + uri + ". Skipping.");
					} else {
						logger.log(Level.INFO, "No match found for " + uri + ". Skipping.");
					}	
				} else {
					logger.log(Level.INFO, "Skipping " + uri + ".");
				}
				
			}
			
			// Get a new URI
			uri = reader.getLine();
		}
	}
	
	private void updateFromFile(Model equivalences){
		
	}
	
	
	public Model getBySPARQL(String uri){
		
		String queryString= "CONSTRUCT {?s ?p ?o} WHERE {<" + uri + "> ?p ?o. ?s ?p ?o }";

		Query query = QueryFactory.create(queryString);
		QueryExecution qexec = QueryExecutionFactory.sparqlService(sparql_endpoint, query);

		Model results;
		try {
			results = qexec.execConstruct();
		}
		finally {
			qexec.close();
		}
		
		return results;
	}

}
