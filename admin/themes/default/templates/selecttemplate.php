<?php
$db = Zend_Registry::get('db');
?>

<?php
if (!isset($_POST['submit_language'])) {

    //query for all values
    $sql = "SELECT * FROM metadata_record WHERE object_id=" . $_POST['item_id'] . " and object_type='template'";
    $execrecord = $db->query($sql);
    $record = $execrecord->fetch();

    $record_id = $record['id'];
    ?>
    <script>
        function showloader() { 

            document.getElementById('pleasewait').style.display='';

        }

        function hideloader() { 

            document.getElementByID('pleasewait').style.display='none';

        }
    </script>
    <?php if ($record['id']) { ?>
            <!--<span>Please select language to translate:<br><br></span>-->
        <form action="<?php echo uri('templates/createresourcefromtemplate'); ?>" name="select_template_form" method="post" onsubmit="showloader();">
            <input type="radio" value="hyperlink" name="resourcetype" checked="checked"/><?php echo __('Use this Template to describe a Hyperlink'); ?><br />
            <input type="radio" value="image" name="resourcetype" /><?php echo __('Use this Template to describe an Image'); ?><br />
            <input type="radio" value="file" name="resourcetype"/><?php echo __('Use this Template to describe a File'); ?><br /><br />
            <input type="submit" value="<?php echo __('Use Template'); ?>" name="submit_language">
            <input type="hidden" value="<?php echo $_POST['item_id']; ?>" name="item_id">
        </form>

        <div id="pleasewait" style=" display: none; position: absolute; top: 0px; left: 0px; z-index: 100; ">
            <span style="position: absolute; z-index: 4; top: 45px; left: 10px;">Please wait. Copying metadata from Template in progress</span>
            <img src="<?php echo uri('themes/default/templates/images/loader.gif'); ?>" alt="loading" style="width: 100%; height: 100%;"/>

        </div>  
    <?php } ////if record['id']  ?>

<?php } else { ///////////////////////////////else user submit ?>


    <!--code after submit to auto translate-->
    <?php
    



    
}///////////////else//////
