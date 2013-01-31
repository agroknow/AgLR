<?php
    $itemTitle = strip_formatting(item('Dublin Core', 'Title'));
    if ($itemTitle != '' && $itemTitle != __('[Untitled]')) {
        $itemTitle = ': &quot;' . $itemTitle . '&quot; ';
    } else {
        $itemTitle = '';
    }
    $itemTitle = __('Edit Item #%s', item('id')) . $itemTitle;
?>
<?php head(array('title'=> $itemTitle, 'bodyclass'=>'items primary','content_class' => 'vertical-nav'));?>
<h1><?php echo $itemTitle; ?></h1>
<?php echo delete_button(null, 'delete-item', __('Delete this Item'), array(), 'delete-record-form'); ?>
<div>
<a style="position:relative; float:right; right:0px;cursor: hand; cursor: pointer;" id="show_optional">Enrich Metadata</a>
</div>
<br style="clear:both;">

<?php include 'form-tabs.php'; // Definitions for all the tabs for the form. ?>



<div id="primary">



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

    <form method="post" enctype="multipart/form-data" id="item-form" action="">
        <?php include 'form.php'; ?>
        <div>
        <?php echo submit(array('name'=>'save_meta', 'id'=>'save-changes', 'class'=>'submit'),  __('Save Changes')); ?>
            <?php //echo submit(array('name'=>'submit', 'id'=>'save-changes', 'class'=>'submit'), __('Save Changes')); ?>
        </div>
    </form>

</div>

<?php foot();?>
