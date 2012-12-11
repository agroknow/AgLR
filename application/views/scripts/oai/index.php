<?php
session_start();
/*    require_once("./custom/include/db_connect.php");  
    
    $path_db="./db.ini";
	db_connect($path_db);*/

require_once('functions.php');
require_once('common.php');

if (isset($args['verb'])) {
	switch ($args['verb']) {

		case 'Identify':
			unset($args['verb']);
			// we never use compression in Identify
			$compress = FALSE;
			include 'oai2/identify.php';
			break;
		
		case 'ListRecords':
			unset($args['verb']);
			include 'oai2/listrecords.php';
			break;
			
		case 'GetRecord':
			unset($args['verb']);
			include 'oai2/getrecord.php';
			break;
                    
                case 'GetRecordOnlyLom':
			unset($args['verb']);
			include 'oai2/getrecordonlylom.php';
			break;
			
		case 'ListSets':
			unset($args['verb']);
			include 'oai2/listsets.php';
			break;
			
		case 'ListMetadataFormats':
			unset($args['verb']);
			include 'oai2/listmetadataformats.php';
			break;
			
		case 'ListIdentifiers':
			unset($args['verb']);
			include 'oai2/listidentifiers.php';
			break;

		default:
			// we never use compression with errors
			$compress = FALSE;
			$errors .= oai_error('badVerb', $args['verb']);
	} /*switch */

} else {
	$errors .= oai_error('noVerb');
}



//header($CONTENT_TYPE);
if($_GET['verb']=='GetRecordOnlyLom'){

echo $output;
echo $errors;

}else{
  echo $xmlheader;
echo $request;
echo $output;
echo $errors;
oai_close();   
}

?>


