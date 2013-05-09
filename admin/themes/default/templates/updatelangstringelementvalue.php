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
$sqllan="SELECT * FROM metadata_record WHERE id=".$_POST['record_id']." ";
$execlan=$db->query($sqllan);
$result_rec=$execlan->fetch();
$execlan=null;

$result_multi=get_db()->getTable('Item')->find($result_rec['object_id']);

if($user['id']==1 or $user['id']==11 or $result_multi->wasAddedBy(current_user()) or has_permission($result_multi, 'edit')){  //if he has add the exhibit 
$sqllan="UPDATE metadata_element_value SET  language_id='".$_POST['language_id']."' WHERE element_hierarchy=".$_POST['element_hierarchy']." and language_id='".$_POST['language_id_old']."' and record_id=".$_POST['record_id']." and multi=".$_POST['multi'].""; //echo $sqllan; break;
$execlan=$db->query($sqllan);
if($execlan){
return true; } else{ return false; }

}
else{ return false; }

?>