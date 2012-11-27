<?php
/**
 * @version $Id$
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2010
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 * @access private
 **/

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
 **/
class OaiController extends Omeka_Controller_Action
{
	
	
    public function indexAction()
    {
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
		$this->getResponse()->setHeader('Content-Type', 'text/xml');
        $this->_helper->viewRenderer->renderScript('oai/index.php');

    }
}