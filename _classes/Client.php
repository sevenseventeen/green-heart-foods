<?php 

class Client {

	private $database_connection = null;

	public function __construct() {
		require_once("../_classes/Database.php");
        require_once("../_classes/Messages.php");
        require_once("../_classes/User.php");
        require_once("../_classes/Image.php");
        $this->image = new Image();
		$database = new Database();
		$this->database_connection = $database->connect();
	}

    public function get_client($client_id) {
        $arguments = [$client_id];
        $query = $this->database_connection->prepare("SELECT * FROM clients LEFT JOIN users ON clients.admin_user_id = users.user_id WHERE clients.client_id = ?");
        // $query = $this->database_connection->prepare("SELECT * FROM clients WHERE client_id = ?");
        $query->execute($arguments);
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        if(count($result) > 0) {
            return $result;
        } else {
            echo "Sorry, there was an error. No clients were found."; //TODO - Send as error.
        }
    }

    public function get_all_clients(){
        $query = $this->database_connection->prepare("SELECT * FROM clients");
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        if(count($result) > 0) {
            return $result;
        } else {
            echo "Sorry, there was an error. No clients were found."; //TODO - Send as error.
        }
    }

    public function update_client() {

        // TODO Figure out logic for files.
        // TODO - Fix update flow to work with users in user table.

        $client_id = $_POST['client_id'];

        $arguments = [
            $_POST['admin_email'],
            $_POST['admin_password'],
            $client_id,
            2
        ];
        $query = $this->database_connection->prepare("UPDATE users SET user_name = ?, password = ? WHERE client_id = ? AND user_type_id = ?");
        $result = $query->execute($arguments);

        $arguments = [
            $_POST['general_username'],
            $_POST['general_password'],
            $client_id,
            3
        ];
        $query = $this->database_connection->prepare("UPDATE users SET user_name = ?, password = ? WHERE client_id = ? AND user_type_id = ?");
        $result = $query->execute($arguments);

        if(!isset($_POST['has_breakfast'])) $_POST['has_breakfast'] = 0;
        if(!isset($_POST['has_lunch'])) $_POST['has_lunch'] = 0;
        if(!isset($_POST['has_dinner'])) $_POST['has_dinner'] = 0;
        if(!isset($_POST['has_snack'])) $_POST['has_snack'] = 0;
        if(!isset($_POST['is_active'])) $_POST['is_active']= 0;

        $company_logo_large = 'TODO - Large'; //$_FILES["company_logo"]["name"];
        $company_logo_small = 'TODO - Small'; //$_FILES["company_logo"]["name"];
        $arguments = [
            $_POST['company_name'],
            $_POST['admin_name'],
            // $_POST['admin_email'],
            // $_POST['admin_password'],
            // $_POST['general_username'],
            // $_POST['general_password'],
            $_POST['meals_per_day'],
            $_POST['has_breakfast'],
            $_POST['has_lunch'],
            $_POST['has_dinner'],
            $_POST['has_snack'],
            $company_logo_large,
            $company_logo_small,
            $_POST['is_active'],
            $client_id
        ];
        $query = $this->database_connection->prepare("UPDATE clients SET company_name = ?, admin_name = ?, meals_per_day = ?, has_breakfast = ?, has_lunch = ?, has_dinner = ?, has_snack = ?, company_logo_large = ?, company_logo_small = ?, is_active = ? WHERE client_id = ?");
        $result = $query->execute($arguments);
        // print_r($result);
        if($query->rowCount() === 1){
            Messages::add($_POST['company_name'].' has been updated.');
            header('Location: ../admin/clients.php');
        }
    }

    
    /* Create Client */ 
    // TODO - Check for correct file type, size, pre-existing name
    // TODO - Rename image on upload.

    
    public function create_client() {
        foreach ($_POST as $key => $value) {
            echo $key." : ".$value."<br />";
            if ($value === '') {
                echo "Sorry, it looks like you missed a field. Please try again."; // TODO - Send as error.
                // exit();       
            }
        }

        $company_logo_small = 'temp'; //$this->image->upload_image($_FILES, "company_logo_small");
        $company_logo_large = 'temp'; //$this->image->upload_image($_FILES, "company_logo_large");
        
        if ($company_logo_small && $company_logo_large) {
            if(!isset($_POST['has_breakfast'])) $_POST['has_breakfast'] = 0;
            if(!isset($_POST['has_lunch'])) $_POST['has_lunch'] = 0;
            if(!isset($_POST['has_dinner'])) $_POST['has_dinner'] = 0;
            if(!isset($_POST['has_snack'])) $_POST['has_snack'] = 0;
            if(!isset($_POST['is_active'])) $_POST['is_active']= 0;
            $arguments = array(
                $_POST['company_name'],
                $company_logo_small,
                $company_logo_large,
                $_POST['admin_name'],
                $_POST['meals_per_day'],
                $_POST['has_breakfast'],
                $_POST['has_lunch'],
                $_POST['has_dinner'],
                $_POST['has_snack'],
                $_POST['is_active']
            );
            $query = $this->database_connection->prepare("INSERT INTO clients (company_name, company_logo_large, company_logo_small, admin_name, meals_per_day, has_breakfast, has_lunch, has_dinner, has_snack, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $query->execute($arguments);
            $client_id = $this->database_connection->lastInsertId();
            
            if($query->rowCount() === 1){

                // Admin user arguments and query

                $arguments = [
                    $client_id,
                    2,
                    $_POST['admin_email'],
                    $_POST['admin_password'],
                ];
                $query = $this->database_connection->prepare("INSERT INTO users (client_id, user_type_id, user_name, password) VALUES (?, ?, ?, ?)");
                $admin_user_result = $query->execute($arguments);

                // General user arguments and query

                $arguments = [
                    $client_id,
                    3,
                    $_POST['general_username'],
                    $_POST['general_password'],
                ];
                $query = $this->database_connection->prepare("INSERT INTO users (client_id, user_type_id, user_name, password) VALUES (?, ?, ?, ?)");
                $general_user_result = $query->execute($arguments);

                if($admin_user_result && $general_user_result) {
                    Messages::add('The client, '.$_POST['company_name'].', has been added.');
                    header('Location: ../admin/clients.php');    
                } else {
                    Messages::add('Error, there was a problem adding either the client admin or general user');
                    header('Location: ../admin/clients.php');    
                }
            }
        } else {
            Messages::add('Sorry, there was a problem uploading the images. The client has not been created');
            header('Location: ../admin/clients.php');
        }
    }


    /* 
    
    get_client_form()
    returns the html for the client form. 
    Putting it here allows us to use the same HTML for both create and edit

    */


    public function get_client_form($client_id = null) {

        // If client_id is null, the form is used for creating. 
        // Otherwise, it's for editing. 

        /*

        We have a litte issue here. When creating a client we're also creating 2 
        user types for that client. To standardize the login flow and permissions systems, both user types are stored 
        in the users table, rather than the original design of storing them as fields in the client database. 
        The creation script is working fine, but there's a problem during editing a client. Specifically, when 
        we do a join to merge the client table with the it's associated users in the user table, 

        */


        if ($client_id !== null) {
            $user = new User();
            $client = $this->get_client($client_id);
            $client_users = $user->get_client_users($client_id);
            for($i=0; $i<count($client_users); $i++) {
                switch ($client_users[$i]['user_type_id']) {
                    case '2':
                        $admin_email = $client_users[$i]['user_name'];
                        $admin_password = $client_users[$i]['password'];
                        break;
                    case '3':
                        $general_username = $client_users[$i]['user_name'];
                        $general_password = $client_users[$i]['password'];
                        break;
                }
            }
            $company_logo_large = $client[0]['company_logo_large'];
            $company_logo_small = $client[0]['company_logo_small'];
            $company_name = $client[0]['company_name'];
            $admin_name = $client[0]['admin_name'];
            $admin_email = $admin_email;
            $admin_password = $admin_password;
            $general_username = $general_username;
            $general_password = $general_password;
            $meals_per_day = $client[0]['meals_per_day'];
            $has_breakfast = $client[0]['has_breakfast'];
            $has_lunch = $client[0]['has_lunch'];
            $has_dinner = $client[0]['has_dinner'];
            $has_snack = $client[0]['has_snack'];
            $is_active = $client[0]['is_active'];
            $client[0]['has_breakfast'] == 1 ? $has_breakfast_checked = "checked" : $has_breakfast_checked = "";
            $client[0]['has_lunch'] == 1 ? $has_lunch_checked = "checked" : $has_lunch_checked = "";
            $client[0]['has_dinner'] == 1 ? $has_dinner_checked = "checked" : $has_dinner_checked = "";
            $client[0]['has_snack'] == 1 ? $has_snack_checked = "checked" : $has_snack_checked = "";
            $client[0]['is_active'] == 1 ? $is_active_checked = "checked" : $is_active_checked = "";
            $form_action = '../_actions/update-client.php';
        } else {
            $company_logo_large = "";
            $company_logo_small = "";
            $company_name = "";
            $admin_name = "";
            $admin_email = "";
            $admin_password = "";
            $general_username = "";
            $general_password = "";
            $meals_per_day = "";
            $has_breakfast = "";
            $has_lunch = "";
            $has_dinner = "";
            $has_snack = "";
            $is_active = "";
            $has_breakfast_checked = "";
            $has_lunch_checked = "";
            $has_dinner_checked = "";
            $has_snack_checked = "";
            $is_active_checked = "";
            $form_action = '../_actions/create-client.php';
        }

        return <<<HTML

        <form method="post" action="$form_action" enctype="multipart/form-data">

            <label>Company Logo Large</label>
            <input name='company_logo_large' type='file' value='$company_logo_large' />

            <label>Company Logo Small</label>
            <input name='company_logo_small' type='file' value='$company_logo_small' />

            <label>Company Name</label>
            <input name="company_name" type='text' value="$company_name" />

            <label>Admin Name</label>
            <input name='admin_name' type='text' value='$admin_name' />

            <label>Admin Email</label>
            <input name='admin_email' type='text' value='$admin_email' />

            <label>Admin Password</label>
            <input name='admin_password' type='text' value='$admin_password' />

            <label>General Login Username</label>
            <input name='general_username' type='text' value='$general_username' />

            <label>General Login Password</label>
            <input name='general_password' type='text' value='$general_password' />

            <label>Number of Items per Meal</label>
            <input name='meals_per_day' type='text' value='$meals_per_day' />

            <label>Breakfast</label>
            <input name='has_breakfast' type='checkbox' $has_breakfast_checked value="1" />

            <label>Lunch</label>
            <input name='has_lunch' type='checkbox' $has_lunch_checked value="1" />

            <label>Dinner</label>
            <input name='has_dinner' type='checkbox' $has_dinner_checked value="1" />

            <label>Snacks</label>
            <input name='has_snack' type='checkbox' $has_snack_checked value="1" />

            <label>Active</label>
            <input name='is_active' type='checkbox' $is_active_checked value="1" />

            <input type="hidden" name="client_id" value="$client_id" />

            <input type='submit' value='Submit'>

        </form>
HTML;
    }

    // TODO - Figure out unique id/system for weeks / week date ranges.

    public function get_weekly_menu($client_id) {
        $arguments = array(
            $client_id
        );
        $query = $this->database_connection->prepare("SELECT * FROM menu_items WHERE client_id = ?");
        $query->execute($arguments);
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        if(count($result) > 0) {
            return $result;
        } else {
            echo "Sorry, no menu for this week."; //TODO - Send as error.
        }
    }

    /* Get number of meals by client_id */

    public function get_meals_per_day($client_id) {
        $arguments = array(
            $client_id
        );
        $query = $this->database_connection->prepare("SELECT meals_per_day FROM clients WHERE client_id = ?");
        $query->execute($arguments);
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        if(count($result) > 0) {
            return $result;
        } else {
            echo "Sorry, there was an error. No meals found."; //TODO - Send as error.
        }
    }
}