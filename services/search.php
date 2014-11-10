<?php
  include('../gadm.php');
  require_once('../config.php');

  // Parameter validation
  if ( count($_GET) == 0 )
  {
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-type: text/html');
    die("A search criteria is needed.");
  }

  $conditions = array();
  $joins = array();

  // Check for intersection relations with a region defined by point and radius
  if (isset($_GET['geo_long']) && isset($_GET['geo_lat'])){
    $long = stripslashes($_GET['geo_long']);
    $lat = stripslashes($_GET['geo_lat']);

    if (!is_numeric($long) || !is_numeric($lat)) {
      header('HTTP/1.1 500 Internal Server Error');
      header('Content-type: text/html');
      die("The radius must be a numeric value.");
    }

    $point_geom = "ST_GeomFromText('POINT(".$long." ".$lat.")',4326)";

    if (isset($_GET['radius'])){
      $radius = stripslashes($_GET['radius']);

      if (!is_numeric($radius)){
	header('HTTP/1.1 500 Internal Server Error');
	header('Content-type: text/html');
	die("The radius must be a numeric value.");
      }

      $point_geom = "ST_Buffer(".$point_geom.",".$radius.")";
    }
    
    $conditions[] = "ST_Intersects(g.simplified_geometry,".$point_geom.")";
  }

  // Check for similarity with rdfs:label
  if (isset($_GET['rdfs_label'])) {
    $rdfs_label = urldecode(stripslashes($_GET['rdfs_label']));
    $conditions[] = "(g.name ILIKE '%".$rdfs_label."%' OR g.name_english ILIKE '%".$rdfs_label."%' OR g.name_variations ILIKE '%".$rdfs_label."%' 
		    OR g.name_french ILIKE '%".$rdfs_label."%' OR g.name_spanish ILIKE '%".$rdfs_label."%')";
  }
  
  // Filter resources of a certain class
  if (isset($_GET['rdf_type'])) {
    $rdf_type = urldecode(stripslashes($_GET['rdf_type']));
    $joins[] = "INNER JOIN rdf_type t ON (g.gadm_level = t.gadm_level AND g.gadm_id = t.gadm_id)";
    $conditions[] = "t.uri = '".$rdf_type."'";
  }
  

  // Is properly contained within a certain bounding box
  $wktBBox = "";
  if (isset($_GET['bbox']) || isset($_GET['BBOX'])) {

    // Support for Google Earth uppercase parameter
    $bounding_box = (isset($_GET['bbox']))? $_GET['bbox'] : $_GET['BBOX'];

    $bounding_box = explode(',', $bounding_box);

    if (count($bounding_box) != 4)
    {
      header('HTTP/1.1 500 Internal Server Error');
      header('Content-type: text/html');
      die("Malformed bouning box.");
    }

    $longA = stripslashes($bounding_box[0]);
    $latA = stripslashes($bounding_box[1]);
    $longB = stripslashes($bounding_box[2]);
    $latB = stripslashes($bounding_box[3]);

    if (!is_numeric($longA) || !is_numeric($latA) || !is_numeric($longB) || !is_numeric($latB))
    {
      header('HTTP/1.1 500 Internal Server Error');
      header('Content-type: text/html');
      die("The latitude and longitude parameters of the bounding box must be numeric values.");
    }
    $wktBBox = "POLYGON((".$longA." ".$latA.",".$longB." ".$latA.",".$longB." ".$latB.",".$longA." ".$latB.",".$longA." ".$latA."))";

    $conditions[] = "ST_ContainsProperly(ST_GeomFromText('".$wktBBox."', 4326), g.simplified_geometry)";
  }  

  // Check if the geometry overlaps a certain region
  if (isset($_GET['spatial_o'])) {
    $uri = urldecode(stripslashes($_GET['spatial_o']));
    if (substr($uri,0,28) != "http://gadm.geovocab.org/id/"){
	header('HTTP/1.1 500 Internal Server Error');
	header('Content-type: text/html');
	die("For security reasons, only regions in the GADM-RDF namespace are allowed.");
    }
    $gadm_level = substr($uri,28,1);
    $gadm_id = substr($uri,30);
    if (!is_numeric($gadm_level) || !is_numeric($gadm_id)){
      header('HTTP/1.1 500 Internal Server Error');
      header('Content-type: text/html');
      die("Malformed URI.");
    }

    $joins[] = "INNER JOIN gadm_regions g1 ON (g.simplified_geometry && g1.simplified_geometry)";
    $conditions[] = "ST_Intersects(g.simplified_geometry, g1.simplified_geometry) AND g1.gadm_level = ".$gadm_level." AND g1.gadm_id = ".$gadm_id;
  }

  // Check if the geometry is externally connected to a certain region
  if (isset($_GET['spatial_ec'])) {
    $uri = urldecode(stripslashes($_GET['spatial_ec']));
    if (substr($uri,0,28) != "http://gadm.geovocab.org/id/"){
	header('HTTP/1.1 500 Internal Server Error');
	header('Content-type: text/html');
	die("For security reasons, only regions in the GADM-RDF namespace are allowed.");
    }
    $gadm_level = substr($uri,28,1);
    $gadm_id = substr($uri,30);
    if (!is_numeric($gadm_level) || !is_numeric($gadm_id)){
      header('HTTP/1.1 500 Internal Server Error');
      header('Content-type: text/html');
      die("Malformed URI.");
    }

    $joins[] = "INNER JOIN gadm_regions g1 ON (g.simplified_geometry && g1.simplified_geometry)";
    $conditions[] = "ST_Touches(g.simplified_geometry, g1.simplified_geometry) AND g1.gadm_level = ".$gadm_level." AND g1.gadm_id = ".$gadm_id;
  }
  

  // Check if the geometry partially overlaps to a certain region
  if (isset($_GET['spatial_po'])) {
    $uri = urldecode(stripslashes($_GET['spatial_po']));
    if (substr($uri,0,28) != "http://gadm.geovocab.org/id/"){
	header('HTTP/1.1 500 Internal Server Error');
	header('Content-type: text/html');
	die("For security reasons, only regions in the GADM-RDF namespace are allowed.");
    }
    $gadm_level = substr($uri,28,1);
    $gadm_id = substr($uri,30);
    if (!is_numeric($gadm_level) || !is_numeric($gadm_id)){
      header('HTTP/1.1 500 Internal Server Error');
      header('Content-type: text/html');
      die("Malformed URI.");
    }

    $joins[] = "INNER JOIN gadm_regions g1 ON (g.simplified_geometry && g1.simplified_geometry)";
    $conditions[] = "ST_Overlaps(g.simplified_geometry, g1.simplified_geometry) AND g1.gadm_level = ".$gadm_level." AND g1.gadm_id = ".$gadm_id;
  }
  

  // Check if the geometry partially overlaps to a certain region
  // TODO: The DBpedia links are not shown by the service. It should return them and also perform inference on the reginos where they are
  // not materialized. Since we are not keeping the coordinates, we should use the known relations. This remains unsolved for the moment.
  if (isset($_GET['spatial_pp'])) {
    $uri = urldecode(stripslashes($_GET['spatial_pp']));
    if (substr($uri,0,28) != "http://gadm.geovocab.org/id/"){
	header('HTTP/1.1 500 Internal Server Error');
	header('Content-type: text/html');
	die("For security reasons, only regions in the GADM-RDF namespace are allowed.");
    }
    $gadm_level = substr($uri,28,1);
    $gadm_id = substr($uri,30);
    if (!is_numeric($gadm_level) || !is_numeric($gadm_id)){
      header('HTTP/1.1 500 Internal Server Error');
      header('Content-type: text/html');
      die("Malformed URI.");
    }

    $joins[] = "INNER JOIN gadm_regions g1 ON (g.simplified_geometry && g1.simplified_geometry)";
    $conditions[] = "ST_ContainsProperly(g1.simplified_geometry, g.simplified_geometry) AND g1.gadm_level = ".$gadm_level." AND g1.gadm_id = ".$gadm_id;
  }
  

  // Check if the geometry partially overlaps to a certain region
  if (isset($_GET['spatial_ppi'])) {
    $uri = urldecode(stripslashes($_GET['spatial_ppi']));
    if (substr($uri,0,28) != "http://gadm.geovocab.org/id/"){
	header('HTTP/1.1 500 Internal Server Error');
	header('Content-type: text/html');
	die("For security reasons, only regions in the GADM-RDF namespace are allowed.");
    }
    $gadm_level = substr($uri,28,1);
    $gadm_id = substr($uri,30);
    if (!is_numeric($gadm_level) || !is_numeric($gadm_id)){
      header('HTTP/1.1 500 Internal Server Error');
      header('Content-type: text/html');
      die("Malformed URI.");
    }

    $joins[] = "INNER JOIN gadm_regions g1 ON (g.simplified_geometry && g1.simplified_geometry)";
    $conditions[] = "ST_ContainsProperly(g.simplified_geometry, g1.simplified_geometry) AND g1.gadm_level = ".$gadm_level." AND g1.gadm_id = ".$gadm_id;
  }


  // Check if a certain criteria was provided
  if (empty($conditions)) {
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-type: text/html');
    die("Search criteria needed");
  }

  // Prepare the SQL query
  $clauses = "";

  // Remove duplicated joins
  $joins = array_unique($joins);

  foreach ($joins as $join){
    $clauses .= " ".$join;
  }

  $clauses .= " WHERE ".$conditions[0];
  $nConditions = count($conditions);

  for ($i = 1; $i < $nConditions; $i++){
    $clauses .= " AND ".$conditions[$i];
  }


  // Perform the SQL query and assemble the model
  $content_type = (isset($_GET['content_type']))? $_GET['content_type'] : "rdf";
  $content_type = stripslashes($content_type);

  $ogc_ns = "http://www.opengis.net/rdf#";
  $gadm_inst_ns = BASE_URL."/id/";
  $rdfs_ns = "http://www.w3.org/2000/01/rdf-schema#";

  if ($content_type == "gml" || $content_type == "svg" || $content_type == "geojson"){

    $geometry = get_geometry($content_type, false, true);
    $sql = "SELECT g.gadm_level, g.gadm_id, g.name,".$geometry." FROM gadm_regions g".$clauses;
    $sql .= "GROUP BY 1, g.gadm_level, g.gadm_id, g.name, geometry";
    $result = make_query($sql);
    $data = pg_fetch_array($result);

  } elseif ($content_type == "kml" || $content_type == "kmz") {

    $sql = "SELECT g.gadm_level, g.gadm_id, g.name FROM gadm_regions g".$clauses;
    $result = make_query($sql);
    $data = array();

    // TODO: Add the point and radius
    if ($wktBBox != ""){
      $data['geometry'] = $wktBBox;
    }    
    while ( $row = pg_fetch_array($result) ) {
      $data['links'][] = array('name' => $row['name'], 'url' => $gadm_inst_ns.$row['gadm_level'].'_'.$row['gadm_id'].'_simplified_geometry.kmz');
    }

  } else {

    $sql = "SELECT g.gadm_level, g.gadm_id, g.name FROM gadm_regions g".$clauses;
    $result = make_query($sql);
    $resource = array();
    $resource['uri'] = "#results";
    $resource['properties'] = array();

    while ( $row = pg_fetch_array($result) ) {
      $resource['properties'][] = array('predicate_uri' => $rdfs_ns."seeAlso", 'predicate' => "rdfs:seeAlso", 'object' => $gadm_inst_ns.$row['gadm_level'].'_'.$row['gadm_id'], 'datatype' => "anyURI");
    }
    $data = array($resource);

  }

  show_template($data, $content_type);
