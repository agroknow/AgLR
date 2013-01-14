<?php 
require_once 'Omeka/Core.php';
$core = new Omeka_Core;

try {
    $db = $core->getDb();
	$db->query("SET NAMES 'utf8'");
   
    //Force the Zend_Db to make the connection and catch connection errors
    try {
        $mysqli = $db->getConnection()->getConnection();
    } catch (Exception $e) {
        throw new Exception("<h1>MySQL connection error: [" . mysqli_connect_errno() . "]</h1>" . "<p>" . $e->getMessage() . '</p>');
    }
} catch (Exception $e) {
	die($e->getMessage() . '<p>Please refer to <a href="http://omeka.org/codex/">Omeka documentation</a> for help.</p>');
}
?>
<?php    
    $itemTitle = strip_formatting(item('Dublin Core', 'Title'));
    if ($itemTitle != '' && $itemTitle != __('[Untitled]')) {
        $itemTitle = ': &quot;' . $itemTitle . '&quot; ';
    } else {
        $itemTitle = '';
    }
    $itemTitle = __('Item #%s', item('id')) . $itemTitle;
?>
<?php head(array('title' => $itemTitle, 'bodyclass'=>'items show primary-secondary')); ?>

<?php echo js('items'); ?>

<h1 id="item-title"><?php echo $itemTitle; ?></h1>

<?php if (has_permission($item, 'edit')): ?>
<p id="edit-item" class="edit-button"><?php 
echo link_to_item(__('Edit this Item'), array('class'=>'edit'), 'edit'); ?></p>   
<?php endif; ?>

<ul class="item-pagination navigation group">
<li id="previous-item" class="previous">
    <?php //echo link_to_previous_item(); ?>
</li>
<li id="next-item" class="next">
    <?php //echo link_to_next_item(); ?>
</li>
</ul>
<script type="text/javascript" charset="utf-8">
//<![CDATA[
jQuery(document).ready(function () {
    Omeka.Items.modifyTagsShow();
    Omeka.Items.tagDelimiter = <?php echo js_escape(get_option('tag_delimiter')); ?>;
    Omeka.Items.tagChoices('#tags-field', <?php echo js_escape(uri(array('controller' => 'tags', 'action' => 'autocomplete'), 'default')); ?>);
});
//]]>     
</script>
<div id="primary">
<?php echo flash(); ?>

    <?php /*
<div id="item-images">
<?php echo display_files_for_item(array('imageSize' => 'fullsize')); ?> 
</div>
     * 
     */ ?>
<div id="itemfiles" class="element">
	    <?php echo '<br><h2 style="color:#90A886; font-size:13px;">'.__('Access the Resource').':</h2>'; ?>
		<div class="element-text"><?php //echo display_files_for_item(); ?></div>
        
        <?php
		$sql="SELECT * FROM metadata_record WHERE object_id=".$item->id." and object_type='item'";
		$execrecord=$db->query($sql);
		$datarecord=$execrecord->fetch();
		
		$sql="SELECT a.* FROM metadata_element_value a join metadata_element_hierarchy b ON b.id=a.element_hierarchy 
		WHERE a.record_id=".$datarecord['id']." and a.element_hierarchy=32";
$exec5=$db->query($sql);
$data51=$exec5->fetch();

$sql="SELECT a.* FROM metadata_element_value a join metadata_element_hierarchy b ON b.id=a.element_hierarchy 
		WHERE a.record_id=".$datarecord['id']." and a.element_hierarchy=34";
$exec5=$db->query($sql);
$data52=$exec5->fetch();

$uri=WEB_ROOT;	
$sql="SELECT a.* FROM metadata_element_value a join metadata_element_hierarchy b ON b.id=a.element_hierarchy 
		WHERE a.record_id=".$datarecord['id']." and a.element_hierarchy=33";
$exec5=$db->query($sql);
$dataformat=$exec5->fetch();
if($dataformat['vocabulary_record_id']>0){
$sql2 = "SELECT * FROM metadata_vocabulary_record WHERE id=" . $dataformat['vocabulary_record_id'] . " ";
$exec2 = $db->query($sql2);
$dataformatfromvoc = $exec2->fetch();
}
			if(item_has_files($item) and $item->item_type_id==6 and !stripos($data51['value'],".emf")>0 and !stripos($data51['value'],".tif")>0 and !stripos($data51['value'],".tiff")>0){
				
				echo '<a href="'.$data51['value'].'"  class="lightview"><img src="'.$data51['value'].'" style=" max-width:500px;"/></a><br>';
				
				 } elseif($item->item_type_id==11 or $item->item_type_id==20 or $item->item_type_id==6){ 
		   ?>
		   <div style="float:left; margin-top:10px;">
		   <?php
		   if(stripos($data51['value'],".jpg")>0 or stripos($data51['value'],".gif")>0 or stripos($data51['value'],".jpeg")>0 or stripos($data51['value'],".png")>0 or stripos($data51['value'],".bmp")>0 or stripos($data51['value'],"content/thumbs/src")>0 or $dataformatfromvoc['value']=="IMAGE"){
		   
		   if(stripos($data51['value'],"artpast.org/oaipmh/getimage")>0){
		   
		   echo '<a href="http://europeanastatic.eu/api/image?type=IMAGE&uri='.$data51['value'].'"  class="lightview"><img src="http://europeanastatic.eu/api/image?type=IMAGE&uri='.$data51['value'].'" style=" max-width:400px;"/></a><br>';
		   
		   }else{
		   
		   echo '<a href="'.$data51['value'].'"  class="lightview"><img src="'.$data51['value'].'" style=" max-width:400px;"/></a><br>';
		   
		   }
		   
		   }elseif(stripos($data51['value'],".pdf")>0){
		   
		   echo '<a href="'.$data51['value'].'" target="_blank"><img src="'.uri('themes/default/images/files-icons/pdf.png').'"/></a><br>';
		   
		   }elseif(stripos($data51['value'],".tiff")>0 or stripos($data51['value'],".tif")>0){ 
		   
		   //http://education.natural-europe.eu/natural_europe/custom/phpThumb/phpThumb.php?src=/natural_europe/archive/files/riekko-ansasta2_72a2f5e439.tif&w=135
		   echo '<a href="'.$uri.'/custom/phpThumb/phpThumb.php?src='.$data51['value'].'"  class="lightview"><img src="'.$uri.'/custom/phpThumb/phpThumb.php?src='.$data51['value'].'"  style=" max-width:500px; max-height:400px;"/></a><br>';
		   
		   }elseif(stripos($data51['value'],".doc")>0 or stripos($data51['value'],"docx")>0  or stripos($dataformatfromvoc['value'],"word")>0){
		   
		   echo '<a href="'.$data51['value'].'" target="_blank"><img src="'.uri('themes/default/images/files-icons/word.png').'" /></a><br>';
		   
		   }elseif(stripos($data51['value'],".ppt")>0 or stripos($data51['value'],".pptx")>0 or stripos($data51['value'],".pps")>0 or stripos($dataformatfromvoc['value'],"powerpoint")>0){
		   
		   echo '<a href="'.$data51['value'].'" target="_blank"><img src="'.uri('themes/default/images/files-icons/powerpoint.png').'" /></a><br>';
		   
		   }elseif(stripos($data51['value'],".emf")>0 ){
		   
		   echo '<a href="'.$data51['value'].'" target="_blank">'.$data51['value'].'</a><br>';
		   	
		   }elseif(stripos($data51['value'],".html")>0 or stripos($data51['value'],".htm")>0 or stripos($data51['value'],".asp")>0 or stripos($dataformatfromvoc['value'],"HTML")>0 or stripos($dataformatfromvoc['value'],"Html")>0 or $dataformatfromvoc['value']=='html' or $dataformatfromvoc['value']=='html/text' or $dataformatfromvoc['value']=='text/html' or $dataformatfromvoc['value']=='HTML'){
		  if(!(strrpos('1'.$data51['value'], 'http://'))){
                      $data51['value']='http://'.$data51['value'];
                  }
		   echo '<a href="'.$data51['value'].'" target="_blank"><img src="http://open.thumbshots.org/image.aspx?url='.$data51['value'].'"/></a><br>';
		   //echo '<a href="'.$data51['value'].'" target="_blank">'.$data51['value'].'</a><br>';
		   } else{ 
		   if(!(strrpos('1'.$data51['value'], 'http://'))){
                      $data51['value']='http://'.$data51['value'];
                  }
		   echo '<a href="'.$data51['value'].'" target="_blank"><img src="http://open.thumbshots.org/image.aspx?url='.$data51['value'].'"/></a><br>';
		   	//echo '<a href="'.$data51['value'].'" target="_blank">'.$data51['value'].'</a><br>';
		   
		   }
	   		echo "<br>";
		   
		  
		  
		  ?>
          </div>
          <br style="clear:both;">
          <?php } ?>
	</div>
    <?php show_metadata_info(item('id'),'item',$_SESSION['get_language']);  ?>


<?php fire_plugin_hook('admin_append_to_items_show_primary', $item); ?>

</div>
<div id="secondary">
    
   

<?php /*
    <div class="info-panel">
        <h2><?php echo __('Output Formats'); ?></h2>
        <div><?php echo output_format_list(); ?></div>
    </div>
    */ ?>
    <?php fire_plugin_hook('admin_append_to_items_show_secondary', $item); ?>
</div>
<?php foot();?>
