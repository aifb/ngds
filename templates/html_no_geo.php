<?php
	require_once 'config.php';
	// Set expiration time for cache
	$expires = 86400;
	header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
?>
<html>
   <head> 
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"> 
      <title><?php echo $data['name'] ?></title> 
      <link rel="alternate" type="text/turtle" href="<?php echo $data['id'] ?>.ttl">
      <link rel="alternate" type="application/rdf+xml" href="<?php echo $data['id'] ?>.rdf">
      <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL?>/css/style.css"/>
  </head> 
  <body> 

    <div id="container">
      <?php include('header.php') ?>

      <div id="content">
	<div id="intro_block">
	    <h1><?php echo $data['name'] ?></h1> 
	    <i><?php echo $data['uri'] ?></i>
	    <?php $nFormats = count($data['formats']); $i = 0; ?>
	    <p>View as: 
	      <?php foreach ($data['formats'] as $format => $name): ?>
		<a href="<?php echo $data['uri'] ?>.<?php echo $format; ?>"><?php echo $name; ?></a>
		<?php $i++; echo ($i < $nFormats)? "," : ""; ?>
	      <?php endforeach; ?>
	    </p>
	</div>
	<?php $uri = $data['uri']; ?>
	<div id="description">
	    <table>
	      <?php foreach ($data['properties'] as $property): ?>
		<?php if ($property['subject'] != $uri): ?>
		  <?php $uri = $property['subject']; ?>
		  </table>
		  <h2><?php echo $property['subject'] ?></h2> 
		  <table>
		<?php endif; ?>
		<?php print render("html_resource.php", array('property' => $property)); ?>
	      <?php endforeach; ?>
	    </table> 	  
	</div>

      </div>
    </div>
    <?php include('footer.php') ?>
   </body> 
</html>