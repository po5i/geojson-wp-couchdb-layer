<?php
header('Content-Type: application/json');
require_once 'config.php';
require_once 'lib/couch.php';
require_once 'lib/couchClient.php';
require_once 'lib/couchDocument.php';
$client = new couchClient ($COUCH_CLIENT,$COUCH_DB);

//$all_docs = $client->getAllDocs();
$result = $client->getView('all','all');
$out = array("type" => "FeatureCollection", "features" => array());

foreach($result->rows as $res){
	$out["features"][] = $res->value;
}

echo json_encode($out);

?>