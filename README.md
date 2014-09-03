geojson-wp-couchdb-layer
========================

Couchdb exporter (couchdb to mysql): it requires the credentials of a mysql wordpress database in order to perform the inserts from the couchdb store.

Couchdb importer (geojson to couchdb): at the beginning it imported from mysql database directly, but now it imports from an external geojson url such as http://www.mapasdigitais.org/rea/?geojson=1 but I am facing an issue: I do not have a way to avoid duplicates at couchdb yet.

Couchdb geojson transformer: a layer api to expose couchdb data with the geojson schema.

TODO:
=====

avoid the duplication at couchdb
