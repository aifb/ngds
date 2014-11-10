<?php
  include('gadm.php');
  require_once('config.php');

  $error = false;

  if ( !isset($_GET['q']) )
  {
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-type: text/html');
    die("Search criteria needed");
  }else{

    // Parameter validation
    $search = stripslashes($_GET['q']);
    if (isset($_GET['content_type'])){
      $content_type = stripslashes($_GET['content_type']);
    }else{
      $content_type = "";
    }

    // Build the SQL query
    $sql = "SELECT g.name, g.name_english, name_0, name_1, name_2, name_3, g.gadm_level, g.gadm_id
		FROM gadm_regions g
		WHERE g.name ILIKE '%".$search."%' OR g.name_english ILIKE '%".$search."%' OR g.name_variations ILIKE '%".$search."%' 
		    OR g.name_french ILIKE '%".$search."%' OR g.name_spanish ILIKE '%".$search."%'";

    // Execute the query and retrieve the results. This variable is used by the templates.
    $result = make_query($sql);

  }

?>
<?php if ($content_type == "rdf"): ?> 
<?php
  // Set MIME Type to 'application/rdf+xml'
  header('Content-type: application/rdf+xml; charset=utf-8');
  // Echo XML header as a string, so it is not treated as an opening PHP tag.
  echo '<?xml version="1.0" encoding="utf-8"?>';
?>

<rdf:RDF 
  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" 
  xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#">

  <rdf:Description rdf:about="">
    <rdfs:comment>Source: GADM-RDF Linked Data Services (http://gadm.geovocab.org/).</rdfs:comment>
  </rdf:Description>

  <rdf:Description rdf:about="<?php echo BASE_URL?>/search?q=<?php echo $_GET['q'] ?>">
  <?php while ($row = pg_fetch_array($result, null, PGSQL_ASSOC)): ?>
    <rdfs:seeAlso rdf:resource="<?php echo BASE_URL?>/id/<?php echo $row['gadm_level'].'_'.$row['gadm_id'] ?>" />
  <?php endwhile ?>
  </rdf:Description>
</rdf:RDF>

<?php else: ?>
<html>
   <head> 
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"> 
      <title>Search results for "<?php echo $search ?>"</title> 

      <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL?>/css/style.css">
  </head> 
  <body> 

    <div id="container">
      <?php include('header.php') ?>
      <div id="content">
	<h1>Search results for "<?php echo $search ?>"</h1>

	<?php if ($error): ?>
	  <div class="error">Error: <?php echo $error ?></div>
	<?php elseif (pg_num_rows($result) == 0): ?>
	<p>No results found.</p>
	<?php else: ?>
	  <table class="results">
	  <tr>
	    <th>Name</th>
	    <th>Name@en</th>
	    <th>Region</th>
	    <th>Country</th>
	    <th>GADM Level</th>
	    <th>GADM Id</th>
	  </tr>
	  <?php $count = 0 ?>
	  <?php while ($row = pg_fetch_array($result, null, PGSQL_ASSOC)): ?>
	    <tr class="<?php echo ($count++%2 == 0)? "even" : "odd"; ?>">
	      <td><a href="<?php echo BASE_URL?>/id/<?php echo $row['gadm_level'].'_'.$row['gadm_id'] ?>"><?php echo $row['name'] ?></a></td>
	      <td><?php echo $row['name_english'] ?></td>
	      <td>
		<?php 
		  if ($row['name_3']) {
		    echo $row['name_3'];
		  } else if ($row['name_2']) {
		    echo $row['name_2'];
		  } else if ($row['name_1']) {
		    echo $row['name_1'];
		  }
		?>
	      </td>
	      <td><?php echo $row['name_0'] ?></td>
	      <td><?php echo $row['gadm_level'] ?></td>
	      <td><?php echo $row['gadm_id'] ?></td>
	    </tr>
	  <?php endwhile ?>
	  </table>
	<?php endif ?>

      </div>
    </div> 
    <?php include('footer.php') ?>
   </body> 
</html>
<?php endif; ?>