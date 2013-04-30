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
    }
}

if (!isset($args['identifier'])) {
    $errors .= oai_error('missingArgument', 'identifier');
}
//if (!isset($args['metadataPrefix'])) {
//    $errors .= oai_error('missingArgument', 'metadataPrefix');
//}
///////explode identifier for use/////////////
$identifier = explode('scorm:' . $repositoryIdentifier . ':', $identifier);
$identifier = $identifier[1];
$identifier = onlyNumbers($identifier);


/* $XMLHEADER =
  '<?xml version="1.0" encoding="UTF-8"?>
  <OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"  xmlns:lom="http://ltsc.ieee.org/xsd/LOM" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">' . "\n";
 */
$XMLHEADER = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<manifest xmlns="http://www.imsproject.org/xsd/imscp_rootv1p1p2" xmlns:imsmd="http://ltsc.ieee.org/xsd/LOM" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:adlcp="http://www.adlnet.org/xsd/adlcp_rootv1p2" identifier="MANIFEST-' . $identifier . '" xsi:schemaLocation="http://www.imsproject.org/xsd/imscp_rootv1p1p2 imscp_rootv1p1p2.xsd http://ltsc.ieee.org/xsd/LOM lom.xsd http://www.adlnet.org/xsd/adlcp_rootv1p2 adlcp_rootv1p2.xsd">' . "\n";



$xmlheader = $XMLHEADER .
        "<!-- Each pathway template is described as a whole with metadata according to the LOM NE AP. -->";

////////////query if exist metadata record in the db!!!!//////////////////////////////
$sqlmetadatarecord = "select * from metadata_record where object_id=" . $identifier . " and object_type='exhibit' and validate=1";
//echo $sqlmetadatarecord; //break;
$exec2 = $db->query($sqlmetadatarecord);
$metadatarecord = $exec2->fetch();

if (!isset($metadatarecord['id'])) {
    $errors .= oai_error('idDoesNotExist', NULL, $identifier);
}

if (empty($errors)) { //if no errors
    $sqlomekaitem = "select * from omeka_exhibits where id=" . $identifier . "";
//echo $sqlmetadatarecord; //break;
    $execomekaitem = $db->query($sqlomekaitem);
    $omekaitem = $execomekaitem->fetch();




    $sqlmetadatarecordvalue = "select * from metadata_element_value where record_id=" . $metadatarecord['id'] . " ORDER BY element_hierarchy ASC";
    $exec = $db->query($sqlmetadatarecordvalue);
    $metadatarecordvalue_res = $exec->fetchAll();
//echo $sqlmetadatarecordvalue; break;
//$metadatarecordvalue_res=mysql_query($sqlmetadatarecordvalue);
//$metadatarecordvalue=mysql_fetch_array($metadatarecordvalue_res);



    $output .= '<metadata>' . "\n";
    $output .= '<imsmd:lom>' . "\n";

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

            $output.= '<imsmd:' . $datageneral3['machine_name'] . '>' . "\n";
            $output.= $output2;
            $output.= '</imsmd:' . $datageneral3['machine_name'] . '>' . "\n";
        }
    }//datageneral3



    $output .= '</imsmd:lom>' . "\n";
    $output .= '</metadata>' . "\n";



    $output .= '<!-- The first level of the <organization> hierarchy corresponds to the phases (Acts) of the educational pathway.
         The second and last level of the hierarchy, e.g. the leaf <item> elements, correspond to the Activities within each phase 
		 of the educational pathway.
         Each leaf <item> element (activity) points to a <resource> element within the <resources> section of the manifest.-->';

    $sqlehibitsections = "select * from omeka_sections where exhibit_id=" . $omekaitem['id'] . " ";
//echo $sqlmetadatarecord; //break;
    $ehibitsections = $db->query($sqlehibitsections);
    $sections = $ehibitsections->fetchAll();


    $output .= '<organizations default="ORG-Pathway">' . "\n";
    $output .= '<organization identifier="ORG-' . $omekaitem['id'] . '" structure="hierarchical">' . "\n";
    $output .= '<title>' . "\n"; ///for pathway
    $output .= '<![CDATA[' . $omekaitem['title'] . ']]>'; ///for pathway
    $output .= '</title>' . "\n";
    foreach ($sections as $sections2) {

        $sqlsectionpages = "select * from omeka_section_pages where section_id=" . $sections2['id'] . " ";
//echo $sqlmetadatarecord; //break;
        $sectionpages = $db->query($sqlsectionpages);
        $pages = $sectionpages->fetchAll();

        $output .= '<item identifier="ITEM-' . $sections2['id'] . '" isvisible="true">' . "\n";
        $output .= '<title>' . "\n"; //for section
        $output .= $sections2['title']; ///for section
        $output .= '</title>' . "\n";
        foreach ($pages as $pages) {
            $output .= '<item identifier="ITEM-' . $sections2['id'] . '-' . $pages['id'] . '" identifierref="RES-' . $sections2['id'] . '-' . $pages['id'] . '" isvisible="true">' . "\n";
            $output .= '<title>' . "\n"; //for page
            $output .= $pages['title']; ///for page
            $output .= '</title>' . "\n";
            $output .= '</item>' . "\n";
        }

        $output .= '</item>' . "\n";
    }

    $output .= '</organization>' . "\n";
    $output .= '</organizations>' . "\n";



    $output .= '<!-- Each leaf <item> element (activity) points to a <resource> element within the <resources> section of the manifest. 
	     Each resource may contain a number of <file> subelements. The first of these elements corresponds to the activity description 
         referred from the href attribute of the <resource> element, e.g. href="Activity1description.html". An Activity can be optionally described
		 with metadata (metadata section just below opening <resource> tag). Since it is a pathway template, some of them (e.g. description) can contain 
		 guidelines/instructions on how to develop this activity (see the first resource below). 
		 Note: Theoretically, with the activity description in the activity metadata, the Activity1description.html file could be omitted. 
		 However, in this case the manifest file would not be valid. Moreover, using this file may help the pathway tool present the corresponding 
		 activity description (e.g. instructions on what this activity should contain) directly. Else, it should be able to read the description 
		 from the activity metadata and present it to the teacher developing the pathway.
		 
		 If other <file> elements are presented, these correspond to supporting material that an activity may optionally have. Again, since this is a 
		 pathway template, the role of these <files> elements is to propose characteristics of appropriate supporting material that can be
		 used in this activity. This is done with the use of metadata within each <file> element of this type (see for example the first resource), 
		 which on the side of the pathway tool should be translated into filters that will be used to retrieve appropriate content from NE, ARIADNE, 
		 Europeana etc. using the corresponding APIs.
                 Files are seperated in resources that are supporting materials and in resources that are in the text of an (activity).
                 The files that has the attribute "inText" <file inText="yes"> are those which are presented in the text.
		 -->';


    $output .= '<resources>' . "\n";
    $output .= '<!-- A resource element in its most rich form containing activity metadata and <files> with metadata defining the characteristics
		of appropriate supporting material for this activity-->';

    foreach ($sections as $sections2) {

        $sqlsectionpages = "select * from omeka_section_pages where section_id=" . $sections2['id'] . " ";
//echo $sqlmetadatarecord; //break;
        $sectionpages = $db->query($sqlsectionpages);
        $pages = $sectionpages->fetchAll();

        foreach ($pages as $pages) {
            $output .= '<resource identifier="RES-' . $sections2['id'] . '-' . $pages['id'] . '" >' . "\n";
            $output .= '<metadata>' . "\n";
            $output .= '<imsmd:lom>' . "\n";
            $output .= '<imsmd:general>' . "\n";
            $output .= '<imsmd:title>' . "\n";
            $output .= '<imsmd:string>' . "\n";
            $output .= $pages['title']; ///for page
            $output .= '</imsmd:string>' . "\n";
            $output .= '</imsmd:title>' . "\n";
            $output .= '<imsmd:description>' . "\n";
            $output .= '<imsmd:string>' . "\n";
            $output .= '<![CDATA[';
            $sqlpagestext = "select * from omeka_items_section_pages where page_id=" . $pages['id'] . " ORDER BY `order` ASC";
//echo $sqlmetadatarecord; //break;
            $pagestextr = $db->query($sqlpagestext);
            $pagestext2 = $pagestextr->fetchAll();
            foreach ($pagestext2 as $pagestext) {
                ////replace strange space ascii character////////////////
                $pagestexttext = trim ($pagestext['text']);
                $pagestexttext = trim($pagestexttext,chr(0xC2).chr(0xA0).chr(0xb)); 
                $pagestexttext = str_replace("", " ", $pagestext['text']); 
                
                $output .= trim ($pagestexttext) . "<br>";
            }
            $output .= ']]>';
            $output .= '</imsmd:string>' . "\n";
            $output .= '</imsmd:description>' . "\n";
            $output .= '</imsmd:general>' . "\n";
            $output .= '</imsmd:lom>' . "\n";
            $output .= '</metadata>' . "\n";


            foreach ($pagestext2 as $pagestext3) {
                if ($pagestext3['item_id'] > 0) {


                    $sqlmetadatarecord = "select * from metadata_record where object_id=" . $pagestext3['item_id'] . " and object_type='item' and validate=1";
//echo $sqlmetadatarecord; //break;
                    $exec2 = $db->query($sqlmetadatarecord);
                    $metadatarecord = $exec2->fetch();

                    $sqlomekaitem = "select * from omeka_items where id=" . $pagestext3['item_id'] . "";
//echo $sqlmetadatarecord; //break;
                    $execomekaitem = $db->query($sqlomekaitem);
                    $omekaitem = $execomekaitem->fetch();


                    if ($metadatarecord['id'] > 0) {
                        $output .= '<file inText="yes">' . "\n";
                        $sqlmetadatarecordvalue = "select * from metadata_element_value where record_id=" . $metadatarecord['id'] . " ORDER BY element_hierarchy ASC";
                        $exec = $db->query($sqlmetadatarecordvalue);
                        $metadatarecordvalue_res = $exec->fetchAll();
//echo $sqlmetadatarecordvalue; break;
//$metadatarecordvalue_res=mysql_query($sqlmetadatarecordvalue);
//$metadatarecordvalue=mysql_fetch_array($metadatarecordvalue_res);



                        $output .= '<metadata>' . "\n";
                        $output .= '<imsmd:lom>' . "\n";

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

                                $output.= '<imsmd:' . $datageneral3['machine_name'] . '>' . "\n";
                                $output.= $output2;
                                $output.= '</imsmd:' . $datageneral3['machine_name'] . '>' . "\n";
                            }
                        }//datageneral3



                        $output .= '</imsmd:lom>' . "\n";
                        $output .= '</metadata>' . "\n";


                        $output .= '</file>' . "\n";
                    }///if($metadatarecord['id']>0){
                }
            }

            $maxIdSQL = "select * from omeka_teasers where exhibit_id=" . $identifier . " and type!='europeana' and pg_id=" . $pages['id'] . " and sec_id=" . $sections2['id'];
//echo $maxIdSQL;break;
            $exec = $db->query($maxIdSQL);
            $result_multi = $exec->fetchAll();
            foreach ($result_multi as $result_multi) {
                if ($result_multi['item_id'] > 0) {

                    $sqlmetadatarecord = "select * from metadata_record where object_id=" . $result_multi['item_id'] . " and object_type='item' and validate=1";
//echo $sqlmetadatarecord; //break;
                    $exec2 = $db->query($sqlmetadatarecord);
                    $metadatarecord = $exec2->fetch();

                    $sqlomekaitem = "select * from omeka_items where id=" . $result_multi['item_id'] . "";
//echo $sqlmetadatarecord; //break;
                    $execomekaitem = $db->query($sqlomekaitem);
                    $omekaitem = $execomekaitem->fetch();


                    if ($metadatarecord['id'] > 0) {

                        $sqlmetadatarecordvalue = "select * from metadata_element_value where record_id=" . $metadatarecord['id'] . " ORDER BY element_hierarchy ASC";
                        $exec = $db->query($sqlmetadatarecordvalue);
                        $metadatarecordvalue_res = $exec->fetchAll();
//echo $sqlmetadatarecordvalue; break;
//$metadatarecordvalue_res=mysql_query($sqlmetadatarecordvalue);
//$metadatarecordvalue=mysql_fetch_array($metadatarecordvalue_res);


                        $output .= '<file>' . "\n";
                        $output .= '<metadata>' . "\n";
                        $output .= '<imsmd:lom>' . "\n";

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

                                $output.= '<imsmd:' . $datageneral3['machine_name'] . '>' . "\n";
                                $output.= $output2;
                                $output.= '</imsmd:' . $datageneral3['machine_name'] . '>' . "\n";
                            }
                        }//datageneral3



                        $output .= '</imsmd:lom>' . "\n";
                        $output .= '</metadata>' . "\n";


                        $output .= '</file>' . "\n";
                    }
                }
            }




            $output .= '</resource>' . "\n";
        }
    }

    $output .= '</resources>' . "\n";
}//if no errors!!!
?>
