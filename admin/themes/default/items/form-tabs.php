<?php /*?><?php 
$tabs = array();
foreach ($elementSets as $key => $elementSet) {
    $tabName = $elementSet->name;
        
    switch ($tabName) {
        case ELEMENT_SET_ITEM_TYPE:
            // Output buffer this form instead of displaying it right away.
            ob_start();
            include 'item-type-form.php';
            $tabs[$tabName] = ob_get_contents();
            ob_end_clean();
            break;
        
        default:
            $tabContent  = '<span class="element-set-description" id="';
            $tabContent .= html_escape(text_to_id($elementSet->name) . '-description') . '">';            
            $tabContent .= url_to_link($elementSet->description) . '</span>' . "\n\n";
            $tabContent .= display_element_set_form($item, $elementSet->name);
            $tabs[$tabName] = $tabContent;
            break;
    }
}

foreach (array('Collection', 'Files', 'Tags', 'Miscellaneous') as $tabName) {
    ob_start();
    switch ($tabName) {
        case 'Collection':
            require 'collection-form.php';
        break;
        case 'Files':
            require 'files-form.php';
        break;
        case 'Tags':
            require 'tag-form.php';
        break;
        case 'Miscellaneous':
            require 'miscellaneous-form.php';
        break;
    }
    $tabs[$tabName] = ob_get_contents();
    ob_end_clean();
} 

$tabs = apply_filters('admin_items_form_tabs', $tabs, $item);
?>

<!-- Create the sections for the various element sets -->

<ul id="section-nav" class="navigation tabs">
    <?php foreach ($tabs as $tabName => $tabContent): ?>
        <?php if (!empty($tabContent)): // Don't display tabs with no content. '?>
            <li><a href="#<?php echo html_escape(text_to_id($tabName) . '-metadata'); ?>"><?php echo html_escape(__($tabName)); ?></a></li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul><?php */?>


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
?>


	<?php //Metadata start ?>	
<?php if(isset($item['id'])){ ?>



	<ul id="section-nav" class="navigation tabs">
		<?php
		
		//query for creating general elements pelement=0	
		$sql3="SELECT DISTINCT b.id FROM metadata_element_label a LEFT JOIN metadata_element b ON a.element_id = b.id LEFT JOIN metadata_element_hierarchy c 
			ON c.element_id = b.id WHERE c.pelement_id=0 and c.is_visible=1 ORDER BY (case WHEN c.sequence IS NULL THEN '9999' ELSE c.sequence END) ASC;"; 
			$exec3=$db->query($sql3); 
			$datageneral3=$exec3->fetchAll();
			$step=0;
			foreach($datageneral3 as $datageneral3){
			$step+=1;
			
			//end
			$datageneral['labal_name']=return_multi_language_label_name($datageneral3['id']);

	$sql4="SELECT * FROM  metadata_element_hierarchy  WHERE element_id=".$datageneral3['id']." ;"; 
			$exec4=$db->query($sql4); 
			$datageneralqw=$exec4->fetch();
	if($datageneralqw['min_occurs']>0){
		echo '<li id="stepbutton'.$step.'" class="mandatory_element"><a href="#step'.$step.'">'.$datageneral['labal_name'].'</a></li>';
	}elseif($datageneralqw['is_recommented']==1){
		echo '<li id="stepbutton'.$step.'" class="recommented_element"><a href="#step'.$step.'">'.$datageneral['labal_name'].'</a></li>';
	}else{
		echo '<li id="stepbutton'.$step.'" class="optional_element"><a href="#step'.$step.'" >'.$datageneral['labal_name'].'</a></li>';
	}
	

	
			}//foreach datageneral3

		//query for creating general elements pelement=0		 
$sql3="SELECT a.* FROM metadata_element_value a LEFT JOIN metadata_record b ON a.record_id = b.id WHERE b.object_id=".$item['id']." and b.object_type='item' and a.element_hierarchy=34 LIMIT 0,1";
$exec3=$db->query($sql3); 
$itemsource=$exec3->fetch();
//echo "<li>".$itemsource['value']."</li>";
		//end
?>
<li><a id="stepbuttoncollection_aglr" href="#stepbuttoncollection"><?php echo __('Collection'); ?></a></li>
            <?php
 if(($item['item_type_id']===6 or $item['item_type_id']===20) and !(stripos($itemsource['value'],"europeana.eu/portal/")>0 or $itemsource['value']=='Ariadne' or $itemsource['value']=='Natural_Europe_TUC')){ ?>
<li><a id="stepbuttonfile" href="#stepfile" ><?php echo __('File'); ?></a></li>
<?php } else{ ?>
<li><a id="stepbuttonurl" href="#stepurl" ><?php echo __('Url'); ?></a></li>
<?php } ?>
</ul>

<?php } ?>
