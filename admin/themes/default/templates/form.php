<?php
//echo js('tiny_mce/tiny_mce');
// echo js('tiny_mce/tiny_mce_src'); // Use the 'tiny_mce_src' file for debugging.
?>
<script type="text/javascript" charset="utf-8">
    //<![CDATA[
    // TinyMCE hates document.ready.
    jQuery(window).load(function () {
        Omeka.Items.initializeTabs();

        //var addImage = <?php //echo js_escape(img('silk-icons/add.png'));  ?>;
        //var deleteImage = <?php //echo js_escape(img('silk-icons/delete.png'));  ?>;
        //Omeka.Items.tagDelimiter = <?php //echo js_escape(get_option('tag_delimiter'));  ?>;
        //Omeka.Items.enableTagRemoval(addImage, deleteImage);
        //Omeka.Items.makeFileWindow();
        //Omeka.Items.tagChoices('#tags', <?php //echo js_escape(uri(array('controller' => 'tags', 'action' => 'autocomplete'), 'default', array(), true));  ?>);

        // Must run the element form scripts AFTER reseting textarea ids.
       // Query(document).trigger('omeka:elementformload');

        //Omeka.Items.enableAddFiles();
        //Omeka.Items.changeItemType(<?php //echo js_escape(uri("items/change-type"))  ?><?php //if ($id = item('id')) echo ', ' . $id;  ?>);
    });

    jQuery(document).bind('omeka:elementformload', function () {
        Omeka.Items.makeElementControls(<?php echo js_escape(uri('templates/element-form')); ?><?php if ($id = item('id')) echo ', ' . $id; ?>);
        //Omeka.Items.enableWysiwyg();
    });
    //]]>   
</script>

<?php echo flash(); ?>

<div id="public-featured">
    <?php if (has_permission('Items', 'makePublic')) { ?>
        <div class="checkbox">
            <label for="public"><?php echo __('Validate'); ?>:</label>
            <div class="checkbox"><?php echo checkbox(array('name' => 'public', 'id' => 'public'), $item->public); ?></div>
        </div>
    <?php } else { ?>
        <input type="hidden" name="public" value="<?php echo $item->public; ?>">
    <?php } ?>
    <!--    <?php //if ( has_permission('Items', 'makeFeatured') ):   ?>
            <div class="checkbox">
                <label for="featured">Featured:</label> 
                <div class="checkbox"><?php //echo checkbox(array('name'=>'featured', 'id'=>'featured'), $item->featured);   ?></div>
            </div>
    <?php //endif;  ?> -->
</div>
<div id="item-metadata">

    <?php if (isset($item['id'])) { ?>
        <?php
        $step = 0;

//query for all elements without asking pelement
        $values = $metadataFile[metadata_elements_hide_from_resources][element_hierarchy_resources_hide];
        if ($values != false) {
            $valuesql = "and f.id NOT IN (" . implode(',', $values) . ") ";
        } else {
            $valuesql = "";
        }
        $sql = "SELECT f.*,e.vocabulary_id,e.id as elm_id FROM  metadata_element_hierarchy f  JOIN metadata_element e ON f.element_id = e.id WHERE f.is_visible=1 and e.schema_id=" . $metadataFile[metadata_schema_resources][id] . " " . $valuesql . " ORDER BY (case WHEN f.sequence IS NULL THEN '9999' ELSE f.sequence END) ASC";
/////////////////query for translate specific elements//////////
        if (isset($_POST['submit_language'])) {
        $sql = "SELECT f.*,e.vocabulary_id,e.id as elm_id FROM  metadata_element_hierarchy f  JOIN metadata_element e ON f.element_id = e.id WHERE (f.id=6 or f.id=8 or f.id=35) and f.is_visible=1 ORDER BY (case WHEN f.sequence IS NULL THEN '9999' ELSE f.sequence END) ASC";
        }
        $exec4 = $db->query($sql);
        $data4 = $exec4->fetchAll();
        $exec4 = NULL;
//end
//query for all values
        $sql = "SELECT * FROM metadata_record WHERE object_id=? and object_type=?";
        $execrecord = $db->query($sql, array($item->id, 'template'));
        $record = $execrecord->fetch();
        $execrecord = NULL;
        $record_id = $record['id'];
//end
//query for all languages iso vocabulary_record=13 (ISO LANGUAGES)
        $sqllan = "SELECT e.value,e.sequence,e.id as vov_rec_id FROM metadata_vocabulary_record e JOIN
					metadata_vocabulary_value f ON f.vocabulary_rid = e.id WHERE e.vocabulary_id=? and e.public=?  and f.language_id=?  ORDER BY (case WHEN e.sequence IS NULL THEN '99999' END),e.sequence,f.label ASC";
        $execlan = $db->query($sqllan, array(23, 1, get_language_for_switch()));
        $datalan = $execlan->fetchAll();
        $execlan = NULL;

//end


        foreach ($general_pelements as $data) {  //for every element general
            $step+=1;
            echo '<div class="toggle" id="step' . $step . '">'; //create div for toggle
//if($step==9){echo createlomlabel('Under Construction!','style="width:158px;"');}
//
/////////////////if translation no central description//////////
            if (!isset($_POST['submit_language'])) {
                $label_description = return_label_description($data['element_id']);
            }

            if (strlen($label_description) > 0) {
                echo "<p style='padding:2px;border:solid 1px #76BB5F; color: #76BB5F;'><strong><i>" . $label_description . "</i></strong></p>";
            }
            
            foreach ($data4 as $dataform) {  //for every element under general
                if ($data['element_id'] === $dataform['pelement_id']) { //if pelement tou hierarchy = element general
                    if (!isset($_POST['submit_language'])) {
                        checkelement($dataform, $datalan, $record, NULL, NULL, NULL, NULL, NULL, $xml_general);
                    } else {
                        checkelement($dataform, $datalan, $record, 0, NULL, NULL, 1, NULL, $xml_general);
                    }
                }//if $data['element_id']===$dataform['pelement_id']
            }//if pelement tou hierarchy = element general (dataform)

            
            echo '</div>';  //close div general
        }//end for every element general  (data)
        ?>

        <script type="text/javascript">
            function addFormField(multi,divid,iddiv) {
                var id = document.getElementById(''+iddiv+'').value;
                id = (id - 1) + 2;
                document.getElementById(''+iddiv+'').value = id;
        	
                jQuery('#'+divid+'_inputs').append("<div id='"+divid+"_"+id+"_field' style='clear:both;'><textarea cols='60' rows='4' class='textinput' name='"+divid+"_"+id+"' id='txt" + id + "' style='float:left;'></textarea>&nbsp;&nbsp<div style='position:relative; left:5px; top:2px; float:left;'><select style='vertical-align:top;' name='"+divid+"_"+id+"_lan' class='combo'><option value='none'><?php echo __('Select'); ?> </option><?php foreach ($datalan as $datalan1) { ?><option value='<?php echo $datalan1['value']; ?>'><?php echo voc_multi_label($datalan1['vov_rec_id']); ?></option><?php } ?></select>&nbsp;&nbsp;<br><a class='lom-remove' style='float:right;' href='#' onClick='removeFormField(\"#"+divid+"_"+id+"_field\"); return false;'><?php echo __('Remove Language'); ?></a></div><div>");

        	
            }

            function addFormvcard(multi,divid,iddiv,label) {
                var id = document.getElementById(''+iddiv+'').value;
                id = (id - 1) + 2;
                document.getElementById(''+iddiv+'').value = id;

                jQuery('#'+divid+'').append("<div id='"+divid+"_"+id+"' style='float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px;width:100%;'><input name='vcard_general_"+divid+"_"+id+"' id='vcard_general_"+divid+"_"+id+"' type='hidden' value=''><div style='float:left;'><label style='width:158px;'>"+label+"&nbsp;&nbsp;<a class='lom-remove' href='#' onClick='removedivid(\""+divid+"_"+id+"\"); return false;'><?php echo __('Remove'); ?></a></label></div><br><div style='float:left;'><span style='float:left; width:70px;'><?php echo __('Name'); ?>: </span><input type='text' value='' name='vcard_name_"+divid+"_"+id+"' style='float:left;width:200px;' id='"+divid+"_"+id+"' class='textinput'><br><br><span style='float:left; width:70px;'><?php echo __('Surname'); ?>: </span><input type='text' value='' name='vcard_surname_"+divid+"_"+id+"' style='float:left;width:200px;' id='"+divid+"_"+id+"' class='textinput'><br><br><span style='float:left; width:70px;'><?php echo __('Email'); ?>: </span><input type='text' value='' name='vcard_email_"+divid+"_"+id+"' style='float:left;width:200px;' id='"+divid+"_"+id+"' class='textinput'><br><br><span style='float:left; width:70px;'><?php echo __('Organization'); ?>: </span><input type='text' value='' name='vcard_organization_"+divid+"_"+id+"' style='float:left;width:200px;' id='"+divid+"_"+id+"' class='textinput'><br><br><div></div>");

        	
            }

            function removeFormvcardExisted(divid,element_hierarchy,record_id,multi,vcard,parent_indexer) {

                var answer = confirm("<?php echo __('Are you sure you want to DELETE it?'); ?>")
                if (answer){

                    jQuery.post("<?php echo uri('templates/deleteelementvalue'); ?>", { element_hierarchy: element_hierarchy, record_id: record_id, multi: multi, vcard: vcard, parent_indexer: parent_indexer },
                    function(data) {

                        jQuery('#'+divid).remove();
                    });

                } 
            }



            function removeFormmultiParent(divid,element_hierarchy,record_id,multi,parent_element) {

                var answer = confirm("<?php echo __('Are you sure you want to DELETE it?'); ?>")
                if (answer){

                    jQuery.post("<?php echo uri('templates/deleteelementvalue'); ?>", { element_hierarchy: element_hierarchy, record_id: record_id, multi: multi, parent_element: parent_element},
                    function(data) {

                        jQuery('#'+divid).remove();
                    });

                } 
            }

            function addFormFieldText(multi,divid,iddiv) {
                var id = document.getElementById(''+iddiv+'').value;
                id = (id - 1) + 2;
                document.getElementById(''+iddiv+'').value = id;
        	
                jQuery('#'+divid+'_inputs').append("<div id='"+divid+"_"+id+"_field' style='margin-top:15px;style='clear:both;''><input type='text' class='textinput' style='width:200px;' name='"+divid+"_"+id+"' id='txt" + id + "' value=''>&nbsp;&nbsp<select style='vertical-align:top;' name='"+divid+"_"+id+"_lan' class='combo'><option value='none'><?php echo __('Select'); ?> </option><?php foreach ($datalan as $datalan1) { ?><option value='<?php echo $datalan1['value']; ?>'><?php echo voc_multi_label($datalan1['vov_rec_id']); ?></option><?php } ?></select>&nbsp;&nbsp;<a class='lom-remove' style='float:right;' href='#' onClick='removeFormField(\"#"+divid+"_"+id+"_field\"); return false;'><?php echo __('Remove Language'); ?></a><div>");

        	
            }




            function addFormmultiParent(multi,divid,iddiv,label) {
                var id = document.getElementById(''+iddiv+'').value;
                id = (id - 1) + 2;
                document.getElementById(''+iddiv+'').value = id;


                jQuery.post("<?php echo uri('templates/childsfromparentelement'); ?>", { element_hierarchy: divid, multi: id},
                function(data) {

                    jQuery('#'+divid+'').append(data);
                });


            }


            function addFormTotalField(multi,divid,iddiv,label) {
                var id = document.getElementById(''+iddiv+'').value;
                id = (id - 1) + 2;
                document.getElementById(''+iddiv+'').value = id;

                jQuery('#'+divid+'_inputs').append("<div id='"+divid+"_"+id+"_inputs'><hr style='clear:both;'><a class='lom-add-new' href='#' onClick='addFormField(\"0\",\""+divid+"_"+id+"\",\"hdnLine_"+divid+"_"+id+"\"); return false;'><?php echo __('Add Language'); ?></a>&nbsp;&nbsp;<a class='lom-remove' href='#' onClick='removeFormFieldTotal(\""+divid+"_"+id+"\"); return false;'><?php echo __('Remove'); ?> "+label+"</a><br><br><div id='"+divid+"_"+id+"_1_field'><textarea cols='60' rows='4' class='textinput' name='"+divid+"_"+id+"_1' id='txt1'></textarea>&nbsp;&nbsp<select style='vertical-align:top;' name='"+divid+"_"+id+"_1_lan' class='combo'><option value='none'><?php echo __('Select'); ?> </option><?php foreach ($datalan as $datalan1) { ?><option value='<?php echo $datalan1['value']; ?>'><?php echo voc_multi_label($datalan1['vov_rec_id']); ?></option><?php } ?></select>&nbsp;&nbsp;<a class='lom-remove' style='float:right;' href='#' onClick='removeFormField(\"#"+divid+"_"+id+"_1_field\"); return false;'><?php echo __('Remove Language'); ?></a><div><input type='hidden' name='hdnLine_"+divid+"_"+id+"' id='hdnLine_"+divid+"_"+id+"' value='1'></div>");

        	
            }

            function addFormTotalFieldText(multi,divid,iddiv,label) {
                var id = document.getElementById(''+iddiv+'').value;
                id = (id - 1) + 2;
                document.getElementById(''+iddiv+'').value = id;

                jQuery('#'+divid+'_inputs').append("<div id='"+divid+"_"+id+"_inputs'><hr style='clear:both;'><a class='lom-add-new' href='#' onClick='addFormFieldText(\"0\",\""+divid+"_"+id+"\",\"hdnLine_"+divid+"_"+id+"\"); return false;'><?php echo __('Add Language'); ?></a>&nbsp;&nbsp;<a class='lom-remove' href='#' onClick='removeFormFieldTotal(\""+divid+"_"+id+"\"); return false;'><?php echo __('Remove'); ?> "+label+"</a><br><br><div id='"+divid+"_"+id+"_1_field'><input type='text' class='textinput' style='width:200px;' name='"+divid+"_"+id+"_1' id='txt1' value=''>&nbsp;&nbsp<select style='vertical-align:top;' name='"+divid+"_"+id+"_1_lan' class='combo'><option value='none'><?php echo __('Select'); ?> </option><?php foreach ($datalan as $datalan1) { ?><option value='<?php echo $datalan1['value']; ?>'><?php echo voc_multi_label($datalan1['vov_rec_id']); ?></option><?php } ?></select>&nbsp;&nbsp;<a class='lom-remove' style='float:right;' href='#' onClick='removeFormField(\"#"+divid+"_"+id+"_1_field\"); return false;'><?php echo __('Remove Language'); ?></a><div><input type='hidden' name='hdnLine_"+divid+"_"+id+"' id='hdnLine_"+divid+"_"+id+"' value='1'></div>");

        	
            }

            function addFormTotalFieldTextnolan(multi,divid,iddiv,label) {
                var id = document.getElementById(''+iddiv+'').value;
                id = (id - 1) + 2;
                document.getElementById(''+iddiv+'').value = id;

                jQuery('#'+divid+'_inputs').append("<div id='"+divid+"_"+id+"_inputs'><hr style='clear:both;'><div id='"+divid+"_"+id+"_1_field'><input type='text' class='textinput' style='width:200px;' name='"+divid+"_"+id+"_1' id='txt1' value=''><a class='lom-remove' href='#' onClick='removeFormFieldTotal(\""+divid+"_"+id+"\"); return false;'><?php echo __('Remove'); ?> </a></div>");

        	
            }

            function addFormFieldSelect(multi,divid,iddiv,vocabulary_id) {
                var id = document.getElementById(''+iddiv+'').value;
                id = (id - 1) + 2;
                document.getElementById(''+iddiv+'').value = id;
                
                jQuery.post("<?php echo uri('templates/findvocbyid'); ?>", { vocabulary_id: vocabulary_id, id: id,  divid:divid},
                function(data) {

                    jQuery('#'+divid+'_inputs').append(data);
                });
                


        	
        }

        function addFormFieldSelectXml(multi,divid,iddiv,vocabulary_id) {
            var id = document.getElementById(''+iddiv+'').value;
            id = (id - 1) + 2;
            document.getElementById(''+iddiv+'').value = id;
        	
            jQuery.post("<?php echo uri('templates/xmlselectbox'); ?>", { vocabulary_id: vocabulary_id, id: id,  divid:divid, ontology:0},
            function(data) {

                jQuery('#'+divid+'_inputs').append(data);
            });
        	
        }

        function addFormFieldSelectXmlOntology(multi,divid,iddiv,vocabulary_id) {
            var id = document.getElementById(''+iddiv+'').value;
            id = (id - 1) + 2;
            document.getElementById(''+iddiv+'').value = id;
        	
            jQuery.post("<?php echo uri('templates/xmlselectbox'); ?>", { vocabulary_id: vocabulary_id, id: id,  divid:divid,  ontology:1},
            function(data) {

                jQuery('#'+divid+'_inputs').append(data);
            });
        	
        }


        function removeFormFieldExisted(id,element_hierarchy,language_id,record_id,multi) {

                var answer = confirm("<?php echo __('Are you sure you want to DELETE it?'); ?>")
            if (answer){

                jQuery.post("<?php echo uri('templates/deleteelementvalue'); ?>", { element_hierarchy: element_hierarchy, language_id: language_id, record_id: record_id, multi: multi },
                function(data) {

                    jQuery('#'+id).remove();
                });

            } 
        }


        function removeFormFieldTotalExisted(id,element_hierarchy,record_id,multi,allvalues) {

                var answer = confirm("<?php echo __('Are you sure you want to DELETE it?'); ?>")
            if (answer){

                jQuery.post("<?php echo uri('templates/deleteelementvalue'); ?>", { element_hierarchy: element_hierarchy, record_id: record_id, multi: multi, allvalues: allvalues },
                function(data) {

                    jQuery('#'+id+'_inputs').remove();
                });

            } 
        }

        function removeFormFieldTotal(id) {

                var answer = confirm("<?php echo __('Are you sure you want to DELETE it?'); ?>")
            if (answer){

                jQuery('#'+id+'_inputs').remove();
            } 
        }

        function UpdateLangstringFormFieldExisted(element_hierarchy,record_id,multi,language_id_old,language_id,id) {

                var answer = confirm("<?php echo __('Are you sure you want to CHANGE the language? This action will be SAVED!'); ?>");
            if (answer){

                jQuery.post("<?php echo uri('templates/updatelangstringelementvalue'); ?>", { element_hierarchy: element_hierarchy, language_id: language_id, language_id_old: language_id_old, record_id: record_id, multi: multi },
                function(data) {


                });

            }else{document.getElementById(id).value=language_id_old;} 
        }


        function removedivid(id) {

                var answer = confirm("<?php echo __('Are you sure you want to DELETE it?'); ?>")
            if (answer){

                jQuery('#'+id+'').remove();
            } 
        }

        function removeFormField(id) {
            jQuery(id).remove();

        }

        function change49(value){

        }
        </script>


    </div><br style="clear:both;">
    <?php $date_modified = date("Y-m-d H:i:s"); ?>
    <input type="hidden" name="date_modified" id="date_modified" value="<?php echo $date_modified; ?>" />
    <input type="hidden" name="item_id" id="item_id" value="<?php echo $item['id']; ?>" />
<?php
} //is isset item
else {
    ?>

    <div class="field">
        <label for="title" id="title"><b>*<?php echo __('Title'); ?></b></label>
    <?php //$title=item('Dublin Core', 'Title');   ?>
        <textarea rows="4" cols="70" class="textinput" name="title" /></textarea>
    <?php //echo form_error('title');   ?>
    </div> 

    <div class="field">
        <label for="title" id="title"><b>*<?php echo __('Description'); ?></b></label>
    <?php //$title=item('Dublin Core', 'Title');   ?>
        <textarea rows="4" cols="70" class="textinput" name="description" /></textarea>
    <?php //echo form_error('title');   ?>
    </div>

    <!--<div class="field">
                    <label for="title" id="title"><b>*Type</b></label>
    <?php //$title=item('Dublin Core', 'Title');   ?>
                    <select name="type">
            <option value="6">Photo</option>
            <option value="20">File</option>
            </select>
    <?php //echo form_error('title');   ?>
            
    </div> -->



<?php } ?>


<?php
/////////////////if translate not show file-url//////////
if (!isset($_POST['submit_language'])) {
    ?>
    <div id="stepbuttoncollection">
    <?php include('collection-form.php'); ?> 
    </div>
    <?php
    if (isset($item['id'])) {

        if (($item['item_type_id'] === 6 or $item['item_type_id'] === 20) and !(stripos($itemsource['value'], "europeana.eu/portal/") > 0 or $itemsource['value'] == 'Ariadne' or $itemsource['value'] == 'Natural_Europe_TUC' or stripos($itemsource['value'], "europeana.eu/api") > 0)) {
            ?>
            <div id="stepfile">
            <?php include('files-form.php'); ?> 
            </div>
        <?php } elseif ($item['item_type_id'] === 6 or $item['item_type_id'] === 20) { ?>

            <div id="stepurl">
                <?php
                $sql = "SELECT * FROM metadata_element_value WHERE record_id=" . $record_id . " and element_hierarchy=" . $metadataFile[metadata_schema_resources][element_hierarchy_location] . "";

                $exec5 = $db->query($sql);
                $data5 = $exec5->fetch();
                //echo $data5['value'];
                echo '<div style="float:left;"><label for=32 style="width:158px;">Url</label></div>';

                echo '<textarea rows="4" cols="60" class="textinput" name="32_1" id="32_1" readonly="readonly">' . $data5['value'] . '</textarea>&nbsp;&nbsp';
                ?>
            </div>

            <?php } else { ?>
            <div id="stepurl">
                <?php
                $sql = "SELECT * FROM metadata_element_value WHERE record_id=" . $record_id . " and element_hierarchy=" . $metadataFile[metadata_schema_resources][element_hierarchy_location] . "";

                $exec5 = $db->query($sql);
                $data5 = $exec5->fetch();
                //echo $data5['value'];
                echo '<div style="float:left;"><label for=32 style="width:158px;">Url</label></div>';

                echo '<textarea rows="4" cols="60" class="textinput" name="item_url" id="item_url">' . $data5['value'] . '</textarea>&nbsp;&nbsp';
                ?>
            </div>
            <?php
        }
    }//if isset item
    else {
        ?>
        <div id="stepfile">
        <?php include('files-form.php'); ?> 
        </div>
        <?php
    }
}//if translate not show file-url  if(!isset($_POST['submit_language'])){
//////clean php arrays for memory/////////////////////
unset($datalan);
unset($data4);
unset($general_pelements);
unset($label_description);
unset($date_modified);
unset($record);
unset($_POST);
unset($_GET);
?>
