<?php
require_once 'lib/couch.php';
require_once 'lib/couchClient.php';
require_once 'lib/couchDocument.php';
$client = new couchClient ('http://localhost:5984',"jeocouch");


$last_seq = 0;
$prefix = "wp_dhgp3n_";
$mysqli = new mysqli("localhost", "root", "h6r49QuZ6yx7W35", "rea2");
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




//STEP 2: mysql (geojson) to CouchDB sync
//$data = file_get_contents("http://www.mapasdigitais.org/rea/?geojson=1");
echo "\n\n======STEP 2======\n\n";
$data = file_get_contents("http://200.10.150.230/rea/?geojson=1");
$data = json_decode($data, true); 

foreach($data["features"] as $post){	
	try {
	       	$object = new stdClass();
	       	$post_id = $title = "";
			foreach ($post as $key => $value)
			{
			    $object->$key = $value;
			    if($key == "properties"){
			    	$post_id = $value["postID"];
			    	$title = $value["title"];
			    }
			}

			//TODO:validation,


			//$doc->_id = $doc->properties->postID;
			$response = $client->storeDoc($object);
			echo "document inserted\n";

			//UPDATE wordpress records with couchdb ID $response->id
			//IMPORTANT: only if using the same database from geojson wp api, otherwise comment this lines.
			if(!empty($post_id)){
				$query = "INSERT INTO {$prefix}postmeta(post_id,meta_key,meta_value) VALUES({$post_id},'couchdb_id', '{$response->id}')";
				$mysqli->query($query);	
				echo "-mysql record inserted to update couchdb_id\n";
			}

	} catch (Exception $e) {
	        echo "Something weird happened: ".$e->getMessage()." (errcode=".$e->getCode().")\n";
	}
	echo "The document is stored. CouchDB response body: ".print_r($response,true)."\n";
}





//STEP 2: mysql to CouchDB sync
/*
echo "\nprocessing wordpress database ";
echo "\n\n";

$new_wordpress_records = "
						SELECT 
						p.ID post_id,
						m1.meta_value longitude,
						m2.meta_value latitude,
						m4.meta_value address,
						m3.meta_value couchdb_id

						FROM {$prefix}posts p

						INNER JOIN {$prefix}postmeta m1
						ON p.ID = m1.post_id and m1.meta_key = 'geocode_longitude'

						INNER JOIN {$prefix}postmeta m2
						ON p.ID = m2.post_id and m2.meta_key = 'geocode_latitude'

						INNER JOIN {$prefix}postmeta m4
						ON p.ID = m4.post_id and m4.meta_key = 'geocode_address'

						LEFT JOIN {$prefix}postmeta m3
						ON p.ID = m3.post_id and m3.meta_key = 'couchdb_id' -- and m3.meta_value = null

						LIMIT 4
";
if ($result = $mysqli->query($new_wordpress_records)) {
	while($cobj = $result->fetch_object()){				
		
		if(!empty($cobj->couchdb_id))
			continue;		
		
		//create document		
		//TODO: join with post table to bring post_name
		$new_doc = new stdClass();
		$new_doc->post_id = $cobj->post_id;
		$new_doc->geojson->type = "Feature";
		$new_doc->geojson->properties->address = $cobj->address;
		$new_doc->geojson->geometry->type = "Point";
		$new_doc->geojson->geometry->coordinates = array($cobj->longitude,$cobj->latitude);
		//print_r($new_doc);

		try {
		    $response = $client->storeDoc($new_doc);

		    //UPDATE wordpress records with couchdb ID $response->id
			$query = "INSERT INTO {$prefix}postmeta(post_id,meta_key,meta_value) VALUES({$cobj->post_id},'couchdb_id', '{$response->id}')";
			$mysqli->query($query);	

		} catch (Exception $e) {
		    echo "ERROR: ".$e->getMessage()." (".$e->getCode().")<br>\n";
		}
		echo "Doc recorded. id = ".$response->id." and revision = ".$response->rev."<br>\n";	
		echo "\n";
	}
    $result->close();
}*/



?>