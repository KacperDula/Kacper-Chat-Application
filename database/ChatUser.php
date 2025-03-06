<?php

// ChatUser.php

class ChatUser
{
    // Private properties related to user information
    private $user_id;
    private $user_name;
    private $user_email;
    private $user_password;
    private $user_profile;
    private $user_status;
    private $user_created_on;
    private $user_verification_code;
    private $user_login_status;
    private $user_token;
    private $user_connection_id;
    public $connect;

    // Constructor: Establishes database connection
    public function __construct()
    {
        require_once('Database_connection.php');
        $database_object = new Database_connection;
        $this->connect = $database_object->connect();
    }

    // Setter and Getter methods for user properties
    function setUserId($user_id) { $this->user_id = $user_id; }
    function getUserId() { return $this->user_id; }

    function setUserName($user_name) { $this->user_name = $user_name; }
    function getUserName() { return $this->user_name; }

    function setUserEmail($user_email) { $this->user_email = $user_email; }
    function getUserEmail() { return $this->user_email; }

    function setUserPassword($user_password) { $this->user_password = $user_password; }
    function getUserPassword() { return $this->user_password; }

    function setUserProfile($user_profile) { $this->user_profile = $user_profile; }
    function getUserProfile() { return $this->user_profile; }

    function setUserStatus($user_status) { $this->user_status = $user_status; }
    function getUserStatus() { return $this->user_status; }

    // Generates a profile avatar with a randomly colored background
    function make_avatar($character)
    {
        $path = "images/". time() . ".png";
        $image = imagecreate(200, 200);
        $red = rand(0, 255);
        $green = rand(0, 255);
        $blue = rand(0, 255);
        imagecolorallocate($image, $red, $green, $blue);  
        $textcolor = imagecolorallocate($image, 255,255,255);
        $font = dirname(__FILE__) . '/font/arial.ttf';
        imagettftext($image, 100, 0, 55, 150, $textcolor, $font, $character);
        imagepng($image, $path);
        imagedestroy($image);
        return $path;
    }

    // Retrieves user data based on email
    function get_user_data_by_email()
    {
        $query = "SELECT * FROM chat_user_table WHERE user_email = :user_email";
        $statement = $this->connect->prepare($query);
        $statement->bindParam(':user_email', $this->user_email);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    // Saves a new user to the database
    function save_data()
    {
        $query = "INSERT INTO chat_user_table (user_name, user_email, user_password, user_profile, user_status, user_created_on, user_verification_code) 
                  VALUES (:user_name, :user_email, :user_password, :user_profile, :user_status, :user_created_on, :user_verification_code)";
        $statement = $this->connect->prepare($query);
        // Binding parameters here...
        return $statement->execute();
    }
}

?>
