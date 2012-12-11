<?php
/*
* +----------------------------------------------------------------------+
* | PHP Version 4                                                        |
* +----------------------------------------------------------------------+
* | Copyright (c) 2002-2005 Heinrich Stamerjohanns                       |
* |                                                                      |
* | listrecords.php -- Utilities for the OAI Data Provider               |
* |                                                                      |
* | This is free software; you can redistribute it and/or modify it under|
* | the terms of the GNU General Public License as published by the      |
* | Free Software Foundation; either version 2 of the License, or (at    |
* | your option) any later version.                                      |
* | This software is distributed in the hope that it will be useful, but |
* | WITHOUT  ANY WARRANTY; without even the implied warranty of          |
* | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the         |
* | GNU General Public License for more details.                         |
* | You should have received a copy of the GNU General Public License    |
* | along with  software; if not, write to the Free Software Foundation, |
* | Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA         |
* |                                                                      |
* +----------------------------------------------------------------------+
* | Derived from work by U. Müller, HUB Berlin, 2002                     |
* |                                                                      |
* | Written by Heinrich Stamerjohanns, May 2002                          |
* /            stamer@uni-oldenburg.de                                   |
* +----------------------------------------------------------------------+
*/
//
// $Id: listrecords.php,v 1.03 2004/07/02 14:24:21 stamer Exp $
//

// parse and check arguments
//print_r($args);
foreach($args as $key => $val) {

	switch ($key) { 
		case 'from':
			// prevent multiple from
			if (!isset($from)) {
				$from = $val;
			} else {
				$errors .= oai_error('badArgument', $key, $val);
			}
			break;

		case 'until':
			// prevent multiple until
			if (!isset($until)) {
				$until = $val; 
			} else {
				$errors .= oai_error('badArgument', $key, $val);
			}
			break;

		case 'metadataPrefix':
			if (is_array($METADATAFORMATS[$val])
					&& isset($METADATAFORMATS[$val]['myhandler'])) {
				$metadataPrefix = $val;
				$inc_record  = $METADATAFORMATS[$val]['myhandler'];
			} else {
				$errors .= oai_error('cannotDisseminateFormat', $key, $val);
			}
			break;

		case 'set':
			if (!isset($set)) {
				$set = $val;
			} else {
				$errors .= oai_error('badArgument', $key, $val);
			}
			break;      

		case 'resumptionToken':
			if (!isset($resumptionToken)) {
				$resumptionToken = $val;
			} else {
				$errors .= oai_error('badArgument', $key, $val);
			}
			break;

		default:
			$errors .= oai_error('badArgument', $key, $val);
	}
}

if (empty($errors)) { //if no errors

$output .= "<ListRecords>\n";


$query_item="select * from omeka_items";
$exec=$db->query($query_item);
$row_item=$exec->fetchAll();

foreach($row_item as $row_item){

$sqlmetadatarecord="select * from metadata_record where object_id=".$row_item['id']." and object_type='item'";
//echo $sqlmetadatarecord; //break;
$exec2=$db->query($sqlmetadatarecord);
$metadatarecord=$exec2->fetch();


$sqlmetadatarecordvalue="select * from metadata_element_value where record_id=".$metadatarecord['id']." ORDER BY element_hierarchy ASC";
$exec=$db->query($sqlmetadatarecordvalue);
$metadatarecordvalue_res=$exec->fetchAll();
//echo $sqlmetadatarecordvalue; break;

//$metadatarecordvalue_res=mysql_query($sqlmetadatarecordvalue);
//$metadatarecordvalue=mysql_fetch_array($metadatarecordvalue_res);

$output .= '<record>'."\n";
	$output .= '<header>'."\n";
		$output .= '<identifier>';
			$output .=  'oai:'.$repositoryIdentifier.':'.$metadatarecord['id'];		
		$output .= '</identifier>'."\n";
		$output .= '<datestamp>';
			$output .=  $metadatarecord['date_modified'];		
		$output .= '</datestamp>'."\n";
	$output .= '</header>'."\n"; 
	$output .= '<metadata>'."\n";
		$output .= '<lom xmlns="http://ltsc.ieee.org/xsd/LOM" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://ltsc.ieee.org/xsd/LOM http://ltsc.ieee.org/xsd/lomv1.0/lomLoose.xsd">' . "\n";



		
					/*$general='';
					$educational='';
					$rights='';
					$classification='';
					$metaMetadata='';
					$technical='';
					$counttitle='';
					$countdes='';
					$typicalAgeRange='';
					$ifexist=0;
					$cost='';
					$cost.= '<cost>'."\n";
					$cost .= xmlformat('LOMv1.0', 'source', '', $indent);
					$cost .= xmlformat('no', 'value', '', $indent);
					$cost.= '</cost>'."\n";
					$ifexist2=0;
					$cost2='';
					$cost2.= '<copyrightAndOtherRestrictions>'."\n";
					$cost2 .= xmlformat('LOMv1.0', 'source', '', $indent);
					$cost2 .= xmlformat('yes', 'value', '', $indent);
					$cost2.= '</copyrightAndOtherRestrictions>'."\n";
					$right1='no';
					$right2='yes';
					foreach($metadatarecordvalue_res as $metadatarecordvalue_res){
							
							$sql2="select * from metadata_element_hierarchy where id=".$metadatarecordvalue_res['element_hierarchy']."";
							$exec3=$db->query($sql2);
							$result2=$exec3->fetch();
							
							$sql2="select * from metadata_element_label where id=".$result2['element_id']."";
							$sql_res2=$db->query($sql2);
							$result3=$sql_res2->fetch();
							
						
							
							
							if($metadatarecordvalue['element_hierarchy']==6){

							$counttitle .= xmlformat($metadatarecordvalue['value'], 'string', 'language="'.$metadatarecordvalue['language_id'].'"', $indent);
							
							} //metadatarecordvalue['element_hierarchy']==6
							
							
							
							if($metadatarecordvalue['element_hierarchy']==7){
							
							
							$general .= xmlformat($metadatarecordvalue['value'], 'language', '', $indent);
						
							
							} //metadatarecordvalue['element_hierarchy']==7
							
							
							if($metadatarecordvalue['element_hierarchy']==8){
							$countdes .= xmlformat($metadatarecordvalue['value'], 'string', 'language="'.$metadatarecordvalue['language_id'].'"', $indent);
							} //metadatarecordvalue['element_hierarchy']==8
							
							
							if($metadatarecordvalue['element_hierarchy']==9){ //
							$ifexist2=1;
							$rights.= '<copyrightAndOtherRestrictions>'."\n";
							$rights .= xmlformat('LOMv1.0', 'source', '', $indent);
							$rights .= xmlformat($metadatarecordvalue['value'], 'value', '', $indent);
							$rights.= '</copyrightAndOtherRestrictions>'."\n";
							
							} //metadatarecordvalue['element_hierarchy']==9
							
							if($metadatarecordvalue['element_hierarchy']==24){ //
							$ifexist=1;
							$rights.= '<cost>'."\n";
							$rights .= xmlformat('LOMv1.0', 'source', '', $indent);
							$rights .= xmlformat($metadatarecordvalue['value'], 'value', '', $indent);
							$rights.= '</cost>'."\n";
							
							} //metadatarecordvalue['element_hierarchy']==9 no
							
							if($metadatarecordvalue['element_hierarchy']==22){ //
							//$rights .= xmlformat($metadatarecordvalue['value'], 'AreCommercialUsesOfThisResourceAllowed', '', $indent);
							$right1=$metadatarecordvalue['value'];
							
							} //metadatarecordvalue['element_hierarchy']==9
							
							if($metadatarecordvalue['element_hierarchy']==23){ //
							//$rights .= xmlformat($metadatarecordvalue['value'], 'AreModificationsOfYourWorkOfThisResourceByOtherPeopleAllowed', '', $indent);
							$right2=$metadatarecordvalue['value'];
							} //metadatarecordvalue['element_hierarchy']==9
							
							
							if($metadatarecordvalue['element_hierarchy']==11){ //
							$educational .= '<learningResourceType>'."\n";
							$educational .= xmlformat('LREv3.0', 'source', '', $indent);
							$educational .= xmlformat($metadatarecordvalue['value'], 'value', '', $indent);
							$educational .= '</learningResourceType>'."\n";
							
							} //metadatarecordvalue['element_hierarchy']==9
							
							if($metadatarecordvalue['element_hierarchy']==12){ //
							$educational .= '<intendedEndUserProfile>'."\n";
							$educational .= xmlformat('LREv3.0', 'source', '', $indent);
							$educational .= xmlformat($metadatarecordvalue['value'], 'value', '', $indent);
							$educational .= '</intendedEndUserProfile>'."\n";
							
							} //metadatarecordvalue['element_hierarchy']==9
							
							if($metadatarecordvalue['element_hierarchy']==13){ //
							$educational .= '<learningContext>'."\n";
							$educational .= xmlformat($metadatarecordvalue['value'], 'value', '', $indent);
							$educational .= '</learningContext>'."\n";
							
							} //metadatarecordvalue['element_hierarchy']==9
							
							if($metadatarecordvalue['element_hierarchy']==14){ //
							$typicalAgeRange .= xmlformat($metadatarecordvalue['value'], 'string', 'language="'.$metadatarecordvalue['language_id'].'"', $indent);
							
							} //metadatarecordvalue['element_hierarchy']==9
							
							if($metadatarecordvalue['element_hierarchy']==26){ //
							$educational .= xmlformat($metadatarecordvalue['value'], 'difficulty', '', $indent);
							
							} //metadatarecordvalue['element_hierarchy']==9
							
							if($metadatarecordvalue['element_hierarchy']==18){ //
							$classification .= xmlformat($metadatarecordvalue['value'], 'cognitiveDomain', '', $indent);
							
							} //metadatarecordvalue['element_hierarchy']==9
							
							if($metadatarecordvalue['element_hierarchy']==19){ //
							$classification .= xmlformat($metadatarecordvalue['value'], 'cognitiveDomainKnowledge', '', $indent);
							
							} //metadatarecordvalue['element_hierarchy']==9
							
							if($metadatarecordvalue['element_hierarchy']==20){ //
							$classification .= xmlformat($metadatarecordvalue['value'], 'affectiveDomain', '', $indent);
							
							} //metadatarecordvalue['element_hierarchy']==9
							
							if($metadatarecordvalue['element_hierarchy']==21){ //
							$classification .= xmlformat($metadatarecordvalue['value'], 'psychomotorDomain', '', $indent);
							
							} //metadatarecordvalue['element_hierarchy']==9
							
							if($metadatarecordvalue['element_hierarchy']==25){ //
							$classification .= '<educationalLevel>'."\n";
							$classification .= xmlformat($metadatarecordvalue['value'], 'value', '', $indent);
							$classification .= '</educationalLevel>'."\n";
							
							} //metadatarecordvalue['element_hierarchy']==9
					
					
					} //while($metadatarecordvalue=mysql_fetch_array($metadatarecordvalue_res)){
			
			

			$output .= '<general>'."\n";
			$output .= '<identifier>'."\n";
			$output .= xmlformat('URI', 'catalog', '', $indent);
			$output .= xmlformat('http://'.$_SERVER['SERVER_NAME'].'/natural_europe/exhibits/show/'.$row_item['slug'].'/to-begin-with', 'entry', '', $indent);
			$output .= '</identifier>'."\n";
			
			$output .= '<title>'."\n";
			$output .= $counttitle;
			$output .= '</title>'."\n";
			
			$output .= '<description>'."\n";
			$output .= $countdes;
			$output .= '</description>'."\n";
			
			$output .= $general;
			$output .= '</general>'."\n";
			
			$output .= '<educational>'."\n";
			$output .= $educational;
			
			$output .= '<typicalAgeRange>'."\n";
			$output .= $typicalAgeRange;
			$output .= '</typicalAgeRange>'."\n";
			
			$output .= '</educational>'."\n";
			
			$output .= '<rights>'."\n";
			if($ifexist==0){$output .= $cost;} $ifexist=0;
			if($ifexist2==0){$output .= $cost2;} $ifexist2=0;
			$output .= '<description>'."\n";
			if($right1=='yes' and $right2=='yes'){			
			$output .= xmlformat('http://www.creativecommons.org/licenses/by/3.0', 'string', '', $indent);}
			elseif($right1=='yes' and $right2=='no'){
			$output .= xmlformat('http://www.creativecommons.org/licenses/by-nd/3.0', 'string', '', $indent);}
			elseif($right1=='yes' and $right2=='Yes, if others share alike'){
			$output .= xmlformat('http://www.creativecommons.org/licenses/by-sa/3.0', 'string', '', $indent);}
			elseif($right1=='no' and $right2=='yes'){
			$output .= xmlformat('http://www.creativecommons.org/licenses/by-nc/3.0', 'string', '', $indent);}
			elseif($right1=='no' and $right2=='no'){
			$output .= xmlformat('http://www.creativecommons.org/licenses/by-nc-nd/3.0', 'string', '', $indent);}
			elseif($right1=='no' and $right2=='Yes, if others share alike'){
			$output .= xmlformat('http://www.creativecommons.org/licenses/by-nc-sa/3.0', 'string', '', $indent);}
			$output .= '</description>'."\n";
			
			$right1='no';
			$right2='yes';
			
			$output .= $rights;
			$output .= '</rights>'."\n";
			
			$output .= '<technical>'."\n";
			$output .= xmlformat('HTML', 'format', '', $indent);
			$output .= xmlformat('http://'.$_SERVER['SERVER_NAME'].'/natural_europe/exhibits/show/'.$row_item['slug'].'/to-begin-with', 'location', '', $indent);
			$output .= $technical;
			$output .= '</technical>'."\n";
			
			
			$output .= '<metaMetadata>'."\n";
			$output .= '<identifier>'."\n";
			$output .= xmlformat('URI', 'catalog', '', $indent);
			$output .= xmlformat('http://'.$_SERVER['SERVER_NAME'].'/natural_europe/exhibits/show/'.$row_item['slug'].'/to-begin-with', 'entry', '', $indent);
			$output .= '</identifier>'."\n";
			$output .= $metaMetadata;
			$output .= '</metaMetadata>'."\n";
			
			$output .= '<classification>'."\n";
			$output .= $classification;
			$output .= '</classification>'."\n";*/	
				
		$output .= '</lom>'."\n";	
	$output .= '</metadata>'."\n";
	
	
$output .= '</record>'."\n";  

}


// end ListRecords
$output .= 
'</ListRecords>'."\n";
 
}//if no errors!!!
  
?>
