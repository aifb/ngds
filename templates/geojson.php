<?php 
  // Set MIME Type to 'application/json'
  header('Content-type: application/json');

  // Set expiration time for cache
  $expires = 86400;
  header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
  echo $data['geometry'];
?>
