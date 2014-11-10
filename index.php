<?php
echo '<?xml version="1.0" encoding="utf-8"?>';
require_once 'config.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">

<html>
<head>
<title property="dc:title">GADM-RDF Project: An RDF spatial
	representation of all the administrative regions in the world</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="<?php echo BASE_URL?>/css/style.css" />
</head>

<body>

	<div id="container">
		<?php include('header.php') ?>
		<div id="content">
			<h2>An RDF spatial representation of all the administrative regions
				in the world</h2>

			<p>
				<a href="<?php echo BASE_URL?>">GADM</a> is a spatial database of the
				location of the world's administrative areas (or adminstrative
				boundaries) for use in GIS and similar software. Administrative
				areas in this database are countries and lower level subdivisions
				such as provinces, departments, bibhag, bundeslander, daerah
				istimewa, fivondronana, krong, landsvæðun, opština,
				sous-préfectures, counties, and thana. GADM describes where these
				administrative areas are (the "spatial features"), and for each area
				it provides some attributes, foremost being the name and variant
				names.
			</p>
			<p>
				This site contains an RDF spatial representation of the regions
				represented in the GADM project, providing links to existing spatial
				datasets, with the ultimate goal of enhancing the integration of
				spatial information in the Semantic Web. You can find RDF dumps of
				the available data in our <a href="<?php echo BASE_URL?>/data/">download section</a>.<br />
				<br /> <b><i>Examples:</i> </b>
			
			
			<ul>
				<li><b>Germany:</b> <a href="<?php echo BASE_URL?>/id/0/60"><?php echo BASE_URL?>/id/0/60</a></li>
				<li><b>Argentina:</b> <a href="<?php echo BASE_URL?>/id/0/10"><?php echo BASE_URL?>/id/0/10</a></li>
				<li><b>Dorset (UK):</b> <a href="<?php echo BASE_URL?>/id/2/13958"><?php echo BASE_URL?>/id/2/13958</a></li>
			</ul>
			</p>

			<h2>Services</h2>

			<p>
				We also provide a growing set of <a href="http://openlids.org/">Linked
					Data Services (LIDS)</a>:
			
			
			<ul>
				<li><b>neogeo spatial search with bounding box:</b> Returns the administrative regions which are related to the bounding box by a neogeo spatial relation.<br />
				    <i>Format: </i><?php echo BASE_URL?>/neogeo/{neogeo relation}?bbox={longitude&#8321;},{latitude&#8321;},{longitude&#8322;},{latitude&#8322;}
				    <i>Example: </i><a href="<?php echo BASE_URL?>/neogeo/O?bbox=10,47,7,50"><?php echo BASE_URL?>/neogeo/O?bbox=10,47,7,50</a>
				</li>
				<li><b>neogeo spatial search with point:</b> Returns the administrative regions which are related to the point by a neogeo spatial relation.<br />
                                    <i>Format: </i><?php echo BASE_URL?>/neogeo/{neogeo relation}?point={longitude},{latitude}
                                    <i>Example: </i><a href="<?php echo BASE_URL?>/neogeo/O?point=10,47"><?php echo BASE_URL?>/neogeo/O?point=10,47</a>
                                </li>
                                <li><b>neogeo spatial search with polygon:</b> Returns the administrative regions which are related to the polygon by a neogeo spatial relation.<br />
                                    <i>Format: </i><?php echo BASE_URL?>/neogeo/{neogeo relation}?point={longitude&#8321;},{latitude&#8321;},,{longitude&#8322;},{latitude&#8322;},{longitude&#8323;},{latitude&#8323;},...,{longitude&#8345;},{latitude&#8345;}
                                    <i>Example: </i><a href="<?php echo BASE_URL?>/neogeo/O?polygon=10,47,9,48,7,50,10,47"><?php echo BASE_URL?>/neogeo/O?polygon=10,47,9,48,7,50,10,47</a>
                                </li>
				<li><b>withinRegion:</b> Returns the administrative regions that
					include a certain point based on its latitude/longitude
					coordinates.<br /> <i>Example: </i><a
					href="<?php echo BASE_URL?>/services/withinRegion?lat=49&long=8.4#point"><?php echo BASE_URL?>/services/withinRegion?lat=49&long=8.4#point</a>
				</li>
				<li><b>search:</b> Perform a search on the GADM-RDF knowledge base
					using multiple optional criteria.<br /> The supported parameteres
					are:
					<ul>
						<li><i>bbox:</i> Filter the administrative regions that are
							contained within the bounding box defined by two
							latitude/longitude pairs.</li>
						<li><i>geo_long, geo_lat, radius:</i> Select the regions that
							intersect a point defined by its latitude (geo_lat) and longitude
							(geo_long). A radius parameter can optionally be defined to
							filter the regions which are located within a certain distance
							from the defined point. Units are expressed in decimal degrees.</li>
						<li><i>rdfs_label:</i> Filter the regions which have a similar
							label to the one defined in this parameter. Matching is case
							insensitive.</li>
						<li><i>rdf_type:</i> Filter the regions belonging to a certain
							class. The URI must be encoded.</li>
						<li><i>spatial_pp:</i> Filter the regions which are part of a
							certain region. The URI must be encoded.</li>
						<li><i>spatial_ppi:</i> Select the regions that contain a certain
							region. The URI must be encoded.</li>
						<li><i>spatial_o:</i> Select the regions that overlap a certain
							region. The URI must be encoded.</li>
						<li><i>spatial_po:</i> Select the regions that partially overlap a
							certain region. The URI must be encoded.</li>
						<li><i>spatial_ec:</i> Filter the regions that are externally
							connected to a certain region. The URI must be encoded.</li>
					</ul> <i>Example: </i><a
					href="<?php echo BASE_URL?>/services/search?bbox=10,47,7,50&rdfs_label=Karlsruhe&rdf_type=http%3A%2F%2Fgadm.geovocab.org%2Fontology%23Level3#results"><?php echo BASE_URL?>/services/search?bbox=10,47,7,50&rdfs_label=Karlsruhe&rdf_type=http%3A%2F%2Fgadm.geovocab.org%2Fontology%23Level3#results</a>
				</li>
			</ul>

			</p>

			<h2>News</h2>

			<dl>
                                <dt>2014-06-14</dt>
                                <dd>Added pagination for level feature</dd>
                                <dt>2014-04-21</dt>
                                <dd>Added point and polygon parameter to NeoGeo Spatial Ontology search.</dd>
                                <dt>2014-04-15</dt>
                                <dd>Switched to github.</dd>
				<dt>2013-11-20</dt>
				<dd>Added NeoGeo Spatial Ontology search.</dd>
				<dt>2013-10-20</dt>
				<dd>Switched to OpenLayers map module.<dd>
				<dt>2013-07-31</dt>
				<dd>Changed uri format.<dd>
				<dt>2012-02-16</dt>
				<dd>Removed menus. Added new set of dumps.</dd>
				<dt>2011-11-30</dt>
				<dd>Search service supporting multiple criteria. Geo.LinkedData.es
					integration.</dd>
				<dt>2011-09-04</dt>
				<dd>New set of templates. Search engine.</dd>
				<dt>2011-08-30</dt>
				<dd>Bounding Box LIDS. Corrected bug on the representation of large
					files in the Google Maps gadget.</dd>
				<dt>2011-08-25</dt>
				<dd>Improved the mappings between the original data and the RDF
					model. Added GeoJSON and KMZ representation formats.</dd>
				<dt>2011-08-2</dt>
				<dd>First version</dd>
			</dl>

			<hr />
			$Id: index.php 1 2014-04-22 10:30:22Z &#x70;&#x72;&#x69;&#x76;<div style="display:none;">thisisprivate</div>&#x61;&#x74;<div style="display: inline">&#x40;</div>&#100&#101&#110&#110<div style="display:none;">thisisprivate</div>&#105&#115&#107&#101&#99&#107&#46&#100&#101 $
		</div>
	</div>
	<?php include('footer.php') ?>


</body>
</html>
