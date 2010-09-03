<?php

//Switch page target depending on version
function ceo_getTarget() {
	return "admin.php";
}

global $wpdb;
	
$mode = "";
$mode = $_GET['mode'];
$startingID = 13;
$parentID = 13;
$success = "";
if (isset($_GET['parentID']))
    $parentID = $_GET['parentID'];
		
	$wpdb->show_errors();

	$query1 = $wpdb->query("SHOW COLUMNS FROM $wpdb->terms LIKE 'term_order'");
	
	if ($query1 == 0) {
		$wpdb->query("ALTER TABLE $wpdb->terms ADD `term_order` INT( 4 ) NULL DEFAULT '0'");
	}

	if($mode == "act_OrderCategories")
	{  
		$idString = $_GET['idString'];
		$catIDs = explode(",", $idString);
		$result = count($catIDs);
		for($i = 0; $i < $result; $i++)
		{	
			$wpdb->query("UPDATE $wpdb->terms SET term_order = '$i' WHERE term_id ='$catIDs[$i]'");
		}
		$success = '<div id="message" class="updated fade"><p>'. __('Categories updated successfully.', 'ceo').'</p></div>';
	}

	$subCatStr = '';
	if ($parentID == $startingID) {
		$results=$wpdb->get_results("SELECT t.term_id, t.name FROM $wpdb->term_taxonomy tt, $wpdb->terms t, $wpdb->term_taxonomy tt2 WHERE tt.parent = $startingID AND tt.taxonomy = 'category' AND t.term_id = tt.term_id AND tt2.parent = tt.term_id GROUP BY t.term_id, t.name HAVING COUNT(*) > 0 ORDER BY t.term_order ASC");
		foreach($results as $row) {
			$subCatStr = $subCatStr."<option value='$row->term_id' style='width: 100px;'>$row->name</option>";
		}
	}
?>
<style>
	li.lineitem {
		margin: 3px 0px;
		padding: 2px 5px 2px 5px;
		background-color: #F1F1F1;
		border:1px solid #B2B2B2;
		cursor: move;
	}
</style>

<script language="JavaScript">
	
	function ceo_addloadevent(){
		jQuery("#order").sortable({ 
			placeholder: "ui-selected", 
			revert: false,
			tolerance: "pointer" 
		});
	};

	addLoadEvent(ceo_addloadevent);

	function orderCats() {
		jQuery("#orderButton").css("display", "none");
		jQuery("#updateText").html("<?php _e('Updating Category Order...','comiceasel'); ?>");
		
		idList = jQuery("#order").sortable("toArray");
		location.href = 'admin.php?page=comiceasel-storyline&mode=act_OrderCategories&parentID=<?php echo $parentID; ?>&idString='+idList;
	}
	function goEdit ()
	{
		if(jQuery("#cats").val() != "")
			location.href="admin.php?page=comiceasel-storyline&parentID="+jQuery("#cats").val();
	}
</script>
	<div class='wrap' style="width: 500px;">
		<h2><?php _e('Storyline Books / Chapters','comiceasel'); ?></h2>
	<?php 
	
	echo $success;  ?>

	<p><?php _e('Choose a category from the drop down to order subcategories in that category or order the categories on this level by dragging and dropping them into the desired order.','ceo'); ?></p>

<?php
	$cat_args = array('orderby' => $orderby, 'order' => $order, 'show_last_updated' => $show_last_updated, 'show_count' => $show_count, 
	'hide_empty' => 1, 'child_of' => $startingID, 'hierarchical' => 1);
	$infocats = get_categories($cat_args);
//	var_dump($infocats);
	 
	if ($parentID != $startingID) {
		$parentsParent = $wpdb->get_row("SELECT parent FROM $wpdb->term_taxonomy WHERE term_id = $parentID ", ARRAY_N);
		echo "<a href='admin.php?page=comiceasel-storyline&parentID=${parentsParent[0]}'>".__('Return to book ordering.','ceo')."</a>";
	}

	if($subCatStr != "") { ?>
	<select id="cats" name="cats">
		<?php echo $subCatStr; ?>
	</select>
	&nbsp;<input type="button" name="edit" Value="<?php _e('Select Chapter','comiceasel'); ?>" onClick="javascript:goEdit();">
<?php }
	$results=$wpdb->get_results("SELECT * FROM $wpdb->terms t inner join $wpdb->term_taxonomy tt on t.term_id = tt.term_id WHERE taxonomy = 'category' and parent = $parentID ORDER BY term_order ASC"); ?>
	<h3><?php if ($parentID != $startingID) { _e('Order Chapters','ceo'); } else { _e('Order Books','ceo'); }?></h3>
	    <ul id="order" style="width: 300; margin:10px 10px 10px 0px; padding:5px; border:1px solid #B2B2B2; list-style:none;">
		<?php foreach($results as $row) {
			echo "<li id='$row->term_id' class='lineitem'>$row->name</li>";
		} ?>
	</ul>
	<input type="button" id="orderButton" Value="<?php _e('Save Re-Order','comiceasel'); ?>" onclick="javascript:orderCats();">&nbsp;&nbsp;<strong id="updateText"></strong>
</div>

<?php

function ceo_applyorderfilter($orderby, $args)
{
	if($args['orderby'] == 'order')
		return 't.term_order';
	else
		return $orderby;
}

add_filter('get_terms_orderby', 'ceo_applyorderfilter', 10, 2);
?>
