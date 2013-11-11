<?php

/**
 * @version $Id$
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2010
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 * @access private
 * */

/**
 * The only thing this controller does is load the home page of the theme
 * at index.php within any given theme.
 *
 * @internal This implements Omeka internals and is not part of the public API.
 * @access private
 * @package Omeka
 * @subpackage Controllers
 * @author CHNM
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2010
 * */
class JsonplayerController extends Omeka_Controller_Action {

    public function indexAction() {
        //custom code for loop all exhibits that are public
        //$menuexhibits = exhibit_builder_get_exhibits(array('public' => '1'));
        //$params = $this->_getAllParams();
        //$params['user']=1;
        //$params['public']=1;
        //$menuexhibits=get_db()->getTable('Exhibit')->findBy($params);
        //print_r($params);
        //$this->view->assign(compact('menuexhibits'));
        //$this->render('oai/oai2/index');
        //$this->_helper->layout->disableLayout();
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
        $metadataFile = parse_ini_file($metadataFile, true);
        Zend_Registry::set('metadataFile', $metadataFile);
        $this->view->assign(compact('metadataFile'));
        Zend_Registry::set('db', $db);

        $_SESSION['get_language_for_internal_xml'] = get_language_for_internal_xml();
        $uri = WEB_ROOT;
        $xml_general = array();
        $execvocele2_general = $db->query("SELECT DISTINCT d.vocabulary_id FROM metadata_element d JOIN  metadata_element_hierarchy e ON d.id = e.element_id WHERE e.datatype_id=? and e.is_visible=? and d.schema_id=?", array(5, 1, 1));
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
            $new_xml_value = array();
            $i = 0;
            $xml = $reader;

            while ($xml->read()) {


                switch ($xml->nodeType) {
                    case (XMLReader::ELEMENT):
                        if ($xml->localName == 'instance' and ($xml->getAttribute('lang') == $_SESSION['get_language_for_internal_xml'] or $xml->getAttribute('lang') == 'en')) {
                            $i++;
                            $instanceOfvalue = $xml->getAttribute('instanceOf');
                            $instanceLang = $xml->getAttribute('lang');
                            $xml->read();
                            $instanceValue = $xml->value;
                            array_push($new_xml_value, array('id' => $i, 'instanceOf' => $instanceOfvalue, 'value' => $instanceValue, 'lang' => $instanceLang));
                        }
                }
            }
            // $new_xml_value=array(1,3);
            //echo $_SESSION['get_language_for_internal_xml'];
            //print_r($new_xml_value);


            $xml_general[$datavocele['id']] = $new_xml_value;
            unset($reader);
            unset($xml);
            //$reader->close();
        }



        Zend_Registry::set('xml_general', $xml_general);

        $standar_query = "SELECT * FROM  metadata_element_value WHERE record_id=? and element_hierarchy=? ORDER BY multi ASC ;";
        Zend_Registry::set('standar_query', $standar_query);
        $get_label_elem_sqltr = "SELECT * FROM metadata_element_label WHERE element_id=? and language_id=?";
        Zend_Registry::set('get_label_elem_sqltr', $get_label_elem_sqltr);
        $get_label_elem_sqltrrd = "SELECT * FROM metadata_element_label WHERE element_id=? LIMIT 1";
        Zend_Registry::set('get_label_elem_sqltrrd', $get_label_elem_sqltrrd);

        $get_label_voc_sqltr = "SELECT * FROM metadata_vocabulary_value WHERE vocabulary_rid=? and language_id=?";
        Zend_Registry::set('get_label_voc_sqltr', $get_label_voc_sqltr);
        $get_label_voc_sqltrrd = "SELECT * FROM metadata_vocabulary_value WHERE vocabulary_rid=? LIMIT 1";
        Zend_Registry::set('get_label_voc_sqltrrd', $get_label_voc_sqltrrd);

        $this->getResponse()->setHeader('Content-Type', 'text/xml');
        $this->_helper->viewRenderer->renderScript('jsonplayer/index.php');
    }

}