<?php
include('../gadm.php');
require_once('../config.php');

// Parameter validation
if ( !isset($_GET['relation']))
{
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-type: text/html');
        die("A relation must be specified.");
}
//the relation will be checked later
$relation = $_GET['relation'];

//the uppercase BBOX is for google earth
if (!isset($_GET['bbox']) && !isset($_GET['BBOX']) && !isset($_GET['point']) && !isset($_GET['polygon'])) 
{
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-type: text/html');
    die("Bounding box (bbox) or point or polygon parameter required.");
}

if (isset($_GET['bbox']) || isset($_GET['BBOX']))
{
    $wktGeometry = (isset($_GET['bbox']))? boundingBoxWktParser($_GET['bbox']) : boundingBoxWktParser($_GET['BBOX']);
} elseif (isset($_GET['point'])) {
    $wktGeometry = pointWktParser($_GET['point']);
} else {
    $wktGeometry = polygonWktParser($_GET['polygon']);
}

$content_type = (isset($_GET['content_type']))? $_GET['content_type'] : "rdf";
$content_type = stripslashes($content_type);

$ogc_ns = "http://www.opengis.net/rdf#";
$gadm_inst_ns = BASE_URL."/id/";
$spatial_ns = BASE_URL."/spatial#";

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

$whereClause = neogeoToPostgis($relation, "ST_GeomFromText('".$wktGeometry."', 4326)", "g.simplified_geometry");
if ($whereClause == null) {
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-type: text/html');
        die("No valid neogeo spatial relation.");
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
    $data['links'][] = array('name' => $row['name'], 'url' => $gadm_inst_ns.$row['gadm_level'].'/'.$row['gadm_id'].'/simplified_geometry.kml');
    }

} else {
    $sql = "SELECT g.gadm_level, g.gadm_id, g.name, g.name_english, g.name_iso FROM gadm_regions g ".$type_clause." WHERE ".$whereClause;
    $result = make_query($sql);

    $data = array();
    //$resource['uri'] = BASE_URL."/services/boundingBox?bbox=".$_GET['bbox']."#bbox";
    //$resource['properties'] = array();
    //$resource['properties'][] = array('predicate_uri' => $ogc_ns."asWKT", 'predicate' => "ogc:asWKT", 'object' => $wktBBox, 'datatype' => $ogc_ns."WKTLiteral");
    $rdfs_ns = "http://www.w3.org/2000/01/rdf-schema#";
    $gadm_ns = BASE_URL."/ontology#";
    while ( $row = pg_fetch_array($result) ) {
    $resource = array();
    $resource['uri'] = $gadm_inst_ns.$row['gadm_level'].'/'.$row['gadm_id'];
    $resource['properties'][] = array('subject' => $resource['uri'], 'predicate_uri' => $rdfs_ns."label", 'predicate' => "rdfs:label", 'object' => $row['name'], 'datatype' => "XMLLiteral");
    if ($row['name_iso'] != '') {
        $resource['properties'][] = array('subject' => $resource['uri'], 'predicate_uri' => $gadm_ns."name_iso", 'predicate' => "gadm:name_iso", 'object' => $row['name_iso'], 'datatype' => "XMLLiteral");
    }
    if ($row['name_english'] != '') {
        $resource['properties'][] = array('subject' => $resource['uri'], 'predicate_uri' => $rdfs_ns."label", 'predicate' => "rdfs:label", 'object' => $row['name_english'], 'datatype' => "XMLLiteral", 'lang' => "en");
    }
    $resource['properties'][] = array('subject' => $resource['uri'], 'predicate' => "gadm:gadm_level", 'object' => $row['gadm_level'], 'datatype' => "decimal");
    $data[] = $resource;
    }
}
show_template($data, $content_type);