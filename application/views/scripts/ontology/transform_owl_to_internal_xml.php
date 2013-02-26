<?php 
ob_start();
$output='<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$output .= '<classification>' . "\n";

///////get the OWL file////////////////
libxml_use_internal_errors(false);
$xmlobj = @simplexml_load_file('http://'.$_SERVER['SERVER_NAME'].''.  uri('ontology/olontology').'');

//$xml->registerXPathNamespace("owl","http://localhost/TestRequestOntology.php");
if ($xmlobj===false)
    die('Bad XML response!');
/////get all elements with the name:'owl:Class'
$result = $xmlobj->xpath("owl:Class");
 $i=0;
 
$output .= '<hierarchy rootElement="#OAAEConcept">' . "\n";
foreach($result as $c){ 
$rdf_elements=$c->children('rdf',true);
$rdfs_elements=$c->children('rdfs',true);
$owl_elements=$c->children('owl',true);


if($rdfs_elements->subClassOf){
	$i+=1;
	$sub_owl_elements=$rdfs_elements->subClassOf->children('owl',true);
$attofsubelement=$sub_owl_elements->attributes('rdf',true);
$attofelement=$c->attributes('rdf',true);
//print $i.'. Element: '.$attofelement['about'].' - Subclass: '.$attofsubelement['about'];
$output .= '<class id="'.$attofelement['about'].'" className="'.$attofelement['about'].'" subClassOf="'.$attofsubelement['about'].'" />' . "\n";
}

}
$output .= '</hierarchy>' . "\n";
$output .= '<instances>' . "\n";

foreach($result as $c){ 


$rdf_elements=$c->children('rdf',true);
$rdfs_elements=$c->children('rdfs',true);
$owl_elements=$c->children('owl',true);


if($owl_elements->hasTranslation){
	
	$attofelement=$c->attributes('rdf',true);
	//echo $attofelement['about'].' - ';
	$attofelement2=$owl_elements->attributes();
	//echo $attofelement2['lang'].' - ';
	//print($owl_elements);
	$output .= '<instance instanceOf="'.$attofelement['about'].'" lang="'.$attofelement2['lang'].'">' . "";
	$output .= ''.$owl_elements.'' . "";
	$output .= '</instance>' . "\n";
}

}
$output .= '</instances>' . "\n";

$output .= '</classification>' . "\n";
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

$xmlname="new_oe_ontology_hierrarchy.xml";
$fp = fopen("/var/www/html/".uri('archive/xmlvoc')."/new_oe_ontology_hierrarchy.xml","w");
//$fp = fopen("C:/Program Files (x86)/EasyPHP-12.1/www/".uri('archive/xmlvoc')."/".$xmlname."","w");
fwrite($fp,$output);
fclose($fp);
if($fp){
echo "<a href='http://".$_SERVER['SERVER_NAME']."".uri('archive/xmlvoc')."/".$xmlname."' target='_blank'> New Internal Xml from API ontology</a>";
}

?>
