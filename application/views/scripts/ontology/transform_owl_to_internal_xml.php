<?php

ob_start();
$output = '<?xml version="1.0" encoding="UTF-8"?>';
$output .= '<classification>';

///////get the OWL file////////////////
libxml_use_internal_errors(false);
$xmlobj = @simplexml_load_file('http://' . $_SERVER['SERVER_NAME'] . '' . uri('ontology/olontology') . '');

//$xml->registerXPathNamespace("owl","http://localhost/TestRequestOntology.php");
if ($xmlobj === false)
    die('Bad XML response!');

 
$output .= '<hierarchy rootElement="#OAAEConcept">';

/////get all elements with the name:'owl:Class'
$result = $xmlobj->xpath("owl:Class");
$i = 0;

/////////////////////////HIERARCHY///////////////////////////////////

foreach ($result as $c) {
    $rdf_elements = $c->children('rdf', true);
    $rdfs_elements = $c->children('rdfs', true);
    $owl_elements = $c->children('owl', true);


    if ($rdfs_elements->subClassOf) {
        $i+=1;
        $sub_owl_elements = $rdfs_elements->subClassOf->children('owl', true);
        $attofsubelement = $sub_owl_elements->attributes('rdf', true);
        $attofelement = $c->attributes('rdf', true);
//print $i.'. Element: '.$attofelement['about'].' - Subclass: '.$attofsubelement['about'].'<br>';
        $output .= '<class id="' . $attofelement['about'] . '" className="' . $attofelement['about'] . '" subClassOf="' . $attofsubelement['about'] . '" />' . "\n";
    }
}
$result2 = $xmlobj->xpath("rdf:Description");
foreach ($result2 as $c2) {
    $attofelement = $c2->attributes('rdf', true);

    $rdf_elements2 = $c2->children('rdf', true);
    foreach ($rdf_elements2 as $rdf_elements2) {
        $owl_elements2 = $rdf_elements2->children('owl', true);
        foreach ($owl_elements2 as $owl_elements2) {
            $i+=1;
            $attofsubelement = $owl_elements2->attributes('rdf', true);
//            print $i.'. Element: '.$attofelement['about'].' - Subclass: '.$attofsubelement['about'].'<br>';
            $output .= '<class id="' . $attofelement['about'] . '" className="' . $attofelement['about'] . '" subClassOf="' . $attofsubelement['about'] . '" />';
        }
    }
}


$output .= '</hierarchy>';
$output .= '<instances>';

/////////////////TRANSLATIONS//////////////////

/////ta owl class which are classes
foreach ($result as $c) {


    $rdf_elements = $c->children('rdf', true);
    $rdfs_elements = $c->children('rdfs', true);
    $owl_elements = $c->children('owl', true);

        if ($rdfs_elements->label) {
            //if ($owl_elements->hasTranslation) {

            $attofelement = $c->attributes('rdf', true);
            //echo $attofelement['about'].' - ';
            $attofelement2 = $rdfs_elements->label->attributes('xml', true);
             //echo $attofelement2['lang'].' - ';
            //print($rdfs_elements->label);
            ////print($owl_elements);
            //echo "<br>";

            $output .= '<instance instanceOf="' . $attofelement['about'] . '" lang="' . $attofelement2['lang'] . '">' . "";
            $output .= '' . $rdfs_elements . '' . "";
            //$output .= '' . $owl_elements . '' . "";
            $output .= '</instance>';
        }
}

///////ta rdf description which are the last ones
foreach ($result2 as $c) {


    $rdf_elements = $c->children('rdf', true);
    $rdfs_elements = $c->children('rdfs', true);
    $owl_elements = $c->children('owl', true);

        if ($rdfs_elements->label) {
            //if ($owl_elements->hasTranslation) {

            $attofelement = $c->attributes('rdf', true);
            //echo $attofelement['about'].' - ';
            $attofelement2 = $rdfs_elements->label->attributes('xml', true);
             //echo $attofelement2['lang'].' - ';
            //print($rdfs_elements->label);
            ////print($owl_elements);
            //echo "<br>";

            $output .= '<instance instanceOf="' . $attofelement['about'] . '" lang="' . $attofelement2['lang'] . '">' . "";
            $output .= '' . $rdfs_elements . '' . "";
            //$output .= '' . $owl_elements . '' . "";
            $output .= '</instance>';
        }
}

$output .= '</instances>';

$output .= '</classification>';
//echo $output;
/*
  print_r($c->children('owl',true));
  echo "<br>";
  print_r($c->children('rdf',true));
  echo "<br>";
  print_r($c->children('rdfs',true));
 */
//echo "<br><br>";
//if($i>2){break;}
//if ($xmlobj===false){die('Bad XML!'); }
//print_r($xmlobj->getNamespaces());
//print_r($xmlobj->children('rdf',true));
//print_r($xmlobj->asXML());
//$page = ob_get_contents();

ob_end_flush();
//echo "http://".$_SERVER['SERVER_NAME']."".uri('archive/xmlvoc')."/output.xml";

$xmlname = "new_oe_ontology_hierrarchy.xml";
$fp = fopen("/var/www/html/".uri('archive/xmlvoc')."/new_oe_ontology_hierrarchy.xml","w");
//$fp = fopen("C:/Program Files (x86)/EasyPHP-12.1/www/" . uri('archive/xmlvoc') . "/" . $xmlname . "", "w");
fwrite($fp, $output);
fclose($fp);
if ($fp) {
    echo "<a href='http://" . $_SERVER['SERVER_NAME'] . "" . uri('archive/xmlvoc') . "/" . $xmlname . "' target='_blank'> New Internal Xml from API ontology</a>";
}
?>
