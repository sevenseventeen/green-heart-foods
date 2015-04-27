$(document).ready(function() {

	/* 

	Create Menu Page 

	*/

	$('body.create_and_edit_menu').ready(function(event) {
		set_days_in_month();
	});
	
	$('.create_and_edit_menu select.month').change(function(event){
		set_days_in_month();
	});

	$('.create_and_edit_menu .price-per-order-input').keyup(function(event){
		var current_class = get_current_menu_item_class($(this));
		update_item_price(current_class);
	});

	$('.create_and_edit_menu .serves-input').keyup(function(event){
		var current_class = get_current_menu_item_class($(this));
		var input_value = $(this).val();
		if(Math.floor(input_value) == input_value && $.isNumeric(input_value)) {
			$(current_class+' .serves-output').html(input_value);
		} else if (input_value === "") {
			$(current_class+' .serves-output').html(0);
		}
		update_item_price(current_class);
	});

	$('.create_and_edit_menu .quantity-button').click(function(event){
		var current_class = get_current_menu_item_class($(this));
		var quantity = $(current_class+' .quantity').html();
		if($(this).hasClass('add')) {
			quantity++;
		} else {
			quantity--;
		}
		if(quantity < 0) quantity = 0;
		$(current_class+' .quantity').html(quantity);
		$(current_class+' .order-quantity').val(quantity);
		update_item_price(current_class);
	});

	$('.create_and_edit_menu .preview_menu_button').click(function(event){
		$('.create_menu_form').submit();
	});

	$('.create_and_edit_menu select.server').change(function(event) {
		var image_path = $('option:selected', this).attr('data-server-image-path');
		console.log(image_path);
		$('.server-image').attr('src', '../'+image_path);
	});

	/* 

	Daily Menu Page 

	*/

	$('.like-heart').click(function(event) {
		//TODO Fix this bug.
		var this_item = $(this);
		var menu_item_id = this_item.attr('data-menu-item-id');
		$.post('../_actions/like-menu-item.php', { 
			menu_item_id: menu_item_id
		}).done(function(data){
			if(data == 1) {
				this_item.addClass('liked disabled');
			}
		});
	});

	$('select.meal-types').change(function(event){
		var client_id = $(this).attr('data-client-id');
		var service_date = $(this).attr('data-service-date');
		var meal_id = $(this).val();
		document.location = '../admin/daily-menu.php?client-id='+client_id+'&service-date='+service_date+'&meal-id='+meal_id;
	});

});

function get_current_menu_item_class(element) {
	var increment_id = element.closest('.menu-item').attr('data-increment-id');
	var current_class = '.menu-item-'+increment_id;
	return current_class;
}

function update_item_price(current_class) {
	var quantity = Number($(current_class+' .quantity').html());
	var price_per_order = Number($(current_class+' .price-per-order-input').val());
	var total_number_of_orders = 0;
	var total_people_served = 0;
	var total_cost = 0;
	if(isNaN(quantity) || isNaN(price_per_order)) {
		return;
	} else {
		var total = quantity*price_per_order;
		$(current_class+' .price-per-order-output').html(total)
	}
	$('.quantity').each(function(event){
		total_number_of_orders += Number($(this).html());
	});
	$('.serves-output').each(function(event){
		total_people_served += Number($(this).html());
	});
	$('.price-per-order-output').each(function(event){
		total_cost += Number($(this).html());
	});
	$('.total-number-of-orders').html(total_number_of_orders);
	$('.total-people-served').html(total_people_served);
	$('.total-cost').html(total_cost);

}

/* Function for setting the number of days in the selected month */

function set_days_in_month() {
	var month_number = $('select.month').val();
	var year = $('select.year').val();
	var days_in_month = new Date(year, month_number, 0).getDate();
	var html = "";
	var selected = "";
	var tomorrow = new Date();
	var leadingZeroDay = "";
	tomorrow.setDate(tomorrow.getDate() + 1);
	for (var i=1; i < days_in_month+1; i++) {
		if(i < 10) {
			leadingZeroDay = '0'+i;
		} else {
			leadingZeroDay = i;
		}
		if($('.current_day_edit_mode').val() == 0) {
			if(i === tomorrow.getDate()) {
				selected = "selected='selected'";
			} else {
				selected = "";
			}	
		} else {
			if($('.current_day_edit_mode').val() == i) {
				selected = "selected='selected'";
			} else {
				selected = "";
			}
		}
		
		html += "<option "+selected+" value='"+leadingZeroDay+"'>"+i+"</option>";
	};
	$('select.day').html(html);
}