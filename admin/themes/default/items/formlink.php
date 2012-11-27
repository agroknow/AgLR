<?php echo js('tiny_mce/tiny_mce'); 
// echo js('tiny_mce/tiny_mce_src'); // Use the 'tiny_mce_src' file for debugging.
?>
<?php
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
?>
<?php echo js('items'); ?>
<script type="text/javascript" charset="utf-8">
//<![CDATA[
// TinyMCE hates document.ready.
jQuery(window).load(function () {
    Omeka.Items.initializeTabs();

    var addImage = <?php echo js_escape(img('silk-icons/add.png')); ?>;
    var deleteImage = <?php echo js_escape(img('silk-icons/delete.png')); ?>;
    Omeka.Items.tagDelimiter = <?php echo js_escape(get_option('tag_delimiter')); ?>;
    Omeka.Items.enableTagRemoval(addImage, deleteImage);
    Omeka.Items.makeFileWindow();
    Omeka.Items.tagChoices('#tags', <?php echo js_escape(uri(array('controller'=>'tags', 'action'=>'autocomplete'), 'default', array(), true)); ?>);

    // Must run the element form scripts AFTER reseting textarea ids.
    jQuery(document).trigger('omeka:elementformload');

    Omeka.Items.enableAddFiles();
    Omeka.Items.changeItemType(<?php echo js_escape(uri("items/change-type")) ?><?php if ($id = item('id')) echo ', '.$id; ?>);
});

jQuery(document).bind('omeka:elementformload', function () {
    Omeka.Items.makeElementControls(<?php echo js_escape(uri('items/element-form')); ?><?php if ($id = item('id')) echo ', '.$id; ?>);
    Omeka.Items.enableWysiwyg();
});
//]]>   
</script>

<?php echo flash(); ?>

<div id="public-featured">
    <?php if ( has_permission('Items', 'makePublic') ): ?>
        <div class="checkbox">
            <!--<label for="public">Public:</label>  -->
            <div class="checkbox"><?php echo hidden(array('name'=>'public', 'id'=>'public'), $item->public); ?></div>
        </div>
    <?php endif; ?>
<!--    <?php //if ( has_permission('Items', 'makeFeatured') ): ?>
        <div class="checkbox">
            <label for="featured">Featured:</label> 
            <div class="checkbox"><?php //echo checkbox(array('name'=>'featured', 'id'=>'featured'), $item->featured); ?></div>
        </div>
    <?php //endif; ?> -->
</div>
<div id="item-metadata">

<?php if(isset($item['id'])){ ?>
<?php



		//query for creating general elements pelement=0		 
$sql2="SELECT a.*,c.*,b.* FROM metadata_element_label a LEFT JOIN metadata_element b ON a.element_id = b.id LEFT JOIN metadata_element_hierarchy c ON c.element_id = b.id WHERE c.pelement_id=0 and c.is_visible=1 ORDER BY (case WHEN c.sequence IS NULL THEN '9999' ELSE c.sequence END) ASC;";
$exec2=$db->query($sql2); 
$step=0;
$exec3=$db->query($sql2); 




$data2=$exec3->fetchAll();//again to query gia ola ta parent =0 gia create step div



//query for all elements without asking pelement
$sql="SELECT d.element_id,d.labal_name,d.language_id,f.*,e.vocabulary_id FROM metadata_element_label d RIGHT JOIN metadata_element e ON d.element_id = e.id RIGHT JOIN metadata_element_hierarchy f ON f.element_id = e.id WHERE f.is_visible=1 ORDER BY (case WHEN f.sequence IS NULL THEN '9999' ELSE f.sequence END) ASC";
$exec4=$db->query($sql);
$data4=$exec4->fetchAll();
//end



//query for all values
$sql="SELECT * FROM metadata_record WHERE object_id=".$item->id." and object_type='item'";
$execrecord=$db->query($sql);
$datarecord=$execrecord->fetchAll();
foreach($datarecord as $datarecord){
$sql="SELECT * FROM metadata_element_value WHERE record_id=".$datarecord['id']." ";
}
$exec5=$db->query($sql);
$data5=$exec5->fetchAll();
//end



//query for all languages
$sqllan="SELECT * FROM metadata_language WHERE is_active=1 ORDER BY (case WHEN id='en' THEN 1 ELSE 2 END) ASC";
$execlan=$db->query($sqllan);
$datalan=$execlan->fetchAll();
//end

//query for selecting vocabulary
$sqlvoc="SELECT f.value,d.id FROM metadata_vocabulary d RIGHT JOIN metadata_vocabulary_record e ON d.id = e.vocabulary_id RIGHT JOIN metadata_vocabulary_value f ON f.vocabulary_rid = e.id";
$execvoc=$db->query($sqlvoc);
$datavoc=$execvoc->fetchAll();
//end query for selecting vocabulary


foreach($data2 as $data){  //for every element general
$step+=1; echo '<div class="toggle" id="step'.$step.'">'; //create div for toggle
	
foreach($data4 as $dataform){  //for every element under general
if($data['element_id']===$dataform['pelement_id']){ //if pelement tou hierarchy = element general





//if hierarchy form name = radio
if($dataform['form_type_id']===4){ 
foreach($data5 as $datarecord){ if($datarecord['element_hierarchy']===$dataform['id']){
$datarecordvalue=$datarecord['value'];}}//select the value for more than one foreach
echo '<label for="theme">'.$dataform['labal_name'].'</label>';

echo '<input type="radio" name="'.$dataform['id'].'" ';
if($datarecordvalue==='yes'){echo 'checked=checked ';}
echo 'value="yes"> Yes &nbsp;&nbsp;';

echo '<input type="radio" name="'.$dataform['id'].'" ';
if($datarecordvalue==='no'){echo 'checked=checked ';}
echo 'value="no"> No ';

if($dataform['id']===23){

echo '<input type="radio" name="'.$dataform['id'].'" ';
if($datarecordvalue==='Yes, if others share alike'){echo 'checked=checked ';}
echo 'value="Yes, if others share alike"> Yes, if others share alike ';

}

echo '<br style="clear:both"><br>';
} //end form name = radio






//if hierarchy form name = select
elseif($dataform['form_type_id']===3){ 

echo '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; width:100%;" id="'.$dataform['id'].'">';

//echo '<div id="'.$dataform['id'].'">';

echo '<div style="float:left;"><label for='.$dataform['id'].' style="width:158px;">'.$dataform['labal_name'].'</label></div>';

echo '<div style="float:left;" id="'.$dataform['id'].'_inputs">';


$formcount=0;
foreach($data5 as $datarecord){ if($datarecord['element_hierarchy']===$dataform['id']){ //select the value for more than one foreach
$datarecordvalue=$datarecord['value'];

$formmulti=$datarecord['multi'];
$formcount+=1;
echo'<div id="'.$dataform['id'].'_'.$formmulti.'_field">';

if($dataform['vocabulary_id']>0){//select and isset vocabulary

echo '<select name="'.$dataform['id'].'_'.$datarecord['multi'].'" style="width:300px;">';

echo '<option value="">Select </option>';

foreach($datavoc as $datavoc1){
if($datavoc1['id']===$dataform['vocabulary_id']){
echo '<option value="'.$datavoc1['value'].'" ';
if($datarecordvalue===$datavoc1['value']){echo 'selected=selected';}
echo '>'.$datavoc1['value'].'</option>';
}}
echo '</select>';
if($dataform['max_occurs']>1){?>
<a href="#" onClick="removeFormFieldExisted('<?php echo $dataform['id'].'_'.$formmulti.'_field'; ?>','<?php echo $dataform['id']; ?>','<?php echo $datarecord['language_id']; ?>','<?php echo $datarecord['record_id']; ?>','<?php echo $datarecord['multi']; ?>'); return false;" style="position:relative; left:5px; top:2px;">Remove</a><?php
}//maxoccurs>1
echo '<br style="clear:both"><br>';


} //select and isset vocabulary
else{


echo '<select name="'.$dataform['id'].'_'.$formcount.'">';
foreach($datalan as $datalan1){
echo '<option value="'.$datalan1['id'].'" ';
if($datarecordvalue===$datalan1['id']){echo 'selected=selected';}
echo '>'.$datalan1['locale_name'].'</option>';
}
echo '</select>';

echo '<br style="clear:both"><br>';

}//end else select and isset vocabulary
echo "</div>";
}}//select the value for more than one foreach



//an den uparxei eggrafh create one empty
if($formcount===0){
$formmulti=1;
$formcount+=1;
if($dataform['vocabulary_id']>0){//select and isset vocabulary

echo '<select name="'.$dataform['id'].'_'.$formcount.'" style="width:300px;">';
echo '<option value="">Select </option>';
foreach($datavoc as $datavoc1){
if($datavoc1['id']===$dataform['vocabulary_id']){
echo '<option value="'.$datavoc1['value'].'" ';
//if($datarecordvalue===$datalan1['id']){echo 'selected=selected';}
echo '>'.$datavoc1['value'].'</option>';
}}
echo '</select>';
echo '<br style="clear:both"><br>';


} //select and isset vocabulary
else{

echo '<select name="'.$dataform['id'].'_'.$formcount.'">';
foreach($datalan as $datalan1){
echo '<option value="'.$datalan1['id'].'" ';
//if($datarecordvalue===$datalan1['id']){echo 'selected=selected';}
echo '>'.$datalan1['locale_name'].'</option>';
}
echo '</select>';

echo '<br style="clear:both"><br>';

}//end else select and isset vocabulary


}

//end create one empty
echo "</div>";


if($dataform['max_occurs']>1){?>
<input name="hdnLine_<?php echo $dataform['id']; ?>" id="hdnLine_<?php echo $dataform['id']; ?>" type="hidden" value="<?php echo $formmulti; ?>">

<div style="position:relative;clear:both;"><a href="#" onClick="addFormFieldSelect('<?php echo $formmulti; ?>','<?php echo $dataform['id']; ?>','hdnLine_<?php echo $dataform['id']; ?>','<?php echo $dataform['vocabulary_id']; ?>'); return false;">Add</a></div>

<!--<INPUT style="margin-top:0px; margin-left:10px;" type="button" value="Add" onclick="addmulti('<?php //echo $formcount; ?>','<?php //echo $dataform['id']; ?>','hdnLine_<?php //echo $dataform['id']; ?>','<?php //echo $dataform['vocabulary_id']; ?>'); "/> -->
<?php }

echo '</div>';
echo '<br style="clear:both"><br>';

} //end form name = select


else{



echo '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; width:100%;" id="'.$dataform['id'].'">';

//echo '<div id="'.$dataform['id'].'">';

echo '<div style="float:left;"><label for='.$dataform['id'].' style="width:158px;">'.$dataform['labal_name'].'</label></div>';

$formcount=0;
echo '<div style="float:left;" id="'.$dataform['id'].'_inputs">';
foreach($data5 as $datarecord){ if($datarecord['element_hierarchy']===$dataform['id']){
$datarecordvalue=$datarecord['value']; $datarecordvaluelan=$datarecord['language_id'];//select the value for more than one foreach


$formcount+=1;

echo'<div id="'.$dataform['id'].'_'.$formcount.'_field">';
echo '<textarea rows="4" cols="60" class="textinput" name="'.$dataform['id'].'_'.$formcount.'" id="'.$dataform['id'].'_'.$formcount.'">'.$datarecordvalue.'</textarea>&nbsp;&nbsp';

//if hierarchy type= langstring
if($dataform['datatype_id']===1){
echo '<select name="'.$dataform['id'].'_'.$formcount.'_lan" class="combo" style="vertical-align:top;">';
foreach($datalan as $datalan1){
echo '<option value="'.$datalan1['id'].'" ';
if($datarecordvaluelan===$datalan1['id']){echo 'selected=selected';}
echo '>'.$datalan1['locale_name'].'</option>';
}
echo '</select>';

}//langstring
?><a href="#" onClick="removeFormFieldExisted('<?php echo $dataform['id'].'_'.$formcount.'_field'; ?>','<?php echo $dataform['id']; ?>','<?php echo $datarecord['language_id']; ?>','<?php echo $datarecord['record_id']; ?>','<?php echo $datarecord['multi']; ?>'); return false;" style="position:relative; left:5px; top:2px;">Remove</a><?php
echo '<br>';
echo '</div>';

}}//select the value for more than one foreach //if $datarecord['element_hierarchy']===$dataform['id']  an uparxei eggrafh
 
 
//an den uparxei eggrafh create one empty
if($formcount===0){

$formcount+=1;
echo '<textarea rows="4" cols="60" class="textinput" name="'.$dataform['id'].'_'.$formcount.'" id="'.$dataform['id'].'_'.$formcount.'" ></textarea>&nbsp;&nbsp';

//if hierarchy type= langstring
if($dataform['datatype_id']===1){
echo '<select name="'.$dataform['id'].'_'.$formcount.'_lan" class="combo" style="vertical-align:top;">';
foreach($datalan as $datalan1){
echo '<option value="'.$datalan1['id'].'" ';
echo '>'.$datalan1['locale_name'].'</option>';
}
echo '</select>';

}//langstring

echo '<br>';

//end create one empty


}

echo "</div>";

?>


<?php
//if hierarchy type= langstring
 if($dataform['datatype_id']===1){ ?>
<div style="position:relative;clear:both;"><a href="#" onClick="addFormField('<?php echo $formcount; ?>','<?php echo $dataform['id']; ?>','hdnLine_<?php echo $dataform['id']; ?>'); return false;">Add</a></div>
<input name="hdnLine_<?php echo $dataform['id']; ?>" id="hdnLine_<?php echo $dataform['id']; ?>" type="hidden" value="<?php echo $formcount; ?>">
<?php } ?>


 <?php


echo '</div>';


if($dataform['max_occurs']>1){?>

<p><a href="#" onClick="addFormField('<?php echo $formcount; ?>','<?php echo $dataform['id']; ?>','hdnLine_<?php echo $dataform['id']; ?>'); return false;">Add</a></p>
<input name="hdnLine_group_<?php echo $dataform['id']; ?>" id="hdnLine_group_<?php echo $dataform['id']; ?>" type="hidden" value="<?php echo $formcount; ?>">
<!--<INPUT style="margin-top:0px; margin-left:10px;" type="button" value="Add" onclick="add('<?php //echo $formcount; ?>','<?php //echo $dataform['id']; ?>','hdnLine_<?php //echo $dataform['id']; ?>'); "/> -->
<?php }
echo '<br style="clear:both"><br>';
}


}//if $data['element_id']===$dataform['pelement_id']


}//if pelement tou hierarchy = element general (dataform)

	echo '<br><br></div>';  //close div general
	
		}//end for every element general  (data)

?>

<script type="text/javascript">
function addFormField(multi,divid,iddiv) {
	var id = document.getElementById(''+iddiv+'').value;
	id = (id - 1) + 2;
	document.getElementById(''+iddiv+'').value = id;
	
	jQuery('#'+divid+'_inputs').append("<div id='row" + id + "'><textarea cols='30' row='5' class='textinput' name='"+divid+"_"+id+"' id='txt" + id + "'></textarea>&nbsp;&nbsp<select style='vertical-align:top;' name='"+divid+"_"+id+"_lan' class='combo'><?php foreach($datalan as $datalan1){?><option value='<?php echo $datalan1['id']; ?>'><?php echo $datalan1['locale_name']; ?></option><?php } ?></select>&nbsp;&nbsp;<a href='#' onClick='removeFormField(\"#row" + id + "\"); return false;'>Remove</a><div>");

	
}

function addFormFieldSelect(multi,divid,iddiv,vocabulary_id) {
	var id = document.getElementById(''+iddiv+'').value;
	id = (id - 1) + 2;
	document.getElementById(''+iddiv+'').value = id;
	var selectoption="<div id='row" + id + "'><select name='"+divid+"_"+id+"' class='combo' style='width:300px;'>";
	selectoption+="<option value=''>Select</option>";
	
	<?php foreach($datavoc as $datavoc1){ ?>
	var vocabulary=<?php echo $datavoc1['id']; ?>; 	
	if(vocabulary_id==vocabulary){	
	selectoption+="<option value='<?php echo $datavoc1['value']; ?>'><?php echo $datavoc1['value']; ?></option>"; }	
	<?php }	?>
	selectoption+="</select> <a href='#' onClick='removeFormField(\"#row" + id + "\"); return false;'>Remove</a><div><br>";
	
	jQuery('#'+divid+'_inputs').append(selectoption);

	
}


function removeFormFieldExisted(id,element_hierarchy,language_id,record_id,multi) {

var answer = confirm("Are you sure you want to DELETE it?")
    if (answer){

jQuery.post("<?php echo uri('exhibits/deleteelementvalue'); ?>", { element_hierarchy: element_hierarchy, language_id: language_id, record_id: record_id, multi: multi },
   function(data) {

  jQuery('#'+id).remove();
});

} 
}



function removeFormField(id) {
	jQuery(id).remove();

}
</script>


</div><br style="clear:both;">
<input type="hidden" name="date_modified" id="date_modified" value="<?php echo $date_modified ?>" />
		<input type="hidden" name="item_id" id="item_id" value="<?php echo $item['id']; ?>" />
<?php } //is isset item
else{ ?>

<div class="field">
		<label for="title" id="title"><b>*<?php echo __('Title'); ?></b></label>
        <?php //$title=item('Dublin Core', 'Title'); ?>
		<textarea rows="4" cols="70" class="textinput" name="title" /></textarea>
		<?php //echo form_error('title'); ?>
</div> 

<div class="field">
		<label for="title" id="title"><b>*<?php echo __('Description'); ?></b></label>
        <?php //$title=item('Dublin Core', 'Title'); ?>
		<textarea rows="4" cols="70" class="textinput" name="description" /></textarea>
		<?php //echo form_error('title'); ?>
</div> 

<div class="field">
		<label for="title" id="title"><b>*URL</b></label>
        <?php //$title=item('Dublin Core', 'Title'); ?>
		<textarea rows="4" cols="70" class="textinput" name="link" /></textarea>
		<?php //echo form_error('title'); ?>
</div> 
<input type="hidden" name="type" value="11">


<?php } ?>
<div id="stepfile">
<?php //include('files-form.php'); ?> 
</div>


