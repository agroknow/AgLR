<?php
//if ($handle = opendir('./xmls/')) {
//      /* This is the correct way to loop over the directory. */
//    while (false !== ($entry = readdir($handle))) {
//	if($entry!='.' and $entry!='..'){



$xml = '';
$output = '';
libxml_use_internal_errors(false);
$entry = 'http_.s..s.confolio.vm.grnet.gr.s.scam.s.12.s.entry.s.1277.xml';
//echo 'localhost/parselom/' . $entry . '';
//$xml = @simplexml_load_file('education.natural-europe.eu/organic-edunet/admin/items/xmls/'.$entry.'', NULL, LIBXML_NOERROR | LIBXML_NOWARNING);
$xml = @simplexml_load_file('http://localhost/parselom/' . $entry . '', NULL, LIBXML_NOERROR | LIBXML_NOWARNING);
if ($xml === false) {
    echo "An Error occured. Please try again later. Thank you!";
}
//$xml = simplexml_load_file('http://ariadne.cs.kuleuven.be/ariadne-partners/api/sqitarget?query=learning&start='.$startPage.'&size=12&lang=plql1&format=lom', NULL, LIBXML_NOERROR | LIBXML_NOWARNING);

if ($xml) {
    global $item_id;
    $item_id = insertnewitemfromxml($xml);
    $xml->getName();
    foreach ($xml as $xml) {
        $xmlname = $xml->getName();
        //echo "<br><u>".$xmlname."</u><br>";

        $xmlname_gelement = findidsfromxmlname($xmlname);
        global $multi;
        $multi = 0;
        global $previous_getgeneralname;
        $previous_getgeneralname = '';
        foreach ($xml->children() as $getgeneral) {
            $getgeneralname = $getgeneral->getName();
            $xmlname_element = findidsfromxmlname($getgeneralname, $xmlname_gelement['id']);
            //echo $getgeneralname."&nbsp".$xmlname_element['id']."<br>";
            if ($getgeneralname != $previous_getgeneralname) {
                $multi = 0;
            }
            create_the_query_for_ingest($xmlname_gelement, $xmlname_element, $getgeneralname, $xmlname, $getgeneral);
            $previous_getgeneralname = $getgeneralname;
        }
    }
}

function create_the_query_for_ingest($xmlname_gelement, $xmlname_element, $getgeneralname, $xmlname, $getgeneral) {
    global $multi;
    global $previous_getgeneralname;
    //echo "<br><u>" . $xmlname . "." . $getgeneralname . "&nbsp" . $xmlname_element['id'] . "</u><br>";
    if ($xmlname == 'general') {

        //echo 'multi=' . $multi . '<br>';
        if ($getgeneralname == 'identifier') {
            $multi+=1;
            $i = 1;
            savelomelementforxmlparsing($xmlname_element['id'], 'Parent Element', $item_id, 'none', $i, $multi);
            foreach ($getgeneral as $string) {
                //$i+=1;
                $stringname = $string->getName();
                $xmlname_element2 = findidsfromxmlname($stringname, $xmlname_element['id']);
                if ($stringname == 'catalog') {
                    savelomelementforxmlparsing($xmlname_element2['id'], $string, $item_id, 'none', $i, $multi, NULL, 0);
                }
                if ($stringname == 'entry') {
                    savelomelementforxmlparsing($xmlname_element2['id'], $string, $item_id, 'none', $i, $multi, NULL, 0);
                }

                //catalog-entry
            }
        } //identifier

        if ($getgeneralname == 'title') {

            $multi+=1;
            $i = 1;
            langstring($getgeneral, $xmlname_element['id'], $i, $multi);
        } //if($getgeneralname=='title')

        if ($getgeneralname == 'language') {

            $multi+=1;
            $i = 0;

            $vocid = findvocabularyid($getgeneral, $xmlname_element['element_id']);
            $i+=1;
            savelomelementforxmlparsing($xmlname_element['id'], NULL, $item_id, 'none', $i, $multi, $vocid);
        } //if($getgeneralname=='language') 

        if ($getgeneralname == 'description') {
            $multi+=1;
            $i = 1;
            langstring($getgeneral, $xmlname_element['id'], $i, $multi);
        } //if($getgeneralname=='description') 

        if ($getgeneralname == 'keyword') {
            $multi+=1;
            $i = 1;
            langstring($getgeneral, $xmlname_element['id'], $i, $multi);
        } //if($getgeneralname=='keyword')

        if ($getgeneralname == 'coverage') {
            $multi+=1;
            $i = 0;
            foreach ($getgeneral as $string) {
                $i+=1;
                $vocid = findvocabularyid($string, $xmlname_element['element_id']);
                //echo $vocid . '123';
                savelomelementforxmlparsing($xmlname_element['id'], NULL, $item_id, 'none', $i, $multi, $vocid);
                //echo $string."-".$string['language']."<br>";
            }
        } //if($getgeneralname=='coverage')
        if ($getgeneralname == 'structure') {
            $multi+=1;
            $i = 0;
            //print_r($getgeneral);
            foreach ($getgeneral->value as $string) {
                $i+=1;
                $vocid = findvocabularyid($string, $xmlname_element['element_id']);
                //echo $vocid . '123';
                savelomelementforxmlparsing($xmlname_element['id'], NULL, $item_id, 'none', $i, $multi, $vocid);
                //echo $string."-".$string['language']."<br>";
            }
        } //if($getgeneralname=='structure') 
        if ($getgeneralname == 'aggregationLevel') {
            $multi+=1;
            $i = 0;
            //print_r($getgeneral);
            foreach ($getgeneral->value as $string) {
                $i+=1;
                $vocid = findvocabularyid($string, $xmlname_element['element_id']);
                //echo $vocid . '123';
                savelomelementforxmlparsing($xmlname_element['id'], NULL, $item_id, 'none', $i, $multi, $vocid);
                //echo $string."-".$string['language']."<br>";
            }
        } //if($getgeneralname=='aggregationLevel') 
    }/////////end general



    if ($xmlname == 'lifeCycle') {

        //echo 'multi=' . $multi . '<br>';
        if ($getgeneralname == 'version') {
            $multi+=1;
            $i = 1;
            langstring($getgeneral, $xmlname_element['id'], $i, $multi);
        } //if($getgeneralname=='version')

        if ($getgeneralname == 'status') {
            $multi+=1;
            $i = 0;
            //print_r($getgeneral);
            foreach ($getgeneral->value as $string) {
                $i+=1;
                $vocid = findvocabularyid($string, $xmlname_element['element_id']);
                //echo $vocid . '123';
                savelomelementforxmlparsing($xmlname_element['id'], NULL, $item_id, 'none', $i, $multi, $vocid);
                //echo $string."-".$string['language']."<br>";
            }
        } //if($getgeneralname=='status')

        if ($getgeneralname == 'contribute') {
            $multi+=1;
            $i = 0;
            $xmlname_element1 = findidsfromxmlname($getgeneralname, $xmlname_gelement['id']);
            savelomelementforxmlparsing($xmlname_element1['id'], 'Parent Element', $item_id, 'none', '1', $multi);
            $xmlname_element2 = findidsfromxmlname('role', $xmlname_element1['id']);
            $vocid = findvocabularyid($getgeneral->role->value, $xmlname_element2['element_id']);
            savelomelementforxmlparsing($xmlname_element2['id'], NULL, $item_id, 'none', '1', $multi, $vocid);
            $xmlname_element3 = findidsfromxmlname('date', $xmlname_element1['id']);
            savelomelementforxmlparsing($xmlname_element3['id'], $getgeneral->date->dateTime, $item_id, 'none', '1', $multi);

            foreach ($getgeneral->entity as $string) {
                $i+=1;

                if (isset($string)) {
                    //print($string)."<br>";
//                        $vcardstart = explode("BEGIN:VCARD", $string);
//                        $vcard = $vcardstart[1];
//                        $vcardstart = explode("VERSION:3.0", $vcard);
//                        $vcard = $vcardstart[0];

                    $vcard = $string;


                    if (stripos($vcard, "\nN:")) {
                        $name = explode("\nN:", $vcard);
                        $name = explode("\n", $name[1]);
                        $name = $name[0];
                    } else {
                        $name = "";
                    }

                    if (stripos($vcard, "\nORG:")) {
                        $org = explode("\nORG:", $vcard);
                        $org = explode("\n", $org[1]);
                        $org = $org[0];
                    } else {
                        $org = "";
                    }
                    if (stripos($vcard, "\nFN:")) {
                        $fname = explode("\nFN:", $vcard);

                        $fname = explode("\n", $fname[1]);
                        $fname = $fname[0];

                        if (strlen($fname) > 0) {
                            $entity = explode($name . ' ', $fname);
                            if (isset($entity['1'])) {
                                $surname = $entity['1'];
                            } else {
                                $surname = "";
                            }
                            // echo $name."<br>";
                            // echo $surname."<br>";
                        }//if isset entity:
                    } else {
                        $fname = "";
                    }

                    if (stripos($vcard, "\nEMAIL;")) {
                        $email = explode("\nEMAIL;", $vcard);
                        $email = explode("\n", $email[1]);
                        $email = explode("INTERNET:", $email[0]);
                        $email = $email[1];
                    } else {
                        $email = "";
                    }
                }
                $xmlname_element4 = findidsfromxmlname('entity', $xmlname_element1['id']);
                vcardinsert($xmlname_element4['id'], '', $item_id, 'none', $i, $multi, $name, $surname, $email, $org);
                //savelomelementforxmlparsing('41',$string,$item_id,'none',$i,$multi);
                //echo $string."-".$string['language']."<br>";
            }
        } //contribute
    }/////////end lifeCycle


    if ($xmlname == 'metaMetadata') {

        //echo 'multi=' . $multi . '<br>';
        if ($getgeneralname == 'identifier') {
            $multi+=1;
            $i = 1;
            savelomelementforxmlparsing($xmlname_element['id'], 'Parent Element', $item_id, 'none', $i, $multi);
            foreach ($getgeneral as $string) {
                //$i+=1;
                $stringname = $string->getName();
                $xmlname_element2 = findidsfromxmlname($stringname, $xmlname_element['id']);
                if ($stringname == 'catalog') {
                    savelomelementforxmlparsing($xmlname_element2['id'], $string, $item_id, 'none', $i, $multi, NULL, 0);
                }
                if ($stringname == 'entry') {
                    savelomelementforxmlparsing($xmlname_element2['id'], $string, $item_id, 'none', $i, $multi, NULL, 0);
                }

                //catalog-entry
            }
        } //identifier

        if ($getgeneralname == 'contribute') {
            $multi+=1;
            $i = 0;
            $xmlname_element1 = findidsfromxmlname($getgeneralname, $xmlname_gelement['id']);
            savelomelementforxmlparsing($xmlname_element1['id'], 'Parent Element', $item_id, 'none', '1', $multi);
            $xmlname_element2 = findidsfromxmlname('role', $xmlname_element1['id']);
            $vocid = findvocabularyid($getgeneral->role->value, $xmlname_element2['element_id']);
            savelomelementforxmlparsing($xmlname_element2['id'], NULL, $item_id, 'none', '1', $multi, $vocid);
            $xmlname_element3 = findidsfromxmlname('date', $xmlname_element1['id']);
            savelomelementforxmlparsing($xmlname_element3['id'], $getgeneral->date->dateTime, $item_id, 'none', '1', $multi);

            foreach ($getgeneral->entity as $string) {
                $i+=1;

                if (isset($string)) {
                    //print($string)."<br>";
//                        $vcardstart = explode("BEGIN:VCARD", $string);
//                        $vcard = $vcardstart[1];
//                        $vcardstart = explode("VERSION:3.0", $vcard);
//                        $vcard = $vcardstart[0];

                    $vcard = $string;


                    if (stripos($vcard, "\nN:")) {
                        $name = explode("\nN:", $vcard);
                        $name = explode("\n", $name[1]);
                        $name = $name[0];
                    } else {
                        $name = "";
                    }

                    if (stripos($vcard, "\nORG:")) {
                        $org = explode("\nORG:", $vcard);
                        $org = explode("\n", $org[1]);
                        $org = $org[0];
                    } else {
                        $org = "";
                    }
                    if (stripos($vcard, "\nFN:")) {
                        $fname = explode("\nFN:", $vcard);

                        $fname = explode("\n", $fname[1]);
                        $fname = $fname[0];

                        if (strlen($fname) > 0) {
                            $entity = explode($name . ' ', $fname);
                            if (isset($entity['1'])) {
                                $surname = $entity['1'];
                            } else {
                                $surname = "";
                            }
                            // echo $name."<br>";
                            // echo $surname."<br>";
                        }//if isset entity:
                    } else {
                        $fname = "";
                    }

                    if (stripos($vcard, "\nEMAIL;")) {
                        $email = explode("\nEMAIL;", $vcard);
                        $email = explode("\n", $email[1]);
                        $email = explode("INTERNET:", $email[0]);
                        $email = $email[1];
                    } else {
                        $email = "";
                    }
                }
                $xmlname_element4 = findidsfromxmlname('entity', $xmlname_element1['id']);
                vcardinsert($xmlname_element4['id'], '', $item_id, 'none', $i, $multi, $name, $surname, $email, $org);
                //savelomelementforxmlparsing('41',$string,$item_id,'none',$i,$multi);
                //echo $string."-".$string['language']."<br>";
            }
        } //contribute

        if ($getgeneralname == 'metadataSchema') {
            $multi+=1;
            $i = 0;
            $i+=1;
            savelomelementforxmlparsing($xmlname_element['id'], $getgeneral, $item_id, 'none', $i, $multi);
        } //if($getgeneralname=='metadataSchema'){
        if ($getgeneralname == 'language') {

            $multi+=1;
            $i = 0;

            $vocid = findvocabularyid($getgeneral, $xmlname_element['element_id']);
            $i+=1;
            savelomelementforxmlparsing($xmlname_element['id'], NULL, $item_id, 'none', $i, $multi, $vocid);
        } //if($getgeneralname=='language')
    }/////////end metaMetadata

    if ($xmlname == 'technical') {

        //echo 'multi=' . $multi . '<br>';
        if ($getgeneralname == 'format') {
            $multi+=1;
            $i = 0;

            $vocid = findvocabularyid($getgeneral, $xmlname_element['element_id']);
            $i+=1;
            savelomelementforxmlparsing($xmlname_element['id'], NULL, $item_id, 'none', $i, $multi, $vocid);
        } //if($getgeneralname=='format') 
        if ($getgeneralname == 'location') {
            $multi+=1;
            $i = 0;
            $i+=1;
            savelomelementforxmlparsing($xmlname_element['id'], $getgeneral, $item_id, 'none', $i, $multi, NULL, 0);
        } //if($getgeneralname=='location')

        if ($getgeneralname == 'installationRemarks') {
            $multi+=1;
            $i = 1;
            langstring($getgeneral, $xmlname_element['id'], $i, $multi);
        } //if($getgeneralname=='installationRemarks')

        if ($getgeneralname == 'otherPlatformRequirements') {
            $multi+=1;
            $i = 1;
            langstring($getgeneral, $xmlname_element['id'], $i, $multi);
        } //if($getgeneralname=='otherPlatformRequirements')
    }/////////end technical

    if ($xmlname == 'educational') {

        //echo 'multi=' . $multi . '<br>';



        if ($getgeneralname == 'interactivityType') {

            $multi+=1;
            $i = 0;
            //print_r($getgeneral);
            foreach ($getgeneral->value as $string) {
                $i+=1;
                $vocid = findvocabularyid($string, $xmlname_element['element_id']);
                //echo $vocid . '123';
                savelomelementforxmlparsing($xmlname_element['id'], NULL, $item_id, 'none', $i, $multi, $vocid);
                //echo $string."-".$string['language']."<br>";
            }
        } //if($getgeneralname=='interactivityType'){ 



        if ($getgeneralname == 'learningResourceType') {
            $multi+=1;
            $i = 0;
            //print_r($getgeneral);
            foreach ($getgeneral->value as $string) {
                $i+=1;
                $vocid = findvocabularyid($string, $xmlname_element['element_id']);
                //echo $vocid . '123';
                savelomelementforxmlparsing($xmlname_element['id'], NULL, $item_id, 'none', $i, $multi, $vocid);
                //echo $string."-".$string['language']."<br>";
            }
        } //if($getgeneralname=='learningResourceType'){ 

        if ($getgeneralname == 'interactivityLevel') {
            $multi+=1;
            $i = 0;
            //print_r($getgeneral);
            foreach ($getgeneral->value as $string) {
                $i+=1;
                $vocid = findvocabularyid($string, $xmlname_element['element_id']);
                //echo $vocid . '123';
                savelomelementforxmlparsing($xmlname_element['id'], NULL, $item_id, 'none', $i, $multi, $vocid);
                //echo $string."-".$string['language']."<br>";
            }
        } //if($getgeneralname=='interactivityLevel'){ 



        if ($getgeneralname == 'semanticDensity') {
            $multi+=1;
            $i = 0;
            //print_r($getgeneral);
            foreach ($getgeneral->value as $string) {
                $i+=1;
                $vocid = findvocabularyid($string, $xmlname_element['element_id']);
                //echo $vocid . '123';
                savelomelementforxmlparsing($xmlname_element['id'], NULL, $item_id, 'none', $i, $multi, $vocid);
                //echo $string."-".$string['language']."<br>";
            }
        } //if($getgeneralname=='semanticDensity'){ 


        if ($getgeneralname == 'intendedEndUserRole') {
            $multi+=1;
            $i = 0;
            //print_r($getgeneral);
            foreach ($getgeneral->value as $string) {
                $i+=1;
                $vocid = findvocabularyid($string, $xmlname_element['element_id']);
                //echo $vocid . '123';
                savelomelementforxmlparsing($xmlname_element['id'], NULL, $item_id, 'none', $i, $multi, $vocid);
                //echo $string."-".$string['language']."<br>";
            }
        } //if($getgeneralname=='intendedEndUserRole'){ 

        if ($getgeneralname == 'context') {
            $multi+=1;
            $i = 0;
            //print_r($getgeneral);
            foreach ($getgeneral->value as $string) {
                $i+=1;
                $vocid = findvocabularyid($string, $xmlname_element['element_id']);
                //echo $vocid . '123';
                savelomelementforxmlparsing($xmlname_element['id'], NULL, $item_id, 'none', $i, $multi, $vocid);
                //echo $string."-".$string['language']."<br>";
            }
        } //if($getgeneralname=='context'){

        if ($getgeneralname == 'difficulty') {
            $multi+=1;
            $i = 0;
            //print_r($getgeneral);
            foreach ($getgeneral->value as $string) {
                $i+=1;
                $vocid = findvocabularyid($string, $xmlname_element['element_id']);
                //echo $vocid . '123';
                savelomelementforxmlparsing($xmlname_element['id'], NULL, $item_id, 'none', $i, $multi, $vocid);
                //echo $string."-".$string['language']."<br>";
            }
        } //if($getgeneralname=='difficulty'){


        if ($getgeneralname == 'typicalAgeRange') {
            $multi+=1;
            $i = 1;
            langstring($getgeneral, $xmlname_element['id'], $i, $multi);
        } //if($getgeneralname=='typicalAgeRange'){ 

        if ($getgeneralname == 'language') {
            $multi+=1;
            $i = 0;

            $vocid = findvocabularyid($getgeneral, $xmlname_element['element_id']);
            $i+=1;
            savelomelementforxmlparsing($xmlname_element['id'], NULL, $item_id, 'none', $i, $multi, $vocid);
        } //if($getgeneralname=='language'){

        if ($getgeneralname == 'description') {
            $multi+=1;
            $i = 1;
            langstring($getgeneral, $xmlname_element['id'], $i, $multi);
        } //if($getgeneralname=='description'){
    }/////////end educational

    if ($xmlname == 'rights') {
        //echo 'multi=' . $multi . '<br>';
        if ($getgeneralname == 'cost') {
            $multi+=1;
            $i = 0;
            //print_r($getgeneral);
            foreach ($getgeneral->value as $string) {
                $i+=1;
                savelomelementforxmlparsing($xmlname_element['id'], $string, $item_id, 'none', $i, $multi);
                //echo $string."-".$string['language']."<br>";
            }
        } //if($getgeneralname=='cost')

        if ($getgeneralname == 'copyrightAndOtherRestrictions') {
            $multi+=1;
            $i = 0;
            //print_r($getgeneral);
            foreach ($getgeneral->value as $string) {
                $i+=1;
                savelomelementforxmlparsing($xmlname_element['id'], $string, $item_id, 'none', $i, $multi);
                //echo $string."-".$string['language']."<br>";
            }
        } //if($getgeneralname=='copyrightAndOtherRestrictions')

        if ($getgeneralname == 'description') {
            $multi+=1;
            $i = 0;
            $right1 = '';
            $right2 = '';
            $right3 = '';
            foreach ($getgeneral as $string) {

                if (stripos($string, 'creativecommons.org/licenses/by/3.0')) {
                    $right1 = 'yes';
                    $right2 = 'yes';
                } elseif (stripos($string, 'creativecommons.org/licenses/by-nd/3.0')) {
                    $right1 = 'yes';
                    $right2 = 'no';
                } elseif (stripos($string, 'creativecommons.org/licenses/by-sa/3.0')) {
                    $right1 = 'yes';
                    $right2 = 'Yes, if others share alike';
                } elseif (stripos($string, 'creativecommons.org/licenses/by-nc/3.0')) {
                    $right1 = 'no';
                    $right2 = 'yes';
                } elseif (stripos($string, 'creativecommons.org/licenses/by-nc-nd/3.0')) {
                    $right1 = 'no';
                    $right2 = 'no';
                } elseif (stripos($string, 'creativecommons.org/licenses/by-nc-sa/3.0')) {
                    $right1 = 'no';
                    $right2 = 'Yes, if others share alike';
                } else {
                    $right3 = $string;
                }
            }
            if (strlen($right3) > 0) {
                savelomelementforxmlparsing($xmlname_element['id'], $string, $item_id, 'none', $i, $multi);
            } else {
                savelomelementforxmlparsing('22', $right1, $item_id, 'none', $i, $multi);
                savelomelementforxmlparsing('23', $right2, $item_id, 'none', $i, $multi);
            }
        }
    }/////////end rights
    if ($xmlname == 'relation') {
        //echo 'multi=' . $multi . '<br>';

        if ($getgeneralname == 'kind') {
            $multi+=1;
            $i = 0;
            //print_r($getgeneral);
            foreach ($getgeneral->value as $string) {
                $i+=1;
                $vocid = findvocabularyid($string, $xmlname_element['element_id']);
                //echo $vocid . '123';
                savelomelementforxmlparsing($xmlname_element['id'], NULL, $item_id, 'none', $i, $multi, $vocid);
                //echo $string."-".$string['language']."<br>";
            }
        } //if($getgeneralname=='kind')

        if ($getgeneralname == 'resource') {
            $multi+=1;
            $i = 1;
            $xmlname_element4 = findidsfromxmlname('identifier', $xmlname_gelement['id']);
            savelomelementforxmlparsing($xmlname_element4['id'], 'Parent Element', $item_id, 'none', $i, $multi);
            //print_r($getgeneral);
            foreach ($getgeneral as $string) {

                foreach ($string as $string) {

                    //$i+=1;
                    $stringname = $string->getName();

                    if ($stringname == 'catalog') {
                        $xmlname_element3 = findidsfromxmlname('catalog', $xmlname_element4['id']);
                        savelomelementforxmlparsing($xmlname_element3['id'], $string, $item_id, 'none', $i, $multi);
                    }
                    if ($stringname == 'entry') {
                        $xmlname_element3 = findidsfromxmlname('entry', $xmlname_element4['id']);
                        savelomelementforxmlparsing($xmlname_element3['id'], $string, $item_id, 'none', $i, $multi);
                    }
                    if ($stringname == 'string') {
                        $xmlname_element3 = findidsfromxmlname('description', $xmlname_gelement['id']);
                        savelomelementforxmlparsing($xmlname_element3['id'], $string, $item_id, $string['language'], $i, $multi);
                    }
                }//string identifier
                //catalog-entry
            }
        } //if($getgeneralname=='resource')
    }/////////end relation

    if ($xmlname == 'annotation') {
        //echo 'multi=' . $multi . '<br>';

        if ($getgeneralname == 'description') {
            $multi+=1;
            $i = 1;
            langstring($getgeneral, $xmlname_element['id'], $i, $multi);
        } //if($getgeneralname=='description')

        if ($getgeneralname == 'date') {
            $multi+=1;
            $i = 1;
            foreach ($getgeneral as $string) {
                //$i+=1;
                savelomelementforxmlparsing($xmlname_element['id'], $string, $item_id, 'none', $i, $multi);
                //echo $string."-".$string['language']."<br>";
            }
        } //if($getgeneralname=='date')
        if ($getgeneralname == 'entity') {
            $multi+=1;
            $i = 0;
            //print($getgeneral);
            $string = $getgeneral;
            $i+=1;
            if (isset($string)) {
                //print($string)."<br>";
                $vcard = $string;
                if (stripos($vcard, "\nN:")) {
                    $name = explode("\nN:", $vcard);
                    $name = explode("\n", $name[1]);
                    $name = $name[0];
                } else {
                    $name = "";
                }

                if (stripos($vcard, "\nORG:")) {
                    $org = explode("\nORG:", $vcard);
                    $org = explode("\n", $org[1]);
                    $org = $org[0];
                } else {
                    $org = "";
                }
                if (stripos($vcard, "\nFN:")) {
                    $fname = explode("\nFN:", $vcard);

                    $fname = explode("\n", $fname[1]);
                    $fname = $fname[0];

                    if (strlen($fname) > 0) {
                        $entity = explode($name . ' ', $fname);
                        if (isset($entity['1'])) {
                            $surname = $entity['1'];
                        } else {
                            $surname = "";
                        }
                        // echo $name."<br>";
                        // echo $surname."<br>";
                    }//if isset entity:
                } else {
                    $fname = "";
                }

                if (stripos($vcard, "\nEMAIL;")) {
                    $email = explode("\nEMAIL;", $vcard);
                    $email = explode("\n", $email[1]);
                    $email = explode("INTERNET:", $email[0]);
                    $email = $email[1];
                } else {
                    $email = "";
                }
            }

            vcardinsert($xmlname_element['id'], '', $item_id, 'none', $i, $multi, $name, $surname, $email, $org);

            //savelomelementforxmlparsing('41',$string,$item_id,'none',$i,$multi);
            //echo $string."-".$string['language']."<br>";
        }//if($getgeneralname=='entity')
    }/////////end annotation


    if ($xmlname == 'classification') {
        //echo 'multi=' . $multi . '<br>';
        if ($getgeneralname == 'taxonPath') {
            $multi+=1;
            $i = 0;

            foreach ($getgeneral->taxon->entry as $key => $getgeneral) {
                $taxon = $getgeneral->string;
                $taxon = explode('::', $taxon);

                $taxon1 = $taxon[0];
                $taxon2 = $taxon[1];



                $taxon2 = splitByCaps($taxon2);
                $taxon2 = substr($taxon2, 2); ///remove first characer
                $taxon2 = strtolower($taxon2); ///convert to lower case characters
                //echo '<br>';

                $chunks = splitByCaps($taxon[0]);
                $chunks = substr($chunks, 1); ///remove first characer
                $chunks = substr($chunks, 0, -1); ///remove last character

                $vocid = findvocabularyid($chunks, 85);
                //echo $vocid . '123';
                savelomelementforxmlparsing(86, 'Parent Element', $item_id, 'none', $i, $multi);
                savelomelementforxmlparsing(87, NULL, $item_id, 'none', $i, $multi, $vocid);

                savelomelementforxmlparsing(22, $taxon2, $item_id, 'none', $i, $multi);
            }
        }//if($getgeneralname=='taxonPath')
    }/////////end classification
}

///////end function create_the_query_for_ingest

function insertnewitemfromxml($xml) {

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
    global $collection_id;
    $collection_id = 6;

    $itemtdb = $db->Items;

    $maxIdSQL = "SELECT MAX(id) AS MAX_ID FROM " . $itemtdb . " LIMIT 0,1";
    $exec = $db->query($maxIdSQL);
    $row = $exec->fetch();
    $max_id = $row["MAX_ID"];
    $exec = null;


//print($xml->general->title->string);
//print($xml->technical->format); 
    $type = $xml->technical->format;
    $path_title = $xml->general->title->string;
    if ($type == 'text/html') {
        $formtype = 11;
    } elseif (stripos(' ' . $type, "image") > 0) {
        $formtype = 6;
    } else {
        $formtype = 20;
    }
    $path_public = 0;

    $date_modified = date("Y-m-d H:i:s");
    $mainAttributesSql = "INSERT INTO $itemtdb (featured,item_type_id,public,modified,added,collection_id) VALUES (0," . $formtype . ",'" . $path_public . "','" . $date_modified . "','" . $date_modified . "'," . $collection_id . ")";
echo $mainAttributesSql; 
    $db->exec($mainAttributesSql);

    $lastExhibitIdSQL = "SELECT LAST_INSERT_ID() AS LAST_EXHIBIT_ID FROM " . $itemtdb;
    $exec = $db->query($lastExhibitIdSQL);
    $row = $exec->fetch();
    $last_exhibit_id = $row["LAST_EXHIBIT_ID"];
    $exec = null;

    $entitiesRelationsdb = $db->EntitiesRelations;
    $entity_id = current_user();
    $entitiesRelationsSql = "INSERT INTO " . $entitiesRelationsdb . " (entity_id, relation_id, relationship_id, type, time) VALUES (1, " . $last_exhibit_id . ",1,'Item','" . date("Y-m-d H:i:s") . "')";
    $exec = $db->query($entitiesRelationsSql);

    $path_title = htmlspecialchars($path_title);
    $path_title = addslashes($path_title);
//$path_description=htmlspecialchars($path_description);
//$path_description=addslashes($path_description);
//$path_url=htmlspecialchars($path_url);
//$path_url=addslashes($path_url);


    $mainAttributesSql = "INSERT INTO omeka_element_texts (record_id ,record_type_id ,element_id,text) VALUES (" . $last_exhibit_id . ",2,68,'" . $path_title . "')";
echo $mainAttributesSql;
   $db->exec($mainAttributesSql);

    $metadatarecordSql = "INSERT INTO metadata_record (id, object_id, object_type,date_modified) VALUES ('', " . $last_exhibit_id . ",'item','" . $date_modified . "')";
   $execmetadatarecordSql = $db->query($metadatarecordSql);


    $lastExhibitIdSQL = "SELECT LAST_INSERT_ID() AS LAST_EXHIBIT_ID FROM metadata_record";
    $exec = $db->query($lastExhibitIdSQL);
    $row = $exec->fetch();
    $last_record_id = $row["LAST_EXHIBIT_ID"];
    $exec = null;


    return $last_record_id;
}

function splitByCaps($string) {
    return preg_replace('/([a-z0-9])?([A-Z])/', '$1 $2', $string);
}

function langstring($getgeneral, $id, $i, $multi) {
    global $item_id;
    foreach ($getgeneral as $string) {
        //$i+=1;
        savelomelementforxmlparsing($id, $string, $item_id, $string['language'], $i, $multi);
        //echo $string."-".$string['language']."<br>";
    }
}

function findvocabularyid($getgeneral, $xmlname_element_id) {
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

    $chechvcardnew3 = "select * from metadata_element  WHERE id=" . $xmlname_element_id . " ";
    $chechvcardnewres3 = $db->query($chechvcardnew3);
    $resultforfunc3 = $chechvcardnewres3->fetch();

    $getgeneral_lower = strtolower($getgeneral);
    $getgeneral_firstupper = ucfirst($getgeneral);
    $getgeneral_upper = strtoupper($getgeneral);

    if (strlen($getgeneral) > 0) {
        $chechvcardnew2 = "select * from metadata_vocabulary_record  WHERE vocabulary_id=" . $resultforfunc3['vocabulary_id'] . " and (value='" . $getgeneral . "' or value='" . $getgeneral_lower . "' or value='" . $getgeneral_firstupper . "' or value='" . $getgeneral_upper . "') ";
        $chechvcardnewres2 = $db->query($chechvcardnew2);
        $resultforfunc2 = $chechvcardnewres2->fetch();
    }
    return $resultforfunc2['id'];
}

//	}//if($entry!='.' and $entry!='..'){
//    }//while uparxoun arxei ston fakelo
//    closedir($handle);
//} //close handle gia arxeia


function savelomelementforxmlparsing($element_hierarchy, $value, $item_id, $language, $parent_indexer = 1, $multi = 1, $vocabulary_id = NULL, $is_editable = 1) {
    global $item_id;
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

    $value = htmlspecialchars($value);
    $value = addslashes($value);


    if ($is_editable == 0) {
        $is_editable = $is_editable;
    } else {
        $is_editable = 'NULL';
    }
    if ($vocabulary_id > 0) {
        $maxIdSQL_sg = "insert into metadata_element_value SET element_hierarchy=" . $element_hierarchy . ",is_editable=" . $is_editable . ",vocabulary_record_id=" . $vocabulary_id . ",language_id='" . $language . "',record_id=" . $item_id . ",multi=" . $multi . ",parent_indexer=" . $parent_indexer . " ON DUPLICATE KEY UPDATE vocabulary_record_id=" . $vocabulary_id . ";";
    } else {
        if (strlen($value) > 0) {
            $maxIdSQL_sg = "insert into metadata_element_value SET element_hierarchy=" . $element_hierarchy . ",is_editable=" . $is_editable . ",value='" . $value . "',language_id='" . $language . "',record_id=" . $item_id . ",multi=" . $multi . ",parent_indexer=" . $parent_indexer . " ON DUPLICATE KEY UPDATE value='" . $value . "';";
        }
    }
    //echo $maxIdSQL_sg . "<br>";

    $execinsertelements_sg = $db->query($maxIdSQL_sg);
    $execinsertelements_sg = null;
}

function vcardinsert($element_hierarchy, $value, $item_id, $language, $parent_indexer = 1, $multi = 1, $vcard_name, $vcard_surname, $vcard_email, $vcard_organization) {
    global $item_id;
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

    $vcard_name = addslashes(htmlspecialchars($vcard_name));
    $vcard_surname = addslashes(htmlspecialchars($vcard_surname));
    $vcard_email = addslashes(htmlspecialchars($vcard_email));
    $vcard_organization = addslashes(htmlspecialchars($vcard_organization));

    if (strlen($vcard_name) > 0 or strlen($vcard_surname) > 0 or strlen($vcard_email) > 0 or strlen($vcard_organization) > 0) {

        $chechvcard = "select * from metadata_vcard WHERE name='" . $vcard_name . "' and surname='" . $vcard_surname . "' and email='" . $vcard_email . "' and organization='" . $vcard_organization . "'";
        $execchechvcard = $db->query($chechvcard);
        $result_chechvcard = $execchechvcard->fetch();
        $execchechvcard = null;

        if (strlen($result_chechvcard['id']) > 0) {

            $maxIdSQL_vc = "insert into metadata_element_value SET element_hierarchy=" . $element_hierarchy . ",value='Vcard Element',language_id='" . $language . "',record_id=" . $item_id . ",multi=" . $multi . ",parent_indexer=" . $parent_indexer . ",vcard_id=" . $result_chechvcard['id'] . " ON DUPLICATE KEY UPDATE vcard_id=" . $result_chechvcard['id'] . ";";

            echo $maxIdSQL_vc . "<br>";
           $exec = $db->query($maxIdSQL_vc);
           $result_multi = $exec->fetch();
        } else {
            $chechvcardins = "insert into metadata_vcard SET name='" . $vcard_name . "',surname='" . $vcard_surname . "',email='" . $vcard_email . "',organization='" . $vcard_organization . "';";
            $execchechvcardins = $db->query($chechvcardins);
            $result_chechvcardins = $execchechvcardins->fetch();
            $execchechvcardins = null;

            $chechvcardnew = "select * from metadata_vcard WHERE name='" . $vcard_name . "' and surname='" . $vcard_surname . "' and email='" . $vcard_email . "' and organization='" . $vcard_organization . "';";
            $execchechvcardnew = $db->query($chechvcardnew);
            $result_chechvcardnew = $execchechvcardnew->fetch();
            $execchechvcardnew = null;

            $maxIdSQL_vc = "insert into metadata_element_value SET element_hierarchy=" . $element_hierarchy . ",value='Vcard Element',language_id='" . $language . "',record_id=" . $item_id . ",multi=" . $multi . ",parent_indexer=" . $parent_indexer . ",vcard_id=" . $result_chechvcardnew['id'] . " ON DUPLICATE KEY UPDATE vcard_id=" . $result_chechvcardnew['id'] . ";";

            echo $maxIdSQL_vc . "<br>";
            $exec = $db->query($maxIdSQL_vc);
            $result_multi = $exec->fetch();
        }
    }
}
function findidsfromxmlname($xmlelementname, $xmlparentelementhierarchyid = NULL) {

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

    if ($xmlparentelementhierarchyid > 0) {
        $chechvcardnew2 = "select b.* from metadata_element_hierarchy a JOIN metadata_element b on b.id=a.element_id WHERE a.id='" . $xmlparentelementhierarchyid . "' ";
        $chechvcardnewres2 = $db->query($chechvcardnew2);
        $resultforfunc2 = $chechvcardnewres2->fetch();

        $sqsq = " and a.pelement_id='" . $resultforfunc2['id'] . "'";
    } else {
        $sqsq = " and a.pelement_id=0";
    }

    $chechvcardnew = "select a.* from metadata_element_hierarchy a JOIN metadata_element b on b.id=a.element_id WHERE b.machine_name='" . $xmlelementname . "' " . $sqsq . " ";
    $chechvcardnewres = $db->query($chechvcardnew);
    $resultforfunc = $chechvcardnewres->fetch();
    $chechvcardnewres-NULL;

    return $resultforfunc;
}
?>
