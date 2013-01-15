<div class="field">
<?php echo label('collection-id', __('Collection'));?>
<div class="inputs">
    <?php 
    $action123 = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
    if(isset($action123) and $action123=='show'){
        echo select_collection(array('name'=>'collection_id', 'id'=>'collection-id', 'disable'=>'disable'),$item->collection_id);
    }else{
       echo select_collection(array('name'=>'collection_id', 'id'=>'collection-id'),$item->collection_id); 
    }
     ?>
</div>
</div>

<?php fire_plugin_hook('admin_append_to_items_form_collection', $item); ?>