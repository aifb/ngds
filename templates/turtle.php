<?php
	require_once('config.php');
  	// Set MIME Type to 'text/turtle'
  	header('Content-type: text/turtle; charset=utf-8');

  	// Set expiration time for cache
  	$expires = 86400;
  	header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
?>
@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .
@prefix spatial: <<?php echo BASE_URL?>/spatial#> .
@prefix ngeo: <http://geovocab.org/geometry#> .
@prefix geo: <http://www.w3.org/2003/01/geo/wgs84_pos#> .
@prefix ogc: <http://www.opengis.net/rdf#> .
@prefix owl: <http://www.w3.org/2002/07/owl#> .
@prefix gadm: <<?php echo BASE_URL?>/ontology#> .
@prefix dctype: <http://purl.org/dc/dcmitype/> .
@prefix dct: <http://purl.org/dc/terms/> .
@prefix foaf: <http://xmlns.com/foaf/0.1/> .

[] rdfs:comment "Source: GADM project (http://gadm.org/). Conversion: GADM-RDF project (http://gadm.geovocab.org/)" .

<?php foreach ($data as $resource): ?>
<?php if (isset($resource['properties'])): ?>
<?php foreach ($resource['properties'] as $property): ?>
<?php print render("turtle_resource.php", array('property' => $property)); ?>
<?php endforeach; ?>
<?php endif; ?>
<?php endforeach; ?>
