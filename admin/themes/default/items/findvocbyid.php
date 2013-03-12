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
$output = "<div id='row" . $_POST['id'] . "' style='position:relative; margin-top:10px;'><select name='" . $_POST['divid'] . "_" . $_POST['id'] . "' class='combo' style='width:300px;float:left;'>";
$output.= "<option value=''>Select</option>";


if ($_POST['vocabulary_id'] > 0) {//select and isset vocabulary
    $sqlvocelem = "SELECT f.label,e.id as vov_rec_id FROM metadata_vocabulary_record e JOIN
					metadata_vocabulary_value f ON f.vocabulary_rid = e.id WHERE e.vocabulary_id=" . $_POST['vocabulary_id'] . " and e.public=1  and f.language_id='" . get_language_for_switch() . "'  ORDER BY (case WHEN e.sequence IS NULL THEN '99999' END),e.sequence,f.label ASC";
    $execvocele = $db->query($sqlvocelem);
    $datavocele = $execvocele->fetchAll();
}

$size_of_objects = sizeof($datavocele);
if($size_of_objects>0)
{
for($i=0;$i<$size_of_objects;$i++)
{
$object =& $datavocele[$i];
 $output.= "<option value='" . $object['vov_rec_id'] . "'>" . $object['label'] . "</option>";
}
}
$output.= "</select> <a class='lom-remove' style='float:left;' href='#' onClick='removeFormField(\"#row" . $_POST['id'] . "\"); return false;'>Remove</a><br><br style='clear:both;'></div>";
echo $output;
?>