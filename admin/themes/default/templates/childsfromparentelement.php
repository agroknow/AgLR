<?php
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

$sql="SELECT c.*,b.vocabulary_id,c.id as id_hiera,b.id as elm_id FROM  metadata_element b JOIN metadata_element_hierarchy c 
			ON c.element_id = b.id WHERE c.id=".$_POST['element_hierarchy']." and c.is_visible=1  ORDER BY (case WHEN c.sequence IS NULL THEN '9999' ELSE c.sequence END) ASC";
			$exec=$db->query($sql); 
			$dataform=$exec->fetch();
			$exec=NULL;

$sqlchele="SELECT c.*,b.vocabulary_id,b.id as elm_id FROM  metadata_element b JOIN metadata_element_hierarchy c 
			ON c.element_id = b.id WHERE c.pelement_id=".$dataform['elm_id']." and c.is_visible=1  ORDER BY (case WHEN c.sequence IS NULL THEN '9999' ELSE c.sequence END) ASC";
			$execchele=$db->query($sqlchele); 
			$childelements=$execchele->fetchAll();
			$execchele=NULL;
			$childelementscount=count($childelements);
			$parent_multi=$_POST['multi'];
			$depth=1;
			$output='';
			if($childelementscount>0){
			$margindepth=$depth*10;
			//$parentdivcount=1;
			echo '<div style="float:left;border-bottom:1px solid #d7d5c4;padding-right:9px; margin-right:5px;padding-bottom:9px; margin-bottom:5px; 
			width:100%; " id="'.$dataform['id'].'_'.$parent_multi.'">';
			//echo '<div id="'.$dataform['id'].'">';
			$dataform['labal_name']=return_multi_language_label_name($dataform['element_id']);
			$labalname=$dataform['labal_name'];
			if($dataform['max_occurs']>1){
			$labalname.= '&nbsp;&nbsp;<a class="lom-add-new" href="#" onClick="addFormmultiParent(\'0\',\''.$dataform['id'].'\',\'hdnLine_group_total_parent_'.$dataform['id'].'\',\''.$dataform['labal_name'].'\'); return false;">Add '.$dataform['labal_name'].' </a>&nbsp;&nbsp;<a class="lom-remove" href="#" onClick="removedivid(\''.$dataform['id'].'_'.$parent_multi.'\'); return false;">Remove '.$dataform['labal_name'].'</a>';
			}
			echo '<input name="'.$dataform['id'].'_'.$parent_multi.'" id="'.$dataform['id'].'_'.$parent_multi.'" type="hidden" value="">';		
			echo '<div style="float:left;">'.createlomlabel($labalname,'for='.$dataform['id'].' style="width:608px;"').'</div><br>';
			
			echo'<br style="clear:both;">';
			$margindepth+=10;
			echo'<div style="margin-left:'.$margindepth.'px;">';
			//echo $childelements['labal_name'];
			foreach($childelements as $childelements){
			//$depth+=1;
			//echo $childelements['labal_name'];
			$extra='style="font-weight:normal;"';
			if($childelements['element_id']===48){$extra="onchange='change49(this.value)'";}
			checkelement($childelements,$datalan,$record,$depth,$extra,$parent_multi,NULL,NULL,$xml_general);
			}
			echo'</div>';
			
			
		echo'</div><br style="clear:both;">';
			}//if isset if(childelements['id']>0){

?>