<?php
  include('../gadm.php');
  require_once('../config.php');

  // Parameter validation
  if ( !isset($_GET['wkt']) )
  {
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-type: text/html');
    die("wkt parameter needed.");
  }

  $wkt = stripslashes(urldecode($_GET['wkt']));  

  // Perform CRS reprojection if needed
  if (substr($wkt,0,4) == "SRID"){
    $srid = substr($wkt,5,strpos($wkt,";")-5);
    $geometry = "ST_SimplifyPreserveTopology(ST_Transform(ST_SetSRID(ST_GeomFromText('".$wkt."'),".$srid."),4326),0.5)";  
  }else{
    $geometry = "ST_SimplifyPreserveTopology(ST_GeomFromText('".$wkt."', 4326),0.5)";
  }

  // Select regions who intersect the input geometry, having a Hausdorff Distance lesser than 0.1 SRID units and with an error margin in the area of 10%.
  $sql = "SELECT gadm_level, gadm_id FROM gadm_regions WHERE 
	    ".$geometry." && geometry
	    AND ST_Area(".$geometry.") BETWEEN (shape_area*0.9) AND (shape_area*1.1) 
	    AND ST_HausdorffDistance(".$geometry.", ST_SimplifyPreserveTopology(geometry,0.5)) < max_hausdorff_dist";

  $result = make_query($sql);

  // Set MIME Type to 'application/rdf+xml'
  header('Content-type: application/rdf+xml; charset=utf-8');

  // Echo XML header as a string, so it is not treated as an opening PHP tag.
  echo '<?xml version="1.0" encoding="utf-8"?>';

?>

<rdf:RDF 
  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" 
  xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#" 
  xmlns:ngeo="<?php echo BASE_URL?>/geometry#"
  xmlns:spatial="<?php echo BASE_URL?>/spatial#">

  <rdf:Description rdf:about="">
    <rdfs:comment>Source: GADM-RDF Linked Data Services (http://gadm.geovocab.org/).</rdfs:comment>
  </rdf:Description>

  <rdf:Description rdf:ID="geometry">
    <ngeo:asWKT><?php echo $wkt ?></ngeo:asWKT>
  <?php while ( $row = pg_fetch_array($result, null, PGSQL_ASSOC) ): ?>
    <spatial:EQ rdf:resource="<?php echo BASE_URL?>/id/<?php echo $row['gadm_level']; ?>_<?php echo $row['gadm_id']; ?>"/>
  <?php endwhile; ?>
  </rdf:Description>
  
</rdf:RDF>
