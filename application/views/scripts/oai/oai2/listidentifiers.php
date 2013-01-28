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
foreach ($args as $key => $val) {

    switch ($key) {
        case 'from':
            if (!isset($from)) {
                $from = $val;
            } else {
                $errors .= oai_error('badArgument', $key, $val);
            }
            break;

        case 'until':
            if (!isset($until)) {
                $until = $val;
            } else {
                $errors .= oai_error('badArgument', $key, $val);
            }
            break;

        case 'set':
            if (!isset($set)) {
                $set = $val;
            } else {
                $errors .= oai_error('badArgument', $key, $val);
            }
            break;

        case 'metadataPrefix':
            if (!isset($metadataPrefix)) {
                if (is_array($METADATAFORMATS[$val])
                        && isset($METADATAFORMATS[$val]['myhandler'])) {
                    $metadataPrefix = $val;
                    $inc_record = $METADATAFORMATS[$val]['myhandler'];
                } else {
                    $errors .= oai_error('cannotDisseminateFormat', $key, $val);
                }
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
    $resumptionTokensetSpecforno = $resumptionToken[3];

    if ($resumptionTokensetSpec > 0) {
        $query_spec = "select * from omeka_collections where id=" . $resumptionTokensetSpec . " ";
        $execspec = $db->query($query_spec);
        $row_spec = $execspec->fetch();
        $specSize = count($row_spec);
    }  else {
        $specSize=0;
    }
    
    
    $testdivoffset = $resumptionTokenoffset % $MAXIDS;
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
} elseif ($resumptionTokensetSpec > 0) {
    $getsetn = onlyNumbers($resumptionTokensetSpec);
    if ($getsetn > 0) {
        $getset = " and collection_id=" . $getsetn . "";
    } else {
        $getset = " and collection_id=0";
    }
} else {
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
$query_item = "select * from omeka_items where public=1 " . $getset . " ORDER BY id ASC LIMIT " . $MAXIDS . " " . $resumptionTokenoffset . " ";
$exec = $db->query($query_item);
$row_item = $exec->fetchAll();
$count_results = count($row_item);
if (!$count_results > 0) {
    $errors .= oai_error('noRecordsMatch');
}

if (empty($errors)) { //if no errors
    $output .= "<ListIdentifiers>\n";


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
        $datestamp.='Z';


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



        $output .= '</record>' . "\n";
    }

///////////////resumptionToken creation

    $newresumptionTokenoffset = $resumptionTokenoffsetn + $MAXIDS;
    if ($completeListSize - $newresumptionTokenoffset > 0) {
        $output .= '<resumptionToken expirationDate="' . $datetime_resum2 . 'T24:59:59Z" completeListSize="' . $completeListSize . '" cursor="' . $MAXIDS . '">';
        $output .=$datetime_resum . ':' . $newresumptionTokenoffset . ':' . $metadataPrefix . ':' . $getsetn;
        $output .= '</resumptionToken>' . "\n";
    }
// end ListRecords
    $output .=
            '</ListIdentifiers>' . "\n";
}//if no errors!!!
?>
