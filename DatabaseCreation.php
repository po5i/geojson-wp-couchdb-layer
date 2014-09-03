<?php
require_once 'lib/couch.php';
require_once 'lib/couchClient.php';
require_once 'lib/couchDocument.php';

$couch_dsn = "http://200.10.150.230:5984/";
$couch_db = "mira";

$client = new couchClient($couch_dsn,$couch_db);

echo "#### Creating database ".$client->getDatabaseUri().': $result = $client->createDatabase();'."\n";
try {
        $result = $client->createDatabase();
} catch (Exception $e) {
        if ( $e instanceof couchException ) {
                echo "We issued the request, but couch server returned an error.\n";
                echo "We can have HTTP Status code returned by couchDB using \$e->getCode() : ". $e->getCode()."\n";
                echo "We can have error message returned by couchDB using \$e->getMessage() : ". $e->getMessage()."\n";
                echo "Finally, we can have CouchDB's complete response body using \$e->getBody() : ". print_r($e->getBody(),true)."\n";
		echo "Are you sure that your CouchDB server is at $couch_dsn, and that database $couch_db does not exist ?\n";
                exit (1);
        } else {
                echo "It seems that something wrong happened. You can have more details using :\n";
                echo "the exception class with get_class(\$e) : ".get_class($e)."\n";
                echo "the exception error code with \$e->getCode() : ".$e->getCode()."\n";
                echo "the exception error message with \$e->getMessage() : ".$e->getMessage()."\n";
                exit (1);
        }
}
echo "Database successfully created. CouchDB sent the response :".print_r($result,true)."\n";

?>