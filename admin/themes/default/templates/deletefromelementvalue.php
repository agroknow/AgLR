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
$user = current_user();
$sqllan="SELECT * FROM metadata_record WHERE id=".$_POST['record_id']."";
$execrec=$db->query($sqllan);
$result_rec=$execrec->fetch();
$execrec=null;



$result_multi=get_db()->getTable('Item')->find($result_rec['object_id']);

if($user['id']==1 or $user['id']==11 or $result_multi->wasAddedBy(current_user()) or has_permission($result_multi, 'edit')){  //if he has add the exhibit 

if(isset($_POST['allvalues']) and $_POST['allvalues']==1){

$sqllan="DELETE  FROM metadata_element_value WHERE record_id=".$_POST['record_id']." and element_hierarchy=".$_POST['element_hierarchy']." and multi=".$_POST['multi'].""; //echo $sqllan; break;
$execlan=$db->query($sqllan);
if($execlan){
return true; } else{ return false; }

} elseif(isset($_POST['vcard']) and $_POST['vcard']==1){

$sqllan="DELETE  FROM metadata_element_value WHERE element_hierarchy=".$_POST['element_hierarchy']." and language_id='none' and record_id=".$_POST['record_id']." and multi=".$_POST['multi']."  and parent_indexer=".$_POST['parent_indexer'].""; //echo $sqllan; break;
$execlan=$db->query($sqllan);
if($execlan){
return true; } else{ return false; }


} elseif(isset($_POST['parent_element']) and $_POST['parent_element']==1){


$sql="SELECT * FROM metadata_element_hierarchy WHERE id=".$_POST['element_hierarchy']."";
			$exec=$db->query($sql); 
			$dataform=$exec->fetch();
			$exec=NULL;

$sqlchele="SELECT c.* FROM  metadata_element b  LEFT JOIN metadata_element_hierarchy c 
			ON c.element_id = b.id WHERE c.pelement_id=".$dataform['element_id']."";
			$execchele=$db->query($sqlchele); 
			$childelements=$execchele->fetchAll();
			$execchele=NULL;
			$childelementscount=count($childelements);
			$parent_multi=$_POST['multi'];
			if($childelementscount>0){
foreach($childelements as $childelements){
$sqllan="DELETE  FROM metadata_element_value WHERE element_hierarchy=".$childelements['id']." and language_id='none' and record_id=".$_POST['record_id']." and multi=".$_POST['multi'].""; //echo $sqllan;// break;
$execlan=$db->query($sqllan);
if($execlan){

$sqllan="DELETE  FROM metadata_element_value WHERE element_hierarchy=".$_POST['element_hierarchy']." and language_id='none' and record_id=".$_POST['record_id']." and multi=".$_POST['multi'].""; //echo $sqllan;// break;
$execlan=$db->query($sqllan);
if($execlan){
return true; } else{ return false; }

 } else{ return false; }
}//forech child

}//if($childelementscount>0)




}else{

$sqllan="DELETE  FROM metadata_element_value WHERE element_hierarchy=".$_POST['element_hierarchy']." and language_id='".$_POST['language_id']."' and record_id=".$_POST['record_id']." and multi=".$_POST['multi'].""; //echo $sqllan; break;
$execlan=$db->query($sqllan);
if($execlan){
return true; } else{ return false; }

}//else allvalues

}
else{ return false; }

?>