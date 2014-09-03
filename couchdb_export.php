<?php
require_once 'config.php';
require_once 'lib/couch.php';
require_once 'lib/couchClient.php';
require_once 'lib/couchDocument.php';
$client = new couchClient ($COUCH_CLIENT,$COUCH_DB);


$last_seq = 0;
$prefix = "wp_dhgp3n_";
$mysqli = new mysqli($MYSQL_SERVER, $MYSQL_USER, $MYSQL_PASS, $MYSQL_DB);
/* check connection */
if ($mysqli->connect_errno) {
    printf("\nConnect failed: %s\n", $mysqli->connect_error);
    exit();
}
/* Select queries return a resultset */
if ($result = $mysqli->query("SELECT * FROM jeos WHERE id = 1")) {
	$obj = $result->fetch_object();
    $last_seq = $obj->value;

    /* free result set */
    $result->close();
}


//$last_seq = 11;	//debug






//STEP 1: CouchDB to mysql sync
echo "\n\n======STEP 1======\n\n";
$changes = $client->since($last_seq)->style("all_docs")->getChanges();

/*echo "<pre>";
print_r($changes);
echo "</pre>";*/

echo "\nlast sequence from changes:";
echo $this_last_seq = $changes->last_seq;
echo "\n\n";
$query = "UPDATE jeos SET value = '$this_last_seq' WHERE id = 1";
$mysqli->query($query);

foreach($changes->results as $obj){	
	echo "\nprocessing document id: ";
	echo $couchdb_id = $obj->id;
	$doc = $client->getDoc($obj->id);
	echo "\n\n";
	
	/*echo "<pre>";
	print_r($doc);
	echo "</pre>";*/

	if(empty($doc))
		continue;

	//search for existing (in order to make an update)
	$old_post_id = 0;	
	if ($result = $mysqli->query("SELECT * FROM {$prefix}postmeta WHERE meta_key like 'couchdb_id' AND  meta_value like '$couchdb_id' ")) {
		$cobj = $result->fetch_object();
		if(!empty($cobj)){
	    	$old_post_id = $cobj->post_id;
	    	echo "-old post detected with id $old_post_id \n";
	    }
	    $result->close();
	}

	//extraet properties from JSON
	/*if($doc->geojson->properties)
		$address   = $doc->geojson->properties->name;
	$latitude  = $doc->geojson->geometry->coordinates[1];
	$longitude = $doc->geojson->geometry->coordinates[0];*/

	//TODO: insert properties
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
		$query = "INSERT INTO {$prefix}posts(post_author, post_date, post_title, post_status, post_name, post_type) 
					VALUES(1,NOW(),'$title','publish','$title','post') ";

		$mysqli->query($query);
		$post_id = $mysqli->insert_id;

		
		$query = "INSERT INTO {$prefix}postmeta(post_id,meta_key,meta_value) VALUES($post_id,'geocode_viewport', '')";
		$mysqli->query($query);
		$query = "INSERT INTO {$prefix}postmeta(post_id,meta_key,meta_value) VALUES($post_id,'_geocode_country', '')";
		$mysqli->query($query);
		$query = "INSERT INTO {$prefix}postmeta(post_id,meta_key,meta_value) VALUES($post_id,'_geocode_city', '')";
		$mysqli->query($query);
		$query = "INSERT INTO {$prefix}postmeta(post_id,meta_key,meta_value) VALUES($post_id,'geocode_longitude', '$longitude')";
		$mysqli->query($query);
		$query = "INSERT INTO {$prefix}postmeta(post_id,meta_key,meta_value) VALUES($post_id,'geocode_latitude', '$latitude')";
		$mysqli->query($query);
		$query = "INSERT INTO {$prefix}postmeta(post_id,meta_key,meta_value) VALUES($post_id,'geocode_address', '$address')";
		$mysqli->query($query);
		$query = "INSERT INTO {$prefix}postmeta(post_id,meta_key,meta_value) VALUES($post_id,'_edit_lock', '')";
		$mysqli->query($query);
		$query = "INSERT INTO {$prefix}postmeta(post_id,meta_key,meta_value) VALUES($post_id,'_edit_last', '1')";
		$mysqli->query($query);	
		$query = "INSERT INTO {$prefix}postmeta(post_id,meta_key,meta_value) VALUES($post_id,'couchdb_id', '$couchdb_id')";
		$mysqli->query($query);	
	}
	else{		
		$query = "UPDATE {$prefix}posts SET post_title='$address', post_name='$address' WHERE id = $old_post_id";
		$mysqli->query($query);
		$query = "UPDATE {$prefix}postmeta SET meta_value = '$address' WHERE post_id = $old_post_id AND meta_key like 'geocode_address'";
		$mysqli->query($query);
		$query = "UPDATE {$prefix}postmeta SET meta_value = '$longitude' WHERE post_id = $old_post_id AND meta_key like 'geocode_longitude'";
		$mysqli->query($query);
		$query = "UPDATE {$prefix}postmeta SET meta_value = '$latitude' WHERE post_id = $old_post_id AND meta_key like 'geocode_latitude'";
		$mysqli->query($query);
		echo "\n... Edited post.\n";
	}
		

}


?>