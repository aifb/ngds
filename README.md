ngds - neogeo linked data system
========
The neogeo linked data system is a system to display and export spatial linked geo data on the semantic web.

API documentation of neogeo linked data system

feature
  *regions
      Brings the feature representation of a region and links to the geometry representation
      Note: the id relative to the level. So there might be a region 0/1 and a region 1/1

      URI: $BASE_URL$/id/$level$/$id$

      Export formats:
	-html: $BASE_URL$/id/$level$/$id$.html
	-turtle: $BASE_URL$/id/$level$/$id$.ttl
	-rdf/XML: $BASE_URL$/id/$level$/$id$.rdf

  *subregions
      Brings all regions under a speciefied other region

      URI: $BASE_URL$/id/$level$/$id$/sub

      Export formats:
	-html: $BASE_URL$/id/$level$/$id$/sub.html
	-turtle: $BASE_URL$/id/$level$/$id$/sub.ttl
	-rdf/XML: $BASE_URL$/id/$level$/$id$/sub.rdf
	-kml: $BASE_URL$/id/$level$/$id$/sub.kml

geometry
  *geometry
      Returns the geometry representation of a region in a specified resolution
      
      URI:
	-full resolution:  $BASE_URL$/id/$level$/$id$/geometry
	-resolution 100m:  $BASE_URL$/id/$level$/$id$/geometry_100m
	-resolution 1km:   $BASE_URL$/id/$level$/$id$/geometry_1km
	-resolution 10km:  $BASE_URL$/id/$level$/$id$/geometry_10km
	-resolution 100km: $BASE_URL$/id/$level$/$id$/geometry_100km
      

      Export formats:
	-html: $BASE_URL$/id/$level$/$id$.html
	-kml: $BASE_URL$/id/$level$/$id$.kml
	-kmz: $BASE_URL$/id/$level$/$id$.kmz
	-gml: $BASE_URL$/id/$level$/$id$.gml
	-geojson: $BASE_URL$/id/$level$/$id$.json
	-svg: $BASE_URL$/id/$level$/$id$.svg

  *neogeo spatial search with bounding box
      Returns the administrative regions which are related to the bounding box by a neogeo spatial relation.
      
      URI: $BASE_URL$/neogeo/$neogeo relation$?bbox=$longitude₁$,$latitude₁$,$longitude₂$,$latitude₂$
      
      Export formats:
	-rdf/XML: $BASE_URL$/neogeo/$neogeo relation$.rdf?bbox=$longitude₁$,$latitude₁$,$longitude₂$,$latitude₂$
	-kml: $BASE_URL$/neogeo/$neogeo relation$.kml?bbox=$longitude₁$,$latitude₁$,$longitude₂$,$latitude₂$
	-kmz: $BASE_URL$/neogeo/$neogeo relation$.kmz?bbox=$longitude₁$,$latitude₁$,$longitude₂$,$latitude₂$
	-gml: $BASE_URL$/neogeo/$neogeo relation$.gml?bbox=$longitude₁$,$latitude₁$,$longitude₂$,$latitude₂$
	-svg: $BASE_URL$/neogeo/$neogeo relation$.svg?bbox=$longitude₁$,$latitude₁$,$longitude₂$,$latitude₂$
	-geojson: $BASE_URL$/neogeo/$neogeo relation$.json?bbox=$longitude₁$,$latitude₁$,$longitude₂$,$latitude₂$

  *neogeo spatial search with point
      Returns the administrative regions which are related to the point by a neogeo spatial relation.

      URI: $BASE_URL$/neogeo/$neogeo relation$?point=$longitude$,$latitude$
      
      Export formats:
	-rdf/XML: $BASE_URL$/neogeo/$neogeo relation$.rdf?point=$longitude$,$latitude$
	-kml: $BASE_URL$/neogeo/$neogeo relation$.kml?point=$longitude$,$latitude$
	-kmz: $BASE_URL$/neogeo/$neogeo relation$.kmz?point=$longitude$,$latitude$
	-gml: $BASE_URL$/neogeo/$neogeo relation$.gml?point=$longitude$,$latitude$
	-svg: $BASE_URL$/neogeo/$neogeo relation$.svg?point=$longitude$,$latitude$
	-geojson: $BASE_URL$/neogeo/$neogeo relation$.json?point=$longitude$,$latitude$
	
  *neogeo spatial search with polygon
      Returns the administrative regions which are related to the polygon by a neogeo spatial relation.

      URI: $BASE_URL$/neogeo/$neogeo relation$?point=$longitude₁$,$latitude₁$,$longitude₂$,$latitude₂$,$longitude₃$,$latitude₃$,...,$longitudeₙ$,$latitudeₙ$
      
      Export formats:
	-rdf/XML: $BASE_URL$/neogeo/$neogeo relation$.rdf?point=$longitude₁$,$latitude₁$,$longitude₂$,$latitude₂$,$longitude₃$,$latitude₃$,...,$longitudeₙ$,$latitudeₙ$
	-kml: $BASE_URL$/neogeo/$neogeo relation$.kml?point=$longitude₁$,$latitude₁$,$longitude₂$,$latitude₂$,$longitude₃$,$latitude₃$,...,$longitudeₙ$,$latitudeₙ$
	-kmz: $BASE_URL$/neogeo/$neogeo relation$.kmz?point=$longitude₁$,$latitude₁$,$longitude₂$,$latitude₂$,$longitude₃$,$latitude₃$,...,$longitudeₙ$,$latitudeₙ$
	-gml: $BASE_URL$/neogeo/$neogeo relation$.gml?point=$longitude₁$,$latitude₁$,$longitude₂$,$latitude₂$,$longitude₃$,$latitude₃$,...,$longitudeₙ$,$latitudeₙ$
	-svg: $BASE_URL$/neogeo/$neogeo relation$.svg?point=$longitude₁$,$latitude₁$,$longitude₂$,$latitude₂$,$longitude₃$,$latitude₃$,...,$longitudeₙ$,$latitudeₙ$
	-geojson: $BASE_URL$/neogeo/$neogeo relation$.json?point=$longitude₁$,$latitude₁$,$longitude₂$,$latitude₂$,$longitude₃$,$latitude₃$,...,$longitudeₙ$,$latitudeₙ$
	
  *withinRegion (deprecated - use neogeo spatial search instead)
  
      Returns the administrative regions that include a certain point based on its latitude/longitude coordinates.
      
      URI: $BASE_URL$/services/withinRegion?lat=$latitude$&long=$longitude$#point 
      
      Export formats:
	-rdf/XML (only)
	
feature & geometry
  *search
      Perform a search using multiple optional criteria
      The supported HTTP GET parameteres are:

	bbox: Filter the administrative regions that are contained within the bounding box defined by two longitude/latitude pairs. (format: bbox=long1,lat1,long2,lat2)
	geo_long, geo_lat, radius: Select the regions that intersect a point defined by its latitude (geo_lat) and longitude (geo_long). A radius parameter can optionally be defined to filter the regions which are located within a certain distance from the defined point. Units are expressed in decimal degrees. (format: 
	rdfs_label: Filter the regions which have a similar label to the one defined in this parameter. Matching is case insensitive.
	rdf_type: Filter the regions belonging to a certain class. The URI must be encoded.
	spatial_pp: Filter the regions which are part of a certain region. The URI must be encoded.
	spatial_ppi: Select the regions that contain a certain region. The URI must be encoded.
	spatial_o: Select the regions that overlap a certain region. The URI must be encoded.
	spatial_po: Select the regions that partially overlap a certain region. The URI must be encoded.
	spatial_ec: Filter the regions that are externally connected to a certain region. The URI must be encoded.

Example: http://somni/gadm-rdf/services/search?bbox=10,47,7,50&rdfs_label=Karlsruhe&rdf_type=http%3A%2F%2Fgadm.geovocab.org%2Fontology%23Level3#results 