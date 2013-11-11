<?php
/**
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2010
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 * @subpackage Controllers
 * @author CHNM
 * @see Omeka_Controller_Action
 * @access private
 */

/**
 * @internal This implements Omeka internals and is not part of the public API.
 * @access private
 * @package Omeka
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2010
 */
class TemplatesController extends Omeka_Controller_Action {

    public $contexts = array(
        'browse' => array('json', 'dcmes-xml', 'rss2', 'omeka-xml', 'omeka-json', 'atom'),
        'show' => array('json', 'dcmes-xml', 'omeka-xml', 'omeka-json', 'atom')
    );
    private $_ajaxRequiredActions = array(
        'element-form',
        'tag-form',
        'change-type',
    );
    private $_methodRequired = array(
        'element-form' => array('POST'),
        'modify-tags' => array('POST'),
        'power-edit' => array('POST'),
        'change-type' => array('POST'),
        'batch-edit-save' => array('POST'),
    );

    public function init() {
        $this->_modelClass = 'Item';
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
        $metadataFile = CONFIG_DIR . '/metadata.ini';
        if (!file_exists($metadataFile)) {
            throw new Zend_Config_Exception('Your Omeka metadata file is missing.');
        }        
        //$metadataFile =Zend_Config_Ini($metadataFile, NULL);
        $metadataFile = parse_ini_file($metadataFile,true);
        Zend_Registry::set('metadataFile', $metadataFile);
        $this->view->assign(compact('metadataFile'));
        Zend_Registry::set('db', $db);
    }

    public function preDispatch() {
        $action = $this->getRequest()->getActionName();
        if (in_array($action, $this->_ajaxRequiredActions)) {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                return $this->_forward('not-found', 'error');
            }
        }
        if (array_key_exists($action, $this->_methodRequired)) {
            if (!in_array($this->getRequest()->getMethod(), $this->_methodRequired[$action])) {
                return $this->_forward('method-not-allowed', 'error');
            }
        }
    }

    /**
     * This shows the advanced search form for items by going to the correct URI.
     * 
     * This form can be loaded as a partial by calling items_search_form().
     * 
     * @return void
     */
    public function advancedSearchAction() {
        // Only show this form as a partial if it's being pulled via XmlHttpRequest
        $this->view->isPartial = $this->getRequest()->isXmlHttpRequest();

        // If this is set to null, use the default items/browse action.
        $this->view->formActionUri = null;

        $this->view->formAttributes = array('id' => 'advanced-search-form');
    }

    protected function _getItemElementSets() {
        return $this->getTable('ElementSet')->findForItems();
    }

    /**
     * Adds an additional permissions check to the built-in edit action.
     * 
     */
    public function editAction() {
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
        Zend_Registry::set('db', $db);

        if (array_key_exists('save_meta', $_POST)) {
            $lastexid = savemetadataitem($_POST, 'template');
            //print_r($_POST); 
            //break;
            $this->view->elementSets = $this->_getItemElementSets();
            $varName = strtolower($this->_modelClass);

            $record = $this->findById($lastexid);

            try {
                if ($record->saveForm($_POST)) {
                    $successMessage = $this->_getEditSuccessMessage($record);
                    if ($successMessage != '') {
                        $this->flashSuccess($successMessage);
                    }
                    $this->redirect->goto('edit', null, null, array('id' => $record->id));
                }
            } catch (Omeka_Validator_Exception $e) {
                $this->flashValidationErrors($e);
            }
            $this->view->assign(array($varName => $record));
            //$this->redirect->goto('edit', null, null, array('id' => $lastexid));
            //$this->render('metadataform',$_POST);
        } elseif (array_key_exists('save_item', $_POST)) {
            $lastexid = savemetadataitem($lastexid);
            $this->view->elementSets = $this->_getItemElementSets();

            $varName = strtolower($this->_modelClass);

            $record = $this->findById($lastexid);

            try {
                if ($record->saveForm($_POST)) {
                    $successMessage = $this->_getEditSuccessMessage($record);
                    if ($successMessage != '') {
                        $this->flashSuccess($successMessage);
                    }
                    $this->redirect->goto('browse', null, null, array('id' => $record->id));
                }
            } catch (Omeka_Validator_Exception $e) {
                $this->flashValidationErrors($e);
            }
            $this->view->assign(array($varName => $record));
            //$this->redirect->goto('browse', null, null, array('id' => $lastexid));
            //$this->render('metadataform',$_POST);
        } else {

            // Get all the element sets that apply to the item.
            $this->view->elementSets = $this->_getItemElementSets();

            if ($user = $this->getCurrentUser()) {

                $item = $this->findById();

                // If the user cannot edit any given item. Check if they can edit 
                // this specific item
                $metadataFile= Zend_Registry::get('metadataFile');
                if ($this->isAllowed('edit', $item)) {
                    $_SESSION['get_language_for_internal_xml'] = get_language_for_internal_xml();
                    $uri = WEB_ROOT;
                    $xml_general = array();
                    $execvocele2_general = $db->query("SELECT DISTINCT d.vocabulary_id FROM metadata_element d JOIN  metadata_element_hierarchy e ON d.id = e.element_id WHERE e.datatype_id=? and e.is_visible=? and d.schema_id=?", array(5,1,$metadataFile[metadata_schema_resources][id]));
                    $datavocele2 = $execvocele2_general->fetchAll();
                    $execvocele2_general = NULL;
                    $sqlvocelem = "SELECT e.value,d.id FROM metadata_vocabulary d JOIN metadata_vocabulary_record e ON d.id = e.vocabulary_id LEFT JOIN
					metadata_vocabulary_value f ON f.vocabulary_rid = e.id WHERE d.id=?";
                    foreach ($datavocele2 as $datavocele2) {
                        $execvocele = $db->query($sqlvocelem, array($datavocele2['vocabulary_id']));
                        $datavocele = $execvocele->fetch();
                        $execvocele = NULL;
                        //$xmlvoc = '' . $uri . '/archive/xmlvoc/' . $datavocele['value'] . '.xml';
                        // $xmlvoc='http://aglr.agroknow.gr/organic-edunet/archive/xmlvoc/new_oe_ontology_hierrarchy.xml';
                        $reader = new XMLReader();
                        $reader->open('' . $uri . '/archive/xmlvoc/' . $datavocele['value'] . '.xml', 'utf8');
                        //$xml = parse_ontologies($reader);
                        $xml_general[$datavocele['id']] = parse_ontologies($reader);
                        unset($reader);
                        //$reader->close();
                    }
                    //query for creating general elements pelement=0
                    $values=$metadataFile[metadata_elements_hide_from_resources][element_hierarchy_resources_hide];
                    if($values != false){
                        $valuesql= "and a.id NOT IN (".implode(',', $values).") ";
                    }else{$valuesql="";}
                    $sql2 = "SELECT a.* FROM metadata_element_hierarchy a JOIN metadata_element b on b.id=a.element_id WHERE b.schema_id=? and a.pelement_id=? and a.is_visible=? ".$valuesql." ORDER BY (case WHEN a.sequence IS NULL THEN 9999 ELSE a.sequence END) ASC;";
                    $exec3 = $db->query($sql2, array($metadataFile[metadata_schema_resources][id],0,1)); 
                    $general_pelements = $exec3->fetchAll();
                    $exec3 = NULL;
                    $this->view->assign(compact('general_pelements', 'xml_general', 'db'));


                    return parent::editAction();
                }
            }

            $this->forbiddenAction();
        }//else sacve
    }

    protected function _getAddSuccessMessage($record) {
        return 'The item was successfully added!';
    }

    protected function _getEditSuccessMessage($record) {
        return 'The item was successfully changed!';
    }

    protected function _getDeleteSuccessMessage($record) {
        return 'The item was successfully deleted!';
    }

    protected function _getDeleteConfirmMessage($record) {
        return 'This will delete the item and its associated metadata. It will '
                . 'also delete all files and file metadata associated with this '
                . 'item.';
    }

    public function addAction() {

        if (array_key_exists('add_new_item', $_POST)) {
            $lastexid = savenewtemplate($lastexid, '28');
            $this->view->elementSets = $this->_getItemElementSets();

            $varName = strtolower($this->_modelClass);

            $record = $this->findById($lastexid);

            try {
                if ($record->saveForm($_POST)) {
                    $successMessage = $this->_getEditSuccessMessage($record);
                    if ($successMessage != '') {
                        $this->flashSuccess($successMessage);
                    }
                    $this->redirect->goto('edit', null, null, array('id' => $record->id));
                }
            } catch (Omeka_Validator_Exception $e) {
                $this->flashValidationErrors($e);
            }
            $this->view->assign(array($varName => $record));
            //$this->redirect->goto('edit', null, null, array('id' => $lastexid));
            //$this->render('metadataform',$_POST);
        } else {
            // Get all the element sets that apply to the item.
            $this->view->elementSets = $this->_getItemElementSets();


            return parent::addAction();
        }
    }

    public function showAction() {
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
        Zend_Registry::set('db', $db);
        $_SESSION['get_language_for_internal_xml'] = get_language_for_internal_xml();
        $uri = WEB_ROOT;
        $xml_general = array();
        $execvocele2_general = $db->query("SELECT d.vocabulary_id FROM metadata_element d JOIN  metadata_element_hierarchy e ON d.id = e.element_id WHERE e.datatype_id=? and e.is_visible=?", array(5,1));
        $datavocele2 = $execvocele2_general->fetchAll();
        $execvocele2_general = NULL;
        $sqlvocelem = "SELECT e.value,d.id FROM metadata_vocabulary d JOIN metadata_vocabulary_record e ON d.id = e.vocabulary_id LEFT JOIN
					metadata_vocabulary_value f ON f.vocabulary_rid = e.id WHERE d.id=?";
        foreach ($datavocele2 as $datavocele2) {
            $execvocele = $db->query($sqlvocelem, array($datavocele2['vocabulary_id']));
            $datavocele = $execvocele->fetch();
            $execvocele = NULL;
            //$xmlvoc = '' . $uri . '/archive/xmlvoc/' . $datavocele['value'] . '.xml';
            // $xmlvoc='http://aglr.agroknow.gr/organic-edunet/archive/xmlvoc/new_oe_ontology_hierrarchy.xml';
            $reader = new XMLReader();
            $reader->open('' . $uri . '/archive/xmlvoc/' . $datavocele['value'] . '.xml', 'urf8');
            //$xml = parse_ontologies($reader);
            $xml_general[$datavocele['id']] =  parse_ontologies($reader);
            //$reader->close();
        }

        $metadataFile= Zend_Registry::get('metadataFile');
        //query for creating general elements pelement=0
                    $sql2 = "SELECT a.* FROM metadata_element_hierarchy a JOIN metadata_element b on b.id=a.element_id WHERE b.schema_id=? and a.pelement_id=? and a.is_visible=?  ORDER BY (case WHEN a.sequence IS NULL THEN '9999' ELSE a.sequence END) ASC;";
                    $exec3 = $db->query($sql2, array($metadataFile[metadata_schema_resources][id],0,1)); 
        $general_pelements = $exec3->fetchAll();
        $exec3 = NULL;
        $this->view->assign(compact('general_pelements', 'xml_general', 'db'));

        return parent::showAction();
    }

    /**
     * Delete an item.
     *
     * Wraps the standard deleteAction in permission checks.
     */
    public function deleteAction() {
        if (($user = $this->getCurrentUser())) {
            $item = $this->findById();

            // Permission check
            if ($this->isAllowed('delete', $item)) {


                if (!$this->getRequest()->isPost()) {
                    $this->_forward('method-not-allowed', 'error', 'default');
                    return;
                }

                $record = $this->findById();

                $form = $this->_getDeleteForm();

                if ($form->isValid($_POST)) {
                    $record->delete();
                    deleteitemlomid($item['id']);
                    deleteteasers($item['id']);
                } else {
                    $this->_forward('error');
                    return;
                }

                $successMessage = $this->_getDeleteSuccessMessage($record);
                if ($successMessage != '') {
                    $this->flashSuccess($successMessage);
                }
                $this->redirect->goto('browse');


                //return parent::deleteAction();
            }
        }

        $this->_forward('forbidden');
    }

    /**
     * Finds all tags associated with items (used for tag cloud)
     * 
     * @return void
     */
    public function tagsAction() {
        $params = array_merge($this->_getAllParams(), array('type' => 'Item'));
        $tags = $this->getTable('Tag')->findBy($params);
        $this->view->assign(compact('tags'));
    }

    /**
     * Browse the items.  Encompasses search, pagination, and filtering of
     * request parameters.  Should perhaps be split into a separate
     * mechanism.
     * 
     * @return void
     */
    public function browseAction() {


        $user = current_user();
        $results = $this->_helper->searchItems(array('search' => 'advanced', 'user' => '' . $user['id'] . '', 'role' => '' . $user['role'] . '', 'type' => '28')); //omeka item type=28 => template
        /**
         * Now process the pagination
         * 
         * */
        $paginationUrl = $this->getRequest()->getBaseUrl() . '/items/browse/';

        //Serve up the pagination
        $pagination = array('menu' => null, // This hasn't done anything since $menu was never instantiated in ItemsController::browseAction()
            'page' => $results['page'],
            'per_page' => $results['per_page'],
            'total_results' => $results['total_results'],
            'link' => $paginationUrl);

        Zend_Registry::set('pagination', $pagination);

        fire_plugin_hook('browse_items', $results['items']);

        $this->view->assign(array('items' => $results['items'], 'total_items' => $results['total_items']));
    }

    public function elementFormAction() {
        $elementId = (int) $_POST['element_id'];
        $itemId = (int) $_POST['item_id'];

        // Re-index the element form posts so that they are displayed in the correct order
        // when one is removed.
        $_POST['Elements'][$elementId] = array_merge($_POST['Elements'][$elementId]);

        $element = $this->getTable('Element')->find($elementId);
        try {
            $item = $this->findById($itemId);
        } catch (Exception $e) {
            $item = new Item;
        }

        $this->view->assign(compact('element', 'item'));
    }

    ///// AJAX ACTIONS /////

    /**
     * Find or create an item for this mini-form
     *
     */
    public function changeTypeAction() {
        if ($id = $_POST['item_id']) {
            $item = $this->findById($id);
        } else {
            $item = new Item;
        }

        $item->item_type_id = (int) $_POST['type_id'];
        $this->view->assign(compact('item'));
    }

    /**
     * Display the form for tags for a given item.
     * 
     * @return void
     */
    public function tagFormAction() {
        $item = $this->findById();
        $this->view->assign(compact('item'));
    }

    /**
     * Modify the tags for an item (add or remove).  If this is an AJAX request, it will
     * render the 'tag-list' partial, otherwise it will redirect to the
     * 'show' action.
     * 
     * @return void
     */
    public function modifyTagsAction() {
        $item = $this->findById();

        //Add the tags

        if (array_key_exists('modify_tags', $_POST) || !empty($_POST['tags'])) {
            if ($this->isAllowed('tag')) {
                $currentUser = $this->getInvokeArg('bootstrap')->getResource('Currentuser');
                $tagsAdded = $item->applyTagString($_POST['tags'], $currentUser->Entity);
                // Refresh the item.
                $item = $this->findById();
            } else {
                $this->flashError(__('User does not have permission to add tags.'));
            }
        }

        if (!$this->getRequest()->isXmlHttpRequest()) {
            $itemId = $this->_getParam('id');
            return $this->redirect->gotoRoute(array('controller' => 'items',
                        'action' => 'show',
                        'id' => $itemId), 'id');
        }

        $this->view->assign(compact('item'));
        $this->render('tag-list');
    }

    public function organicAction() {
        $this->render('organic');
        // $this->redirect->goto('browse', null, null, array('id' => 'injest'));
    }

    public function europeanaAction() {
        $this->render('europeana');
        // $this->redirect->goto('browse', null, null, array('id' => 'injest'));
    }

    public function testeuropeanaAction() {
        $this->render('testeuropeana');
        // $this->redirect->goto('browse', null, null, array('id' => 'injest'));
    }

    public function ariadneAction() {
        $this->render('ariadne');
        // $this->redirect->goto('browse', null, null, array('id' => 'injest'));
    }

    public function minihacathonAction() {
        $this->render('minihacathon');
        // $this->redirect->goto('browse', null, null, array('id' => 'injest'));
    }

    public function ingestitemtorepositoryAction() {
        $this->render('ingestitem');
        // $this->redirect->goto('browse', null, null, array('id' => 'injest'));
    }

    public function youtubeAction() {
        $this->render('youtube');
        // $this->redirect->goto('browse', null, null, array('id' => 'injest'));
    }

    public function naturaleuropeAction() {
        $this->render('naturaleurope');
        // $this->redirect->goto('browse', null, null, array('id' => 'injest'));
    }

    public function addinjestitemAction() {
        //print_r($_GET); break;
        $lastexid = injestitem($itemid);

        //$lastexid=1544;
        $this->redirect->goto('edit', null, null, array('id' => $lastexid));
    }

    public function createresourcefromtemplateAction() {
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

//print_r($_GET); break;

        $_POST['item_id'] = (int) $_POST['item_id'];

        //query for all values
        $sql = "SELECT * FROM metadata_record WHERE object_id=" . $_POST['item_id'] . " and object_type='template'";
        $execrecord = $db->query($sql);
        $record = $execrecord->fetch();

        $sqlfortitle = "SELECT * FROM omeka_element_texts where record_id=" . $_POST['item_id'] . " and element_id=68";
        $execsqlfortitle = $db->query($sqlfortitle);
        $rowsqlfortitle = $execsqlfortitle->fetch();

        $record_id = $record['id'];
        if ($record_id) {
            $lastexid = createresourcefromtemplate($record_id, $rowsqlfortitle);

            //$lastexid=1544;
            $this->redirect->goto('edit', 'items', null, array('id' => $lastexid));
        } else {
            $this->redirect->goto('browse');
        }
    }

    public function deleteelementvalueAction() {

        //$lastexid=bypass($lastexid);
        $this->render('deletefromelementvalue');
    }

    public function childsfromparentelementAction() {
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
        //$lastexid=bypass($lastexid);
        libxml_use_internal_errors(false);
        $metadataFile= Zend_Registry::get('metadataFile');
        $uri = WEB_ROOT;
        $xml_general = array();
        $execvocele2_general = $db->query("SELECT DISTINCT d.vocabulary_id FROM metadata_element d JOIN  metadata_element_hierarchy e ON d.id = e.element_id WHERE e.datatype_id=? and e.is_visible=? and d.schema_id=?", array(5,1,$metadataFile[metadata_schema_resources][id]));
        $datavocele2 = $execvocele2_general->fetchAll();
        $execvocele2_general = NULL;
        $sqlvocelem = "SELECT e.value,d.id FROM metadata_vocabulary d JOIN metadata_vocabulary_record e ON d.id = e.vocabulary_id LEFT JOIN
					metadata_vocabulary_value f ON f.vocabulary_rid = e.id WHERE d.id=?";
        foreach ($datavocele2 as $datavocele2) {
        $execvocele = $db->query($sqlvocelem, array($datavocele2['vocabulary_id']));
            $datavocele = $execvocele->fetch();
        $execvocele = NULL;

            $xmlvoc = '' . $uri . '/archive/xmlvoc/' . $datavocele['value'] . '.xml';
            // $xmlvoc='http://aglr.agroknow.gr/organic-edunet/archive/xmlvoc/new_oe_ontology_hierrarchy.xml';
            $reader = new XMLReader();
            $reader->open($xmlvoc, 'urf8');
            $xml = parse_ontologies($reader);


            //$xml_general = array(''.$datavocele['id'].''=>@simplexml_load_file($xmlvoc, NULL, LIBXML_NOERROR | LIBXML_NOWARNING));
            $xml_general[$datavocele['id']] =  $xml;
        }
        $this->view->assign(compact('xml_general'));
        $this->render('childsfromparentelement');
    }

    public function findvocbyidAction() {

        //$lastexid=bypass($lastexid);
        $this->render('findvocbyid');
    }

    public function xmlselectboxAction() {

        //$lastexid=bypass($lastexid);
        $this->render('xmlselectbox');
    }

    public function parsinglomAction() {

        //$lastexid=bypass($lastexid);
        $this->render('parsinglom');
    }

    public function updatelangstringelementvalueAction() {

        //$lastexid=bypass($lastexid);
        $this->render('updatelangstringelementvalue');
    }

    public function selecttemplateAction() {
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
        $this->render('selecttemplate');
    }

    ///// END AJAX ACTIONS /////

    /**
     * Batch editing of Items. If this is an AJAX request, it will
     * render the 'batch-edit' as a partial.
     * 
     * @return void
     */
    public function batchEditAction() {
        /**
         * Only show this view as a partial if it's being pulled via
         * XmlHttpRequest
         */
        $this->view->isPartial = $this->getRequest()->isXmlHttpRequest();

        $itemIds = $this->_getParam('items');
        if (empty($itemIds)) {
            $this->flashError(__('You must choose some items to batch edit.'));
            return $this->_helper->redirector->goto('browse', 'items');
        }

        $this->view->assign(compact('itemIds'));
    }

    /**
     * Processes batch edit information. Only accessible via POST.
     * 
     * @return void
     */
    public function batchEditSaveAction() {
        $hashParam = $this->_getParam('batch_edit_hash');
        $hash = new Zend_Form_Element_Hash('batch_edit_hash');
        if (!$hash->isValid($hashParam)) {
            throw new Omeka_Controller_Exception_403;
        }

        if ($itemIds = $this->_getParam('items')) {
            $metadata = $this->_getParam('metadata');
            $removeMetadata = $this->_getParam('removeMetadata');
            $delete = $this->_getParam('delete');
            $custom = $this->_getParam('custom');

            // Set metadata values to null for "removed" metadata keys.
            if ($removeMetadata && is_array($removeMetadata)) {
                foreach ($removeMetadata as $key => $value) {
                    if ($value) {
                        $metadata[$key] = null;
                    }
                }
            }

            $errorMessage = null;

            if ($metadata && array_key_exists('public', $metadata) && !$this->isAllowed('makePublic')) {
                $errorMessage =
                        __('User is not allowed to modify visibility of items.');
            }

            if ($metadata && array_key_exists('featured', $metadata) && !$this->isAllowed('makeFeatured')) {
                $errorMessage =
                        __('User is not allowed to modify featured status of items.');
            }

            if (!$errorMessage) {
                foreach ($itemIds as $id) {
                    if ($item = $this->getTable('Item')->find($id)) {
                        if ($delete && !$this->isAllowed('delete', $item)) {
                            $errorMessage = __('User is not allowed to delete selected items.');
                            break;
                        }

                        // Check to see if anything but 'tag'
                        if ($metadata && array_diff_key($metadata, array('tags' => '')) && !$this->isAllowed('edit', $item)) {
                            $errorMessage = __('User is not allowed to edit selected items.');
                            break;
                        }

                        if ($metadata && array_key_exists('tags', $metadata) && !$this->isAllowed('tag', $item)) {
                            $errorMessage = __('User is not allowed to tag selected items.');
                            break;
                        }
                        release_object($item);
                    }
                }
            }

            $errorMessage = apply_filters('items_batch_edit_error', $errorMessage, $metadata, $custom, $itemIds);

            if ($errorMessage) {
                $this->flashError($errorMessage);
            } else {
                $dispatcher = Zend_Registry::get('job_dispatcher');
                $dispatcher->send(
                        'Item_BatchEditJob', array(
                    'itemIds' => $itemIds,
                    'delete' => $delete,
                    'metadata' => $metadata,
                    'custom' => $custom
                        )
                );
                $this->flashSuccess(__('The items were successfully changed!'));
            }
        }

        $this->_helper->redirector->goto('browse', 'items');
    }

}