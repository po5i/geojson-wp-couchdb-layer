<?php
require_once 'lib/couch.php';
require_once 'lib/couchClient.php';
require_once 'lib/couchDocument.php';

$couch_dsn = "http://200.10.150.230:5984/";
$couch_db = "mira";

$client = new couchClient($couch_dsn,$couch_db);
try {
	$result = $client->deleteDatabase();
} catch ( Exception $e) {
	echo "Something weird happened: ".$e->getMessage()." (errcode=".$e->getCode().")\n";
        exit(1);
}
?>