<?php

class Menu {
    
    private $database_connection = null;
    
    public function __construct() {
        require_once("../_classes/Messages.php");
        require_once("../_classes/Client.php");
        require_once("../_classes/Database.php");
        require_once("../_classes/Image.php");
        $database = new Database();
        $this->image = new Image();
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
        $service_date = $_POST['service_year'].'-'.$_POST['service_month'].'-'.$_POST['service_day'];
        $client_id = $_POST['client_id'];
        $meal_id = $_POST['meal_id'];
        $menu_image_path = $this->image->upload_image($_FILES, "menu_image");
        for ($i=0; $i <= $_POST['meals_per_day']; $i++) { 
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
                $menu_image_path,
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
            $query = $this->database_connection->prepare("INSERT INTO menu_items (service_date, meal_id, client_id, server_id, item_status_id, menu_image_path, meal_description, menu_item_name, ingredients, special_notes, is_vegetarian, is_vegan, is_gluten_free, is_whole_grain, contains_nuts, contains_soy, contains_shellfish, price_per_order, number_of_servings, order_quantity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $result = $query->execute($arguments);
        }
        if($query->rowCount() === 1){
            Messages::add('The menu has been created.');
            header("Location: ../admin/daily-menu.php?client-id=$client_id&service-date=$service_date&meal-id=$meal_id");
        }
    }

    public function update_menu() {

        // TODO - Figure out logic for files.
        // For images, use AJAX to upload the image and store the path in a hidden field for uploading.
        // TODO - Check for empty or incomplete fields.
        
        $service_date = $_POST['service_year'].'-'.$_POST['service_month'].'-'.$_POST['service_day'];
        $client_id = $_POST['client_id'];
        $meal_id = $_POST['meal_id'];
        $menu_item_id_array = $_POST['menu_item_id_array'];


        // for ($i=0; $i <= $_POST['meals_per_day']; $i++) { 

        echo "<pre>";
        print_r($_POST);
        $number_of_menu_items = count($menu_item_id_array);

        echo "number_of_menu_items: ".$number_of_menu_items;

        for ($i=0; $i < $number_of_menu_items; $i++) { 
            // $file_name = $_FILES["company_logo"]["name"];
            

            echo "<br />-----I: ".$i;
            $menu_item_id = $menu_item_id_array[$i];

            if(!isset($_POST['is_vegetarian'][$i])) $_POST['is_vegetarian'][$i] = 0;
            if(!isset($_POST['is_vegan'][$i])) $_POST['is_vegan'][$i] = 0;
            if(!isset($_POST['is_gluten_free'][$i])) $_POST['is_gluten_free'][$i] = 0;
            if(!isset($_POST['is_whole_grain'][$i])) $_POST['is_whole_grain'][$i] = 0;
            if(!isset($_POST['contains_nuts'][$i])) $_POST['contains_nuts'][$i]= 0;
            if(!isset($_POST['contains_soy'][$i])) $_POST['contains_soy'][$i] = 0;
            if(!isset($_POST['contains_shellfish'][$i])) $_POST['contains_shellfish'][$i] = 0;

            $arguments = [
                $_POST['meal_id'],
                $_POST['client_id'],
                $_POST['server_id'],
                $_POST['item_status_id'],
                $_POST['meal_description'],
                $_POST['menu_item_name'][$i],
                $service_date,
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
                $menu_item_id
            ];  

            $query = $this->database_connection->prepare("UPDATE menu_items SET 
                meal_id = ?, 
                client_id = ?, 
                server_id = ?, 
                item_status_id = ?, 
                meal_description = ?, 
                menu_item_name = ?, 
                service_date = ?, 
                ingredients = ?, 
                special_notes = ?, 
                is_vegetarian = ?, 
                is_vegan = ?, 
                is_gluten_free = ?, 
                is_whole_grain = ?, 
                contains_nuts = ?, 
                contains_soy = ?, 
                contains_shellfish = ?, 
                price_per_order = ?, 
                number_of_servings = ?, 
                order_quantity = ?
                WHERE 
                menu_item_id = ?");
            $result = $query->execute($arguments);

            if($i == $number_of_menu_items-1) {
                Messages::add('The menu has been updated');
                header("Location: ../admin/daily-menu.php?client-id=$client_id&service-date=$service_date&meal-id=$meal_id");
            }

            // if($query->rowCount() === 1){
            //     Messages::add($_POST['company_name'].' has been updated.');
            //     header('Location: ../admin/clients.php');
            // }


            // $query = $this->database_connection->prepare("INSERT INTO menu_items (service_date, meal_id, client_id, server_id, item_status_id, meal_description, menu_item_name, ingredients, special_notes, is_vegetarian, is_vegan, is_gluten_free, is_whole_grain, contains_nuts, contains_soy, contains_shellfish, price_per_order, number_of_servings, order_quantity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            // $result = $query->execute($arguments);
            // if($query->rowCount() === 1){
                // Messages::add('The menu has been created.');
                // header("Location: ../admin/daily-menu.php?client-id=$client_id&service-date=$service_date&meal-id=$meal_id");
            // }
        }
    }

    public function get_daily_menu_page($context) {
        $html = "";
        $result = $this->get_meal_types();
        $message = Messages::render();
        $meal_id = $_GET['meal-id'];
        $selected = "";
        $client_id = $_GET['client-id'];
        $service_date = $_GET['service-date'];
        $weekday = date('l', strtotime($service_date));
        $web_root = WEB_ROOT;
        // TODO - If daily menu is in client context, need to check that client_id is the same as 
        // the one stored in session_id so clients can't view eachothers menus

        $html .= "<div class='page_header'>";
        if($context == 'green_heart_foods_admin') {
            $html .= "<a href='$web_root/admin/daily-menu-print-menu.php?client-id=$client_id&service-date=$service_date&meal-id=$meal_id'>Print Menu</a>";
        }
        $html .= "<h2>$weekday</h2>";
        if($context == 'green_heart_foods_admin') {
            $html .= "<a href='$web_root/admin/daily-menu-print-placards.php?client-id=$client_id&service-date=$service_date&meal-id=$meal_id'>Print Placards</a>";
        }
        $html .= "</div>";
        $html .= "<div class='message'>$message</div>";
        $html .= date('M d', strtotime($service_date))."<br />";
        $html .= "<select data-client-id='$client_id' data-service-date='$service_date' class='meal-types'>";
        for($i=0; $i<count($result); $i++) {
            $meal_id_option = $result[$i]['meal_id'];
            if($meal_id === $meal_id_option) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            $meal_name = $result[$i]['meal_name'];
            $html .= $result[$i]['meal_name']."<br />";
            $html .= "<option $selected value='".$meal_id_option."'>$meal_name</option>";
        }
        $html .= '</select>';

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
        if($result_count > 0) {

            $server_image_path = WEB_ROOT.'/'.$result[0]['server_image_path'];
            $menu_image_path = WEB_ROOT.'/_uploads/'.$result[0]['menu_image_path'];
            $total_orders = 0;
            $total_servings = 0;
            $total_price = 0;

            $html .= "<p class='meal_description'>".$result[0]['meal_description']."</p>";
            $html .= "<div class='fake_hr'></div>";
            $html .= "<div class='server_information'>";
            $html .=    "<img src='$server_image_path' />";
            $html .=    $result[0]['server_first_name']."<br />";
            $html .=    $result[0]['server_phone_number']."<br />";
            $html .=    "<p>Will be servings lunch today.</p>";
            $html .= "</div>";
            $html .= "<div class='menu_image_container'>";
            $html .=    "<img src='$menu_image_path' />";
            $html .= "</div>";
            $html .= "<div class='fake_hr'></div>";
            for($i=0; $i<$result_count; $i++) {
                $checkboxes = "";
                $menu_item_id = $result[$i]['menu_item_id'];
                $like_count = $result[$i]['like_count'];
                $order_quantity = $result[$i]['order_quantity'];
                $price_per_order = $result[$i]['price_per_order'];
                $number_of_servings = $result[$i]['number_of_servings'];
                $total_item_price = $order_quantity*$price_per_order;
                $total_orders = $total_orders+$order_quantity;
                $total_servings = $total_servings+$number_of_servings;
                $total_price = $total_price+$total_item_price;
                if($like_count > 0) {
                    $like_heart_class = 'liked';
                } else {
                    $like_heart_class = '';
                }
                $html .= "<div data-menu-item-id='$menu_item_id' class='like-heart $like_heart_class'>Like Heart</div>";
                $html .= "<p>".$like_count." Likes</p>";
                $html .= "<p>".$result[$i]['menu_item_name'].'</p>';
                $html .= "<p>".$result[$i]['ingredients'].'</p>';
                $html .= "<p>".$result[$i]['special_notes'].'</p>';
                for($j=0; $j<count($item_attributes_array); $j++) {
                    if($result[$i][$item_attributes_array[$j]] == 1) {
                        $checkboxes .= $item_attributes_array[$j]. ", ";
                    }
                }
                $checkboxes = str_replace('is_', '', $checkboxes);
                $checkboxes = str_replace('_', ' ', $checkboxes);
                $checkboxes = substr($checkboxes, 0, -2);
                $html .= $checkboxes;
                $html .= "<p>".$result[$i]['special_notes']."</p>";
                $html .= "<p>".$result[$i]['special_requests']."</p>";
                $html .= "<p>1 Order Serves $number_of_servings People / $$price_per_order Per Order</p>";
                $html .= "<div class='item_summary_container'>";
                $html .=    "$order_quantity Orders Serves $number_of_servings $$total_item_price";
                $html .= "</div>";
                if($context == 'client_admin' || $context == 'client_general') {
                    $html .= "TODO - Add button, subtract button, special instructions.";    
                }
                if($i < $result_count-1) {
                    $html .= "<div class='fake_hr'></div>";    
                }
            }
            $html .= "<div class='order_summary'>";
            $html .=    "<p>$total_orders Orders Serves $total_servings = $$total_price</p>";
            $html .= "</div>";
            $html .= "<p><a href='".WEB_ROOT."/admin/edit-daily-menu.php?client-id=$client_id&service-date=$service_date&meal-id=$meal_id'>Edit Daily Menu</a></p>";
        } else {
            $html .= '<p>Sorry, no menu items found.</p>';
        }
        return $html;
    }


    public function get_weekly_menu_page($context) {

        echo "<h1>Weekly Menu - Determine Context</h1>";
        echo "<h2>Context is: $context</h2>";

        require_once("../_config/config.php");
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





    public function get_menu_form($client_id, $service_date = null, $meal_id = null) {
        $menu = new Menu();
        $servers = new Servers();
        $client = new Client();
        $meal_types = $menu->get_meal_types();
        $server_list = $servers->get_all_servers();
        $meals_result = $client->get_meals_per_day($client_id);
        $meals_per_day = $meals_result[0]['meals_per_day'];
        $form = "";
        $html = "";
        $meal_type_options = "";
        $server_list_options = "";
        $year_options = "";
        $month_options = "";
        $start_month = date('F');
        $start_month_number = date('m');
        $end_month = date('F', strtotime('+1 month'));
        $end_month_number = date('m', strtotime('+1 month'));
        $start_year = date('Y');
        $end_year = date('Y', strtotime('+1 year'));
        $month_options_array = [[$start_month_number, $start_month], [$end_month_number, $end_month]];
        $year_options_array = [$start_year, $end_year];
        $server_image_path = '../_images/server-placeholder.jpg';
        $menu_item_hidden_ids = "";
        if(isset($service_date) && isset($meal_id)) {
            $mode = 'edit';
            $form_action = '../_actions/update-menu.php';
            $arguments = [
                $client_id,
                $service_date,
                $meal_id,
            ];
            $query = $this->database_connection->prepare("SELECT * FROM menu_items WHERE client_id = ? AND service_date = ? AND meal_id = ?");
            $query->execute($arguments);
            $query->execute($arguments);
            $menu_items = $query->fetchAll(PDO::FETCH_ASSOC);
            $number_of_meals = count($menu_items);
            for ($i=0; $i < count($menu_items); $i++) { 
                $current_month = date('m', strtotime($service_date));
                $current_year = date('Y', strtotime($service_date));
                $current_day = date('d', strtotime($service_date));
                $current_server_id = $menu_items[0]['server_id'];
                $current_meal_id = $menu_items[0]['meal_id'];
                $meal_description = $menu_items[0]['meal_description'];
            }
        } else {
            $mode = 'create';
            $form_action = '../_actions/create-menu.php';
            $current_day = 0;
            $meal_description = "Meal Description";
            $number_of_meals = $meals_per_day;
        }

        // The following for loops were just complex enough to not cosolidate into a function. 
        // They are used to create the options for the select fields, selecting the current option when in edit mode.

        // Year

        for ($i=0; $i < count($year_options_array); $i++) { 
            if(isset($current_year) && ($year_options_array[$i] == $current_year)) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            $year_options .= "<option $selected value=".$year_options_array[$i].">".$year_options_array[$i]."</option>";
        }

        // Month

        for ($i=0; $i < count($month_options_array); $i++) { 
            if(isset($current_year) && ($month_options_array[$i][0] == $current_month)) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            $month_options .= "<option $selected value=".$month_options_array[$i][0].">".$month_options_array[$i][1]."</option>";
        }

        // Meal Types
        
        for ($i=0; $i < count($meal_types); $i++) { 
            if(isset($current_meal_id) && ($meal_types[$i]['meal_id'] == $current_meal_id)) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            $meal_type_options .= "<option $selected value=".$meal_types[$i]['meal_id'].">".$meal_types[$i]['meal_name']."</option>";
        }

        // Server List
        
        for ($i=0; $i < count($server_list); $i++) { 
            if(isset($current_server_id) && ($server_list[$i]['server_id'] == $current_server_id)) {
                $selected = 'selected';
                $server_image_path = '../'.$server_list[$i]['server_image_path'];
            } else {
                $selected = '';
            }
            $server_list_options .= "<option $selected data-server-image-path='".$server_list[$i]['server_image_path']."' value='".$server_list[$i]['server_id']."'>".$server_list[$i]['server_first_name']." ".$server_list[$i]['server_last_name']."</option>";
        }

        $html .= "<form class='create_menu_form' action='$form_action' method='post' enctype='multipart/form-data'>";
        $html .=    "<fieldset>";
        $html .=        "<h3>Date</h3>";
        $html .=        "<select name='service_month' class='month'>";
        $html .=                $month_options;
        $html .=        "</select>";
        $html .=        "<select name='service_day' class='day'></select>";
        $html .=        "<select name='service_year' class='year'>";
        $html .=            $year_options;
        $html .=        "</select>";
        $html .=    "</fieldset>";
        $html .=    "<fieldset>";
        $html .=        "<h3>Meal Type</h3>";
        $html .=        "<select name='meal_id'>";
        $html .=            $meal_type_options;
        $html .=        "</select>";
        $html .=    "</fieldset>";
        $html .=    "<fieldset>";
        $html .=        "<h3>Meal Description</h3>";
        $html .=        "<input name='meal_description' type='text' placeholder='Add Description Here' value='$meal_description' />";
        $html .=    "</fieldset>";
        $html .=    "<fieldset>";
        $html .=        "<div>";
        $html .=            "<div>";
        $html .=                "<img width='100' class='server-image' src='$server_image_path' />";
        $html .=                "<select class='server' name='server_id'>";
        $html .=                    "<option value='none'>Select Server</option>";
        $html .=                        $server_list_options;
        $html .=                "</select>";
        $html .=            "</div>";
        $html .=            "<div class='menu-image'>";
        $html .=                "<img width='100' src='../_images/menu-image-placeholder.jpg' />";
        $html .=                "<input name='menu_image' type='file' />";
        $html .=            "</div>";
        $html .=        "</div>";
        $html .=    "</fieldset>";
        for ($i=0; $i < $number_of_meals; $i++) {
            if($mode == 'edit') {
                $item_name = $menu_items[$i]['menu_item_name'];
                $ingredients = $menu_items[$i]['ingredients'];
                $special_notes = $menu_items[$i]['special_notes'];
                $menu_items[$i]['is_vegetarian'] == 1 ? $is_vegetarian_checked = "checked" : $is_vegetarian_checked = "";
                $menu_items[$i]['is_vegan'] == 1 ? $is_vegan_checked = "checked" : $is_vegan_checked = "";
                $menu_items[$i]['is_gluten_free'] == 1 ? $is_gluten_free_checked = "checked" : $is_gluten_free_checked = "";
                $menu_items[$i]['is_whole_grain'] == 1 ? $is_whole_grain_checked = "checked" : $is_whole_grain_checked = "";
                $menu_items[$i]['contains_nuts'] == 1 ? $contains_nuts_checked = "checked" : $contains_nuts_checked = "";
                $menu_items[$i]['contains_soy'] == 1 ? $contains_soy_checked = "checked" : $contains_soy_checked = "";
                $menu_items[$i]['contains_shellfish'] == 1 ? $contains_shellfish_checked = "checked" : $contains_shellfish_checked = "";
                $price_per_order = $menu_items[$i]['price_per_order'];
                $number_of_servings = $menu_items[$i]['number_of_servings'];
                $order_quantity = $menu_items[$i]['order_quantity'];
                $menu_item_id = $menu_items[$i]['menu_item_id'];
                $menu_item_hidden_ids .= "<input type='hidden' name='menu_item_id_array[]' value='$menu_item_id' />";
            } else {
                $item_name = "";
                $ingredients = "";
                $special_notes = "";
                $is_vegetarian_checked = "";
                $is_vegan_checked = "";
                $is_gluten_free_checked = "";
                $is_whole_grain_checked = "";
                $contains_nuts_checked = "";
                $contains_soy_checked = "";
                $contains_shellfish_checked = "";
                $price_per_order = "";
                $number_of_servings = "";
                $order_quantity = 0;
                // $number_of_orders = 0;
            }

            $form .= <<<FORM
            <fieldset>
                <div data-increment-id="$i" class="menu-item menu-item-$i">
                    <input type="text" name="menu_item_name[$i]" value="$item_name" placeholder="Add Menu Item Name" />
                    <input type="text" name="ingredients[$i]" value="$ingredients" placeholder="Add Ingredients" />
                    <input type="text" name="special_notes[$i]" value="$special_notes" placeholder="Special Notes" />
                    <input type="checkbox" value="1" $is_vegetarian_checked name="is_vegetarian[$i]"><label>Vegetarian</label>
                    <input type="checkbox" value="1" $is_vegan_checked name="is_vegan[$i]"><label>Vegan</label>
                    <input type="checkbox" value="1" $is_gluten_free_checked name="is_gluten_free[$i]"><label>Gluten Free</label>
                    <input type="checkbox" value="1" $is_whole_grain_checked name="is_whole_grain[$i]"><label>Whole Grain</label>
                    <input type="checkbox" value="1" $contains_nuts_checked name="contains_nuts[$i]"><label>Contains Nuts</label>
                    <input type="checkbox" value="1" $contains_soy_checked name="contains_soy[$i]"><label>Contains Soy</label>
                    <input type="checkbox" value="1" $contains_shellfish_checked name="contains_shellfish[$i]"><label>Contains Shellfish</label>
                    <h3>Set Price per Order</h3>
                    <input class="price-per-order-input" name="price_per_order[$i]" type="text" value="$price_per_order" placeholder="$0.00" />
                    <input class="serves-input" name="number_of_servings[$i]" type="text" value="$number_of_servings" placeholder="Serves 0" />
                    <p class="order-summary">
                        <span class="quantity">$order_quantity</span> Orders Serves
                        <span class="serves-output">0</span> 
                        $<span class="price-per-order-output">0</span>
                    </p>
                    <input type="hidden" name="order_quantity[$i]" class="order-quantity"  value="" />
                    <input type="hidden" name="meals_per_day" value="$i" />
                    <input type="hidden" name="client_id" value="$client_id" />
                    <input type="hidden" name="item_status_id" value="1" />
                    <a class="quantity-button subtract">Subtract</a>
                    <a class="quantity-button add">Add</a>
                </div>
            </fieldset>
FORM;
        }
        $html .= $form;
        $html .= "<input type='hidden' class='current_day_edit_mode' name='current_day' value='$current_day' />";
        $html .= $menu_item_hidden_ids;
        $html .= "</form>";
        $html .= "<div class='order-summary'>";
        $html .=    "<p>";
        $html .=        "<span class='total-number-of-orders'>0</span> Orders Serves ";
        $html .=        "<span class='total-people-served'>0</span> = ";
        $html .=        "<span class='total-cost'>0</span>";
        $html .=    "</p>";
        $html .=    "<button class='preview_menu_button'>Save and Preview</button>";
        $html .= "</div>";
        return $html;
    }

}