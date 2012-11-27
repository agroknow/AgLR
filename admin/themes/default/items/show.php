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

<h1 id="item-title"><?php echo $itemTitle; ?> <span class="view-public-page">[ <a href="<?php echo html_escape(public_uri('items/show/'.item('id'))); ?>"><?php echo __('View Public Page'); ?></a> ]</span></h1>

<?php if (has_permission($item, 'edit')): ?>
<p id="edit-item" class="edit-button"><?php 
echo link_to_item(__('Edit this Item'), array('class'=>'edit'), 'edit'); ?></p>   
<?php endif; ?>

<ul class="item-pagination navigation group">
<li id="previous-item" class="previous">
    <?php echo link_to_previous_item(); ?>
</li>
<li id="next-item" class="next">
    <?php echo link_to_next_item(); ?>
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

<div id="item-images">
<?php echo display_files_for_item(array('imageSize' => 'fullsize')); ?> 
</div>



<?php fire_plugin_hook('admin_append_to_items_show_primary', $item); ?>

</div>
<div id="secondary">
    
   


    <div class="info-panel">
        <h2><?php echo __('Output Formats'); ?></h2>
        <div><?php echo output_format_list(); ?></div>
    </div>
    
    <?php fire_plugin_hook('admin_append_to_items_show_secondary', $item); ?>
</div>
<?php foot();?>
