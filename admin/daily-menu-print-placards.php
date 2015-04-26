<?php
	$page_class = 'daily_menu_print_placards_page';
	$page_title_detail = 'Daily Menu - Placards';
	require_once("../_config/config.php");
    require_once(SERVER_ROOT . "/_includes/restrict-access-green-heart-foods.php");
    require_once(SERVER_ROOT . "/_includes/print-header.php");
    require_once(SERVER_ROOT . "/_classes/Client.php");
    require_once(SERVER_ROOT . "/_classes/Messages.php");
	require_once(SERVER_ROOT . "/_classes/Menu.php");
	$menu = new Menu();
	$context = $green_heart_foods_access_level;
	$meal_id = $_GET['meal-id'];
	$client_id = $_GET['client-id'];
	$service_date = $_GET['service-date'];
	$menu_items = $menu->get_daily_menu($client_id, $service_date, $meal_id);
?>

<div class="menu">
	<?php 
		$html = "";
		$item_attributes_array = [
            'is_vegetarian', 
            'is_vegan', 
            'is_gluten_free', 
            'is_whole_grain', 
            'contains_nuts', 
            'contains_soy', 
            'contains_shellfish'
        ];
		for ($i=0; $i < count($menu_items); $i++) { 
			$checkboxes = "";
			$html .= "<div class='placard_container'>";
			$html .= "<div class='fake_hr'></div>";
			$html .= "<div class='green-heart-foods-logo'></div>";
			$html .= "<h3>".$menu_items[$i]['menu_item_name']."</h3>";
			for($j=0; $j<count($item_attributes_array); $j++) {
                if($menu_items[$i][$item_attributes_array[$j]] == 1) {
                    $checkboxes .= $item_attributes_array[$j]. ", ";
                }
            }
            $checkboxes = str_replace('is_', '', $checkboxes);
            $checkboxes = str_replace('_', ' ', $checkboxes);
            $checkboxes = substr($checkboxes, 0, -2);
            $html .= ucwords($checkboxes);
            $html .= "</div>";
		}
		echo $html;
	?>
</div>


<?php require_once(SERVER_ROOT . "/_includes/print-footer.php"); ?>