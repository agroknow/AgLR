<?php
/**
 * All Item helper functions
 *
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2010
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka_ThemeHelpers
 * @subpackage ItemHelpers
 */

/**
 * @since 0.10
 * @uses current_user_tags()
 * @uses get_current_item()
 * @param Item|null $item Check for this specific item record (current item if null).
 * @return array
 */
function current_user_tags_for_item($item = null) {
    if (!$item) {
        $item = get_current_item();
    }
    // eventually, we need to not use current_user_tags because it is deprecated
    return current_user_tags($item);
}

/**
 * @since 0.10
 * @uses display_files()
 * @uses get_current_item()
 * @param array $options
 * @param array $wrapperAttributes
 * @param Item|null $item Check for this specific item record (current item if null).
 * @return string HTML
 */
function display_files_for_item($options = array(), $wrapperAttributes = array('class' => 'item-file'), $item = null) {
    if (!$item) {
        $item = get_current_item();
    }

    return display_files($item->Files, $options, $wrapperAttributes);
}

/**
 * Returns the HTML markup for displaying a random featured item.  Most commonly
 * used on the home page of public themes.
 *
 * @since 0.10
 * @param boolean $withImage Whether or not the featured item should have an image associated
 * with it.  If set to true, this will either display a clickable square thumbnail
 * for an item, or it will display "You have no featured items." if there are
 * none with images.
 * @return string HTML
 */
function display_random_featured_item($withImage = null) {
    $html = '<h2>' . __('Featured Item') . '</h2>';
    $html .= display_random_featured_items('1', $withImage);
    return $html;
}

/**
 * Retrieve the current Item record.
 *
 * @since 0.10
 * @throws Exception
 * @return Item
 */
function get_current_item() {
    if (!($item = __v()->item)) {
        throw new Exception(__('An item has not been set to be displayed on this theme page! Please see Omeka documentation for details.'));
    }

    return $item;
}

/**
 * Retrieve an Item object directly by its ID.
 *
 * Example of usage on a public theme page:
 *
 * $item = get_item_by_id(4);
 * set_current_item($item); // necessary to use item() and other similar theme API calls.
 * echo item('Dublin Core', 'Title');
 *
 * @since 0.10
 * @param integer $itemId
 * @return Item|null
 */
function get_item_by_id($itemId) {
    return get_db()->getTable('Item')->find($itemId);
}

/**
 * Retrieve a set of Item records corresponding to the criteria given by $params.
 *
 * This could be used on the public theme like so:
 *
 * set_items_for_loop(get_items('tags'=>'foo, bar', 'recent'=>true), 10);
 * while (loop_items()): ....
 *
 * @since 0.10
 * @see ItemTable::applySearchFilters()
 * @param array $params
 * @param integer $limit The maximum number of items to return.
 * @return array
 */
function get_items($params = array(), $limit = 10) {
    return get_db()->getTable('Item')->findBy($params, $limit);
}

/**
 * Retrieve the set of items for the current loop.
 *
 * @since 0.10
 * @return array
 */
function get_items_for_loop() {
    return __v()->items;
}

/**
 * Retrieve the next item in the database.
 *
 * @todo Should this look for the next item in the loop, or just via the database?
 * @since 0.10
 * @param Item|null Check for this specific item record (current item if null).
 * @return Item|null
 */
function get_next_item($item = null) {
    if (!$item) {
        $item = get_current_item();
    }
    return $item->next();
}

/**
 * @see get_previous_item()
 * @since 0.10
 * @param Item|null Check for this specific item record (current item if null).
 * @return Item|null
 */
function get_previous_item($item = null) {
    if (!$item) {
        $item = get_current_item();
    }
    return $item->previous();
}

/**
 * Determine whether or not there are any items in the database.
 *
 * @deprecated since 1.5
 * @since 0.10
 * @return boolean
 */
function has_items() {
    return (total_items() > 0);
}

/**
 * @since 0.10
 * @return boolean
 */
function has_items_for_loop() {
    $view = __v();
    return ($view->items and count($view->items));
}

/**
 * Retrieve the values for a given field in the current item.
 *
 * @since 0.10
 * @uses Omeka_View_Helper_RecordMetadata::_get() Contains instructions and
 * examples.
 * @uses Omeka_View_Helper_ItemMetadata::_getRecordMetadata() Contains a list of
 * all fields that do not belong to element sets, e.g. 'id', 'date modified', etc.
 * @param string $elementSetName
 * @param string $elementName
 * @param array $options
 * @param Item|null Check for this specific item record (current item if null).
 * @return string|array|null
 */
function item($elementSetName, $elementName = null, $options = array(), $item = null) {
    if (!$item) {
        $item = get_current_item();
    }
    return __v()->itemMetadata($item, $elementSetName, $elementName, $options);
}

/**
 * Determine whether or not the current item belongs to a collection.
 *
 * @since 0.10
 * @param string|null The name of the collection that the item would belong
 * to.  If null, then this will check to see whether the item belongs to
 * any collection.
 * @param Item|null Check for this specific item record (current item if null).
 * @return boolean
 */
function item_belongs_to_collection($name = null, $item = null) {
    //Dependency injection
    if (!$item) {
        $item = get_current_item();
    }

    return (($collection = $item->Collection)
            && (!$name || $collection->name == $name)
            && ($collection->public || has_permission('Collections', 'showNotPublic')));
}

/**
 * Retrieve a valid citation for the current item.
 *
 * Generally follows Chicago Manual of Style note format for webpages.  Does not
 * account for multiple creators or titles.
 *
 * @since  0.10
 * @param Item|null Check for this specific item record (current item if null).
 * @return string
 */
function item_citation($item = null) {
    if (!$item) {
        $item = get_current_item();
    }

    $creator = strip_formatting(item('Dublin Core', 'Creator', array(), $item));
    $title = strip_formatting(item('Dublin Core', 'Title', array(), $item));
    $siteTitle = strip_formatting(settings('site_title'));
    $itemId = item('id', null, array(), $item);
    $accessDate = date('F j, Y');
    $uri = html_escape(abs_item_uri($item));

    $cite = '';
    if ($creator) {
        $cite .= "$creator, ";
    }
    if ($title) {
        $cite .= "&#8220;$title,&#8221; ";
    }
    if ($siteTitle) {
        $cite .= "<em>$siteTitle</em>, ";
    }
    $cite .= "accessed $accessDate, ";
    $cite .= "$uri.";

    return apply_filters('item_citation', $cite, $item);
}

/**
 * Determine whether or not a specific element uses HTML.  By default this will
 * test the first element text, though it is possible to test against a different
 * element text by modifying the $index parameter.
 *
 * @since 0.10
 * @param string
 * @param string
 * @param integer
 * @param Item|null Check for this specific item record (current item if null).
 * @return boolean
 */
function item_field_uses_html($elementSetName, $elementName, $index = 0, $item = null) {
    if (!$item) {
        $item = get_current_item();
    }

    $textRecords = $item->getElementTextsByElementNameAndSetName($elementName, $elementSetName);
    $textRecord = @$textRecords[$index];

    return ($textRecord instanceof ElementText and $textRecord->isHtml());
}

/**
 * @see item_thumbnail()
 * @since 0.10
 * @param array $props
 * @param integer $index
 * @return string HTML
 */
function item_fullsize($props = array(), $index = 0, $item = null) {
    return item_image('fullsize', $props, $index, $item);
}

/**
 * Determine whether or not the item has any files associated with it.
 *
 * @since 0.10
 * @see has_files()
 * @uses Item::hasFiles()
 * @param Item|null Check for this specific item record (current item if null).
 * @return boolean
 */
function item_has_files($item = null) {
    if (!$item) {
        $item = get_current_item();
    }
    return $item->hasFiles();
}

/**
 * @since 0.10
 * @param Item|null Check for this specific item record (current item if null).
 * @return boolean
 */
function item_has_tags($item = null) {
    if (!$item) {
        $item = get_current_item();
    }
    return (count($item->Tags) > 0);
}

/**
 * Determine whether an item has an item type.
 *
 * If no $name is given, this will return true if the item has any item type
 * (items do not have to have an item type).  If $name is given, then this will
 * determine if an item has a specific item type.
 *
 * @since 0.10
 * @param string|null $name
 * @param Item|null Check for this specific item record (current item if null).
 * @return boolean
 */
function item_has_type($name = null, $item = null) {
    if (!$item) {
        $item = get_current_item();
    }

    $itemTypeName = item('Item Type Name', null, array(), $item);
    return ($name and ($itemTypeName == $name)) or (!$name and !empty($itemTypeName));
}

/**
 * Determine whether or not the item has a thumbnail image that it can display.
 *
 * @since 0.10
 * @param Item|null Check for this specific item record (current item if null).
 * @return void
 */
function item_has_thumbnail($item = null) {
    if (!$item) {
        $item = get_current_item();
    }
    return $item->hasThumbnail();
}

/**
 * Primarily used internally by other theme helpers, not intended to be used
 * within themes.  Plugin writers creating new helpers may want to use this
 * function to display a customized derivative image.
 *
 * @since 0.10
 * @param string $imageType
 * @param array $props
 * @param integer $index
 * @param Item|null Check for this specific item record (current item if null).
 * @return void
 */
function item_image($imageType, $props = array(), $index = 0, $item = null) {
    if (!$item) {
        $item = get_current_item();
    }

    $imageFile = get_db()->getTable('File')->findWithImages($item->id, $index);

    $width = @$props['width'];
    $height = @$props['height'];

    require_once 'Media.php';
    $media = new Omeka_View_Helper_Media;
    return $media->archive_image($imageFile, $props, $width, $height, $imageType);
}

/**
 * Returns the HTML for an item search form
 *
 * @param array $props
 * @param string $formActionUri
 * @return string
 */
function items_search_form($props = array(), $formActionUri = null) {
    return __v()->partial('items/advanced-search.php', array('isPartial' => true, 'formAttributes' => $props, 'formActionUri' => $formActionUri));
}

/**
 * @see item_thumbnail()
 * @since 0.10
 * @param array $props
 * @param integer $index
 * @param Item $item The item to which the image belongs
 * @return string HTML
 */
function item_square_thumbnail($props = array(), $index = 0, $item = null) {
    return item_image('square_thumbnail', $props, $index, $item);
}

/**
 * HTML for a thumbnail image associated with an item.  Default parameters will
 * use the first image, but that can be changed by modifying $index.
 *
 * @since 0.10
 * @uses item_image()
 * @param array $props A set of attributes for the <img /> tag.
 * @param integer $index The position of the file to use (starting with 0 for
 * the first file).
 * @param Item $item The item to which the image belongs
 * @return string HTML
 */
function item_thumbnail($props = array(), $index = 0, $item = null) {
    return item_image('thumbnail', $props, $index, $item);
}

/**
 * Loops through items assigned to the view.
 *
 * @since 0.10
 * @return mixed The current item
 */
function loop_items() {
    return loop_records('items', get_items_for_loop(), 'set_current_item');
}

/**
 * Loops through files assigned to the current item.
 *
 * @since 0.10
 * @return mixed The current file within the loop.
 * @param Item|null Check for this specific item record (current item if null).
 */
function loop_files_for_item($item = null) {
    if (!$item) {
        $item = get_current_item();
    }
    $files = $item->Files;
    return loop_records('files_for_item', $files, 'set_current_file');
}

/**
 * @since 0.10
 * @access private
 * @see loop_items()
 * @param Item
 * @return void
 */
function set_current_item(Item $item) {
    $view = __v();
    $view->previous_item = $view->item;
    $view->item = $item;
}

/**
 * @since 0.10
 * @param array $items
 */
function set_items_for_loop($items) {
    $view = __v();
    $view->items = $items;
}

/**
 * Retrieve the set of all metadata for the current item.
 *
 * @since 0.10
 * @uses Omeka_View_Helper_ItemMetadata
 * @param array $options Optional
 * @param Item|null Check for this specific item record (current item if null).
 * @return string|array
 */
function show_item_metadata(array $options = array(), $item = null) {
    if (!$item) {
        $item = get_current_item();
    }
    return __v()->itemMetadataList($item, $options);
}

/**
 * Returns the most recent items
 *
 * @param integer $num The maximum number of recent items to return
 * @return array
 */
function recent_items($num = 10) {
    return get_db()->getTable('Item')->findBy(array('recent' => true), $num);
}

/**
 * Returns a random featured item
 *
 * @since 7/3/08 This will retrieve featured items with or without images by
 *  default. The prior behavior was to retrieve only items with images by
 *  default.
 * @param boolean|null $hasImage
 * @return Item
 */
function random_featured_item($hasImage = null) {
    $item = random_featured_items('1', $hasImage);
    return $item[0];
}

/**
 * Returns the total number of items
 *
 * @return integer
 */
function total_items() {
    return get_db()->getTable('Item')->count();
}

/**
 * Returns multiple random featured item
 *
 * @since 1.4
 * @param integer $num The maximum number of recent items to return
 * @param boolean|null $hasImage
 * @return array $items
 */
function random_featured_items($num = 5, $hasImage = null) {
    return get_items(array('featured' => 1, 'random' => 1, 'hasImage' => $hasImage), $num);
}

function display_random_featured_items($num = 5, $hasImage = null) {
    $html = '';

    if ($randomFeaturedItems = random_featured_items($num, $hasImage)) {
        foreach ($randomFeaturedItems as $randomItem) {
            $itemTitle = item('Dublin Core', 'Title', array(), $randomItem);

            $html .= '<h3>' . link_to_item($itemTitle, array(), 'show', $randomItem) . '</h3>';

            if (item_has_thumbnail($randomItem)) {
                $html .= link_to_item(item_square_thumbnail(array(), 0, $randomItem), array('class' => 'image'), 'show', $randomItem);
            }

            if ($itemDescription = item('Dublin Core', 'Description', array('snippet' => 150), $randomItem)) {
                $html .= '<p class="item-description">' . $itemDescription . '</p>';
            }
        }
    } else {
        $html .= '<p>' . __('No featured items are available.') . '</p>';
    }

    return $html;
}

//////////////////////////  custom code for natural europe		//////////////////////////

function injestitem() {
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
    $db->query("SET NAMES 'utf8'");

    $date_modified = date("Y-m-d H:i:s");
    $item_type_id = 11;
    if (stripos($_POST['identifier'], ".jpg") > 0 or $_POST['format'] == "IMAGE" or $_POST['format'] == "image" or stripos($_POST['format'], "MAGE/") > 0 or stripos($_POST['format'], "mage/") > 0 or stripos($_POST['identifier'], ".gif") > 0 or stripos($_POST['identifier'], ".jpeg") > 0 or stripos($_POST['identifier'], ".png") > 0 or stripos($_POST['identifier'], ".tiff") > 0 or stripos($_POST['identifier'], ".tif") > 0 or stripos($_POST['identifier'], ".bmp") > 0 or stripos($_POST['identifier'], "content/thumbs/src") > 0 or $dataformatfromvoc['value'] == "IMAGE") {
        $item_type_id = 6;
    }
//print_r($_POST);break;

    $metadatarecordSql = "INSERT INTO omeka_items (item_type_id ,collection_id,featured,public,added) VALUES (?,?,?,?,?)";
//echo $metadatarecordSql;break;
    $execmetadatarecordSql = $db->query($metadatarecordSql, array($item_type_id, NULL, 0, 0, $date_modified));
    $execmetadatarecordSql = null;

    $lastExhibitIdSQL = "SELECT LAST_INSERT_ID() AS LAST_EXHIBIT_ID FROM omeka_items";
    $exec = $db->query($lastExhibitIdSQL);
    $row = $exec->fetch();
    $last_exhibit_id = $row["LAST_EXHIBIT_ID"];
    $exec = null;
    $_POST['title'] = json_decode(base64_decode($_POST['title']), true); //server side
    $titleforloop = $_POST['title'];

    $titlegen = '';
    $title1 = '';
    $titleinter = '';
    $titleen = '';
    $title = '';
    foreach ($titleforloop as $titleforloop) {
        if ($titleforloop['lang'] == $_SESSION['get_language']) {
            $titleinter = $titleforloop['value'];
        } elseif ($titleforloop['lang'] == 'en') {
            $titleen = $titleforloop['value'];
        } elseif (strlen($title) == 0) {
            $title1 = $titleforloop['value'];
        } else {
            $titlegen = $titleforloop['value'];
        }
    }
    if (strlen($titleinter) > 0) {
        $title = $titleinter;
    } elseif (strlen($titleen) > 0) {
        $title = $titleen;
    } elseif (strlen($title1) > 0) {
        $title = $title1;
    } elseif (strlen($titlegen) > 0) {
        $title = $titlegen;
    } else {
        $title = $_POST['title'];
    }
    $title = preg_replace('/(["\'])/ie', '', $title);

    $_POST['description'] = json_decode(base64_decode($_POST['description']), true); //server side
    $descriptionforloop = $_POST['description'];
    $descriptiongen = '';
    $description1 = '';
    $descriptioninter = '';
    $descriptionen = '';
    $description = '';
    foreach ($descriptionforloop as $descriptionforloop) {
        if ($descriptionforloop['lang'] == $_SESSION['get_language']) {
            $descriptioninter = $descriptionforloop['value'];
        } elseif ($descriptionforloop['lang'] == 'en') {
            $descriptionen = $descriptionforloop['value'];
        } elseif (strlen($description) == 0) {
            $description1 = $descriptionforloop['value'];
        } else {
            $descriptiongen = $descriptionforloop['value'];
        }
    }
    if (strlen($descriptioninter) > 0) {
        $description = $descriptioninter;
    } elseif (strlen($descriptionen) > 0) {
        $description = $descriptionen;
    } elseif (strlen($description1) > 0) {
        $description = $description1;
    } elseif (strlen($descriptiongen) > 0) {
        $description = $descriptiongen;
    } else {
        $description = $_POST['description'];
    }
    $description = preg_replace('/(["\'])/ie', '', $description);


    $metadatarecordSql = "INSERT INTO omeka_element_texts (record_id ,element_id,record_type_id,text) VALUES (?,?,?,?)";
//echo $metadatarecordSql;break;
    $execmetadatarecordSql = $db->query($metadatarecordSql, array($last_exhibit_id, 68, 2, $title));

    $metadatarecordSql = "INSERT INTO omeka_entities_relations (entity_id,relation_id,relationship_id,type,time) VALUES ('" . $_POST['user'] . "','" . $last_exhibit_id . "',1,'item','" . $date_modified . "')";
    $execmetadatarecordSql = $db->query($metadatarecordSql);

    /* ===================================INSERT record for METADATA=================================== */
    $metadatarecordSql = "INSERT INTO metadata_record (id, object_id, object_type) VALUES ('', " . $last_exhibit_id . ",'item')";
    $execmetadatarecordSql = $db->query($metadatarecordSql);

    $lastExhibitIdSQL = "SELECT LAST_INSERT_ID() AS LAST_EXHIBIT_ID FROM metadata_record";
    $exec = $db->query($lastExhibitIdSQL);
    $row = $exec->fetch();
    $last_record_id = $row["LAST_EXHIBIT_ID"];
    $exec = null;

    $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id) VALUES (?,?,?,?,?)";
    $execmetadatarecordSql = $db->query($metadatarecordSql, array(34, $_POST['source'], 'none', 1, $last_record_id));
    $exec = null;


    $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer) VALUES (?,?,?,?,?,?)";
//echo $metadatarecordSql;break;

    $titleforloop = $_POST['title'];
    if (is_array($titleforloop)) {
        foreach ($titleforloop as $titleforloop) {
            if (!strlen($titleforloop['lang']) > 0) {
                $titleforloop['lang'] = 'en';
            }
            $execmetadatarecordSql = $db->query($metadatarecordSql, array(6, $titleforloop['value'], $titleforloop['lang'], 1, $last_record_id, 1));
        }
    } else {
        $execmetadatarecordSql = $db->query($metadatarecordSql, array(6, $titleforloop, 'en', 1, $last_record_id, 1));
    }
    $titleforloop = $_POST['description'];
    if (is_array($titleforloop)) {
        foreach ($titleforloop as $titleforloop) {
            if (!strlen($titleforloop['lang']) > 0) {
                $titleforloop['lang'] = 'en';
            }
            $execmetadatarecordSql = $db->query($metadatarecordSql, array(8, $titleforloop['value'], $titleforloop['lang'], 1, $last_record_id, 1));
        }
    } else {
        $execmetadatarecordSql = $db->query($metadatarecordSql, array(8, $titleforloop, 'en', 1, $last_record_id, 1));
    }

    if (isset($_POST['keywords'])) {
        $_POST['keywords'] = json_decode(base64_decode($_POST['keywords']), true); //server side
        $keywords = $_POST['keywords'];
//echo $metadatarecordSql;break;
        $keymulti = 0;
        foreach ($keywords as $keywords) {
            $keymulti+=1;
            $execmetadatarecordSql = $db->query($metadatarecordSql, array(35, $keywords, 'en', $keymulti, $last_record_id, 1));
        }
    }
    if (isset($_POST['itemlanguage'])) {
        $itemlanguage = find_voc_rec_id($_POST['itemlanguage'], 23);
        if ($itemlanguage > 0) {
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, vocabulary_record_id, multi, record_id, parent_indexer,is_editable) VALUES ('7',NULL,'none'," . $itemlanguage . ",1, " . $last_record_id . ",1,1)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
        }
    }

    if (isset($_POST['rights']) and strlen($_POST['rights']) > 0) {

        if ($_POST['rights'] == 'http://creativecommons.org/publicdomain/mark/1.0/') {
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('9','no','none',1, " . $last_record_id . ",1,1)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
        } elseif ($_POST['rights'] == 'http://creativecommons.org/publicdomain/zero/1.0/') {
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('9','no','none',1, " . $last_record_id . ",1,1)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
        } elseif ($_POST['rights'] == 'http://creativecommons.org/licenses/by/3.0/') {
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('9','yes','none',1, " . $last_record_id . ",1,1)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('22','yes','none',1, " . $last_record_id . ",1,1)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('23','yes','none',1, " . $last_record_id . ",1,1)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
        } elseif ($_POST['rights'] == 'http://creativecommons.org/licenses/by-sa/3.0/') {
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('9','yes','none',1, " . $last_record_id . ",1,1)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('22','yes','none',1, " . $last_record_id . ",1,1)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('23','Yes, if others share alike','none',1, " . $last_record_id . ",1,1)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
        } elseif ($_POST['rights'] == 'http://creativecommons.org/licenses/by-nd/3.0/') {
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('9','yes','none',1, " . $last_record_id . ",1,1)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('22','yes','none',1, " . $last_record_id . ",1,1)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('23','no','none',1, " . $last_record_id . ",1,1)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
        } elseif ($_POST['rights'] == 'http://creativecommons.org/licenses/by-nc/3.0/') {
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('9','yes','none',1, " . $last_record_id . ",1,1)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('22','no','none',1, " . $last_record_id . ",1,1)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('23','yes','none',1, " . $last_record_id . ",1,1)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
        } elseif ($_POST['rights'] == 'http://creativecommons.org/licenses/by-nc-sa/3.0/') {
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('9','yes','none',1, " . $last_record_id . ",1,1)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('22','no','none',1, " . $last_record_id . ",1,1)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('23','Yes, if others share alike','none',1, " . $last_record_id . ",1,1)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
        } elseif ($_POST['rights'] == 'http://creativecommons.org/licenses/by-nc-nd/3.0/') {
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('9','yes','none',1, " . $last_record_id . ",1,1)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('22','no','none',1, " . $last_record_id . ",1,1)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('23','no','none',1, " . $last_record_id . ",1,1)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
        } elseif ($_POST['rights'] == 'http://www.europeana.eu/rights/rr-f/') {
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('9','yes','none',1, " . $last_record_id . ",1,1)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('24','no','none',1, " . $last_record_id . ",1,1)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
        } elseif ($_POST['rights'] == 'http://www.europeana.eu/rights/rr-p/') {
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('9','yes','none',1, " . $last_record_id . ",1,1)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('24','yes','none',1, " . $last_record_id . ",1,1)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
        } elseif ($_POST['rights'] == 'http://www.europeana.eu/rights/rr-r/') {
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('9','yes','none',1, " . $last_record_id . ",1,1)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
        }
    }

//libraries/omeka/record.php
    $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('32','" . $_POST['identifier'] . "','none',1, " . $last_record_id . ",1,0)";
    $execmetadatarecordSql = $db->query($metadatarecordSql);
    $exec = null;

//libraries/omeka/record.php
    $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, vocabulary_record_id, multi, record_id, parent_indexer,is_editable) VALUES ('33',NULL,'none',272,1, " . $last_record_id . ",1,1)";
    $execmetadatarecordSql = $db->query($metadatarecordSql);
    $exec = null;

    $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, vocabulary_record_id, multi, record_id, parent_indexer,is_editable) VALUES ('68',NULL,'none','305',1, " . $last_record_id . ",1,1)";
    $execmetadatarecordSql = $db->query($metadatarecordSql);
    $exec = null;

    $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('53','Parent Element','none',1, " . $last_record_id . ",1,0)";
    $execmetadatarecordSql = $db->query($metadatarecordSql);
    $exec = null;

    $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('54','URI','none',1, " . $last_record_id . ",1,0)";
    $execmetadatarecordSql = $db->query($metadatarecordSql);
    $exec = null;

    $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('55','" . $_POST['identifier'] . "','none',1, " . $last_record_id . ",1,0)";
    $execmetadatarecordSql = $db->query($metadatarecordSql);
    $exec = null;

    $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('60','Parent Element','none',1, " . $last_record_id . ",1,0)";
    $execmetadatarecordSql = $db->query($metadatarecordSql);
    $exec = null;

    $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('61','Natural_Europe_Schema','none',1, " . $last_record_id . ",1,0)";
    $execmetadatarecordSql = $db->query($metadatarecordSql);
    $exec = null;

    $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('62','Natural_Europe_" . $last_record_id . "','none',1, " . $last_record_id . ",1,0)";
    $execmetadatarecordSql = $db->query($metadatarecordSql);
    $exec = null;

    $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('63','Parent Element','none',1, " . $last_record_id . ",1,0)";
    $execmetadatarecordSql = $db->query($metadatarecordSql);
    $exec = null;

    $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, vocabulary_record_id, multi, record_id, parent_indexer,is_editable) VALUES ('64',NULL,'none',120,1, " . $last_record_id . ",1,0)";
    $execmetadatarecordSql = $db->query($metadatarecordSql);
    $exec = null;

///////////////////////vcard///////////////////////////
    if (isset($_POST['creator']) and strlen($_POST['creator']) > 0) {
        $vcard_name = $_POST['creator'];
        $vcard_surname = '';
        $vcard_email = '';
        $vcard_organization = '';

        $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('50','Parent Element','none',1, " . $last_record_id . ",1,0)";
        $execmetadatarecordSql = $db->query($metadatarecordSql);
        $exec = null;

        $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, vocabulary_record_id, multi, record_id, parent_indexer,is_editable) VALUES ('51',NULL,'none',96,1, " . $last_record_id . ",1,0)";
        $execmetadatarecordSql = $db->query($metadatarecordSql);
        $exec = null;

        if (strlen($vcard_name) > 0 or strlen($vcard_surname) > 0 or strlen($vcard_email) > 0 or strlen($vcard_organization) > 0) {

            $chechvcard = "select * from metadata_vcard WHERE name='" . $vcard_name . "' and surname='" . $vcard_surname . "' and email='" . $vcard_email . "' and organization='" . $vcard_organization . "'";
            $execchechvcard = $db->query($chechvcard);
            $result_chechvcard = $execchechvcard->fetch();
            $execchechvcard = null;

            if (strlen($result_chechvcard['id']) > 0) {

                $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,vcard_id,is_editable) VALUES ('52','author','none',1, " . $last_record_id . ",1," . $result_chechvcard['id'] . ",0)";
                $execmetadatarecordSql = $db->query($metadatarecordSql);
                $exec = null;
            } else {
                $chechvcardins = "insert into metadata_vcard SET name='" . $vcard_name . "',surname='" . $vcard_surname . "',email='" . $vcard_email . "',organization='" . $vcard_organization . "'";
                $execchechvcardins = $db->query($chechvcardins);
                $result_chechvcardins = $execchechvcardins->fetch();
                $execchechvcardins = null;

                $chechvcardnew = "select * from metadata_vcard WHERE name='" . $vcard_name . "' and surname='" . $vcard_surname . "' and email='" . $vcard_email . "' and organization='" . $vcard_organization . "'";
                $execchechvcardnew = $db->query($chechvcardnew);
                $result_chechvcardnew = $execchechvcardnew->fetch();
                $execchechvcardnew = null;

                $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,vcard_id,is_editable) VALUES ('52','author','none',1, " . $last_record_id . ",1," . $result_chechvcardnew['id'] . ",0)";
                $execmetadatarecordSql = $db->query($metadatarecordSql);
                $exec = null;
            }
        }//if isset one value from vcard
    }

    if (isset($_POST['provider']) and strlen($_POST['provider']) > 0) {
        $vcard_name = $_POST['provider'];
        $vcard_surname = '';
        $vcard_email = '';
        $vcard_organization = '';

        $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('50','Parent Element','none',2, " . $last_record_id . ",1,0)";
        $execmetadatarecordSql = $db->query($metadatarecordSql);
        $exec = null;


        $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, vocabulary_record_id, multi, record_id, parent_indexer,is_editable) VALUES ('51',NULL,'none',97,2, " . $last_record_id . ",1,0)";
        $execmetadatarecordSql = $db->query($metadatarecordSql);
        $exec = null;

        if (strlen($vcard_name) > 0 or strlen($vcard_surname) > 0 or strlen($vcard_email) > 0 or strlen($vcard_organization) > 0) {

            $chechvcard = "select * from metadata_vcard WHERE name='" . $vcard_name . "' and surname='" . $vcard_surname . "' and email='" . $vcard_email . "' and organization='" . $vcard_organization . "'";
            $execchechvcard = $db->query($chechvcard);
            $result_chechvcard = $execchechvcard->fetch();
            $execchechvcard = null;

            if (strlen($result_chechvcard['id']) > 0) {

                $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,vcard_id,is_editable) VALUES ('52','author','none',2, " . $last_record_id . ",1," . $result_chechvcard['id'] . ",0)";
                $execmetadatarecordSql = $db->query($metadatarecordSql);
                $exec = null;
            } else {
                $chechvcardins = "insert into metadata_vcard SET name='" . $vcard_name . "',surname='" . $vcard_surname . "',email='" . $vcard_email . "',organization='" . $vcard_organization . "'";
                $execchechvcardins = $db->query($chechvcardins);
                $result_chechvcardins = $execchechvcardins->fetch();
                $execchechvcardins = null;

                $chechvcardnew = "select * from metadata_vcard WHERE name='" . $vcard_name . "' and surname='" . $vcard_surname . "' and email='" . $vcard_email . "' and organization='" . $vcard_organization . "'";
                $execchechvcardnew = $db->query($chechvcardnew);
                $result_chechvcardnew = $execchechvcardnew->fetch();
                $execchechvcardnew = null;

                $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,vcard_id,is_editable) VALUES ('52','author','none',2, " . $last_record_id . ",1," . $result_chechvcardnew['id'] . ",0)";
                $execmetadatarecordSql = $db->query($metadatarecordSql);
                $exec = null;
            }
        }//if isset one value from vcard
    }


    $entityuser = current_user(); //print_r($entityuser); break;
    $vcard_name = $entityuser['first_name'];
    $vcard_surname = $entityuser['last_name'];
    $vcard_email = $entityuser['email'];
    $vcard_organization = $entityuser['institution'];

    if (strlen($vcard_name) > 0 or strlen($vcard_surname) > 0 or strlen($vcard_email) > 0 or strlen($vcard_organization) > 0) {

        $chechvcard = "select * from metadata_vcard WHERE name='" . $vcard_name . "' and surname='" . $vcard_surname . "' and email='" . $vcard_email . "' and organization='" . $vcard_organization . "'";
        $execchechvcard = $db->query($chechvcard);
        $result_chechvcard = $execchechvcard->fetch();
        $execchechvcard = null;

        if (strlen($result_chechvcard['id']) > 0) {

            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,vcard_id,is_editable) VALUES ('65','creator','none',1, " . $last_record_id . ",1," . $result_chechvcard['id'] . ",0)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
        } else {
            $chechvcardins = "insert into metadata_vcard SET name='" . $vcard_name . "',surname='" . $vcard_surname . "',email='" . $vcard_email . "',organization='" . $vcard_organization . "'";
            $execchechvcardins = $db->query($chechvcardins);
            $result_chechvcardins = $execchechvcardins->fetch();
            $execchechvcardins = null;

            $chechvcardnew = "select * from metadata_vcard WHERE name='" . $vcard_name . "' and surname='" . $vcard_surname . "' and email='" . $vcard_email . "' and organization='" . $vcard_organization . "'";
            $execchechvcardnew = $db->query($chechvcardnew);
            $result_chechvcardnew = $execchechvcardnew->fetch();
            $execchechvcardnew = null;

            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,vcard_id,is_editable) VALUES ('65','creator','none',1, " . $last_record_id . ",1," . $result_chechvcardnew['id'] . ",0)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
        }
    }//if isset one value from vcard
///////////////////////end vcard///////////////////////////


    $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('66','" . $date_modified . "','none',1, " . $last_record_id . ",1,0)";
    $execmetadatarecordSql = $db->query($metadatarecordSql);
    $exec = null;



    $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('67','NE AP v1.0','none',1, " . $last_record_id . ",1,0)";
    $execmetadatarecordSql = $db->query($metadatarecordSql);
    $exec = null;




    /* ===================================INSERT record for METADATA=================================== */


    return $last_exhibit_id;
}

function savemetadataitem($postvariable = NULL, $object_type = 'item') {

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

    $maxIdSQL = "select * from metadata_record where object_id=" . $_POST['item_id'] . " and object_type='" . $object_type . "'";
    $exec = $db->query($maxIdSQL);
    $row = $exec->fetch();
    $record_id = $row["id"];
    $exec = null;

    $metadataFile = Zend_Registry::get('metadataFile'); /////read metadata file

    if (isset($_POST['item_url']) and $metadataFile[metadata_schema_resources][element_hierarchy_location] != false and $metadataFile[metadata_schema_resources][element_hierarchy_identifier_entry] != false) {
        $_POST[$metadataFile[metadata_schema_resources][element_hierarchy_location] . '_1'] = $_POST['item_url'];
        $_POST[$metadataFile[metadata_schema_resources][element_hierarchy_identifier_entry] . '_1'] = $_POST['item_url'];
    }
//print_r($_POST);break;
    foreach ($_POST as $var => $value) {
        $var12 = explode("_", $var); //split form name at _
        if ($var != 'item_id' and $var != 'title' and $var != 'delete_files' and $var != 'Pages' and $var != 'hdnLine' and $var != 'hdnLine_group_total' and $var != 'hdnLine_group_vcard' and $var != 'hdnLine_group_total_parent' and $var != 'slug' and $var != 'public' and $var != 'Sections' and $var != 'save_exhibit' and $var != 'date_modified' and $var != 'save_meta' and $var != 'item_url' and $var != 'collection_id' and $var12[0] != 'translatedanalytics' and $var12[0] != 'fortranslationanalytics' and $var12[0] != 'translatedanalyticslan' and $var12[0] != 'fortranslationanalyticslan' and $var12[0] != 'translatedanalyticsservice') {
            $var1 = explode("_", $var); //split form name at _
            if ($var1[0] == 'vcard') { //if is vcard!!!
                $var = $var1[2];
                $varmulti = $var1[3];
                $varlan = NULL;
            } else {
                $var = $var1[0];
                $varmulti = $var1[1];
                $varlan = $var1[3];
            }



            if ($varlan != 'lan' and $var != 'hdnLine') { //not get in if is language name at form or name is hdnline
                if (isset($_POST[$var . '_' . $var1[1] . '_' . $var1[2] . '_lan'])) {
                    $language = $_POST[$var . '_' . $var1[1] . '_' . $var1[2] . '_lan'];
                    $parent_indexer = 1;
                } else {
                    if (isset($var1[2]) and $var1[2] > 0) {
                        $parent_indexer = $var1[2];
                    } else {
                        $parent_indexer = 1;
                    }
                    $language = 'none';
                }//langueage for this form name


                if ($var == $metadataFile[metadata_schema_resources][element_hierarchy_title] and $language == 'en') {
                    $exhibit_title_from_metadata = $value;
                }//title gia pathway


                $maxIdSQL = "select * from metadata_element_hierarchy where id=" . $var . "";
                $exec = $db->query($maxIdSQL);
                $result_multi = $exec->fetch();
//$exec=null;
//if($result_multi['max_occurs']>0){ $multi=$var1[1]; } else{$multi=1;}




                if ($var1[0] == 'vcard') { //if is vcard!!!
                    if ($var1[1] == 'general') {
                        $vcard_name = addslashes(htmlspecialchars($_POST[$var1[0] . '_name_' . $var1[2] . '_' . $var1[3] . '_' . $var1[4] . '']));
                        $vcard_surname = addslashes(htmlspecialchars($_POST[$var1[0] . '_surname_' . $var1[2] . '_' . $var1[3] . '_' . $var1[4] . '']));
                        $vcard_email = addslashes(htmlspecialchars($_POST[$var1[0] . '_email_' . $var1[2] . '_' . $var1[3] . '_' . $var1[4] . '']));
                        $vcard_organization = addslashes(htmlspecialchars($_POST[$var1[0] . '_organization_' . $var1[2] . '_' . $var1[3] . '_' . $var1[4] . '']));

                        if (strlen($vcard_name) > 0 or strlen($vcard_surname) > 0 or strlen($vcard_email) > 0 or strlen($vcard_organization) > 0) {

                            $chechvcard = "select * from metadata_vcard WHERE name='" . $vcard_name . "' and surname='" . $vcard_surname . "' and email='" . $vcard_email . "' and organization='" . $vcard_organization . "'";
                            $execchechvcard = $db->query($chechvcard);
                            $result_chechvcard = $execchechvcard->fetch();
                            $execchechvcard = null;

                            if (strlen($result_chechvcard['id']) > 0) {

                                $maxIdSQL = "insert into metadata_element_value SET element_hierarchy=" . $var . ",value='Vcard Element',language_id='" . $language . "',record_id=" . $record_id . ",multi=" . $varmulti . ",parent_indexer=" . $var1[4] . ",vcard_id=" . $result_chechvcard['id'] . " ON DUPLICATE KEY UPDATE vcard_id=" . $result_chechvcard['id'] . "";

                                //echo $maxIdSQL."<br>"; 
                                $exec = $db->query($maxIdSQL);
                                $result_multi = $exec->fetch();
                            } else {
                                $chechvcardins = "insert into metadata_vcard SET name='" . $vcard_name . "',surname='" . $vcard_surname . "',email='" . $vcard_email . "',organization='" . $vcard_organization . "'";
                                $execchechvcardins = $db->query($chechvcardins);
                                $result_chechvcardins = $execchechvcardins->fetch();
                                $execchechvcardins = null;

                                $chechvcardnew = "select * from metadata_vcard WHERE name='" . $vcard_name . "' and surname='" . $vcard_surname . "' and email='" . $vcard_email . "' and organization='" . $vcard_organization . "'";
                                $execchechvcardnew = $db->query($chechvcardnew);
                                $result_chechvcardnew = $execchechvcardnew->fetch();
                                $execchechvcardnew = null;

                                $maxIdSQL = "insert into metadata_element_value SET element_hierarchy=" . $var . ",value='Vcard Element',language_id='" . $language . "',record_id=" . $record_id . ",multi=" . $varmulti . ",parent_indexer=" . $var1[4] . ",vcard_id=" . $result_chechvcardnew['id'] . " ON DUPLICATE KEY UPDATE vcard_id=" . $result_chechvcardnew['id'] . "";

                                //echo $maxIdSQL."<br>"; 
                                $exec = $db->query($maxIdSQL);
                                $result_multi = $exec->fetch();
                            }
                        }//if isset one value from vcard
                    }//if is general
                } else { //if is vcard!!!		
                    if ($result_multi['datatype_id'] === 2) {
                        $value = 'Parent Element';
                    }

                    if (strlen($value) > 0) {


                        $maxIdSQL_check_if_voc = "select * from metadata_element_hierarchy where id=" . $var . " ";
//echo "<br><br>".$maxIdSQL_check_if_voc."<br>"; 
                        $exec_check_if_voc = $db->query($maxIdSQL_check_if_voc);
                        $result_check_if_voc = $exec_check_if_voc->fetch(); //echo $result_check_if_voc['datatype_id']."<br>";
                        if ($result_check_if_voc['datatype_id'] == 6) {
                            $vocabulary_record_id = $value;
                            $value = NULL;
                            $classification_id = NULL;
                        } elseif ($result_check_if_voc['datatype_id'] == 5) {
                            $vocabulary_record_id = NULL;
                            $classification_id = $value;
                            $value = NULL;
                        } else {
                            $vocabulary_record_id = NULL;
                            $classification_id = NULL;
                            $value = $value;
                            $value = htmlspecialchars($value);
                            //$value = addslashes($value);
                        }

//$maxIdSQL = "insert into metadata_element_value SET element_hierarchy=" . $var . ",value='" . $value . "',language_id='" . $language . "',record_id=" . $record_id . ",multi=" . $varmulti . ",parent_indexer=" . $parent_indexer . ",vocabulary_record_id=" . $vocabulary_record_id . ",classification_id='" . $classification_id . "' ON DUPLICATE KEY UPDATE value='" . $value . "',vocabulary_record_id=" . $vocabulary_record_id . ",classification_id='" . $classification_id . "'";
//$mainAttributesSql="INSERT INTO omeka_element_texts (record_id ,record_type_id ,element_id,text) VALUES (?,?,?,?)";
                        $maxIdSQL = "insert into metadata_element_value (element_hierarchy,value,language_id,record_id,multi,parent_indexer,vocabulary_record_id,classification_id) VALUES (?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE value=?,vocabulary_record_id=?,classification_id=?";
                        $exec = $db->exec($maxIdSQL, array($var, $value, $language, $record_id, $varmulti, $parent_indexer, $vocabulary_record_id, $classification_id, $value, $vocabulary_record_id, $classification_id)); //title
//echo $maxIdSQL."<br>"; 
//$exec=$db->query($maxIdSQL);
//$result_multi=$exec->fetch();
                        $exec = null;
                    }//if strlen >1 if exist value
                }//if is vcard!!!		
            }//end not get in if is language name at form 
        }
    }
    if ($metadataFile[metadata_schema_resources][element_hierarchy_metadata_language] == false) {
        $metadataFile[metadata_schema_resources][element_hierarchy_metadata_language] = 0;
    }
    if ($metadataFile[metadata_schema_resources][element_hierarchy_title] == false) {
        $metadataFile[metadata_schema_resources][element_hierarchy_title] = 0;
    }
    $sqllan = "SELECT b.value FROM metadata_element_value as a inner join metadata_vocabulary_record as b on b.id=a.vocabulary_record_id WHERE a.record_id=" . $record_id . " and element_hierarchy=" . $metadataFile[metadata_schema_resources][element_hierarchy_metadata_language] . "  "; //echo $sqllan; break;
    $execlan = $db->query($sqllan);
    $result_multi = $execlan->fetch();
    $execlan = null;
    $sqllan4 = "SELECT * FROM metadata_element_value WHERE record_id=" . $record_id . " and element_hierarchy=" . $metadataFile[metadata_schema_resources][element_hierarchy_title] . " and language_id='" . $result_multi['value'] . "'"; //echo $sqllan; break;
    $execlan4 = $db->query($sqllan4);
    $result_multi4 = $execlan4->fetch();
    $execlan4 = null;
    $sqllan2 = "SELECT * FROM metadata_element_value WHERE record_id=" . $record_id . " and element_hierarchy=" . $metadataFile[metadata_schema_resources][element_hierarchy_title] . " and language_id='en'"; //echo $sqllan; break;
    $execlan2 = $db->query($sqllan2);
    $result_multi2 = $execlan2->fetch();
    $execlan2 = null;
    if (strlen($result_multi4['value']) > 0) {
        $sqllan3 = "SELECT * FROM metadata_element_value WHERE record_id=" . $record_id . " and element_hierarchy=" . $metadataFile[metadata_schema_resources][element_hierarchy_title] . " and language_id='" . $result_multi['value'] . "'"; //echo $sqllan; break;
    } elseif (strlen($result_multi2['value']) > 0) {
        $sqllan3 = "SELECT * FROM metadata_element_value WHERE record_id=" . $record_id . " and element_hierarchy=" . $metadataFile[metadata_schema_resources][element_hierarchy_title] . " and language_id='en'"; //echo $sqllan; break;
    } else {
        $sqllan3 = "SELECT * FROM metadata_element_value WHERE record_id=" . $record_id . " and element_hierarchy=" . $metadataFile[metadata_schema_resources][element_hierarchy_title] . " LIMIT 0,1";
    }
//echo $sqllan3; break;
    $execlan3 = $db->query($sqllan3);
    $result_multi3 = $execlan3->fetch();

    $exhibit_title_from_metadata = $result_multi3['value']; //title gia pathway


    $maxIdSQL = "update omeka_element_texts SET text=? where record_id=? and element_id=?";
    $exec = $db->exec($maxIdSQL, array($exhibit_title_from_metadata, $_POST['item_id'], 68)); //title

    $maxIdSQL = "update metadata_record SET date_modified='" . $_POST['date_modified'] . "',validate='" . $_POST['public'] . "' where object_id=" . $_POST['item_id'] . " and object_type='" . $object_type . "'";
    $exec = $db->query($maxIdSQL);

    $maxIdSQL = "update omeka_items SET public=" . $_POST['public'] . ",modified='" . $_POST['date_modified'] . "' where id=" . $_POST['item_id'] . "";
    $exec = $db->query($maxIdSQL); //break;
//$result_multi=$exec->fetch();
    $exec = null;

//$result_teaser2=mysql_query("update omeka_exhibits SET title='".$exhibit_title_from_metadata."',slug='".$_POST['slug']."',public=".$_POST['public'].",date_modified='".$_POST['date_modified']."' where id=".$_POST['exhibit_id']."");  //update exhibit table

    return $_POST['item_id'];
}

function savenewitem($itid, $formtype) {

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


    $itemtdb = $db->Items;

    $maxIdSQL = "SELECT MAX(id) AS MAX_ID FROM " . $itemtdb . " LIMIT 0,1";
    $exec = $db->query($maxIdSQL);
    $row = $exec->fetch();
    $max_id = $row["MAX_ID"];
    $exec = null;

//print_r($_FILES['file']['type']['0']); break; 

    if (strlen($_POST['title']) > 0) {
        $_POST['Elements']['68']['0']['text'] = $_POST['title'];
    }
    if (strlen($_POST['Elements']['68']['0']['text']) > 0) {
        $path_title = $_POST['Elements']['68']['0']['text'];
    } else {
        $path_title = "resource-title-" . $max_id . "";
        $_POST['Elements']['68']['0']['text'] = "resource-title-" . $max_id . "";
    }
    if ($_POST['description']) {
        $path_description = $_POST['description'];
    } else {
        $path_description = "";
    }
    if ($_POST['link']) {
        $path_url = $_POST['link'];
    } else {
        $path_url = "";
    }
    if ($_POST['type']) {
        $formtype = $_POST['type'];
    } else {
        $formtype = "0";
    }
    if ($formtype == 11) {
        $formetypetext = 'text/html';
        $formetypetext = find_voc_rec_id($formetypetext, 21);
    } elseif (isset($_FILES['file']['type']['0'])) {
        $formetypetext = FiletypeMapping($_FILES['file']['type']['0']);
        if (stripos(' ' . $_FILES['file']['type']['0'], "image") > 0) {
            $formtype = 6;
        } else {
            $formtype = 20;
        }
    } else {
        $formetypetext = "";
    }
//if($_POST['Elements']['68']['0']['text']){$path_title=addslashes($_POST['Elements']['68']['0']['text']);} else{$path_title="resource-title-".$max_id."";}
    if ($_POST['public']) {
        $path_public = $_POST['public'];
    } else {
        $path_public = "0";
    }

    $date_modified = date("Y-m-d H:i:s");
    $mainAttributesSql = "INSERT INTO $itemtdb (featured,item_type_id,public,modified,added) VALUES (0," . $formtype . ",'" . $_POST['public'] . "','" . $date_modified . "','" . $date_modified . "')";
//echo $mainAttributesSql; break;
    $db->exec($mainAttributesSql);



    $lastExhibitIdSQL = "SELECT LAST_INSERT_ID() AS LAST_EXHIBIT_ID FROM " . $itemtdb;
    $exec = $db->query($lastExhibitIdSQL);
    $row = $exec->fetch();
    $last_exhibit_id = $row["LAST_EXHIBIT_ID"];
    $exec = null;

    $entitiesRelationsdb = $db->EntitiesRelations;
    $entity_id = current_user();
    $entitiesRelationsSql = "INSERT INTO " . $entitiesRelationsdb . " (entity_id, relation_id, relationship_id, type, time) VALUES (" . $entity_id->entity_id . ", " . $last_exhibit_id . ",1,'Item','" . date("Y-m-d H:i:s") . "')";
    $exec = $db->query($entitiesRelationsSql);

    $path_title = htmlspecialchars($path_title);
    //$path_title = addslashes($path_title);
    $path_description = htmlspecialchars($path_description);
    //$path_description = addslashes($path_description);
    $path_url = htmlspecialchars($path_url);
    //$path_url = addslashes($path_url);


    $mainAttributesSql = "INSERT INTO omeka_element_texts (record_id ,record_type_id ,element_id,text) VALUES (?,?,?,?)";
//echo $mainAttributesSql;break;
    $db->exec($mainAttributesSql, array($last_exhibit_id, 2, 68, $path_title)); //title
    $db->exec($mainAttributesSql, array($last_exhibit_id, 2, 59, $path_description)); //description
    $db->exec($mainAttributesSql, array($last_exhibit_id, 2, 28, $path_url)); //path url
//$db->exec($mainAttributesSql);

    /* ===================================INSERT record for METADATA=================================== */
    $metadatarecordSql = "INSERT INTO metadata_record (id, object_id, object_type,date_modified,validate) VALUES ('', " . $last_exhibit_id . ",'item','" . $date_modified . "'," . $path_public . ")";
    $execmetadatarecordSql = $db->query($metadatarecordSql);

    $metadataFile = Zend_Registry::get('metadataFile'); /////read metadata file

    $lastExhibitIdSQL = "SELECT LAST_INSERT_ID() AS LAST_EXHIBIT_ID FROM metadata_record";
    $exec = $db->query($lastExhibitIdSQL);
    $row = $exec->fetch();
    $last_record_id = $row["LAST_EXHIBIT_ID"];
    $exec = null;


    $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer) VALUES (?,?,?,?,?,?)";
    if ($metadataFile[metadata_schema_resources][element_hierarchy_description] != false) {
        $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_description], $path_description, 'en', 1, $last_record_id, 1)); ///description in metadata record  
    }
    if ($metadataFile[metadata_schema_resources][element_hierarchy_title] != false) {
        $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_title], $path_title, 'en', 1, $last_record_id, 1)); ///title in metadata record
    }

    $execmetadatarecordSql = null;

//libraries/omeka/record.php
    $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES (?,?,?,?,?,?,?)";
    if ($metadataFile[metadata_schema_resources][element_hierarchy_location] != false) {
        $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_location], $path_url, 'none', 1, $last_record_id, 1, 0)); ///location in metadata record
    }
    $execmetadatarecordSql = null;

//libraries/omeka/record.php
    $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, vocabulary_record_id, multi, record_id, parent_indexer,is_editable) VALUES (?,?,?,?,?,?,?,?)";
    if ($metadataFile[metadata_schema_resources][element_hierarchy_format] != false) {
        $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_format], NULL, 'none', $formetypetext, 1, $last_record_id, 1, 1)); /////format type
    }
    if ($metadataFile[metadata_schema_resources][element_hierarchy_metadata_language] != false and $metadataFile[metadata_schema_resources][vocabulary_record_languages_english] != false) {
        $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_metadata_language], NULL, 'none', $metadataFile[metadata_schema_resources][vocabulary_record_languages_english], 1, $last_record_id, 1, 1)); /////metadata language
    }
    $execmetadatarecordSql = null;

    $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES (?,?,?,?,?,?,?)";
    if ($metadataFile[metadata_schema_resources][element_hierarchy_identifier_parent] != false) { ///if identifier parent exist
        $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_identifier_parent], 'Parent Element', 'none', 1, $last_record_id, 1, 0)); /////identifier parent

        if ($metadataFile[metadata_schema_resources][element_hierarchy_identifier_catalog] != false) {
            $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_identifier_catalog], 'URI', 'none', 1, $last_record_id, 1, 0)); /////identifier catalog
        }

        if ($metadataFile[metadata_schema_resources][element_hierarchy_identifier_entry] != false) {
            $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_identifier_entry], $path_url, 'none', 1, $last_record_id, 1, 0)); /////identifier entry
        }
    }
    if ($metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_parent] != false) { ///if medatadata-identifier parent exist
        $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_parent], 'Parent Element', 'none', 1, $last_record_id, 1, 0)); /////medatadata-identifier parent

        if ($metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_catalog] != false and $metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_catalog_value] != false) {
            $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_catalog], $metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_catalog_value], 'none', 1, $last_record_id, 1, 0)); /////medatadata-identifier catalog
        }

        if ($metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_entry] != false and $metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_entry_value_prefix] != false) {
            $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_entry], $metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_entry_value_prefix] . '' . $last_record_id, 'none', 1, $last_record_id, 1, 0)); /////medatadata-identifier entry
        }
    }
    if ($metadataFile[metadata_schema_resources][element_hierarchy_metadata_schema] != false and $metadataFile[metadata_schema_resources][element_hierarchy_metadata_schema_id] != false) {///if medatadata-schema exist
        $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_metadata_schema_id], $metadataFile[metadata_schema_resources][element_hierarchy_metadata_schema], 'none', 1, $last_record_id, 1, 0)); /////medatadata-schema   
    }
    $execmetadatarecordSql = null;

    if ($metadataFile[metadata_schema_resources][element_hierarchy_metadata_creator_parent] != false) { ///if medatadata-creator parent exist
        $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES (" . $metadataFile[metadata_schema_resources][element_hierarchy_metadata_creator_parent] . ",'Parent Element','none',1, " . $last_record_id . ",1,0)";
        $execmetadatarecordSql = $db->query($metadatarecordSql);
        $execmetadatarecordSql = null;

        if ($metadataFile[metadata_schema_resources][element_hierarchy_metadata_creator_role] != false and $metadataFile[metadata_schema_resources][element_hierarchy_metadata_creator_role_value] != false) {  /////metadata creator role
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, vocabulary_record_id, multi, record_id, parent_indexer,is_editable) VALUES (" . $metadataFile[metadata_schema_resources][element_hierarchy_metadata_creator_role] . ",NULL,'none'," . $metadataFile[metadata_schema_resources][element_hierarchy_metadata_creator_role_value] . ",1, " . $last_record_id . ",1,0)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $execmetadatarecordSql = null;
        }

        if ($metadataFile[metadata_schema_resources][element_hierarchy_metadata_creator_vcard] != false) { /////metdata creator vcard
///////////////////////vcard///////////////////////////
            $entityuser = current_user(); //print_r($entityuser); break;
            $vcard_name = $entityuser['first_name'];
            $vcard_surname = $entityuser['last_name'];
            $vcard_email = $entityuser['email'];
            $vcard_organization = $entityuser['institution'];

            if (strlen($vcard_name) > 0 or strlen($vcard_surname) > 0 or strlen($vcard_email) > 0 or strlen($vcard_organization) > 0) {

                $chechvcard = "select * from metadata_vcard WHERE name='" . $vcard_name . "' and surname='" . $vcard_surname . "' and email='" . $vcard_email . "' and organization='" . $vcard_organization . "'";
                $execchechvcard = $db->query($chechvcard);
                $result_chechvcard = $execchechvcard->fetch();
                $execchechvcard = null;

                if (strlen($result_chechvcard['id']) > 0) {

                    $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,vcard_id,is_editable) VALUES (" . $metadataFile[metadata_schema_resources][element_hierarchy_metadata_creator_vcard] . ",'creator','none',1, " . $last_record_id . ",1," . $result_chechvcard['id'] . ",0)";
                    $execmetadatarecordSql = $db->query($metadatarecordSql);
                    $exec = null;
                } else {
                    $chechvcardins = "insert into metadata_vcard SET name='" . $vcard_name . "',surname='" . $vcard_surname . "',email='" . $vcard_email . "',organization='" . $vcard_organization . "'";
                    $execchechvcardins = $db->query($chechvcardins);
                    $result_chechvcardins = $execchechvcardins->fetch();
                    $execchechvcardins = null;

                    $chechvcardnew = "select * from metadata_vcard WHERE name='" . $vcard_name . "' and surname='" . $vcard_surname . "' and email='" . $vcard_email . "' and organization='" . $vcard_organization . "'";
                    $execchechvcardnew = $db->query($chechvcardnew);
                    $result_chechvcardnew = $execchechvcardnew->fetch();
                    $execchechvcardnew = null;

                    $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,vcard_id,is_editable) VALUES (" . $metadataFile[metadata_schema_resources][element_hierarchy_metadata_creator_vcard] . ",'creator','none',1, " . $last_record_id . ",1," . $result_chechvcardnew['id'] . ",0)";
                    $execmetadatarecordSql = $db->query($metadatarecordSql);
                    $exec = null;
                }
            }//if isset one value from vcard
///////////////////////end vcard///////////////////////////
        }

        if ($metadataFile[metadata_schema_resources][element_hierarchy_metadata_creator_date] != false) { /////metdata creator vcard
            $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES (" . $metadataFile[metadata_schema_resources][element_hierarchy_metadata_creator_date] . ",'" . $date_modified . "','none',1, " . $last_record_id . ",1,0)";
            $execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
        }
    }///////end of if exist metadata - creator
    $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES (?,?,?,?,?,?,?)";
    //////////////////////rights standard values/////////////////////////////
    if ($metadataFile[metadata_schema_resources][element_hierarchy_metadata_rights_copyrights_and_other_restrictions_id] != false and $metadataFile[metadata_schema_resources][element_hierarchy_metadata_rights_copyrights_and_other_restrictions_value] != false) {
        $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_metadata_rights_copyrights_and_other_restrictions_id], $metadataFile[metadata_schema_resources][element_hierarchy_metadata_rights_copyrights_and_other_restrictions_value], 'none', 1, $last_record_id, 1, NULL)); ///location in metadata record
    }
    if ($metadataFile[metadata_schema_resources][element_hierarchy_metadata_rights_cost_id] != false and $metadataFile[metadata_schema_resources][element_hierarchy_metadata_rights_cost_value] != false) {
        $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_metadata_rights_cost_id], $metadataFile[metadata_schema_resources][element_hierarchy_metadata_rights_cost_value], 'none', 1, $last_record_id, 1, NULL)); ///location in metadata record
    }
    if ($metadataFile[metadata_schema_resources][element_hierarchy_metadata_rights_description_id] != false and $metadataFile[metadata_schema_resources][element_hierarchy_metadata_rights_description_value] != false) {
        $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_metadata_rights_description_id], $metadataFile[metadata_schema_resources][element_hierarchy_metadata_rights_description_value], 'en', 1, $last_record_id, 1, NULL)); ///location in metadata record
    }

    return $last_exhibit_id;
}

function deleteitemlomid($item_eid) {
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

    $maxIdSQL = "DELETE from metadata_record where object_id=" . $item_eid . " and object_type='item'";
//echo $maxIdSQL;break;
    $exec = $db->query($maxIdSQL);
//$result_multi=$exec->fetchAll();
}

function deleteteasers($item_eid) {
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

    $maxIdSQL = "DELETE from omeka_teasers where item_id=" . $item_eid . " ";
//echo $maxIdSQL;break;
    $exec = $db->query($maxIdSQL);
//$result_multi=$exec->fetchAll();
}

function return_user_of_item($it_id) {
    require_once 'Zend/Db.php';

    $configSQL = new Zend_Config_Ini('./db.ini', 'database');

    $params = array(
        'host' => $configSQL->host,
        'dbname' => $configSQL->name,
        'username' => $configSQL->username,
        'password' => $configSQL->password,
        'charset' => $configSQL->charset);
    $db = Zend_Db::factory('Mysqli', $params);
    $db->query("SET NAMES 'utf8'");


    $select = $db->select();
    $select->from(array('f' => 'omeka_entities_relations'), array('entity_id', 'id'));
    $select->join(array('sec' => 'omeka_entities'), 'f.entity_id=sec.id', array('first_name', 'last_name'));
    $select->where('f.relation_id = ?', $it_id);
    $select->where('f.type = ?', 'Item');
    $select->where('f.relationship_id = ?', '1');
    $select->order(array('f.id ASC'));
    $rowset = $db->fetchRow($select);
    $name = $rowset['last_name'] . " " . $rowset['first_name'];
    return $name;
}

function saveomekasql($sql) {

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

//echo $sql; break;
    $exec = $db->query($sql);
    $exec = null;
}

function viewhyperlinkthumb($item_id, $widththumb = 50) {

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
    $metadataFile = Zend_Registry::get('metadataFile'); //read metadata file
    $sqlit = "SELECT * FROM omeka_items WHERE id=" . $item_id . "";
    $execrecordit = $db->query($sqlit);
    $datarecordit = $execrecordit->fetch();

    $sql = "SELECT * FROM metadata_record WHERE object_id=" . $item_id . " and object_type='item'";
    $execrecord = $db->query($sql);
    $datarecord = $execrecord->fetch();
    if ($datarecord['id'] > 0) {
        $sql = "SELECT a.* FROM metadata_element_value a join metadata_element_hierarchy b ON b.id=a.element_hierarchy 

		WHERE a.record_id=" . $datarecord['id'] . " and a.element_hierarchy=" . $metadataFile[metadata_schema_resources][element_hierarchy_location] . "";
        $exec5 = $db->query($sql);
        $data51 = $exec5->fetch();

        $sql = "SELECT a.* FROM metadata_element_value a join metadata_element_hierarchy b ON b.id=a.element_hierarchy 
		WHERE a.record_id=" . $datarecord['id'] . " and a.element_hierarchy=34";
        $exec5 = $db->query($sql);
        $data52 = $exec5->fetch();


        $sql = "SELECT a.* FROM metadata_element_value a join metadata_element_hierarchy b ON b.id=a.element_hierarchy 
		WHERE a.record_id=" . $datarecord['id'] . " and a.element_hierarchy=" . $metadataFile[metadata_schema_resources][element_hierarchy_format] . "";
        $exec5 = $db->query($sql);
        $dataformat = $exec5->fetch();
        if ($dataformat['vocabulary_record_id'] > 0) {
            $sql2 = "SELECT * FROM metadata_vocabulary_record WHERE id=" . $dataformat['vocabulary_record_id'] . " ";
            $exec2 = $db->query($sql2);
            $dataformatfromvoc = $exec2->fetch();
        }

        $uri = WEB_ROOT;
        if (stripos($data51['value'], ".jpg") > 0 or stripos($data51['value'], ".gif") > 0 or $datarecordit['item_type_id'] == 6 or stripos($data51['value'], ".jpeg") > 0 or stripos($data51['value'], ".png") > 0 or stripos($data51['value'], ".bmp") > 0 or stripos($data51['value'], "content/thumbs/src") > 0 or $dataformatfromvoc['value'] == "IMAGE") {

            $string = '<img src="' . $data51['value'] . '" style=" width:' . $widththumb . 'px; max-height:' . $widththumb . 'px;"/><br>';
        } elseif (stripos($data51['value'], ".pdf") > 0) {

            $string = '<img src="' . $uri . '/application/views/scripts/images/files-icons/pdf.png" width="' . $widththumb . 'px; max-height:' . $widththumb . 'px;"/><br>';
        } elseif (stripos($data51['value'], ".tiff") > 0 or stripos($data51['value'], ".tif") > 0) {

            //http://education.natural-europe.eu/natural_europe/custom/phpThumb/phpThumb.php?src=/natural_europe/archive/files/riekko-ansasta2_72a2f5e439.tif&w=135
            $string = '<img src="' . $uri . '/custom/phpThumb/phpThumb.php?src=' . $data51['value'] . '"  width="' . $widththumb . 'px; max-height:' . $widththumb . 'px;"/><br>';
        } elseif (stripos($data51['value'], ".doc") > 0 or stripos($data51['value'], "docx") > 0 or stripos($data51['value'], ".txt") > 0 or stripos($dataformatfromvoc['value'], "word") > 0) {

            $string = '<img src="' . $uri . '/application/views/scripts/images/files-icons/word.png" width="' . $widththumb . 'px; max-height:' . $widththumb . 'px;"/><br>';
        } elseif (stripos($data51['value'], ".ppt") > 0 or stripos($data51['value'], ".pptx") > 0 or stripos($data51['value'], ".pps") > 0 or stripos($dataformatfromvoc['value'], "powerpoint") > 0) {

            $string = '<img src="' . $uri . '/application/views/scripts/images/files-icons/powerpoint.png" width="' . $widththumb . 'px; max-height:' . $widththumb . 'px;"/><br>';
        } elseif (stripos($data51['value'], "html") > 0 or stripos($data51['value'], "htm") > 0 or stripos($data51['value'], "asp") > 0 or stripos($dataformatfromvoc['value'], "HTML") > 0 or stripos($dataformatfromvoc['value'], "Html") > 0 or $dataformatfromvoc['value'] == 'html' or $dataformatfromvoc['value'] == 'html/text' or $dataformatfromvoc['value'] == 'HTML') {

            $string = '<img src="http://img.bitpixels.com/getthumbnail?code=29089&size=200&url=' . $data51['value'] . '" style=" width:' . $widththumb . 'px; max-height:' . $widththumb . 'px;"><br>';
            // echo ''.$data51['value'].'<br>';
        } elseif (stripos($data51['value'], ".emf") > 0) {
            
        } else {

            $string = '<img src="http://img.bitpixels.com/getthumbnail?code=29089&size=200&url=' . $data51['value'] . '" style=" width:' . $widththumb . 'px; max-height:' . $widththumb . 'px; "><br>';
        }
        return $string;
    }//if exist in metadata record table
}

function viewhyperlinkimage($item_id) {

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
    $metadataFile = Zend_Registry::get('metadataFile'); //read metadata file
    $sqlit = "SELECT * FROM omeka_items WHERE id=" . $item_id . "";
    $execrecordit = $db->query($sqlit);
    $datarecordit = $execrecordit->fetch();

    $sql = "SELECT * FROM metadata_record WHERE object_id=" . $item_id . " and object_type='item'";
    $execrecord = $db->query($sql);
    $datarecord = $execrecord->fetch();
    if ($datarecord['id'] > 0) {
        $sql = "SELECT a.* FROM metadata_element_value a join metadata_element_hierarchy b ON b.id=a.element_hierarchy 
		WHERE a.record_id=" . $datarecord['id'] . " and a.element_hierarchy=" . $metadataFile[metadata_schema_resources][element_hierarchy_location] . "";
        $exec5 = $db->query($sql);
        $data51 = $exec5->fetch();

        $sql = "SELECT a.* FROM metadata_element_value a join metadata_element_hierarchy b ON b.id=a.element_hierarchy 
		WHERE a.record_id=" . $datarecord['id'] . " and a.element_hierarchy=34";
        $exec5 = $db->query($sql);
        $data52 = $exec5->fetch();


        $sql = "SELECT a.* FROM metadata_element_value a join metadata_element_hierarchy b ON b.id=a.element_hierarchy 
		WHERE a.record_id=" . $datarecord['id'] . " and a.element_hierarchy=" . $metadataFile[metadata_schema_resources][element_hierarchy_format] . "";
        $exec5 = $db->query($sql);
        $dataformat = $exec5->fetch();
        if ($dataformat['vocabulary_record_id'] > 0) {
            $sql2 = "SELECT * FROM metadata_vocabulary_record WHERE id=" . $dataformat['vocabulary_record_id'] . " ";
            $exec2 = $db->query($sql2);
            $dataformatfromvoc = $exec2->fetch();
        }
        $uri = WEB_ROOT;
        if (stripos($data51['value'], ".jpg") > 0 or stripos($data51['value'], ".gif") > 0 or $datarecordit['item_type_id'] == 6 or stripos($data51['value'], ".jpeg") > 0 or stripos($data51['value'], ".png") > 0 or stripos($data51['value'], ".bmp") > 0 or stripos($data51['value'], "content/thumbs/src") > 0 or $dataformatfromvoc['value'] == "IMAGE") {

            $string = '<img src="' . $data51['value'] . '" style=" max-width:500px; max-height:400px; height:auto;"/><br>';
        } elseif (stripos($data51['value'], ".tiff") > 0 or stripos($data51['value'], ".tif") > 0) {

            //http://education.natural-europe.eu/natural_europe/custom/phpThumb/phpThumb.php?src=/natural_europe/archive/files/riekko-ansasta2_72a2f5e439.tif&w=135
            $string = '<img src="' . $uri . '/custom/phpThumb/phpThumb.php?src=' . $data51['value'] . '" style=" max-width:500px; max-height:400px; height:auto;"/><br>';
        } elseif (stripos($data51['value'], ".pdf") > 0) {

            $string = '<img src="' . $uri . '/application/views/scripts/images/files-icons/pdf.png" width="40px"/><br>';
        } elseif (stripos($data51['value'], ".doc") > 0 or stripos($data51['value'], "docx") > 0 or stripos($data51['value'], ".txt") > 0 or stripos($dataformatfromvoc['value'], "word") > 0) {

            $string = '<img src="' . $uri . '/application/views/scripts/images/files-icons/word.png" width="40px"/><br>';
        } elseif (stripos($data51['value'], ".ppt") > 0 or stripos($data51['value'], ".pptx") > 0 or stripos($data51['value'], ".pps") > 0 or stripos($dataformatfromvoc['value'], "powerpoint") > 0) {

            $string = '<img src="' . $uri . '/application/views/scripts/images/files-icons/powerpoint.png" width="40px"/><br>';
        } elseif (stripos($data51['value'], "html") > 0 or stripos($data51['value'], "htm") > 0 or stripos($data51['value'], "asp") > 0 or stripos($dataformatfromvoc['value'], "HTML") > 0 or stripos($dataformatfromvoc['value'], "Html") > 0 or $dataformatfromvoc['value'] == 'html' or $dataformatfromvoc['value'] == 'html/text' or $dataformatfromvoc['value'] == 'HTML') {

            $string = '<img src="http://img.bitpixels.com/getthumbnail?code=29089&size=200&url=' . $data51['value'] . '" style=" max-width:500px; max-height:400px; height:auto;"/><br>';
            // echo ''.$data51['value'].'<br>';
        } elseif (stripos($data51['value'], ".emf") > 0) {
            
        } else {

            $string = '<img src="http://img.bitpixels.com/getthumbnail?code=29089&size=200&url=' . $data51['value'] . '" style=" max-width:500px; max-height:400px; height:auto;"/><br>';
        }
        return $string;
    }//if exist in metadata record table
}

function ingest_search_total_block($search_item, $search_type = 1) {

    libxml_use_internal_errors(false);


/////////////////////////general//////////////////
    if (isset($search_item)) {
        $europeanatext = $search_item;
        $search_item = str_replace(' ', '+', $search_item);
    }
/////////////////////////general//////////////////
/////////////////////europeana/////////////////////
    if (isset($_POST['bytype'])) {
        $europenana_type = "+europeana_type:*" . $_POST['bytype'] . "*";
    } else {
        $europenana_type = "";
    }
    $feedURL = @simplexml_load_file('http://api.europeana.eu/api/opensearch.rss?searchTerms=text:"' . $europeanatext . '*"' . $europenana_type . '&startPage=1&wskey=IIRTOOIRNG', NULL, LIBXML_NOERROR | LIBXML_NOWARNING);

    // read feed into SimpleXML object
    //$sxmlyou = simplexml_load_file($feedURL, NULL, LIBXML_NOERROR | LIBXML_NOWARNING);
    // get summary counts from opensearch: namespace
    // print_r($sxmlyou);
    $europeana_child1 = $feedURL->children();
    $europeana_child2 = $europeana_child1->children();

    $counts = $europeana_child1->children('http://a9.com/-/spec/opensearch/1.1/');
    $total_euroepana = $counts->totalResults; //break;
///////////////////////euroepana////////////////////////
/////////////////////Natural Europe/////////////////////
    if (isset($_POST['bytype'])) {
        $bytypeforurl = "type:" . $_POST['bytype'] . " AND ";
        $bytypeforurlNE = $_POST['bytype'];
    } else {
        $bytypeforurl = "";
        $bytypeforurlNE = "";
    }
    if (isset($_POST['dataProvider'])) {
        $dataProviderforurl = "dataProvider:" . $_POST['dataProvider'] . " AND ";
    } else {
        $dataProviderforurl = "";
    }
    //$naturaleuropexml = @simplexml_load_file('http://collections.natural-europe.eu/cmss/search?query='.$bytypeforurl.''.$dataProviderforurl.'text:'.$europeanatext.'&start='.$startPageurl.'', NULL, LIBXML_NOERROR | LIBXML_NOWARNING);
    // read feed into SimpleXML object
    //$sxmlyou = simplexml_load_file($feedURL, NULL, LIBXML_NOERROR | LIBXML_NOWARNING);
    // get summary counts from opensearch: namespace
    // print_r($sxmlyou);
    //$naturaleurope_child1=$naturaleuropexml->children();
    //$naturaleurope_child2=$naturaleurope_child1->children();
    // $counts = $naturaleurope_child1->children('http://a9.com/-/spec/opensearch/1.1/');
    // $total_naturaleurope = $counts->totalResults; //break;
    $europeanatext_forcultural = urlencode($europeanatext);
    $resp = call_cultural_federation($europeanatext_forcultural, 0, $bytypeforurlNE);

    if ($resp) {

        foreach ($resp as $key => $resp1) {
            //echo $key . '<br>';
            foreach ($resp1 as $key2 => $resp2) {
                if ($key2 == 'nrOfResults') {
                    $total_naturaleurope = $resp2;
                }
                //echo $key2 . ':' . $resp2 . '<br>';
            }
        }
    }

///////////////////////Natural Europe////////////////////////
/////////////////////ariadne/////////////////////

    $feedURL2 = @simplexml_load_file('http://ariadne.cs.kuleuven.be/ariadne-partners/api/sqitarget?query="' . $europeanatext . '"&start=1&size=12&lang=plql1&format=lom', NULL, LIBXML_NOERROR | LIBXML_NOWARNING);

    // read feed into SimpleXML object
    //$sxmlyou = simplexml_load_file($feedURL, NULL, LIBXML_NOERROR | LIBXML_NOWARNING);
    // get summary counts from opensearch: namespace
    // print_r($sxmlyou);
    //$counts = $europeana_child1->children('http://a9.com/-/spec/opensearch/1.1/');
    $total_ariadne = $feedURL2['cardinality']; //break;
///////////////////////ariadne////////////////////////
    ?>
    <script>
        function GoPage(iPage) {
                  
            document.form2.startPage.value = iPage;
            document.form2.submit();
        }
        function submitform() {
                	  
                	
            document.formnatural.submit();
        }
        function submitform2() {
                	  
                		
            document.formeuropeana.submit();
        }
        function submitform3() {
                	  
                		
            document.formariadne.submit();
        }

    </script>

    <form action="naturaleurope" method="post" name="formnatural" style=" float:left; margin-top:10px; ">
        <input type="hidden" name="europeanatext" value="<?php echo $europeanatext; ?>">
        <input type="hidden" name="startPage" value="1">
        <input type="hidden" name="europeanatext" value="<?php echo $europeanatext; ?>">
        <?php if (isset($_POST['bytype'])) { ?> <input type="hidden" name="bytype" value="<?php echo $_POST['bytype']; ?>"> <?php } ?>
        <a href="#" onclick="submitform();" <?php
    if ($search_type == 1) {
        echo 'style=" font-weight:bold; text-decoration:underline;"';
    }
        ?>> Natural Europe (<?php echo $total_naturaleurope; ?>)</a><br>
    </form>
    <br>
    <form action="europeana" method="post" name="formeuropeana" style=" float:left; margin-top:10px; ">
        <input type="hidden" name="europeanatext" value="<?php echo $europeanatext; ?>">
        <input type="hidden" name="startPage" value="1">
        <input type="hidden" name="europeanatext" value="<?php echo $europeanatext; ?>">
        <?php if (isset($_POST['bytype'])) { ?> <input type="hidden" name="bytype" value="<?php echo $_POST['bytype']; ?>"> <?php } ?>
        <a href="#" onclick="submitform2();" <?php
    if ($search_type == 2) {
        echo 'style=" font-weight:bold; text-decoration:underline;"';
    }
        ?>>Europeana (<?php echo $total_euroepana; ?>)  </a><br>
    </form>
    <br>
    <form action="ariadne" method="post" name="formariadne" style=" float:left; margin-top:10px; ">
        <input type="hidden" name="europeanatext" value="<?php echo $europeanatext; ?>">
        <input type="hidden" name="startPage" value="1">
        <input type="hidden" name="europeanatext" value="<?php echo $europeanatext; ?>">
        <a href="#" onclick="submitform3();" <?php
       if ($search_type == 3) {
           echo 'style=" font-weight:bold; text-decoration:underline;"';
       }
        ?>>Ariadne (<?php echo $total_ariadne; ?>)  </a>

    </form>

    <?php
}

function ingest_search_providers($search_item, $dataProvider = NULL) {

    libxml_use_internal_errors(false);


/////////////////////////general//////////////////
    if (isset($search_item)) {
        $europeanatext = $search_item;
        $search_item = str_replace(' ', '+', $search_item);
    }
/////////////////////////general//////////////////
/////////////////////Natural Europe/////////////////////
    if (isset($_POST['bytype'])) {
        $bytypeforurl = "type:" . $_POST['bytype'] . " AND ";
    } else {
        $bytypeforurl = "";
    }
    $search_type = 0;

    if (isset($_POST['dataProvider']) and $_POST['dataProvider'] == $dataProvider) {
        $search_type = 1;
    }//echo $search_type;
//$dataProvider="nhmc";
    if (isset($dataProvider)) {
        $dataProviderforurl = "dataProvider:" . $dataProvider . " AND ";
    } else {
        $dataProviderforurl = "";
    }
    $naturaleuropexml = simplexml_load_file('http://collections.natural-europe.eu/cmss/search?query=' . $bytypeforurl . '' . $dataProviderforurl . 'text:' . $europeanatext . '&start=' . $startPageurl . '', NULL, LIBXML_NOERROR | LIBXML_NOWARNING);

    // read feed into SimpleXML object
    //$sxmlyou = simplexml_load_file($feedURL, NULL, LIBXML_NOERROR | LIBXML_NOWARNING);
    // get summary counts from opensearch: namespace
    // print_r($sxmlyou);
    $naturaleurope_child1 = $naturaleuropexml->children();
    $naturaleurope_child2 = $naturaleurope_child1->children();

    $counts = $naturaleurope_child1->children('http://a9.com/-/spec/opensearch/1.1/');
    $total_naturaleurope = $counts->totalResults; //break;
///////////////////////Natural Europe////////////////////////
    ?>


    <?php if ($total_naturaleurope > 0) { ?>
        <br><br>
        <form action="#" method="post" name="<?php echo $dataProvider; ?>form" style=" float:left; margin-top:10px; ">
            <input type="hidden" name="europeanatext" value="<?php echo $europeanatext; ?>">
            <input type="hidden" name="startPage" value="1">
            <input type="hidden" name="europeanatext" value="<?php echo $europeanatext; ?>">
            <input type="hidden" name="dataProvider" value="<?php echo $dataProvider; ?>">
            <?php if (isset($_POST['bytype'])) { ?> <input type="hidden" name="bytype" value="<?php echo $_POST['bytype']; ?>"> <?php } ?>



            <a href="#" style="text-transform:uppercase;<?php
        if ($search_type == 1) {
            echo 'font-weight:bold; text-decoration:underline;';
        }
            ?>" onclick="document.<?php echo $dataProvider; ?>form.submit();return false;"> <?php echo $dataProvider; ?> (<?php echo $total_naturaleurope; ?>)</a>

        </form>
    <?php } ?>


    <?php
}

function savenewtemplate($itid, $formtype) {

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


    $itemtdb = $db->Items;

    $maxIdSQL = "SELECT MAX(id) AS MAX_ID FROM " . $itemtdb . " LIMIT 0,1";
    $exec = $db->query($maxIdSQL);
    $row = $exec->fetch();
    $max_id = $row["MAX_ID"];
    $exec = null;

//print_r($_FILES['file']['type']['0']); break; 

    if (strlen($_POST['title']) > 0) {
        $_POST['Elements']['68']['0']['text'] = $_POST['title'];
    }
    if (strlen($_POST['Elements']['68']['0']['text']) > 0) {
        $path_title = $_POST['Elements']['68']['0']['text'];
    } else {
        $path_title = "template-title-" . $max_id . "";
        $_POST['Elements']['68']['0']['text'] = "resource-title-" . $max_id . "";
    }
    if ($_POST['description']) {
        $path_description = $_POST['description'];
    } else {
        $path_description = "";
    }

    if ($_POST['type']) {
        $formtype = $_POST['type'];
    } else {
        $formtype = "0";
    }
//if($_POST['Elements']['68']['0']['text']){$path_title=addslashes($_POST['Elements']['68']['0']['text']);} else{$path_title="resource-title-".$max_id."";}
    if ($_POST['public']) {
        $path_public = $_POST['public'];
    } else {
        $path_public = "0";
    }

    $date_modified = date("Y-m-d H:i:s");
    $mainAttributesSql = "INSERT INTO $itemtdb (featured,item_type_id,public,modified,added) VALUES (0," . $formtype . ",'" . $_POST['public'] . "','" . $date_modified . "','" . $date_modified . "')";
//echo $mainAttributesSql; break;
    $db->exec($mainAttributesSql);



    $lastExhibitIdSQL = "SELECT LAST_INSERT_ID() AS LAST_EXHIBIT_ID FROM " . $itemtdb;
    $exec = $db->query($lastExhibitIdSQL);
    $row = $exec->fetch();
    $last_exhibit_id = $row["LAST_EXHIBIT_ID"];
    $exec = null;

    $entitiesRelationsdb = $db->EntitiesRelations;
    $entity_id = current_user();
    $entitiesRelationsSql = "INSERT INTO " . $entitiesRelationsdb . " (entity_id, relation_id, relationship_id, type, time) VALUES (" . $entity_id->entity_id . ", " . $last_exhibit_id . ",1,'Item','" . date("Y-m-d H:i:s") . "')";
    $exec = $db->query($entitiesRelationsSql);

    $path_title = htmlspecialchars($path_title);
    //$path_title = addslashes($path_title);
    $path_description = htmlspecialchars($path_description);
    //$path_description = addslashes($path_description);



    $mainAttributesSql = "INSERT INTO omeka_element_texts (record_id ,record_type_id ,element_id,text) VALUES (?,?,?,?)";
//echo $mainAttributesSql;break;
    $db->exec($mainAttributesSql, array($last_exhibit_id, 2, 68, $path_title)); //title
    $db->exec($mainAttributesSql, array($last_exhibit_id, 2, 59, $path_description)); //description

    /* ===================================INSERT record for METADATA=================================== */
    $metadatarecordSql = "INSERT INTO metadata_record (id, object_id, object_type,date_modified) VALUES ('', " . $last_exhibit_id . ",'template','" . $date_modified . "')";
    $execmetadatarecordSql = $db->query($metadatarecordSql);

    $metadataFile = Zend_Registry::get('metadataFile'); /////read metadata file

    $lastExhibitIdSQL = "SELECT LAST_INSERT_ID() AS LAST_EXHIBIT_ID FROM metadata_record";
    $exec = $db->query($lastExhibitIdSQL);
    $row = $exec->fetch();
    $last_record_id = $row["LAST_EXHIBIT_ID"];
    $exec = null;

    $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer) VALUES (?,?,?,?,?,?)";
    if ($metadataFile[metadata_schema_resources][element_hierarchy_description] != false) {
        $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_description], $path_description, 'en', 1, $last_record_id, 1)); ///description in metadata record  
    }
    if ($metadataFile[metadata_schema_resources][element_hierarchy_title] != false) {
        $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_title], $path_title, 'en', 1, $last_record_id, 1)); ///title in metadata record
    }
    $execmetadatarecordSql = null;

    $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES (?,?,?,?,?,?,?)";
    if ($metadataFile[metadata_schema_resources][element_hierarchy_location] != false) {
        $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_location], '', 'none', 1, $last_record_id, 1, 0)); ///location in metadata record
    }
    if ($metadataFile[metadata_schema_resources][element_hierarchy_identifier_parent] != false) {
        $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_identifier_parent], 'Parent Element', 'none', 1, $last_record_id, 1, 0)); ///location in metadata record

        if ($metadataFile[metadata_schema_resources][element_hierarchy_identifier_catalog] != false) {
            $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_identifier_catalog], 'URI', 'none', 1, $last_record_id, 1, 0)); ///location in metadata record
        }
        if ($metadataFile[metadata_schema_resources][element_hierarchy_identifier_entry] != false) {
            $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_identifier_entry], '', 'none', 1, $last_record_id, 1, 0)); ///location in metadata record
        }
    }
    if ($metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_parent] != false) {
        $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_parent], 'Parent Element', 'none', 1, $last_record_id, 1, 0)); ///location in metadata record
        if ($metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_catalog] != false and $metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_catalog_value] != false) {
            $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_catalog], $metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_catalog_value], 'none', 1, $last_record_id, 1, 0)); ///location in metadata record
        }
        if ($metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_entry] != false and $metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_entry_value_prefix] != false) {
            $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_entry], $metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_entry_value_prefix] . '' . $last_record_id, 'none', 1, $last_record_id, 1, 0)); ///location in metadata record
        }
    }
    $execmetadatarecordSql = null;
//////////////////////rights standard values/////////////////////////////
    if ($metadataFile[metadata_schema_resources][element_hierarchy_metadata_rights_copyrights_and_other_restrictions_id] != false and $metadataFile[metadata_schema_resources][element_hierarchy_metadata_rights_copyrights_and_other_restrictions_value] != false) {
        $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_metadata_rights_copyrights_and_other_restrictions_id], $metadataFile[metadata_schema_resources][element_hierarchy_metadata_rights_copyrights_and_other_restrictions_value], 'none', 1, $last_record_id, 1, NULL)); ///location in metadata record
    }
    if ($metadataFile[metadata_schema_resources][element_hierarchy_metadata_rights_cost_id] != false and $metadataFile[metadata_schema_resources][element_hierarchy_metadata_rights_cost_value] != false) {
        $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_metadata_rights_cost_id], $metadataFile[metadata_schema_resources][element_hierarchy_metadata_rights_cost_value], 'none', 1, $last_record_id, 1, NULL)); ///location in metadata record
    }
    if ($metadataFile[metadata_schema_resources][element_hierarchy_metadata_rights_description_id] != false and $metadataFile[metadata_schema_resources][element_hierarchy_metadata_rights_description_value] != false) {
        $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_metadata_rights_description_id], $metadataFile[metadata_schema_resources][element_hierarchy_metadata_rights_description_value], 'en', 1, $last_record_id, 1, NULL)); ///location in metadata record
    }

///////////////////////vcard///////////////////////////
    $entityuser = current_user(); //print_r($entityuser); break;
///////////////////////end vcard///////////////////////////
    if ($metadataFile[metadata_schema_resources][element_hierarchy_metadata_schema_id] != false and $metadataFile[metadata_schema_resources][element_hierarchy_metadata_schema] != false) {
        $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_metadata_schema_id], $metadataFile[metadata_schema_resources][element_hierarchy_metadata_schema] . '' . $last_record_id, 'none', 1, $last_record_id, 1, 0)); ///location in metadata record
    }
    $execmetadatarecordSql = null;
    $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES ('86','Parent Element','none',1, " . $last_record_id . ",1,0)";
    $execmetadatarecordSql = $db->query($metadatarecordSql);
    $exec = null;


    return $last_exhibit_id;
}

function createresourcefromtemplate($record_id, $rowsqlfortitle) {

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

    $sql = "SELECT * FROM metadata_element_value WHERE record_id=" . $record_id . " order by element_hierarchy,multi ASC";
    $exec5 = $db->query($sql);
    $data5 = $exec5->fetchAll();


    $itemtdb = $db->Items;

//print($xml->general->title->string);
//print($xml->technical->format); 
    $type = $_POST['resourcetype'];
    if ($type == 'hyperlink') {
        $formtype = 11;
    } elseif ($type == 'image') {
        $formtype = 6;
    } elseif ($type == 'file') {
        $formtype = 20;
    } else {
        $formtype = 11;
    }
    $path_public = 0;

    $date_modified = date("Y-m-d H:i:s");
    $mainAttributesSql = "INSERT INTO $itemtdb (featured,item_type_id,public,modified,added) VALUES (0," . $formtype . ",'" . $path_public . "','" . $date_modified . "','" . $date_modified . "')";
    $mainAttributesSql;
    $db->exec($mainAttributesSql);



    $lastExhibitIdSQL = "SELECT LAST_INSERT_ID() AS LAST_EXHIBIT_ID FROM " . $itemtdb;
    $exec = $db->query($lastExhibitIdSQL);
    $row = $exec->fetch();
    $last_exhibit_id = $row["LAST_EXHIBIT_ID"];
    $exec = null;

    $entitiesRelationsdb = $db->EntitiesRelations;
    $user_entity_id = current_user();
    $entitiesRelationsSql = "INSERT INTO " . $entitiesRelationsdb . " (entity_id, relation_id, relationship_id, type, time) VALUES (" . $user_entity_id['entity_id'] . ", " . $last_exhibit_id . ",1,'Item','" . date("Y-m-d H:i:s") . "')";
    $exec = $db->query($entitiesRelationsSql);

    $path_title = htmlspecialchars($rowsqlfortitle['text']);
    $path_title = addslashes($path_title);
//$path_description=htmlspecialchars($path_description);
//$path_description=addslashes($path_description);
//$path_url=htmlspecialchars($path_url);
//$path_url=addslashes($path_url);


    $mainAttributesSql = "INSERT INTO omeka_element_texts (record_id ,record_type_id ,element_id,text) VALUES (" . $last_exhibit_id . ",2,68,'" . $path_title . "')";
    //echo $mainAttributesSql;
    $db->exec($mainAttributesSql);

    $metadatarecordSql = "INSERT INTO metadata_record (id, object_id, object_type,date_modified) VALUES ('', " . $last_exhibit_id . ",'item','" . $date_modified . "')";
    $execmetadatarecordSql = $db->query($metadatarecordSql);


    $lastExhibitIdSQL = "SELECT LAST_INSERT_ID() AS LAST_EXHIBIT_ID FROM metadata_record";
    $exec = $db->query($lastExhibitIdSQL);
    $row = $exec->fetch();
    $last_record_id = $row["LAST_EXHIBIT_ID"];
    $exec = null;

    $metadataFile = Zend_Registry::get('metadataFile'); /////read metadata file

    $metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy, value, language_id, multi, record_id, parent_indexer,is_editable) VALUES (?,?,?,?,?,?,?)";
    if ($metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_parent] != false) {
        $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_parent], 'Parent Element', 'none', 1, $last_record_id, 1, 0)); ///location in metadata record
        if ($metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_catalog] != false and $metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_catalog_value] != false) {
            $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_catalog], $metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_catalog_value], 'none', 1, $last_record_id, 1, 0)); ///location in metadata record
        }
        if ($metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_entry] != false and $metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_entry_value_prefix] != false) {
            $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_entry], $metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_entry_value_prefix] . '' . $last_record_id, 'none', 1, $last_record_id, 1, 0)); ///location in metadata record
        }
    }

    if ($metadataFile[metadata_schema_resources][element_hierarchy_metadata_schema_id] != false and $metadataFile[metadata_schema_resources][element_hierarchy_metadata_schema] != false) {
        $execmetadatarecordSql = $db->query($metadatarecordSql, array($metadataFile[metadata_schema_resources][element_hierarchy_metadata_schema_id], $metadataFile[metadata_schema_resources][element_hierarchy_metadata_schema], 'none', 1, $last_record_id, 1, 0)); ///location in metadata record
    }
    $execmetadatarecordSql = null;

    $maxIdSQL = "INSERT INTO metadata_element_value (element_hierarchy,value,language_id,vocabulary_record_id,multi,record_id,parent_indexer,vcard_id,is_editable,classification_id) VALUES (?,?,?,?,?,?,?,?,?,?)";
    foreach ($data5 as $data5) {

        $arr = array($metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_parent], $metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_catalog], $metadataFile[metadata_schema_resources][element_hierarchy_metadata_identifier_entry], $metadataFile[metadata_schema_resources][element_hierarchy_metadata_schema_id]);
        if (!(in_array($data5['element_hierarchy'], $arr) and $data5['multi'] == 1)) { //echo $data5['is_editable'];
            if (!(strlen($data5['vocabulary_record_id']) > 0)) {
                $data5['vocabulary_record_id'] = NULL;
            }
            if (!(strlen($data5['multi']) > 0)) {
                $data5['multi'] = NULL;
            }
            if (!(strlen($data5['parent_indexer']) > 0)) {
                $data5['parent_indexer'] = NULL;
            }
            if (!(strlen($data5['vcard_id']) > 0)) {
                $data5['vcard_id'] = NULL;
            }
            if (!(strlen($data5['is_editable']) > 0)) {
                $data5['is_editable'] = NULL;
            }
            if (!(strlen($data5['classification_id']) > 0)) {
                $data5['classification_id'] = NULL;
            }

            //$metadatarecordSql = "INSERT INTO metadata_element_value (element_hierarchy,value,language_id,vocabulary_record_id,multi,record_id,parent_indexer,vcard_id,is_editable,classification_id) VALUES 
            //    ('" . $data5['element_hierarchy'] . "','" . $data5['value'] . "','" . $data5['language_id'] . "'," . $data5['vocabulary_record_id'] . "," . $data5['multi'] . "," . $last_record_id . "," . $data5['parent_indexer'] . "," . $data5['vcard_id'] . "," . $data5['is_editable'] . ",'" . $data5['classification_id'] . "')";

            $exec = $db->exec($maxIdSQL, array($data5['element_hierarchy'], $data5['value'], $data5['language_id'], $data5['vocabulary_record_id'], $data5['multi'], $last_record_id, $data5['parent_indexer'], $data5['vcard_id'], $data5['is_editable'], $data5['classification_id'])); //title            
//echo "<br>";
            //$execmetadatarecordSql = $db->query($metadatarecordSql);
            $exec = null;
        }
    }

    return $last_exhibit_id;
}

function save_analytics_for_translation($postvariable = NULL, $object_type = 'item') {

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

    $maxIdSQL = "select * from metadata_record where object_id=" . $_POST['item_id'] . " and object_type='" . $object_type . "'";
    $exec = $db->query($maxIdSQL);
    $row = $exec->fetch();
    $record_id = $row["id"];
    $exec = null;
//print_r($_POST);break;
    $entityuser = current_user(); //print_r($entityuser); break;
    foreach ($_POST as $var => $value) {
        $var12 = explode("_", $var); //split form name at _
        if ($var12[0] == 'translatedanalytics' or $var12[0] == 'fortranslationanalytics' or $var12[0] == 'fortranslationanalyticslan' or $var12[0] === 'translatedanalyticslan') {

            $var1 = explode("_", $var); //split form name at _
            $var = $var1[0];
            $varrec = $var1[1];
            $varelem = $var1[2];
            $varmul = $var1[3];
            $varforcount = $var1[4];

            $original_text = $_POST['fortranslationanalytics_' . $varrec . '_' . $varelem . '_' . $varmul . ''];
            $original_text = htmlspecialchars($original_text);
//$original_text = addslashes($original_text);
            $original_text_lang = $_POST['fortranslationanalyticslan_' . $varrec . '_' . $varelem . '_' . $varmul . ''];
            $translated_text = $_POST['translatedanalytics_' . $varrec . '_' . $varelem . '_' . $varmul . '_' . $varforcount . ''];
            $translated_text = htmlspecialchars($translated_text);
//$translated_text = addslashes($translated_text);
            $translated_text_lang = $_POST['translatedanalyticslan_' . $varrec . '_' . $varelem . '_' . $varmul . '_' . $varforcount . ''];
            $user_fixed_text = $_POST['' . $varelem . '_' . $varmul . '_' . $varforcount . ''];
            $user_fixed_text = htmlspecialchars($user_fixed_text);
//$user_fixed_text = addslashes($user_fixed_text);
            $translated_service = $_POST['translatedanalyticsservice_' . $varrec . '_' . $varelem . '_' . $varmul . '_' . $varforcount . ''];

            $result_multi = NULL;
            $maxIdSQL = "select * from omeka_translation_analytics_service where title=?;";
            $exec = $db->query($maxIdSQL, array($translated_service));
            $result_multi = $exec->fetch();
            if (!$result_multi['id'] > 0 and strlen($translated_service) > 0) {
                $maxIdSQL = "insert into omeka_translation_analytics_service SET title=?;";
                $exec = $db->query($maxIdSQL, array($translated_service));
                $maxIdSQL = "select * from omeka_translation_analytics_service where title=?;";
                $exec = $db->query($maxIdSQL, array($translated_service));
                $result_multi = $exec->fetch();
            }
            $exec = null;

            if ($var != 'fortranslationanalytics' and $var != 'fortranslationanalyticslan' and $var != 'translatedanalyticslan') { //not get in if is language name at form or name is hdnline
                $maxIdSQL = "insert into omeka_translation_analytics SET date=?,service_id=?,element_id=?,record_id=?,user_id=?,original_text=?,original_text_lang=?,translated_text=?,translated_text_lang=?,user_fixed_text=? ;";

//echo $maxIdSQL."<br>"; 
                $exec = $db->query($maxIdSQL, array($_POST['date_modified'], $result_multi['id'], $varelem, $varrec, $entityuser['entity_id'], $original_text, $original_text_lang, $translated_text, $translated_text_lang, $user_fixed_text));
                $result_multi = $exec->fetch();
                $exec = null;
            }//end not get in if is language name at form 
        }
    }
    return $_POST['item_id'];
}
?>
