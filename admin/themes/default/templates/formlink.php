<?php echo js('tiny_mce/tiny_mce'); 
// echo js('tiny_mce/tiny_mce_src'); // Use the 'tiny_mce_src' file for debugging.
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

<input type="hidden" name="type" value="28">
