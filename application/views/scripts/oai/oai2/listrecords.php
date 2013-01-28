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
foreach ($args as $key => $val) {

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
                $inc_record = $METADATAFORMATS[$val]['myhandler'];
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
// Resume previous session?
if (isset($args['resumptionToken'])) {
    if (count($args) > 1) {
        // overwrite all other errors
        $errors = oai_error('exclusiveArgument');
    } /* else {
      if (is_file("tokens/id-$resumptionToken")) {
      $fp = fopen("tokens/id-$resumptionToken", 'r');
      $filetext = fgets($fp, 255);
      $textparts = explode('#', $filetext);
      $deliveredrecords = (int)$textparts[0];
      $extquery = $textparts[1];
      $metadataPrefix = $textparts[2];
      fclose($fp);
      unlink ("tokens/id-$resumptionToken");
      } else {
      $errors .= oai_error('badResumptionToken', '', $resumptionToken);
      }
      } */
}
// no, new session
else {
    $deliveredrecords = 0;
    $extquery = '';

    if (!isset($args['metadataPrefix'])) {
        $errors .= oai_error('missingArgument', 'metadataPrefix');
    }

    /* if (isset($args['from'])) {
      if (!checkDateFormat($from)) {
      $errors .= oai_error('badGranularity', 'from', $from);
      }
      $extquery .= fromQuery($from);
      } */

    /* if (isset($args['until'])) {
      if (!checkDateFormat($until)) {
      $errors .= oai_error('badGranularity', 'until', $until);
      }
      $extquery .= untilQuery($until);
      } */
}


if (isset($_GET['resumptionToken'])) { ///////yyyymmdd:offset:metadataprefix:setSpec for checking resumptionToken
    $resumptionToken = explode(':', $_GET['resumptionToken']);
    $resumptionTokendate = $resumptionToken[0];
    $resumptionTokenoffset = onlyNumbers($resumptionToken[1]);
    $resumptionTokenoffsetn = onlyNumbers($resumptionToken[1]);
    $resumptionTokenmetadataprefix = $resumptionToken[2];
    $resumptionTokensetSpec = onlyNumbers($resumptionToken[3]);

    if ($resumptionTokensetSpec > 0) {
        $query_spec = "select * from omeka_collections where id=" . $resumptionTokensetSpec . " ";
        $execspec = $db->query($query_spec);
        $row_spec = $execspec->fetch();
        $specSize = count($row_spec);
    }  else {
        $specSize=0;
    }

    $testdivoffset = $resumptionTokenoffset % $MAXRECORDS;
    $val = $resumptionTokenmetadataprefix;

    /////////checking for error in resumptionToken/////////
    if ($datetime_resum != $resumptionTokendate) {        
        $errors .= oai_error('badResumptionToken', '', $_GET['resumptionToken']);
    } elseif ((!$resumptionTokenoffset > 0) or $testdivoffset != 0) { 
        $errors .= oai_error('badResumptionToken', '', $_GET['resumptionToken']);
    } elseif (((!$resumptionTokensetSpec > 0)  or (!$specSize > 0)) and $resumptionTokensetSpecforno=='no') {
        $errors .= oai_error('badResumptionToken', '', $_GET['resumptionToken']);
    } elseif (strlen($val) > 0) {
        if (is_array($METADATAFORMATS[$val])
                && isset($METADATAFORMATS[$val]['myhandler'])) {
            $metadataPrefix = $val;
            $inc_record = $METADATAFORMATS[$val]['myhandler'];
        } else {
            $errors .= oai_error('badResumptionToken', '', $_GET['resumptionToken']);
        }
    }



    //OFFSET 10
    //$errors .= oai_error('badResumptionToken', '', $resumptionToken);
}

///////////////////////////setSpecs///////////////////////

if (isset($_GET['set'])) {
    $getset = $_GET['set'];
    $getset = explode($set_prefix, $getset);
    $getsetn = onlyNumbers($getset[1]);
    if ($getsetn > 0) {
        $getset = " and collection_id=" . $getsetn . "";
    } else {
        $getset = " and collection_id=0";
    }
}elseif($resumptionTokensetSpec>0){
    $getsetn = onlyNumbers($resumptionTokensetSpec);
    if ($getsetn > 0) {
        $getset = " and collection_id=" . $getsetn . "";
    } else {
        $getset = " and collection_id=0";
    }
}else{
    $getset = "";
    $getsetn = "no";
}

$query_itemtotal = "select * from omeka_items where public=1 " . $getset . " ";
$exectotal = $db->query($query_itemtotal);
$row_itemtotal = $exectotal->fetchAll();
$completeListSize = count($row_itemtotal);

if ($resumptionTokenoffset > 0 and $testdivoffset == 0) {
    $resumptionTokenoffset = " OFFSET " . $resumptionTokenoffset . "";
} else {
    $resumptionTokenoffset = " OFFSET 0";
}

$query_item = "select * from omeka_items where public=1 " . $getset . " ORDER BY id ASC LIMIT " . $MAXRECORDS . "  " . $resumptionTokenoffset . " ";
$exec = $db->query($query_item);
$row_item = $exec->fetchAll();
$count_results = count($row_item);
if (!$count_results > 0) {
    $errors .= oai_error('noRecordsMatch');
}


if (empty($errors)) { //if no errors
    $output .= "<ListRecords>\n";




    foreach ($row_item as $row_item) {


        if (strlen($row_item['collection_id']) > 0) {
            $sqlcollection = "select * from omeka_collections where id=" . $row_item['collection_id'] . " ";
//echo $sqlmetadatarecord; //break;
            $execcollection = $db->query($sqlcollection);
            $oai_collection = $execcollection->fetch();
        } else {
            $oai_collection['id'] = '';
        }

        $sqlmetadatarecord = "select * from metadata_record where object_id=" . $row_item['id'] . " and object_type='item'";
//echo $sqlmetadatarecord; //break;
        $exec2 = $db->query($sqlmetadatarecord);
        $metadatarecord = $exec2->fetch();


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


        $output .= '<record>' . "\n";
        $output .= '<header>' . "\n";
        $output .= '<identifier>';
        $output .= 'oai:' . $repositoryIdentifier . ':' . $metadatarecord['object_id'];
        $output .= '</identifier>' . "\n";
        $output .= '<datestamp>';
        $output .= $datestamp;
        $output .= '</datestamp>' . "\n";
        if (strlen($oai_collection['id']) > 0) {
            $output .= '<setSpec>';
            $output .=$set_prefix.''. $oai_collection['id'];
            $output .= '</setSpec>' . "\n";
        }
        $output .= '</header>' . "\n";
        $output .= '<metadata>' . "\n";
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
                $output.= preview_elements($datageneral4, NULL, $metadatarecord, $datageneral3);
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
        $output .= '</metadata>' . "\n";


        $output .= '</record>' . "\n";
    }

///////////////resumptionToken creation
    $newresumptionTokenoffset = $resumptionTokenoffsetn + $MAXRECORDS;
    if ($completeListSize - $newresumptionTokenoffset > 0) {
    $output .= '<resumptionToken expirationDate="' . $datetime_resum2 . 'T24:59:59Z" completeListSize="' . $completeListSize . '" cursor="' . $MAXRECORDS . '">';
    $output .=$datetime_resum . ':' . $newresumptionTokenoffset . ':' . $metadataPrefix.':'.$getsetn;
    $output .= '</resumptionToken>' . "\n";
    }


// end ListRecords
    $output .=
            '</ListRecords>' . "\n";
}//if no errors!!!
?>
