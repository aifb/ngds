#!/bin/bash

if [ $# -ne 1 ]
then
  echo "Please use the version number of the dataset as an argument."
  echo "Usage: `basename $0` [version]"
  exit -1
fi

if [ ! -d "data/$1" ]; then
  mkdir data/$1
fi

if [ ! -d "data/$1/links" ]; then
  mkdir data/$1/links
fi

php dump_sameas.php dbpedia > data/$1/links/dbpedia_links-$1.nt
bzip2 data/$1/links/dbpedia_links-$1.nt

php dump_sameas.php geo.linkeddata.es > data/$1/links/geo.linkeddata.es_links-$1.nt
bzip2 data/$1/links/geo.linkeddata.es_links-$1.nt

php dump.php nt > data/$1/gadm-rdf-$1.nt
bzip2 data/$1/gadm-rdf-$1.nt

php dump.php nq > data/$1/gadm-rdf-$1.nq
bzip2 data/$1/gadm-rdf-$1.nq