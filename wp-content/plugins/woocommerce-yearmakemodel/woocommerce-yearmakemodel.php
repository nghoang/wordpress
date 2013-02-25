<?php
/* 
Plugin Name: WooCommerce Year Make Model
Description: WooCommerce Year Make Model
Version: 1.2.1
Author: Hoang Ong
*/

 
class woocommerce_yearmakemodel
{
	var $PLUGIN_PATH;
	var $PLUGIN_URL;
	var $PLUGIN_ADMIN_PAGE;
	
	function __construct()
	{
		$this->PLUGIN_ADMIN_PAGE = $_GET["page"];
		$temp = pathinfo(__FILE__);
		$this->PLUGIN_PATH = $temp["dirname"];
		$this->PLUGIN_URL = get_bloginfo("wpurl") . '/wp-content/plugins/' . $temp["filename"];
	
		add_action('init', array(&$this,'init'));	
		add_action('woocommerce_product_write_panel_tabs', array(&$this,'woocommerce_product_write_panel_tabs'));	
		add_action('woocommerce_product_write_panels', array(&$this,'woocommerce_product_write_panels'));	
		add_action('admin_menu', array(&$this,'admin_menu'));
		register_activation_hook(__FILE__, array(&$this,'install'));
		register_deactivation_hook(__FILE__, array(&$this,'uninstall'));
		add_filter('loop_shop_post_in', array(&$this,'loop_shop_post_in'));
		
		wp_register_script( 'jquery', $this->PLUGIN_URL . '/js/jquery-1.9.0.js');
		wp_enqueue_script("jquery");
		
		wp_register_script( 'jquery-ui', $this->PLUGIN_URL . '/js/jquery-ui-1.10.0.custom.min.js');
		wp_enqueue_script("jquery-ui");
		
		wp_register_script( 'ymm-script', $this->PLUGIN_URL . '/script.js');
		wp_enqueue_script("ymm-script");
		
		wp_register_style( 'ymm-style', $this->PLUGIN_URL . '/style.css');
		wp_enqueue_style("ymm-style");

		add_shortcode('treesearch', array(&$this,'treesearch'));
	}
	
	function admin_menu()
	{
		add_submenu_page('woocommerce','Add MMY Information', 'Add MMY Information', 'add_users', 'add_mmy_information', array(&$this,"add_mmy_information"));
	}
	
	function add_mmy_information()
	{
		global $wpdb;
		
		$table = "make";
		$parent_id = $_GET["parent_id"];
		if (isset($_GET["table"]))
			$table = $_GET["table"];
		if ($table == "model")
			$where = " AND make_id='{$parent_id}'";
		elseif ($table == "year")
			$where = " AND model_id='{$parent_id}'";
		$query = $wpdb->get_results("SELECT * FROM ymm_{$table} WHERE 1=1 $where");
		
		if ($table == "make")
			$field_name = "Make";
		elseif ($table == "model")
			$field_name = "Model";
		elseif ($table == "year")
			$field_name = "Year";
		
		if ($table == "make")
			$sub_field = "model";
		elseif ($table == "model")
			$sub_field = "year";
		
		$parent_field = "";
		if ($table == "model")
			$parent_field = "make";
		elseif ($table == "year")
			$parent_field = "model";
		
		?>
		<h2>Add MMY Information</h2>
		<h3>List <?php echo $field_name; ?> Information</h3>
		<?php
		if ($table == "model")
			echo '<a href="admin.php?page=add_mmy_information&table='.$parent_field.'">Back</a>';
		else
		if ($parent_field != '')
			echo '<a href="admin.php?page=add_mmy_information&table='.$parent_field.'&parent_id='.$parent_id.'">Back</a>';
		?>
		<table border="1">
			<tr>
				<td><strong><?php echo $field_name; ?> Name<strong></td>
				<td><strong>Actions<strong></td>
			</tr>
		<? foreach ($query as $row): ?>
			<tr>
				<td><?php echo $row->{$table."_name"};?></td>
				<td><?php
		
				if ($table != "year")
					echo '<a href="admin.php?page=add_mmy_information&table='.$sub_field.'&parent_id='.($row->{$table . "_id"}).'">Sub Items</a>';
				
				echo ' <a onClick="return confirmDelete()" href="admin.php?act=delete_ymm_item&page=add_mmy_information&table='.$table.'&delete_id='.($row->{$table . "_id"}).'">Delete</a>'; 
				?></td>
			</tr>
		<? endforeach; ?>
		</table>
		
		<h3>Add <?php echo $field_name; ?> Information</h3>
		<form method="post" >
			<table>
				<tr>
					<td><strong><?php echo $field_name; ?> Name<strong></td>
					<td><input type="text" name="name" /></td>
				</tr>
				<tr>
					<td colspan="2"><input type="submit" name="smAddYMMInfo" value="Add" /></td>
				</tr>
			</table>
		</form>
		<?php
	}

	function loop_shop_post_in($filtered_posts)
	{
		global $wpdb;
		if (isset($_GET["ymm_year"]) ||
			isset($_GET["ymm_model"]) ||
			isset($_GET["ymm_make"]))
		{
			$filtered_posts = array();
			$query = $wpdb->get_results("SELECT * FROM ymm_product WHERE year_id='".$_GET["ymm_year"]."' AND model_id='".$_GET["ymm_model"]."' AND make_id='".$_GET["ymm_make"]."'");
			foreach ($query as $row)
			{
				$filtered_posts[] = $row->product_id;
			}
		}
		
		return $filtered_posts;
	}
	
	function woocommerce_product_write_panel_tabs()
	{
		echo '<li class="advanced_tab"><a href="#attribute_tree_data">Year - Make - Model</a></li>';
	}
	
	function woocommerce_product_write_panels()
	{
		global $wpdb;
		$product_id = $_GET["post"];
		$row = $wpdb->get_results("SELECT DISTINCT make_id,model_id FROM ymm_product WHERE product_id={$product_id}");
		
		
		echo '<div id="attribute_tree_data" class="panel woocommerce_options_panel" style="padding: 10px;" >
		<input type="hidden" name="attribute_tree_data" value="1"/>
		<div id="attribute_list" style="padding:10px;"><h4>Attributes</h4>
		<button onClick="return AppendAttribute()">Add More</button><br>';
		$count_block = 1;
		
		foreach ($row as $r){
			$query_y = $wpdb->get_results("SELECT year_id FROM ymm_product WHERE product_id={$product_id} AND make_id='{$r->make_id}' AND model_id='{$r->model_id}'");
			$years = array();
			foreach ($query_y as $y){
				$years[] = $y->year_id;
			}
			
			$makes = $wpdb->get_results("SELECT * FROM `ymm_make`");
			$models = $wpdb->get_results("SELECT * FROM `ymm_model` WHERE make_id={$r->make_id}");
		
			$select_makes = 'Make: <select name="ymm_make[]" onChange="GetModelDropbox(this,'.$count_block.')">';
			$select_makes .= '<option value="">Select Make</option>';
		
			foreach ($makes as $m){
				if ($m->make_id == $r->make_id)
					$select_makes .= '<option selected="selected" value="'.$m->make_id.'">'.$m->make_name.'</option>';
				else
					$select_makes .= '<option value="'.$m->make_id.'">'.$m->make_name.'</option>';
			}
			$select_makes .= '</select>';
		
			$select_models = 'Model: <select name="ymm_model[]">';
			$select_models .= '<option value="">Select Model</option>';
			foreach ($models as $m){
				if ($m->model_id == $r->model_id)
					$select_models .= '<option selected="selected" value="'.$m->model_id.'">'.$m->model_name.'</option>';
				else
					$select_models .= '<option value="'.$m->model_id.'">'.$m->model_name.'</option>';
			}
			$select_models .= '</select>';
			
			$rowy = $wpdb->get_results("SELECT * FROM ymm_year WHERE model_id='{$r->model_id}'");
			$contenty = '';
			$i = 1;
			foreach ($rowy as $ry){
				$temp = '';
				if (in_array($ry->year_id,$years))
					$temp = ' checked="checked" ';
				if ($i % 7 == 0)
					$contenty .= '<input type="checkbox" '.$temp.' name="ymm_year_'.$count_block.'[]" value="'.$ry->year_id.'" /> ' . $ry->year_name . "<br>";
				else
					$contenty .= '<input type="checkbox" '.$temp.' name="ymm_year_'.$count_block.'[]" value="'.$ry->year_id.'" /> ' . $ry->year_name . " &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				$i++;
			}
			//echo $contenty;
			
			echo "{$select_makes} <span id='model_container_{$count_block}'>{$select_models}</span> <div id='year_container_{$count_block}'>{$contenty}</div>";
			$count_block++;
		}
		echo '</div></div>';
		
		
		
		$temp = '<option value="">Select Make</option>';
		foreach ($makes as $m){
			if ($m->make_id == $make_id)
				$temp .= '<option selected="selected" value="'.$m->make_id.'">'.$m->make_name.'</option>';
			else
				$temp .= '<option value="'.$m->make_id.'">'.$m->make_name.'</option>';
		}
		$temp .= '</select>';
		
		
		echo '<script>
		var block_count = '.$count_block.';
		function AppendAttribute()
		{
			jQuery("#attribute_list").append("<br>Make: <select name=\"ymm_make[]\" onChange=\"GetModelDropbox(this,"+block_count+")\" id=\"ymm_make\">'.str_replace("\n",' ',str_replace('"','\"',$temp)).' <span id=\'model_container_"+block_count+"\'></span> <div id=\'year_container_"+block_count+"\'></div>");
			block_count++;
			return false;
		}
		jQuery(document).ready(function(){ AppendAttribute(); });
		</script>';
	}
	
	function install()
	{
		 global $wpdb;
		 
		 $wpdb->query("CREATE TABLE IF NOT EXISTS `ymm_make` (
		  `make_id` int(11) NOT NULL auto_increment,
		  `make_name` varchar(255) NOT NULL,
		  PRIMARY KEY  (`make_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
		
		$wpdb->query("CREATE TABLE IF NOT EXISTS `ymm_model` (
		  `model_id` int(11) NOT NULL auto_increment,
		  `make_id` int(11) NOT NULL,
		  `model_name` varchar(255) NOT NULL,
		  PRIMARY KEY  (`model_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
		
		$wpdb->query("CREATE TABLE IF NOT EXISTS `ymm_year` (
		  `year_id` int(11) NOT NULL auto_increment,
		  `model_id` int(11) NOT NULL,
		  `year_name` varchar(255) NOT NULL,
		  PRIMARY KEY  (`year_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
		
		$wpdb->query("CREATE TABLE IF NOT EXISTS `ymm_product` (
		  `ymm_id` int(11) NOT NULL auto_increment,
		  `product_id` int(11) NOT NULL,
		  `year_id` int(11) NOT NULL,
		  `make_id` int(11) NOT NULL,
		  `model_id` int(11) NOT NULL,
		  `created_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
		  PRIMARY KEY  (`ymm_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
	}
	
	function uninstall()
	{
		
	}
	
	function init()
	{
		global $wpdb;
		
		if (isset($_POST["update_attribute_list"]))
		{
			$product_id = $_POST["post_ID"];
			$years = $_POST["ymm_data_year"];
			$makes = $_POST["ymm_data_make"];
			$models = $_POST["ymm_data_model"];
			$wpdb->query("DELETE FROM ymm_product WHERE product_id='{$product_id}'");
			
			for ($i=0;$i<count($years);$i++)
			{
				$make = $makes[$i];
				$year = $years[$i];
				$model = $models[$i];
				
				if ($make == "" && $year == "" && $model == "")
					continue;
				
				$count = $wpdb->get_var("SELECT COUNT(*) FROM ymm_make WHERE make_name = '{$make}'");
				if ($count == 0)
					$wpdb->get_var("INSERT INTO ymm_make SET make_name = '{$make}'");
				$make_id = $wpdb->get_var("SELECT make_id FROM ymm_make WHERE make_name = '{$make}'");
				
				$count = $wpdb->get_var("SELECT COUNT(*) FROM ymm_model WHERE model_name = '{$model}' AND make_id={$make_id}");
				if ($count == 0)
					$wpdb->get_var("INSERT INTO ymm_model SET model_name = '{$model}', make_id={$make_id}");
				$model_id = $wpdb->get_var("SELECT model_id FROM ymm_model WHERE model_name = '{$model}' AND make_id={$make_id}");
				
				$count = $wpdb->get_var("SELECT COUNT(*) FROM ymm_year WHERE year_name = '{$year}' AND model_id={$model_id}");
				if ($count == 0)
					$wpdb->get_var("INSERT INTO ymm_year SET year_name = '{$year}', model_id={$model_id}");
				$year_id = $wpdb->get_var("SELECT year_id FROM ymm_year WHERE year_name = '{$year}' AND model_id={$model_id}");
				
				$wpdb->query("INSERT INTO ymm_product SET make_id='{$make_id}', 
				model_id='{$model_id}', 
				product_id = '{$product_id}',
				year_id='{$year_id}'");
			}
		}
		
		if ($_GET["act"] == "delete_ymm_item")
		{
			$table = $_GET["table"];
			$item_id = $_GET["delete_id"];
			$wpdb->query("DELETE FROM ymm_{$table} WHERE {$table}_id={$item_id}");
			$this->action_message = "Item is deleted.";
			header("Location: admin.php?page=add_mmy_information&table={$table}&parent_id={$item_id}");
			die();
		}
		
		if (isset($_POST["smAddYMMInfo"]))
		{
			$table = $_GET["table"];
			$item_id = $_GET["parent_id"];
			if ($table == "make")
			{
				$wpdb->query("INSERT INTO ymm_make SET make_name='{$_POST["name"]}'");
			}
			elseif ($table == "model")
			{
				$wpdb->query("INSERT INTO ymm_model SET model_name='{$_POST["name"]}', make_id={$item_id}");
			}
			elseif ($table == "make")
			{
				$wpdb->query("INSERT INTO ymm_year SET year_name='{$_POST["name"]}', model_id={$item_id}");
			}
		}
		
		if (isset($_REQUEST["attribute_tree_data"])){
			$product_id = $_POST["post_ID"];
			$wpdb->query("DELETE FROM ymm_product WHERE product_id='{$product_id}'");
			for($i=0;$i < count($_REQUEST["ymm_make"]);$i++){
				$make = $_REQUEST["ymm_make"][$i];
				$model = $_REQUEST["ymm_model"][$i];
				if (empty($make) || empty($model))
					continue;
				$years = $_REQUEST["ymm_year_" . ($i+1)];
				foreach ($years as $year){
					$wpdb->query("INSERT IGNORE INTO ymm_product SET make_id='{$make}', model_id='{$model}', year_id='{$year}', product_id='{$product_id}'");
				}
			}
		}
		
		if ($_REQUEST["action"] == 'GetModelDropbox') {
			$make_id = $_REQUEST["make_id"];
			$block_id = $_REQUEST["block_id"];
			$query = $wpdb->get_results("SELECT * FROM ymm_model WHERE make_id='{$make_id}'");
			$content = 'Model: <select name="ymm_model[]" onChange="GetYearList(this,'.$block_id.')"><option value="">Select Model</option>';
			foreach ($query as $r)
				$content .= '<option value="'.$r->model_id.'">'.$r->model_name.'</option>';
			$content .= '</select>';
			echo $content;
			die();
		}
		
		if ($_REQUEST["action"] == "GetYearList"){
			$model_id = $_REQUEST["model_id"];
			$block_id = $_REQUEST["block_id"];
			$query = $wpdb->get_results("SELECT * FROM ymm_year WHERE model_id='{$model_id}'");
			$content = '';
			$i = 1;
			foreach ($query as $r){
				if ($i % 7 == 0)
					$content .= '<input type="checkbox" name="ymm_year_'.$block_id.'[]" value="'.$r->year_id.'" /> ' . $r->year_name . "<br>";
				else
					$content .= '<input type="checkbox" name="ymm_year_'.$block_id.'[]" value="'.$r->year_id.'" /> ' . $r->year_name . " &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				$i++;
			}
			echo $content;
			die();
		}
		
		if (isset($_GET["search_product_attribute"]))
		{
			$result = array();
			$table = $_GET["data"];
			$term = $_GET["term"];
			$query = $wpdb->get_results("SELECT * FROM ymm_".$table." WHERE ".$table."_name LIKE '{$term}%'",ARRAY_A);
			foreach ($query as $row)
			{
				$result[] = array(
					"id" => $row[$table . "_name"],
					"label" => $row[$table . "_name"],
					"value" => $row[$table . "_name"]
				);
			}
			echo json_encode($result);
			die();
		}
		
		if (isset($_POST["smTreeSearch"]))
		{
			wp_redirect("/?post_type=product&ymm_year=".$_POST["ymm_year"]."&ymm_make=".$_POST["ymm_make"]."&ymm_model=" . $_POST["ymm_model"]);
			die();
		}
	}
	
	function treesearch($atts, $content="") {
		global $wpdb;
		$makes = $wpdb->get_results("SELECT * FROM ymm_make");
		$content = '<form method="post" id="treesearch_form">
			<div class="ymm_section"><span>Make:</span> <select name="ymm_make">';
		$content .= '<option value="">Select Make</option>';
		foreach($makes as $m)
		{
			if ($m->make_id == $_POST["ymm_make"])
				$content .= '<option selected="selected" value="'.$m->make_id.'">'.$m->make_name.'</option>';
			else
				$content .= '<option value="'.$m->make_id.'">'.$m->make_name.'</option>';
		}
		$content .= '</select></div>';
		
		if ($_POST["ymm_make"] != "")
		{
			$make_id = $_POST["ymm_make"];
			$models = $wpdb->get_results("SELECT * FROM ymm_model WHERE make_id='{$make_id}'");
			$content .= '<div class="ymm_section"><span>Model:</span> <select name="ymm_model">';
			$content .= '<option value="">Select Model</option>';
			foreach($models as $m)
			{
				if ($m->model_id == $_POST["ymm_model"])
					$content .= '<option selected="selected" value="'.$m->model_id.'">'.$m->model_name.'</option>';
				else
					$content .= '<option value="'.$m->model_id.'">'.$m->model_name.'</option>';
			}
			$content .= '</select></div>';
		}
		
		if ($_POST["ymm_model"] != "")
		{
			$model_id = $_POST["ymm_model"];
			$years = $wpdb->get_results("SELECT * FROM ymm_year WHERE model_id='{$model_id}'");
			$content .= '<div class="ymm_section"><span>Year:</span> <select name="ymm_year">';
			$content .= '<option value="">Select Year</option>';
			foreach($years as $m){
				if ($m->year_id == $_POST["ymm_year"])
					$content .= '<option selected="selected" value="'.$m->year_id.'">'.$m->year_name.'</option>';
				else
					$content .= '<option value="'.$m->year_id.'">'.$m->year_name.'</option>';
			}
			$content .= '</select></div>';
		}
		
		if ($_POST["ymm_year"] != "")
		{
			$content .= '<br><input type="submit" name="smTreeSearch" value="Submit" />';
		}
				
		$content .= "</form>";
		return $content;
	}
}

$wymm = new woocommerce_yearmakemodel();
?>