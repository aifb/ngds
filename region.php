<?php
include('gadm.php');

// Parameter validation
$params = validate_request();

if ($params['geometry']) {
	$geometry = ','.get_geometry($params['content_type'], $params['simplify'], false, $params['resolution']);
} else {
	$geometry = '';
}	

// Build the SQL query
$sql = "SELECT g.*, s.uri as sameas_uri, e.uri as eq_uri, p.uri as ppi_uri ".$geometry."
		FROM gadm_regions g
		LEFT JOIN spatial_eq e  ON (g.gadm_level = e.gadm_level AND g.gadm_id = e.gadm_id)
		LEFT JOIN spatial_ppi p ON (g.gadm_level = p.gadm_level AND g.gadm_id = p.gadm_id)
		LEFT JOIN owl_sameas s ON (g.gadm_level = s.gadm_level AND g.gadm_id = s.gadm_id)
		WHERE g.gadm_level = ".$params['gadm_level']." AND g.gadm_id = ".$params['gadm_id'];

// Execute the query and retrieve the results. This variable is used by the templates.
$result = make_query($sql);

if (pg_num_rows($result) == 0)
{
	header("HTTP/1.0 404 Not Found");
	header('Content-type: text/html');
	die();
}

// Display the results in the right format
show_region($result, $params['content_type'], $params['feature'], $params['geometry'], $params['resolution']);

