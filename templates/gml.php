<?php 
  // Set MIME Type to 'application/vnd.google-earth.kml+xml'
  header('Content-type: application/vnd.ogc.gml');

  // Set expiration time for cache
  $expires = 86400;
  header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');

  // Echo XML header as a string, so it is not treated as an opening PHP tag.
  echo '<?xml version="1.0" encoding="utf-8"?>';
  echo $data['geometry'];
?>
