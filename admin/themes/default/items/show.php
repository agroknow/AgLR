<?php
$itemTitle = strip_formatting(item('Dublin Core', 'Title'));
if ($itemTitle != '' && $itemTitle != __('[Untitled]')) {
    $itemTitle = ': &quot;' . $itemTitle . '&quot; ';
} else {
    $itemTitle = '';
}
$itemTitle = __('View Item #%s', item('id')) . $itemTitle;
?>
<?php //head(array('title' => $itemTitle, 'bodyclass'=>'items show primary-secondary'));  ?>
<?php head(array('title' => $itemTitle, 'bodyclass' => 'items primary', 'content_class' => 'vertical-nav')); ?>

<?php echo js('items'); ?>
<?php echo js('jquery.jstree'); ?>
<?php echo js('prototype'); ?>
<?php //echo js('scriptaculous'); ?>
<?php echo js('tooltip'); ?>

<h1 id="item-title"><?php echo $itemTitle; ?></h1>

<?php if (has_permission($item, 'edit')): ?>
    <p id="edit-item" class="edit-button"><?php echo link_to_item(__('Edit this Item'), array('class' => 'edit'), 'edit'); ?></p>   
    <?php endif; ?>

<ul class="item-pagination navigation group">
    <li id="previous-item" class="previous">
        <?php //echo link_to_previous_item();  ?>
    </li>
    <li id="next-item" class="next">
        <?php //echo link_to_next_item();  ?>
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
<script type="text/javascript">

    jQuery(function(){
        jQuery('#show_optional').click(function(){
            var value=jQuery('.optional_element').css("display");
            if(value=='none'){
                jQuery('.optional_element').css("display", "block"); 
                jQuery('#show_optional').css("background-color", "#FFFFFF");
                jQuery('#show_optional').text("Only recommended");

 
            }else{
                jQuery('.optional_element').css("display", "none"); 
                jQuery('#show_optional').css("background-color", "#F4F3EB");
                jQuery('#show_optional').text("Enrich Metadata");


            }
              


        });


    });

</script>
<script type="text/javascript" charset="utf-8">
    //<![CDATA[
    // TinyMCE hates document.ready.
    jQuery(window).load(function () {
        Omeka.Items.initializeTabs();

        //var addImage = <?php //echo js_escape(img('silk-icons/add.png'));  ?>;
        //var deleteImage = <?php //echo js_escape(img('silk-icons/delete.png'));  ?>;
        //Omeka.Items.tagDelimiter = <?php //echo js_escape(get_option('tag_delimiter'));  ?>;
        //Omeka.Items.enableTagRemoval(addImage, deleteImage);
        //Omeka.Items.makeFileWindow();
        //Omeka.Items.tagChoices('#tags', <?php //echo js_escape(uri(array('controller' => 'tags', 'action' => 'autocomplete'), 'default', array(), true));  ?>);

        // Must run the element form scripts AFTER reseting textarea ids.
        // Query(document).trigger('omeka:elementformload');

        //Omeka.Items.enableAddFiles();
        //Omeka.Items.changeItemType(<?php //echo js_escape(uri("items/change-type"))  ?><?php //if ($id = item('id')) echo ', ' . $id;  ?>);
    });

    jQuery(document).bind('omeka:elementformload', function () {
        Omeka.Items.makeElementControls(<?php echo js_escape(uri('items/element-form')); ?><?php if ($id = item('id')) echo ', ' . $id; ?>);
        //Omeka.Items.enableWysiwyg();
    });
    //]]>   
</script>
<style>
    [disabled] {
        background-color: #ffffff !important;
        border-color: #ccc !important;
        color: #000000 !important;
    }

    [readonly] {
        background-color: #ffffff !important;
        border: 1px solid #ccc !important;
        color: #000000 !important;
        font-family: "Lucida Grande",sans-serif;
        font-size: 1.2em;
        padding: 3px;

    }
</style>

<div>
    <a style="position:relative; float:right; right:0px;" id="show_optional">Enrich Metadata</a>
</div>
<br style="clear:both;">
<?php include 'form-tabs.php'; // Definitions for all the tabs for the form.  ?>
<div id="primary">
    <?php echo flash(); ?>

    <?php /*
      <div id="item-images">
      <?php echo display_files_for_item(array('imageSize' => 'fullsize')); ?>
      </div>
     * 
     */ ?>


    <div id="item-metadata">

        <?php if (isset($item['id'])) { ?>
            <?php
            $step = 0;

//query for all elements without asking pelement
            $sql = "SELECT f.*,e.vocabulary_id,e.id as elm_id FROM  metadata_element_hierarchy f  JOIN metadata_element e ON f.element_id = e.id WHERE f.is_visible=1  ORDER BY (case WHEN f.sequence IS NULL THEN '9999' ELSE f.sequence END) ASC";
/////////////////query for translate specific elements//////////
            if (isset($_POST['submit_language'])) {
                $sql = "SELECT f.*,e.vocabulary_id,e.id as elm_id FROM  metadata_element_hierarchy f  JOIN metadata_element e ON f.element_id = e.id WHERE (f.id=6 or f.id=8 or f.id=35) and f.is_visible=1 ORDER BY (case WHEN f.sequence IS NULL THEN '9999' ELSE f.sequence END) ASC";
            }
            $exec4 = $db->query($sql);
            $data4 = $exec4->fetchAll();
            $exec4 = NULL;
//end
//query for all values
            $sql = "SELECT * FROM metadata_record WHERE object_id=" . $item->id . " and object_type='item'";
            $execrecord = $db->query($sql);
            $record = $execrecord->fetch();
            $execrecord = NULL;
            $record_id = $record['id'];
//end
//query for all languages iso vocabulary_record=13 (ISO LANGUAGES)
            $sqllan = "SELECT e.value,e.sequence,e.id as vov_rec_id FROM metadata_vocabulary_record e JOIN
					metadata_vocabulary_value f ON f.vocabulary_rid = e.id WHERE e.vocabulary_id=23 and e.public=1  and f.language_id='" . get_language_for_switch() . "'  ORDER BY (case WHEN e.sequence IS NULL THEN '99999' END),e.sequence,f.label ASC";
            $execlan = $db->query($sqllan);
            $datalan = $execlan->fetchAll();
            $execlan = NULL;

//end


            foreach ($general_pelements as $data) {  //for every element general
                $step+=1;
                echo '<div class="toggle" id="step' . $step . '">'; //create div for toggle
//if($step==9){echo createlomlabel('Under Construction!','style="width:158px;"');}
//
/////////////////if translation no central description//////////
                if (!isset($_POST['submit_language'])) {
                    $label_description = return_label_description($data['element_id']);
                }

                if (strlen($label_description) > 0) {
                    echo "<p style='padding:2px;border:solid 1px #76BB5F; color: #76BB5F;'><strong><i>" . $label_description . "</i></strong></p>";
                }

                foreach ($data4 as $dataform) {  //for every element under general
                    if ($data['element_id'] === $dataform['pelement_id']) { //if pelement tou hierarchy = element general
                        checkelement($dataform, $datalan, $record, 0, NULL, NULL, NULL, 1, $xml_general);
                    }//if $data['element_id']===$dataform['pelement_id']
                }//if pelement tou hierarchy = element general (dataform)

                echo '</div>';  //close div general
            }//end for every element general  (data)
            ?>


            <div id="stepbuttoncollection">
    <?php include('collection-form.php'); ?> 
            </div>
                <?php
                if (isset($item['id'])) {
                    if (($item['item_type_id'] === 6 or $item['item_type_id'] === 20) and !(stripos($itemsource['value'], "europeana.eu/portal/") > 0 or $itemsource['value'] == 'Ariadne' or $itemsource['value'] == 'Natural_Europe_TUC')) {
                        ?>
                    <div id="stepfile">
                    <?php include('files-form.php'); ?> 
                    </div>
                    <?php } elseif ($item['item_type_id'] === 6 or $item['item_type_id'] === 20) { ?>

                    <div id="stepurl">
                    <?php
                    $sql = "SELECT * FROM metadata_element_value WHERE record_id=" . $record_id . " and element_hierarchy=32";

                    $exec5 = $db->query($sql);
                    $data5 = $exec5->fetch();
                    //echo $data5['value'];
                    echo '<div style="float:left;"><label for=32 style="width:158px;">Url</label></div>';

                    echo '<textarea rows="4" cols="60" class="textinput" name="32_1" id="32_1" readonly="readonly">' . $data5['value'] . '</textarea>&nbsp;&nbsp';
                    ?>
                    </div>

                    <?php } else { ?>
                    <div id="stepurl">
                    <?php
                    $sql = "SELECT * FROM metadata_element_value WHERE record_id=" . $record_id . " and element_hierarchy=32";

                    $exec5 = $db->query($sql);
                    $data5 = $exec5->fetch();
                    //echo $data5['value'];
                    echo '<div style="float:left;"><label for=32 style="width:158px;">Url</label></div>';

                    echo '<textarea rows="4" cols="60" class="textinput" name="item_url" id="item_url" readonly="readonly">' . $data5['value'] . '</textarea>&nbsp;&nbsp';
                    ?>
                    </div>
                        <?php
                    }
                }//if isset item
                else {
                    ?>
                <div id="stepfile">
                <?php include('files-form.php'); ?> 
                </div>
                    <?php }
                ?>


        <?php } //is isset item ?>
    </div><br style="clear:both;">







<?php fire_plugin_hook('admin_append_to_items_show_primary', $item); ?>

</div>


<?php foot(); ?>



<?php /*
 * 
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
 */ ?>
<?php /*
  <div id="secondary">




  <div class="info-panel">
  <h2><?php echo __('Output Formats'); ?></h2>
  <div><?php echo output_format_list(); ?></div>
  </div>

  <?php fire_plugin_hook('admin_append_to_items_show_secondary', $item); ?>
  </div>
 */ ?>