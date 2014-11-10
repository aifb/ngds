<?php require_once 'config.php';?>
<div id="header">
	<h1><a href="<?php echo BASE_URL?>">GADM-RDF</a></h1>
	<div id="search">
	  <form id="search_form" action="<?php echo BASE_URL?>/search" method="get">
	    <input type="text" name="q" />
	    <a href="#" onclick="document.forms['search_form'].submit();"><div id="search_button">Search</div></a>
	  </form>
	</div>
	<div id="menu">
	  <ul>
	    <li class="last"><a href="<?php echo BASE_URL?>">Home</a></li>
	    <!--<li class="last"><a href="/downloads.php">Downloads</a></li>-->
	  </ul>
	</div>
</div>