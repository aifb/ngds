<?php 
  // Set MIME Type to 'image/svg+xml'
  header('Content-type: image/svg+xml');

  // Set expiration time for cache
  $expires = 86400;
  header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
  
  // Echo XML header as a string, so it is not treated as an opening PHP tag.
  echo '<?xml version="1.0" encoding="utf-8"?>';

  $bbox = str_replace(","," ",substr($data['bbox'],4,strlen($data['bbox'])-5));
  $bbox_coordinates = explode(" ",$bbox);
  
  $xmin = floor($bbox_coordinates[0]);
  $ymin = floor($bbox_coordinates[1]);
  $xmax = ceil($bbox_coordinates[2]);
  $ymax = ceil($bbox_coordinates[3]);

  $xwidth = $xmax - $xmin;
  $ywidth = $ymax - $ymin;

  $viewBox = $xmin." ".(-$ymax)." ".$xwidth." ".$ywidth;

  $randomColor = sprintf("%02X%02X%02X", mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)); 

?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd"> 
<svg width="1024" height="768" preserveAspectRatio="xMinYMin" viewBox="-90 -90 180 180" version="1.1" xmlns="http://www.w3.org/2000/svg">
  <path fill="#<?php echo $randomColor; ?>" fill-opacity="0.5" stroke="#ffffff" stroke-width="0.01" d="<?php echo $data['geometry']; ?>"/>
</svg>
