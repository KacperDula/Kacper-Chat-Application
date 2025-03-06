<?php

// Register.php - Handles user registration and email verification

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Load PHPMailer library

$error = '';
$success_message = '';

// Check if the register form is submitted
if(isset($_POST["register"]))
{
    session_start(); // Start session for user authentication

    // Redirect if user is already logged in
    if(isset($_SESSION['user_data']))
    {
        header('location:chatroom.php');
    }

    require_once('database/ChatUser.php'); // Include the ChatUser class

    $user_object = new ChatUser; // Create an instance of ChatUser class

    // Set user details from form input
    $user_object->setUserName($_POST['user_name']);
    $user_object->setUserEmail($_POST['user_email']);
    $user_object->setUserPassword($_POST['user_password']);

    // Generate user profile avatar based on the first letter of their name
    $user_object->setUserProfile($user_object->make_avatar(strtoupper($_POST['user_name'][0])));
    
    $user_object->setUserStatus('Disabled'); // Set initial status as disabled
    $user_object->setUserCreatedOn(date('Y-m-d H:i:s')); // Set registration timestamp
    $user_object->setUserVerificationCode(md5(uniqid())); // Generate a unique verification code

    // Check if the email is already registered
    $user_data = $user_object->get_user_data_by_email();

    if(is_array($user_data) && count($user_data) > 0)
    {
        $error = 'This Email is already registered';
    }
    else
    {
        // Save user data and send verification email
        if($user_object->save_data())
        {
            $mail = new PHPMailer(true); // Create PHPMailer instance
            
            $mail->isSMTP(); // Use SMTP protocol
            $mail->Host = 'Host Name'; // SMTP Host
            $mail->SMTPAuth = true; // Enable SMTP authentication
            $mail->Username = 'SMTP Username'; // SMTP username
            $mail->Password = 'SMTP Password'; // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
            $mail->Port = 80; // SMTP port
            
            $mail->setFrom('tutorial@webslesson.info', 'Webslesson'); // Set sender
            $mail->addAddress($user_object->getUserEmail()); // Add recipient email
            
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = 'Registration Verification for Chat Application Demo'; // Email subject
            
            // Email body with verification link
            $mail->Body = '
            <p>Thank you for registering for Chat Application Demo.</p>
            <p>This is a verification email, please click the link to verify your email address.</p>
            <p><a href="http://localhost:81/tutorial/chat_application/verify.php?code='.$user_object->getUserVerificationCode().'">Click to Verify</a></p>
            <p>Thank you...</p>
            ';

            $mail->send(); // Send the email

            // Set success message
            $success_message = 'Verification Email sent to ' . $user_object->getUserEmail() . ', please verify your email before logging in';
        }
        else
        {
            $error = 'Something went wrong, please try again';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Register | PHP Chat Application using Websocket</title>
    
    <!-- Bootstrap core CSS -->
    <link href="vendor-front/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="vendor-front/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="vendor-front/parsley/parsley.css"/>
    
    <!-- Bootstrap core JavaScript -->
    <script src="vendor-front/jquery/jquery.min.js"></script>
    <script src="vendor-front/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor-front/jquery-easing/jquery.easing.min.js"></script>
    <script type="text/javascript" src="vendor-front/parsley/dist/parsley.min.js"></script>
</head>

<body>
    <div class="container">
        <br />
        <br />
        <h1 class="text-center">Chat Application in PHP & MySQL using WebSocket - Email Verification</h1>
        
        <div class="row justify-content-md-center">
            <div class="col col-md-4 mt-5">
                <?php
                // Display error message if registration fails
                if($error != '')
                {
                    echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">'.$error.'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                }
                
                // Display success message if registration succeeds
                if($success_message != '')
                {
                    echo '<div class="alert alert-success">'.$success_message.'</div>';
                }
                ?>
                
                <div class="card">
                    <div class="card-header">Register</div>
                    <div class="card-body">
                        <form method="post" id="register_form">
                            <div class="form-group">
                                <label>Enter Your Name</label>
                                <input type="text" name="user_name" id="user_name" class="form-control" data-parsley-pattern="/^[a-zA-Z\s]+$/" required />
                            </div>
                            <div class="form-group">
                                <label>Enter Your Email</label>
                                <input type="text" name="user_email" id="user_email" class="form-control" data-parsley-type="email" required />
                            </div>
                            <div class="form-group">
                                <label>Enter Your Password</label>
                                <input type="password" name="user_password" id="user_password" class="form-control" data-parsley-minlength="6" data-parsley-maxlength="12" required />
                            </div>
                            <div class="form-group text-center">
                                <input type="submit" name="register" class="btn btn-success" value="Register" />
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<script>
$(document).ready(function(){
    $('#register_form').parsley();
});
</script>
