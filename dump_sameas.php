<?php
  // Allow only command-line usage
  if (PHP_SAPI != 'cli') 
  { 
    header("HTTP/1.0 404 Not Found");
    header('Content-type: text/html');
    die();
  } 

  // Validate the arguments
  if (!isset($argv[1])){
    echo "Usage: php dump_sameas.php [DATASET]\nPlease use the desired dataset as a parameter. \n\nExample: php dump_sameas.php dbpedia\n";
    die();
  }

  $dataset = $argv[1];

  include('gadm.php');

  $offset = 0;
  $limit = 100;

  // Build the SQL query
  $sql = "SELECT * FROM owl_sameas g WHERE uri LIKE '%".$dataset."%' ORDER BY gadm_level, gadm_id";

  // Execute the query and retrieve the results. This variable is used by the templates.
  $result = make_query($sql. " LIMIT ".$limit." OFFSET ".$offset);  

  while (pg_num_rows($result) > 0){

    // Process the results
    while ($row = pg_fetch_array($result, null, PGSQL_ASSOC)){
      echo "<http://gadm.geovocab.org/id/".$row['gadm_level']."_".$row['gadm_id']."> <http://www.w3.org/2002/07/owl#sameAs> <".$row['uri']."> .\n";
    }    

    // Execute the query and retrieve the results. This variable is used by the templates.
    $offset += $limit;
    $result = make_query($sql. " LIMIT ".$limit." OFFSET ".$offset);  

  }

?>