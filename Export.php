<?php
require_once 'lib/couch.php';
require_once 'lib/couchClient.php';
require_once 'lib/couchDocument.php';

$couch_dsn = "http://200.10.150.230:5984/";
$couch_db = "mira";

$client = new couchClient($couch_dsn,$couch_db);

$dblocation="localhost";
$dbuser="root";
$dbpassword="h6r49QuZ6yx7W35";
$dbname="jeos";

$mysqli = new mysqli($dblocation, $dbuser, $dbpassword, $dbname);
/* check connection */
if ($mysqli->connect_errno) {
    printf("\nConnect failed: %s\n", $mysqli->connect_error);
}

$last_seq = 0;	//debug
$changes = $client->since($last_seq)->style("all_docs")->getChanges();

foreach($changes->results as $obj){	
	echo "\nprocessing document id: ";
	echo $couchdb_id = $obj->id;
	$doc = $client->getDoc($obj->id);
	
	echo $address = $doc->properties->name;
	echo $latitude  = $doc->geometry->coordinates[1];
	echo $longitude = $doc->geometry->coordinates[0];
    $postID = $doc->properties->id;
    $title = $doc->properties->title;
	$date = $doc->properties->date;
	$url = $doc->properties->url;
	$bubble = $doc->properties->bubble;
	$iconUrl = $doc->properties->marker->iconUrl;
	$iconSize1 = $doc->properties->marker->iconSize[0];
	$iconSize1 = $doc->properties->marker->iconSize[1];
	$iconArchor1 = $doc->properties->marker->iconArchor[0];
	$iconArchor1 = $doc->properties->marker->iconArchor[1];
	$popupArchor1 = $doc->properties->marker->popupArchor[0];
	$popupArchor1 = $doc->properties->marker->popupArchor[1];
	$markerId = $doc->properties->marker->markerId;
	$class = $doc->properties->class;
	

}



$mysqli->close();

?>