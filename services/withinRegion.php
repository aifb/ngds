<?php
  include('../gadm.php');
  require_once('../config.php');
  
  // Parameter validation
  if ( !isset($_GET['lat']) || !isset($_GET['long']) )
  {
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-type: text/html');
    die("lat/long parameters needed.");
  }

  $long = stripslashes($_GET['long']);
  $lat = stripslashes($_GET['lat']);

  if (!is_numeric($long) || !is_numeric($lat))
  {
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-type: text/html');
    die("lat/long parameters must be numeric values.");
  }

  // Perform the SQL query
  $sql = "SELECT gadm_level, gadm_id FROM gadm_regions r WHERE ST_Within(ST_GeomFromText('POINT(".$long." ".$lat.")', 4326),simplified_geometry)";

  $result = make_query($sql);

  // Set MIME Type to 'application/rdf+xml'
  header('Content-type: application/rdf+xml; charset=utf-8');

  // Echo XML header as a string, so it is not treated as an opening PHP tag.
  echo '<?xml version="1.0" encoding="utf-8"?>';
?>

<rdf:RDF 
  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" 
  xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#" 
  xmlns:spatial="<?php echo BASE_URL?>/spatial#"
  xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#">

  <rdf:Description rdf:about="">
    <rdfs:comment>Source: GADM-RDF Linked Data Services (http://gadm.geovocab.org/).</rdfs:comment>
  </rdf:Description>

  <rdf:Description rdf:ID="point">
    <geo:lat rdf:datatype="http://www.w3.org/2001/XMLSchema#float"><?php echo $lat; ?></geo:lat>
    <geo:long rdf:datatype="http://www.w3.org/2001/XMLSchema#float"><?php echo $long; ?></geo:long>
  <?php while ( $row = pg_fetch_array($result) ): ?>
    <spatial:PP rdf:resource="<?php echo BASE_URL?>/id/<?php echo $row['gadm_level']; ?>_<?php echo $row['gadm_id']; ?>"/>
  <?php endwhile; ?>
  </rdf:Description>
  
</rdf:RDF>
