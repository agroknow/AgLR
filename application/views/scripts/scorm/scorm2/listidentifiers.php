<?php

/*
 * +----------------------------------------------------------------------+
 * | PHP Version 4                                                        |
 * +----------------------------------------------------------------------+
 * | Copyright (c) 2002-2005 Heinrich Stamerjohanns                       |
 * |                                                                      |
 * | listidentifiers.php -- Utilities for the OAI Data Provider           |
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
 * | Derived from work by U. M�ller, HUB Berlin, 2002                     |
 * |                                                                      |
 * | Written by Heinrich Stamerjohanns, May 2002                          |
 * |            stamer@uni-oldenburg.de                                   |
 * +----------------------------------------------------------------------+
 */
//
// $Id: listidentifiers.php,v 1.02 2003/04/08 14:17:47 stamer Exp $
//
// parse and check arguments




///////////////////////////setSpecs///////////////////////
$xmlheader ='<?xml version="1.0" encoding="UTF-8"?>';
$q='';
if (isset($_GET['query']) and strlen($_GET['query'])) {
    $getset = addslashes($_GET['query']);
    $kt=explode(" ",$getset);//Breaking the string to array of words
// Now let us generate the sql 
			while(list($key,$val)=each($kt)){
if($val<>" " and strlen($val) > 0){$q .= " title like '%$val%' or ";}

			}// end of while
$q=substr($q,0,(strlen($q)-3));
$q=" and (".$q.")";
}


$query_itemtotal = "select * from omeka_exhibits where public=1 " . $q . " ";
$exectotal = $db->query($query_itemtotal);
$row_itemtotal = $exectotal->fetchAll();
$completeListSize = count($row_itemtotal);


if (isset($_GET['offset']) and $_GET['offset']> 0) {
    $_GET['offset']=  onlyNumbers($_GET['offset']);
    $resumptionTokenoffset = " OFFSET " . $_GET['offset'] . "";
    $offset = $_GET['offset'];
} else {
    $resumptionTokenoffset = " OFFSET 0";
    $offset = "0";
}
if (isset($_GET['count']) and $_GET['count']> 0) {
    $_GET['count']=  onlyNumbers($_GET['count']);
    $MAXIDS = $_GET['count'] . "";
} else {
    $MAXIDS = 12;
}

$query_item = "select * from omeka_exhibits where public=1 " . $q . " ORDER BY id ASC LIMIT " . $MAXIDS . " " . $resumptionTokenoffset . " ";
$exec = $db->query($query_item);
$row_item = $exec->fetchAll();
$count_results = count($row_item);
if (!$count_results > 0) {
    $errors .= oai_error('noRecordsMatch');
}

if (empty($errors)) { //if no errors
    $output .= "<List>\n";
    $output .= "<totalResults>".$completeListSize."</totalResults>";
    $output .= "<startIndex>".$offset."</startIndex>";
    $output .= "<itemsPerPage>".$MAXIDS."</itemsPerPage>";
    $output .= "<Query searchTerms='".$getset."' count='".$MAXIDS."' startIndex='".$offset."'/>";

    foreach ($row_item as $row_item) {


        $sqlmetadatarecord = "select * from metadata_record where object_id=" . $row_item['id'] . " and object_type='exhibit'";
//echo $sqlmetadatarecord; //break;
        $exec2 = $db->query($sqlmetadatarecord);
        $metadatarecord = $exec2->fetch();


        $sqlmetadatarecordvalue = "select * from metadata_element_value where record_id=" . $metadatarecord['id'] . " ORDER BY element_hierarchy ASC";
        $exec = $db->query($sqlmetadatarecordvalue);
        $metadatarecordvalue_res = $exec->fetchAll();
//echo $sqlmetadatarecordvalue; break;
//$metadatarecordvalue_res=mysql_query($sqlmetadatarecordvalue);
//$metadatarecordvalue=mysql_fetch_array($metadatarecordvalue_res);

       
        $output .= '<item>' . "\n";
        $output .= '<title>' . "\n";
        $output .= htmlspecialchars($row_item['title']);
        $output .= '</title>' . "\n";
        $output .= '<link>' . "\n";
        $output .= '<![CDATA['.$MY_URI.'?verb=GetRecord&identifier=scorm:'.$repositoryIdentifier.':'.$row_item['id'].']]>';
        $output .= '</link>' . "\n";

        $output .= '</item>' . "\n";
    }

///////////////resumptionToken creation

    
// end ListRecords
    $output .=
            '</List>' . "\n";
}//if no errors!!!
?>
