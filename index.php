<?php

// Start the session to manage user login state
session_start();

// Initialize an error message variable
$error = '';

// Check if user is already logged in, redirect to chatroom if true
if(isset($_SESSION['user_data']))
{
    header('location:chatroom.php');
}

// Check if login form has been submitted
if(isset($_POST['login']))
{
    // Include the ChatUser class for database interactions
    require_once('database/ChatUser.php');

    // Create a new ChatUser object
    $user_object = new ChatUser;

    // Set the user's email from the form input
    $user_object->setUserEmail($_POST['user_email']);

    // Fetch user data based on email
    $user_data = $user_object->get_user_data_by_email();

    // Check if the user exists in the database
    if(is_array($user_data) && count($user_data) > 0)
    {
        // Check if the user's account is enabled
        if($user_data['user_status'] == 'Enable')
        {
            // Verify the password
            if($user_data['user_password'] == $_POST['user_password'])
            {
                // Set user ID for session tracking
                $user_object->setUserId($user_data['user_id']);

                // Mark user as logged in
                $user_object->setUserLoginStatus('Login');

                // Generate a unique session token
                $user_token = md5(uniqid());

                // Assign the token to the user
                $user_object->setUserToken($user_token);

                // Update login data in the database
                if($user_object->update_user_login_data())
                {
                    // Store user details in session
                    $_SESSION['user_data'][$user_data['user_id']] = [
                        'id'    =>  $user_data['user_id'],
                        'name'  =>  $user_data['user_name'],
                        'profile'   =>  $user_data['user_profile'],
                        'token' =>  $user_token
                    ];

                    // Redirect to chatroom after successful login
                    header('location:chatroom.php');
                }
            }
            else
            {
                // Display error if password is incorrect
                $error = 'Wrong Password';
            }
        }
        else
        {
            // Display error if the user hasn't verified their email
            $error = 'Please Verify Your Email Address';
        }
    }
    else
    {
        // Display error if email doesn't exist in the database
        $error = 'Wrong Email Address';
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

    <title>Load Chat from Mysql Database | PHP Chat Application using Websocket</title>

    <!-- Bootstrap core CSS -->
    <link href="vendor-front/bootstrap/bootstrap.min.css" rel="stylesheet">

    <link href="vendor-front/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">

    <link rel="stylesheet" type="text/css" href="vendor-front/parsley/parsley.css"/>

    <!-- Bootstrap core JavaScript -->
    <script src="vendor-front/jquery/jquery.min.js"></script>
    <script src="vendor-front/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor-front/jquery-easing/jquery.easing.min.js"></script>

    <script type="text/javascript" src="vendor-front/parsley/dist/parsley.min.js"></script>
</head>

<body>

    <div class="container">
        <br />
        <br />
        <h1 class="text-center">Realtime One to One Chat App using Ratchet WebSockets with PHP Mysql - Online Offline Status - 8</h1>
        <div class="row justify-content-md-center mt-5">
            
            <div class="col-md-4">
               <?php
               // Display success message if the user has successfully registered
               if(isset($_SESSION['success_message']))
               {
                    echo '
                    <div class="alert alert-success">
                    '.$_SESSION["success_message"] .'
                    </div>
                    ';
                    unset($_SESSION['success_message']);
               }

               // Display any error messages encountered during login
               if($error != '')
               {
                    echo '
                    <div class="alert alert-danger">
                    '.$error.'
                    </div>
                    ';
               }
               ?>
                <div class="card">
                    <div class="card-header">Login</div>
                    <div class="card-body">
                        <!-- Login Form -->
                        <form method="post" id="login_form">
                            <div class="form-group">
                                <label>Enter Your Email Address</label>
                                <input type="text" name="user_email" id="user_email"  class="form-control" data-parsley-type="email" required />
                            </div>
                            <div class="form-group">
                                <label>Enter Your Password</label>
                                <input type="password" name="user_password" id="user_password" class="form-control" required />
                            </div>
                            <div class="form-group text-center">
                                <input type="submit" name="login" id="login" class="btn btn-primary" value="Login" />
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

// Initialize form validation when the document is ready
$(document).ready(function(){
    
    $('#login_form').parsley();
    
});

</script>
