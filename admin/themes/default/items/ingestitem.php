<?php head(array('title'=>'Browse Items','content_class' => 'horizontal-nav', 'bodyclass'=>'items primary browse-items')); ?>
<h1><?php echo __('Ingest a Resource from External federations'); ?></h1>
<?php if (has_permission('Items', 'add')): ?>

<?php
	
function onlyNumbers($string){
    //This function removes all characters other than numbers
    $string = preg_replace("/[^0-9]/", "", $string);
    return (int) $string;
} 

if(isset($_POST['insert'])){ //if click add to pathway

}else{//if click add to pathway
?>

<div style="text-align:center; width:850px;">
<form action="<?php echo uri('items/europeana') ?>" method="post" style="text-align:center;">

<div style="text-align:left;">
<img src="<?php echo uri('themes/default/items/images/ingest-logo.jpg'); ?>"><br>
<?php echo __('Search'); ?>:
</div>
<div style="text-align:left;">
<input type="text" name="europeanatext"> 
<input type="submit" class="button" value="<?php echo __('Search'); ?>" style="float:none;">
</div>
</form>
<iframe width="350" height="200" src="//www.youtube.com/embed/7Au-mYd7jHE" frameborder="0" allowfullscreen style="position: absolute; left: 310px; top: 60px;"></iframe>
<style>
a {
color:black;
text-decoration:none;
}
#active{
background-color:#cc6600;
color:#ffffff;
}
</style>

<?php


// Get SimpleXMLElement object from an XML document
//$xml = simplexml_load_file("http://api.europeana.eu/api/opensearch.rss?searchTerms=bible&startPage=1&wskey=IIRTOOIRNG");
 
// Get XML string from a SimpleXML element
// When you select "View source" in the browser window, you will see the objects and elements
//echo $xml->asXML();
if(isset($_POST['europeanatext'])){$europeanatext= $_POST['europeanatext'];
$_POST['europeanatext']=str_replace(' ','+', $_POST['europeanatext']);
if(isset($_POST['startPage'])){$startPage= $_POST['startPage']; $startPageurl= $_POST['startPage']*12-11;} else{$startPageurl=1; $startPage=1;}
if(isset($_POST['bytype'])){$europenana_type= "+europeana_type:*".$_POST['bytype']."*";} else{$europenana_type="";}
//print_r($_POST);break;
//echo 'http://api.europeana.eu/api/opensearch.rss?searchTerms='.$europeanatext.''.$europenana_type.'&startPage='.$startPage.'&wskey=IIRTOOIRNG';
?>
<div><em><strong><?php echo __('Filter results by type'); ?>: </strong></em><br> 
  <script>
  function GoType(type){
  document.form4.bytype.value=''+type+'';
  document.form4.submit();}
  </script>
  <form action="#" method="post" name="form4">
  <input type="hidden" name="europeanatext" value="<?php echo $_POST['europeanatext']; ?>">
  <input type="hidden" name="startPage" value="1">
  <?php if(isset($_POST['bytype'])){$bytype=$_POST['bytype'];}else{$bytype='';} ?>
  <input type="hidden" name="bytype" value="<?php echo $bytype; ?>">
  
<div style="float:left; text-align:center;">
<?php    echo __('IMAGE'); ?><br>
<a href="#" onclick="GoType('IMAGE');"><img src="<?php echo uri('themes/default/items/images/image_icon.png'); ?>"> </a>
</div> 
<div style="float:left;margin-left:10px;text-align:center;">
<?php    echo __('TEXT'); ?><br>
<a href="#" onclick="GoType('TEXT');"><img src="<?php echo uri('themes/default/items/images/text_icon.gif'); ?>"> </a>
</div> 
<div style="float:left;margin-left:10px;text-align:center;">
<?php    echo __('VIDEO'); ?><br>
<a href="#" onclick="GoType('VIDEO');"><img src="<?php echo uri('themes/default/items/images/video_icon.png'); ?>"> </a>
</div> 
<div style="float:left;margin-left:10px;text-align:center;">
<?php    echo __('SOUND'); ?><br>
<a href="#" onclick="GoType('SOUND');"><img src="<?php echo uri('themes/default/items/images/sound_icon.png'); ?>"> </a>
</div> 

</form>
</div>
<?php 
libxml_use_internal_errors(false);
//echo 'http://api.europeana.eu/api/opensearch.rss?searchTerms=text:"'.$europeanatext.'*"'.$europenana_type.'&startPage='.$startPageurl.'&wskey=IIRTOOIRNG';
$xml = @simplexml_load_file('http://api.europeana.eu/api/opensearch.rss?searchTerms=text:"'.$europeanatext.'*"'.$europenana_type.'&startPage='.$startPageurl.'&wskey=IIRTOOIRNG', NULL, LIBXML_NOERROR | LIBXML_NOWARNING);
if($xml === false){ echo "An Error occured. Please try again later. Thank you!";}
if($xml){
$xml->getName() . "<br />";


foreach($xml->children() as $child1)
  {
  
  $childgeneral = $child1->children();
  echo "<div style='width:850px; border-top:1px solid; border-bottom:1px solid; margin-top:10px;'>";
	 print  "".__('You search').": ". $europeanatext."<br />";
	 //print  "You search: ". $childgeneral->description."<br />";
//Use that namespace
$opensearch = $child1->children('http://a9.com/-/spec/opensearch/1.1/');
  print "".__('Total results')." : ".$opensearch->totalResults; 
 // print "<br />startIndex : ".$opensearch->startIndex;
  $pages=$opensearch->totalResults/12;
  $pages2=round($pages); 
  if($pages2>=$pages){$pages=$pages2;}else{$pages=$pages2+1;}
  if($pages>0){ 

  //print $opensearch->itemsPerPage;

	 //print  "Total results: ". $childgeneral->opensearch->totalResults."<br />";
	 echo "</div>";
    $i=1; ?>
  <script>
  function GoPage(iPage) {
  
    document.form2.startPage.value = iPage;
	document.form2.submit();
}
  function submitform(formname) {
	  
	
		document.formname.submit();
	}

  </script>
  <form action="#" method="post" name="form2" class="pagination" style="float:none; margin-top:10px; width:540px;">
  <input type="hidden" name="europeanatext" value="<?php echo $_POST['europeanatext']; ?>">
  <input type="hidden" name="startPage" value="<?php echo $_GET['startPage']; ?>">
  <?php if(isset($_POST['bytype'])){?> <input type="hidden" name="bytype" value="<?php echo $_POST['bytype']; ?>"> <?php } ?>

  <?php 
  if($pages<10){
  while($i<=$pages){
  	
  	echo "<a href='javascript:GoPage(".$i.")' ";
  	if($i==$startPage){echo "id='active'";}
  	echo " >".$i."</a> ";  $i+=1;}
  
  } 
  else{
  
   if($startPage<8){ 
   while($i<$pages and $i<11){  
   	
   echo "<a href='javascript:GoPage(".$i.")' ";
  	if($i==$startPage){echo "id='active'";}
  	echo " >".$i."</a> ";  $i+=1;}
   	
  echo ("...");
  echo "<a href='javascript:GoPage(".$pages.")' ";
  	if($i==$startPage){echo "id='active'";}
  	echo "  >".$pages."</a> ";
  }
  elseif($startPage<($pages-8)){
  echo "<a href='javascript:GoPage(1)'  ";
  	if($i==$startPage){echo "id='active'";}
  	echo " >1</a> ";
  echo ("...");
  $i=$startPage-5;
  $x=$startPage+5;
  while($i<$x){  echo "<a href='javascript:GoPage(".$i.")'  ";
  	if($i==$startPage){echo "id='active'";}
  	echo " >".$i."</a> ";  $i+=1;}
  echo ("...");
  echo "<a href='javascript:GoPage(".$pages.")'  ";
  	if($i==$startPage){echo "id='active'";}
  	echo " >".$pages."</a> ";
  }//elseif
  
  elseif($startPage>($pages-8)){
  echo "<a href='javascript:GoPage(1)'  ";
  	if($i==$startPage){echo "id='active'";}
  	echo " >1</a> ";
  echo ("...");
  $i=$pages-10;
   while($i<$pages+1){  echo "<a href='javascript:GoPage(".$i.")'  ";
  	if($i==$startPage){echo "id='active'";}
  	echo " >".$i."</a> ";  $i+=1;}
  }//elseif
  
  
  
  }//else kenriko
  ?>
  </form>
  
     <div style="float:left; text-align:center; margin-top:20px;">
<?php echo ingest_search_total_block(''.$europeanatext.'',2); ?>
</div> 
<div style="float:left; width:670px; margin-left:25px;margin-top:20px;">
  <?php 
  $cb=0;
 	 foreach($child1->children() as $child2)
 	 {
  $cb+=1;

	
	 
	  		 $child = $child2->children();
		 $name=$child2->getName();
		
		 if($name=='item'){
          echo "<div style='width:805px; margin-top:10px;clear:both;'>";
		  
		 $link1= str_replace('srw?wskey=IIRTOOIRNG','html', $child->link);

	 
		 
		 echo '<div style="float:left; width:130px;">'; 
		 
		 if($child->enclosure and strlen($child->enclosure->attributes())>5){
		 if(stripos($child->enclosure->attributes(),"artpast.org/oaipmh/getimage")>0){
		 
		  print  "<a href='http://europeanastatic.eu/api/image?type=IMAGE&uri=".$child->enclosure->attributes()."' target='_new'><img src='http://europeanastatic.eu/api/image?type=IMAGE&uri=".$child->enclosure->attributes()." ' width='100' height='100' border='0'></a><br />";
		 
		 }else{
		 
		 print  "<a href='".$child->enclosure->attributes()."' target='_new'><img src='".$child->enclosure->attributes()." ' width='100' height='100' border='0'></a><br />";
		 
		 }
		 
		 $source=$child->enclosure->attributes();
		 $source=preg_replace('/(["\'])/ie', '',$source);
		 }
		 
		 echo '</div>';
		 echo '<div style="float:left;width:605px;">';
		 $title=$child->title;
		 $title=preg_replace('/(["\'])/ie', '',$title);
		 //echo $title;
		 
		 $identifier=$link1;
		 print  "<strong>". $title."</strong><br />";
		  //print  "Link :<br />". $child->link."<br /><hr>";
		  
///////////////////////////////parse second xml metadata/////////////////////
$metadataxml=$child->link;
$xmlmetadata = @simplexml_load_file(''.$metadataxml.'', NULL, LIBXML_NOERROR | LIBXML_NOWARNING);
if($xmlmetadata){
$dc =$xmlmetadata->records->children('http://www.loc.gov/zing/srw/')->record->recordData->children('http://purl.org/dc/elements/1.1/');
//print_r($dc);
//$dc = $xmlmetadata->records->record->recordData->children('http://purl.org/dc/elements/1.1/');
//echo $dc->dc->description;
//if($xmlmetadata){ break;}
///////////////////////////////parse second xml metadata/////////////////////
		  
		  $descrip=$dc->dc->description;
		  $descrip=preg_replace('/(["\'])/ie', '',$descrip);
		   print  "". $descrip."<br />";		  
}else{$descrip='';}

$creator=$dc->dc->creator;

		   //Use that namespace
$europenana = $child2->children('http://www.europeana.eu');
$itemlanguage=$europenana->language;
$itemlanguage=strtolower($itemlanguage);
		$format=$europenana->type;
		$format=preg_replace('/(["\'])/ie', '',$format);
  if(strlen($europenana->rights)>7){print "<strong>".__('Rights')." :</strong> <a href='".$europenana->rights."' target='_blank'>".$europenana->rights."</a><br />";} 
  if(strlen($europenana->provider)>2){print "<strong>".__('Provider')." : </strong>".$europenana->provider."<br />";} 
  echo "<a href='". $link1."' target='_new'>".__('Access resource at Europeana')."</a><br>";

		   //print  "<br><a href='". $child->link."' target='_new'>View Metadata</a>";
  $keywords=$dc->dc->subject;
  //print_r($keywords);
	
	$user = current_user();
$params=array('title'=>$title,
			  'description'=>$descrip,
			  'source'=>$identifier,
			  'format'=>$format,
			  'identifier'=>$source,
			  'user'=>$user['entity_id']);
echo '<form method="post" target="_blank" name="'.$cb.'" action="'.uri("items/addinjestitem").'">';

$title = preg_replace('/(["\'])/ie', '',  $title);
$descrip = preg_replace('/(["\'])/ie', '',  $descrip);

echo '<input type="hidden" name="title" value="'.base64_encode(json_encode($title)).'">';
echo '<input type="hidden" name="description" value="'.base64_encode(json_encode($descrip)).'">';
echo '<input type="hidden" name="source" value="'.$identifier.'">';
echo '<input type="hidden" name="format" value="'.$format.'">';
echo '<input type="hidden" name="identifier" value="'.$source.'">';
echo '<input type="hidden" name="keywords" value="'.base64_encode(json_encode($keywords)).'">';
echo '<input type="hidden" name="itemlanguage" value="'.$itemlanguage.'">';
echo '<input type="hidden" name="creator" value="'.$creator.'">';
echo '<input type="hidden" name="provider" value="'.$creator.'">';
echo '<input type="hidden" name="rights" value="'.$europenana->rights.'">';
echo '<input type="hidden" name="user" value="'.$user['entity_id'].'">';
		   
		  // echo '<br><div style="position:relative; top:2px;height:40px;">
		  // <a style="position:relative; top:10px;background-color: #F4F3EB;
  //  color: #CC5500;
  // padding-bottom: 10px;
   // padding-right: 10px;
	//padding-left: 10px;
   // padding-top: 10px;" href="" onclick="document.'.$cb.'.submit();">Add it to my Repository</a>';
//echo '</div>';

echo "<br><div style='position:relative; top:-12px;height:40px;'>
<input id='newsubmit' type='submit' value='".__('Add it to my Repository')."' name='insert' 
style='background-color:#F4F3EB;color: #CC5500; background-image:none; border:none; float:left; font-weight:normal;text-shadow:none;'>";
echo '</div>';
echo '</form>';

		   echo '</div>';
		   
		    // echo '<input type="hidden" name="ex_id" value="'.$exhibit_id.'">';
			// echo '<input type="hidden" name="sec_id" value="'.$sec_id.'">';
			// echo '<input type="hidden" name="pg_id" value="'.$pg_id.'">';
			// $child->title = preg_replace('/(["\'])/ie', '',  $child->title);
			// echo '<input type="hidden" name="title" value="'.$child->title.'">';
			// echo '<input type="hidden" name="uri" value="'.$link1.'">';
			 

		 $arr = $child->enclosure->attributes();
		// print ("ID=".$arr['url']);
		// print ("  Company=".$child->title); echo "<br><br>";
 		//echo $child->getName() . ": " . $child . "<br /><br />";
		
		echo "</div>";
			}
 		 
 	 } 
  } 
  ?>
    <script>
                    function GoPage2(iPage) {
                                                                  
                        document.form3.startPage.value = iPage;
                        document.form3.submit();
                    }

                </script>
    <form action="#" method="post" name="form3" class="pagination" style="float:none; margin-top:10px; width:540px;">
  <input type="hidden" name="europeanatext" value="<?php echo $_POST['europeanatext']; ?>">
  <input type="hidden" name="startPage" value="<?php echo $_GET['startPage']; ?>">
  <?php if(isset($_POST['bytype'])){?> <input type="hidden" name="bytype" value="<?php echo $_POST['bytype']; ?>"> <?php } ?>

  <?php 
  $i=1;
  if($pages<10){
  while($i<=$pages){
  	
  	echo "<a href='javascript:GoPage2(".$i.")' ";
  	if($i==$startPage){echo "id='active'";}
  	echo " >".$i."</a> ";  $i+=1;}
  
  } 
  else{
  
   if($startPage<8){ 
   while($i<$pages and $i<11){  
   	
   echo "<a href='javascript:GoPage2(".$i.")' ";
  	if($i==$startPage){echo "id='active'";}
  	echo " >".$i."</a> ";  $i+=1;}
   	
  echo ("...");
  echo "<a href='javascript:GoPage2(".$pages.")' ";
  	if($i==$startPage){echo "id='active'";}
  	echo "  >".$pages."</a> ";
  }
  elseif($startPage<($pages-8)){
  echo "<a href='javascript:GoPage2(1)'  ";
  	if($i==$startPage){echo "id='active'";}
  	echo " >1</a> ";
  echo ("...");
  $i=$startPage-5;
  $x=$startPage+5;
  while($i<$x){  echo "<a href='javascript:GoPage2(".$i.")'  ";
  	if($i==$startPage){echo "id='active'";}
  	echo " >".$i."</a> ";  $i+=1;}
  echo ("...");
  echo "<a href='javascript:GoPage2(".$pages.")'  ";
  	if($i==$startPage){echo "id='active'";}
  	echo " >".$pages."</a> ";
  }//elseif
  
  elseif($startPage>($pages-8)){
  echo "<a href='javascript:GoPage2(1)'  ";
  	if($i==$startPage){echo "id='active'";}
  	echo " >1</a> ";
  echo ("...");
  $i=$pages-10;
   while($i<$pages+1){  echo "<a href='javascript:GoPage2(".$i.")'  ";
  	if($i==$startPage){echo "id='active'";}
  	echo " >".$i."</a> ";  $i+=1;}
  }//elseif
  
  
  
  }//else kenriko
  ?>
  </form>
    <?php
     }//if isset xml
}//is iiset europeana text



}//if click add to pathway

}//if >0 page
?>
</div>
<?php 
endif; //permission to add item
?>
</div>

</div>