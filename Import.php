<?php
$data = file_get_contents("http://www.mapasdigitais.org/rea/?geojson=1");
$data = json_decode($data, true); 

require_once 'lib/couch.php';
require_once 'lib/couchClient.php';
require_once 'lib/couchDocument.php';

$couch_dsn = "http://200.10.150.230:5984/";
$couch_db = "mira";

$client = new couchClient($couch_dsn,$couch_db);

foreach($data[features] as $post){	
	try {
	       	$object = new stdClass();
			foreach ($post as $key => $value)
			{
			    $object->$key = $value;
			}
			$object->_id = $object->properties["id"];			
			$response = $client->storeDoc($object);
	} catch (Exception $e) {
	        echo "Something weird happened: ".$e->getMessage()." (errcode=".$e->getCode().")\n";
	}
	echo "The document is stored. CouchDB response body: ".print_r($response,true)."\n";
}
?>