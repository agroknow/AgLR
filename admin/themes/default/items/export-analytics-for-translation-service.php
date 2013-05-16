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
        echo "<table>";
        echo "<tr><td>User </td><td> Service </td><td> Original Text </td><td> Original Text Lang </td><td> Translated Text </td><td> Translated Text Lang </td><td> User Input </td><td> Element </td><td> Resource </td></tr>";

        $execvocele2_general = $db->query("SELECT f.title,a.*,e.first_name,e.last_name,e.middle_name FROM omeka_translation_analytics a JOIN  omeka_entities e ON e.id = a.user_id JOIN  omeka_translation_analytics_service f ON f.id = a.service_id");
        $datavocele2 = $execvocele2_general->fetchAll();
        $execvocele2_general = NULL;
        foreach ($datavocele2 as $datavocele2) {
            
         echo "<tr><td>".$datavocele2['last_name']." ".$datavocele2['middle_name']." ".$datavocele2['first_name']."</td><td> ".$datavocele2['title']." </td><td> ".$datavocele2['original_text']." </td><td> ".$datavocele2['original_text_lang']." </td><td> ".$datavocele2['translated_text']." </td><td> ".$datavocele2['translated_text_lang']." </td><td> ".$datavocele2['user_fixed_text']." </td><td> ".$datavocele2['element_id']." </td><td> ".$datavocele2['record_id']." </td></tr> ";   
        }
        echo "</table>";

?>
