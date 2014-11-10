<?php
  include('../gadm.php');
  require_once('../config.php');

  // Parameter validation
  if ( !isset($_GET['bbox']) && !isset($_GET['BBOX'])) 
  {
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-type: text/html');
    die("Bounding box (bbox) parameter required.");
  }

  // Support for Google Earth uppercase parameter
  $bounding_box = (isset($_GET['bbox']))? $_GET['bbox'] : $_GET['BBOX'];

  $bounding_box = explode(',', $bounding_box);

  if (count($bounding_box) != 4)
  {
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-type: text/html');
    die("Malformed bounding box.");
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

  $content_type = (isset($_GET['content_type']))? $_GET['content_type'] : "rdf";
  $content_type = stripslashes($content_type);

  $ogc_ns = "http://www.opengis.net/rdf#";
  $gadm_inst_ns = BASE_URL."/id/";
  $spatial_ns = BASE_URL."/spatial#";

  $wktBBox = "POLYGON((".$longA." ".$latA.",".$longB." ".$latA.",".$longB." ".$latB.",".$longA." ".$latB.",".$longA." ".$latA."))";

  if (isset($_GET['zoom_filter']) && $_GET['zoom_filter']){
    if (abs($longB-$longA) > 20){
      $type_clause = "INNER JOIN rdf_type t ON (g.gadm_level = t.gadm_level AND g.gadm_id = t.gadm_id AND t.uri='http://gadm.geovocab.org/ontology#Country')";
    }elseif(abs($longB-$longA) > 5){
      $type_clause = "INNER JOIN rdf_type t ON (g.gadm_level = t.gadm_level AND g.gadm_id = t.gadm_id AND t.uri = 'http://gadm.geovocab.org/ontology#Level1')";
    }else{
      $type_clause = "INNER JOIN rdf_type t ON (g.gadm_level = t.gadm_level AND g.gadm_id = t.gadm_id AND t.uri = 'http://gadm.geovocab.org/ontology#Level2')";
    }
  }else{
    $type_clause = "";
  }

  // Perform the SQL query
  if ($content_type == "gml" || $content_type == "svg" || $content_type == "geojson"){

    $geometry = get_geometry($content_type, false, true);
    $sql = "SELECT 1 as group, ".$geometry." FROM gadm_regions g ".$type_clause." WHERE ST_Intersects(ST_GeomFromText('".$wktBBox."', 4326),g.simplified_geometry) GROUP BY 1, g.geometry";

    $result = make_query($sql);
    $data = pg_fetch_array($result);

  } elseif ($content_type == "kml" || $content_type == "kmz") {

    $sql = "SELECT g.gadm_level, g.gadm_id, g.name FROM gadm_regions g ".$type_clause." WHERE ST_Intersects(ST_GeomFromText('".$wktBBox."', 4326),g.simplified_geometry)";

    $result = make_query($sql);

    $data = array();
    $data['geometry'] = $wktBBox;
    while ( $row = pg_fetch_array($result) ) {
      $data['links'][] = array('name' => $row['name'], 'url' => $gadm_inst_ns.$row['gadm_level'].'_'.$row['gadm_id'].'_simplified_geometry.kmz');
    }

  } else {
    $sql = "SELECT g.gadm_level, g.gadm_id FROM gadm_regions g ".$type_clause." WHERE ST_Intersects(ST_GeomFromText('".$wktBBox."', 4326),g.simplified_geometry)";

    $result = make_query($sql);

    $resource = array();
    $resource['uri'] = BASE_URL."/services/boundingBox?bbox=".$_GET['bbox']."#bbox";
    $resource['properties'] = array();
    $resource['properties'][] = array('predicate_uri' => $ogc_ns."asWKT", 'predicate' => "ogc:asWKT", 'object' => $wktBBox, 'datatype' => $ogc_ns."WKTLiteral");
    while ( $row = pg_fetch_array($result) ) {
      $resource['properties'][] = array('predicate_uri' => $spatial_ns."PPi", 'predicate' => "spatial:PPi", 'object' => $gadm_inst_ns.$row['gadm_level'].'_'.$row['gadm_id'], 'datatype' => "anyURI");
    }
    $data = array($resource);

  }
  show_template($data, $content_type);