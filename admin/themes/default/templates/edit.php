<?php
$itemTitle = strip_formatting(item('Dublin Core', 'Title'));
if ($itemTitle != '' && $itemTitle != __('[Untitled]')) {
    $itemTitle = ': &quot;' . $itemTitle . '&quot; ';
} else {
    $itemTitle = '';
}
$itemTitle = __('Edit Template #%s', item('id')) . $itemTitle;
?>
<?php head(array('title'=> $itemTitle, 'bodyclass'=>'items primary','content_class' => 'vertical-nav'));?>
<?php echo js('items'); ?>
<?php echo js('jquery.jstree'); ?>
<?php echo js('prototype'); ?>
<?php //echo js('scriptaculous');  ?>
<?php echo js('tooltip'); ?>
<?php //echo js('calendar/js/jquery-1.5.1.min.js');  ?>
<?php //echo js('calendar/css/ui-lightness/jquery-ui-1.8.13.custom.css');   ?>
<h1><?php echo $itemTitle; ?></h1>
<?php echo delete_button(null, 'delete-item', __('Delete this Template'), array(), 'delete-record-form'); ?>
<div>
    <a style="position:relative; float:right; right:0px;cursor: hand; cursor: pointer;" id="show_optional">Enrich Metadata</a>
</div>
                        <div id="show_optional_help" style="display:none; position:absolute;top:0px; border:1px solid #333;background:#f7f5d1;padding:2px 5px; color:#333;z-index:100;">
        <?php echo __('Use of more elements to describe your resource'); ?>
    </div>
     <script type="text/javascript">
var my_tooltip = new Tooltip('show_optional', 'show_optional_help');
</script>  
<p class="help_text" style="position:relative; float:left;">
                    <?php //echo __('Describe your resource providing the information requested bellow.'); ?> <br>
                    <?php //echo __('Make your resource public and visible in your pathway by clicking the Public button.'); ?><br>
                </p>
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
                                   jQuery('#show_optional_help').text("<?php echo __('Basic elements for describe your resource.'); ?>");

 
                }else{
                    jQuery('.optional_element').css("display", "none"); 
                    jQuery('#show_optional').css("background-color", "#F4F3EB");
                    jQuery('#show_optional').text("Enrich Metadata");
                                   jQuery('#show_optional_help').text("<?php echo __('Use of more elements to describe your resource'); ?>");


                }
              


            });


        });

    </script>

    <form method="post" enctype="multipart/form-data" id="item-form" action="">
        <?php include 'form.php'; ?>
        <div style="widht:300px;">
            <?php echo submit(array('name' => 'save_meta', 'id' => 'save-changes', 'class' => 'submit','style'=>'clear:none'), __('Save Changes')); ?>
            <?php //echo submit(array('name'=>'submit', 'id'=>'save-changes', 'class'=>'submit'), __('Use Templpate')); ?>


            <?php if (has_permission($item, 'edit')): ?>
                <?php echo '<p style="position:relative; clear:none;top: 1px;margin-right:10px; float:right;" id="edit-item" class="submit"><a style="color:#ffffff;text-decoration:none;" href="javascript:void(0);" onclick="translatediv(\'' . $item->id . '_trans\',\'' . $item->id . '\')">' . __('Use Template') . '</a><p>'; ?> 

                <?php echo '<div id="' . $item->id . '_trans" title="' . __('Please select type of Resource to use the Template:') . '"></div>'; ?>
                <script type="text/javascript" charset="utf-8">
                    function translatediv(name,item_id){


                        //var answer = confirm("Are you sure you want to TRANSLATE it?")
                        //   if (answer){

                        //var name = document.getElementById(name).value;

                        jQuery.post("<?php echo uri('templates/selecttemplate'); ?>", { name: name, item_id: item_id },
                        function(data) {

                            //alert(name);
                            // document.getElementById('#'+nameid+'_trans')=data;
                            //jQuery('#'+nameid+'_trans').html(data);

                            jQuery('#'+name+'').html(data).dialog({modal: true}).dialog('open');
                        });

                        //}

                    }
                </script>

<?php endif; ?>
        </div>
    </form>

</div>

<?php foot(); ?>
