<?php

class Menu {
    
    private $database_connection = null;
    
    public function __construct() {
        require_once("../_config/config.php");
        require_once("../_classes/Messages.php");
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

        // echo '<pre>POST ARRAY: ';
        // print_r($_POST);
        // $sth = $this->database_connection->prepare("SELECT * FROM meals");
        // $sth->execute();
        // $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        // if(count($result) > 0) {
        //     return $result;
        // } else {
        //     echo "Sorry, there was an error. Could not find any meal tyoes."; //TODO - Send as error.
        //     exit();
        // }
    }

    public function get_daily_menu_page() {

        echo "<h1>Daily Menu - Determine Context</h1>";

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
        } else {
            echo '<p>Sorry, no menu items found.</p>';
        }
    }


    public function get_weekly_menu_page() {

        echo "<h1>Weekly Menu - Determine Context</h1>";

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

    public function send_meal_to_client($client_id, $start_date) {
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
            $to_email  = 'aidan@example.com';
            $subject = 'Birthday Reminders for August';
            $message = '
                <html>
                    <head>
                        <title>Birthday Reminders for August</title>
                    </head>
                    <body>
                        <p>Hello! Your weekly menu is ready to review. Please click the link below to review, edit and confirm.</p>
                        <a href="link">link</a>
                    </body>
                </html>';
            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $headers .= 'From: Green Heart Foods <'.FROM_EMAIL.'>' . "\r\n";
            $sent = mail($to, $subject, $message, $headers);
            if($sent) {
                echo "Email has been sent";
            } else {
                echo "Email has not been sent";
            }
        }
    }
}