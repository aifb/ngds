<?php
	require_once(__DIR__.'/../config.php');
  	// Set MIME Type to 'application/rdf+xml'
  	header('Content-type: application/rdf+xml; charset=utf-8');

  	// Set expiration time for cache
  	$expires = 86400;
  	header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');

  	// Echo XML header as a string, so it is not treated as an opening PHP tag.
  	echo '<?xml version="1.0" encoding="utf-8"?>';
?>

<rdf:RDF 
  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" 
  xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#" 
  xmlns:spatial="<?php echo BASE_URL?>/spatial#"
  xmlns:ngeo="<?php echo BASE_URL?>/geometry#"
  xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#"
  xmlns:ogc="http://www.opengis.net/rdf#"
  xmlns:owl="http://www.w3.org/2002/07/owl#"
  xmlns:gadm="<?php echo BASE_URL?>/ontology#"
  xmlns:dctype="http://purl.org/dc/dcmitype/"
  xmlns:dct="http://purl.org/dc/terms/"
  xmlns:foaf="http://xmlns.com/foaf/0.1/">

  <rdf:Description rdf:about="">
    <rdfs:comment>Source: GADM project (http://gadm.org/). Conversion: GADM-RDF project (<?php echo BASE_URL?>)</rdfs:comment>
  </rdf:Description>

  <?php foreach ($data as $resource):?>
  <<?php if (isset($resource['predicate'])) { echo $resource['predicate']; } else {?>rdf:Description<?php } ?> rdf:about="<?php echo $resource['uri'];?>">
  <?php foreach ($resource['properties'] as $property): ?>
  <?php print render("rdf-xml_resource.php", array('property' => $property)); ?>
  <?php endforeach; ?>
  </<?php if (isset($resource['predicate'])) { echo $resource['predicate']; } else {?>rdf:Description<?php } ?>>
  <?php endforeach; ?>

</rdf:RDF>
