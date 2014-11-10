<?php
include('gadm.php');

// Parameter validation
$params = validate_request();

//get the name of the region for which to find the subregions
$sql = "SELECT g.gadm_level, g.gadm_id, g.name FROM gadm_regions g WHERE g.gadm_level = ".$params['gadm_level']." AND g.gadm_id = ".$params['gadm_id'];

$name = make_query($sql);

if (pg_num_rows($name) == 0)
{
	header("HTTP/1.0 404 Not Found");
	header('Content-type: text/html');
	die();
} else {
	$name = pg_fetch_array($name);
}

if ($params['geometry']) {
	$geometry = ','.get_geometry($params['content_type'], $params['simplify'], false, $params['resolution']);
} else {
	$geometry = '';
}

$sql = "SELECT g.gadm_level, g.gadm_id, g.name, g.name_english, g.name_iso ".$geometry." FROM gadm_regions g
		WHERE g.id_".$params['gadm_level']." = ".$params['gadm_id']." 
		AND g.gadm_level = ".($params['gadm_level']+1)." ORDER BY g.name";

// Execute the query and retrieve the results. This variable is used by the templates.
$result = make_query($sql);

if (pg_num_rows($result) == 0)
{
	header("HTTP/1.0 404 Not Found");
	header('Content-type: text/html');
	die();
}

// Display the results in the right format
show_sub($name, $result, $params['content_type'], $params['geometry']);