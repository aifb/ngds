<?php 

  // Set MIME Type to 'application/vnd.google-earth.kml+xml'
  header('Content-type: application/vnd.google-earth.kmz');

  // Set expiration time for cache
  $expires = 86400;
  header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');

  // Echo XML header as a string, so it is not treated as an opening PHP tag.
  $content = '<?xml version="1.0" encoding="utf-8"?>';
  $content .= '<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2">';

  if (isset($data['links'])){
    $content .= '<Folder><name>Search results</name><visibility>1</visibility><open>0</open>';
    if (isset($data['geometry'])){
      $content .= '<Placemark>'.$data['geometry'].'</Placemark>';
    }
    foreach ($data['links'] as $link){
      $content .= '<NetworkLink>';
      $content .= '<name>'.$link['name'].'</name>';
      $content .= '<visibility>1</visibility><open>0</open><refreshVisibility>0</refreshVisibility><flyToView>0</flyToView>';
      $content .= '<Link><href>'.$link['url'].'</href></Link></NetworkLink>';
    }
    $content .= '</Folder>';
  } else {

    // Use our own PHP generated random color code, because Google Maps does not support the KML 'colorMode' property with the 'random' value.
    $randomColor = sprintf("%02X%02X%02X", mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));  

    $content .= '<Document><Style id="randomColorStyle"><LineStyle><color>7fffffff</color><width>3</width></LineStyle>';
    $content .= '<PolyStyle><color>a7'.$randomColor.'</color></PolyStyle></Style>';
    $content .= '<Placemark><styleUrl>#randomColorStyle</styleUrl><name>'.$data['name'].'</name><snippet>'.$data['uri'].'</snippet>';
    $content .= '<ExtendedData>';
    foreach ($data['properties'] as $property){
      $content .= '<Data name="rdfProperty">
          <displayName><![CDATA[
            <a href="'.$property['predicate_uri'].'">'.$property['predicate'].'</a>';
	    if (!empty($property['lang'])){
	      $content .= '@'.$property['lang'];
	    }
      $content .= ']]></displayName>
          <value><![CDATA[';
	    if($property['datatype'] == "anyURI"){
	      $content .= '<a href="'.$property['object'].'">'.$property['object'].'</a>';
	    } else {
	      $content .= $property['object'];
	    }
	    
	    
      $content .= ']]></value></Data>';
    }
    $content .= '</ExtendedData>';
    $content .= $data['geometry'];
    $content .= '</Placemark></Document>';
  }
  $content .= '</kml>';

  // USING KMZ CLASS
  include('KMZfile.php');
  $KMZfile = new KMZfile();  
  $KMZfile -> add_file($content, $data['filename']);
  echo $KMZfile->file();

?>
