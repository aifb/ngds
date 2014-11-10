<?php
include('gadm.php');

// Parameter validation
$params = validate_request_level();
if ($params['geometry']) {
	$geometry = ','.get_geometry($params['content_type'], $params['simplify'], false, $params['resolution']);
} else {
	$geometry = '';
}

if ($params['limit']!=null) {
    $limit = "LIMIT ".abs($params['limit'][1] - $params['limit'][0])." OFFSET ".$params['limit'][0];
} else {
    $limit = '';
}
$sql = "SELECT g.gadm_level, g.gadm_id, g.name, g.name_iso, g.name_english ".$geometry." FROM gadm_regions g
		WHERE g.gadm_level = ".$params['gadm_level']." ORDER BY g.name ".$limit;
		
// Execute the query and retrieve the results. This variable is used by the templates.
$result = make_query($sql);

$sql = "SELECT COUNT(*) FROM gadm_regions g WHERE g.gadm_level = ".$params['gadm_level'];
$num_entries_result = make_query($sql);

if (pg_fetch_array($num_entries_result)[0] > $params['limit'][1]) {
  $has_next_page = true;
} else {
  $has_next_page = false;
}  

if (pg_num_rows($result) == 0)
{
	header("HTTP/1.0 404 Not Found");
	header('Content-type: text/html');
	die();
}
$feature = array("gadm_level" => $params['gadm_level']);
// Display the results in the right format
show_level($feature, $result, $params['content_type'], $params['geometry'], $params['limit'], $has_next_page);
