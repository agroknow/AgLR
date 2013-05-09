<?php head(array('title'=>'Browse Items','content_class' => 'horizontal-nav', 'bodyclass'=>'items primary browse-items')); ?>
<h1>Ingest a Green Resource from Organic.Edunet federation</h1>
<?php if (has_permission('Items', 'add')): ?>
<div style="text-align:center; width:800px;">
<form action="#" method="post" style="text-align:center;">
Search Green Resources <br>
<div style="text-align:center;">
<input type="text" name="europeanatext"> 
<input type="submit" class="button" value="Search" style="float:none;">
</div>
</form>

<div style="position:absolute;right:400px;top:200px;filter:alpha(opacity=20);opacity:0.20;">
<img <?php $uri=WEB_ROOT;
	echo '<img src="'.$uri.'/application/views/scripts/images/files-icons/GreenHack.png"/>';?>
</div>

<style>
a {
color:black;
text-decoration:none;
}
</style>

<?php
if(isset($_POST['europeanatext'])){

$_POST['europeanatext']=str_replace(' ','+AND+', $_POST['europeanatext']);
$_POST['europeanatext']="'+AND+".$_POST['europeanatext'];
}


$string = file_get_contents("http://oe.confolio.org/scam/search?type=solr&query=environment".$_POST['europeanatext']."");
//echo "http://oe.confolio.org/scam/search?type=solr&query=environment".$_POST['europeanatext']."+AND+lang:en&sort=title.en+asc,modified+asc";
$json_a=json_decode($string,true);
echo  "</div><br><br>";

$i=0;
$params=array();
foreach ($json_a['resource']['children'] as $k => $v) {

foreach ($v['cached-external-metadata'] as $z=>$y) {
if($z=="dc:title"){
   $title= $y['@value'];
   
}
if($z=="dc:description"){
   $descrip= $y['@value'];
   
}

if($z=="dc:format"){
   $format= $y['@value'];
   $params=array_merge($params,array('format'=>$format));
}

if($z=="@id"){
   $identifier= html_escape($y);
   $params=array_merge($params,array('identifier'=>$identifier));
   
}


   
}
$user = current_user();
$params=array('title'=>$title,
			  'description'=>$descrip,
			  'format'=>$format,
			  'identifier'=>$identifier,
			  'user'=>$user['entity_id']);
			  
echo "<div style='width:800px; border:0px solid; margin-top:10px;position:relative; top:14px;'>";
echo '<div style="float:left; width:130px;">'; 
if ($format=="PDF" || $format=="HTML,PDF") 
{
	$uri=WEB_ROOT;
	echo '<img src="'.$uri.'/application/views/scripts/images/files-icons/pdf.png"/>';
	}
else {
	echo '<img src="http://open.thumbshots.org/image.aspx?url='.$identifier.'"/>';
	}
echo '</div>';
echo '<div style="float:left;width:605px;"><strong>'.$title.'</strong><br>'.$descrip.'';
if($identifier){ echo '<br><b>Access to the resource: </b><a href="'.$identifier.'" target="_blank">'.$identifier.'</a>'; }
echo '<br><div style="position:relative; top:2px;height:40px;"><a style="position:relative; top:10px;background-color: #F4F3EB;
    color: #CC5500;
    padding-bottom: 10px;
    padding-right: 10px;
	padding-left: 10px;
    padding-top: 10px;" href="'.uri("items/addinjestitem",$params).'">Add it to my Repository</a>';
echo '</div></div>';
echo "</div><br style='clear:both;'>";
}


endif;
?>
</div>