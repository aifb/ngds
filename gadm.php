<?php
require_once 'config.php';

function validate_request(){
	if ( !isset($_GET['level']) )
	{
		header('HTTP/1.1 500 Internal Server Error');
		header('Content-type: text/html');
		die("'level' parameter required.");
	}
	if ( !isset($_GET['id']) )
	{
		header('HTTP/1.1 500 Internal Server Error');
		header('Content-type: text/html');
		die("'id' parameter required.");
	}

	if (!is_numeric($_GET['level']) || !is_numeric($_GET['id'])){
		header("HTTP/1.0 404 Not Found");
		header('Content-type: text/html');
		die();
	}

	$params = array();
	$params['gadm_level'] = $_GET['level'];
	$params['gadm_id'] = $_GET['id'];

	$params['content_type'] = stripslashes($_GET['content_type']);

	$params['feature'] = isset($_GET['feature']) && (boolean) stripslashes($_GET['feature']);
	$params['geometry'] = isset($_GET['geometry']) && (boolean) stripslashes($_GET['geometry']);
	if (isset($_GET['geometry'])) {
		$params['resolution'] = stripslashes($_GET['resolution']);
	} else {
		$params['resolution'] = 0;
	}

	$params['simplify'] = isset($_GET['simplify']) && (boolean) stripslashes($_GET['simplify']);
	return $params;
}

function validate_request_level(){
	if ( !isset($_GET['level']) )
	{
		header('HTTP/1.1 500 Internal Server Error');
		header('Content-type: text/html');
		die("'level' parameter required.");
	}
	
	if (!is_numeric($_GET['level'])){
		header("HTTP/1.0 404 Not Found");
		header('Content-type: text/html');
		die();
	}

	$params = array();
	$params['gadm_level'] = $_GET['level'];

	$params['content_type'] = stripslashes($_GET['content_type']);
	if (isset($_GET['geometry'])) {
		$params['resolution'] = stripslashes($_GET['resolution']);
	} else {
		$params['resolution'] = 0;
	}
	
	if (isset($_GET['limit'])) {
	    $params['limit'] = explode(',', stripslashes($_GET['limit']));
	    if (!(count($params['limit'])==2 && is_numeric($params['limit'][0]) && is_numeric($params['limit'][1]))) {
		$params['limit'] = null;
	    }
	} else {
	    $params['limit'] = null;
	}
	$params['feature'] = isset($_GET['feature']) && (boolean) stripslashes($_GET['feature']);
	$params['geometry'] = isset($_GET['geometry']) && (boolean) stripslashes($_GET['geometry']);
	$params['simplify'] = isset($_GET['simplify']) && (boolean) stripslashes($_GET['simplify']);
	return $params;
}

function get_geometry($content_type, $simplify = false, $aggregate = false, $resolution = 0){

	if ($simplify || $resolution){
		if ($resolution != 0){
			// Retrieve a scaled down version of the geometry by snapping the points to a grid. Consecutive points within the same slot will be ignored.
			$source_geom = "ST_SnapToGrid(ST_SimplifyPreserveTopology(g.geometry, ".($resolution/100)."), ".($resolution/10000).")";
		}else{
			// The Google Maps Widget does not load geometries that uncompress to more than 1MB. Also, if it takes too long to
			// build the file, the geometry will not load because of a timeout. Here we set an arbitrary maximum of 20000 points
			// for each geometry.
			//$source_geom = "ST_SimplifyPreserveTopology(g.geometry,(ST_Perimeter(g.geometry)/20000))";
			$source_geom = "g.simplified_geometry";
		}
	}else{
		$source_geom = "g.geometry";
	}

	if ($aggregate){
		$source_geom = "ST_Multi(ST_Collect(".$source_geom."))";
	}

	switch ($content_type){
		case 'kml':
			$geometry = "asKML(".$source_geom.",6) as geometry";
			break;
		case 'kmz':
			$geometry = "asKML(".$source_geom.",6) as geometry";
			break;
		case 'gml':
			$geometry = "asGML(".$source_geom.",6) as geometry";
			break;
		case 'svg':
			$geometry = "ST_asSVG(".$source_geom.",0,6) as geometry, ST_Box2D(".$source_geom.") as bbox";
			break;
		case 'geojson':
			$geometry = "ST_AsGeoJSON(".$source_geom.",6) as geometry";
			break;
		default:
			$geometry = "ST_AsText(".$source_geom.") as geometry";
	}

	return $geometry;
}


function prepare_feature($data, $sameas_uris = array(), $eq_uris = array(), $ppi_uris = array()){

	$rdf_ns = "http://www.w3.org/1999/02/22-rdf-syntax-ns#";
	$rdfs_ns = "http://www.w3.org/2000/01/rdf-schema#";
	$spatial_ns = "http://geovocab.org/spatial#";
	$ngeo_ns = "http://geovocab.org/geometry";
	$gadm_ns = BASE_URL."/ontology#";
	$gadm_inst_ns = BASE_URL."/id/";
	$gadm_igov_ns = BASE_URL."/igov#";
	$owl_ns = "http://www.w3.org/2002/07/owl#";

	$feature = array();
	$feature_id = $data['gadm_level'].'/'.$data['gadm_id'];
	$feature_uri = $gadm_inst_ns.$feature_id;

	$feature_classes = array(
			"landlocked" => array('subject' => $feature_uri, 'predicate_uri' => $rdf_ns."type", 'predicate' => "rdf:type", 'object' => $gadm_ns.'LandlockedCountry', 'datatype' => "anyURI"),
			"islands" => array('subject' => $feature_uri, 'predicate_uri' => $rdf_ns."type", 'predicate' => "rdf:type", 'object' => $gadm_ns.'IslandCountry', 'datatype' => "anyURI"),
			"ldc" => array('subject' => $feature_uri, 'predicate_uri' => $rdf_ns."type", 'predicate' => "rdf:type", 'object' => $gadm_ns.'LeastDevelopedCountry', 'datatype' => "anyURI"),
			"transition" => array('subject' => $feature_uri, 'predicate_uri' => $rdf_ns."type", 'predicate' => "rdf:type", 'object' => $gadm_ns.'TransitionEconomy', 'datatype' => "anyURI")
	);

	$feature_groups = array(
			"cis" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'CIS', 'datatype' => "anyURI"),
			"oecd" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'OECD', 'datatype' => "anyURI"),
			"ceeac" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'CEEAC', 'datatype' => "anyURI"),
			"cemac" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'CEMAC', 'datatype' => "anyURI"),
			"ceplg" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'CEPLG', 'datatype' => "anyURI"),
			"comesa" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'COMESA', 'datatype' => "anyURI"),
			"eac" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'EAC', 'datatype' => "anyURI"),
			"ecowas" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'ECOWAS', 'datatype' => "anyURI"),
			"igad" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'IGAD', 'datatype' => "anyURI"),
			"ioc" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'IOC', 'datatype' => "anyURI"),
			"mru" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'MRU', 'datatype' => "anyURI"),
			"sacu" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'SACU', 'datatype' => "anyURI"),
			"uemoa" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'UEMOA', 'datatype' => "anyURI"),
			"uma" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'UMA', 'datatype' => "anyURI"),
			"palop" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'PALOP', 'datatype' => "anyURI"),
			"parta" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'PARTA', 'datatype' => "anyURI"),
			"cacm" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'CACM', 'datatype' => "anyURI"),
			"eurasec" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'EURASEC', 'datatype' => "anyURI"),
			"agadir" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'AgadirAgreement', 'datatype' => "anyURI"),
			"saarc" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'SAARC', 'datatype' => "anyURI"),
			"asean" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'ASEAN', 'datatype' => "anyURI"),
			"nafta" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'NAFTA', 'datatype' => "anyURI"),
			"gcc" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'GCC', 'datatype' => "anyURI"),
			"csn" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'CSN', 'datatype' => "anyURI"),
			"caricom" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'CARICOM', 'datatype' => "anyURI"),
			"eu" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'EU', 'datatype' => "anyURI"),
			"can" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'CAN', 'datatype' => "anyURI"),
			"acp" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'ACP', 'datatype' => "anyURI"),
			"aosis" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'AOSIS', 'datatype' => "anyURI"),
			"sids" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."member_of", 'predicate' => "gadm:member_of", 'object' => $gadm_igov_ns.'SIDS', 'datatype' => "anyURI")
	);

	$feature_properties = array(
			"gadm_id" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."gadm_id", 'predicate' => "gadm:gadm_id", 'datatype' => "decimal"),
			"gadm_level" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."gadm_level", 'predicate' => "gadm:gadm_level", 'datatype' => "decimal"),
			"name" => array('subject' => $feature_uri, 'predicate_uri' => $rdfs_ns."label", 'predicate' => "rdfs:label", 'datatype' => "XMLLiteral"),
			"name_english" => array('subject' => $feature_uri, 'predicate_uri' => $rdfs_ns."label", 'predicate' => "rdfs:label", 'datatype' => "XMLLiteral", 'lang' => "en"),
			"name_spanish" => array('subject' => $feature_uri, 'predicate_uri' => $rdfs_ns."label", 'predicate' => "rdfs:label", 'datatype' => "XMLLiteral", 'lang' => "es"),
			"name_french" => array('subject' => $feature_uri, 'predicate_uri' => $rdfs_ns."label", 'predicate' => "rdfs:label", 'datatype' => "XMLLiteral", 'lang' => "fr"),
			"name_iso" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."name_iso", 'predicate' => "gadm:name_iso", 'datatype' => "XMLLiteral"),
			"name_fao" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."name_fao", 'predicate' => "gadm:name_fao", 'datatype' => "XMLLiteral"),
			"name_obsolete" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."name_obsolete", 'predicate' => "gadm:name_obsolete", 'datatype' => "XMLLiteral"),
			"nl_name" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."nl_name", 'predicate' => "gadm:nl_name", 'datatype' => "XMLLiteral"),
			"name_variations" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."name_variations", 'predicate' => "gadm:name_variations", 'datatype' => "XMLLiteral"),
			"type" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."type", 'predicate' => "gadm:type", 'datatype' => "XMLLiteral"),
			"type_english" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."type", 'predicate' => "gadm:type", 'datatype' => "XMLLiteral", 'lang' => "en"),
			"iso" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."iso", 'predicate' => "gadm:iso", 'datatype' => "XMLLiteral"),
			"iso2" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."iso2", 'predicate' => "gadm:iso2", 'datatype' => "XMLLiteral"),
			"fips" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."fips", 'predicate' => "gadm:fips", 'datatype' => "XMLLiteral"),
			"ison" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."ison", 'predicate' => "gadm:ison", 'datatype' => "decimal"),
			"www" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."www", 'predicate' => "gadm:www", 'datatype' => "XMLLiteral"),
			"waspartof" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."waspartof", 'predicate' => "gadm:waspartof", 'datatype' => "XMLLiteral"),
			"contains" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."contains", 'predicate' => "gadm:contains", 'datatype' => "XMLLiteral"),
			"sovereign" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."sovereign", 'predicate' => "gadm:sovereign", 'datatype' => "XMLLiteral"),
			"valid_from" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."valid_from", 'predicate' => "gadm:valid_from", 'datatype' => "XMLLiteral"),
			"valid_to" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."valid_to", 'predicate' => "gadm:valid_to", 'datatype' => "XMLLiteral"),
			"andyid" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."andyid", 'predicate' => "gadm:andyid", 'datatype' => "XMLLiteral"),
			"pop2000" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."pop2000", 'predicate' => "gadm:pop2000", 'datatype' => "decimal"),
			"sqkm" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."sqkm", 'predicate' => "gadm:sqkm", 'datatype' => "decimal"),
			"popsqkm" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."popsqkm", 'predicate' => "gadm:popsqkm", 'datatype' => "decimal"),
			"unregion1" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."unregion1", 'predicate' => "gadm:unregion1", 'datatype' => "XMLLiteral"),
			"unregion2" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."unregion2", 'predicate' => "gadm:unregion2", 'datatype' => "XMLLiteral"),
			"wbregion" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."wbregion", 'predicate' => "gadm:wbregion", 'datatype' => "XMLLiteral"),
			"wbincome" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."wbincome", 'predicate' => "gadm:wbincome", 'datatype' => "XMLLiteral"),
			"wbdebt" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."wbdebt", 'predicate' => "gadm:wbdebt", 'datatype' => "XMLLiteral"),
			"wbother" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."wbother", 'predicate' => "gadm:wbother", 'datatype' => "XMLLiteral"),
			"developing" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."developing", 'predicate' => "gadm:developing", 'datatype' => "decimal"),
			"has_code" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."has_code", 'predicate' => "gadm:has_code", 'datatype' => "XMLLiteral"),
			"cc" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."cc", 'predicate' => "gadm:cc", 'datatype' => "XMLLiteral"),
			"name_0" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."in_country", 'predicate' => "gadm:in_country", 'datatype' => "XMLLiteral"),
			"name_1" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."in_region", 'predicate' => "gadm:in_region", 'datatype' => "XMLLiteral"),
			"name_2" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."in_region", 'predicate' => "gadm:in_region", 'datatype' => "XMLLiteral"),
			"name_3" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."in_region", 'predicate' => "gadm:in_region", 'datatype' => "XMLLiteral"),
			"remarks" => array('subject' => $feature_uri, 'predicate_uri' => $gadm_ns."remarks", 'predicate' => "gadm:remarks", 'datatype' => "XMLLiteral")
	);

	$containment_properties = array(
			"id_0" => array('subject' => $feature_uri, 'predicate_uri' => $spatial_ns."PP", 'predicate' => "spatial:PP", 'object' => $gadm_inst_ns.'0/', 'datatype' => "anyURI"),
			"id_1" => array('subject' => $feature_uri, 'predicate_uri' => $spatial_ns."PP", 'predicate' => "spatial:PP", 'object' => $gadm_inst_ns.'1/', 'datatype' => "anyURI"),
			"id_2" => array('subject' => $feature_uri, 'predicate_uri' => $spatial_ns."PP", 'predicate' => "spatial:PP", 'object' => $gadm_inst_ns.'2/', 'datatype' => "anyURI"),
			"id_3" => array('subject' => $feature_uri, 'predicate_uri' => $spatial_ns."PP", 'predicate' => "spatial:PP", 'object' => $gadm_inst_ns.'3/', 'datatype' => "anyURI")
	);

	// URI that defines the resource
	//$feature[] = array('predicate_uri' => $rdf_ns."isDefinedBy", 'predicate' => "rdfs:isDefinedBy", 'object' => $feature_uri, 'datatype' => "anyURI");

	$geometry_id = $feature_id.'/geometry';
	$geometry_uri = $gadm_inst_ns.$geometry_id;

	// Assign Feature Classes
	// TODO: Select classes from rdf_type table
	$feature[] = array('subject' => $feature_uri, 'predicate_uri' => $rdf_ns."type", 'predicate' => "rdf:type", 'object' => $spatial_ns."Feature", 'datatype' => "anyURI");
	$feature[] = array('subject' => $feature_uri, 'predicate_uri' => $rdf_ns."type", 'predicate' => "rdf:type", 'object' => $gadm_ns."AdministrativeRegion", 'datatype' => "anyURI");
	if ($data['gadm_level'] == 0){
		$feature[] = array('subject' => $feature_uri, 'predicate_uri' => $rdf_ns."type", 'predicate' => "rdf:type", 'object' => $gadm_ns."Country", 'datatype' => "anyURI");
		foreach ($feature_classes as $field => $triple){
			if ($data[$field] && $data[$field] != "f"){
				$feature[] = $triple;
			}
		}
	}elseif ($data['gadm_level'] == 1){
		$feature[] = array('subject' => $feature_uri, 'predicate_uri' => $rdf_ns."type", 'predicate' => "rdf:type", 'object' => $gadm_ns."Level1", 'datatype' => "anyURI");
	}elseif ($data['gadm_level'] == 2){
		$feature[] = array('subject' => $feature_uri, 'predicate_uri' => $rdf_ns."type", 'predicate' => "rdf:type", 'object' => $gadm_ns."Level2", 'datatype' => "anyURI");
	}elseif ($data['gadm_level'] == 3){
		$feature[] = array('subject' => $feature_uri, 'predicate_uri' => $rdf_ns."type", 'predicate' => "rdf:type", 'object' => $gadm_ns."Level3", 'datatype' => "anyURI");
	}elseif ($data['gadm_level'] == 4){
		$feature[] = array('subject' => $feature_uri, 'predicate_uri' => $rdf_ns."type", 'predicate' => "rdf:type", 'object' => $gadm_ns."Level4", 'datatype' => "anyURI");
	}

	// Assign the groups the Feature belongs to
	foreach ($feature_groups as $field => $triple){
		if ($data[$field] && $data[$field] != "f"){
			$feature[] = $triple;
		}
	}

	// Assign the regions the Feature belongs to
	foreach ($containment_properties as $field => $triple){
		if ($data[$field] && $data[$field] != ""){
			$triple['object'] .= $data[$field];
			$feature[] = $triple;
		}
	}
	
	//check whether there are sub regions
	if (has_sub($data['gadm_level'], $data['gadm_id'])) {
		$feature[] = array('subject' => $feature_uri, 'predicate_uri' => $spatial_ns."PPi", 'predicate' => "spatial:PPi", 'object' => $feature_uri.'/sub', 'datatype' => "anyURI");
	}
	
	// Assign the Geometry to the Feature
	$feature[] = array('subject' => $feature_uri, 'predicate_uri' => $ngeo_ns."geometry", 'predicate' => "ngeo:geometry", 'object' => $geometry_uri, 'datatype' => "anyURI");
	$feature[] = array('subject' => $feature_uri, 'predicate_uri' => $ngeo_ns."geometry", 'predicate' => "ngeo:geometry", 'object' => $geometry_uri."_100m", 'datatype' => "anyURI");
	$feature[] = array('subject' => $feature_uri, 'predicate_uri' => $ngeo_ns."geometry", 'predicate' => "ngeo:geometry", 'object' => $geometry_uri."_1km", 'datatype' => "anyURI");
	$feature[] = array('subject' => $feature_uri, 'predicate_uri' => $ngeo_ns."geometry", 'predicate' => "ngeo:geometry", 'object' => $geometry_uri."_10km", 'datatype' => "anyURI");
	$feature[] = array('subject' => $feature_uri, 'predicate_uri' => $ngeo_ns."geometry", 'predicate' => "ngeo:geometry", 'object' => $geometry_uri."_100km", 'datatype' => "anyURI");

	// Asign the Feature's value properties
	foreach ($feature_properties as $field => $triple){
		if ($data[$field] && $data[$field] != ""){
			$multiple_values = explode("|", $data[$field]);
			foreach ($multiple_values as $value){
				$triple['object'] = $value;
				$feature[] = $triple;
			}
		}
	}

	// Assign the equivalent Features
	foreach ($sameas_uris as $uri){
		if ($uri != ""){
			$feature[] = array('subject' => $feature_uri, 'predicate_uri' => $owl_ns."sameAs", 'predicate' => "owl:sameAs", 'object' => $uri, 'datatype' => "anyURI");
		}
	}

	// Assign the spatially co-located Features
	foreach ($eq_uris as $uri){
		if ($uri != ""){
			$feature[] = array('subject' => $feature_uri, 'predicate_uri' => $spatial_ns."EQ", 'predicate' => "spatial:EQ", 'object' => $uri, 'datatype' => "anyURI");
		}
	}

	// Assign the properly contained Features
	foreach ($ppi_uris as $uri){
		if ($uri != ""){
			$feature[] = array('subject' => $feature_uri, 'predicate_uri' => $spatial_ns."PPi", 'predicate' => "spatial:PPi", 'object' => $uri, 'datatype' => "anyURI");
		}
	}

	$description = array();

	$description['properties'] = $feature;
	$description['id'] = $feature_id;
	$description['simplified_geometry_uri'] = $feature_uri."/simplified_geometry";
	$description['uri'] = $feature_uri;
	$multiple_names = explode("|", $data['name']);
	$description['name'] = $multiple_names[0];
	$description['formats'] = array('ttl' => "Turtle", 'rdf' => "RDF/XML");

	return $description;
}

function has_sub($level, $id) {
    if ($level < 4) {
      $sql = "SELECT g.gadm_level FROM gadm_regions g WHERE g.id_".$level." = ".$id." AND g.gadm_level = ".($level+1)." LIMIT 1";
      $result = make_query($sql);
      if (pg_num_rows($result) == 1) {
	  return true;
      } else {
	  return false;
      }
   }   
}

function prepare_geometry($data, $resolution = 0){

	$rdf_ns = "http://www.w3.org/1999/02/22-rdf-syntax-ns#";
	$ngeo_ns = "http://geovocab.org/geometry#";
	$gadm_ns = BASE_URL."/ontology#";
	$gadm_inst_ns = BASE_URL."/id/";
	$gadm_igov_ns = BASE_URL."/igov#";
	$ogc_ns = "http://www.opengis.net/rdf#";
	$dct_ns = "http://purl.org/dc/terms/";
	$dctype_ns = "http://purl.org/dc/dcmitype/";
	$foaf_ns = "http://xmlns.com/foaf/0.1/";

	$geometry_properties = array(
	//	"geometry" => array('predicate_uri' => $ogc_ns."asWKT", 'predicate' => "ogc:asWKT", 'datatype' => $ogc_ns."WKTLiteral")
	);

	$feature_id = $data['gadm_level'].'/'.$data['gadm_id'];
	$feature_uri = $gadm_inst_ns.$feature_id;

	$geometry = array();
	$description = array();

	switch($resolution){
		case 0.1:
			$geometry_id = $feature_id.'/geometry_100m';
			break;
		case 1:
			$geometry_id = $feature_id.'/geometry_1km';
			break;
		case 10:
			$geometry_id = $feature_id.'/geometry_10km';
			break;
		case 100:
			$geometry_id = $feature_id.'/geometry_100km';
			break;
		default:
			$geometry_id = $feature_id.'/geometry';
			break;
	}

	$geometry_uri = $gadm_inst_ns.$geometry_id;

	if ($geometry_id == $feature_id.'/geometry'){
		$description['simplified_geometry_uri'] = $feature_uri."/simplified_geometry";
	}else{
		$description['simplified_geometry_uri'] = $gadm_inst_ns.$geometry_id;
		$geometry[] = array('subject' => $geometry_uri, 'predicate_uri' => $ngeo_ns."resolution", 'predicate' => "ngeo:resolution", 'object' => $resolution*1000, 'datatype' => "decimal");
	}

	// Assign the Geometry's properties
	$geometry[] = array('subject' => $geometry_uri, 'predicate_uri' => $rdf_ns."type", 'predicate' => "rdf:type", 'object' => $ngeo_ns."Geometry", 'datatype' => "anyURI");
	//$geometry[] = array('predicate_uri' => $rdf_ns."isDefinedBy", 'predicate' => "rdfs:isDefinedBy", 'object' => $geometry_uri, 'datatype' => "anyURI");
	$geometry[] = array('subject' => $geometry_uri, 'predicate_uri' => $ngeo_ns."geometryOf", 'predicate' => "ngeo:geometryOf", 'object' => $feature_uri, 'datatype' => "anyURI");

	//    $geometry[] = array('predicate_uri' => $rdf_ns."type", 'predicate' => "rdf:type", 'object' => $ogc_ns."Geometry", 'datatype' => "anyURI");
	foreach ($geometry_properties as $field => $triple){
		if ($data[$field] && $data[$field] != ""){
			$triple['object'] = $data[$field];
			$geometry[] = $triple;
		}
	}

	// RDF
	$bnode = array();
	$bnode[] = array('subject' => $geometry_uri.".rdf", 'predicate_uri' => $rdf_ns."type", 'predicate' => "rdf:type", 'object' => $dctype_ns."Image", 'datatype' => "anyURI");
	$bnode[] = array('subject' => "_".$geometry_id."_rdf", 'bnode' => true, 'predicate_uri' => $dct_ns."isFormatOf", 'predicate' => "dct:isFormatOf", 'object' => $geometry_uri, 'datatype' => "anyURI");
	$bnode[] = array('subject' => "_".$geometry_id."_rdf", 'bnode' => true, 'predicate_uri' => $dct_ns."format", 'predicate' => "dct:format", 'object' => "application/rdf+xml", 'datatype' => "XMLLiteral");
	$bnode[] = array('subject' => "_".$geometry_id."_rdf", 'bnode' => true, 'predicate_uri' => $foaf_ns."primaryTopic", 'predicate' => "foaf:primaryTopic", 'object' => $feature_uri, 'datatype' => "anyURI");
	$geometry[] = array('subject' => $geometry_uri, 'predicate_uri' => $dct_ns."hasFormat", 'predicate' => "dct:hasFormat", 'object' => $bnode, 'datatype' => "bnode");

	// HTML
	$bnode = array();
	$bnode[] = array('subject' => $geometry_uri.".html", 'predicate_uri' => $rdf_ns."type", 'predicate' => "rdf:type", 'object' => $dctype_ns."Image", 'datatype' => "anyURI");
	$bnode[] = array('subject' => "_".$geometry_id."_html", 'bnode' => true, 'predicate_uri' => $dct_ns."isFormatOf", 'predicate' => "dct:isFormatOf", 'object' => $geometry_uri, 'datatype' => "anyURI");
	$bnode[] = array('subject' => "_".$geometry_id."_html", 'bnode' => true, 'predicate_uri' => $dct_ns."format", 'predicate' => "dct:format", 'object' => "text/html", 'datatype' => "XMLLiteral");
	$bnode[] = array('subject' => "_".$geometry_id."_html", 'bnode' => true, 'predicate_uri' => $foaf_ns."primaryTopic", 'predicate' => "foaf:primaryTopic", 'object' => $feature_uri, 'datatype' => "anyURI");
	$geometry[] = array('subject' => $geometry_uri, 'predicate_uri' => $dct_ns."hasFormat", 'predicate' => "dct:hasFormat", 'object' => $bnode, 'datatype' => "bnode");

	// Turtle
	$bnode = array();
	$bnode[] = array('subject' => $geometry_uri.".ttl", 'predicate_uri' => $rdf_ns."type", 'predicate' => "rdf:type", 'object' => $dctype_ns."Image", 'datatype' => "anyURI");
	$bnode[] = array('subject' => "_".$geometry_id."_ttl", 'bnode' => true, 'predicate_uri' => $dct_ns."isFormatOf", 'predicate' => "dct:isFormatOf", 'object' => $geometry_uri, 'datatype' => "anyURI");
	$bnode[] = array('subject' => "_".$geometry_id."_ttl", 'bnode' => true, 'predicate_uri' => $dct_ns."format", 'predicate' => "dct:format", 'object' => "text/turtle", 'datatype' => "XMLLiteral");
	$bnode[] = array('subject' => "_".$geometry_id."_ttl", 'bnode' => true, 'predicate_uri' => $foaf_ns."primaryTopic", 'predicate' => "foaf:primaryTopic", 'object' => $feature_uri, 'datatype' => "anyURI");
	$geometry[] = array('subject' => $geometry_uri, 'predicate_uri' => $dct_ns."hasFormat", 'predicate' => "dct:hasFormat", 'object' => $bnode, 'datatype' => "bnode");

	// GeoJson
	$bnode = array();
	$bnode[] = array('subject' => $geometry_uri.".json", 'predicate_uri' => $rdf_ns."type", 'predicate' => "rdf:type", 'object' => $dctype_ns."Image", 'datatype' => "anyURI");
	$bnode[] = array('subject' => "_".$geometry_id."_json", 'bnode' => true, 'predicate_uri' => $dct_ns."isFormatOf", 'predicate' => "dct:isFormatOf", 'object' => $geometry_uri, 'datatype' => "anyURI");
	$bnode[] = array('subject' => "_".$geometry_id."_json", 'bnode' => true, 'predicate_uri' => $dct_ns."format", 'predicate' => "dct:format", 'object' => "application/json", 'datatype' => "XMLLiteral");
	$bnode[] = array('subject' => "_".$geometry_id."_json", 'bnode' => true, 'predicate_uri' => $foaf_ns."primaryTopic", 'predicate' => "foaf:primaryTopic", 'object' => $feature_uri, 'datatype' => "anyURI");
	$geometry[] = array('subject' => $geometry_uri, 'predicate_uri' => $dct_ns."hasFormat", 'predicate' => "dct:hasFormat", 'object' => $bnode, 'datatype' => "bnode");

	// GML
	$bnode = array();
	$bnode[] = array('subject' => $geometry_uri.".gml", 'predicate_uri' => $rdf_ns."type", 'predicate' => "rdf:type", 'object' => $dctype_ns."Image", 'datatype' => "anyURI");
	$bnode[] = array('subject' => "_".$geometry_id."_gml", 'bnode' => true, 'predicate_uri' => $dct_ns."isFormatOf", 'predicate' => "dct:isFormatOf", 'object' => $geometry_uri, 'datatype' => "anyURI");
	$bnode[] = array('subject' => "_".$geometry_id."_gml", 'bnode' => true, 'predicate_uri' => $dct_ns."format", 'predicate' => "dct:format", 'object' => "application/vnd.ogc.gml", 'datatype' => "XMLLiteral");
	$bnode[] = array('subject' => "_".$geometry_id."_gml", 'bnode' => true, 'predicate_uri' => $foaf_ns."primaryTopic", 'predicate' => "foaf:primaryTopic", 'object' => $feature_uri, 'datatype' => "anyURI");
	$geometry[] = array('subject' => $geometry_uri, 'predicate_uri' => $dct_ns."hasFormat", 'predicate' => "dct:hasFormat", 'object' => $bnode, 'datatype' => "bnode");

	// KML
	$bnode = array();
	$bnode[] = array('subject' => $geometry_uri.".kml", 'predicate_uri' => $rdf_ns."type", 'predicate' => "rdf:type", 'object' => $dctype_ns."Image", 'datatype' => "anyURI");
	$bnode[] = array('subject' => "_".$geometry_id."_kml", 'bnode' => true, 'predicate_uri' => $dct_ns."isFormatOf", 'predicate' => "dct:isFormatOf", 'object' => $geometry_uri, 'datatype' => "anyURI");
	$bnode[] = array('subject' => "_".$geometry_id."_kml", 'bnode' => true, 'predicate_uri' => $dct_ns."format", 'predicate' => "dct:format", 'object' => "application/vnd.google-earth.kml+xml", 'datatype' => "XMLLiteral");
	$bnode[] = array('subject' => "_".$geometry_id."_kml", 'bnode' => true, 'predicate_uri' => $foaf_ns."primaryTopic", 'predicate' => "foaf:primaryTopic", 'object' => $feature_uri, 'datatype' => "anyURI");
	$geometry[] = array('subject' => $geometry_uri, 'predicate_uri' => $dct_ns."hasFormat", 'predicate' => "dct:hasFormat", 'object' => $bnode, 'datatype' => "bnode");

	// SVG
	$bnode = array();
	$bnode[] = array('subject' => $geometry_uri.".svg", 'predicate_uri' => $rdf_ns."type", 'predicate' => "rdf:type", 'object' => $dctype_ns."Image", 'datatype' => "anyURI");
	$bnode[] = array('subject' => "_".$geometry_id."_svg", 'bnode' => true, 'predicate_uri' => $dct_ns."isFormatOf", 'predicate' => "dct:isFormatOf", 'object' => $geometry_uri, 'datatype' => "anyURI");
	$bnode[] = array('subject' => "_".$geometry_id."_svg", 'bnode' => true, 'predicate_uri' => $dct_ns."format", 'predicate' => "dct:format", 'object' => "image/svg+xml", 'datatype' => "XMLLiteral");
	$bnode[] = array('subject' => "_".$geometry_id."_svg", 'bnode' => true, 'predicate_uri' => $foaf_ns."primaryTopic", 'predicate' => "foaf:primaryTopic", 'object' => $feature_uri, 'datatype' => "anyURI");
	$geometry[] = array('subject' => $geometry_uri, 'predicate_uri' => $dct_ns."hasFormat", 'predicate' => "dct:hasFormat", 'object' => $bnode, 'datatype' => "bnode");

	$description['properties'] = $geometry;
	$description['id'] = $geometry_id;
	$description['uri'] = $geometry_uri;
	$description['name'] = $data['name']." (geometry)";
	$description['formats'] = array('ttl' => "Turtle", 'rdf' => "RDF/XML", 'gml' => "GML", 'kml' => "KML", 'kmz' => "KMZ", 'geojson' => "GeoJSON", 'svg' => "SVG");

	return $description;
}

function show_region($result, $content_type, $show_feature = false, $show_geometry = false, $resolution = 0){
	$pg_array = pg_fetch_array($result, null, PGSQL_ASSOC);
	$sameas_uris = array_unique(pg_fetch_all_columns($result, pg_field_num($result, "sameas_uri")));
	$eq_uris  = array_unique(pg_fetch_all_columns($result, pg_field_num($result, "eq_uri")));
	$ppi_uris = array_unique(pg_fetch_all_columns($result, pg_field_num($result, "ppi_uri")));
	$data = array();

	switch ($content_type) {
		case 'rdf':
			if ($show_feature) $data['feature'] = prepare_feature($pg_array, $sameas_uris, $eq_uris, $ppi_uris);
			if ($show_geometry) $data['geometry'] = prepare_geometry($pg_array, $resolution);
			break;
		case 'html':
			if ($show_feature) $data = prepare_feature($pg_array, $sameas_uris, $eq_uris, $ppi_uris);
			elseif ($show_geometry) $data = prepare_geometry($pg_array, $resolution);
			break;
		case 'ttl':
			if ($show_feature) $data['feature'] = prepare_feature($pg_array, $sameas_uris, $eq_uris, $ppi_uris);
			if ($show_geometry) $data['geometry'] = prepare_geometry($pg_array, $resolution);
			break;
		case 'gml':
			$data = $pg_array;
			break;
		case 'svg':
			$data = $pg_array;
			break;
		case 'kml':
			$data = prepare_feature($pg_array, $rdf_type, $rdfs_label, $owl_sameas, $spatial_pp);
			$data['geometry'] = $pg_array['geometry'];
			break;
		case 'kmz':
			$data = prepare_feature($pg_array, $rdf_type, $rdfs_label, $owl_sameas, $spatial_pp);
			$data['geometry'] = $pg_array['geometry'];
			$data['filename'] = $data['gadm_level']."_".$data['gadm_id']."_geometry.kml";
			break;
		case 'geojson':
			$data = $pg_array;
			break;
	}

	show_template($data, $content_type);
}

function show_sub($element, $subs, $content_type, $show_geometry = true){
    $rdf_ns = "http://www.w3.org/1999/02/22-rdf-syntax-ns#";
    $rdfs_ns = "http://www.w3.org/2000/01/rdf-schema#";
    $gadm_inst_ns = BASE_URL."/id/";
    $feature = $element;
    $feature_uri = $gadm_inst_ns.$feature['gadm_level'].'/'.$feature['gadm_id'].'/sub';
    $spatial_ns = "http://geovocab.org/spatial#";
    $feature['name'] = $feature['name']." (sub)";
    $feature['uri'] = $feature_uri;
    $feature['properties'] = array();
    $feature['formats'] = array('ttl' => "Turtle", 'rdf' => "RDF/XML", 'kml' => "KML");
    $feature['simplified_geometry_uri'] = $feature_uri;

    if ($show_geometry)
    {
        while ( $row = pg_fetch_array($subs) )
        {
            $feature['properties'][] =array('name' => $row['name'], 'subject' => $feature_uri,'predicate_uri' => $spatial_ns."PP", 'predicate' => "spatial:PPi", 'uri' => $gadm_inst_ns.$row['gadm_level'].'/'.$row['gadm_id'], 'object' => $gadm_inst_ns.$row['gadm_level'].'/'.$row['gadm_id'], 'datatype' => "anyURI", 'geometry' => $row['geometry']);
    	}
    } else {
    	while ( $row = pg_fetch_array($subs) )
    	{
            $resource = array();
            $uri = $gadm_inst_ns.$row['gadm_level'].'/'.$row['gadm_id'];
            $resource['uri'] = $uri;
            $resource['datatype'] = 'bnode';
            $resource['predicate'] = 'spatial:PPi';
            $resource['predicate_uri'] = $spatial_ns."PP";
            $resource['subject'] = $feature['uri'];
            $resource['object'][] = array('subject' => $feature['uri'], 'predicate_uri' => $rdfs_ns."label", 'predicate' => "rdfs:label", 'object' => $row['name'], 'datatype' => "XMLLiteral");
            if ($row['name_iso'] != '')
            {
                $resource['object'][] = array('subject' => $uri, 'predicate_uri' => $gadm_ns."name_iso", 'predicate' => "gadm:name_iso", 'object' => $row['name_iso'], 'datatype' => "XMLLiteral");
            }
            if ($row['name_english'] != '')
            {
                $resource['object'][] = array('subject' => $uri, 'predicate_uri' => $rdfs_ns."label", 'predicate' => "rdfs:label", 'object' => $row['name_english'], 'datatype' => "XMLLiteral", 'lang' => "en");
            }
            $resource['object'][] = array('subject' => $feature_uri,'predicate_uri' => $rdfs_ns."isDefinedBy", 'predicate' => "rdfs:isDefinedBy", 'uri' => $gadm_inst_ns.$row['gadm_level'].'/'.$row['gadm_id'], 'object' => $gadm_inst_ns.$row['gadm_level'].'/'.$row['gadm_id'], 'datatype' => "anyURI");
            $feature['properties'][] = $resource;
    	}
    }
    
    switch ($content_type) {
    	case 'rdf':
    		$data['feature'] = $feature;
    		//if ($show_geometry) $data['geometry'] = prepare_geometry($pg_array, $resolution);
    		break;
    	case 'html':
    		$data = $feature;
    		//elseif ($show_geometry) $data = prepare_geometry($pg_array, $resolution);
    		break;
    	case 'ttl':
    		$data['feature'] = $feature;
    		//if ($show_geometry) $data['geometry'] = prepare_geometry($pg_array, $resolution);
    		break;
    	case 'kml':
    		$data = $feature;
    		break;
    }
	show_template($data, $content_type);
}

function show_level($basic, $result, $content_type, $show_geometry = true, $limit = null, $has_next_page = false){
	$rdf_ns = "http://www.w3.org/1999/02/22-rdf-syntax-ns#";
	$rdfs_ns = "http://www.w3.org/2000/01/rdf-schema#";
	$spatial_ns = "http://geovocab.org/spatial#";
	$ngeo_ns = "http://geovocab.org/geometry";
	$gadm_ns = BASE_URL."/ontology#";
	$gadm_inst_ns = BASE_URL."/id/";
	$gadm_igov_ns = BASE_URL."/igov#";
	$owl_ns = "http://www.w3.org/2002/07/owl#";
	$feature = array();
	$feature_uri = $gadm_inst_ns.$basic['gadm_level'];
	$feature['predicate'] = 'rdf:Bag';
	$feature['name'] = "Level ".$basic['gadm_level'];
	$feature['subject'] = $feature_uri;
	$feature['uri'] = $feature_uri;
	$feature['properties'] = array();
	$feature['formats'] = array('ttl' => "Turtle", 'rdf' => "RDF/XML", 'kml' => "KML");
	$feature['simplified_geometry_uri'] = $feature_uri;

	if ($show_geometry) {
		while ( $row = pg_fetch_array($result) ) {
                        $resource['properties'][] = array('subject' => $feature['uri'], 'predicate_uri' => $rdfs_ns."label", 'predicate' => "rdfs:label", 'object' => $row['name'], 'datatype' => "XMLLiteral");
                        if ($row['name_iso'] != '') {
                            $resource['properties'][] = array('subject' => $feature['uri'], 'predicate_uri' => $gadm_ns."name_iso", 'predicate' => "gadm:name_iso", 'object' => $row['name_iso'], 'datatype' => "XMLLiteral");
                        }
                        if ($row['name_english'] != '') {
                            $resource['properties'][] = array('subject' => $feature['uri'], 'predicate_uri' => $rdfs_ns."label", 'predicate' => "rdfs:label", 'object' => $row['name_english'], 'datatype' => "XMLLiteral", 'lang' => "en");
                        }
		
			$feature['properties'][] = array('name' => $row['name'], 'subject' => $feature_uri,'predicate_uri' => $rdfs_ns."seeAlso", 'predicate' => "rdfs:seeAlso", 'uri' => $gadm_inst_ns.$row['gadm_level'].'/'.$row['gadm_id'], 'object' => $gadm_inst_ns.$row['gadm_level'].'/'.$row['gadm_id'], 'datatype' => "anyURI", 'geometry' => $row['geometry']);
		}
	} else {
		while ( $row = pg_fetch_array($result) ) {
                        $resource = array();
                        $uri = $gadm_inst_ns.$row['gadm_level'].'/'.$row['gadm_id'];
                        $resource['uri'] = $uri;
                        $resource['datatype'] = 'bnode';
                        $resource['predicate'] = 'rdf:li';
                        $resource['subject'] = $feature['uri'];
		        $resource['object'][] = array('subject' => $feature['uri'], 'predicate_uri' => $rdfs_ns."label", 'predicate' => "rdfs:label", 'object' => $row['name'], 'datatype' => "XMLLiteral");
		        if ($row['name_iso'] != '') {
                            $resource['object'][] = array('subject' => $uri, 'predicate_uri' => $gadm_ns."name_iso", 'predicate' => "gadm:name_iso", 'object' => $row['name_iso'], 'datatype' => "XMLLiteral");
                        }
                        if ($row['name_english'] != '') {
                            $resource['object'][] = array('subject' => $uri, 'predicate_uri' => $rdfs_ns."label", 'predicate' => "rdfs:label", 'object' => $row['name_english'], 'datatype' => "XMLLiteral", 'lang' => "en");
                        }
		
			$resource['object'][] = array('subject' => $feature_uri,'predicate_uri' => $rdfs_ns."isDefinedBy", 'predicate' => "rdfs:isDefinedBy", 'uri' => $gadm_inst_ns.$row['gadm_level'].'/'.$row['gadm_id'], 'object' => $gadm_inst_ns.$row['gadm_level'].'/'.$row['gadm_id'], 'datatype' => "anyURI");
			$feature['properties'][] = $resource;
		}
	}
	
	if ($limit != null && $has_next_page) {
	  $feature['properties'][] = nextPage($feature_uri, $limit);
	}

	switch ($content_type) {
		case 'rdf':
			$data['feature'] = $feature;
			if ($show_geometry) $data['geometry'] = prepare_geometry($pg_array, $resolution);
			break;
		case 'html':
			$data = $feature;
			if ($show_geometry) $data = prepare_geometry($pg_array, $resolution);
			break;
		case 'ttl':
			$data['feature'] = $feature;
			if ($show_geometry) $data['geometry'] = prepare_geometry($pg_array, $resolution);
			break;
		case 'kml':
			$data = $feature;
			break;
	}
	show_template($data, $content_type, $show_geometry);
}

function show_template($data, $content_type, $show_geometry = true){
	switch ($content_type) {
		case 'rdf':
			include(__DIR__.'/templates/rdf-xml.php');
			break;
		case 'html':
			if ($show_geometry) {
			    include(__DIR__.'/templates/html.php');
			} else {
			    include(__DIR__.'/templates/html_no_geo.php');
			}
			break;
		case 'ttl':
			include(__DIR__.'/templates/turtle.php');
			break;
		case 'gml':
			include(__DIR__.'/templates/gml.php');
			break;
		case 'svg':
			include(__DIR__.'/templates/svg.php');
			break;
		case 'kml':
			include(__DIR__.'/templates/kml.php');
			break;
		case 'kmz':
			include(__DIR__.'/templates/kmz.php');
			break;
		case 'geojson':
			include(__DIR__.'/templates/geojson.php');
			break;
		default:
			include(__DIR__.'/templates/rdf-xml.php');
	}
}

function nextPage($uri, $old_limit) {
	 $resource = array();
	 $resource['uri'] = $uri;
	 $resource['subject'] = $uri;
	 $resource['datatype'] = 'anyURI';
         $resource['predicate'] = 'rdf:seeAlso';
         $resource['object'] = $uri.'?limit='.$old_limit[1].','.(2*$old_limit[1]-$old_limit[0]);
         return $resource;
}

function make_query($sql){
	// Connect to PostgreSQL
	$db = pg_connect("host=".DB_SERVER." port=".DB_PORT." dbname=".DB_DATABASE." user=".DB_USERNAME." password=".DB_PASSWORD);

	if (!$db)
	{
		header('HTTP/1.1 500 Internal Server Error');
		header('Content-type: text/html');
		die('Failed to connect to PostgreSQL. Error: ' . pg_last_error());
	}

	$result = pg_query($db, $sql);

	if (!$result)
	{
		header('HTTP/1.1 500 Internal Server Error');
		header('Content-type: text/html');
		die('Failed to query PostgreSQL. Error: ' . pg_last_error());
	}

	// Close the connection
	pg_close($db);

	return $result;
}

function render($template, $param){
	ob_start();
	extract($param);
	include("templates/".$template);
}

function asciiEncode($str){
	return str_replace("\\/","/",json_encode(utf8_encode($str)));
}


/**
 * @name                    : makeImageF
 *
 * Function for create image from text with selected font. Justify text in image (0-Left, 1-Right, 2-Center).
 *
 * @param String $text     : String to convert into the Image.
 * @param String $font     : Font name of the text. Kip font file in same folder.
 * @param int    $Justify  : Justify text in image (0-Left, 1-Right, 2-Center).
 * @param int    $Leading  : Space between lines.
 * @param int    $W        : Width of the Image.
 * @param int    $H        : Hight of the Image.
 * @param int    $X        : x-coordinate of the text into the image.
 * @param int    $Y        : y-coordinate of the text into the image.
 * @param int    $fsize    : Font size of text.
 * @param array  $color    : RGB color array for text color.
 * @param array  $bgcolor  : RGB color array for background.
 *
 */
function imagettfJustifytext($text, $font="CENTURY.TTF", $Justify=2, $Leading=0, $W=0, $H=0, $X=0, $Y=0, $fsize=12, $color=array(0x0,0x0,0x0), $bgcolor=array(0xFF,0xFF,0xFF)){

	$angle = 0;
	$_bx = imageTTFBbox($fsize,0,$font,$text);
	$s = split("[\n]+", $text);  // Array of lines
	$nL = count($s);  // Number of lines
	$W = ($W==0)?abs($_bx[2]-$_bx[0]):$W;    // If Width not initialized by programmer then it will detect and assign perfect width.
	$H = ($H==0)?abs($_bx[5]-$_bx[3])+($nL>1?($nL*$Leading):0):$H;    // If Height not initialized by programmer then it will detect and assign perfect height.

	$im = @imagecreate($W, $H)
	or die("Cannot Initialize new GD image stream");

	$background_color = imagecolorallocate($im, $bgcolor[0], $bgcolor[1], $bgcolor[2]);  // RGB color background.
	$text_color = imagecolorallocate($im, $color[0], $color[1], $color[2]); // RGB color text.

	if ($Justify == 0){ //Justify Left
		imagettftext($im, $fsize, $angle, $X, $fsize, $text_color, $font, $text);
	} else {
		// Create alpha-nummeric string with all international characters - both upper- and lowercase
		$alpha = range("a", "z");
		$alpha = $alpha.strtoupper($alpha).range(0, 9);
		// Use the string to determine the height of a line
		$_b = imageTTFBbox($fsize,0,$font,$alpha);
		$_H = abs($_b[5]-$_b[3]);
		$__H=0;
		for ($i=0; $i<$nL; $i++) {
			$_b = imageTTFBbox($fsize,0,$font,$s[$i]);
			$_W = abs($_b[2]-$_b[0]);
			//Defining the X coordinate.
			if ($Justify == 1) $_X = $W-$_W;  // Justify Right
			else $_X = abs($W/2)-abs($_W/2);  // Justify Center

			//Defining the Y coordinate.
			$__H += $_H;
			imagettftext($im, $fsize, $angle, $_X, $__H, $text_color, $font, $s[$i]);
			$__H += $Leading;
		}
	}
	return $im;
}

/**
 * Converts a neogeo spatial relation to its functional postgis equivalent 
 */
function neogeoToPostgis($relation, $A, $B) {
	switch($relation) {
		case "C": return "ST_Intersects(".$A.",".$B.")";
		case "DC": return "NOT ST_Intersects(".$A.",".$B.")";
		case "DR": return "NOT ST_Equals(".$A.",".$B.")";
		case "EC": return "ST_Touches(".$A.",".$B.")";
		case "EQ": return "ST_Equals(".$A.",".$B.")";
		case "NTPP": return "ST_ContainsProperly(".$B.",".$A.")";
		case "NTPPi": return "ST_ContainsProperly(".$A.",".$B.")";
		case "O": return "ST_Intersects(".$A.",".$B.") AND NOT ST_Touches(".$A.",".$B.")";
		case "P": return "ST_Contains(".$B.",".$A.")";
		case "PO": return "ST_Overlaps(".$A.",".$B.")";
		case "PP": return "ST_Contains(".$B.",".$A.") AND NOT ST_Equals(".$A.",".$B.")";
		case "PPi": return "ST_Contains(".$A.",".$B.") AND NOT ST_Equals(".$A.",".$B.")";
		case "Pi": return "ST_Contains(".$A.",".$B.")";
		case "TPP": return "ST_Contains(".$B.",".$A.") AND NOT ST_Equals(".$A.",".$B.") AND NOT ST_ContainsProperly(".$B.",".$A.")";
		case "TPPi": return "ST_Contains(".$A.",".$B.") AND NOT ST_Equals(".$A.",".$B.") AND NOT ST_ContainsProperly(".$A.",".$B.")";
		default: return null;
	}
}

/**
 * Convert a bounding box get parameter to a postgis Well-Known Text polygon
 *
 * @param $bboxString the bbox string as delivered as a get parameter
 * @return the bounding box as a postgis Well-Known Text Polygon String
 */
function boundingBoxWktParser($bboxString) {
    $bbox = explode(',', $bboxString);
    
    if (count($bbox) != 4)
    {
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-type: text/html');
        die("Malformed bounding box. A bounding box is defined by 4 values.");
    }
    
    $longA = stripslashes($bbox[0]);
    $latA = stripslashes($bbox[1]);
    $longB = stripslashes($bbox[2]);
    $latB = stripslashes($bbox[3]);
    
    if (!is_numeric($longA) || !is_numeric($latA) || !is_numeric($longB) || !is_numeric($latB))
    {
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-type: text/html');
        die("The latitude and longitude parameters must be numeric values.");
    }
    
    return "POLYGON((".$longA." ".$latA.",".$longB." ".$latA.",".$longB." ".$latB.",".$longA." ".$latB.",".$longA." ".$latA."))";
}

/**
 * Convert a point get parameter to a postgis Well-Known Text point
 *
 * @param $pointString the point string as delivered as a get parameter
 * @return the point as a postgis Well-Known Text Polygon String
 */
function pointWktParser($pointString) {
    $point = explode(',', $pointString);
    
    if (count($point) != 2)
    {
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-type: text/html');
        die("Malformed point. A point is defined by 2 values.");
    }
    
    $long = stripslashes($point[0]);
    $lat = stripslashes($point[1]);
    
    if (!is_numeric($long) || !is_numeric($lat))
    {
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-type: text/html');
        die("The latitude and longitude parameters must be numeric values.");
    }
    
    return "POINT(".$long." ".$lat.")";
}

/**
 * Convert a polygon get parameter to a postgis Well-Known Text polygon
 *
 * @param $polygonString the polygon string as delivered as a get parameter
 * @return the polygon as a postgis Well-Known Text Polygon String
 */
function polygonWktParser($polygonString) {
    $polygon = explode(',', $polygonString);
    
    if (count($polygon) < 8 || (count($polygon) % 2) == 1)    
    {
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-type: text/html');
        die("Malformed polygon. A polygon is defined by an even number >= 8 of parameters.");
    }
    
    //for postgis the last point of a polygon has to be identical to the first point
    if ($polygon[stripslashes(count($polygon)) - 2] != stripslashes($polygon[0]) || $polygon[stripslashes(count($polygon)) - 1] != stripslashes($polygon[1]))
    {
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-type: text/html');
        die("Malformed polygon. First and last point have to be identical.");
    }
    
    $return = "POLYGON((";
    
    $pointStripped = 0.0;
    for ($i = 0; $i < count($polygon); $i++)
    {
        $pointStripped = stripslashes($polygon[$i]);
        if (!is_numeric($pointStripped))
        {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-type: text/html');
            die("The parameters must be numeric values.");
        }
        $return .= $pointStripped;
        if ($i % 2 == 1 && $i < (count($polygon) - 1))
        {
            $return .= ",";
        } elseif ($i % 2 == 0) {
            $return .= " ";
        }
    }
    $return .= "))";
    return $return;
}
