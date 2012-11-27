<?php /*?><?php 
$pathToConvert = get_option('path_to_convert');
if (empty($pathToConvert) && has_permission('Settings', 'edit')): ?>
    <div class="error"><?php echo __('The path to Image Magick has not been set. No derivative images will be created. If you would like Omeka to create derivative images, please add the path to your settings form.'); ?></div>
<?php endif; ?>
<?php if ( item_has_files() ): ?>
    <h3><?php echo __('Current Files'); ?></h3>
    <div id="file-list">
    <table>
        <thead>
            <tr>
                <th><?php echo __('File Name'); ?></th>
                <th><?php echo __('Edit File Metadata'); ?></th>
                <th><?php echo __('Order'); ?></th>
                <th><?php echo __('Delete'); ?></th>
            </tr>
        </thead>
        <tbody>
    <?php foreach( $item->Files as $key => $file ): ?>
        <tr>
            <td><?php echo link_to($file, 'show', html_escape($file->original_filename), array()); ?></td>
            <td class="file-link">
                <?php echo link_to($file, 'edit', __('Edit'), array('class'=>'edit')); ?>
            </td>
            <td><?php echo $this->formText("order[{$file->id}]", $file->order, array('size' => 3)); ?></td>
            <td class="delete-link">
                <?php echo checkbox(array('name'=>'delete_files[]'),false,$file->id); ?>
            </td>   
        </tr>

    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
<?php endif; ?>
<h3><?php echo __('Add New Files'); ?></h3>

<div id="add-more-files">
<label for="add_num_files"><?php echo __('Find a File'); ?></label>
    <div class="files">
    <?php $numFiles = (int)@$_REQUEST['add_num_files'] or $numFiles = 1; ?>
    <?php 
    echo text(array('name'=>'add_num_files','size'=>2),$numFiles);
    echo submit('add_more_files', 'Add this many files'); 
    ?>
    </div>
</div>

<div class="field" id="file-inputs">
    <label><?php echo __('Find a File'); ?></label>
        
    <?php for($i=0;$i<$numFiles;$i++): ?>
    <div class="files inputs">
        <input name="file[<?php echo $i; ?>]" id="file-<?php echo $i; ?>" type="file" class="fileinput" />          
    </div>
    <?php endfor; ?>
</div>

<?php fire_plugin_hook('admin_append_to_items_form_files', $item); ?><?php */?>
<?php 
$pathToConvert = get_option('path_to_convert');
if (empty($pathToConvert) && has_permission('Settings', 'edit')): ?>
    <div class="error">The path to Image Magick has not been set. No derivative images will be created. If you would like Omeka to create derivative images, please add the path to your settings form.</div>
<?php endif; ?>
<?php if ( item_has_files() ){ ?>
    <h3>Current Files</h3>
    <div id="file-list">
    <table>
        <thead>
            <tr>
                <th>File Name</th>
                <!-- <th>Edit File Metadata</th> -->
                <th>Delete?</th>
            </tr>
        </thead>
        <tbody>
    <?php foreach( $item->Files as $key => $file ): ?>
        <tr>
            <td><?php echo link_to($file, 'show', html_escape($file->original_filename), array()); ?></td>
            <!-- <td class="file-link">
                <?php //echo link_to($file, 'edit', 'Edit', array('class'=>'edit')); ?>
            </td>  -->
            <td class="delete-link">
                <?php echo checkbox(array('name'=>'delete_files[]'),false,$file->id); ?>
            </td>   
        </tr>

    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
<?php }else{ ?>
<h3 style="font-weight:bold;"><?php echo __('Upload a File'); ?></h3>

<!--<div id="add-more-files">
<label for="add_num_files">Find a File</label>
    <div class="files">
    <?php $numFiles = (int)@$_REQUEST['add_num_files'] or $numFiles = 1; ?>
    <?php 
    //echo text(array('name'=>'add_num_files','size'=>2),$numFiles);
    //echo submit('add_more_files', 'Add this many files'); 
    ?>
    </div>
</div> -->

<div >
   <!-- <label>Find a File</label> -->
        
    <?php //for($i=0;$i<$numFiles;$i++): ?>
    <div class="">
        <input name="file[0]" id="file-0" type="file" class="fileinput" />          
    </div>
    <?php //endfor; ?>
</div>
<?php fire_plugin_hook('admin_append_to_items_form_files', $item); ?>
<?php } ?>


