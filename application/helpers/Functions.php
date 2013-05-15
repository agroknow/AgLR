<?php
/**
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2010
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka_ThemeHelpers
 * @subpackage GeneralHelpers
 */

/**
 * Retrieve the view object.  Should be used only to avoid function scope
 * issues within other theme helper functions.
 *
 * @since 0.10
 * @access private
 * @return Omeka_View
 */
function __v() {
    return Zend_Registry::get('view');
}

/**
 * Simple math for determining whether a number is odd or even
 *
 * @deprecated since 1.5
 * @return bool
 */
function is_odd($num) {
    return $num & 1;
}

/**
 * Wrapper for the auto_discovery_link_tags() helper.
 *
 * @since 0.9
 * @uses auto_discovery_link_tags()
 * @return string HTML
 * @deprecated since 1.4
 */
function auto_discovery_link_tag() {
    return auto_discovery_link_tags();
}

/**
 * Output a <link> tag for the RSS feed so the browser can auto-discover the field.
 *
 * @since 1.4
 * @uses items_output_uri()
 * @return string HTML
 */
function auto_discovery_link_tags() {
    $html = '<link rel="alternate" type="application/rss+xml" title="' . __('Omeka RSS Feed') . '" href="' . html_escape(items_output_uri()) . '" />';
    $html .= '<link rel="alternate" type="application/atom+xml" title="' . __('Omeka Atom Feed') . '" href="' . html_escape(items_output_uri('atom')) . '" />';
    return $html;
}

/**
 * Includes a file from the common/ directory, passing variables into that script.
 *
 * @param string $file Filename
 * @param array $vars A keyed array of variables to be extracted into the script
 * @param string $dir Defaults to 'common'
 * @return void
 */
function common($file, $vars = array(), $dir = 'common') {
    echo __v()->partial($dir . '/' . $file . '.php', $vars);
}

/**
 * Include the header script into the view
 *
 * @see common()
 * @param array Keyed array of variables
 * @param string $file Filename of header script (defaults to 'header')
 * @return void
 */
function head($vars = array(), $file = 'header') {
    common($file, $vars);
}

/**
 * Include the footer script into the view
 *
 * @param array Keyed array of variables
 * @param string $file Filename of footer script (defaults to 'footer')
 * @return void
 */
function foot($vars = array(), $file = 'footer') {
    common($file, $vars);
}

/**
 * Retrieve a flashed message from the controller
 *
 * @param boolean $wrap Whether or not to wrap the flashed message in a div
 * with an appropriate class ('success','error','alert')
 * @return string
 */
function flash($wrap = true) {
    $flash = new Omeka_Controller_Flash;

    switch ($flash->getStatus()) {
        case Omeka_Controller_Flash::SUCCESS:
            $wrapClass = 'success';
            break;
        case Omeka_Controller_Flash::VALIDATION_ERROR:
            $wrapClass = 'error';
            break;
        case Omeka_Controller_Flash::GENERAL_ERROR:
            $wrapClass = 'error';
            break;
        case Omeka_Controller_Flash::ALERT:
            $wrapClass = 'alert';
            break;
        default:
            return;
            break;
    }

    return $wrap ?
            '<div class="' . $wrapClass . '">' . nl2br(html_escape($flash->getMsg())) . '</div>' :
            $flash->getMsg();
}

/**
 * Retrieve the value of a particular site setting.  This can be used to display
 * any option that would be retrieved with get_option().
 *
 * Content for any specific option can be filtered by using a filter named
 * 'display_setting_(option)' where (option) is the name of the option, e.g.
 * 'display_setting_site_title'.
 *
 * @uses get_option()
 * @since 0.9
 * @return string
 */
function settings($name) {
    $name = apply_filters("display_setting_$name", get_option($name));
    $name = html_escape($name);
    return $name;
}

/**
 * Loops through a specific record set, setting the current record to a
 * globally accessible scope and returning it.  Records are only valid for
 * the current call to loop_records (i.e., the next call to loop_records()
 * will release the previously-returned item).
 *
 * @since 0.10
 * @see loop_items()
 * @see loop_files_for_item()
 * @see loop_collections()
 * @param string $recordType The type of record to loop through
 * @param mixed $records The iterable set of records
 * @param mixed $setCurrentRecordCallback The callback to set the current record
 * @return mixed The current record
 */
function loop_records($recordType, $records, $setCurrentRecordCallback = null) {
    if (!$records) {
        return false;
    }

    // If this is the first call to loop_records(), set a static record loop and
    // set it to NULL.
    static $recordLoop = null;

    // If this is the first call, set an array holding the last-returned
    // record from the loop, for each record type.  Initially set to null.
    static $lastRecord = null;

    // If the record type index does not exist, set it with the provided
    // records. We do this so multiple record types can coexist.
    if (!isset($recordLoop[$recordType])) {
        $recordLoop[$recordType] = $records;
    }

    // If there is a previously-returned record from this loop, release the
    // object before returning the next record.
    if ($lastRecord && array_key_exists($recordType, $lastRecord) && $lastRecord[$recordType]) {
        release_object($lastRecord[$recordType]);
        $lastRecord[$recordType] = null;
    }

    // If we haven't reached the end of the loop, set the current record in the
    // loop and return it. This advances the array cursor so the next loop
    // iteration will get the next record.
    if (list($key, $record) = each($recordLoop[$recordType])) {

        $lastRecord[$recordType] = $record;

        if (is_callable($setCurrentRecordCallback)) {
            call_user_func($setCurrentRecordCallback, $record);
        } else {
            throw new Exception(__('Error: Invalid callback was provided for the loop.'));
        }

        return $record;
    }

    // Reset the particular record loop if the loop has finished (so we can run
    // it again if necessary). Return false to indicate the end of the loop.
    unset($recordLoop[$recordType]);
    return false;
}

/**
 * Get all output formats available in the current action.
 *
 * @return array A sorted list of contexts.
 */
function current_action_contexts() {
    $actionName = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
    $contexts = Zend_Controller_Action_HelperBroker::getStaticHelper('contextSwitch')->getActionContexts($actionName);
    sort($contexts);
    return $contexts;
}

/**
 * Builds an HTML list containing all available output format contexts for the
 * current action.
 *
 * @param bool True = unordered list; False = use delimiter
 * @param string If the first argument is false, use this as a delimiter.
 * @return string HTML
 */
function output_format_list($list = true, $delimiter = ' | ') {
    $actionContexts = current_action_contexts();
    $html = '';

    // Do not display the list if there are no output formats available in the
    // current action.
    if (empty($actionContexts)) {
        return false;
    }

    // Unordered list format.
    if ($list) {
        $html .= '<ul id="output-format-list">';
        foreach ($actionContexts as $key => $actionContext) {
            $query = $_GET;
            $query['output'] = $actionContext;
            $html .= '<li><a href="' . html_escape(uri() . '?' . http_build_query($query)) . '">' . $actionContext . '</a></li>';
        }
        $html .= '</ul>';

        // Delimited format.
    } else {
        $html .= '<p id="output-format-list">';
        foreach ($actionContexts as $key => $actionContext) {
            $query = $_GET;
            $query['output'] = $actionContext;
            $html .= '<a href="' . html_escape(uri() . '?' . http_build_query($query)) . '">' . $actionContext . '</a>';
            $html .= (count($actionContexts) - 1) == $key ? '' : $delimiter;
        }
        $html .= '</p>';
    }

    return $html;
}

function browse_headings($headings) {
    $sortParam = Omeka_Db_Table::SORT_PARAM;
    $sortDirParam = Omeka_Db_Table::SORT_DIR_PARAM;
    $req = Zend_Controller_Front::getInstance()->getRequest();
    $currentSort = trim($req->getParam($sortParam));
    $currentDir = trim($req->getParam($sortDirParam));

    foreach ($headings as $label => $column) {
        if ($column) {
            $urlParams = $_GET;
            $urlParams[$sortParam] = $column;
            $class = '';
            if ($currentSort && $currentSort == $column) {
                if ($currentDir && $currentDir == 'd') {
                    $class = 'class="sorting desc"';
                    $urlParams[$sortDirParam] = 'a';
                } else {
                    $class = 'class="sorting asc"';
                    $urlParams[$sortDirParam] = 'd';
                }
            }
            $url = uri(array(), null, $urlParams);
            echo "<th $class scope=\"col\"><a href=\"$url\">$label</a></th>";
        } else {
            echo "<th scope=\"col\">$label</th>";
        }
    }
}

/**
 * Returns a <body> tag with attributes. Attributes
 * can be filtered using the 'body_tag_attributes' filter.
 *
 * @since 1.4
 * @uses _tag_attributes()
 * @return string An HTML <body> tag with attributes and their values.
 */
function body_tag($attributes = array()) {
    $attributes = apply_filters('body_tag_attributes', $attributes);
    if ($attributes = _tag_attributes($attributes)) {
        return "<body " . $attributes . ">\n";
    }
    return "<body>\n";
}

/////////////////////////////GKISTA for natural europe

function target($start = 1) {
    if (isset($_GET['nhm']) and $_GET['nhm'] == 'MNHN') {
        if ($start == 1) {
            $target = '?nhm=' . $_GET['nhm'];
        } else {
            $target = '&nhm=' . $_GET['nhm'];
        }
    } elseif (isset($_GET['nhm']) and $_GET['nhm'] == 'TNHM') {

        if ($start == 1) {
            $target = '?nhm=' . $_GET['nhm'];
        } else {
            $target = '&nhm=' . $_GET['nhm'];
        }
    } else {
        $target = '';
    }

    return $target;
}

function returntoexhibitfromitem($eidteaser) {
    require_once 'Omeka/Core.php';
    $core = new Omeka_Core;

    try {
        $db = $core->getDb();

        //Force the Zend_Db to make the connection and catch connection errors
        try {
            $mysqli = $db->getConnection()->getConnection();
        } catch (Exception $e) {
            throw new Exception("<h1>MySQL connection error: [" . mysqli_connect_errno() . "]</h1>" . "<p>" . $e->getMessage() . '</p>');
        }
    } catch (Exception $e) {
        die($e->getMessage() . '<p>Please refer to <a href="http://omeka.org/codex/">Omeka documentation</a> for help.</p>');
    }

    $maxIdSQL = "SELECT * FROM omeka_exhibits WHERE id='" . $eidteaser . "'";
//echo $maxIdSQL;break;
    $exec = $db->query($maxIdSQL);
    $result_multi = $exec->fetch();

    $output = "";
    $output.="<a href='" . uri('exhibits/show/') . "" . $result_multi['slug'] . "/to-begin-with" . target() . "' style='text-decoration:none;'>";
    $output.='<img src="' . uri('application/views/scripts/images/files-icons/arrow-left.png') . '" /> <span style="position:relative; top:-8px; left:5px;"> Return to Pathway</span>';
    $output.="</a><br><br>";
    return $output;
}

/////////////////////////////////////////////////////////////////	
/////////////////////////////////////////////////////////////////	
///////////////////////////////////lom functions////////////////
/////////////////////////////////////////////////////////////////	
/////////////////////////////////////////////////////////////////	

function createlomlabel($name, $extra = NULL, $min_occurs = NULL, $element_id = NULL) {

    if ($element_id > 0) {
        $label_description = return_label_description($element_id);
    } else {
        $label_description = '';
    }

    if (strlen($label_description) > 0) {
        if ($min_occurs > 0) {
            $label = "<label " . $extra . ">" . $name . "*  <img id='" . $element_id . "_tooltip' src='" . uri('themes/default/items/images/information.png') . "'></label>
			 
			<div id='" . $element_id . "_help' style='display:none; position:absolute;top:-100px;border:1px solid #333;background:#f7f5d1;padding:2px 5px;	color:#333;z-index:100;'>" . $label_description . "</div>";
        } else {
            $label = "<label " . $extra . ">" . $name . "  <img id='" . $element_id . "_tooltip' src='" . uri('themes/default/items/images/information.png') . "'></label>
			 
			<div id='" . $element_id . "_help' style='display:none; position:absolute;top:-100px;border:1px solid #333;background:#f7f5d1;padding:2px 5px;	color:#333;z-index:100;'>" . $label_description . "</div>";
        }
        $label.='<script type="text/javascript">
   var my_tooltip = new Tooltip(\'' . $element_id . '_tooltip\', \'' . $element_id . '_help\') </script>';
    } else {


        if ($min_occurs > 0) {
            $label = "<label " . $extra . ">" . $name . "* </label>";
        } else {
            $label = "<label " . $extra . ">" . $name . "</label>";
        }
    }
    return $label;
}

function createlomelement($type, $name, $value = NULL, $extra = NULL, $selectvalues = NULL, $selectvalueswhich = NULL, $selectalter = NULL, $langstringparams = NULL, $is_editable = NULL, $view_mode = NULL) {
    $readonly = '';
    $disabled = '';
    if ($is_editable === 0 or $view_mode === 1) {
        $readonly = 'readonly="readonly"';
        $disabled = 'disabled="disabled"';
    } else {
        $readonly = '';
        $disabled = '';
    }
    //echo $langstringparams.'123';
    if ($type == 'textarea') {
        $element = '<textarea  ' . $extra . ' ' . $readonly . '  name="' . $name . '">' . $value . '</textarea>';
    } elseif ($type == 'text') {
        $element = '<input type="text"  ' . $extra . ' ' . $readonly . '  name="' . $name . '" value="' . $value . '">';
    } elseif ($type == 'select') {
        $element = '<select ' . $extra . ' ' . $disabled . '  name="' . $name . '">';

        $element.='<option value="">'.__("Select").' </option>';

        $ar = 0;
        $size_of_objects = sizeof($selectvalues);
        for ($x = 0; $x < $size_of_objects; $x++) {
            $selectvaluesforlang = & $selectvalues[$x];
            if (strlen($selectvaluesforlang[$selectvalueswhich]) > 0) {
                $element.='<option value="' . $selectvaluesforlang[$selectvalueswhich] . '" ';
                if ($value === $selectvaluesforlang[$selectvalueswhich]) {
                    $element.= 'selected=selected';
                }
                $element.='>' . voc_multi_label($selectvaluesforlang[$selectvalueswhich]) . '</option>';
            }
            unset($selectvaluesforlang);
            unset($selectvalues[$x]);
        }
        $element.='</select>';
    } elseif ($type == 'selectxml') {
        $element = '<select ' . $extra . ' ' . $disabled . '  name="' . $name . '">';
        $element.='<option value="">Select </option>';

        $size_of_objects = sizeof($selectvalues);
        for ($x = 0; $x < $size_of_objects; $x++) {
            $selectvaluesforlang = & $selectvalues[$x];
            if (strlen($selectvaluesforlang[$selectvalueswhich]) > 0) {
                $element.='<option value="' . $selectvaluesforlang[$selectvalueswhich] . '" ';
                if ($value == $selectvaluesforlang[$selectvalueswhich]) {
                    $element.= 'selected=selected';
                }
                $element.='>' . $selectvaluesforlang[$selectalter] . '</option>';
            }
        }
        $element.='</select>';
        unset($selectvaluesforlang);
        unset($selectvalues);
    } elseif ($type == 'selectlanstr') {
        $lan = $value;
        $element = '<select ' . $extra . ' ' . $disabled . ' id="' . $name . '"  name="' . $name . '"  onchange="UpdateLangstringFormFieldExisted(' . $langstringparams['element_hierarchy'] . ',' . $langstringparams['record_id'] . ',' . $langstringparams['multi'] . ',\'' . $value . '\',this.value,\'' . $name . '\'); return false;">';
        $element.='<option value="none">Select </option>';
        //print_r($selectvalues);
        //echo $value;
        $size_of_objects = sizeof($selectvalues);
        for ($x = 0; $x < $size_of_objects; $x++) {
            $selectvaluesforlang = & $selectvalues[$x];

            $element.='<option value="' . $selectvaluesforlang['value'] . '" ';
            if ($value === $selectvaluesforlang['value']) {
                $element.= 'selected=selected';
            }
            $element.='>' . voc_multi_label($selectvaluesforlang[$selectvalueswhich]) . '</option>';
            unset($selectvaluesforlang);
            unset($selectvalues[$x]);
        }
        $element.='</select>';
    }
    return $element;
}

function lomradioform($data6, $dataform, $view_mode = NULL) {
    $db = Zend_Registry::get('db');
    $disable = '';
    if ($view_mode == 1) {
        $disable = 'disabled="disabled"';
    }
    $size_of_objects = sizeof($data6);
    for ($x = 0; $x < $size_of_objects; $x++) {
        $datarecord = & $data6[$x];
        if ($datarecord['element_hierarchy'] === $dataform['id']) {
            $datarecordvalue = $datarecord['value'];
            unset($datarecord);
            unset($data6[$x]);
        }
    }//select the value for more than one foreach
    $dataform['labal_name'] = return_multi_language_label_name($dataform['element_id']);
    $output = createlomlabel($dataform['labal_name'], 'for="theme"', $dataform['min_occurs'], $dataform['element_id']);
    $output.='<input type="radio" ' . $disable . ' name="' . $dataform['id'] . '_1_1" ';
    if ($datarecordvalue === 'yes') {
        $output.= 'checked=checked ';
    }
    $output.= 'value="yes"> Yes &nbsp;&nbsp;';
    $output.= '<input type="radio" ' . $disable . ' name="' . $dataform['id'] . '_1_1" ';
    if ($datarecordvalue === 'no') {
        $output.= 'checked=checked ';
    }
    $output.= 'value="no"> No ';

    if ($dataform['id'] === 23) {
        $output.= '<input type="radio" ' . $disable . ' name="' . $dataform['id'] . '_1_1" ';
        if ($datarecordvalue === 'Yes, if others share alike') {
            $output.= 'checked=checked ';
        }
        $output.= 'value="Yes, if others share alike"> Yes, if others share alike ';
    }

    $output.= '<br style="clear:both"><br>';

    return $output;
}

function lomontology($data6, $dataform, $extra, $parent_multi = NULL, $record = NULL, $view_mode = NULL, $xml_general) {
    $db = Zend_Registry::get('db');
    ?>
    <script language="javascript"> 
        function toggletree(iddivtree) {
            var ele = document.getElementById(iddivtree);
            if(ele.style.display == "block") {
                ele.style.display = "none";
            }
            else {
                ele.style.display = "block";
            }
        } 
    </script>
    <?php
    $output = '';

    if ($dataform['min_occurs'] > 0) {
        $output = '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; width:100%;"  id="' . $dataform['id'] . '" class="mandatory_element">';
    } elseif ($dataform['is_recommented'] == 1) {
        $output = '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; width:100%;"  id="' . $dataform['id'] . '" class="recommented_element">';
    } else {
        $output = '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; width:100%;"  id="' . $dataform['id'] . '" class="optional_element">';
    }

    //$output= '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; width:100%;" 
    //id="'.$dataform['id'].'">';
    //echo '<div id="'.$dataform['id'].'">';
    if ($parent_multi) {
        $madatory = 0;
    } else {
        $madatory = $dataform['min_occurs'];
    }
    $dataform['labal_name'] = return_multi_language_label_name($dataform['element_id']);
    $output.= '<div style="float:left;width:160px;">' . createlomlabel($dataform['labal_name'], 'for=' . $dataform['id'] . ' style="width:158px;"', $madatory, $dataform['element_id']) . '</div>';
    $output.= '<div style="float:left;width:610px;" id="' . $dataform['id'] . '_inputs">';
    $formcount = 0;

    if (isset($parent_multi) and $parent_multi > 0) {
        $sqltest = "SELECT * FROM metadata_element_value WHERE record_id='" . $record['id'] . "' and element_hierarchy='" . $dataform['id'] . "' and multi='" . $parent_multi . "' ORDER BY (case WHEN multi IS NULL THEN '9999' ELSE multi END) ASC";
        //echo $sqltest; //break;
        $exec5 = $db->query($sqltest);
        $data6 = $exec5->fetchAll();
        $exec5 = NULL;
    }
    $size_of_objects = sizeof($data6);
    for ($x = 0; $x < $size_of_objects; $x++) {
        $datarecord = & $data6[$x];
        if ($datarecord['element_hierarchy'] === $dataform['id']) { //select the value for more than one foreach
            $datarecordvalue = $datarecord['classification_id'];
            $formmulti = $datarecord['multi'];
            $multi = $datarecord['multi'];
            $datarecoreditable = $datarecord['is_editable'];
            $formcount+=1;
            $output.='<div id="' . $dataform['id'] . '_' . $formmulti . '_field">';
            if ($dataform['vocabulary_id'] > 0) {//select and isset vocabulary
                $xml = internal_xml('' . $dataform['id'] . '_' . $multi . '_tree', $xml_general, $_SESSION['get_language_for_internal_xml'], $dataform['vocabulary_id']);
                $output.= createlomelement('selectxml', '' . $dataform['id'] . '_' . $formmulti . '', $datarecordvalue, 'id="' . $dataform['id'] . '_' . $formmulti . '" style="width:300px;float:left;" ' . $extra . '', $xml['drop_down'], 'id', 'value', NULL, NULL, $view_mode);
                if ($view_mode != 1) {
                    $output.= '<a href="javascript:void(0)" onclick="toggletree(\'' . $dataform['id'] . '_' . $multi . '_tree\');" style="float:left;margin-left:2px;" id="' . $dataform['id'] . '_' . $multi . '">Browse</a>';
                    $output.=$xml['hierarchy_tree'];
                }

                if ($view_mode != 1) {
                    //if($dataform['max_occurs']>1){
                    $output.= '<a class="lom-remove" alt="Remove ' . $dataform['labal_name'] . '" title="Remove ' . $dataform['labal_name'] . '" href="#" onClick="removeFormFieldExisted(\'' . $dataform['id'] . '_' . $formmulti . '_field\',\'' . $dataform['id'] . '\',\'' . $datarecord['language_id'] . '\',\'' . $datarecord['record_id'] . '\',\'' . $datarecord['multi'] . '\'); return false;" 
                style="position:relative; left:5px; top:2px;float:left;">Remove</a>';
                    //}//maxoccurs>1
                } ///if view_mode not display
                $output.= '<br style="clear:both"><br>';
            } //select and isset vocabulary


            $output.= "</div>";
        }
        unset($datarecord);
        unset($data6[$x]);
    }//select the value for more than one foreach
    //an den uparxei eggrafh create one empty //////////////////////////////////////////////////////
    if ($formcount === 0 and $view_mode != 1) {
        $formmulti = 1;
        if ($parent_multi > 0) {
            $multi = $parent_multi;
        } else {
            $multi = 1;
        }
        $formcount+=1;
        if ($dataform['vocabulary_id'] > 0) {//select and isset vocabulary
            $xml = internal_xml('' . $dataform['id'] . '_' . $multi . '_tree', $xml_general, $_SESSION['get_language_for_internal_xml'], $dataform['vocabulary_id']);
            //foreach ($sortedxml as $sortedxml) { echo $sortedxml; }
            //print_r($sortedxml);
            //print_r($xml->term);
            $output.= createlomelement('selectxml', '' . $dataform['id'] . '_' . $multi . '', '', 'id="' . $dataform['id'] . '_' . $multi . '" style="width:300px;float:left;" ' . $extra . '', $xml['drop_down'], 'id', 'value', NULL, NULL, '' . $dataform['id'] . '');
            $output.= '<a href="javascript:void(0)" onclick="toggletree(\'' . $dataform['id'] . '_' . $multi . '_tree\');" style="float:left;margin-left:2px;" id="' . $dataform['id'] . '_' . $multi . '">Browse</a>';

            //$output.=internal_xml('' . $dataform['id'] . '_' . $multi . '_tree', '' . $datavocele['value'] . '', $_SESSION['get_language_for_internal_xml'], NULL);
            $output.=$xml['hierarchy_tree'];
            $output.= '<br style="clear:both"><br>';
        } //select and isset vocabulary
    }//end create one empty
    $output.= "</div>";


    if ($dataform['max_occurs'] > 1 and $view_mode != 1) {
        $output.='<input name="hdnLine_' . $dataform['id'] . '" id="hdnLine_' . $dataform['id'] . '" type="hidden" value="' . $formmulti . '">
        <div style="position:relative;clear:both;"><a alt="Add ' . $dataform['labal_name'] . '" title="Add ' . $dataform['labal_name'] . '" style="float:left;" class="lom-add-new" href="#" 
       onClick="addFormFieldSelectXmlOntology(\'' . $formmulti . '\',\'' . $dataform['id'] . '\',\'hdnLine_' . $dataform['id'] . '\',\'' . $dataform['vocabulary_id'] . '\'); return false;">Add ' . $dataform['labal_name'] . '</a></div>';
    } //end max occurs

    $output.= '</div>';
    $output.= '<br style="clear:both"><br>';

    return $output;
}

function lomselectform($data6, $dataform, $datalan, $extra, $parent_multi = NULL, $record = NULL, $view_mode = NULL) {
    $db = Zend_Registry::get('db');

    $output = '';
    if ($dataform['min_occurs'] > 0) {
        $output = '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; width:100%;"  id="' . $dataform['id'] . '" class="mandatory_element">';
    } elseif ($dataform['is_recommented'] == 1 or $parent_multi > 0) {
        $output = '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; width:100%;"  id="' . $dataform['id'] . '" class="recommented_element">';
    } else {
        $output = '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; width:100%;"  id="' . $dataform['id'] . '" class="optional_element">';
    }

    //$output= '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; width:100%;" 
    //id="'.$dataform['id'].'">';
    //echo '<div id="'.$dataform['id'].'">';
    if ($parent_multi) {
        $madatory = 0;
    } else {
        $madatory = $dataform['min_occurs'];
    }
    $dataform['labal_name'] = return_multi_language_label_name($dataform['element_id']);
    $output.= '<div style="float:left;width:160px;">' . createlomlabel($dataform['labal_name'], 'for=' . $dataform['id'] . ' style="width:158px;"', $madatory, $dataform['element_id']) . '</div>';
    $output.= '<div style="float:left;width:610px;" id="' . $dataform['id'] . '_inputs">';
    $formcount = 0;

    if (isset($parent_multi) and $parent_multi > 0) {
        $sqltest = "SELECT * FROM metadata_element_value WHERE record_id='" . $record['id'] . "' and element_hierarchy='" . $dataform['id'] . "' and multi='" . $parent_multi . "' ORDER BY (case WHEN multi IS NULL THEN '9999' ELSE multi END) ASC";
        //echo $sqltest; //break;
        $exec5 = $db->query($sqltest);
        $data6 = $exec5->fetchAll();
        $exec5 = NULL;
    }
    $size_of_objects = sizeof($data6);
    for ($x = 0; $x < $size_of_objects; $x++) {
        $datarecord = & $data6[$x];
        if ($datarecord['element_hierarchy'] === $dataform['id']) { //select the value for more than one foreach
            $datarecordvalue = $datarecord['vocabulary_record_id'];
            $formmulti = $datarecord['multi'];
            $multi = $datarecord['multi'];
            $datarecoreditable = $datarecord['is_editable'];
            $formcount+=1;
            $output.='<div id="' . $dataform['id'] . '_' . $formmulti . '_field">';
            if ($dataform['vocabulary_id'] > 0) {//select and isset vocabulary
                $execvocele = $db->query("SELECT f.label,e.sequence,e.id as vov_rec_id FROM metadata_vocabulary_record e JOIN
					metadata_vocabulary_value f ON f.vocabulary_rid = e.id WHERE e.vocabulary_id=" . $dataform['vocabulary_id'] . " and e.public=1  and f.language_id='" . get_language_for_switch() . "'  ORDER BY (case WHEN e.sequence IS NULL THEN '99999' END),e.sequence,f.label ASC");

                $datavocele = $execvocele->fetchAll();
                $output.= createlomelement('select', '' . $dataform['id'] . '_' . $formmulti . '', $datarecordvalue, 'style="width:300px;float:left;" ' . $extra . '', $datavocele, 'vov_rec_id', 'label', NULL, $datarecoreditable, $view_mode);



                //if($dataform['max_occurs']>1){
                if ($datarecoreditable === 0 or $view_mode == 1) {
                    
                } else {
                    $output.= '<a class="lom-remove" alt="Remove ' . $dataform['labal_name'] . '" title="Remove ' . $dataform['labal_name'] . '" href="#" onClick="removeFormFieldExisted(\'' . $dataform['id'] . '_' . $formmulti . '_field\',\'' . $dataform['id'] . '\',\'' . $datarecord['language_id'] . '\',\'' . $datarecord['record_id'] . '\',\'' . $datarecord['multi'] . '\'); return false;" 
                style="position:relative; left:5px; top:2px;float:left;">Remove</a>';
                    //}//maxoccurs>1
                }//if not editable
                $output.= '<br style="clear:both"><br>';
            } //select and isset vocabulary
            else {
                $output.= createlomelement('selectlanstr', '' . $dataform['id'] . '_' . $formcount . '', $datarecordvalue, 'style="width:300px;" ' . $extra . '', $datalan, 'vov_rec_id', 'label');
                $output.= '<br style="clear:both"><br>';
            }//end else select and isset vocabulary

            $output.= "</div>";
        }
        unset($datarecord);
        unset($data6[$x]);
    }//select the value for more than one foreach
    //an den uparxei eggrafh create one empty //////////////////////////////////////////////////////
    if ($formcount === 0 and $view_mode != 1) {
        $formmulti = 1;
        if ($parent_multi > 0) {
            $multi = $parent_multi;
        } else {
            $multi = 1;
        }
        $formcount+=1;
        if ($dataform['vocabulary_id'] > 0) {//select and isset vocabulary
            $execvocele = $db->query("SELECT f.label,e.sequence,e.id as vov_rec_id FROM metadata_vocabulary_record e JOIN
					metadata_vocabulary_value f ON f.vocabulary_rid = e.id WHERE e.vocabulary_id=" . $dataform['vocabulary_id'] . " and e.public=1  and f.language_id='" . get_language_for_switch() . "'  ORDER BY (case WHEN e.sequence IS NULL THEN '99999' END),e.sequence,f.label ASC");
            $datavocele = $execvocele->fetchAll();
            $output.= createlomelement('select', '' . $dataform['id'] . '_' . $multi . '', '', 'style="width:300px;" ' . $extra . '', $datavocele, 'vov_rec_id', 'label');
            $output.= '<br style="clear:both"><br>';
        } //select and isset vocabulary
        else {

            $output.= createlomelement('selectlanstr', '' . $dataform['id'] . '_' . $formcount . '', '', 'style="width:300px;" ' . $extra . '', $datalan, 'vov_rec_id', 'label');
            $output.= '<br style="clear:both"><br>';
        }//end else select and isset vocabulary
    }//end create one empty
    $output.= "</div>";


    if ($dataform['max_occurs'] > 1 and $view_mode != 1) {
        $output.='<input name="hdnLine_' . $dataform['id'] . '" id="hdnLine_' . $dataform['id'] . '" type="hidden" value="' . $formmulti . '">
        <div style="position:relative;clear:both;">
		<a href="#" alt="Add ' . $dataform['labal_name'] . '" title="Add ' . $dataform['labal_name'] . '" class="lom-add-new" style="float:left;"
       onClick="addFormFieldSelect(\'' . $formmulti . '\',\'' . $dataform['id'] . '\',\'hdnLine_' . $dataform['id'] . '\',\'' . $dataform['vocabulary_id'] . '\'); return false;">Add ' . $dataform['labal_name'] . '</a></div>';
    } //end max occurs

    $output.= '</div>';
    $output.= '<br style="clear:both"><br>';

    return $output;
}

function lomtextareaform($data6, $dataform, $datalan, $parent_multi = NULL, $record = NULL, $for_translation = NULL, $view_mode = NULL) {
    $db = Zend_Registry::get('db');

    if ($for_translation == 1) {
        $output = '';
        $output = '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; 
			width:100%;"  id="' . $dataform['id'] . '" class="recommented_element">';

        if ($parent_multi) {
            $madatory = 0;
        } else {
            $madatory = $dataform['min_occurs'];
        }
        $dataform['labal_name'] = return_multi_language_label_name($dataform['element_id']);
        $output.= '<div style="float:left;width:160px;">' . createlomlabel($dataform['labal_name'], 'for=' . $dataform['id'] . ' style="width:158px;"', $madatory, $dataform['element_id']) . '';

        $formcount = 0;
        $multi = 0;
        $formcounttotal = 0;

        $output.= "</div>";
        $output.= '<div style="float:left;width:610px;" id="' . $dataform['id'] . '_inputs">';
        $output.= '<div style="" id="' . $dataform['id'] . '_' . $multi . '_inputs">';

        if (isset($parent_multi) and $parent_multi > 0) {
            $sqltest = "SELECT * FROM metadata_element_value WHERE record_id='" . $record['id'] . "' and element_hierarchy='" . $dataform['id'] . "' and multi='" . $parent_multi . "' ORDER BY (case WHEN multi IS NULL THEN '9999' ELSE multi END) ASC";
            //echo $sqltest; //break;
            $exec5 = $db->query($sqltest);
            $data6 = $exec5->fetchAll();
            $exec5 = NULL;
        }



        $languagearray = array();
        $size_of_objects = sizeof($_POST['language_select']);
        for ($x = 0; $x < $size_of_objects; $x++) {
            $language_select = & $_POST['language_select'][$x];
            $languagearray[] = map_language_for_xerox2($language_select);
            unset($language_select);
            //unset($_POST['language_select'][$x]);
        }

        $multi_languagearray = array();
        foreach ($data6 as $data5) {
            if ($multi != $data5['multi']) {
                $multi_languagearray[] = $data5['multi'];
                $multi = $data5['multi'];
            }
        }
        //print_r($multi_languagearray);
// $languagearray_exist=  array(); 
// foreach ($data6 as $data5){
//    $languagearray_exist[]= $data5['language_id'];
// }


        $size_of_objects = sizeof($multi_languagearray);
        for ($x = 0; $x < $size_of_objects; $x++) {
            $multi_languagearray_for = & $multi_languagearray[$x];

            $sqltest23 = "SELECT * FROM metadata_element_value WHERE record_id='" . $record['id'] . "' and element_hierarchy='" . $dataform['id'] . "' and multi='" . $multi_languagearray_for . "' and language_id='en' ORDER BY (case WHEN multi IS NULL THEN '9999' ELSE multi END) ASC";
            //echo $sqltest; //break;
            $exec523 = $db->query($sqltest23);
            $data623 = $exec523->fetch();
            $exec523 = NULL;
            if ($data623) {
                $string_source = $data623['value'];
                $language_source = 'en-EN';
                $output.='<input type="hidden" name="fortranslationanalytics_'.$record['id'].'_'.$dataform['id'].'_'.$multi_languagearray_for.'" value="'.$string_source.'">';
                $output.='<input type="hidden" name="fortranslationanalyticslan_'.$record['id'].'_'.$dataform['id'].'_'.$multi_languagearray_for.'" value="'.$language_source.'">';
            } else {
                $sqltest234 = "SELECT * FROM metadata_element_value WHERE record_id='" . $record['id'] . "' and element_hierarchy='" . $dataform['id'] . "' and multi='" . $multi_languagearray_for . "' ORDER BY (case WHEN multi IS NULL THEN '9999' ELSE multi END) ASC";
                //echo $sqltest234; //break;
                $exec5234 = $db->query($sqltest234);
                $data6234 = $exec5234->fetch();
                $exec5234 = NULL;
                $string_source2 = $data6234['value'];
                $language_source2 = map_language_for_xerox2($data6234['language_id'], 1);
                $string_source = translatexerox('en-EN', $string_source2, $language_source2);
                $language_source = 'en-EN';
                $output.='<input type="hidden" name="fortranslationanalytics_'.$record['id'].'_'.$dataform['id'].'_'.$multi_languagearray_for.'" value="'.$string_source2.'">';
                $output.='<input type="hidden" name="fortranslationanalyticslan_'.$record['id'].'_'.$dataform['id'].'_'.$multi_languagearray_for.'" value="'.$language_source2.'">';
            }
            
            foreach ($languagearray as $languagearray_for) {
                $sqltest2 = "SELECT * FROM metadata_element_value WHERE record_id='" . $record['id'] . "' and element_hierarchy='" . $dataform['id'] . "' and multi='" . $multi_languagearray_for . "' and language_id='" . $languagearray_for . "' ORDER BY (case WHEN multi IS NULL THEN '9999' ELSE multi END) ASC";
                //echo $sqltest; //break;
                $exec52 = $db->query($sqltest2);
                $data62 = $exec52->fetchAll();
                $exec52 = NULL;

                if ($data62) {
                    //print_r($data62);
                    // echo '<br><br>';
                    foreach ($data62 as $datarecord) {

                        if ($datarecord['element_hierarchy'] === $dataform['id']) {
                            $datarecordvalue = $datarecord['value'];
                            $datarecordvaluelan = $datarecord['language_id'];
                            $datarecoreditable = $datarecord['is_editable'];


                            if ($multi != $datarecord['multi']) {

                                $output.='<input name="hdnLine_' . $dataform['id'] . '_' . $multi . '" id="hdnLine_' . $dataform['id'] . '_' . $multi . '" type="hidden" value="' . $formcount . '">';
                                $formcount = 0;
                                $output.= '</div><div style="" id="' . $dataform['id'] . '_' . $datarecord['multi'] . '_inputs">';
                                if ($datarecord['multi'] > 1) {
                                    $output.="<hr style='clear:both;'>";
                                }
                                $output.= '<br><br>';
                            }//if $multi!=$datarecord
                            $multi = $datarecord['multi']; //select the value for more than one foreach
                            $formcount+=1;
                            $formcounttotal+=1;
                            $output.='<div id="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_field" style="clear:both;">';
                            $output.= createlomelement('textarea', '' . $dataform['id'] . '_' . $multi . '_' . $formcount . '', '' . stripslashes($datarecordvalue) . '', 'rows="4" cols="60" class="textinput" id="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '" style="float:left;"', NULL, NULL, NULL, NULL, $datarecoreditable) . '&nbsp;&nbsp';
                            $output.='<div style="position:relative; left:5px; top:2px; float:left;"> ';
                            //if hierarchy type= langstring/////////////////////////////////////////////
                            if ($dataform['datatype_id'] === 1) {
                                $langstringparams = array('element_hierarchy' => $datarecord['element_hierarchy'], "record_id" => $datarecord['record_id'], "multi" => $datarecord['multi']);
                                $output.= createlomelement('selectlanstr', '' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_lan', $datarecordvaluelan, 'class="combo" 
					style="vertical-align:top;" disabled="disabled" ', $datalan, 'vov_rec_id', 'label', $langstringparams);

                                $output.='<input type="hidden" name="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_lan" value="' . $datarecordvaluelan . '">';
                            }//langstring
                            //$output.='<br>';           
//                            if ($dataform['datatype_id'] === 1) {
//                                $output.='<br><a alt="Remove Language" title="Remove Language" class="lom-remove" href="#" onClick="removeFormFieldExisted(\'' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_field\',\'' . $dataform['id'] . '\',\'' . $datarecord['language_id'] . '\',\'' . $datarecord['record_id'] . '\',\'' . $datarecord['multi'] . '\'); return false;" style="float:right;">Remove Language</a><br>';
//                            } else {
//                                $output.='<a alt="Remove ' . $dataform['labal_name'] . '" title="Remove ' . $dataform['labal_name'] . '" class="lom-remove" href="#" onClick="removeFormFieldExisted(\'' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_field\',\'' . $dataform['id'] . '\',\'' . $datarecord['language_id'] . '\',\'' . $datarecord['record_id'] . '\',\'' . $datarecord['multi'] . '\'); return false;" style="">Remove ' . $dataform['labal_name'] . '</a><br>';
//                            }
                            $output.= '</div>'; /////////////////div tou add remove
                            $output.= '</div>';
                        }
                    }//select the value for more than one foreach //if $datarecord['element_hierarchy']===$dataform['id']  an uparxei eggrafh
                }//if data62///////////////
                else { //else data62 an den uparxei eggrafh call xerox///////////////
                    $multi = $multi_languagearray_for; //select the value for more than one foreach
                    $formcount+=1;
                    $formcounttotal+=1;
                    $output.='<div id="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_field" style="clear:both;">';
                    $output.='This is a translation proposed by the system <br> ';
                    if ($languagearray_for == 'en') {
                        $translated_text=$string_source;
                        $output.= createlomelement('textarea', '' . $dataform['id'] . '_' . $multi . '_' . $formcount . '', '' . $translated_text . '', 'rows="4" cols="60" class="textinput" id="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '" style="float:left;background-color:#DDDAD3;"', NULL, NULL, NULL, NULL, $datarecoreditable) . '&nbsp;&nbsp';
                    } else {
                        $translated_text=translatexerox(map_language_for_xerox2($languagearray_for, 1), $string_source, $language_source);
                        $output.= createlomelement('textarea', '' . $dataform['id'] . '_' . $multi . '_' . $formcount . '', '' . $translated_text . '', 'rows="4" cols="60" class="textinput" id="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '" style="float:left;background-color:#FFF8E7;border:2px solid #A74C29;"', NULL, NULL, NULL, NULL, $datarecoreditable) . '&nbsp;&nbsp';
                    }
                    $output.='<input type="hidden" name="translatedanalytics_'.$record['id'].'_'.$dataform['id'].'_' . $multi . '_' . $formcount . '" value="'.$translated_text.'">';
                    $output.='<input type="hidden" name="translatedanalyticslan_'.$record['id'].'_'.$dataform['id'].'_' . $multi . '_' . $formcount . '" value="'.$languagearray_for.'">';

                    $output.='<div style="position:relative; left:5px; top:2px; float:left;"> ';
                    //if hierarchy type= langstring/////////////////////////////////////////////
                    if ($dataform['datatype_id'] === 1) {
                        $langstringparams = array('element_hierarchy' => $datarecord['element_hierarchy'], "record_id" => $datarecord['record_id'], "multi" => $datarecord['multi']);
                        $output.= createlomelement('selectlanstr', '' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_lan', $languagearray_for, 'class="combo" 
					style="vertical-align:top;" disabled="disabled" ', $datalan, 'vov_rec_id', 'label', $langstringparams);

                        $output.='<input type="hidden" name="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_lan" value="' . $languagearray_for . '">';
                    }//langstring
                    //$output.='<br>';           

                    $output.= '</div>'; /////////////////div tou add remove
                    $output.= '</div>';
                }
            }
        }

        $output.='<input name="hdnLine_' . $dataform['id'] . '_' . $multi . '" id="hdnLine_' . $dataform['id'] . '_' . $multi . '" type="hidden" value="' . $formcount . '">';
        $output.= '</div>';
        $output .= '</div>';


        $output .= '</div>';
    } else {  /////////////////if($for_translation==1){//////////////////////////////////////////
        $output = '';
        if ($dataform['min_occurs'] > 0) {
            $output = '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; 
			width:100%;"  id="' . $dataform['id'] . '" class="mandatory_element">';
        } elseif ($dataform['is_recommented'] == 1 or $parent_multi > 0) {
            $output = '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; 
			width:100%;"  id="' . $dataform['id'] . '" class="recommented_element">';
        } else {
            $output = '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; 
			width:100%;"  id="' . $dataform['id'] . '" class="optional_element">';
        }


        //$output= '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; 
        //width:100%;" id="'.$dataform['id'].'">';
        //echo '<div id="'.$dataform['id'].'">';
        if ($parent_multi) {
            $madatory = 0;
        } else {
            $madatory = $dataform['min_occurs'];
        }
        $dataform['labal_name'] = return_multi_language_label_name($dataform['element_id']);
        $output.= '<div style="float:left;width:160px;">' . createlomlabel($dataform['labal_name'], 'for=' . $dataform['id'] . ' style="width:158px;"', $madatory, $dataform['element_id']) . '';

        $formcount = 0;
        $multi = 0;
        $formcounttotal = 0;

        if ($dataform['max_occurs'] > 1 and $view_mode != 1) {
            $output.='<br><a class="lom-add-new" href="#" alt="Add ' . $dataform['labal_name'] . '" title="Add ' . $dataform['labal_name'] . '" style="float:left;" onClick="addFormTotalField(\'' . $formcount . '\',\'' . $dataform['id'] . '\',\'hdnLine_group_total_' . $dataform['id'] . '\',\'' . $dataform['labal_name'] . '\'); return false;">Add ' . $dataform['labal_name'] . '</a>';
        }
        $output.= "</div>";
        $output.= '<div style="float:left;width:610px;" id="' . $dataform['id'] . '_inputs">';
        $output.= '<div style="" id="' . $dataform['id'] . '_' . $multi . '_inputs">';

        if (isset($parent_multi) and $parent_multi > 0) {
            $sqltest = "SELECT * FROM metadata_element_value WHERE record_id='" . $record['id'] . "' and element_hierarchy='" . $dataform['id'] . "' and multi='" . $parent_multi . "' ORDER BY (case WHEN multi IS NULL THEN '9999' ELSE multi END) ASC";
            //echo $sqltest; //break;
            $exec5 = $db->query($sqltest);
            $data6 = $exec5->fetchAll();
            $exec5 = NULL;
        }

        $size_of_objects = sizeof($data6);
        for ($x = 0; $x < $size_of_objects; $x++) {
            $datarecord = & $data6[$x];
            if ($datarecord['element_hierarchy'] === $dataform['id']) {
                $datarecordvalue = $datarecord['value'];
                $datarecordvaluelan = $datarecord['language_id'];
                $datarecoreditable = $datarecord['is_editable'];
                if ($multi != $datarecord['multi']) {
                    $output.='<input name="hdnLine_' . $dataform['id'] . '_' . $multi . '" id="hdnLine_' . $dataform['id'] . '_' . $multi . '" type="hidden" value="' . $formcount . '">';
                    $formcount = 0;
                    $output.= '</div><div style="" id="' . $dataform['id'] . '_' . $datarecord['multi'] . '_inputs">';
                    if ($datarecord['multi'] > 1) {
                        $output.="<hr style='clear:both;'>";
                    }
                    //if hierarchy type= langstring
                    if ($dataform['datatype_id'] === 1 and $view_mode != 1) {
                        $output.='<a alt="Add Language" title="Add Language" class="lom-add-new" style="float:left;" href="#" onClick="addFormField(\'' . $formcount . '\',\'' . $dataform['id'] . '_' . $datarecord['multi'] . '\',\'hdnLine_' . $dataform['id'] . '_' . $datarecord['multi'] . '\'); return false;">Add Language</a>&nbsp;&nbsp;';
                    }
                    if ($dataform['max_occurs'] > 1 and $view_mode != 1) {
                        $output.='<a alt="Remove ' . $dataform['labal_name'] . '" title="Remove ' . $dataform['labal_name'] . '" class="lom-remove" href="#" onClick="removeFormFieldTotalExisted(\'' . $dataform['id'] . '_' . $datarecord['multi'] . '\',\'' . $dataform['id'] . '\',\'' . $datarecord['record_id'] . '\',\'' . $datarecord['multi'] . '\',\'1\'); return false;" style="">Remove ' . $dataform['labal_name'] . '</a>';
                    }
                    $output.= '<br><br>';
                }//if $multi!=$datarecord
                $multi = $datarecord['multi']; //select the value for more than one foreach
                $formcount+=1;
                $formcounttotal+=1;
                $output.='<div id="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_field" style="clear:both;">';
                $output.= createlomelement('textarea', '' . $dataform['id'] . '_' . $multi . '_' . $formcount . '', '' . stripslashes($datarecordvalue) . '', 'rows="4" cols="60" class="textinput" id="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '" style="float:left;"', NULL, NULL, NULL, NULL, $datarecoreditable, $view_mode) . '&nbsp;&nbsp';
                $output.='<div style="position:relative; left:5px; top:2px; float:left;"> ';
                //if hierarchy type= langstring/////////////////////////////////////////////
                if ($dataform['datatype_id'] === 1) {
                    $langstringparams = array('element_hierarchy' => $datarecord['element_hierarchy'], "record_id" => $datarecord['record_id'], "multi" => $datarecord['multi']);
                    $output.= createlomelement('selectlanstr', '' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_lan', $datarecordvaluelan, 'class="combo" 
					style="vertical-align:top;"', $datalan, 'vov_rec_id', 'label', $langstringparams, NULL, $view_mode);
                }//langstring
                //$output.='<br>';  
                if ($view_mode != 1) {
                    if ($dataform['datatype_id'] === 1) {
                        $output.='<br><a alt="Remove Language" title="Remove Language" class="lom-remove" href="#" onClick="removeFormFieldExisted(\'' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_field\',\'' . $dataform['id'] . '\',\'' . $datarecord['language_id'] . '\',\'' . $datarecord['record_id'] . '\',\'' . $datarecord['multi'] . '\'); return false;" style="float:right;">Remove Language</a><br>';
                    } else {
                        $output.='<a alt="Remove ' . $dataform['labal_name'] . '" title="Remove ' . $dataform['labal_name'] . '" class="lom-remove" href="#" onClick="removeFormFieldExisted(\'' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_field\',\'' . $dataform['id'] . '\',\'' . $datarecord['language_id'] . '\',\'' . $datarecord['record_id'] . '\',\'' . $datarecord['multi'] . '\'); return false;" style="">Remove ' . $dataform['labal_name'] . '</a><br>';
                    }
                }// if not view_mode
                $output.= '</div>'; /////////////////div tou add remove
                $output.= '</div>';
            }
            unset($datarecord);
            unset($data6[$x]);
        }//select the value for more than one foreach //if $datarecord['element_hierarchy']===$dataform['id']  an uparxei eggrafh
        $output.='<input name="hdnLine_' . $dataform['id'] . '_' . $multi . '" id="hdnLine_' . $dataform['id'] . '_' . $multi . '" type="hidden" value="' . $formcount . '">';
        $output.= '</div>';




        //an den uparxei eggrafh create one empty
        if ($formcount === 0 and $view_mode != 1) {
            $formcount+=1;
            if ($parent_multi > 0) {
                $multi = $parent_multi;
            } else {
                $multi = 1;
            }
            $output.= '<div style="" id="' . $dataform['id'] . '_' . $multi . '_inputs">';
            $output.='<input name="hdnLine_' . $dataform['id'] . '_' . $multi . '" id="hdnLine_' . $dataform['id'] . '_' . $multi . '" type="hidden" value="' . $formcount . '">';
            //$output.= "<hr style='clear:both;'>";
            if ($dataform['datatype_id'] === 1) {//if hierarchy type= langstring
                $output.='<a class="lom-add-new" href="#" onClick="addFormField(\'' . $formcount . '\',\'' . $dataform['id'] . '_' . $multi . '\',\'hdnLine_' . $dataform['id'] . '_' . $multi . '\'); return false;">Add Language</a>';
                $output.= '<br><br>';
            }
            $output.='<div id="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_field">';
            $output.= createlomelement('textarea', '' . $dataform['id'] . '_' . $multi . '_' . $formcount . '', '', 'rows="4" cols="60" 
					class="textinput" id="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '"') . '&nbsp;&nbsp';
            //if hierarchy type= langstring
            if ($dataform['datatype_id'] === 1) {
                $output.= createlomelement('selectlanstr', '' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_lan', '', 'class="combo" 
					style="vertical-align:top;"', $datalan, 'vov_rec_id', 'label');
            }//langstring
            $output.= '<br>';
            $output.= "</div>";
            $output.= "</div>";
        }//end create one empty
        $output.= "</div>";
        $output.= '</div>';
        $output.= '<br style="clear:both"><br>';
        $output.= '<input name="hdnLine_group_total_' . $dataform['id'] . '" id="hdnLine_group_total_' . $dataform['id'] . '" type="hidden" value="' . $multi . '">';
    }///if($for_translation==1){

    return $output;
}

function lomtextformdate($data6, $dataform, $datalan, $parent_multi = NULL, $record = NULL, $view_mode = NULL) {
    $db = Zend_Registry::get('db');

    $output = '';
    if ($dataform['min_occurs'] > 0) {
        $output = '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; 
			width:100%;"  id="' . $dataform['id'] . '" class="mandatory_element">';
    } elseif ($dataform['is_recommented'] == 1) {
        $output = '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; 
			width:100%;"  id="' . $dataform['id'] . '" class="recommented_element">';
    } else {
        $output = '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; 
			width:100%;"  id="' . $dataform['id'] . '" class="optional_element">';
    }


    //$output= '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; 
    //width:100%;" id="'.$dataform['id'].'">';
    //echo '<div id="'.$dataform['id'].'">';
    if ($parent_multi) {
        $madatory = 0;
    } else {
        $madatory = $dataform['min_occurs'];
    }
    $dataform['labal_name'] = return_multi_language_label_name($dataform['element_id']);
    $output.= '<div style="float:left;width:160px;">' . createlomlabel($dataform['labal_name'], 'for=' . $dataform['id'] . ' style="width:158px;"', $madatory, $dataform['element_id']) . '';

    $formcount = 0;
    $multi = 0;
    $formcounttotal = 0;

    if ($dataform['max_occurs'] > 1 and $view_mode != 1) {
        if ($dataform['datatype_id'] === 1) {
            $output.='<br><a class="lom-add-new" href="#" style="float:left;" onClick="addFormTotalFieldText(\'' . $formcount . '\',\'' . $dataform['id'] . '\',\'hdnLine_group_total_' . $dataform['id'] . '\',\'' . $dataform['labal_name'] . '\'); return false;">Add ' . $dataform['labal_name'] . '</a>';
        } else {
            $output.='<br><a class="lom-add-new" href="#" style="float:left;" onClick="addFormTotalFieldTextnolan(\'' . $formcount . '\',\'' . $dataform['id'] . '\',\'hdnLine_group_total_' . $dataform['id'] . '\',\'' . $dataform['labal_name'] . '\'); return false;">Add ' . $dataform['labal_name'] . '</a>';
        }
    }
    $output.= "</div>";
    $output.= '<div style="float:left;width:610px;" id="' . $dataform['id'] . '_inputs">';
    $output.= '<div style="" id="' . $dataform['id'] . '_' . $multi . '_inputs">';

    if (isset($parent_multi) and $parent_multi > 0) {
        $sqltest = "SELECT * FROM metadata_element_value WHERE record_id='" . $record['id'] . "' and element_hierarchy='" . $dataform['id'] . "' and multi='" . $parent_multi . "' ORDER BY (case WHEN multi IS NULL THEN '9999' ELSE multi END) ASC";
        //echo $sqltest; //break;
        $exec5 = $db->query($sqltest);
        $data6 = $exec5->fetchAll();
        $exec5 = NULL;
    }
    $size_of_objects = sizeof($data6);
    for ($x = 0; $x < $size_of_objects; $x++) {
        $datarecord = & $data6[$x];
        if ($datarecord['element_hierarchy'] === $dataform['id']) {
            $datarecordvalue = $datarecord['value'];
            $datarecordvaluelan = $datarecord['language_id'];
            $datarecoreditable = $datarecord['is_editable'];
            if ($multi != $datarecord['multi']) {
                $output.='<input name="hdnLine_' . $dataform['id'] . '_' . $multi . '" id="hdnLine_' . $dataform['id'] . '_' . $multi . '" type="hidden" value="' . $formcount . '">';
                $formcount = 0;
                $output.= '</div><div style="" id="' . $dataform['id'] . '_' . $datarecord['multi'] . '_inputs">';
                //$output.="<hr style='clear:both;'>";
                //if hierarchy type= langstring
                if ($dataform['datatype_id'] === 1 and $view_mode != 1) {
                    $output.='<a class="lom-add-new" href="#" onClick="addFormFieldText(\'' . $formcount . '\',\'' . $dataform['id'] . '_' . $datarecord['multi'] . '\',\'hdnLine_' . $dataform['id'] . '_' . $datarecord['multi'] . '\'); return false;">Add Language</a>&nbsp;&nbsp;';
                }
                if ($dataform['max_occurs'] > 1 and $view_mode != 1) {
                    if ($datarecoreditable === 0 or $view_mode == 1) {
                        
                    } else {
                        $output.='<a class="lom-remove" href="#" onClick="removeFormFieldTotalExisted(\'' . $dataform['id'] . '_' . $datarecord['multi'] . '\',\'' . $dataform['id'] . '\',\'' . $datarecord['record_id'] . '\',\'' . $datarecord['multi'] . '\',\'1\'); return false;" style="">Remove ' . $dataform['labal_name'] . '</a>';
                    }//not editable 
                }
                //$output.= '<br><br>';						
            }//if $multi!=$datarecord
            $multi = $datarecord['multi']; //select the value for more than one foreach
            $formcount+=1;
            $formcounttotal+=1;
            $output.='<div id="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_field" style="margin-top:0px;">';
            if ($datarecord['is_editable'] === 0 and $view_mode == 1) {
                
            } else {
                ?>
                <script>
                    jQuery(function() {
                        jQuery( "#<?php echo '' . $dataform['id'] . '_' . $multi . '_' . $formcount . ''; ?>" ).datepicker({ dateFormat: 'yy-mm-dd' });
                    });
                </script>	 <?php
            }//not editable
            $output.= createlomelement('text', '' . $dataform['id'] . '_' . $multi . '_' . $formcount . '', '' . stripslashes($datarecordvalue) . '', 'class="textinput" 
					id="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '" style="float:left;width:200px;"', NULL, NULL, NULL, NULL, $datarecoreditable, $view_mode) . '&nbsp;&nbsp';
            $output.='<div style="position:relative; left:5px; top:0px; float:left;"> ';
            //if hierarchy type= langstring/////////////////////////////////////////////
            if ($dataform['datatype_id'] === 1) {
                $langstringparams = array('element_hierarchy' => $datarecord['element_hierarchy'], "record_id" => $datarecord['record_id'], "multi" => $datarecord['multi']);
                $output.= createlomelement('selectlanstr', '' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_lan', $datarecordvaluelan, 'class="combo" 
					style="vertical-align:top;"', $datalan, 'vov_rec_id', 'label', $langstringparams, NULL, $view_mode);
            }//langstring
            if ($dataform['datatype_id'] === 1 and $view_mode != 1) {
                $output.='<a class="lom-remove" href="#" onClick="removeFormFieldExisted(\'' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_field\',\'' . $dataform['id'] . '\',\'' . $datarecord['language_id'] . '\',\'' . $datarecord['record_id'] . '\',\'' . $datarecord['multi'] . '\'); return false;" style="">Remove Language</a><br>';
            } else {
                if ($datarecord['is_editable'] != 0 and $view_mode != 1) {
                    $output.='<a class="lom-remove" href="#" onClick="removeFormFieldExisted(\'' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_field\',\'' . $dataform['id'] . '\',\'' . $datarecord['language_id'] . '\',\'' . $datarecord['record_id'] . '\',\'' . $datarecord['multi'] . '\'); return false;" style="float:right;">Remove</a><br>';
                }//is editable
            }
            $output.= '</div>'; /////////////////div tou add remove
            $output.= '</div>';
        }
        unset($datarecord);
        unset($data6[$x]);
    }//select the value for more than one foreach //if $datarecord['element_hierarchy']===$dataform['id']  an uparxei eggrafh
    $output.='<input name="hdnLine_' . $dataform['id'] . '_' . $multi . '" id="hdnLine_' . $dataform['id'] . '_' . $multi . '" type="hidden" value="' . $formcount . '">';
    $output.= '</div>';




    //an den uparxei eggrafh create one empty
    if ($formcount === 0 and $view_mode != 1) {
        $formcount+=1;
        if ($parent_multi > 0) {
            $multi = $parent_multi;
        } else {
            $multi = 1;
        }
        $output.= '<div style="" id="' . $dataform['id'] . '_' . $multi . '_inputs">';
        $output.='<input name="hdnLine_' . $dataform['id'] . '_' . $multi . '" id="hdnLine_' . $dataform['id'] . '_' . $multi . '" type="hidden" value="' . $formcount . '">';
        //$output.= "<hr style='clear:both;'>";
        if ($dataform['datatype_id'] === 1) {//if hierarchy type= langstring
            $output.='<a class="lom-add-new" href="#" onClick="addFormFieldText(\'' . $formcount . '\',\'' . $dataform['id'] . '_' . $multi . '\',\'hdnLine_' . $dataform['id'] . '_' . $multi . '\'); return false;">Add Language</a>';
            $output.= '<br><br>';
        }
        $output.='<div id="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_field">';
        ?>
        <script>
            jQuery(function() {
                jQuery( "#<?php echo '' . $dataform['id'] . '_' . $multi . '_' . $formcount . ''; ?>" ).datepicker({ dateFormat: 'yy-mm-dd' });
            });
        </script>	 <?php
        $output.= createlomelement('text', '' . $dataform['id'] . '_' . $multi . '_' . $formcount . '', '', 'class="textinput" style="width:200px;" 
					id="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '"') . '&nbsp;&nbsp';
        //if hierarchy type= langstring
        if ($dataform['datatype_id'] === 1) {
            $output.= createlomelement('selectlanstr', '' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_lan', '', 'class="combo" 
					style="vertical-align:top;"', $datalan, 'vov_rec_id', 'label');
        }//langstring
        $output.= '<br>';
        $output.= "</div>";
        $output.= "</div>";
    }//end create one empty
    $output.= "</div>";
    $output.= '</div>';
    $output.= '<br style="clear:both"><br>';
    $output.= '<input name="hdnLine_group_total_' . $dataform['id'] . '" id="hdnLine_group_total_' . $dataform['id'] . '" type="hidden" value="' . $multi . '">';
    return $output;
}

function lomtextform($data6, $dataform, $datalan, $parent_multi = NULL, $record = NULL, $for_translation = NULL, $view_mode) {
    $db = Zend_Registry::get('db');

    if ($for_translation == 1) {
        $output = '';
        $output = '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; 
			width:100%;"  id="' . $dataform['id'] . '" class="recommented_element">';

        if ($parent_multi) {
            $madatory = 0;
        } else {
            $madatory = $dataform['min_occurs'];
        }
        $dataform['labal_name'] = return_multi_language_label_name($dataform['element_id']);
        $output.= '<div style="float:left;width:160px;">' . createlomlabel($dataform['labal_name'], 'for=' . $dataform['id'] . ' style="width:158px;"', $madatory, $dataform['element_id']) . '';

        $formcount = 0;
        $multi = 0;
        $formcounttotal = 0;

        $output.= "</div>";
        $output.= '<div style="float:left;width:610px;" id="' . $dataform['id'] . '_inputs">';
        $output.= '<div style="" id="' . $dataform['id'] . '_' . $multi . '_inputs">';

        if (isset($parent_multi) and $parent_multi > 0) {
            $sqltest = "SELECT * FROM metadata_element_value WHERE record_id='" . $record['id'] . "' and element_hierarchy='" . $dataform['id'] . "' and multi='" . $parent_multi . "' ORDER BY (case WHEN multi IS NULL THEN '9999' ELSE multi END) ASC";
            //echo $sqltest; //break;
            $exec5 = $db->query($sqltest);
            $data6 = $exec5->fetchAll();
            $exec5 = NULL;
        }



        $languagearray = array();
        foreach ($_POST['language_select'] as $language_select) {
            $languagearray[] = map_language_for_xerox2($language_select);
        }

        $multi_languagearray = array();
        foreach ($data6 as $data5) {
            if ($multi != $data5['multi']) {
                $multi_languagearray[] = $data5['multi'];
                $multi = $data5['multi'];
            }
        }
        //print_r($multi_languagearray);
// $languagearray_exist=  array(); 
// foreach ($data6 as $data5){
//    $languagearray_exist[]= $data5['language_id'];
// }


        foreach ($multi_languagearray as $multi_languagearray_for) {

            $sqltest23 = "SELECT * FROM metadata_element_value WHERE record_id='" . $record['id'] . "' and element_hierarchy='" . $dataform['id'] . "' and multi='" . $multi_languagearray_for . "' and language_id='en' ORDER BY (case WHEN multi IS NULL THEN '9999' ELSE multi END) ASC";
            //echo $sqltest; //break;
            $exec523 = $db->query($sqltest23);
            $data623 = $exec523->fetch();
            $exec523 = NULL;
            if ($data623) {
                $string_source = $data623['value'];
                $language_source = 'en-EN';
                $output.='<input type="hidden" name="fortranslationanalytics_'.$record['id'].'_'.$dataform['id'].'_'.$multi_languagearray_for.'" value="'.$string_source.'">';
                $output.='<input type="hidden" name="fortranslationanalyticslan_'.$record['id'].'_'.$dataform['id'].'_'.$multi_languagearray_for.'" value="'.$language_source.'">';

            } else {
                $sqltest234 = "SELECT * FROM metadata_element_value WHERE record_id='" . $record['id'] . "' and element_hierarchy='" . $dataform['id'] . "' and multi='" . $multi_languagearray_for . "' ORDER BY (case WHEN multi IS NULL THEN '9999' ELSE multi END) ASC";
                //echo $sqltest234; //break;
                $exec5234 = $db->query($sqltest234);
                $data6234 = $exec5234->fetch();
                $exec5234 = NULL;
                $string_source2 = $data6234['value'];
                $language_source2 = map_language_for_xerox2($data6234['language_id'], 1);
                $string_source = translatexerox('en-EN', $string_source2, $language_source2);
                $language_source = 'en-EN';
                $output.='<input type="hidden" name="fortranslationanalytics_'.$record['id'].'_'.$dataform['id'].'_'.$multi_languagearray_for.'" value="'.$string_source2.'">';
                $output.='<input type="hidden" name="fortranslationanalyticslan_'.$record['id'].'_'.$dataform['id'].'_'.$multi_languagearray_for.'" value="'.$language_source2.'">';
            }


            foreach ($languagearray as $languagearray_for) {
                $sqltest2 = "SELECT * FROM metadata_element_value WHERE record_id='" . $record['id'] . "' and element_hierarchy='" . $dataform['id'] . "' and multi='" . $multi_languagearray_for . "' and language_id='" . $languagearray_for . "' ORDER BY (case WHEN multi IS NULL THEN '9999' ELSE multi END) ASC";
                //echo $sqltest; //break;
                $exec52 = $db->query($sqltest2);
                $data62 = $exec52->fetchAll();
                $exec52 = NULL;

                if ($data62) {
                    // print_r($data62);
                    // echo '<br><br>';
                    foreach ($data62 as $datarecord) {

                        if ($datarecord['element_hierarchy'] === $dataform['id']) {
                            $datarecordvalue = $datarecord['value'];
                            $datarecordvaluelan = $datarecord['language_id'];
                            $datarecoreditable = $datarecord['is_editable'];


                            if ($multi != $datarecord['multi']) {

                                $output.='<input name="hdnLine_' . $dataform['id'] . '_' . $multi . '" id="hdnLine_' . $dataform['id'] . '_' . $multi . '" type="hidden" value="' . $formcount . '">';
                                $formcount = 0;
                                $output.= '</div><div style="" id="' . $dataform['id'] . '_' . $datarecord['multi'] . '_inputs">';
                                if ($datarecord['multi'] > 1) {
                                    $output.="<hr style='clear:both;position:relative;top:7px;'>";
                                }
                                $output.= '<br>';
                            }//if $multi!=$datarecord
                            $multi = $datarecord['multi']; //select the value for more than one foreach
                            $formcount+=1;
                            $formcounttotal+=1;
                            $output.='<div id="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_field" style="clear:both;position:relative;padding-top:2px;">';
                            //$output.= createlomelement('text', '' . $dataform['id'] . '_' . $multi . '_' . $formcount . '', '' . stripslashes($datarecordvalue) . '', 'rows="4" cols="60" class="textinput" id="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '" style="float:left;"', NULL, NULL, NULL, NULL, $datarecoreditable) . '&nbsp;&nbsp';
                            $output.= createlomelement('text', '' . $dataform['id'] . '_' . $multi . '_' . $formcount . '', '' . stripslashes($datarecordvalue) . '', 'class="textinput" id="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '" style="float:left;width:200px;"', NULL, NULL, NULL, NULL, $datarecoreditable) . '&nbsp;&nbsp';

                            $output.='<div style="position:relative; left:5px; top:2px; float:left;"> ';
                            //if hierarchy type= langstring/////////////////////////////////////////////
                            if ($dataform['datatype_id'] === 1) {
                                $langstringparams = array('element_hierarchy' => $datarecord['element_hierarchy'], "record_id" => $datarecord['record_id'], "multi" => $datarecord['multi']);
                                $output.= createlomelement('selectlanstr', '' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_lan', $datarecordvaluelan, 'class="combo" 
					style="vertical-align:top;" disabled="disabled" ', $datalan, 'vov_rec_id', 'label', $langstringparams);

                                $output.='<input type="hidden" name="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_lan" value="' . $datarecordvaluelan . '">';
                            }//langstring
                            //$output.='<br>';           
//                            if ($dataform['datatype_id'] === 1) {
//                                $output.='<a alt="Remove Language" title="Remove Language" class="lom-remove" href="#" onClick="removeFormFieldExisted(\'' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_field\',\'' . $dataform['id'] . '\',\'' . $datarecord['language_id'] . '\',\'' . $datarecord['record_id'] . '\',\'' . $datarecord['multi'] . '\'); return false;" style="float:right;">Remove Language</a><br>';
//                            } else {
//                                $output.='<a alt="Remove ' . $dataform['labal_name'] . '" title="Remove ' . $dataform['labal_name'] . '" class="lom-remove" href="#" onClick="removeFormFieldExisted(\'' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_field\',\'' . $dataform['id'] . '\',\'' . $datarecord['language_id'] . '\',\'' . $datarecord['record_id'] . '\',\'' . $datarecord['multi'] . '\'); return false;" style="">Remove ' . $dataform['labal_name'] . '</a><br>';
//                            }
                            $output.= '</div>'; /////////////////div tou add remove
                            $output.= '</div>';
                        }
                    }//select the value for more than one foreach //if $datarecord['element_hierarchy']===$dataform['id']  an uparxei eggrafh
                }//if data62///////////////
                else { //else data62 an den uparxei eggrafh call xerox///////////////
                    $multi = $multi_languagearray_for; //select the value for more than one foreach
                    $formcount+=1;
                    $formcounttotal+=1;
                    $output.='<div id="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_field" style="clear:both;position:relative;padding-top:2px;">';
                    //$output.= createlomelement('text', '' . $dataform['id'] . '_' . $multi . '_' . $formcount . '', '' . translatexerox(map_language_for_xerox2($languagearray_for,1), $string_in_english) . '', 'rows="4" cols="60" class="textinput" id="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '" style="float:left;"', NULL, NULL, NULL, NULL, $datarecoreditable) . '&nbsp;&nbsp';
                    $output.='This is a translation proposed by the system <br> ';
                    if ($languagearray_for == 'en') {
                        $translated_text=$string_source;
                        $output.= createlomelement('text', '' . $dataform['id'] . '_' . $multi . '_' . $formcount . '', '' . $translated_text . '', 'class="textinput" id="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '" style="float:left;width:200px; background-color:#DDDAD3;"', NULL, NULL, NULL, NULL, $datarecoreditable) . '&nbsp;&nbsp';
                    } else {
                        $translated_text=translatexerox(map_language_for_xerox2($languagearray_for, 1), $string_source, $language_source);
                        $output.= createlomelement('text', '' . $dataform['id'] . '_' . $multi . '_' . $formcount . '', '' . $translated_text . '', 'class="textinput" id="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '" style="float:left;width:200px; background-color:#FFF8E7;border:2px solid #A74C29;"', NULL, NULL, NULL, NULL, $datarecoreditable) . '&nbsp;&nbsp';
                    }
                    $output.='<input type="hidden" name="translatedanalytics_'.$record['id'].'_'.$dataform['id'].'_' . $multi . '_' . $formcount . '" value="'.$translated_text.'">';
                    $output.='<input type="hidden" name="translatedanalyticslan_'.$record['id'].'_'.$dataform['id'].'_' . $multi . '_' . $formcount . '" value="'.$languagearray_for.'">';

                    $output.='<div style="position:relative; left:5px; top:2px; float:left;"> ';
                    //if hierarchy type= langstring/////////////////////////////////////////////
                    if ($dataform['datatype_id'] === 1) {
                        $langstringparams = array('element_hierarchy' => $datarecord['element_hierarchy'], "record_id" => $datarecord['record_id'], "multi" => $datarecord['multi']);
                        $output.= createlomelement('selectlanstr', '' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_lan', $languagearray_for, 'class="combo" 
					style="vertical-align:top;" disabled="disabled" ', $datalan, 'vov_rec_id', 'label', $langstringparams);

                        $output.='<input type="hidden" name="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_lan" value="' . $languagearray_for . '">';
                    }//langstring
                    //$output.='<br>';           

                    $output.= '</div>'; /////////////////div tou add remove
                    $output.= '</div>';
                }
            }
        }

        $output.='<input name="hdnLine_' . $dataform['id'] . '_' . $multi . '" id="hdnLine_' . $dataform['id'] . '_' . $multi . '" type="hidden" value="' . $formcount . '">';
        $output.= '</div>';
        $output .= '</div>';


        $output .= '</div>';
    } else {  /////////////////if($for_translation==1){//////////////////////////////////////////
        $output = '';

        if ($dataform['min_occurs'] > 0) {
            $output = '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; 
			width:100%;"  id="' . $dataform['id'] . '" class="mandatory_element">';
        } elseif ($dataform['is_recommented'] == 1 or $parent_multi > 0) {
            $output = '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; 
			width:100%;"  id="' . $dataform['id'] . '" class="recommented_element">';
        } else {
            $output = '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; 
			width:100%;"  id="' . $dataform['id'] . '" class="optional_element">';
        }


        //$output= '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; 
        //width:100%;" id="'.$dataform['id'].'">';
        //echo '<div id="'.$dataform['id'].'">';
        if ($parent_multi) {
            $madatory = 0;
        } else {
            $madatory = $dataform['min_occurs'];
        }
        $dataform['labal_name'] = return_multi_language_label_name($dataform['element_id']);
        $output.= '<div style="float:left;min-width:160px;">' . createlomlabel($dataform['labal_name'], 'for=' . $dataform['id'] . ' style="min-width:158px;"', $madatory, $dataform['element_id']) . '';
        $formcount = 0;
        $multi = 0;
        $formcounttotal = 0;

        if ($dataform['max_occurs'] > 1 and $view_mode != 1) {
            if ($dataform['datatype_id'] === 1) {
                $output.='<br><a class="lom-add-new" href="#" style="float:left;" onClick="addFormTotalFieldText(\'' . $formcount . '\',\'' . $dataform['id'] . '\',\'hdnLine_group_total_' . $dataform['id'] . '\',\'' . $dataform['labal_name'] . '\'); return false;">Add ' . $dataform['labal_name'] . '</a>';
            } else {
                $output.='<br><a class="lom-add-new" href="#" style="float:left;" onClick="addFormTotalFieldTextnolan(\'' . $formcount . '\',\'' . $dataform['id'] . '\',\'hdnLine_group_total_' . $dataform['id'] . '\',\'' . $dataform['labal_name'] . '\'); return false;">Add ' . $dataform['labal_name'] . '</a>';
            }
        }
        $output.= "</div>";
        $output.= '<div style="float:left;width:610px;" id="' . $dataform['id'] . '_inputs">';
        $output.= '<div style="" id="' . $dataform['id'] . '_' . $multi . '_inputs">';

        if (isset($parent_multi) and $parent_multi > 0) {
            $sqltest = "SELECT * FROM metadata_element_value WHERE record_id='" . $record['id'] . "' and element_hierarchy='" . $dataform['id'] . "' and multi='" . $parent_multi . "' ORDER BY (case WHEN multi IS NULL THEN '9999' ELSE multi END) ASC";
            //echo $sqltest; //break;
            $exec5 = $db->query($sqltest);
            $data6 = $exec5->fetchAll();
            $exec5 = NULL;
        }
        $size_of_objects = sizeof($data6);
        for ($x = 0; $x < $size_of_objects; $x++) {
            $datarecord = & $data6[$x];
            if ($datarecord['element_hierarchy'] === $dataform['id']) {
                $datarecordvalue = $datarecord['value'];
                $datarecordvaluelan = $datarecord['language_id'];
                $datarecoreditable = $datarecord['is_editable'];
                if ($multi != $datarecord['multi']) {
                    $output.='<input name="hdnLine_' . $dataform['id'] . '_' . $multi . '" id="hdnLine_' . $dataform['id'] . '_' . $multi . '" type="hidden" value="' . $formcount . '">';
                    $formcount = 0;

                    $output.= '</div><div id="' . $dataform['id'] . '_' . $datarecord['multi'] . '_inputs">';
                    if ($datarecord['multi'] > 1) {
                        $output.="<hr style='clear:both;'>";
                    }
                    //if hierarchy type= langstring
                    if ($dataform['datatype_id'] === 1 and $view_mode != 1) {
                        $output.='<a class="lom-add-new" style="float:left;"  href="#" onClick="addFormFieldText(\'' . $formcount . '\',\'' . $dataform['id'] . '_' . $datarecord['multi'] . '\',\'hdnLine_' . $dataform['id'] . '_' . $datarecord['multi'] . '\'); return false;">Add Language</a>&nbsp;&nbsp;';
                    }
                    if ($dataform['max_occurs'] > 1 and $view_mode != 1) {
                        if ($datarecoreditable === 0 or $view_mode == 1) {
                            
                        } else {
                            $output.='<a class="lom-remove" href="#" onClick="removeFormFieldTotalExisted(\'' . $dataform['id'] . '_' . $datarecord['multi'] . '\',\'' . $dataform['id'] . '\',\'' . $datarecord['record_id'] . '\',\'' . $datarecord['multi'] . '\',\'1\'); return false;" style="">Remove ' . $dataform['labal_name'] . '</a>';
                        }//not editable 
                    }
                    //$output.= '<br><br>';						
                }//if $multi!=$datarecord
                $multi = $datarecord['multi']; //select the value for more than one foreach
                $formcount+=1;
                $formcounttotal+=1;
                $output.='<div id="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_field" style="clear:both;">';
                $output.= createlomelement('text', '' . $dataform['id'] . '_' . $multi . '_' . $formcount . '', '' . stripslashes($datarecordvalue) . '', 'class="textinput" 
					id="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '" style="float:left;width:200px;"', NULL, NULL, NULL, NULL, $datarecoreditable, $view_mode) . '&nbsp;&nbsp';
                $output.='<div style="position:relative; left:5px; top:0px; float:left;"> ';
                //if hierarchy type= langstring/////////////////////////////////////////////
                if ($dataform['datatype_id'] === 1) {
                    $langstringparams = array('element_hierarchy' => $datarecord['element_hierarchy'], "record_id" => $datarecord['record_id'], "multi" => $datarecord['multi']);
                    $output.= createlomelement('selectlanstr', '' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_lan', $datarecordvaluelan, 'class="combo" 
					style="vertical-align:top;"', $datalan, 'vov_rec_id', 'label', $langstringparams, NULL, $view_mode);
                }//langstring
                if ($dataform['datatype_id'] === 1 and $view_mode != 1) {
                    $output.='<a class="lom-remove" href="#" onClick="removeFormFieldExisted(\'' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_field\',\'' . $dataform['id'] . '\',\'' . $datarecord['language_id'] . '\',\'' . $datarecord['record_id'] . '\',\'' . $datarecord['multi'] . '\'); return false;" style="float:right;">Remove Language</a><br>';
                } else {
                    if ($datarecord['is_editable'] != 0 and $view_mode != 1) {
                        $output.='<a class="lom-remove" href="#" onClick="removeFormFieldExisted(\'' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_field\',\'' . $dataform['id'] . '\',\'' . $datarecord['language_id'] . '\',\'' . $datarecord['record_id'] . '\',\'' . $datarecord['multi'] . '\'); return false;" style="float:left;">Remove</a><br>';
                    }//is editable
                }
                $output.= '</div>'; /////////////////div tou add remove
                $output.= '</div>';
            }
            unset($datarecord);
            unset($data6[$x]);
        }//select the value for more than one foreach //if $datarecord['element_hierarchy']===$dataform['id']  an uparxei eggrafh
        $output.='<input name="hdnLine_' . $dataform['id'] . '_' . $multi . '" id="hdnLine_' . $dataform['id'] . '_' . $multi . '" type="hidden" value="' . $formcount . '">';
        $output.= '</div>';




        //an den uparxei eggrafh create one empty
        if ($formcount === 0 and $view_mode != 1) {
            $formcount+=1;
            if ($parent_multi > 0) {
                $multi = $parent_multi;
            } else {
                $multi = 1;
            }
            $output.= '<div style="" id="' . $dataform['id'] . '_' . $multi . '_inputs">';
            $output.='<input name="hdnLine_' . $dataform['id'] . '_' . $multi . '" id="hdnLine_' . $dataform['id'] . '_' . $multi . '" type="hidden" value="' . $formcount . '">';
            //$output.= "<hr style='clear:both;'>";
            if ($dataform['datatype_id'] === 1) {//if hierarchy type= langstring
                $output.='<a class="lom-add-new" href="#" style="float:left;"  onClick="addFormFieldText(\'' . $formcount . '\',\'' . $dataform['id'] . '_' . $multi . '\',\'hdnLine_' . $dataform['id'] . '_' . $multi . '\'); return false;">Add Language</a>';
                $output.= '<br><br>';
            }
            $output.='<div id="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_field"  style="clear:both;">';
            $output.= createlomelement('text', '' . $dataform['id'] . '_' . $multi . '_' . $formcount . '', '', 'class="textinput" style="width:200px;" 
					id="' . $dataform['id'] . '_' . $multi . '_' . $formcount . '"') . '&nbsp;&nbsp';
            //if hierarchy type= langstring
            if ($dataform['datatype_id'] === 1) {
                $output.= createlomelement('selectlanstr', '' . $dataform['id'] . '_' . $multi . '_' . $formcount . '_lan', '', 'class="combo" 
					style="vertical-align:top;"', $datalan, 'vov_rec_id', 'label');
            }//langstring
            $output.= '<br>';
            $output.= "</div>";
            $output.= "</div>";
        }//end create one empty
        $output.= "</div>";
        $output.= '</div>';
        $output.= '<br style="clear:both"><br>';
        $output.= '<input name="hdnLine_group_total_' . $dataform['id'] . '" id="hdnLine_group_total_' . $dataform['id'] . '" type="hidden" value="' . $multi . '">';
    }/////////////////if($for_translation==1){//////////////////////////////////////////
    return $output;
}

function lomvcardform($data6, $dataform, $record, $parent_multi = NULL, $view_mode) {
    $db = Zend_Registry::get('db');
    if (isset($parent_multi) and $parent_multi > 0) {
        $sqltest = "SELECT * FROM metadata_element_value WHERE record_id='" . $record['id'] . "' and element_hierarchy='" . $dataform['id'] . "' and multi='" . $parent_multi . "' ORDER BY (case WHEN multi IS NULL THEN '9999' ELSE multi END) ASC";
        //echo $sqltest; //break;
        $exec5 = $db->query($sqltest);
        $data6 = $exec5->fetchAll();
        $exec5 = NULL;
    }

    echo '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; 
			width:100%; " id="' . $dataform['id'] . '_' . $parent_multi . '">';

    if ($dataform['max_occurs'] > 1 and $view_mode != 1) {
        echo '<a class="lom-add-new" href="#" onClick="addFormvcard(\'0\',\'' . $dataform['id'] . '_' . $parent_multi . '\',\'hdnLine_group_vcard_' . $dataform['id'] . '_' . $parent_multi . '\',\'' . $dataform['labal_name'] . '\'); return false;">Add new ' . $dataform['labal_name'] . '</a><br><br>';
    }
    $vcardcount = 0;
    $vcardcount = count($data6);
    if ($vcardcount > 0) { //an uparxei eggrafh
        foreach ($data6 as $datarecord) {
            if ($datarecord['element_hierarchy'] === $dataform['id']) {
                if ($datarecord['vcard_id'] > 0) {
                    $datarecordvalue = $datarecord['vcard_id'];
                } else {
                    $datarecordvalue = 0;
                }
                $datarecordparent_indexer = $datarecord['parent_indexer'];
                if ($datarecordparent_indexer > 0) {
                    $datarecordparent_indexer = $datarecordparent_indexer;
                } else {
                    $datarecordparent_indexer = 1;
                }
                $multi = $datarecord['multi'];
                $datarecoreditable = $datarecord['is_editable'];
                if ($multi > 0) {
                    $multi = $multi;
                } else {
                    $multi = 1;
                }



                if ($datarecordvalue > 0) {
                    $sqlchele = "SELECT * FROM metadata_vcard WHERE id=" . $datarecordvalue . "";
                    $execchele = $db->query($sqlchele);
                    $childelements = $execchele->fetch();
                    //echo $sqlchele;
                    $execchele = NULL;
                    $childelementscount = count($childelements);


                    echo '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; 
			width:100%; " id="' . $dataform['id'] . '_' . $multi . '_' . $datarecordparent_indexer . '">';

                    echo '<input name="vcard_general_' . $dataform['id'] . '_' . $multi . '_' . $datarecordparent_indexer . '" id="vcard_general_' . $dataform['id'] . '_' . $multi . '_' . $datarecordparent_indexer . '" type="hidden" value="">';
                    $dataform['labal_name'] = return_multi_language_label_name($dataform['element_id']);
                    $labalname = $dataform['labal_name'];
                    if ($datarecoreditable === 0 or $view_mode == 1) {
                        
                    } else {
                        $labalname.= '&nbsp;&nbsp;<a class="lom-remove" href="#" onClick="removeFormvcardExisted(\'' . $dataform['id'] . '_' . $multi . '_' . $datarecordparent_indexer . '\',\'' . $dataform['id'] . '\',\'' . $datarecord['record_id'] . '\',\'' . $multi . '\',\'1\',\'' . $datarecordparent_indexer . '\'); return false;">Remove</a>';
                    }//if editable


                    echo '<div style="float:left;">' . createlomlabel($labalname, 'for=' . $dataform['id'] . ' style="width:158px;"', NULL, $dataform['element_id']) . '</div><br>';

                    echo '<div style="float:left;">';
                    echo '<span style="float:left; width:70px;">Name: </span>' . createlomelement('text', 'vcard_name_' . $dataform['id'] . '_' . $multi . '_' . $datarecordparent_indexer . '', '' . stripslashes($childelements['name']) . '', 'class="textinput" id="' . $dataform['id'] . '_' . $multi . '_' . $datarecordparent_indexer . '" style="float:left;width:200px;"', NULL, NULL, NULL, NULL, $datarecoreditable, $view_mode) . '<br><br>';
                    echo '<span style="float:left; width:70px;">Surname: </span>' . createlomelement('text', 'vcard_surname_' . $dataform['id'] . '_' . $multi . '_' . $datarecordparent_indexer . '', '' . stripslashes($childelements['surname']) . '', 'class="textinput" id="' . $dataform['id'] . '_' . $multi . '_' . $datarecordparent_indexer . '" style="float:left;width:200px;"', NULL, NULL, NULL, NULL, $datarecoreditable, $view_mode) . '<br><br>';
                    echo '<span style="float:left; width:70px;">Email: </span>' . createlomelement('text', 'vcard_email_' . $dataform['id'] . '_' . $multi . '_' . $datarecordparent_indexer . '', '' . stripslashes($childelements['email']) . '', 'class="textinput" id="' . $dataform['id'] . '_' . $multi . '_' . $datarecordparent_indexer . '" style="float:left;width:200px;"', NULL, NULL, NULL, NULL, $datarecoreditable, $view_mode) . '<br><br>';
                    echo '<span style="float:left; width:70px;">Organization: </span>' . createlomelement('text', 'vcard_organization_' . $dataform['id'] . '_' . $multi . '_' . $datarecordparent_indexer . '', '' . stripslashes($childelements['organization']) . '', 'class="textinput" id="' . $dataform['id'] . '_' . $multi . '_' . $datarecordparent_indexer . '" style="float:left;width:200px;"', NULL, NULL, NULL, NULL, $datarecoreditable, $view_mode) . '<br><br>';
                    echo '</div>';

                    echo '</div>';
                } //if datarecord value>0
            }//if hierarchy=dataform[id]
        }//foreach data6 
    } else {  //an den uparxei data6 alliws nea eggrafh
        if ($parent_multi > 0) {
            $multi = $parent_multi;
        } else {
            $multi = 1;
        }
        $datarecordparent_indexer = 1;

        echo '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; 
			width:100%; " id="' . $dataform['id'] . '_' . $multi . '_' . $datarecordparent_indexer . '">';

        echo '<input name="vcard_general_' . $dataform['id'] . '_' . $multi . '_' . $datarecordparent_indexer . '" id="vcard_general_' . $dataform['id'] . '_' . $multi . '_' . $datarecordparent_indexer . '" type="hidden" value="">';
        $dataform['labal_name'] = return_multi_language_label_name($dataform['element_id']);
        $labalname = $dataform['labal_name'];

        echo '<div style="float:left;">' . createlomlabel($labalname, 'for=' . $dataform['id'] . ' style="width:158px;"', NULL, $dataform['element_id']) . '</div><br>';
        if ($view_mode != 1) { //if view_mode not display empty
            echo '<div style="float:left;">';
            echo '<span style="float:left; width:70px;">Name: </span>' . createlomelement('text', 'vcard_name_' . $dataform['id'] . '_' . $multi . '_' . $datarecordparent_indexer . '', '' . stripslashes($childelements['name']) . '', 'class="textinput" id="' . $dataform['id'] . '_' . $multi . '_' . $datarecordparent_indexer . '" style="float:left;width:200px;"') . '<br><br>';
            echo '<span style="float:left; width:70px;">Surname: </span>' . createlomelement('text', 'vcard_surname_' . $dataform['id'] . '_' . $multi . '_' . $datarecordparent_indexer . '', '' . stripslashes($childelements['surname']) . '', 'class="textinput" id="' . $dataform['id'] . '_' . $multi . '_' . $datarecordparent_indexer . '" style="float:left;width:200px;"') . '<br><br>';
            echo '<span style="float:left; width:70px;">Email: </span>' . createlomelement('text', 'vcard_email_' . $dataform['id'] . '_' . $multi . '_' . $datarecordparent_indexer . '', '' . stripslashes($childelements['email']) . '', 'class="textinput" id="' . $dataform['id'] . '_' . $multi . '_' . $datarecordparent_indexer . '" style="float:left;width:200px;"') . '<br><br>';
            echo '<span style="float:left; width:70px;">Organization: </span>' . createlomelement('text', 'vcard_organization_' . $dataform['id'] . '_' . $multi . '_' . $datarecordparent_indexer . '', '' . stripslashes($childelements['organization']) . '', 'class="textinput" id="' . $dataform['id'] . '_' . $multi . '_' . $datarecordparent_indexer . '" style="float:left;width:200px;"') . '<br><br>';
            echo '</div>';
        }//if view_mode not display empty
        echo '</div>';
    }
    echo '<input name="hdnLine_group_vcard_' . $dataform['id'] . '_' . $multi . '" id="hdnLine_group_vcard_' . $dataform['id'] . '_' . $multi . '" type="hidden" value="' . $datarecordparent_indexer . '">';
    echo '</div>';
    return $output;
}

function lomparentform($data6, $dataform, $datalan, $record, $depth, $view_mode, $xml_general) {
    $db = Zend_Registry::get('db');

    if ($dataform['min_occurs'] > 0) {
        echo '<div id="' . $dataform['id'] . '" class="mandatory_element">';
    } elseif ($dataform['is_recommented'] == 1) {
        echo '<div id="' . $dataform['id'] . '" class="recommented_element">';
    } else {
        echo '<div id="' . $dataform['id'] . '" class="optional_element">';
    }



    $parentcount = 0;
    $parentcount = count($data6);
    if ($parentcount > 0) { //an uparxei eggrafh		
        $size_of_objects = sizeof($data6);
        for ($x = 0; $x < $size_of_objects; $x++) {
            $datarecord = & $data6[$x];

            $execchele = $db->query("SELECT c.*,b.vocabulary_id,b.id as elm_id FROM  metadata_element b JOIN metadata_element_hierarchy c 
			ON c.element_id = b.id WHERE c.pelement_id=" . $dataform['elm_id'] . " and c.is_visible=1  ORDER BY (case WHEN c.sequence IS NULL THEN '9999' ELSE c.sequence END) ASC");
            $childelements = $execchele->fetchAll();
            $execchele = NULL;
            $childelementscount = count($childelements);
            $parent_multi = $datarecord['multi'];
            $totalmulti = $datarecord['multi'];
            $datarecoreditablepar = $datarecord['is_editable'];

            $output = '';
            if ($childelementscount > 0) {
                $margindepth = $depth * 10;
                //$parentdivcount=1;
                echo '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; 
			width:100%; " id="' . $dataform['id'] . '_' . $parent_multi . '">';
                //echo '<div id="'.$dataform['id'].'">';
                $dataform['labal_name'] = return_multi_language_label_name($dataform['element_id']);
                $labalname = $dataform['labal_name'];
                if ($dataform['min_occurs'] > 0) {
                    $labalname.='*';
                }
                if ($dataform['max_occurs'] > 1 and $view_mode != 1) {
                    $labalname.= '&nbsp;&nbsp;<a class="lom-add-new" href="#" onClick="addFormmultiParent(\'0\',\'' . $dataform['id'] . '\',\'hdnLine_group_total_parent_' . $dataform['id'] . '\',\'' . $dataform['labal_name'] . '\'); return false;">Add ' . $dataform['labal_name'] . ' </a>&nbsp;&nbsp;';
                    if ($datarecoreditablepar === 0 or $view_mode == 1) {
                        
                    } else {
                        $labalname.= '<a class="lom-remove" href="#" onClick="removeFormmultiParent(\'' . $dataform['id'] . '_' . $parent_multi . '\',\'' . $dataform['id'] . '\',\'' . $datarecord['record_id'] . '\',\'' . $parent_multi . '\',\'1\'); return false;">Remove ' . $dataform['labal_name'] . '</a>';
                    }//if not editable
                }
                echo '<input name="' . $dataform['id'] . '_' . $parent_multi . '" id="' . $dataform['id'] . '_' . $parent_multi . '" type="hidden" value="">';
                echo '<div style="float:left;">' . createlomlabel($labalname, 'for=' . $dataform['id'] . ' style="width:608px;"', NULL, $dataform['element_id']) . '</div><br>';

                echo'<br style="clear:both;">';
                $margindepth+=10;
                echo'<div style="margin-left:' . $margindepth . 'px;">';
                //echo $childelements['labal_name'];
                $size_of_objectsy = sizeof($childelements);
                for ($y = 0; $y < $size_of_objectsy; $y++) {
                    $childelements_vl = & $childelements[$y];
                    //$depth+=1;
                    //echo $childelements['labal_name'];
                    $extra = 'style="font-weight:normal;"';
                    if ($childelements_vl['element_id'] === 48) {
                        $extra = "onchange='change49(this.value)'";
                    }
                    checkelement($childelements_vl, $datalan, $record, $depth, $extra, $parent_multi, NULL, $view_mode, $xml_general);

                    unset($childelements_vl);
                    unset($childelements[$y]);
                }
                echo'</div>';


                echo'</div><br style="clear:both;">';
            }//if isset if(childelements['id']>0){
            unset($datarecord);
            unset($data6[$x]);
        }//foreach $data6
    } else { //an den uparxei data6 eggrafei dhmiourgia neas
        $execchele = $db->query("SELECT c.*,b.vocabulary_id,b.id as elm_id FROM  metadata_element b JOIN metadata_element_hierarchy c 
			ON c.element_id = b.id WHERE c.pelement_id=" . $dataform['elm_id'] . " and c.is_visible=1  ORDER BY (case WHEN c.sequence IS NULL THEN '9999' ELSE c.sequence END) ASC");
        $childelements = $execchele->fetchAll();
        $execchele = NULL;
        $childelementscount = count($childelements);
        $parent_multi = 1;
        $totalmulti = $parent_multi;

        $output = '';
        if ($childelementscount > 0) {
            $margindepth = $depth * 10;
            //$parentdivcount=1;
            echo '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; 
			width:100%; " id="' . $dataform['id'] . '_' . $parent_multi . '">';
            //echo '<div id="'.$dataform['id'].'">';
            $dataform['labal_name'] = return_multi_language_label_name($dataform['element_id']);
            $labalname = $dataform['labal_name'];
            if ($dataform['min_occurs'] > 0) {
                $labalname.='*';
            }
            if ($dataform['max_occurs'] > 1 and $view_mode != 1) {
                $labalname.= '&nbsp;&nbsp;<a class="lom-add-new" href="#" onClick="addFormmultiParent(\'0\',\'' . $dataform['id'] . '\',\'hdnLine_group_total_parent_' . $dataform['id'] . '\',\'' . $dataform['labal_name'] . '\'); return false;">Add ' . $dataform['labal_name'] . ' </a>';
            }
            echo '<input name="' . $dataform['id'] . '_' . $parent_multi . '" id="' . $dataform['id'] . '_' . $parent_multi . '" type="hidden" value="">';
            echo '<div style="float:left;">' . createlomlabel($labalname, 'for=' . $dataform['id'] . ' style="width:608px;"', NULL, $dataform['element_id']) . '</div><br>';

            echo'<br style="clear:both;">';
            $margindepth+=10;
            echo'<div style="margin-left:' . $margindepth . 'px;">';
            //echo $childelements['labal_name'];
            foreach ($childelements as $childelements) {
                //$depth+=1;
                //echo $childelements['labal_name'];
                $extra = 'style="font-weight:normal;"';
                if ($childelements['element_id'] === 48) {
                    $extra = "onchange='change49(this.value)'";
                }
                checkelement($childelements, $datalan, $record, $depth, $extra, $parent_multi, NULL, $view_mode, $xml_general);
            }
            echo'</div>';


            echo'</div><br style="clear:both;">';
        }//if isset if(childelements['id']>0){
    }
    echo '<input name="hdnLine_group_total_parent_' . $dataform['id'] . '" id="hdnLine_group_total_parent_' . $dataform['id'] . '" type="hidden" value="' . $totalmulti . '">';
    echo'</div>'; //arxiko div kentriko id
    return $output;
}

function checkelement($dataform, $datalan, $record, $depth = 0, $extra = NULL, $parent_multi = NULL, $for_translation = NULL, $view_mode = NULL, $xml_general = NULL) {

    $db = Zend_Registry::get('db');

    $exec5 = $db->query("SELECT * FROM metadata_element_value WHERE record_id='" . $record['id'] . "' and element_hierarchy='" . $dataform['id'] . "' ORDER BY (case WHEN multi IS NULL THEN '9999' ELSE multi END) ASC");
    $data6 = $exec5->fetchAll();
    $exec5 = NULL;


///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////if hierarchy type= parent////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    if ($dataform['datatype_id'] === 2) {
        lomparentform($data6, $dataform, $datalan, $record, $depth, $view_mode, $xml_general);
    } //end form name = parent
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////if hierarchy tyoe = vcard////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    elseif ($dataform['datatype_id'] === 3) {
        echo lomvcardform($data6, $dataform, $record, $parent_multi, $view_mode);
    } //end form name = vcard
    elseif ($dataform['datatype_id'] === 4) {
        //echo lomselectformfromXml($data6,$dataform,$datalan,$extra,$parent_multi,$record);
    } //end form name = select from xml//////////////////////////////////////////
    elseif ($dataform['datatype_id'] === 5) {
        echo lomontology($data6, $dataform, $extra, $parent_multi, $record, $view_mode, $xml_general);
    } //end form name = select from ontology//////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////if hierarchy form name = radio////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    elseif ($dataform['form_type_id'] === 4) {
        echo lomradioform($data6, $dataform, $view_mode);
    } //end form name = radio
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////if hierarchy form name = select///////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    elseif ($dataform['datatype_id'] === 6) {
        echo lomselectform($data6, $dataform, $datalan, $extra, $parent_multi, $record, $view_mode);
    } //end form name = select//////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////if hierarchy form name = text///////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    elseif ($dataform['form_type_id'] === 2) {
        echo lomtextform($data6, $dataform, $datalan, $parent_multi, $record, $for_translation, $view_mode);
    } //end form name = text//////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////if hierarchy form name = date///////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    elseif ($dataform['form_type_id'] === 5) {
        echo lomtextformdate($data6, $dataform, $datalan, $parent_multi, $record, $view_mode);
    } //end form name = date//////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////type=textarea////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    else {
        echo lomtextareaform($data6, $dataform, $datalan, $parent_multi, $record, $for_translation, $view_mode);
    }
}

function objecttosortedarray($xml) {
    $sortedxml = array();
    foreach ($xml as $xml2) {
        $sortedxml[] = (string) $xml2[0];
    }

    sort($sortedxml);
    return $sortedxml;
}

function createnew_xml_selectbox($id, $divid, $vocabulary_id, $ontology = NULL) {
    require_once 'Omeka/Core.php';
    $core = new Omeka_Core;

    try {
        $db = $core->getDb();

        //Force the Zend_Db to make the connection and catch connection errors
        try {
            $mysqli = $db->getConnection()->getConnection();
        } catch (Exception $e) {
            throw new Exception("<h1>MySQL connection error: [" . mysqli_connect_errno() . "]</h1>" . "<p>" . $e->getMessage() . '</p>');
        }
    } catch (Exception $e) {
        die($e->getMessage() . '<p>Please refer to <a href="http://omeka.org/codex/">Omeka documentation</a> for help.</p>');
    }

    echo "<div id='row" . $id . "'><select name='" . $divid . "_" . $id . "' id='" . $divid . "_" . $id . "' class='combo' style='width:300px;float:left;'>";
    echo "<option value=''>Select</option>";


    $xmlnew = internal_xml(NULL, $xml_general, $_SESSION['get_language_for_internal_xml'], $vocabulary_id);

    foreach ($xmlnew['drop_down'] as $xmlnew) {
        echo '<option value="' . $xmlnew['id'] . '" ';
        echo '>' . $xmlnew['value'] . '</option>';
    }
    ?>
    </select> 
    <?php
    if ($ontology > 0) {

        echo '<a href="javascript:void(0)" onclick="toggletree(\'' . $divid . '_' . $id . '_tree\');" style="float:left;margin-left:2px;" id="' . $divid . '_' . $id . '">Browse</a>';
    }
    ?>
    <a class='lom-remove' style='float:left;' href='#' onClick='removeFormField("#row<?php echo $_POST['id']; ?>"); return false;'>Remove</a><div><br>
    <?php
    if ($ontology > 0) {

        //echo internal_xml('' . $_POST['divid'] . '_' . $_POST['id'] . '_tree', NULL, $_SESSION['get_language_for_internal_xml'], NULL);
        echo $xmlnew['hierarchy_tree'];
    }
    ?>

        <?php
    }

    function FiletypeMapping($type) {
        $type = str_replace("; charset=binary", "", $type);
        $omekatypearray = array("application/msword" => "application/msword", "application/ogg" => "application/ogg", "application/pdf" => "application/pdf", "application/rtf" => "application/rtf", "application/vnd.ms-access" => "application/msaccess", "application/vnd.ms-excel" => "application/msexcel", "application/vnd.ms-powerpoint" => "application/ppt", "image/pjpeg" => "image/jpeg", "application/pdf" => "application/pdf", "application/pdf" => "application/pdf", "application/pdf" => "application/pdf", "application/pdf" => "application/pdf", "application/pdf" => "application/pdf", "application/pdf" => "application/pdf", "application/pdf" => "application/pdf");

        foreach ($omekatypearray as $key => $omekatypearray) {
            if ($key == $type) {
                $type = $omekatypearray;
            }
        }
        $type = find_voc_rec_id($type, 21);
        return $type;
    }

    function find_voc_rec_id($value, $voc_id) {
        require_once 'Omeka/Core.php';
        $core = new Omeka_Core;

        try {
            $db = $core->getDb();

            //Force the Zend_Db to make the connection and catch connection errors
            try {
                $mysqli = $db->getConnection()->getConnection();
            } catch (Exception $e) {
                throw new Exception("<h1>MySQL connection error: [" . mysqli_connect_errno() . "]</h1>" . "<p>" . $e->getMessage() . '</p>');
            }
        } catch (Exception $e) {
            die($e->getMessage() . '<p>Please refer to <a href="http://omeka.org/codex/">Omeka documentation</a> for help.</p>');
        }

        $sql = "SELECT * FROM metadata_vocabulary_record WHERE vocabulary_id=" . $voc_id . " and value='" . $value . "'";
        $exec = $db->query($sql);
        $data = $exec->fetch();

        return $data['id'];
    }

    function FindElement(&$readerobject, $name) {
        while ($readerobject->name != $name) {
            if (!$readerobject->read())
                break;
        }
        if ($readerobject->name == $name) {
            $readerobject->read();
            return 1;
        }
        else
            return 0;
    }

    function parse_ontologies($xml_general) {
        $xml = $xml_general;

        $new_xml_instanceOf = array();
        $new_xml_lang = array();
        $new_xml_value = array();
        $new_xml_id = array();
        $new_xml_subClassOf = array();
        $new_xml_instanceOf_en = array();
        $new_xml_value_en = array();
        $i = 0;
        $x = 0;
        while ($xml->read()) {


            switch ($xml->nodeType) {
                case (XMLReader::ELEMENT):
                    if ($xml->localName == 'instance' and ($xml->getAttribute('lang') == $_SESSION['get_language_for_internal_xml'] or $xml->getAttribute('lang') == 'en')) {
                        $i++;
                        $instanceOfvalue = $xml->getAttribute('instanceOf');
                        $instanceLang = $xml->getAttribute('lang');
                        $xml->read();
                        $instanceValue = $xml->value;

                        if ($instanceLang == $_SESSION['get_language_for_internal_xml']) {
                            $new_xml_instanceOf[$i] = $instanceOfvalue;
                            $new_xml_value[$i] = $instanceValue;
                        }
                        if ($instanceLang == 'en') {

                            if (strlen($instanceValue) > 0) {
                                $new_xml_instanceOf_en[$i] = $instanceOfvalue;
                                $new_xml_value_en[$i] = $instanceValue;
                            } else {
                                $new_xml_instanceOf_en[$i] = $instanceOfvalue;
                                $new_xml_value_en[$i] = $instanceOfvalue;
                            }
                        }
                    }//print_r($new_xml_instanceOf_en);
                    if ($xml->localName == 'class') {
                        $x++;
                        $new_xml_id[$x] = $xml->getAttribute('id');
                        $new_xml_subClassOf[$x] = $xml->getAttribute('subClassOf');
                    }
                    if ($xml->localName == 'hierarchy') {
                        $rootElement = $xml->getAttribute('rootElement');
                    }
            }
        }
        $xml = NULL;
        $size_of_objects = sizeof($new_xml_id);
        for ($x = 0; $x <= $size_of_objects; $x++) {
            $result2 = & $new_xml_id[$x];
            $result3 = & $new_xml_subClassOf[$x];


            $label_position = array_search($result2, $new_xml_instanceOf);
            if (strlen($result2) > 0) {
                if (strlen($new_xml_value[$label_position]) > 0) {
                    //echo $result2.' - '. $new_xml_value[$label_position].'<br><br>';
                    $sortedxml[] = array('id' => $result2, 'value' => (string) $new_xml_value[$label_position]);
                } else {

                    $label_position2 = array_search($result2, $new_xml_instanceOf_en);
                    $sortedxml[] = array('id' => $result2, 'value' => (string) $new_xml_value_en[$label_position2]);
                }
            }
            //echo $result2['id'];
        }

        foreach ($sortedxml as $key => $row) {
            $volume[$key] = $row['value'];
        }

        // Sort the data with volume descending, edition ascending
        // Add $data as the last parameter, to sort by the common key
        array_multisort($volume, SORT_ASC, $sortedxml);
        $sortedxml1 = array_map("unserialize", array_unique(array_map("serialize", $sortedxml)));

        $output.='<ul>';
        $size_of_objects = sizeof($new_xml_id);
        for ($x = 0; $x <= $size_of_objects; $x++) {
            $result2 = & $new_xml_id[$x];
            $result3 = & $new_xml_subClassOf[$x];

            if ($result3 == $rootElement) {
                $label_position = array_search($result2, $new_xml_instanceOf);
                if (strlen($new_xml_value[$label_position]) > 0) {
                    $result4[0] = $new_xml_value[$label_position];
                } else {

                    $label_position2 = array_search($result2, $new_xml_instanceOf_en);
                    $result4[0] = $new_xml_value_en[$label_position2];
                }

                $output.='<li id="' . $result2 . '"><a href="#">' . $result4[0] . '</a>';
                $output.=ontology_depth_elements($new_xml_id, $result2, $new_xml_subClassOf, $new_xml_instanceOf, $new_xml_value, $new_xml_instanceOf_en, $new_xml_value_en);

                $output.= '</li>';
            }
            unset($result2);
            unset($result3);
        }

        $output.='</ul>';
        unset($new_xml_value_en);
        unset($new_xml_instanceOf_en);
        unset($new_xml_id);
        unset($new_xml_subClassOf);
        unset($new_xml_value);
        unset($new_xml_instanceOf);
        unset($new_xml_lang);
        $sortedxml_final = array('drop_down' => $sortedxml1, 'hierarchy_tree' => $output);
        unset($sortedxml1);
        unset($output);
        return $sortedxml_final;
        ///////////////////////clean memory/////
    }

    function internal_xml($id, $xml_general, $lang, $voc_id) {

        //print_r($xml);break
        $xml = $xml_general[$voc_id];
        ?>
        <?php
        $output = '<div id="' . $id . '"  style="clear:both;height:auto;display:none;">';
        $output .=$xml['hierarchy_tree'];


        $output.='</div>';

        $select_id = str_replace('_tree', '', $id);
        $output.=' <script type="text/javascript" class="source below">
          jQuery(function () {
          jQuery("#' . $id . '")
          .jstree({ "plugins" : ["themes","html_data","ui"] })
          // 1) if using the UI plugin bind to select_node
          .bind("select_node.jstree", function (event, data) {
          // `data.rslt.obj` is the jquery extended node that was clicked
          //alert(data.rslt.obj.attr("id"));
          document.getElementById(\'' . $select_id . '\').value=data.rslt.obj.attr("id");

          })
          // 2) if not using the UI plugin - the Anchor tags work as expected
          //    so if the anchor has a HREF attirbute - the page will be changed
          //    you can actually prevent the default, etc (normal jquery usage)
          .delegate("a", "click", function (event, data) { event.preventDefault(); })
          });
          </script>';
        $sortedxml_final = array('drop_down' => $xml['drop_down'], 'hierarchy_tree' => $output);
        return $sortedxml_final;
    }

    function ontology_depth_elements($new_xml_id2, $result_check, $new_xml_subClassOf, $new_xml_instanceOf, $new_xml_value, $new_xml_instanceOf_en, $new_xml_value_en) {
        $output2 = '';
        $output2.='<ul>';
        $size_of_objects = sizeof($new_xml_id2);
        for ($x = 0; $x <= $size_of_objects; $x++) {
            $result22 = & $new_xml_id2[$x];
            $result32 = & $new_xml_subClassOf[$x];

            if ($result32 == $result_check) {
                $label_position = array_search($result22, $new_xml_instanceOf);
                if (strlen($new_xml_value[$label_position]) > 0) {
                    $result4[0] = $new_xml_value[$label_position];
                } else {

                    $label_position2 = array_search($result22, $new_xml_instanceOf_en);
                    $result4[0] = $new_xml_value_en[$label_position2];
                }

                $output2.='<li id="' . $result22 . '"><a href="#">' . $result4[0] . '</a>';
                $output2.=ontology_depth_elements($new_xml_id2, $result22, $new_xml_subClassOf, $new_xml_instanceOf, $new_xml_value, $new_xml_instanceOf_en, $new_xml_value_en);

                $output2.= '</li>';
            }
            unset($result22);
            unset($result32);
        }

        $output2.='</ul>';

        return $output2;
    }

    function ontology_space_upcs($string) {

//$selectvaluesvalue=explode(' ',$string); 
//$selectvaluesvalue2='';
//foreach($selectvaluesvalue as $selectvaluesvalue){
//$selectvaluesvalue2.=ucfirst($selectvaluesvalue);}
        return $string;
    }

    function show_element_description($id) {

        $db = Zend_Registry::get('db');

        $sqlvocelemnew = "SELECT * FROM metadata_element_label_description  WHERE element_id=" . $id . "";
        $execvocelenew = $db->query($sqlvocelemnew);
        $datavocelenew = $execvocelenew->fetch();
        $execvocelenew = NULL;

        if (strlen($datavocelenew['description']) > 0) {
            return $datavocelenew['description'];
        }
    }

    function return_multi_language_label_name($element_id, $language_id = NULL) {

        $db = Zend_Registry::get('db');
        if ($language_id > 0) {
            $language_id = $language_id;
        } else {
            $language_id = $_SESSION['get_language'];
        }
        $sql2 = "SELECT * FROM metadata_element_label WHERE element_id=" . $element_id . " and language_id='" . $language_id . "'";
        $exec2 = $db->query($sql2);
        $datageneral = $exec2->fetch();

        if ($datageneral['id'] > 0) {
            $datageneral = $datageneral;
        } else {
            $sql2 = "SELECT * FROM metadata_element_label WHERE element_id=" . $element_id . " and language_id='en'";
            $exec2 = $db->query($sql2);
            $datageneral = $exec2->fetch();
        }
        $exec2 = NULL;
        return $datageneral['labal_name'];
    }

    function voc_multi_label($voc_rec_id, $language_id = NULL) {

        $db = Zend_Registry::get('db');
        if ($language_id > 0) {
            $language_id = $language_id;
        } else {
            $language_id = $_SESSION['get_language'];
        }
        $sql2 = "SELECT * FROM metadata_vocabulary_value WHERE vocabulary_rid=" . $voc_rec_id . " and language_id='" . $language_id . "'";
        $exec2 = $db->query($sql2);
        $datageneral = $exec2->fetch();
        if ($datageneral['id'] > 0) {
            $datageneral = $datageneral;
        } else {
            $sql2 = "SELECT * FROM metadata_vocabulary_value WHERE vocabulary_rid=" . $voc_rec_id . " and language_id='en'";
            $exec2 = $db->query($sql2);
            $datageneral = $exec2->fetch();
        }
        $exec2 = NULL;
        return $datageneral['label'];
    }

    function return_label_description($element_id, $language_id = NULL) {

        $db = Zend_Registry::get('db');
        if ($language_id > 0) {
            $language_id = $language_id;
        } else {
            $language_id = $_SESSION['get_language'];
        }
        $sql2 = "SELECT * FROM metadata_element_label_description WHERE element_id=" . $element_id . " and language_id='" . $language_id . "' and public=1";
        $exec2 = $db->query($sql2);
        $datageneral = $exec2->fetch();
        if ($datageneral['id'] > 0) {
            $datageneral = $datageneral;
        } else {
            $sql2 = "SELECT * FROM metadata_element_label_description WHERE element_id=" . $element_id . " and language_id='en' and public=1";
            $exec2 = $db->query($sql2);
            $datageneral = $exec2->fetch();
        }
        $exec2 = NULL;
        if (strlen($datageneral['description']) > 0) {
            $datageneral['description'] = $datageneral['description'];
        } else {
            $datageneral['description'] = '';
        }
        return $datageneral['description'];
    }

    function get_language_for_switch() {
        if (isset($_GET['lang'])) {
            $get_language = transform_language_id($_GET['lang']);
        } elseif (isset($_SESSION['get_language'])) {
            $get_language = $_SESSION['get_language'];
        } else {
            $get_language = 'en';
        }
        //echo $_GET['lang'];
        return $get_language;
    }

    function transform_language_id($language_id) {


        $omekatypearray = array("en" => "en", "de" => "de", "el" => "el", "es" => "es", "fr" => "fr", "it" => "it", "tr" => "tr", "ee" => "ee", "lv" => "lv", "ru" => "ru");

        foreach ($omekatypearray as $key => $omekatypearray) {
            if ($key == $language_id) {
                $type = $omekatypearray;
            }
        }
        return $type;
    }

    function get_language_for_internal_xml() {
        if (isset($_GET['lang'])) {
            $get_language = transform_language_id_for_internal_xml($_GET['lang']);
        } elseif (isset($_SESSION['get_language_for_internal_xml'])) {
            $get_language = $_SESSION['get_language_for_internal_xml'];
        } else {
            $get_language = 'en';
        }
        //echo $_GET['lang'];
        return $get_language;
    }

    function transform_language_id_for_internal_xml($language_id) {


        $omekatypearray = array("en" => "en", "de" => "de", "el" => "el", "es" => "es", "fr" => "fr", "it" => "it", "tr" => "tr", "ee" => "et", "lv" => "lv", "ru" => "ru");

        foreach ($omekatypearray as $key => $omekatypearray) {
            if ($key == $language_id) {
                $type = $omekatypearray;
            }
        }
        return $type;
    }

    function get_language_for_omeka_switch() {
        if (isset($_GET['lang'])) {
            $get_language = transform_language_id_for_omeka($_GET['lang']);
        } elseif (isset($_SESSION['get_language_omeka'])) {
            $get_language = $_SESSION['get_language_omeka'];
        } else {
            $get_language = 'en_US';
        }
        //echo $_GET['lang'];
        return $get_language;
    }

    function transform_language_id_for_omeka($language_id) {


        $omekatypearray = array("en" => "en_US", "el" => "el_GR", "es" => "es", "fr" => "fr", "it" => "it", "de" => "de_DE", "tr" => "tr_TR", "ee" => "es_EE", "lv" => "lv_LV", "ru" => "ru_RU");

        foreach ($omekatypearray as $key => $omekatypearray) {
            if ($key == $language_id) {
                $type = $omekatypearray;
            }
        }
        return $type;
    }

    function savelomelement($element_hierarchy, $value, $item_id, $object_type, $parent_indexer = 1, $vocabulary_record_id = NULL) {

        require_once 'Omeka/Core.php';
        $core = new Omeka_Core;

        try {
            $db = $core->getDb();

            //Force the Zend_Db to make the connection and catch connection errors
            try {
                $mysqli = $db->getConnection()->getConnection();
            } catch (Exception $e) {
                throw new Exception("<h1>MySQL connection error: [" . mysqli_connect_errno() . "]</h1>" . "<p>" . $e->getMessage() . '</p>');
            }
        } catch (Exception $e) {
            die($e->getMessage() . '<p>Please refer to <a href="http://omeka.org/codex/">Omeka documentation</a> for help.</p>');
        }
        if (strlen($vocabulary_record_id) > 0) {
            $vocabulary_record_id = $vocabulary_record_id;
        } else {
            $vocabulary_record_id = 'NULL';
        }
        $lastExhibitIdSQL = "SELECT * FROM metadata_record where object_id=" . $item_id . " and object_type='" . $object_type . "'";
        $exec = $db->query($lastExhibitIdSQL);
        $row = $exec->fetch();
        $last_record_id = $row["id"];
        $exec = null;
        $value = htmlspecialchars($value);
        $value = addslashes($value);
        $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, vocabulary_record_id, multi, record_id, parent_indexer) VALUES ('" . $element_hierarchy . "','" . $value . "','none', " . $vocabulary_record_id . ",1, " . $last_record_id . ", " . $parent_indexer . ") ON DUPLICATE KEY UPDATE value='" . $value . "',vocabulary_record_id=" . $vocabulary_record_id . "";
//echo $metadatarecordSql;break;
        $execmetadatarecordSql = $db->query($metadatarecordSql);
        $exec = null;
    }

    function map_language_for_xerox2($language, $for_xerox = NULL) {
        $isolanguage = '';
        if ($for_xerox == 1) {
            if ($language == 'en') {
                $isolanguage = 'en-EN';
            }
            if ($language == 'es') {
                $isolanguage = 'es_ES';
            }
            if ($language == 'de') {
                $isolanguage = 'de_DE';
            }
            if ($language == 'it') {
                $isolanguage = 'it_IT';
            }
            if ($language == 'fr') {
                $isolanguage = 'fr_FR';
            }
        } else {
            if ($language == 'en-EN') {
                $isolanguage = 'en';
            }
            if ($language == 'es_ES') {
                $isolanguage = 'es';
            }
            if ($language == 'de_DE') {
                $isolanguage = 'de';
            }
            if ($language == 'it_IT') {
                $isolanguage = 'it';
            }
            if ($language == 'fr_FR') {
                $isolanguage = 'fr';
            }
        }


        return $isolanguage;
    }

    function translatexerox($targetLanguage = NULL, $text = NULL, $sourceLanguage) {
//echo $targetLanguage."123";
//echo $sourceLanguage."123<br><br>";
        //$targetLanguage = 'es_ES';
        if (strlen($targetLanguage) > 0) {
//            $post = http_build_query(array(
//                "username" => "gkista",
//                "password" => "organiclinguapass"
//                    ));
//
//            $context = stream_context_create(array("http" => array(
//                    "method" => "POST",
//                    "header" => "Content-Type: application/x-www-form-urlencoded\r\n" .
//                    "Content-Length: " . strlen($post) . "\r\n",
//                    "content" => $post,
//                    )));
//
//            $page = file_get_contents("https://services.open.xerox.com/Auth.svc/OAuth2", false, $context);
//            $obj = json_decode($page);
//            $token = $obj->access_token;
            //$token = "http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name=gkista&http://open.xerox.com/LLTokenId=41&Issuer=https://open.xerox.com&Audience=https://open.xerox.com&ExpiresOn=1355443200&HMACSHA256=EbgxS4cjiu2uCugubzyn64MO9nsrOV%2byPNG2SmiEzw0%3d";
            /////until february 2015!!!!!!
            $token = "http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name=gkista&http://open.xerox.com/LLTokenId=50&Issuer=https://open.xerox.com&Audience=https://open.xerox.com&ExpiresOn=1425081600&HMACSHA256=eWdmtuu%2b6o2XjPb9lxm3Gh52Fny0NEWu6YxGltvgtJI%3d";

            $text = htmlentities($text, ENT_QUOTES, 'UTF-8'); //////add this for characters not ascii ”
            $header = 'Content-Type: application/json' . "\r\n";
            $header.= 'Host: services.open.xerox.com' . "\r\n";
            $header.= 'Authorization: WRAP access_token="' . $token . '"' . "\r\n";
            $context2 = stream_context_create(array("http" => array(
                    'method' => 'POST',
                    'header' => $header,
                    'content' => '{
"modelName":"Organic.Lingua",
"sourceLanguage":"' . $sourceLanguage . '",
"targetLanguage":"' . $targetLanguage . '",
"text":"' . $text . '",
"encoding":"UTF-8"
}',
                    )));

//print_r($context2); break;
            $page2 = file_get_contents("https://services.open.xerox.com/RestOp/TranslabOrganicLingua/TranslateTextStringSync", false, $context2);
            $obj2 = json_decode($page2);
//print_r($page2);
//            echo "<br><br>";

            foreach ($obj2 as $obj3) {
                foreach ($obj3 as $key => $obj4) {
                    if ($key == 'n:StringResponse') {
                        foreach ($obj4 as $key2 => $obj5) {
                            if ($key2 == 'n:resultString') {
//print($obj5); 
//echo $_POST['name'];
//echo $_POST['dividtext'];
//return 'Original text: '.$_POST['dividtext'].' <br> Translated text: '.$obj5;
                                //return $obj5."".$sourceLanguage."";
                                if (is_string($obj5) or $obj5==NULL or $obj5=='') {
                                    return $obj5;
                                } else {
                                    //print_r($obj5);
                                    foreach ($obj5 as $key => $obj51) {
                                        if ($key == '#text') {
                                            $obj511='';
                                            foreach($obj51 as $obj51){
                                                $obj511.=$obj51;
                                            }
                                            return $obj511;
                                        }
                                    }
                                }
                                //
                            }
                        }
                    }
                }
            }
        }//if strlen language
    }

//close function

    function change_interface_language($language) {

        $link = $_SERVER['REQUEST_URI'];
        $linkparamsurl = explode('?', $_SERVER['REQUEST_URI']);
        // natural_europe_new/items/show/3803?eidteaser=348?lang=el
        $linkparams = $linkparamsurl[1];
        $search = Array();
        if (strlen($linkparams) > 0) {
            $linkparams = '?' . $linkparams;
            if (stripos($linkparams, 'lang=') > 0) {
                $paramlanguage = $_GET['lang'];
                $link1 = str_replace('lang=' . $paramlanguage, 'lang=' . $language, $link);
            } else {

                $link1 = $link . '&lang=' . $language . '';
            }
        } else {
            $link1 = $link . '?lang=' . $language . '';
        }
        echo $link1;
    }

    function language_switcher() {
        ?>
        <form>
            <select style="width:190px; font-size: 12px;" name="my_languages" id="my_languages" onchange="top.location.href='<?php change_interface_language('\'+this.form.my_languages.options[this.form.my_languages.selectedIndex].value+\''); ?>' ">

    <?php $chooselang = 'Choose language'; ?>
                <option value="<?php echo $_SESSION['get_language']; ?>"><?php echo __($chooselang); ?></option>
                <option value="el">Ελληνικά</option>
                <option value="en">English</option>
                <option value="it">Italiano</option>
                <option value="de">Deutsch</option>
                <option value="ee">Eesti</option>
                <option value="ru">Russian</option>
                <option value="lv">Latviešu</option>
                <option value="es">Español</option>
                <option value="fr">Français</option>
                <option value="tr">Türkçe</option>
            </select>
        </form>

    <?php
}

