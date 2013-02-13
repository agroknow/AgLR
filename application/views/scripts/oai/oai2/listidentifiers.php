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
    $resumptionTokensetSpec = $resumptionToken[3];
    $resumptionTokensetSpecforno = $resumptionToken[3];




    if (strlen($resumptionTokensetSpec) > 0) {

        if ($resumptionTokensetSpec == 'resources') {
            $query_spec = "select * from metadata_record where object_type='item' and validate=1 ";
            $execspec = $db->query($query_spec);
            $row_spec = $execspec->fetch();
            $specSize = count($row_spec);
        } elseif ($resumptionTokensetSpec == 'pathways') {
            $query_spec = "select * from metadata_record where object_type='exhibit' and validate=1 ";
            $execspec = $db->query($query_spec);
            $row_spec = $execspec->fetch();
            $specSize = count($row_spec);
        } elseif ($resumptionTokensetSpec == 'no') {
            $query_spec = "select * from metadata_record where validate=1 ";
            $execspec = $db->query($query_spec);
            $row_spec = $execspec->fetch();
            $specSize = count($row_spec);
        } elseif (strpos($_GET['resumptionToken'], 'institution')) {
            $getset1 = explode('institution_', $resumptionToken[3]);
            $getsetn1 = $getset1[1];
            $usersfrominstitution = usersFromInstitution($getsetn1);
            $sqlforinstitution = '';
            foreach ($usersfrominstitution as $usersfrominstitution) {
                $sqlforinstitution.="or b.entity_id=" . $usersfrominstitution->id . " ";
            }
            if (strlen($sqlforinstitution) > 0) {
                $sqlforinstitution = substr($sqlforinstitution, 2);
                $sqlforinstitution = " and (" . $sqlforinstitution . ")";
            } else {
                $sqlforinstitution = " and (b.entity_id=0)";
            }
            $query_spec = "select a.* from metadata_record as a join omeka_entities_relations as b on (b.relation_id=a.object_id and lower(b.type)=a.object_type) where a.validate=1 and b.relationship_id=1 " . $sqlforinstitution . "";
            $execspec = $db->query($query_spec);
            $row_spec = $execspec->fetch();
            $specSize = count($row_spec);
        } else {
            $resumptionTokensetSpec = onlyNumbers($resumptionTokensetSpec);
            if ($resumptionTokensetSpec > 0) {
                $query_spec = "select * from omeka_collections where id=" . $resumptionTokensetSpec . " ";
                $execspec = $db->query($query_spec);
                $row_spec = $execspec->fetch();
                $specSize = count($row_spec);
            } else {
                $specSize = 0;
            }
        }
    } else {
        $specSize = 0;
    }



    $testdivoffset = $resumptionTokenoffset % $MAXIDS;
    $val = $resumptionTokenmetadataprefix;

    /////////checking for error in resumptionToken/////////
    if ($datetime_resum != $resumptionTokendate) {
        $errors .= oai_error('badResumptionToken', '', $_GET['resumptionToken']);
    } elseif ((!$resumptionTokenoffset > 0) or $testdivoffset != 0) {
        $errors .= oai_error('badResumptionToken', '', $_GET['resumptionToken']);
    } elseif (!$specSize > 0) {
        $errors .= oai_error('badResumptionToken', '', $_GET['resumptionToken']);
    } elseif ($resumptionTokensetSpec != 'resources' and $resumptionTokensetSpec != 'pathways' and $resumptionTokensetSpec != 'no' and !$resumptionTokensetSpec > 0 and !strpos($_GET['resumptionToken'], 'institution')) {
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
$getsetcol = '';
$getset = '';
if (isset($_GET['set'])) {
    $getset = $_GET['set'];
    $getset = explode($set_prefix, $getset);
    $getsetn = $getset[1];
    if ($getsetn == 'resources') {
        $getset = " and a.object_type='item'";
    } elseif ($getsetn == 'pathways') {
        $getset = " and a.object_type='exhibit'";
    } elseif (strpos($_GET['set'], 'institution')) {
        $getset1 = explode('institution_', $getsetn);
        $getsetn1 = $getset1[1];
        $usersfrominstitution = usersFromInstitution($getsetn1);
        $sqlforinstitution = '';
        foreach ($usersfrominstitution as $usersfrominstitution) {
            $sqlforinstitution.="or b.entity_id=" . $usersfrominstitution->id . " ";
        }
        if (strlen($sqlforinstitution) > 0) {
            $sqlforinstitution = substr($sqlforinstitution, 2);
            $sqlforinstitution = " and (" . $sqlforinstitution . ")";
        } else {
            $sqlforinstitution = " and (b.entity_id=0)";
        }
    } else {
        $getsetn = onlyNumbers($getset[1]);
        if ($getsetn > 0) {
            $getsetcol = " and b.collection_id=" . $getsetn . "";
            $getset = '';
        } else {
            $getsetcol = " and b.collection_id=0";
            $getset = " and a.object_type='nothing'";
        }
    }
} elseif (strlen($resumptionTokensetSpec) > 0) {
    $getsetn = $resumptionTokensetSpec;
    if ($getsetn == 'resources') {
        $getset = " and a.object_type='item'";
    } elseif ($getsetn == 'pathways') {
        $getset = " and a.object_type='exhibit'";
    } elseif (strpos($_GET['resumptionToken'], 'institution')) {
        $getset1 = explode('institution_', $resumptionTokensetSpec);
        $getsetn1 = $getset1[1];
        $usersfrominstitution = usersFromInstitution($getsetn1);
        $sqlforinstitution = '';
        foreach ($usersfrominstitution as $usersfrominstitution) {
            $sqlforinstitution.="or b.entity_id=" . $usersfrominstitution->id . " ";
        }
        if (strlen($sqlforinstitution) > 0) {
            $sqlforinstitution = substr($sqlforinstitution, 2);
            $sqlforinstitution = " and (" . $sqlforinstitution . ")";
        } else {
            $sqlforinstitution = " and (b.entity_id=0)";
        }
    } elseif ($getsetn == 'no') {
        $getset = "";
    } else {
        $getsetn = onlyNumbers($resumptionTokensetSpec);
        if ($getsetn > 0) {
            $getsetcol = " and b.collection_id=" . $getsetn . "";
            $getset = '';
        } else {
            $getsetcol = " and b.collection_id=0";
            $getset = " and a.object_type='nothing'";
        }
    }
} else {
    $getset = "";
    $getsetn = "no";
}

if (isset($_GET['set']) or strlen($resumptionTokensetSpec) > 0) {

    if ($getsetn == 'resources' or $getsetn == 'pathways' or $getsetn == 'no') {
        $sqlmetadatarecord = "select a.* from metadata_record as a where a.validate=1 " . $getset . "";
    } elseif (strpos($_GET['set'], 'institution') or strpos($_GET['resumptionToken'], 'institution')) {
        $sqlmetadatarecord = "select a.* from metadata_record as a join omeka_entities_relations as b on (b.relation_id=a.object_id and lower(b.type)=a.object_type) where a.validate=1 and b.relationship_id=1 " . $sqlforinstitution . "";
    } else {
        $sqlmetadatarecord = "select a.* from metadata_record as a join omeka_items as b on a.object_id=b.id where a.validate=1 and a.object_type='item' " . $getsetcol . "";
    }
} else {
    $sqlmetadatarecord = "select a.* from metadata_record as a where a.validate=1 ";
}
//echo $sqlmetadatarecord; break;
$exec2 = $db->query($sqlmetadatarecord);
$row_itemtotal = $exec2->fetchAll();
$completeListSize = count($row_itemtotal);



if ($resumptionTokenoffset > 0 and $testdivoffset == 0) {
    $resumptionTokenoffset = " OFFSET " . $resumptionTokenoffset . "";
} else {
    $resumptionTokenoffset = " OFFSET 0";
}

$sqlmetadatarecord.= " ORDER BY a.id ASC LIMIT " . $MAXIDS . " " . $resumptionTokenoffset . " ";
//echo $sqlmetadatarecord; break;
$exec2 = $db->query($sqlmetadatarecord);
$metadatarecord = $exec2->fetchAll();

/* $query_item = "select * from omeka_items where public=1 " . $getset . " ORDER BY id ASC LIMIT " . $MAXIDS . " " . $resumptionTokenoffset . " ";
  $exec = $db->query($query_item);
  $row_item = $exec->fetchAll(); */

$count_results = count($metadatarecord);
if (!$count_results > 0) {
    $errors .= oai_error('noRecordsMatch');
}

if (empty($errors)) { //if no errors
    $output .= "<ListIdentifiers>\n";


    foreach ($metadatarecord as $metadatarecord) {


        if ($metadatarecord['object_type'] == 'item') {
            $oai_collection_general = $set_prefix . 'resources';
            $varforidentfiobject = 'item';

            $sqlmetadatarecordfromomeka = "select * from omeka_items where id=" . $metadatarecord['object_id'] . " ";
//echo $sqlmetadatarecord; //break;
            $execfromomeka = $db->query($sqlmetadatarecordfromomeka);
            $metadatarecordfromomeka = $execfromomeka->fetch();

            if (strlen($metadatarecordfromomeka['collection_id']) > 0) {
                $sqlcollection = "select * from omeka_collections where id=" . $metadatarecordfromomeka['collection_id'] . " ";
//echo $sqlmetadatarecord; //break;
                $execcollection = $db->query($sqlcollection);
                $oai_collection = $execcollection->fetch();
            } else {
                $oai_collection['id'] = '';
            }
        } else {

            $oai_collection_general = $set_prefix . 'pathways';
            $varforidentfiobject = 'exhibit';
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
        if (strlen($selectvaluesvalue2[1]) > 0) {
            $datestamp.=$selectvaluesvalue2[1];
        } else {
            $datestamp.='00:00:00';
        }
        $datestamp.='Z';


        $output .= '<record>' . "\n";
        $output .= '<header>' . "\n";
        $output .= '<identifier>';
        $output .= 'oai:' . $repositoryIdentifier . ':' . $metadatarecord['object_id'] . ':' . $varforidentfiobject;
        $output .= '</identifier>' . "\n";
        $output .= '<datestamp>';
        $output .= $datestamp;
        $output .= '</datestamp>' . "\n";
        $output .= '<setSpec>';
        $output .=$oai_collection_general;
        $output .= '</setSpec>' . "\n";
        if (strpos($_GET['set'], 'institution') or strpos($_GET['resumptionToken'], 'institution')) {
            $output .= '<setSpec>';
            $output .=$set_prefix . '' . $getsetn;
            $output .= '</setSpec>' . "\n";
        }
        if (strlen($oai_collection['id']) > 0) {
            $output .= '<setSpec>';
            $output .=$set_prefix . '' . $oai_collection['id'];
            $output .= '</setSpec>' . "\n";
        }
        $output .= '</header>' . "\n";



        $output .= '</record>' . "\n";
    }

///////////////resumptionToken creation

    $newresumptionTokenoffset = $resumptionTokenoffsetn + $MAXIDS;
    if ($completeListSize - $newresumptionTokenoffset > 0) {
        $output .= '<resumptionToken expirationDate="' . $datetime_resum2 . 'T23:59:59Z" completeListSize="' . $completeListSize . '" cursor="' . $MAXIDS . '">';
        $output .=$datetime_resum . ':' . $newresumptionTokenoffset . ':' . $metadataPrefix . ':' . $getsetn;
        $output .= '</resumptionToken>' . "\n";
    }
// end ListRecords
    $output .=
            '</ListIdentifiers>' . "\n";
}//if no errors!!!
?>
