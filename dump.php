<?php

  include('gadm.php');

  // Allow only command-line usage
  if (PHP_SAPI != 'cli') 
  { 
    header("HTTP/1.0 404 Not Found");
    header('Content-type: text/html');
    die();
  } 

  // Validate the arguments
  if (!isset($argv[1]) || ($argv[1] != "nt" && $argv[1] != "nq")){
    echo "Usage: php dump.php [FORMAT]\nPlease use the desired output format as a parameter. Valid output formats are:\n nt: NTriples\n nq: NQuads\n\nExample: php dump.php nt\n";
    die();
  }

  $output_format = $argv[1];

  // Set appropiate MIME Type
  if ($output_format == "nq"){
    header('Content-type: application/x-nquads; charset=utf-8');
  }else{
    header('Content-type: application/x-ntriples; charset=utf-8');
  } 

  $offset = 0;
  $limit = 1000;

  // Build the SQL query
  // , ST_AsText(g.geometry) as geometry
  $sql = "SELECT g.*, s.uri as sameas_uri, e.uri as eq_uri, p.uri as ppi_uri
	      FROM gadm_regions g 
			LEFT JOIN spatial_eq e ON (g.gadm_level = e.gadm_level AND g.gadm_id = e.gadm_id)
			LEFT JOIN spatial_ppi p ON (g.gadm_level = p.gadm_level AND g.gadm_id = p.gadm_id)  
			LEFT JOIN owl_sameas s ON (g.gadm_level = s.gadm_level AND g.gadm_id = s.gadm_id)    
	      ORDER BY g.gadm_level, g.gadm_id, s.uri, e.uri, p.uri";

  // Execute the query and retrieve the results. This variable is used by the templates.
  $result = make_query($sql. " LIMIT ".$limit." OFFSET ".$offset);

  $bnode_id = 0;

  while (pg_num_rows($result) > 0){

    // Process the results
    while ($row = pg_fetch_array($result, null, PGSQL_ASSOC)){

      $sameas_uris = array(); 
      $eq_uris = array(); 
      $ppi_uris = array(); 
      
      // If there are EQ URIs
      if ($row['sameas_uri'] || $row['eq_uri'] || $row['ppi_uri']){
	  if ($row['sameas_uri']){
	    $sameas_uris[] = $row['sameas_uri']; 
	  }
	  if ($row['eq_uri']){
	    $eq_uris[] = $row['eq_uri']; 
	  }
	  if ($row['ppi_uri']){
	    $ppi_uris[] = $row['ppi_uri']; 
	  }
	  
	  // Find if there are more in the following rows
	  $rowB = pg_fetch_array($result, null, PGSQL_ASSOC);
	  while ($row['gadm_level'] == $rowB['gadm_level'] && $row['gadm_id'] == $rowB['gadm_id']){
	    if ($rowB['sameas_uri']){
	      $sameas_uris[] = $rowB['sameas_uri']; 
	    }
	    if ($rowB['eq_uri']){
	      $eq_uris[] = $rowB['eq_uri']; 
	    }
	    if ($rowB['ppi_uri']){
	      $ppi_uris[] = $rowB['ppi_uri']; 
	    }
	    $rowB = pg_fetch_array($result, null, PGSQL_ASSOC);
	  }
      }

      // Delete repeated URIs
      $sameas_uris = array_unique($sameas_uris);
      $eq_uris = array_unique($eq_uris);
      $ppi_uris = array_unique($ppi_uris);

      // Prepare the RDF output
      $data = array(
	'feature' => prepare_feature($row,$sameas_uris,$eq_uris,$ppi_uris),
	'geometry' => prepare_geometry($row)
      );

      // Print the RDF output
      foreach ($data as $resource){
	foreach ($resource['properties'] as $property){
	  if ($property['datatype'] == 'bnode'){
	    $bnode_id++;
	    echo '<'.$resource['uri'].'> <'.$property['predicate_uri'].'> _:bn'.$bnode_id;
	    echo ($output_format == "nq")? ' <'.$resource['uri'].'>' : '';
	    echo " .\n";
	    foreach ($property['object'] as $bnode_property){
		if ($bnode_property['datatype'] == 'anyURI'){
		  echo '_:bn'.$bnode_id.' <'.$bnode_property['predicate_uri'].'> <'.$bnode_property['object'].'>';
		}elseif(isset($bnode_property['lang'])){
		  echo '_:bn'.$bnode_id.' <'.$bnode_property['predicate_uri'].'> '.asciiEncode($bnode_property['object']).'@'.$bnode_property['lang'];
		}elseif(isset($property['datatype']) && $property['datatype'] != "XMLLiteral"){
		  echo '_:bn'.$bnode_id.' <'.$bnode_property['predicate_uri'].'> "'.$bnode_property['object'].'"^^<'.$bnode_property['datatype'].'>';
		}else{
		  echo '_:bn'.$bnode_id.' <'.$bnode_property['predicate_uri'].'> '.asciiEncode($bnode_property['object']);
		}
		echo ($output_format == "nq")? ' <'.$resource['uri'].'>' : '';
		echo " .\n";
	    }
	  }else{
	    if ($property['datatype'] == 'anyURI'){
	      echo '<'.$resource['uri'].'> <'.$property['predicate_uri'].'> <'.$property['object'].'>';
	    }elseif(isset($property['lang'])){
	      echo '<'.$resource['uri'].'> <'.$property['predicate_uri'].'> '.asciiEncode($property['object']).'@'.$property['lang'];
	    }elseif(isset($property['datatype']) && $property['datatype'] != "XMLLiteral"){
	      echo '<'.$resource['uri'].'> <'.$property['predicate_uri'].'> "'.$property['object'].'"^^<'.$property['datatype'].'>';
	    }else{
	      echo '<'.$resource['uri'].'> <'.$property['predicate_uri'].'> '.asciiEncode($property['object']);
	    }
	    echo ($output_format == "nq")? ' <'.$resource['uri'].'>' : '';
	    echo " .\n";
	  }
	}
      }
    }  

    // Execute the query and retrieve the results. This variable is used by the templates.
    $offset += $limit;
    $result = make_query($sql. " LIMIT ".$limit." OFFSET ".$offset);  

  }

?>