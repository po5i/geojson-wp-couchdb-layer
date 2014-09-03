<?php
header('Content-Type: application/json');
require_once 'lib/couch.php';
require_once 'lib/couchClient.php';
require_once 'lib/couchDocument.php';
$client = new couchClient ('http://localhost:5984',"jeocouch");

//$all_docs = $client->getAllDocs();
$result = $client->getView('all','all');
$out = array("type" => "FeatureCollection", "features" => array());

foreach($result->rows as $res){
	$out["features"][] = $res->value;
}

echo json_encode($out);

?>