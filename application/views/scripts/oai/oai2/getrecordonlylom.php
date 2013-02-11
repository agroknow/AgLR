<?php

/*
 * +----------------------------------------------------------------------+
 * | PHP Version 4                                                        |
 * +----------------------------------------------------------------------+
 * | Copyright (c) 2002-2005 Heinrich Stamerjohanns                       |
 * |                                                                      |
 * | getrecord.php -- Utilities for the OAI Data Provider                 |
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
// $Id: getrecord.php,v 1.02 2003/04/08 14:22:07 stamer Exp $
//
// parse and check arguments
foreach ($args as $key => $val) {

    switch ($key) {
        case 'identifier':
            $identifier = $val;
            if (!is_valid_uri($identifier)) {
                $errors .= oai_error('badArgument', $key, $val);
            }
            break;

        case 'metadataPrefix':
            if (is_array($METADATAFORMATS[$val])
                    && isset($METADATAFORMATS[$val]['myhandler'])) {
                $metadataPrefix = $val;
                $inc_record = $METADATAFORMATS[$val]['myhandler'];
            } else {
                $errors .= oai_error('cannotDisseminateFormat', $key, $val);
            }
            break;

        default:
            $errors .= oai_error('badArgument', $key, $val);
    }
}

if (!isset($args['identifier'])) {
    $errors .= oai_error('missingArgument', 'identifier');
}
if (!isset($args['metadataPrefix'])) {
    $errors .= oai_error('missingArgument', 'metadataPrefix');
}

///////explode identifier for use/////////////
$identifier = explode('oai:' . $repositoryIdentifier . ':', $identifier);
$identifier2 = explode(':', $identifier[1]);

$identifier = $identifier2[0];
$identifier = onlyNumbers($identifier);
$object_type = $identifier2[1];
if ($object_type == 'exhibit' or $object_type == 'item') {
    $object_type = $object_type;
} else {
    $object_type = 'Nothing';
}

////////////query if exist metadata record in the db!!!!//////////////////////////////
$sqlmetadatarecord = "select * from metadata_record where object_id=" . $identifier . " and object_type='" . $object_type . "'";
//echo $sqlmetadatarecord; //break;
$exec2 = $db->query($sqlmetadatarecord);
$metadatarecord = $exec2->fetch();

if (!isset($metadatarecord['id'])) {
    $errors .= oai_error('idDoesNotExist',NULL,$identifier);
}

if (empty($errors)) { //if no errors
    

    if ($metadatarecord['object_type'] == 'item') {
        $sqlomekaitem = "select * from omeka_items where id=" . $identifier . " ";
//echo $sqlmetadatarecord; //break;
        $execomekaitem = $db->query($sqlomekaitem);
        $omekaitem = $execomekaitem->fetch();
    } else {
        $sqlomekaitem = "select * from omeka_exhibits where id=" . $identifier . " ";
//echo $sqlmetadatarecord; //break;
        $execomekaitem = $db->query($sqlomekaitem);
        $omekaitem = $execomekaitem->fetch();
    }



    $sqlmetadatarecordvalue = "select * from metadata_element_value where record_id=" . $metadatarecord['id'] . " ORDER BY element_hierarchy ASC";
    $exec = $db->query($sqlmetadatarecordvalue);
    $metadatarecordvalue_res = $exec->fetchAll();
//echo $sqlmetadatarecordvalue; break;
//$metadatarecordvalue_res=mysql_query($sqlmetadatarecordvalue);
//$metadatarecordvalue=mysql_fetch_array($metadatarecordvalue_res);

    $datestamp = $metadatarecord['date_modified'];
    $selectvaluesvalue2 = explode(' ', $datestamp);
    $datestamp = '';
    $datestamp.=$selectvaluesvalue2[0];
    $datestamp.='T';
    if(strlen($selectvaluesvalue2[1])>0){
            $datestamp.=$selectvaluesvalue2[1];
            }else{
             $datestamp.='00:00:00';   
            }
    $datestamp.='.00Z';


    
    $output .= '<lom xmlns="http://ltsc.ieee.org/xsd/LOM" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://ltsc.ieee.org/xsd/LOM http://ltsc.ieee.org/xsd/lomv1.0/lomLoose.xsd">' . "\n";

//query for creating general elements pelement=0		 
    $sql3 = "SELECT c.*,b.machine_name,b.id as elm_id2 FROM  metadata_element b  LEFT JOIN metadata_element_hierarchy c 
			ON c.element_id = b.id WHERE c.pelement_id=0 and c.is_visible=1  ORDER BY (case WHEN c.sequence IS NULL THEN '9999' ELSE c.sequence END) ASC;";
    $exec3 = $db->query($sql3);
    $datageneral3 = $exec3->fetchAll();


/////////////////////////




    foreach ($datageneral3 as $datageneral3) {

        $output2 = '';
        $sql4 = "SELECT c.*,b.machine_name,b.id as elm_id FROM  metadata_element b  LEFT JOIN metadata_element_hierarchy c 
			ON c.element_id = b.id  WHERE c.pelement_id=" . $datageneral3['elm_id2'] . " and c.is_visible=1 ORDER BY (case WHEN c.sequence IS NULL THEN '9999' ELSE c.sequence END) ASC;";
        //echo $sql4;break;
        $exec4 = $db->query($sql4);
        $datageneral4 = $exec4->fetchAll();


        if ($datageneral3['machine_name'] == 'rights') { ///////if RIGHTS
            $output2.= preview_elements($datageneral4, NULL, $metadatarecord, $datageneral3);
        } elseif ($datageneral3['machine_name'] == 'classification') { ///////if CLASSIFICATION
            //$output.= preview_elements($datageneral4, NULL, $metadatarecord, $datageneral3);
        } elseif ($datageneral3['machine_name'] == 'relation') { ///////if RELATION
            $output2.= preview_elements($datageneral4, NULL, $metadatarecord, $datageneral3);
        } else { ///the rest parent elements///////////////////////////////
            foreach ($datageneral4 as $datageneral4) {



                $sql5 = "SELECT * FROM  metadata_element_value WHERE record_id=" . $metadatarecord['id'] . " and element_hierarchy=" . $datageneral4['id'] . " ORDER BY multi ASC;";
                //echo $sql4."<br>";
                $exec5 = $db->query($sql5);
                $datageneral5 = $exec5->fetchAll();
                $count_results = count($datageneral5);

                if ($count_results > 0) {

                    if ($datageneral3['machine_name'] == 'general') { ///////if GENERAL
                        $output2.= preview_elements($datageneral4, $datageneral5, $metadatarecord, $datageneral3);
                    } elseif ($datageneral3['machine_name'] == 'educational') { ///////if EDUCATIONAL
                        $output2.= preview_elements($datageneral4, $datageneral5, $metadatarecord, $datageneral3);
                    } elseif ($datageneral3['machine_name'] == 'technical') { ///////if TECHNICAL
                        $output2.= preview_elements($datageneral4, $datageneral5, $metadatarecord, $datageneral3);
                    } elseif ($datageneral3['machine_name'] == 'lifeCycle') { ///////if LIFECYCLE
                        $output2.= preview_elements($datageneral4, $datageneral5, $metadatarecord, $datageneral3);
                    } elseif ($datageneral3['machine_name'] == 'metaMetadata') { ///////if META-METADATA
                        $output2.= preview_elements($datageneral4, $datageneral5, $metadatarecord, $datageneral3);
                    } elseif ($datageneral3['machine_name'] == 'annotation') { ///////if ANNOTATION
                        $output2.= preview_elements($datageneral4, $datageneral5, $metadatarecord, $datageneral3);
                    } else {
                        $output2.= preview_elements_from_datatype($datageneral4, $datageneral5, $metadatarecord);
                    }
                }//if count_results
            }//datageneral4
        } ///the rest parent elements///////////////////////////////	
        ////////////////echo the result of all parent element if exist
        if (strlen($output2) > 0) {

            $output.= '<' . $datageneral3['machine_name'] . '>' . "\n";
            $output.= $output2;
            $output.= '</' . $datageneral3['machine_name'] . '>' . "\n";
        }
    }//datageneral3



    $output .= '</lom>' . "\n";


}//if no errors!!!
?>
