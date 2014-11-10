DROP FUNCTION IF EXISTS create_level_0(text);
DROP FUNCTION IF EXISTS create_level_1(text);
DROP FUNCTION IF EXISTS create_level_2(text);
DROP FUNCTION IF EXISTS create_level_3(text);
DROP FUNCTION IF EXISTS create_level_4(text);
DROP FUNCTION IF EXISTS drop_level(text);
DROP FUNCTION IF EXISTS merge_0(text);
DROP FUNCTION IF EXISTS merge_1(text);
DROP FUNCTION IF EXISTS merge_2(text);
DROP FUNCTION IF EXISTS merge_3(text);
DROP FUNCTION IF EXISTS merge_4(text);

DELETE FROM rdf_type;

INSERT INTO rdf_type (uri, gadm_level, gadm_id) SELECT 'http://geovocab.org/spatial#Feature', gadm_level, gadm_id FROM gadm_regions;
INSERT INTO rdf_type (uri, gadm_level, gadm_id) SELECT 'http://gadm.geovocab.org/ontology#AdministrativeRegion', gadm_level, gadm_id FROM gadm_regions;
INSERT INTO rdf_type (uri, gadm_level, gadm_id) SELECT 'http://gadm.geovocab.org/ontology#Country', gadm_level, gadm_id FROM gadm_regions WHERE gadm_level = 0;
INSERT INTO rdf_type (uri, gadm_level, gadm_id) SELECT 'http://gadm.geovocab.org/ontology#Level1', gadm_level, gadm_id FROM gadm_regions WHERE gadm_level = 1;
INSERT INTO rdf_type (uri, gadm_level, gadm_id) SELECT 'http://gadm.geovocab.org/ontology#Level2', gadm_level, gadm_id FROM gadm_regions WHERE gadm_level = 2;
INSERT INTO rdf_type (uri, gadm_level, gadm_id) SELECT 'http://gadm.geovocab.org/ontology#Level3', gadm_level, gadm_id FROM gadm_regions WHERE gadm_level = 3;
INSERT INTO rdf_type (uri, gadm_level, gadm_id) SELECT 'http://gadm.geovocab.org/ontology#Level4', gadm_level, gadm_id FROM gadm_regions WHERE gadm_level = 4;
INSERT INTO rdf_type (uri, gadm_level, gadm_id) SELECT 'http://gadm.geovocab.org/ontology#LandlockedCountry', gadm_level, gadm_id FROM gadm_regions WHERE gadm_level = 0 AND landlocked = TRUE;
INSERT INTO rdf_type (uri, gadm_level, gadm_id) SELECT 'http://gadm.geovocab.org/ontology#IslandCountry', gadm_level, gadm_id FROM gadm_regions WHERE gadm_level = 0 AND islands = TRUE;
INSERT INTO rdf_type (uri, gadm_level, gadm_id) SELECT 'http://gadm.geovocab.org/ontology#LeastDevelopedCountry', gadm_level, gadm_id FROM gadm_regions WHERE gadm_level = 0 AND ldc = TRUE;
INSERT INTO rdf_type (uri, gadm_level, gadm_id) SELECT 'http://gadm.geovocab.org/ontology#TransitionEconomy', gadm_level, gadm_id FROM gadm_regions WHERE gadm_level = 0 AND transition = TRUE;

CREATE INDEX "gadm_regions_geometry_gist" ON "gadm_regions" USING gist ("geometry" gist_geometry_ops);

CLUSTER gadm_regions_geometry_gist ON gadm_regions; 

VACUUM ANALYZE;