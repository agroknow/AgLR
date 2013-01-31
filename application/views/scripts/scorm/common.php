<?php

require_once 'Omeka/Core.php';
$core = new Omeka_Core;

try {
    $db = $core->getDb();

    //Force the Zend_Db to make the connection and catch connection errors
    try {
        $mysqli = $db->getConnection()->getConnection();
    } catch (Exception $e) {
        throw new Exception("<h1>MySQL connection error: [" . mysqli_connect_errno() . "]</h1>" . "<p>" . $e->getMessage() . '</p>');
    }
} catch (Exception $e) {
    die($e->getMessage() . '<p>Please refer to <a href="http://omeka.org/codex/">Omeka documentation</a> for help.</p>');
}

$MY_URI = 'http://' . $_SERVER['SERVER_NAME'] . '' . uri('scorm');

$CONTENT_TYPE = 'Content-type: text/xml; charset=utf-8';

// MUST (only one)
// please adjust

$lastExhibitIdSQL = "select * from omeka_options where id=102";
$exec = $db->query($lastExhibitIdSQL);
$row_item = $exec->fetch();

$repositoryName = $row_item['value'];
$baseURL = $MY_URI;

$protocolVersion = '2.0';

// How your repository handles deletions
// no: 			The repository does not maintain status about deletions.
//				It MUST NOT reveal a deleted status.
// persistent:	The repository persistently keeps track about deletions 
//				with no time limit. It MUST consistently reveal the status
//				of a deleted record over time.
// transient:   The repository does not guarantee that a list of deletions is 
//				maintained. It MAY reveal a deleted status for records.
// 
// If your database keeps track of deleted records change accordingly.
// Currently if $record['deleted'] is set to 'true', $status_deleted is set.
// Some lines in listidentifiers.php, listrecords.php, getrecords.php  
// must be changed to fit the condition for your database.
$deletedRecord = 'no';

// MAY (only one)
//granularity is days
//$granularity          = 'YYYY-MM-DD';
// granularity is seconds
$granularity = 'YYYY-MM-DDThh:mm:ssZ';

// MUST (only one)
// the earliest datestamp in your repository,
// please adjust
$earliestDatestamp = '2000-01-01';

// this is appended if your granularity is seconds.
// do not change
if ($granularity == 'YYYY-MM-DDThh:mm:ssZ') {
    $earliestDatestamp .= 'T00:00:00Z';
}

// MUST (multiple)
// please adjust

$lastExhibitIdSQL = "select * from omeka_options where id=213";
$exec = $db->query($lastExhibitIdSQL);
$row_item = $exec->fetch();
$adminEmail = array($row_item['value']);

// MAY (multiple) 
// Comment out, if you do not want to use it.
// Currently only gzip is supported (you need output buffering turned on, 
// and php compiled with libgz). 
// The client MUST send "Accept-Encoding: gzip" to actually receive 
// compressed output.
$compression = array('gzip');

// MUST (only one)
// should not be changed
$delimiter = ':';


// MUST (only one)
// You may choose any name, but for repositories to comply with the oai 
// format for unique identifiers for items records. 
// see: http://www.openarchives.org/OAI/2.0/guidelines-oai-identifier.htm
// Basically use domainname-word.domainname
// please adjust
$repositoryIdentifier = $_SERVER['SERVER_NAME'] . '' . uri('');
$sampleIdentifier = 'oai:' . $_SERVER['SERVER_NAME'] . ':1';


// description is defined in identify.php 
$show_identifier = true;

// You may include details about your community and friends (other
// data-providers).
// Please check identify.php for other possible containers 
// in the Identify response
// maximum mumber of the records to deliver
// (verb is ListRecords)
// If there are more records to deliver
// a ResumptionToken will be generated.
$MAXRECORDS = 50;

// maximum mumber of identifiers to deliver
// (verb is ListIdentifiers)
// If there are more identifiers to deliver
// a ResumptionToken will be generated.
$MAXIDS = 100;

// After 24 hours resumptionTokens become invalid.
$tokenValid = 24 * 3600;
$expirationdatetime = gmstrftime('%Y-%m-%dT%TZ', time() + $tokenValid);




$output = '';
$errors = '';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $args = $_GET;
    $getarr = explode('&', $_SERVER['QUERY_STRING']);
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $args = $_POST;
} else {
    $errors .= oai_error('badRequestMethod', $_SERVER['REQUEST_METHOD']);
}





// the xml schema namespace, do not change this

?>