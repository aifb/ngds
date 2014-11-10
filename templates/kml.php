<?php 
  // Set MIME Type to 'application/vnd.google-earth.kml+xml'
  header('Content-type: application/vnd.google-earth.kml+xml');

  // Set expiration time for cache
  $expires = 86400;
  header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');

  // Echo XML header as a string, so it is not treated as an opening PHP tag.
  echo '<?xml version="1.0" encoding="utf-8"?>';
?>

<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2">
<?php if (isset($data['links'])): ?>
  <Folder>
    <name>Search results</name>
    <visibility>1</visibility>
    <open>0</open>
    <?php if (isset($data['geometry'])): ?>
    <Placemark>
      <?php echo $data['geometry']; ?>
    </Placemark>
    <?php endif; ?>
    <?php foreach ($data['links'] as $link): ?>
      <NetworkLink>
	<name><?php echo $link['name']; ?></name>
	<visibility>1</visibility>
	<open>0</open>
	<refreshVisibility>0</refreshVisibility>
	<flyToView>0</flyToView>
	<Link>
	  <href><?php echo $link['url']; ?></href>
	</Link>
      </NetworkLink>
    <?php endforeach; ?>
  </Folder>
<?php else: ?>
  <?php 
    // Use our own PHP generated random color code, because Google Maps does not support the KML 'colorMode' property with the 'random' value.
    $randomColor = sprintf("%02X%02X%02X", mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)); 
  ?>
  <Document>
    <Style id="randomColorStyle">
      <LineStyle>
	<color>7f333333</color>
	<width>2</width>
      </LineStyle>
      <PolyStyle>
	<color>15<?php echo $randomColor; ?></color>
      </PolyStyle>
    </Style>
    <?php
    	//decide whether there is only one placemark to display or each proper
    	if (isset($data['properties'][0]['geometry'])) {
    		$placemarks = $data['properties'];
    	} else {
			$placemarks[0] = $data;
		}
		foreach ($placemarks as $place) :
	?>
    <Placemark>
      <name><?php echo $place['name']; ?></name>
      <snippet><?php echo $place['uri']; ?></snippet>
      <ExtendedData>       
	<?php if (isset($place['properties'])) : foreach ($place['properties'] as $property): ?>
	<Data name="<?php echo str_replace(":","_",$property['predicate']); ?>">
          <displayName><![CDATA[
            <a href="<?php echo $property['predicate_uri']; ?>"><?php echo $property['predicate']; ?></a>
	    <?php if (!empty($property['lang'])): ?>@<?php echo $property['lang']; ?><?php endif; ?>
          ]]></displayName>
          <value><![CDATA[
	    <?php if($property['datatype'] == "anyURI"): ?>
	    <a href="<?php echo $property['object']; ?>"><?php echo $property['object']; ?></a>
	    <?php else: ?>
	    <?php echo $property['object']; ?>
	    <?php endif;?>
	  ]]></value>
        </Data>
	<?php endforeach; endif;?>
	<?php if (isset($place['object'])) : ?>
	<Data name="<?php echo str_replace(":","_",$place['predicate']); ?>">
          <value><![CDATA[
	    <?php if($place['datatype'] == "anyURI"): ?>
	    <a href="<?php echo $place['object']; ?>"><?php echo $place['object']; ?></a>
	    <?php else: ?>
	    <?php echo $place['object']; ?>
	    <?php endif;?>
	  ]]></value>
        </Data>
	<?php endif;?>
      </ExtendedData>    
      <styleUrl>#randomColorStyle</styleUrl>
      <?php echo $place['geometry']; ?>
    </Placemark>
    <?php endforeach;?>
  </Document>
<?php endif; ?>
</kml>