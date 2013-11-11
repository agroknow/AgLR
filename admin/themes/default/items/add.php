<?php head(array('title' => 'Add Item', 'content_class' => 'vertical-nav', 'bodyclass' => 'items primary')); ?>

<h1><?php echo __('Add a File'); ?></h1>
<?php include('form-tabs.php'); ?>
<div id="primary">
    <SCRIPT TYPE="text/javascript">
        <!--
        // check that they entered an amount tested, an amount passed,
        // and that they didn't pass units than they more than tested

        function check()
        {
            var artitle =document.testform.title.value;
            var ardescription =document.testform.description.value;
            var arlink =document.getElementById("file-0").value


            var returnval;


 
            if((ardescription=="") || (artitle=="")) {
                alert("<?php echo __('You must fill all the mandatory fields (*)'); ?>");
                returnval = false;
            }
            else
            {
                if(arlink=="") {
                    alert("<?php echo __("Keep in mind that you didn't select a file to upload."); ?>");
                    returnval = false;
                }else{
                    returnval = true;
                }
            }
            return returnval;

        }
        // -->
    </SCRIPT>
    <form method="post" enctype="multipart/form-data" id="item-form" action="" name="testform" onSubmit="return check();">
        <?php include('form.php'); ?>
        <div>
            
            <input type="submit" name="add_new_item" class="submit" id="add_item" value="<?php echo __('Add Resource'); ?>" />
            <a href="<?php echo html_escape(uri('items')); ?>" class="submit" style="font-size: 13px; clear: none; margin-right:9px;"><?php echo __('Cancel'); ?></a>
        </div>
    </form>
</div>

<?php foot(); ?>
