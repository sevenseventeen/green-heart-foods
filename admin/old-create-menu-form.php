<?php 

<form class="create-menu-form" action="../_actions/create-menu.php" method="post">

	<h3>Date</h3>
	<select name="service_month" class='month'>
		<?php
			$start_month = date('F');
			$start_month_number = date('m');
			$end_month = date('F', strtotime('+1 month'));
			$end_month_number = date('m', strtotime('+1 month'));
			echo "<option value=$start_month_number>".$start_month."</option>";
			echo "<option value=$end_month_number>".$end_month.'</option>';
		?>
	</select>
	<select name="service_day" class="day">
		<!-- Populated via main.js at runtime - changes based on selected month. -->
	</select>
	<select name="service_year" class='year'>
		<?php
			$start_year = date('Y');
			$end_year = date('Y', strtotime('+1 year'));
			echo "<option value='$start_year'>".$start_year."</option>";
			echo "<option value='$end_year'>".$end_year."</option>";
		?>
	</select>

	<h3>Meal Type</h3>
	<select name="meal_id">
	<?php 
		$menu = new Menu();
		$result = $menu->get_meal_types();
		for ($i=0; $i < count($result); $i++) { 
			echo '<option value='.$result[$i]['meal_id'].'>'.$result[$i]['meal_name'].'</option>';
		}
	?>
	</select>

	<h3>Meal Description</h3>
	<input name="meal_description" type="text" placeholder="Add Description Here" value="Meal Description" />

	<div>
		<div>
			<img width="20" class="server-image" src="../_images/server-placeholder.jpg" />
			<!-- TODO - Need some AJAX here. After making server selection, return imagae path and replace .server-image with path. -->
			<select class="server" name="server_id">
				<option value="none">Select Server</option>
				<?php
					$servers = new Servers();
					$result = $servers->get_all_servers();
					for ($i=0; $i < count($result); $i++) { 
						echo '<option data-server-image-path='.$result[$i]['server_image_path'].' value='.$result[$i]['server_id'].'>'.$result[$i]['server_first_name'].' '.$result[$i]['server_last_name'].'</option>';
					}
				?>
			</select>
		</div>
		<div class="menu-image">
			<img width="20" src="../_images/menu-image-placeholder.jpg" />
			<input type="file" />
		</div>
	</div>

	<!-- Loop through number of meals per day and create blank menu items for each -->

	<?php 
		$client = new Client(); 
		$result = $client->get_meals_per_day($client_id);
		$meals_per_day = $result[0]['meals_per_day'];
		$form = "";
		for ($i=0; $i < $meals_per_day; $i++) {
			$form .= <<<FORM
			<h1>----------------------------</h1>
			<div data-increment-id="$i" class="menu-item menu-item-$i">
				<input type="text" name="menu_item_name[$i]" value="Item Name $i" placeholder="Add Menu Item Name" />
				<input type="text" name="ingredients[$i]" value="Ingredients $i" placeholder="Add Ingredients" />
				<input type="text" name="special_notes[$i]" value="Notes $i" placeholder="Special Notes" />
				<input type="checkbox" value="1" checked name="is_vegetarian[$i]"><label>Vegetarian</label>
				<input type="checkbox" value="1" checked name="is_vegan[$i]"><label>Vegan</label>
				<input type="checkbox" value="1" checked name="is_gluten_free[$i]"><label>Gluten Free</label>
				<input type="checkbox" value="1" checked name="is_whole_grain[$i]"><label>Whole Grain</label>
				<input type="checkbox" value="1" checked name="contains_nuts[$i]"><label>Contains Nuts</label>
				<input type="checkbox" value="1" checked name="contains_soy[$i]"><label>Contains Soy</label>
				<input type="checkbox" value="1" checked name="contains_shellfish[$i]"><label>Contains Shellfish</label>
				<h3>Set Price per Order</h3>
				<input class="price-per-order-input" name="price_per_order[$i]" type="text" placeholder="$0.00" />
				<input class="serves-input" name="number_of_servings[$i]" type="text" placeholder="Serves 0" />
				<p class="order-summary">
					<span class="quantity">0</span> Orders Serves 
					<span class="serves-output">0</span> 
					$<span class="price-per-order-output">0</span>
				</p>
				<input class="order-quantity" name="order_quantity[$i]" type="hidden" value="" />
				<input name="meals_per_day" type="hidden" value="$i" />
				<input type="hidden" name="client_id" value="$client_id" />
				<input type="hidden" name="item_status_id" value="1" />
				<a class="quantity-button subtract">Subtract</a>
				<a class="quantity-button add">Add</a>
			</div>
FORM;
		}
		echo $form;
	?>

	
</form>

<h1>-------Order Summary---------</h1>

<div class="order-summary">
	<p>
		<span class="total-number-of-orders">0</span> Orders Serves
		<span class="total-people-served">0</span> =
		$<span class="total-cost">0</span>
	</p>
	<button class="preview-menu-button">Save and Preview</button>
</div>