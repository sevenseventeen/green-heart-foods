<?php

class Menu {
    
    private $database_connection = null;
    
    public function __construct() {
        require_once("../_classes/Messages.php");
        require_once("../_classes/Client.php");
        require_once("../_classes/Database.php");
        $database = new Database();
        $this->database_connection = $database->connect();
    }

    public function like_menu_item($menu_item_id) {
        $arguments = [
            $menu_item_id
        ];
        $query = $this->database_connection->prepare("UPDATE menu_items SET like_count = like_count+1 WHERE menu_item_id = ?");
        $query->execute($arguments);
        echo $query->rowCount();
    }
    
    
    /* Get Meal Types */


    public function get_meal_types() {
        $query = $this->database_connection->prepare("SELECT * FROM meals");
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        if(count($result) > 0) {
            return $result;
        } else {
            echo "Sorry, there was an error. Could not find any meal tyoes."; // TODO - Send as error.
            exit();
        }
    }

    public function get_weekly_menu($client_id, $start_date) {
        $end_date = date('Y-m-d', strtotime($start_date.' +6 days'));
        echo '<pre>';
        echo "Start: ".$start_date;
        echo "<br />End: ".$end_date;
        $arguments = [
            $client_id,
            $start_date,
            $end_date
        ];
        $query = $this->database_connection->prepare("SELECT * FROM menu_items LEFT JOIN meals ON menu_items.meal_id = meals.meal_id WHERE client_id = ? AND (service_date BETWEEN ? AND ?) ORDER BY service_date ASC, meals.meal_id ASC, menu_item_id ASC");
        $query->execute($arguments);
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        if(count($result) > 0) {
            return $result;
            print_r($result);
        } else {
            echo "<br />There was an error"; //TODO - Add to error class.
            print_r($this->database_connection->errorInfo());
        }
    }

    public function get_daily_menu($client_id, $service_date, $meal_id) {
        $arguments = [
            $client_id,
            $service_date,
            $meal_id
        ];
        $query = $this->database_connection->prepare("SELECT * FROM menu_items LEFT JOIN servers ON menu_items.server_id = servers.server_id WHERE menu_items.client_id = ? AND menu_items.service_date = ? AND menu_items.meal_id = ?");
        $query->execute($arguments);
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        if(count($result) > 0) {
            return $result;
        } else {
            echo "<br />There was an error"; //TODO - Add to error class.
            print_r($this->database_connection->errorInfo());
        }   
    }

    public function create_menu() {

        // TODO - Figure out logic for files.
        // For images, use AJAX to upload the image and store the path in a hidden field for uploading.
        // TODO - Check for empty or incomplete fields.
        
        $service_date = $_POST['service_year'].'-'.$_POST['service_month'].'-'.$_POST['service_day'];
        $client_id = $_POST['client_id'];
        $meal_id = $_POST['meal_id'];

        for ($i=0; $i <= $_POST['meals_per_day']; $i++) { 
            // $file_name = $_FILES["company_logo"]["name"];

            if(!isset($_POST['is_vegetarian'][$i])) $_POST['is_vegetarian'][$i] = 0;
            if(!isset($_POST['is_vegan'][$i])) $_POST['is_vegan'][$i] = 0;
            if(!isset($_POST['is_gluten_free'][$i])) $_POST['is_gluten_free'][$i] = 0;
            if(!isset($_POST['is_whole_grain'][$i])) $_POST['is_whole_grain'][$i] = 0;
            if(!isset($_POST['contains_nuts'][$i])) $_POST['contains_nuts'][$i]= 0;
            if(!isset($_POST['contains_soy'][$i])) $_POST['contains_soy'][$i] = 0;
            if(!isset($_POST['contains_shellfish'][$i])) $_POST['contains_shellfish'][$i] = 0;

            $arguments = [
                $service_date,
                $_POST['meal_id'],
                $_POST['client_id'],
                $_POST['server_id'],
                $_POST['item_status_id'],
                $_POST['meal_description'],
                $_POST['menu_item_name'][$i],
                $_POST['ingredients'][$i],
                $_POST['special_notes'][$i],
                $_POST['is_vegetarian'][$i],
                $_POST['is_vegan'][$i],
                $_POST['is_gluten_free'][$i],
                $_POST['is_whole_grain'][$i],
                $_POST['contains_nuts'][$i],
                $_POST['contains_soy'][$i],
                $_POST['contains_shellfish'][$i],
                $_POST['price_per_order'][$i],
                $_POST['number_of_servings'][$i],
                $_POST['order_quantity'][$i],
            ];    
            $query = $this->database_connection->prepare("INSERT INTO menu_items (service_date, meal_id, client_id, server_id, item_status_id, meal_description, menu_item_name, ingredients, special_notes, is_vegetarian, is_vegan, is_gluten_free, is_whole_grain, contains_nuts, contains_soy, contains_shellfish, price_per_order, number_of_servings, order_quantity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $result = $query->execute($arguments);
            print_r($result);
            if($query->rowCount() === 1){
                Messages::add('The menu has been created.');
                header("Location: ../admin/daily-menu.php?client-id=$client_id&service-date=$service_date&meal-id=$meal_id");
            }
        }
    }

    public function get_daily_menu_page($context) {

        echo "<h1>Daily Menu - Determine Context</h1>";
        echo "<h2>Context is: $context</h2>";

        $client_id = $_GET['client-id'];
        $service_date = $_GET['service-date'];
        $meal_id = $_GET['meal-id'];
        // $menu = new Menu();
        $result = $this->get_meal_types();
        $selected = "";

        // TODO - If daily menu is in client context, need to check that client_id is the same as 
        // the one stored in session_id so clients can't view eachothers menus
        
        echo "Messages: ".Messages::render();
        echo date('M d', strtotime($service_date));
        echo "<br />";
        echo "<select data-client-id='$client_id' data-service-date='$service_date' class='meal-types'>";
        for($i=0; $i<count($result); $i++) {
            $meal_id_option = $result[$i]['meal_id'];
            if($meal_id === $meal_id_option) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            $meal_name = $result[$i]['meal_name'];
            echo $result[$i]['meal_name']."<br />";
            echo "<option $selected value='".$meal_id_option."'>$meal_name</option>";
        }
        echo '</select>';

        $result = $this->get_daily_menu($client_id, $service_date, $meal_id);
        $result_count = count($result);
        $item_attributes_array = [
            'is_vegetarian', 
            'is_vegan', 
            'is_gluten_free', 
            'is_whole_grain', 
            'contains_nuts', 
            'contains_soy', 
            'contains_shellfish'
        ];
        // echo '<pre>';
        // print_r($result);

        if($result_count > 0) {
            echo '<pre />';
            echo "<h2>".$result[0]['meal_description']."</h2>";
            echo "Server First Name: ".$result[0]['server_first_name']."<br />";
            echo "Server Phone Number: ".$result[0]['server_phone_number']."<br />";
            echo "<img src='../".$result[0]['server_image_path']."' />";
            echo "<p>Will be servings lunch today.</p>";
            for($i=0; $i<$result_count; $i++) {
                $menu_item_id = $result[$i]['menu_item_id'];
                echo '<hr />';
                echo "<div data-menu-item-id='$menu_item_id' class='like-heart'>Like Heart</div>";
                echo $result[$i]['menu_item_name'].'<br />';
                echo $result[$i]['ingredients'].'<br />';
                echo $result[$i]['special_notes'].'<br />';
                for($j=0; $j<count($item_attributes_array); $j++) {
                    if($result[$i][$item_attributes_array[$j]] == 1) {
                        echo "Checkbox".$item_attributes_array[$j].'<br />';    
                    }
                }
                echo "Order Quantity: ".$result[$i]['special_notes'].'<br />';
                echo "Number of Servings: ".$result[$i]['number_of_servings'].'<br />';
                echo "TODO - Add button, subtract button, special instructions.";
            }
            echo "<p><a href='".WEB_ROOT."/admin/edit-daily-menu.php?client-id=$client_id'>Edit Daily Menu</a></p>";
        } else {
            echo '<p>Sorry, no menu items found.</p>';
        }
    }


    public function get_weekly_menu_page($context) {

        echo "<h1>Weekly Menu - Determine Context</h1>";
        echo "<h2>Context is: $context</h2>";

        require_once("../_config/config.php");
        require_once("../_classes/Client.php");
        require_once("../_classes/Messages.php");

        $monday_last_week = date('Y-m-d', strtotime('Monday last week'));
        $monday_this_week = date('Y-m-d', strtotime('Monday this week'));
        $monday_next_week = date('Y-m-d', strtotime('Monday next week'));

        if(isset($_GET['start-date'])) {
            $start_date = $_GET['start-date'];
        } else {
            $start_date = $monday_this_week;
        }

        $client_id = $_GET['client-id'];

        $client = new Client();
        $result = $client->get_client($client_id);
        if(count($result) == 1) {
            echo "<img src='../_uploads/".$result[0]['company_logo_large']."' />";
        }

        // $menu = new Menu();
        // $result = $menu->get_weekly_menu($client_id, $start_date);
        $result = $this->get_weekly_menu($client_id, $start_date);
        $result_count = count($result);

        echo "<ul>";
        echo    "<li><a href='weekly-menu.php?client-id=$client_id&start-date=$monday_last_week'>$monday_last_week</a></li>";
        echo    "<li><a href='weekly-menu.php?client-id=$client_id&start-date=$monday_this_week'>$monday_this_week</a></li>";
        echo    "<li><a href='weekly-menu.php?client-id=$client_id&start-date=$monday_next_week'>$monday_next_week</a></li>";
        echo "</ul>";

        echo '<br />';
        echo '<a href="'.WEB_ROOT.'/admin/create-menu.php?client-id='.$client_id.'">Create Menu</a>';
        echo '<br />';
        echo '<br />';
        echo '<br />';

        if($result_count > 0) {

            $service_date = null;
            $meal_id = null;
            $menu_items = "";
            for ($i=0; $i < count($result); $i++) { 

                if($result[$i]['service_date'] != $service_date) {
                    echo '<h1>New Date Group - '.$result[$i]['service_date'].'</h1>';
                    $meal_id = null;
                }
                $service_date = $result[$i]['service_date'];
                
                if($result[$i]['meal_id'] != $meal_id) {
                    echo "<br /><br />***********************************<br /><br />";
                    echo "<h2>New Meal Group</h2>";
                    echo "<a href='daily-menu.php?client-id=$client_id&service-date=$service_date&meal-id=".$result[$i]['meal_id']."'>View Items ></a><br />";
                    echo $result[$i]['service_date'].'<br />';
                    echo "Meal: ".$result[$i]['meal_id']." ".$result[$i]['meal_name'].'<br />';
                    $meal_id = null;
                }
                $meal_id = $result[$i]['meal_id'];
                // $menu_items .= $result[$i]['menu_item_name'].'<br />';
                echo $result[$i]['menu_item_name'].'<br />';
                // echo $result[$i]['meal_description'].'<br />';
                // echo $result[$i]['menu_item_id'].'<br />';
            }
            echo $menu_items;
        } else {
            echo '<p>Sorry, no menu items found.</p>';
        }
        
        echo "<br /><br />***********************************<br /><br />";
        echo "<a href='".WEB_ROOT."/_actions/send-meal-to-client.php?client-id=$client_id&start-date=$start_date'>Send Meal to Client</a><br />";
        // echo "<a href='#'>Edit Menu -- TODO Build this page to link to.</a><br />";

        // $startDate = 'Mon 2015-03-09';
        // $endDate = 'Mon 2017-02-05';
        // $endDate = strtotime($endDate);
        
        // for ($i = strtotime('Monday', strtotime($startDate)); $i <= $endDate; $i = strtotime('+1 week', $i)) {
        //  $monday = date('D Y-m-d', $i);
        //  $friday = date('D Y-m-d', strtotime($monday . ' + 4 day'));
     //         echo date("D m-d", strtotime($monday));
     //         echo ' -- ';
     //         echo date("D m-d", strtotime($friday));
     //         echo '<br />';
        // }
    }

    public function send_menu_for_client_review ($client_id, $start_date) {
        $user = new User();
        $result = $user->get_client_users($client_id);
        for ($i=0; $i < count($result); $i++) { 
            if($result[$i]['user_type_id'] == 2) {
                $client_admin_email = $result[$i]['cliet_name'];        
            }
        }
        
        $end_date = date('Y-m-d', strtotime($start_date.' +6 days'));
        $arguments = [
            2,
            $client_id,
            $start_date,
            $end_date
        ];
        $query = $this->database_connection->prepare("UPDATE menu_items SET item_status_id = ? WHERE client_id = ? AND (service_date BETWEEN ? AND ?)");
        $query->execute($arguments);
        if ($query->rowCount() > 0 ){
            $link = WEB_ROOT . "/clients/weekly-menu.php?client-id=$client_id&start-date=$start_date";
            $to_email  = $client_admin_email; // TODO - Get admin email
            $subject = 'Your Weekly Menu is Ready';
            $message = '
                <html>
                    <body>
                        <p>Hello! Your weekly menu is ready to review. Please click the link below to review, edit and confirm.</p>
                        <a href=$link>$link</a>
                    </body>
                </html>';
            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $headers .= 'From: Green Heart Foods <'.GREEN_HEART_FOODS_ADMIN_EMAIL.'>' . "\r\n";
            $sent = mail($to, $subject, $message, $headers);
            if($sent) {
                echo "Email has been sent";
            } else {
                echo "Email has not been sent";
            }
        }
    }

    public function send_menu_approval ($client_id, $start_date){
        $client = new Client();
        $result = $client->get_client($client_id);
        $client_name = $result[0]['cliet_name'];
        $end_date = date('Y-m-d', strtotime($start_date.' +6 days'));
        $arguments = [
            3,
            $client_id,
            $start_date,
            $end_date
        ];
        $query = $this->database_connection->prepare("UPDATE menu_items SET item_status_id = ? WHERE client_id = ? AND (service_date BETWEEN ? AND ?)");
        $query->execute($arguments);
        if ($query->rowCount() > 0 ){
            $link = WEB_ROOT . "/admin/weekly-menu.php?client-id=$client_id&start-date=$start_date";
            $to_email  = GREEN_HEART_FOODS_ADMIN_EMAIL;
            $subject = "A Menu Has Been Approved by $client_name";
            $message = "
                <html>
                    <body>
                        <p>$client_name has approved a menu - please click below to review.</p>
                        <a href=$link>$link</a>
                    </body>
                </html>";
            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $headers .= 'From: Green Heart Foods <'.GREEN_HEART_FOODS_ADMIN_EMAIL.'>' . "\r\n";
            $sent = mail($to, $subject, $message, $headers);
            if($sent) {
                echo "Email has been sent";
            } else {
                echo "Email has not been sent";
            }
        }
    }





    public function get_menu_form($client_id) {
        $page_class = 'create-menu';
        $start_month = date('F');
        $start_month_number = date('m');
        $end_month = date('F', strtotime('+1 month'));
        $end_month_number = date('m', strtotime('+1 month'));
        $start_year = date('Y');
        $end_year = date('Y', strtotime('+1 year'));
        $menu = new Menu();
        $meal_types = $menu->get_meal_types();
        $servers = new Servers();
        $server_list = $servers->get_all_servers();
        $client = new Client(); 
        $result = $client->get_meals_per_day($client_id);
        $meals_per_day = $result[0]['meals_per_day'];
        $form = "";
        $html = "";
        $meal_type_options = "";
        $server_list_options = "";
        for ($i=0; $i < count($meal_types); $i++) { 
            $meal_type_options .= "<option value=".$meal_types[$i]['meal_id'].">".$meal_types[$i]['meal_name']."</option>";
        }
        for ($i=0; $i < count($server_list); $i++) { 
            $server_list_options .= '<option data-server-image-path='.$server_list[$i]['server_image_path'].' value='.$server_list[$i]['server_id'].'>'.$server_list[$i]['server_first_name'].' '.$server_list[$i]['server_last_name'].'</option>';
        }
        $html .= "<form class='create-menu-form' action='../_actions/create-menu.php' method='post'>";
        $html .=    "<h3>Date</h3>";
        $html .=    "<select name='service_month' class='month'>";
        $html .=        "<option value='$start_month_number'>$start_month</option>";
        $html .=        "<option value='$end_month_number'>$end_month</option>";
        $html .=    "</select>";
        $html .=    "<select name='service_day' class='day'></select>";
        $html .=    "<select name='service_year' class='year'>";
        $html .=        "<option value='$start_year'>$start_year</option>";
        $html .=        "<option value='$end_year'>$end_year</option>";
        $html .=    "</select>";
        $html .=    "<h3>Meal Type</h3>";
        $html .=    "<select name='meal_id'>";
        $html .=        $meal_type_options;
        $html .=    "</select>";
        $html .=    "<h3>Meal Description</h3>";
        $html .=    "<input name='meal_description' type='text' placeholder='Add Description Here' value='Meal Description' />";
        $html .=    "<div>";
        $html .=        "<div>";
        $html .=            "<img width='20' class='server-image' src='../_images/server-placeholder.jpg' />";
        $html .=            "<select class='server' name='server_id'>";
        $html .=                "<option value='none'>Select Server</option>";
        $html .=                    $server_list_options;
        $html .=            "</select>";
        $html .=        "</div>";
        $html .=        "<div class='menu-image'>";
        $html .=            "<img width='20' src='../_images/menu-image-placeholder.jpg' />";
        $html .=            "<input type='file' />";
        $html .=        "</div>";
        $html .=    "</div>";
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
        $html .= $form;
        $html .= "</form>";
        $html .= "<div class='order-summary'>";
        $html .=    "<h1>Order Summary</h1>";
        $html .=    "<p>";
        $html .=        "<span class='total-number-of-orders'>0</span> Orders Serves";
        $html .=        "<span class='total-people-served'>0</span> =";
        $html .=        "<span class='total-cost'>0</span>";
        $html .=    "</p>";
        $html .=    "<button class='preview-menu-button'>Save and Preview</button>";
        $html .= "</div>";
        echo $html;
    }

}