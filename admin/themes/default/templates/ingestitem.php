<?php head(array('title'=>'Browse Items','content_class' => 'horizontal-nav', 'bodyclass'=>'items primary browse-items')); ?>
<h1>Ingest a Resource from Natural Europe federation</h1>
<?php if (has_permission('Items', 'add')): ?>

<?php
	
function onlyNumbers($string){
    //This function removes all characters other than numbers
    $string = preg_replace("/[^0-9]/", "", $string);
    return (int) $string;
} 

    $exhibit_id=onlyNumbers($_GET['ex_id']);
	$sec_id=onlyNumbers($_GET['sec_id']);
	$pg_id=onlyNumbers($_GET['pg_id']);	

?>
<div style="text-align:center; width:850px;">
<form action="#" method="post" style="text-align:center;">
<div style="text-align:center;">
<img src="<?php echo uri('themes/default/items/images/logonatural.png'); ?>"><br>
Search in Natural Europe: 
</div>
<div style="text-align:center;">
<input type="text" name="europeanatext"> 
<input type="submit" class="button" value="Search" style="float:none;">
</div>
</form>

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
if(isset($_POST['startPage'])){$startPage= $_POST['startPage']; $startPageurl= $_POST['startPage']*10-10;} else{$startPageurl=0; $startPage=1;}
if(isset($_POST['bytype'])){$bytypeforurl= "type:".$_POST['bytype']." AND ";} else{$bytypeforurl="";}
//echo 'http://collections.natural-europe.eu/cmss/search?query='.$europeanatext.'&start='.$startPageurl.'';
?>
<div><em><strong>Filter results by type: </strong></em><br> 
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
IMAGE<br>
<a href="#" onclick="GoType('IMAGE');"><img src="<?php echo uri('themes/default/items/images/image_icon.png'); ?>"> </a>
</div> 
<div style="float:left;margin-left:10px;text-align:center;">
TEXT<br>
<a href="#" onclick="GoType('TEXT');"><img src="<?php echo uri('themes/default/items/images/text_icon.gif'); ?>"> </a>
</div> 
<div style="float:left;margin-left:10px;text-align:center;">
VIDEO<br>
<a href="#" onclick="GoType('VIDEO');"><img src="<?php echo uri('themes/default/items/images/video_icon.png'); ?>"> </a>
</div> 
<div style="float:left;margin-left:10px;text-align:center;">
SOUND<br>
<a href="#" onclick="GoType('SOUND');"><img src="<?php echo uri('themes/default/items/images/sound_icon.png'); ?>"> </a>
</div> 

</form>
</div>
<?php 
libxml_use_internal_errors(false);
$xml = @simplexml_load_file('http://collections.natural-europe.eu/cmss/search?query='.$bytypeforurl.'text:'.$europeanatext.'&start='.$startPageurl.'', NULL, LIBXML_NOERROR | LIBXML_NOWARNING);
if($xml === false){ echo "An Error occured. Please try again later. Thank you!";}
//echo 'http://collections.natural-europe.eu/cmss/search?query='.$bytypeforurl.'text:'.$europeanatext.'&start='.$startPageurl.'';
//$xml = simplexml_load_file('http://ariadne.cs.kuleuven.be/ariadne-partners/api/sqitarget?query=learning&start='.$startPage.'&size=12&lang=plql1&format=lom', NULL, LIBXML_NOERROR | LIBXML_NOWARNING);
if($xml){
$xml->getName() . "<br />";

foreach($xml->children() as $child1)
  {
  
  $childgeneral = $child1->children();
  echo "<div style='width:850px; border-top:1px solid; border-bottom:1px solid; margin-top:10px;'>";
  // print  "Page you are: ". $startPage."<br />";
	 print  "You search: ". $europeanatext."<br />";
//Use that namespace
$opensearch = $child1->children('http://a9.com/-/spec/opensearch/1.1/');
  print "Total results : ".$opensearch->totalResults; 
 // print "<br />startIndex : ".$opensearch->startIndex;
  $pages=$opensearch->totalResults/12;
  $pages2=round($pages); 
  if($pages2>$pages){$pages=$pages2;}else{$pages=$pages2+1;}
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

  </script>
  <form action="#" method="post" name="form2" class="pagination" style="float:none; margin-top:10px; width:540px;">
  <input type="hidden" name="europeanatext" value="<?php echo $_POST['europeanatext']; ?>">
  <input type="hidden" name="startPage" value="<?php echo $_GET['startPage']; ?>">
  <?php if(isset($_POST['bytype'])){?> <input type="hidden" name="bytype" value="<?php echo $_POST['bytype']; ?>"> <?php } ?>

  <?php 
  if($pages<10){
  while($i<$pages){echo "<a href='javascript:GoPage(".$i.")'   ";
  	if($i==$startPage){echo "id='active'";}
  	echo " >".$i."</a> ";  $i+=1;}
  
  } 
  else{
  
   if($startPage<8){ 
   while($i<$pages and $i<11){  echo "<a href='javascript:GoPage(".$i.")'   ";
  	if($i==$startPage){echo "id='active'";}
  	echo " >".$i."</a> ";  $i+=1;}
  echo ("...");

  echo "<a href='javascript:GoPage(".$pages.")'   ";
  	if($i==$startPage){echo "id='active'";}
  	echo " >".$pages."</a> ";
  }
  elseif($startPage<($pages-8)){
  echo "<a href='javascript:GoPage(1)'   ";
  	if($i==$startPage){echo "id='active'";}
  	echo " >1</a> ";
  echo ("...");
  $i=$startPage-5;
  $x=$startPage+5;
  while($i<$x){  echo "<a href='javascript:GoPage(".$i.")'   ";
  	if($i==$startPage){echo "id='active'";}
  	echo " >".$i."</a> ";  $i+=1;}
  echo ("...");
  echo "<a href='javascript:GoPage(".$pages.")'   ";
  	if($i==$startPage){echo "id='active'";}
  	echo " >".$pages."</a> ";
  }//elseif
  
  elseif($startPage>($pages-8)){
  echo "<a href='javascript:GoPage(1)'   ";
  	if($i==$startPage){echo "id='active'";}
  	echo " >1</a> ";
  echo ("...");
  $i=$pages-10;
   while($i<$pages+1){  echo "<a href='javascript:GoPage(".$i.")'   ";
  	if($i==$startPage){echo "id='active'";}
  	echo " >".$i."</a> ";  $i+=1;}
  }//elseif
  
  
  
  }//else kenriko
  ?>
  </form>
  
     <div style="float:left; text-align:center; margin-top:20px;">
<?php echo ingest_search_total_block(''.$europeanatext.'',1); ?>
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
		 print  "<a href='".$child->enclosure->attributes()."' target='_new'><img src='".$child->enclosure->attributes()." ' width='100' height='100' border='0'></a><br />";
		 $source=$child->enclosure->attributes();
		 $source=preg_replace('/(["\'])/ie', '',$source);
		 
		 $format=$child->enclosure->attributes()->type;
		 $format=preg_replace('/(["\'])/ie', '',$format);
		 }
		 
		 echo '</div>';
		 echo '<div style="float:left;width:605px;">';
		 $title=$child->title;
		 $title=preg_replace('/(["\'])/ie', '',$title);
		 //echo $title;
		 
		 $identifier=$link1;
		 print  "<strong>". $title."</strong><br />";
		  //print  "Link :<br />". $child->link."<br /><hr>";
		  $descrip=$child->description;
		  $descrip=preg_replace('/(["\'])/ie', '',$descrip);
		   print  "". $child->description."<br />";
		   //Use that namespace

  echo "<a href='". $source."' target='_new'>Access to the resource</a><br>";

		   print  "<br><a href='". $child->link."' target='_new'>View Metadata</a>";
	
	
	$user = current_user();
$params=array('title'=>$title,
			  'description'=>$descrip,
			  'source'=>'Natural_Europe_TUC',
			  'format'=>$format,
			  'identifier'=>$source,
			  'user'=>$user['entity_id']);
echo '<form method="post" name="'.$cb.'" action="'.uri("items/addinjestitem").'">';

$title = preg_replace('/(["\'])/ie', '',  $title);
$descrip = preg_replace('/(["\'])/ie', '',  $descrip);
echo '<input type="hidden" name="title" value="'.$title.'">';
echo '<input type="hidden" name="description" value="'.$descrip.'">';
echo '<input type="hidden" name="source" value="Natural_Europe_TUC">';
echo '<input type="hidden" name="format" value="'.$format.'">';
echo '<input type="hidden" name="identifier" value="'.$source.'">';
echo '<input type="hidden" name="user" value="'.$user['entity_id'].'">';		   
		   //echo '<br><div style="position:relative; top:2px;height:40px;"><a style="position:relative; top:10px;background-color: #F4F3EB;
   //  color: #CC5500;
   // padding-bottom: 10px;
   // padding-right: 10px;
	//padding-left: 10px;
   // padding-top: 10px;" href="'.uri("items/addinjestitem",$params).'">Add it to my Repository</a>';
	//echo '</div>';
echo "<br><div style='position:relative; top:-12px;height:40px;'>
<input id='newsubmit' type='submit' value='Add it to my Repository' name='insert' 
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
			// echo '</form>';

		 $arr = $child->enclosure->attributes();
		// print ("ID=".$arr['url']);
		// print ("  Company=".$child->title); echo "<br><br>";
 		//echo $child->getName() . ": " . $child . "<br /><br />";
		
		echo "</div>";
			}
 		 
 	 } 
  } 
     }//if isset xml
}//is iiset europeana text


}//if >0 page
?>
</div>
<?php 
endif; //permission to add item
?>
</div>

</div>