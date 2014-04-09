<?php
$db = Zend_Registry::get('db');

//query for all values
$sql = "SELECT * FROM metadata_record WHERE object_id=" . $_POST['item_id'] . " and object_type='item'";
$execrecord = $db->query($sql);
$record = $execrecord->fetch();

$record_id = $record['id'];
$sql = "SELECT * FROM metadata_element_value WHERE record_id=" . $record_id . " and (element_hierarchy=6 or element_hierarchy=8 or element_hierarchy=35) order by element_hierarchy,multi ASC";
$exec5 = $db->query($sql);
$data5 = $exec5->fetchAll();
$es_language_id = 0;
$en_language_id = 0;
$fr_language_id = 0;
$de_language_id = 0;
$it_language_id = 0;
foreach ($data5 as $data5) {
    $lang_language_id[] = $data5['language_id'];
//echo $data5['element_hierarchy']."&nbsp;".$data5['multi']."&nbsp;".$data5['language_id']."<br>";
}

if (!isset($_POST['submit_language'])) {



//print_r($lang_language_id);
//end
    ?>
    <script>
        function showloader() {

            document.getElementById('pleasewait').style.display = '';

        }

        function hideloader() {

            document.getElementByID('pleasewait').style.display = 'none';

        }
    </script>

                    <!--<span>Please select language to translate:<br><br></span>-->
    <form action="<?php echo uri('items/translatexerox'); ?>" name="select_language_form" method="post" onsubmit="showloader();">
        <input type="checkbox" value="en" name="language_select[]" <?php if (in_array("en", $lang_language_id)) { ?>checked="checked" <?php } ?> />English<br />
        <input type="checkbox" value="fr" name="language_select[]" <?php if (in_array("fr", $lang_language_id)) { ?>checked="checked" <?php } ?> />French<br />
        <input type="checkbox" value="de" name="language_select[]" <?php if (in_array("de", $lang_language_id)) { ?>checked="checked" <?php } ?> />German<br />
        <input type="checkbox" value="es" name="language_select[]" <?php if (in_array("es", $lang_language_id)) { ?>checked="checked" <?php } ?> />Spanish<br />
        <input type="checkbox" value="it" name="language_select[]" <?php if (in_array("it", $lang_language_id)) { ?>checked="checked" <?php } ?> />Italian<br />
        <input type="checkbox" value="tr" name="language_select[]" <?php if (in_array("tr", $lang_language_id)) { ?>checked="checked" <?php } ?> />Turkish<br />
        <input type="checkbox" value="lv" name="language_select[]" <?php if (in_array("lv", $lang_language_id)) { ?>checked="checked" <?php } ?> />Latvian<br />
        <input type="checkbox" value="ru" name="language_select[]" <?php if (in_array("ru", $lang_language_id)) { ?>checked="checked" <?php } ?> />Russian<br />
        <input type="checkbox" value="et" name="language_select[]" <?php if (in_array("et", $lang_language_id)) { ?>checked="checked" <?php } ?> />Estonian<br />
        <input type="checkbox" value="el" name="language_select[]" <?php if (in_array("el", $lang_language_id)) { ?>checked="checked" <?php } ?> />Greek

        <input type="submit" value="<?php echo __('Translate'); ?>" name="submit_language">
        <input type="hidden" value="<?php echo $_POST['item_id']; ?>" name="item_id">
    </form>

    <div id="pleasewait" style=" display: none; position: absolute; top: 0px; left: 0px; z-index: 100; background-color: #ffffff; width: 100%; height: 100%;">
        <span style="position: absolute; z-index: 4; top: 45px; left: 10px;">Please wait. The translation is in progress</span>
        <img src="<?php echo uri('themes/default/items/images/loader.gif'); ?>" alt="loading" style="width: 100%; height: 100%;"/>

    </div>  

<?php } else { ///////////////////////////////else user submit  ?>


    <!--code after submit to auto translate-->
    <?php
    //get languages that are send as from in AMT 
    Global $language_from_for_rating;
    Global $language_to_for_rating;
    //query for all values
    $sql = "SELECT * FROM metadata_record WHERE object_id=" . $_POST['item_id'] . " and object_type='item'";
    $execrecord = $db->query($sql);
    $record = $execrecord->fetch();

    $record_id = $record['id'];
    $sql = "SELECT * FROM metadata_element_value WHERE record_id=" . $record_id . " and (element_hierarchy=6 or element_hierarchy=8 or element_hierarchy=35) order by element_hierarchy,multi ASC";
    $exec5 = $db->query($sql);
    $data5 = $exec5->fetchAll();

    $itemTitle = strip_formatting(item('Dublin Core', 'Title'));
    if ($itemTitle != '' && $itemTitle != __('[Untitled]')) {
        $itemTitle = ': &quot;' . $itemTitle . '&quot; ';
    } else {
        $itemTitle = '';
    }
    $itemTitle = __('Translate Item #%s', item('id')) . $itemTitle;
    head(array('title' => $itemTitle, 'bodyclass' => 'items primary', 'content_class' => 'vertical-nav'));
    ?>
    <h1><?php echo $itemTitle; ?></h1>
    <?php $AMTlanguages = $_POST['language_select']; ?>
    <form method="post" enctype="multipart/form-data" id="item-form" action="">
        <?php include 'form.php'; ?>
        <?php
        echo "<h2>Rate automatic translation</h2>";
        echo '<div> ';

        //get languages that are send as from in AMT 
        $language_from_for_rating = substr($language_from_for_rating, 0, -1);
        //select the language that is most common in the array
        $language_from_for_rating = explode(',', $language_from_for_rating);
        $result = array_count_values($language_from_for_rating);
        asort($result);
        end($result);        //print_r($result);
        $fromlanguage = key($result);

        //get languages that are send as to in AMT 
        $language_to_for_rating = substr($language_to_for_rating, 0, -1);
        //select the language that is most common in the array
        $language_to_for_rating = explode(',', $language_to_for_rating);
        $language_to_for_rating = array_unique($language_to_for_rating);        //print_r($language_to_for_rating);
        $tolanguage = $language_to_for_rating;

        //foreach languages that are send as to in AMT
        foreach ($tolanguage as $AMTlanguage) {
            echo "<div style='clear:both; margin-top:15px; width:300px;'>";
            echo "<label style='width:80px;' >" . $AMTlanguage . "</label>";

            //echo item('id');
            $ratingtocheck = 0;
            $votestocheck = 0;

            $page2 = file_get_contents("http://oe-api.aglr.agroknow.gr/translationapi/analytics/resources/" . item('id') . "/translation/" . item('id') . "_" . $AMTlanguage . "_" . $fromlanguage . "/rating", false, $ctx);
            $returning_results = array();
            if (!$page2 === false) {
                $obj2 = json_decode($page2);
                if ($obj2 and $obj2->data->rating) {
                    $ratingtocheck = $obj2->data->rating;
                }
                if ($obj2 and $obj2->data->votes) {
                    $votestocheck = $obj2->data->votes;
                }
            }
            ?>
            <div style="float:left;">
                <input name="setstarrating_<?php echo item('id') . '_' . $AMTlanguage . '_' . $fromlanguage; ?>" type="radio" class="star" value="1" <?php
                if ($ratingtocheck == 1) {
                    echo ' checked=checked ';
                }
                ?>/>
                <input name="setstarrating_<?php echo item('id') . '_' . $AMTlanguage . '_' . $fromlanguage; ?>" type="radio" class="star" value="2" <?php
                if ($ratingtocheck == 2) {
                    echo ' checked=checked ';
                }
                ?>/>
                <input name="setstarrating_<?php echo item('id') . '_' . $AMTlanguage . '_' . $fromlanguage; ?>" type="radio" class="star" value="3" <?php
                if ($ratingtocheck == 3) {
                    echo ' checked=checked ';
                }
                ?>/>
                <input name="setstarrating_<?php echo item('id') . '_' . $AMTlanguage . '_' . $fromlanguage; ?>" type="radio" class="star" value="4" <?php
                if ($ratingtocheck == 4) {
                    echo ' checked=checked ';
                }
                ?>/>
                <input name="setstarrating_<?php echo item('id') . '_' . $AMTlanguage . '_' . $fromlanguage; ?>" type="radio" class="star" value="5" <?php
                if ($ratingtocheck == 5) {
                    echo ' checked=checked ';
                }
                ?>/>
            </div>
            <?php
            echo " ( of " . $votestocheck . " votes)";
            echo "</div>";
        }
        echo '</div>';
        ?>
        <div>
                <?php echo submit(array('name' => 'save_meta', 'id' => 'save-changes', 'class' => 'submit'), __('Save Translations')); ?>
            <div style="position: relative; float: right; margin-right: 10px;">
                <a href="javascript:history.back()" style=" font-size: 14.6px;"  class="submit"><?php echo __('Cancel'); ?></a>
            </div>
                <?php //echo submit(array('name'=>'submit', 'id'=>'save-changes', 'class'=>'submit'), __('Save Changes'));    ?>
        </div>
    </form>

    <?php //print_r($_POST); print_r($item);    ?>

<?php } ?>
<script language="javascript" charset='utf-8'>
    function translatexerox(language) {
        //jQuery('#<?php //echo $_POST['dividtextid'];      ?>_trans').dialog('close');
        var isolanguage = '';
        isolanguage = language;
// if(language=='en-EN'){isolanguage='en'}
// if(language=='es_ES'){isolanguage='es'}
// if(language=='de_DE'){isolanguage='de'}
// if(language=='it_IT'){isolanguage='it'}
// if(language=='fr_FR'){isolanguage='fr'}

<?php //$string=translatexerox("de_DE",$_POST["dividtext"]); $string = ereg_replace("\n", " ", $string);      ?>
        //var dividtexttext='<?php //echo $string;     ?>';
        //document.getElementById('<?php //echo $_POST['dividtextid'];    ?>').innerHTML=dividtexttext;
// alert(dividtext);
//dlg1 = jQuery(window.parent.document.getElementById("<?php //echo $_POST['dividtextid'];    ?>").val);




    }
</script>
<?php
echo '<script src="' . uri('themes/default/items/rating/jquery.js') . '" type="text/javascript"></script>'
 . '<script src="' . uri('themes/default/items/rating/jquery.MetaData.js') . '" type="text/javascript" language="javascript"></script>'
 . '<script src="' . uri('themes/default/items/rating/jquery.rating.js') . '" type="text/javascript" language="javascript"></script>
 <link href="' . uri('themes/default/items/rating/jquery.rating.css') . '" type="text/css" rel="stylesheet"/> '
 . ' <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/jquery-ui.min.js" type="text/javascript"></script>';
?>