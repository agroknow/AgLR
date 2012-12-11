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

$MY_URI = 'http://' . $_SERVER['SERVER_NAME'] . '' . uri('oai');

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

// define all supported sets in your repository
$collections_query = "select * from omeka_collections where public=1";
$exec_collection = $db->query($collections_query);
$row_collections = $exec_collection->fetchAll();
$string_collections = "";
$SETS = array();
foreach ($row_collections as $value) {
    $SETS[] = array('setSpec' => 'aglr_collection_' . $value['id'], 'setName' => $value['name'] . '');
}
//$SETS = 	array ( 
//array('setSpec'=>'phdthesis', 'setName'=>'PHD Thesis', 'setDescription'=>'') ,
//array('setSpec'=>'math', 'setName'=>'Mathematics') //,
// array('setSpec'=>'phys', 'setName'=>'Physics') 
//			);
// define all supported metadata formats
//
// myhandler is the name of the file that handles the request for the 
// specific metadata format.
// [record_prefix] describes an optional prefix for the metadata
// [record_namespace] describe the namespace for this prefix

$METADATAFORMATS = array(
    'oai_lom' => array('metadataPrefix' => 'oai_lom',
        'schema' => 'http://www.openarchives.org/OAI/2.0/oai_lom.xsd',
        'metadataNamespace' => 'http://www.openarchives.org/OAI/2.0/oai_lom/',
        'myhandler' => 'record_dc.php',
        'record_prefix' => 'lom',
        'record_namespace' => 'http://purl.org/dc/elements/1.1/'
    ) /* ,

          array('metadataPrefix'=>'olac',
          'schema'=>'http://www.language-archives.org/OLAC/olac-2.0.xsd',
          'metadataNamespace'=>'http://www.openarchives.org/OLAC/0.2/',
          'handler'=>'record_olac.php'
          ) */
);


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



// and now we make the OAI Repository Explorer really happy
// I have not found any way to check this for POST requests.
if (isset($getarr)) {
    if (count($getarr) != count($args)) {
        $errors .= oai_error('sameArgument');
    }
}

$reqattr = '';
if (is_array($args)) {
    foreach ($args as $key => $val) {
        $reqattr .= ' ' . $key . '="' . htmlspecialchars(stripslashes($val)) . '"';
    }
}

// in case register_globals is on, clean up polluted global scope
$verbs = array('from', 'identifier', 'metadataPrefix', 'set', 'resumptionToken', 'until');
foreach ($verbs as $val) {
    unset($$val);
}

$request = ' <request' . $reqattr . '>' . $MY_URI . "</request>\n";
$request_err = ' <request>' . $MY_URI . "</request>\n";

// Current Date
$datetime = gmstrftime('%Y-%m-%dT%T');
$responseDate = $datetime . 'Z';

$datetime_resum2 = gmstrftime('%Y-%m-%d'); //for expire date of the resumptionToken
$datetime_resum = gmstrftime('%Y%m%d'); //for creation and checking of resumptionToken
// do not change
/*$XMLHEADER =
        '<?xml version="1.0" encoding="UTF-8"?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"  xmlns:lom="http://ltsc.ieee.org/xsd/LOM" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">' . "\n";
*/
$XMLHEADER =
        '<?xml version="1.0" encoding="UTF-8"?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">' . "\n";


$xmlheader = $XMLHEADER .
        ' <responseDate>' . $responseDate . "</responseDate>\n";

// the xml schema namespace, do not change this
$XMLSCHEMA = 'http://www.w3.org/2001/XMLSchema-instance';
?>