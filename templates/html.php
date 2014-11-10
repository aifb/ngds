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
      <script src="<?php echo BASE_URL?>/OpenLayers/OpenLayers.js"></script>
      <script src="http://maps.google.com/maps/api/js?v=3.2&sensor=false"></script>
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

	<!-- OpenLayers map -->
	<div id="map" style="width: 380px; height: 280px"></div>
	 <script defer="defer" type="text/javascript">
        var  map = new OpenLayers.Map({
            div: "map",
            projection: new OpenLayers.Projection("EPSG:900913"),
        	displayProjection: new OpenLayers.Projection("EPSG:4326")
        });
        var mapnik = new OpenLayers.Layer.OSM("OpenStreetMap (Mapnik)");
        var gmap = new OpenLayers.Layer.Google("Google", {sphericalMercator:true});
        var kml = new OpenLayers.Layer.Vector("<?php echo $data['name'] ?>", {
        	projection: map.displayProjection,
        	strategies: [new OpenLayers.Strategy.Fixed()],
        	protocol: new OpenLayers.Protocol.HTTP({
        		url: "<?php echo $data['simplified_geometry_uri'] ?>.kml",
        		format: new OpenLayers.Format.KML({
        			extractStyles: true,
        			extractAttributes: true
        		})
        	})
        });
        kml.events.on({
        	"featureselected": onFeatureSelect,
        	"featureunselected": onFeatureUnselect,
        	"loadend": function() {
                map.zoomToExtent(kml.getDataExtent());
            }
        });
        map.addLayers([mapnik, gmap, kml]);
        select = new OpenLayers.Control.SelectFeature(kml);
        map.addControl(select);
        select.activate();
        map.addControl(new OpenLayers.Control.LayerSwitcher());
        function onPopupClose(evt) {
        	select.unselectAll();
        }
        function onFeatureSelect(event) {
        	var feature = event.feature;
        	var selectedFeature = feature;
        	var popup = new OpenLayers.Popup.FramedCloud("popup"+feature.attributes.name,
        		feature.geometry.getBounds().getCenterLonLat(),
        		new OpenLayers.Size(100,100),
        		"<h2>"+feature.attributes.name + "</h2>" + listify(feature.attributes),
        		null, true, onPopupClose
        	);
        	feature.popup = popup;
        	map.addPopup(popup);
        }
        function onFeatureUnselect(event) {
        	var feature = event.feature;
        	if(feature.popup) {
        		map.removePopup(feature.popup);
        		feature.popup.destroy();
       			delete feature.popup;
        	}
        }

        function listify(object) {
            var result = "";
            for (var entry in object) {
                var item = object[entry];
            	if(typeof(item) == 'object' && typeof(item['value']) != 'undefined') {
                	result += "<h4>"+entry+"</h4>"+item['value'];
            	}
            }
            return result;
        }   		

        /**
         * Function : dump()
         * Arguments: The data - array,hash(associative array),object
         *    The level - OPTIONAL
         * Returns  : The textual representation of the array.
         * This function was inspired by the print_r function of PHP.
         * This will accept some data as the argument and return a
         * text that will be a more readable version of the
         * array/hash/object that is given.
         * Docs: http://www.openjs.com/scripts/others/dump_function_php_print_r.php
         */
        function dump(arr,level) {
        	var dumped_text = "";
        	if(!level) level = 0;
        	
        	//The padding given at the beginning of the line.
        	var level_padding = "";
        	for(var j=0;j<level+1;j++) level_padding += "    ";
        	
        	if(typeof(arr) == 'object') { //Array/Hashes/Objects 
        		for(var item in arr) {
        			var value = arr[item];
        			
        			if(typeof(value) == 'object') { //If it is an array,
        				dumped_text += level_padding + "'" + item + "' ...\n";
        				dumped_text += dump(value,level+1);
        			} else {
        				dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
        			}
        		}
        	} else { //Stings/Chars/Numbers etc.
        		dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
        	}
        	return dumped_text;
        }
    </script>
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