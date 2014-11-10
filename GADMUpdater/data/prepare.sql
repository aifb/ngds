BEGIN;

DROP INDEX IF EXISTS "gadm_regions_geometry_gist";

CREATE OR REPLACE FUNCTION create_level_0(table_name text) RETURNS void AS $$
BEGIN
  EXECUTE 'DROP TABLE IF EXISTS ' || table_name || ';';
  EXECUTE 'CREATE TABLE ' || table_name || ' (gid serial PRIMARY KEY,
  "gadmid" int2,
  "iso" varchar(5),
  "name_engli" varchar(50),
  "name_iso" varchar(54),
  "name_fao" varchar(50),
  "name_local" varchar(54),
  "name_obsol" varchar(150),
  "name_varia" varchar(160),
  "name_nonla" varchar(50),
  "name_frenc" varchar(50),
  "name_spani" varchar(50),
  "name_russi" varchar(50),
  "name_arabi" varchar(50),
  "name_chine" varchar(50),
  "waspartof" varchar(100),
  "contains" varchar(50),
  "sovereign" varchar(40),
  "iso2" varchar(4),
  "www" varchar(2),
  "fips" varchar(6),
  "ison" numeric,
  "validfr" varchar(12),
  "validto" varchar(10),
  "andyid" numeric,
  "pop2000" numeric,
  "sqkm" numeric,
  "popsqkm" numeric,
  "unregion1" varchar(254),
  "unregion2" varchar(254),
  "developing" numeric,
  "cis" numeric,
  "transition" numeric,
  "oecd" numeric,
  "wbregion" varchar(254),
  "wbincome" varchar(254),
  "wbdebt" varchar(254),
  "wbother" varchar(254),
  "ceeac" numeric,
  "cemac" numeric,
  "ceplg" numeric,
  "comesa" numeric,
  "eac" numeric,
  "ecowas" numeric,
  "igad" numeric,
  "ioc" numeric,
  "mru" numeric,
  "sacu" numeric,
  "uemoa" numeric,
  "uma" numeric,
  "palop" numeric,
  "parta" numeric,
  "cacm" numeric,
  "eurasec" numeric,
  "agadir" numeric,
  "saarc" numeric,
  "asean" numeric,
  "nafta" numeric,
  "gcc" numeric,
  "csn" numeric,
  "caricom" numeric,
  "eu" numeric,
  "can" numeric,
  "acp" numeric,
  "landlocked" numeric,
  "aosis" numeric,
  "sids" numeric,
  "islands" numeric,
  "ldc" numeric,
  "shape_leng" numeric,
  "shape_area" numeric);';
  EXECUTE 'SELECT AddGeometryColumn($1,$2,$3,$4,$5,2);' USING '', table_name, 'geometry', 4326, 'MULTIPOLYGON';
  EXECUTE 'SELECT AddGeometryColumn($1,$2,$3,$4,$5,2);' USING '', table_name, 'simplified_geometry', 4326, 'MULTIPOLYGON';
  EXECUTE 'ALTER TABLE ' || table_name || ' DROP CONSTRAINT enforce_geotype_simplified_geometry;';
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION create_level_1(table_name text) RETURNS void AS $$
BEGIN
  EXECUTE 'DROP TABLE IF EXISTS ' || table_name || ';';
  EXECUTE 'CREATE TABLE ' || table_name || ' (gid serial PRIMARY KEY,
  "id_0" int4,
  "iso" varchar(3),
  "name_0" varchar(75),
  "id_1" int4,
  "name_1" varchar(75),
  "varname_1" varchar(150),
  "nl_name_1" varchar(50),
  "hasc_1" varchar(15),
  "cc_1" varchar(15),
  "type_1" varchar(50),
  "engtype_1" varchar(50),
  "validfr_1" varchar(25),
  "validto_1" varchar(25),
  "remarks_1" varchar(125),
  "shape_leng" numeric,
  "shape_area" numeric);';
  EXECUTE 'SELECT AddGeometryColumn($1,$2,$3,$4,$5,2);' USING '', table_name, 'geometry', 4326, 'MULTIPOLYGON';
  EXECUTE 'SELECT AddGeometryColumn($1,$2,$3,$4,$5,2);' USING '', table_name, 'simplified_geometry', 4326, 'MULTIPOLYGON';
  EXECUTE 'ALTER TABLE ' || table_name || ' DROP CONSTRAINT enforce_geotype_simplified_geometry;';
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION create_level_2(table_name text) RETURNS void AS $$
BEGIN
  EXECUTE 'DROP TABLE IF EXISTS ' || table_name || ';';
  EXECUTE 'CREATE TABLE ' || table_name || ' (gid serial PRIMARY KEY,
  "id_0" int4,
  "iso" varchar(3),
  "name_0" varchar(75),
  "id_1" int4,
  "name_1" varchar(75),
  "id_2" int4,
  "name_2" varchar(75),
  "varname_2" varchar(150),
  "nl_name_2" varchar(75),
  "hasc_2" varchar(15),
  "cc_2" varchar(15),
  "type_2" varchar(50),
  "engtype_2" varchar(50),
  "validfr_2" varchar(25),
  "validto_2" varchar(25),
  "remarks_2" varchar(100),
  "shape_leng" numeric,
  "shape_area" numeric);';
  EXECUTE 'SELECT AddGeometryColumn($1,$2,$3,$4,$5,2);' USING '', table_name, 'geometry', 4326, 'MULTIPOLYGON';
  EXECUTE 'SELECT AddGeometryColumn($1,$2,$3,$4,$5,2);' USING '', table_name, 'simplified_geometry', 4326, 'MULTIPOLYGON';
  EXECUTE 'ALTER TABLE ' || table_name || ' DROP CONSTRAINT enforce_geotype_simplified_geometry;';
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION create_level_3(table_name text) RETURNS void AS $$
BEGIN
  EXECUTE 'DROP TABLE IF EXISTS ' || table_name || ';';
  EXECUTE 'CREATE TABLE ' || table_name || ' (gid serial PRIMARY KEY,
  "id_0" int4,
  "iso" varchar(3),
  "name_0" varchar(75),
  "id_1" int4,
  "name_1" varchar(75),
  "id_2" int4,
  "name_2" varchar(75),
  "id_3" int4,
  "name_3" varchar(75),
  "varname_3" varchar(100),
  "nl_name_3" varchar(75),
  "hasc_3" varchar(25),
  "type_3" varchar(50),
  "engtype_3" varchar(50),
  "validfr_3" varchar(25),
  "validto_3" varchar(25),
  "remarks_3" varchar(50),
  "shape_leng" numeric,
  "shape_area" numeric);';
  EXECUTE 'SELECT AddGeometryColumn($1,$2,$3,$4,$5,2);' USING '', table_name, 'geometry', 4326, 'MULTIPOLYGON';
  EXECUTE 'SELECT AddGeometryColumn($1,$2,$3,$4,$5,2);' USING '', table_name, 'simplified_geometry', 4326, 'MULTIPOLYGON';
  EXECUTE 'ALTER TABLE ' || table_name || ' DROP CONSTRAINT enforce_geotype_simplified_geometry;';
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION create_level_4(table_name text) RETURNS void AS $$
BEGIN
  EXECUTE 'DROP TABLE IF EXISTS ' || table_name || ';';
  EXECUTE 'CREATE TABLE ' || table_name || ' (gid serial PRIMARY KEY,
  "id_0" int4,
  "iso" varchar(3),
  "name_0" varchar(75),
  "id_1" int4,
  "name_1" varchar(75),
  "id_2" int4,
  "name_2" varchar(75),
  "id_3" int4,
  "name_3" varchar(75),
  "id_4" int4,
  "name_4" varchar(100),
  "varname_4" varchar(100),
  "type_4" varchar(25),
  "engtype_4" varchar(25),
  "validfr_4" varchar(25),
  "validto_4" varchar(25),
  "remarks_4" varchar(50),
  "shape_leng" numeric,
  "shape_area" numeric);';
  EXECUTE 'SELECT AddGeometryColumn($1,$2,$3,$4,$5,2);' USING '', table_name, 'geometry', 4326, 'MULTIPOLYGON';
  EXECUTE 'SELECT AddGeometryColumn($1,$2,$3,$4,$5,2);' USING '', table_name, 'simplified_geometry', 4326, 'MULTIPOLYGON';
  EXECUTE 'ALTER TABLE ' || table_name || ' DROP CONSTRAINT enforce_geotype_simplified_geometry;';
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION drop_level(table_name text) RETURNS void AS $$
BEGIN
  EXECUTE 'DROP TABLE IF EXISTS ' || table_name || ';';
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION merge_0(table_name text) RETURNS void AS $$
BEGIN
  EXECUTE 'UPDATE ' || table_name || ' SET geometry = geomfromtext(astext(snaptogrid(geometry, 0.000001)), 4326);';
  EXECUTE 'UPDATE ' || table_name || ' SET simplified_geometry = ST_Buffer(ST_SimplifyPreserveTopology(geometry,(ST_Perimeter(geometry)/20000)),0);';
  EXECUTE 'DELETE FROM gadm_regions WHERE gadm_level = 0 AND gadm_id IN (SELECT gadmid FROM ' || table_name || ');';
  EXECUTE '
  INSERT INTO gadm_regions (
    gadm_id,
    gadm_level,
    iso,
    name_english,
    name_iso,
    name_fao,
    name,
    name_obsolete,
    name_variations,
    name_french,
    name_spanish,
    waspartof,
    contains,
    sovereign,
    iso2,
    www,
    fips,
    ison,
    valid_from,
    valid_to,
    andyid,
    pop2000,
    sqkm,
    popsqkm,
    unregion1,
    unregion2,
    developing,
    cis,
    transition,
    oecd,
    wbregion,
    wbincome,
    wbdebt,
    wbother,
    ceeac,
    cemac,
    ceplg,
    comesa,
    eac,
    ecowas,
    igad,
    ioc,
    mru,
    sacu,
    uemoa,
    uma,
    palop,
    parta,
    cacm,
    eurasec,
    agadir,
    saarc,
    asean,
    nafta,
    gcc,
    csn,
    caricom,
    eu,
    can,
    acp,
    landlocked,
    aosis,
    sids,
    islands,
    ldc,
    shape_leng,
    shape_area,
    geometry,
    simplified_geometry
  ) SELECT
    gadmid as "gadm_id",
    0 as "gadm_level",
    iso,
    name_engli as "name_english",
    name_iso,
    name_fao,
    name_local as "name",
    name_obsol as "name_obsolete",
    name_varia as "name_variations",
    name_frenc as "name_french",
    name_spani as "name_spanish",
    waspartof,
    contains,
    sovereign,
    iso2,
    www,
    fips,
    ison,
    validfr as "valid_from",
    validto as "valid_to",
    andyid,
    pop2000,
    sqkm,
    popsqkm,
    unregion1,
    unregion2,
    developing,
    cis,
    transition,
    oecd,
    wbregion,
    wbincome,
    wbdebt,
    wbother,
    ceeac,
    cemac,
    ceplg,
    comesa,
    eac,
    ecowas,
    igad,
    ioc,
    mru,
    sacu,
    uemoa,
    uma,
    palop,
    parta,
    cacm,
    eurasec,
    agadir,
    saarc,
    asean,
    nafta,
    gcc,
    csn,
    caricom,
    eu,
    can,
    acp,
    landlocked,
    aosis,
    sids,
    islands,
    ldc,
    shape_leng,
    shape_area,
    geometry,
    simplified_geometry
  FROM ' || table_name || ';';
  EXECUTE 'DELETE FROM ' || table_name || ';';
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION merge_1(table_name text) RETURNS void AS $$
BEGIN
  EXECUTE 'UPDATE ' || table_name || ' SET geometry = geomfromtext(astext(snaptogrid(geometry, 0.000001)), 4326);';
  EXECUTE 'UPDATE ' || table_name || ' SET simplified_geometry = ST_Buffer(ST_SimplifyPreserveTopology(geometry,(ST_Perimeter(geometry)/20000)),0);';
  EXECUTE 'DELETE FROM gadm_regions WHERE gadm_level = 1 AND gadm_id IN (SELECT id_1 FROM ' || table_name || ');';
  EXECUTE 'INSERT INTO gadm_regions (
    gadm_level,
    iso,
    id_0,
    name_0,
    gadm_id,
    name,
    name_variations,
    has_code,
    cc,
    type,
    type_english,
    valid_from,
    valid_to,
    remarks,
    shape_leng,
    shape_area,
    geometry,
    simplified_geometry
  ) SELECT
    1 as "gadm_level",
    iso,
    id_0,
    name_0,
    id_1 as "gadm_id",
    name_1 as "name",
    varname_1 as "name_variations",
    hasc_1 as "has_code",
    cc_1 as "cc",
    type_1 as "type",
    engtype_1 as "type_english",
    validfr_1 as "valid_from",
    validto_1 as "valid_to",
    remarks_1 as "remarks",
    shape_leng,
    shape_area,
    geometry,
    simplified_geometry
  FROM ' || table_name || ';';
  EXECUTE 'DELETE FROM ' || table_name || ';';
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION merge_2(table_name text) RETURNS void AS $$
BEGIN
  EXECUTE 'UPDATE ' || table_name || ' SET geometry = geomfromtext(astext(snaptogrid(geometry, 0.000001)), 4326);';
  EXECUTE 'UPDATE ' || table_name || ' SET simplified_geometry = ST_Buffer(ST_SimplifyPreserveTopology(geometry,(ST_Perimeter(geometry)/20000)),0);';
  EXECUTE 'DELETE FROM gadm_regions WHERE gadm_level = 2 AND gadm_id IN (SELECT id_2 FROM ' || table_name || ');';
  EXECUTE 'INSERT INTO gadm_regions (
    gadm_level,
    id_0,
    iso,
    name_0,
    id_1,
    name_1,
    gadm_id,
    name,
    name_variations,
    has_code,
    cc,
    type,
    type_english,
    valid_from,
    valid_to,
    remarks,
    shape_leng,
    shape_area,
    geometry,
    simplified_geometry
  ) SELECT
    2 as "gadm_level",
    id_0,
    iso,
    name_0,
    id_1,
    name_1,
    id_2 as "gadm_id",
    name_2 as "name",
    varname_2 as "name_variations",
    hasc_2 as "has_code",
    cc_2 as "cc",
    type_2 as "type",
    engtype_2 as "type_english",
    validfr_2 as "valid_from",
    validto_2 as "valid_to",
    remarks_2 as "remarks",
    shape_leng,
    shape_area,
    geometry,
    simplified_geometry
  FROM ' || table_name || ';';
  EXECUTE 'DELETE FROM ' || table_name || ';';
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION merge_3(table_name text) RETURNS void AS $$
BEGIN
  EXECUTE 'UPDATE ' || table_name || ' SET geometry = geomfromtext(astext(snaptogrid(geometry, 0.000001)), 4326);';
  EXECUTE 'UPDATE ' || table_name || ' SET simplified_geometry = ST_Buffer(ST_SimplifyPreserveTopology(geometry,(ST_Perimeter(geometry)/20000)),0);';
  EXECUTE 'DELETE FROM gadm_regions WHERE gadm_level = 3 AND gadm_id IN (SELECT id_3 FROM ' || table_name || ');';
  EXECUTE 'INSERT INTO gadm_regions (
    gadm_level,
    id_0,
    iso,
    name_0,
    id_1,
    name_1,
    id_2,
    name_2,
    gadm_id,
    name,
    name_variations,
    nl_name,
    has_code,
    type,
    type_english,
    valid_from,
    valid_to,
    remarks,
    shape_leng,
    shape_area,
    geometry,
    simplified_geometry
  ) SELECT
    3 as "gadm_level",
    id_0,
    iso,
    name_0,
    id_1,
    name_1,
    id_2,
    name_2,
    id_3 as "gadm_id",
    name_3 as "name",
    varname_3 as "name_variations",
    nl_name_3 as "nl_name",
    hasc_3 as "has_code",
    type_3 as "type",
    engtype_3 as "type_english",
    validfr_3 as "valid_from",
    validto_3 as "valid_to",
    remarks_3 as "remarks",
    shape_leng,
    shape_area,
    geometry,
    simplified_geometry
  FROM ' || table_name || ';';
  EXECUTE 'DELETE FROM ' || table_name || ';';
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION merge_4(table_name text) RETURNS void AS $$
BEGIN
  EXECUTE 'UPDATE ' || table_name || ' SET geometry = geomfromtext(astext(snaptogrid(geometry, 0.000001)), 4326);';
  EXECUTE 'UPDATE ' || table_name || ' SET simplified_geometry = ST_Buffer(ST_SimplifyPreserveTopology(geometry,(ST_Perimeter(geometry)/20000)),0); ';
  EXECUTE 'DELETE FROM gadm_regions WHERE gadm_level = 4 AND gadm_id IN (SELECT id_4 FROM ' || table_name || ');';
  EXECUTE 'INSERT INTO gadm_regions (
    gadm_level,
    id_0,
    iso,
    name_0,
    id_1,
    name_1,
    id_2,
    name_2,
    id_3,
    name_3,
    gadm_id,
    name,
    name_variations,
    type,
    type_english,
    valid_from,
    valid_to,
    remarks,
    shape_leng,
    shape_area,
    geometry,
    simplified_geometry
  ) SELECT
    4 as "gadm_level",
    id_0,
    iso,
    name_0,
    id_1,
    name_1,
    id_2,
    name_2,
    id_3,
    name_3,
    id_4 as "gadm_id",
    name_4 as "name",
    varname_4 as "name_variations",
    type_4 as "type",
    engtype_4 as "type_english",
    validfr_4 as "valid_from",
    validto_4 as "valid_to",
    remarks_4 as "remarks",
    shape_leng,
    shape_area,
    geometry,
    simplified_geometry
  FROM ' || table_name || ';';
  EXECUTE 'DELETE FROM ' || table_name || ';';
END;
$$ LANGUAGE plpgsql;

COMMIT;