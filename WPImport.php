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
			$doc->_id = $doc->properties->postID;
			$response = $client->storeDoc($object);
	} catch (Exception $e) {
	        echo "Something weird happened: ".$e->getMessage()." (errcode=".$e->getCode().")\n";
	}
	echo "The document is stored. CouchDB response body: ".print_r($response,true)."\n";
}

$last_seq = 0;
$mysqli = new mysqli("localhost", "root", "h6r49QuZ6yx7W35", "jeos");
/* check connection */
if ($mysqli->connect_errno) {
    printf("\nConnect failed: %s\n", $mysqli->connect_error);
    exit();
}

$last_seq = 0;	//debug
$changes = $client->since($last_seq)->style("all_docs")->getChanges();

foreach($changes->results as $obj){	
	echo "\nprocessing document id: ";
	echo $couchdb_id = $obj->id;
	$doc = $client->getDoc($obj->id);
	
	/*echo "<pre>";
	print_r($doc);
	echo "</pre>";*/

	//validar modificado o repetido
	$old_post_id = 0;	
	if ($result = $mysqli->query("SELECT * FROM wp_posts WHERE postID like '$couchdb_id' ")) {
		$cobj = $result->fetch_object();
		if(!empty($cobj))
	    	echo $old_post_id = $cobj->post_id;
	    $result->close();
	}

	$address = $doc->properties->name;
	$latitude  = $doc->geometry->coordinates[1];
	$longitude = $doc->geometry->coordinates[0];
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
       

	if($old_post_id == 0){
		$query = "INSERT INTO wp_posts(post_author, post_date, post_title, post_status, post_name, post_type) 
					VALUES(1,NOW(),'$address','publish','$address','post') ";

		$mysqli->query($query);
		$post_id = $mysqli->insert_id;

		
		$query = "INSERT INTO wp_postmeta(post_id,meta_key,meta_value) VALUES($post_id,'geocode_viewport', '')";
		$mysqli->query($query);
		$query = "INSERT INTO wp_postmeta(post_id,meta_key,meta_value) VALUES($post_id,'_geocode_country', '')";
		$mysqli->query($query);
		$query = "INSERT INTO wp_postmeta(post_id,meta_key,meta_value) VALUES($post_id,'_geocode_city', '')";
		$mysqli->query($query);
		$query = "INSERT INTO wp_postmeta(post_id,meta_key,meta_value) VALUES($post_id,'geocode_longitude', '$longitude')";
		$mysqli->query($query);
		$query = "INSERT INTO wp_postmeta(post_id,meta_key,meta_value) VALUES($post_id,'geocode_latitude', '$latitude')";
		$mysqli->query($query);
		$query = "INSERT INTO wp_postmeta(post_id,meta_key,meta_value) VALUES($post_id,'geocode_address', '$address')";
		$mysqli->query($query);
		$query = "INSERT INTO wp_postmeta(post_id,meta_key,meta_value) VALUES($post_id,'_edit_lock', '')";
		$mysqli->query($query);
		$query = "INSERT INTO wp_postmeta(post_id,meta_key,meta_value) VALUES($post_id,'_edit_last', '1')";
		$mysqli->query($query);	
			
	}
	else{
		echo "\n... Edit document.";
		$query = "UPDATE wp_posts SET post_title='$address', post_name='$address' WHERE id = $old_post_id";
		$mysqli->query($query);
		$query = "UPDATE wp_postmeta SET meta_value = '$address' WHERE post_id = $old_post_id AND meta_key like 'geocode_address'";
		$mysqli->query($query);
		$query = "UPDATE wp_postmeta SET meta_value = '$longitude' WHERE post_id = $old_post_id AND meta_key like 'geocode_longitude'";
		$mysqli->query($query);
		$query = "UPDATE wp_postmeta SET meta_value = '$latitude' WHERE post_id = $old_post_id AND meta_key like 'geocode_latitude'";
		$mysqli->query($query);
	}
		

}



$mysqli->close();

?>