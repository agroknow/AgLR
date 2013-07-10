<?php 

if ($handle = opendir('/var/www/coe-xmls/')) {
      /* This is the correct way to loop over the directory. */
    while (false !== ($entry = readdir($handle))) {
	if($entry!='.' and $entry!='..'){
  
//$item_id='item_id';
$xml ='';
$output='';
libxml_use_internal_errors(false);
//$entry='Equality, inclusion, HRE - Helping children with special needs.xml';
$xml = @simplexml_load_file('http://education.natural-europe.eu/coe-xmls/'.$entry.'', NULL, LIBXML_NOERROR | LIBXML_NOWARNING);
if($xml === false){ echo "An Error occured. Please try again later. Thank you!";}
//$xml = simplexml_load_file('http://ariadne.cs.kuleuven.be/ariadne-partners/api/sqitarget?query=learning&start='.$startPage.'&size=12&lang=plql1&format=lom', NULL, LIBXML_NOERROR | LIBXML_NOWARNING);
if($xml){

$item_id=insertnewitemfromxml($xml);

$xml->getName();
	foreach($xml as $xml){
	$xmlname=$xml->getName();
	
								if($xmlname=='general'){
								
									 		$multi=0;
											$previous_getgeneralname='';
											 foreach($xml->children() as $getgeneral){
													$getgeneralname=$getgeneral->getName();
													if($getgeneralname!=$previous_getgeneralname){$multi=0;}
													
													
													if($getgeneralname=='identifier'){ 
													$multi+=1;
													$i=1;
													savelomelementforxmlparsing('53','Parent Element',$item_id,'none',$i,$multi);
														foreach($getgeneral as $string){ 
														//$i+=1;
														$stringname=$string->getName();
														if($stringname=='catalog'){savelomelementforxmlparsing('54',$string,$item_id,'none',$i,$multi);}
														if($stringname=='entry'){savelomelementforxmlparsing('55',$string,$item_id,'none',$i,$multi);}
														
														//catalog-entry
														 }
													} //identifier
													
													if($getgeneralname=='title'){ 
													$multi+=1;
													$i=1;
														foreach($getgeneral as $string){ 
														//$i+=1;
														savelomelementforxmlparsing('6',$string,$item_id,$string['language'],$i,$multi);
														//echo $string."-".$string['language']."<br>";
														 }
													} //if($getgeneralname=='title'){ 
													
													if($getgeneralname=='language'){ 
													$multi+=1;
													$i=0;
														$i+=1;
														savelomelementforxmlparsing('7',$getgeneral,$item_id,'none',$i,$multi);
													} //if($getgeneralname=='title'){ 
													
													if($getgeneralname=='description'){ 
													$multi+=1;
													$i=1;
														foreach($getgeneral as $string){ 
														//$i+=1;
														savelomelementforxmlparsing('8',$string,$item_id,$string['language'],$i,$multi);
														//echo $string."-".$string['language']."<br>";
														 }
													} //if($getgeneralname=='title'){ 
													if($getgeneralname=='keyword'){ 
													$multi+=1;
													$i=1;
														foreach($getgeneral as $string){ 
														//$i+=1;
														savelomelementforxmlparsing('35',$string,$item_id,$string['language'],$i,$multi);
														//echo $string."-".$string['language']."<br>";
														 }
													} //if($getgeneralname=='title'){ 
													
													if($getgeneralname=='coverage'){ 
													$multi+=1;
													$i=0;
														foreach($getgeneral as $string){ 
														$i+=1;
														savelomelementforxmlparsing('78',$string,$item_id,'none',$i,$multi);
														//echo $string."-".$string['language']."<br>";
														 }
													} //if($getgeneralname=='title'){ 
													
													if($getgeneralname=='structure'){ 
													$multi+=1;
													$i=0;
													//print_r($getgeneral);
														foreach($getgeneral->value as $string){ 
														$i+=1;
														savelomelementforxmlparsing('56',$string,$item_id,'none',$i,$multi);
														//echo $string."-".$string['language']."<br>";
														 }
													} //if($getgeneralname=='title'){ 
													
													if($getgeneralname=='aggregationLevel'){ 
													$multi+=1;
													$i=0;
													//print_r($getgeneral);
														foreach($getgeneral->value as $string){ 
														$i+=1;
														savelomelementforxmlparsing('57',$string,$item_id,'none',$i,$multi);
														//echo $string."-".$string['language']."<br>";
														 }
													} //if($getgeneralname=='title'){ 
													
											
													
													
													

									 			$previous_getgeneralname=$getgeneralname;
									 			} // foreach($xml->children() as $getgeneral){
								}//if name-general
								
								
								
								if($xmlname=='lifeCycle'){
								echo "<br><br>";
									 		$multi=0;
											$previous_getgeneralname='';
											 foreach($xml->children() as $getgeneral){
													$getgeneralname=$getgeneral->getName();
													if($getgeneralname!=$previous_getgeneralname){$multi=0;}
													
													
													
													if($getgeneralname=='version'){ 
													$multi+=1;
													$i=1;
														foreach($getgeneral as $string){ 
														//$i+=1;
														savelomelementforxmlparsing('82',$string,$item_id,$string['language'],$i,$multi);
														//echo $string."-".$string['language']."<br>";
														 }
													} //if($getgeneralname=='title'){ 
													
													if($getgeneralname=='status'){ 
													$multi+=1;
													$i=0;
													//print_r($getgeneral);
														foreach($getgeneral->value as $string){ 
														$i+=1;
														savelomelementforxmlparsing('41',$string,$item_id,'none',$i,$multi);
														//echo $string."-".$string['language']."<br>";
														 }
													} //if($getgeneralname=='title'){ 
													
													
													if($getgeneralname=='contribute'){ 
													$multi+=1;
													$i=0;
													savelomelementforxmlparsing('50','Parent Element',$item_id,'none','1',$multi);
													savelomelementforxmlparsing('51',$getgeneral->role->value,$item_id,'none','1',$multi);
													savelomelementforxmlparsing('58',$getgeneral->date->dateTime,$item_id,'none','1',$multi);
													
														foreach($getgeneral->entity as $string){ 
														$i+=1;
													if(isset($string)){
													//print($string)."<br>";
													$vcardstart=explode("VERSION:3.0",$string);
													$vcard=$vcardstart[1];
													$vcardstart=explode("END:VCARD",$vcard);
													$vcard=$vcardstart[0];
													
													if(stripos($vcard,"ORG:")){
													$vcard=explode("ORG:",$vcard);
													$org=$vcard[1]; echo $org."<br>";
													$vcard=$vcard[0];
													}//if isset org: 
													else{$org="";}
													
													if(stripos($vcard,"EMAIL;TYPE=INTERNET:")){
													$vcard=explode("EMAIL;TYPE=INTERNET:",$vcard);
													$email=$vcard[1]; echo $email."<br>";
													$vcard=$vcard[0];
													}//if isset email:
													else{$email="";}
													
													if(stripos($vcard,"FN:")){
													$vcard=explode("FN:",$vcard);
													$fullname=$vcard[1]; echo $fullname."<br>";
													$vcard=$vcard[0];
													}//if isset fn:
													else{$fullname="";}
													
													if(stripos($vcard,"N:")){
													$vcard=explode("N:",$vcard);
													$name=$vcard[1];
													$vcard=$vcard[0];
													$entity=explode(";",$name); 
													if(isset($entity['3'])){$name=$entity['3'];}else{$name="";}
													if(isset($entity['1'])){$name.=$entity['1'];}else{$name.="";}
													if(isset($entity['0'])){$surname=$entity['0'];}else{$surname="";}
													// echo $name."<br>";
													// echo $surname."<br>";
														
													}//if isset entity:
													else{$name="";}

													}
													vcardinsert('52','',$item_id,'none',$i,$multi,$name,$surname,$email,$org);
														//savelomelementforxmlparsing('41',$string,$item_id,'none',$i,$multi);
														//echo $string."-".$string['language']."<br>";
														 }
													} //contribute
													
											
													
													
													

									 			$previous_getgeneralname=$getgeneralname;
									 			} // foreach($xml->children() as $getgeneral){
								}//if name-lifeCycle
								
								if($xmlname=='metaMetadata'){
									echo "<br><br>";
									 		$multi=0;
											$previous_getgeneralname='';
											 foreach($xml->children() as $getgeneral){
													$getgeneralname=$getgeneral->getName();
													if($getgeneralname!=$previous_getgeneralname){$multi=0;}
													
													
													if($getgeneralname=='identifier'){ 
													$multi+=1;
													$i=1;
													savelomelementforxmlparsing('60','Parent Element',$item_id,'none',$i,$multi);
														foreach($getgeneral as $string){ 
														//$i+=1;
														$stringname=$string->getName();
														if($stringname=='catalog'){savelomelementforxmlparsing('61',$string,$item_id,'none',$i,$multi);}
														if($stringname=='entry'){savelomelementforxmlparsing('62',$string,$item_id,'none',$i,$multi);}
														
														//catalog-entry
														 }
													} //identifier
													
													if($getgeneralname=='contribute'){ 
													$multi+=1;
													$i=0;
													savelomelementforxmlparsing('63','Parent Element',$item_id,'none','1',$multi);
													savelomelementforxmlparsing('64',$getgeneral->role->value,$item_id,'none','1',$multi);
													savelomelementforxmlparsing('66',$getgeneral->date->dateTime,$item_id,'none','1',$multi);
													
														foreach($getgeneral->entity as $string){ 
														$i+=1;
													if(isset($string)){
													//print($string)."<br>";
													$vcardstart=explode("VERSION:3.0",$string);
													$vcard=$vcardstart[1];
													$vcardstart=explode("END:VCARD",$vcard);
													$vcard=$vcardstart[0];
													
													if(stripos($vcard,"ORG:")){
													$vcard=explode("ORG:",$vcard);
													$org=$vcard[1]; echo $org."<br>";
													$vcard=$vcard[0];
													}//if isset org: 
													else{$org="";}
													
													if(stripos($vcard,"EMAIL;TYPE=INTERNET:")){
													$vcard=explode("EMAIL;TYPE=INTERNET:",$vcard);
													$email=$vcard[1]; echo $email."<br>";
													$vcard=$vcard[0];
													}//if isset email:
													else{$email="";}
													
													if(stripos($vcard,"FN:")){
													$vcard=explode("FN:",$vcard);
													$fullname=$vcard[1]; echo $fullname."<br>";
													$vcard=$vcard[0];
													}//if isset fn:
													else{$fullname="";}
													
													if(stripos($vcard,"N:")){
													$vcard=explode("N:",$vcard);
													$name=$vcard[1];
													$vcard=$vcard[0];
													$entity=explode(";",$name); 
													if(isset($entity['3'])){$name=$entity['3'];}else{$name="";}
													if(isset($entity['1'])){$name.=$entity['1'];}else{$name.="";}
													if(isset($entity['0'])){$surname=$entity['0'];}else{$surname="";}
													// echo $name."<br>";
													// echo $surname."<br>";
														
													}//if isset entity:
													else{$name="";}

													}
													vcardinsert('65','',$item_id,'none',$i,$multi,$name,$surname,$email,$org);
														//savelomelementforxmlparsing('41',$string,$item_id,'none',$i,$multi);
														//echo $string."-".$string['language']."<br>";
														 }
													} //contribute
													
													
													if($getgeneralname=='metadataSchema'){ 
													$multi+=1;
													$i=0;
														$i+=1;
														savelomelementforxmlparsing('67',$getgeneral,$item_id,'none',$i,$multi);
													} //if($getgeneralname=='title'){ 
													
													
													if($getgeneralname=='language'){ 
													$multi+=1;
													$i=0;
														$i+=1;
														savelomelementforxmlparsing('68',$getgeneral,$item_id,'none',$i,$multi);
													} //if($getgeneralname=='title'){ 


									 			$previous_getgeneralname=$getgeneralname;
									 			} // foreach($xml->children() as $getgeneral){
								}//if name-metaMetadata
								
								if($xmlname=='technical'){
									echo "<br><br>";
									 		$multi=0;
											$previous_getgeneralname='';
											 foreach($xml->children() as $getgeneral){
													$getgeneralname=$getgeneral->getName();
													if($getgeneralname!=$previous_getgeneralname){$multi=0;}
													
													
													
													if($getgeneralname=='format'){ 
													$multi+=1;
													$i=0;
														$i+=1;
														savelomelementforxmlparsing('33',$getgeneral,$item_id,'none',$i,$multi);
													} //if($getgeneralname=='title'){ 
													
													
													if($getgeneralname=='location'){ 
													$multi+=1;
													$i=0;
														$i+=1;
														savelomelementforxmlparsing('32',$getgeneral,$item_id,'none',$i,$multi);
													} //if($getgeneralname=='title'){ 


									 			$previous_getgeneralname=$getgeneralname;
									 			} // foreach($xml->children() as $getgeneral){
								}//if name-technical
								
								if($xmlname=='educational'){
									echo "<br><br>";
									 		$multi=0;
											$previous_getgeneralname='';
											 foreach($xml->children() as $getgeneral){
													$getgeneralname=$getgeneral->getName();
													if($getgeneralname!=$previous_getgeneralname){$multi=0;}
													
																										
													if($getgeneralname=='interactivityType'){ 
													$multi+=1;
													$i=0;
													//print_r($getgeneral);
														foreach($getgeneral->value as $string){ 
														$i+=1;
														savelomelementforxmlparsing('36',$string,$item_id,'none',$i,$multi);
														//echo $string."-".$string['language']."<br>";
														 }
													} //if($getgeneralname=='title'){ 
													
													if($getgeneralname=='learningResourceType'){ 
													$multi+=1;
													$i=0;
													//print_r($getgeneral);
														foreach($getgeneral->value as $string){ 
														$i+=1;
														savelomelementforxmlparsing('11',$string,$item_id,'none',$i,$multi);
														//echo $string."-".$string['language']."<br>";
														 }
													} //if($getgeneralname=='title'){ 
													
													if($getgeneralname=='interactivityLevel'){ 
													$multi+=1;
													$i=0;
													//print_r($getgeneral);
														foreach($getgeneral->value as $string){ 
														$i+=1;
														savelomelementforxmlparsing('37',$string,$item_id,'none',$i,$multi);
														//echo $string."-".$string['language']."<br>";
														 }
													} //if($getgeneralname=='title'){ 
													
													if($getgeneralname=='semanticDensity'){ 
													$multi+=1;
													$i=0;
													//print_r($getgeneral);
														foreach($getgeneral->value as $string){ 
														$i+=1;
														savelomelementforxmlparsing('38',$string,$item_id,'none',$i,$multi);
														//echo $string."-".$string['language']."<br>";
														 }
													} //if($getgeneralname=='title'){ 
													
													
													if($getgeneralname=='intendedEndUserRole'){ 
													$multi+=1;
													$i=0;
													//print_r($getgeneral);
														foreach($getgeneral->value as $string){ 
														$i+=1;
														savelomelementforxmlparsing('12',$string,$item_id,'none',$i,$multi);
														//echo $string."-".$string['language']."<br>";
														 }
													} //if($getgeneralname=='title'){ 
													
													if($getgeneralname=='context'){ 
													$multi+=1;
													$i=0;
													//print_r($getgeneral);
														foreach($getgeneral->value as $string){ 
														$i+=1;
														savelomelementforxmlparsing('13',$string,$item_id,'none',$i,$multi);
														//echo $string."-".$string['language']."<br>";
														 }
													} //if($getgeneralname=='title'){
													
													if($getgeneralname=='difficulty'){ 
													$multi+=1;
													$i=0;
													//print_r($getgeneral);
														foreach($getgeneral->value as $string){ 
														$i+=1;
														savelomelementforxmlparsing('26',$string,$item_id,'none',$i,$multi);
														//echo $string."-".$string['language']."<br>";
														 }
													} //if($getgeneralname=='title'){
													
														
													if($getgeneralname=='typicalAgeRange'){ 
													$multi+=1;
													$i=1;
														foreach($getgeneral as $string){ 
														//$i+=1;
														$string['language']="en"; //for coe 200 xmls!!
														savelomelementforxmlparsing('14',$string,$item_id,$string['language'],$i,$multi);
														//echo $string."-".$string['language']."<br>";
														 }
													} //if($getgeneralname=='title'){ 
													
													if($getgeneralname=='language'){ 
													$multi+=1;
													$i=1;
													savelomelementforxmlparsing('83',$getgeneral,$item_id,'none',$i,$multi);
													} //if($getgeneralname=='title'){
													
													if($getgeneralname=='description'){ 
													$multi+=1;
													$i=0;
														foreach($getgeneral as $string){ 
														$i+=1;
														savelomelementforxmlparsing('39',$string,$item_id,$string['language'],$i,$multi);
														//echo $string."-".$string['language']."<br>";
														 }
													} //if($getgeneralname=='title'){ 
													


									 			$previous_getgeneralname=$getgeneralname;
									 			} // foreach($xml->children() as $getgeneral){
								}//if name-educational
								
								if($xmlname=='rights'){
									echo "<br><br>";
									 		$multi=0;
											$previous_getgeneralname='';
											 foreach($xml->children() as $getgeneral){
													$getgeneralname=$getgeneral->getName();
													if($getgeneralname!=$previous_getgeneralname){$multi=0;}
													
													
													
													if($getgeneralname=='cost'){ 
													$multi+=1;
													$i=0;
													//print_r($getgeneral);
														foreach($getgeneral->value as $string){ 
														$i+=1;
														savelomelementforxmlparsing('24',$string,$item_id,'none',$i,$multi);
														//echo $string."-".$string['language']."<br>";
														 }
													} //if($getgeneralname=='title'){ 
													
													if($getgeneralname=='copyrightAndOtherRestrictions'){ 
													$multi+=1;
													$i=0;
													//print_r($getgeneral);
														foreach($getgeneral->value as $string){ 
														$i+=1;
														savelomelementforxmlparsing('9',$string,$item_id,'none',$i,$multi);
														//echo $string."-".$string['language']."<br>";
														 }
													} //if($getgeneralname=='title'){
													
													if($getgeneralname=='description'){ 
													$multi+=1;
													$i=1;
														foreach($getgeneral as $string){ 
														//$i+=1;
														savelomelementforxmlparsing('81',$string,$item_id,$string['language'],$i,$multi);
														//echo $string."-".$string['language']."<br>";
														 }
													} //if($getgeneralname=='title'){ 
													


									 			$previous_getgeneralname=$getgeneralname;
									 			} // foreach($xml->children() as $getgeneral){
								}//if name-rights
								
								if($xmlname=='relation'){
									echo "<br><br>";
									 		$multi=0;
											$previous_getgeneralname='';
											 foreach($xml->children() as $getgeneral){
													$getgeneralname=$getgeneral->getName();
													if($getgeneralname!=$previous_getgeneralname){$multi=0;}
													
													
													
													if($getgeneralname=='kind'){ 
													$multi+=1;
													$i=0;
													//print_r($getgeneral);
														foreach($getgeneral->value as $string){ 
														$i+=1;
														savelomelementforxmlparsing('69',$string,$item_id,'none',$i,$multi);
														//echo $string."-".$string['language']."<br>";
														 }
													} //if($getgeneralname=='title'){ 
													
													if($getgeneralname=='resource'){ 
													$multi+=1;
													$i=1;
													savelomelementforxmlparsing('70','Parent Element',$item_id,'none',$i,$multi);
													//print_r($getgeneral);
														foreach($getgeneral as $string){ 
														
														foreach($string as $string){ 
														
														//$i+=1;
														echo $stringname=$string->getName();
														if($stringname=='catalog'){savelomelementforxmlparsing('71',$string,$item_id,'none',$i,$multi);}
														if($stringname=='entry'){savelomelementforxmlparsing('72',$string,$item_id,'none',$i,$multi);}
														if($stringname=='string'){														
														savelomelementforxmlparsing('73',$string,$item_id,$string['language'],$i,$multi);}
							
														}//string identifier
	
														
														//catalog-entry
														 }
													} //identifier
	

									 			$previous_getgeneralname=$getgeneralname;
									 			} // foreach($xml->children() as $getgeneral){
								}//if name-relation
								
								if($xmlname=='annotation'){
									echo "<br><br>";
									 		$multi=0;
											$previous_getgeneralname='';
											 foreach($xml->children() as $getgeneral){
													$getgeneralname=$getgeneral->getName();
													if($getgeneralname!=$previous_getgeneralname){$multi=0;}
													
													if($getgeneralname=='description'){ 
													$multi+=1;
													$i=1;
														foreach($getgeneral as $string){ 
														//$i+=1;
														savelomelementforxmlparsing('76',$string,$item_id,$string['language'],$i,$multi);
														//echo $string."-".$string['language']."<br>";
														 }
													} //if($getgeneralname=='title'){ 
													
													if($getgeneralname=='date'){ 
													$multi+=1;
													$i=1;
														foreach($getgeneral as $string){ 
														//$i+=1;
														savelomelementforxmlparsing('75',$string,$item_id,'none',$i,$multi);
														//echo $string."-".$string['language']."<br>";
														 }
													} //if($getgeneralname=='title'){ 
													
													if($getgeneralname=='entity'){ 
													$multi+=1;
													$i=0;
													//print($getgeneral);
													$string=$getgeneral;
														$i+=1;
													if(isset($string)){
													//print($string)."<br>";
													$vcardstart=explode("VERSION:3.0",$string);
													$vcard=$vcardstart[1];
													$vcardstart=explode("END:VCARD",$vcard);
													$vcard=$vcardstart[0];
													
													if(stripos($vcard,"ORG:")){
													$vcard=explode("ORG:",$vcard);
													$org=$vcard[1]; echo $org."<br>";
													$vcard=$vcard[0];
													}//if isset org: 
													else{$org="";}
													
													if(stripos($vcard,"EMAIL;TYPE=INTERNET:")){
													$vcard=explode("EMAIL;TYPE=INTERNET:",$vcard);
													$email=$vcard[1]; echo $email."<br>";
													$vcard=$vcard[0];
													}//if isset email:
													else{$email="";}
													
													if(stripos($vcard,"FN:")){
													$vcard=explode("FN:",$vcard);
													$fullname=$vcard[1]; echo $fullname."<br>";
													$vcard=$vcard[0];
													}//if isset fn:
													else{$fullname="";}
													
													if(stripos($vcard,"N:")){
													$vcard=explode("N:",$vcard);
													$name=$vcard[1];
													$vcard=$vcard[0];
													$entity=explode(";",$name); 
													if(isset($entity['3'])){$name=$entity['3'];}else{$name="";}
													if(isset($entity['1'])){$name.=$entity['1'];}else{$name.="";}
													if(isset($entity['0'])){$surname=$entity['0'];}else{$surname="";}
													// echo $name."<br>";
													// echo $surname."<br>";
														
													}//if isset entity:
													else{$name="";}

													//}
													vcardinsert('74','',$item_id,'none',$i,$multi,$name,$surname,$email,$org);
													
														//savelomelementforxmlparsing('41',$string,$item_id,'none',$i,$multi);
														//echo $string."-".$string['language']."<br>";
														 }
													} //contribute


									 			$previous_getgeneralname=$getgeneralname;
									 			} // foreach($xml->children() as $getgeneral){
								}//if name-annotation
								
								if($xmlname=='classification'){
									echo "<br><br>";
									 		$multi=0;
											$previous_getgeneralname='';
											 foreach($xml->children() as $getgeneral){
													$getgeneralname=$getgeneral->getName();
													if($getgeneralname!=$previous_getgeneralname){$multi=0;}
													
													


									 			$previous_getgeneralname=$getgeneralname;
									 			} // foreach($xml->children() as $getgeneral){
								}//if name-classification
	
	
	}
}




	}//if($entry!='.' and $entry!='..'){
    }//while uparxoun arxei ston fakelo
    closedir($handle);
} //close handle gia arxeia


function insertnewitemfromxml($xml){

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


$itemtdb=$db->Items;

$maxIdSQL="SELECT MAX(id) AS MAX_ID FROM ". $itemtdb." LIMIT 0,1";
$exec=$db->query($maxIdSQL);
$row=$exec->fetch();
$max_id=$row["MAX_ID"];
$exec=null;

if(strlen($_POST['title'])>0){$_POST['Elements']['68']['0']['text']=$_POST['title'];}
if(strlen($_POST['Elements']['68']['0']['text'])>0){$path_title=addslashes($_POST['Elements']['68']['0']['text']);} else{$path_title="resource-title-".$max_id.""; $_POST['Elements']['68']['0']['text']="resource-title-".$max_id."";}
if($_POST['description']){$path_description=addslashes($_POST['description']);} else{$path_description="";}
if($_POST['link']){$path_url=$_POST['link'];} else{$path_url="";}

//if($_POST['Elements']['68']['0']['text']){$path_title=addslashes($_POST['Elements']['68']['0']['text']);} else{$path_title="resource-title-".$max_id."";}

if($_POST['public']){$path_public=$_POST['public'];} else{$path_public="0";}
//print($xml->general->title->string);
//print($xml->technical->format);
$type=$xml->technical->format;
$path_title=$xml->general->title->string;
if($type=='text/html'){$formtype==11;}
if(stripos(' '.$type,"image")>0){$formtype=6;}else{$formtype=20;}


$date_modified = date("Y-m-d H:i:s");
$mainAttributesSql="INSERT INTO $itemtdb (featured,item_type_id,public,modified,added) VALUES (0,".$formtype.",'".$path_public."','".$date_modified."','".$date_modified."')";
//echo $mainAttributesSql; break;
$db->exec($mainAttributesSql);

$lastExhibitIdSQL="SELECT LAST_INSERT_ID() AS LAST_EXHIBIT_ID FROM ". $itemtdb;
$exec=$db->query($lastExhibitIdSQL);
$row=$exec->fetch();
$last_exhibit_id=$row["LAST_EXHIBIT_ID"];
$exec=null;

$entitiesRelationsdb=$db->EntitiesRelations;
$entity_id = current_user();
$entitiesRelationsSql="INSERT INTO ".$entitiesRelationsdb." (entity_id, relation_id, relationship_id, type, time) VALUES (1, ".$last_exhibit_id.",1,'Item','".date("Y-m-d H:i:s")."')";
$exec=$db->query($entitiesRelationsSql);

$path_title=htmlspecialchars($path_title);
$path_title=addslashes($path_title);
//$path_description=htmlspecialchars($path_description);
//$path_description=addslashes($path_description);
//$path_url=htmlspecialchars($path_url);
//$path_url=addslashes($path_url);


$mainAttributesSql="INSERT INTO omeka_element_texts (record_id ,record_type_id ,element_id,text) VALUES (".$last_exhibit_id.",2,68,'".$path_title."')";
//echo $mainAttributesSql;break;
$db->exec($mainAttributesSql);

$metadatarecordSql="INSERT INTO metadata_record (id, object_id, object_type,date_modified) VALUES ('', ".$last_exhibit_id.",'item','".$date_modified."')";
$execmetadatarecordSql=$db->query($metadatarecordSql);


$lastExhibitIdSQL="SELECT LAST_INSERT_ID() AS LAST_EXHIBIT_ID FROM metadata_record";
$exec=$db->query($lastExhibitIdSQL);
$row=$exec->fetch();
$last_record_id=$row["LAST_EXHIBIT_ID"];
$exec=null;


return $last_record_id;

}

function savelomelementforxmlparsing($element_hierarchy,$value,$item_id,$language,$parent_indexer=1,$multi=1){

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


$value=htmlspecialchars($value);
$value=addslashes($value);
$maxIdSQL="insert into metadata_element_value SET element_hierarchy=".$element_hierarchy.",value='".$value."',language_id='".$language."',record_id=".$item_id.",multi=".$multi.",parent_indexer=".$parent_indexer." ON DUPLICATE KEY UPDATE value='".$value."'";
$maxSql=$db->query($maxIdSQL);
$maxSql=null;
//echo $maxIdSQL."<br>";

}

function vcardinsert($element_hierarchy,$value,$item_id,$language,$parent_indexer=1,$multi=1,$vcard_name,$vcard_surname,$vcard_email,$vcard_organization){
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
$vcard_name=addslashes(htmlspecialchars($vcard_name));
				$vcard_surname=addslashes(htmlspecialchars($vcard_surname));
				$vcard_email=addslashes(htmlspecialchars($vcard_email));
				$vcard_organization=addslashes(htmlspecialchars($vcard_organization));
				
				if(strlen($vcard_name)>0 or strlen($vcard_surname)>0 or strlen($vcard_email)>0 or strlen($vcard_organization)>0){

$chechvcard="select * from metadata_vcard WHERE name='".$vcard_name."' and surname='".$vcard_surname."' and email='".$vcard_email."' and organization='".$vcard_organization."'";
$execchechvcard=$db->query($chechvcard);
$result_chechvcard=$execchechvcard->fetch();
$execchechvcard=null;	

					if(strlen($result_chechvcard['id'])>0){
					
					$maxIdSQL="insert into metadata_element_value SET element_hierarchy=".$element_hierarchy.",value='Vcard Element',language_id='".$language."',record_id=".$item_id.",multi=".$multi.",parent_indexer=".$parent_indexer.",vcard_id=".$result_chechvcard['id']." ON DUPLICATE KEY UPDATE vcard_id=".$result_chechvcard['id']."";	
					
				     echo $maxIdSQL."<br>"; 
					$exec=$db->query($maxIdSQL);
					$result_multi=$exec->fetch();
					
					}else{
					$chechvcardins="insert into metadata_vcard SET name='".$vcard_name."',surname='".$vcard_surname."',email='".$vcard_email."',organization='".$vcard_organization."'";
					echo $chechvcardins."<br>"; 
					$execchechvcardins=$db->query($chechvcardins);
					$result_chechvcardins=$execchechvcardins->fetch();
					$execchechvcardins=null;

					$chechvcardnew="select * from metadata_vcard WHERE name='".$vcard_name."' and surname='".$vcard_surname."' and email='".$vcard_email."' and organization='".$vcard_organization."'";
					$execchechvcardnew=$db->query($chechvcardnew);
					$result_chechvcardnew=$execchechvcardnew->fetch();
					$execchechvcardnew=null;
					
					$maxIdSQL="insert into metadata_element_value SET element_hierarchy=".$element_hierarchy.",value='Vcard Element',language_id='".$language."',record_id=".$item_id.",multi=".$multi.",parent_indexer=".$parent_indexer.",vcard_id=".$result_chechvcardnew['id']." ON DUPLICATE KEY UPDATE vcard_id=".$result_chechvcardnew['id']."";	
					
					echo $maxIdSQL."<br>"; 
					$exec=$db->query($maxIdSQL);
					$result_multi=$exec->fetch();

}
}
}

?>
