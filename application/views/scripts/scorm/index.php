<?php
session_start();
/*    require_once("./custom/include/db_connect.php");  
    
    $path_db="./db.ini";
	db_connect($path_db);*/

require_once('functions.php');
require_once('common.php');

if (isset($args['verb'])) {
	switch ($args['verb']) {

		case 'GetRecord':
			
			include 'scorm2/getrecord.php';
			break;
                    case 'Search':
			
			include 'scorm2/listidentifiers.php';
			break;
                    
 		default:
			// we never use compression with errors
			$compress = FALSE;
			$errors .= oai_error('badVerb', $args['verb']);
	} /*switch */

} else {
	$errors .= oai_error('noVerb');
        $args['verb']='';
}



//header($CONTENT_TYPE);
if($_GET['verb']=='GetRecordOnlyLom'){

echo $output;
echo $errors;

}else{
  echo $xmlheader;
echo $output;
echo $errors;

if($args['verb']==='GetRecord'){ 
scorm_close();   
}
}

?>


