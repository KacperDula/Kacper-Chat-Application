<?php

// verify.php - Handles email verification for the chat application

$error = '';

session_start(); // Start the session to store messages

// Check if a verification code is provided in the URL
if(isset($_GET['code']))
{
    require_once('database/ChatUser.php'); // Include the ChatUser class

    $user_object = new ChatUser; // Create an instance of ChatUser class

    $user_object->setUserVerificationCode($_GET['code']); // Set the verification code for the user

    // Check if the provided verification code is valid
    if($user_object->is_valid_email_verification_code())
    {
        $user_object->setUserStatus('Enable'); // Enable the user's account

        // Try to enable the user account in the database
        if($user_object->enable_user_account())
        {
            // If successful, store a success message in session and redirect to login page
            $_SESSION['success_message'] = 'Your Email Successfully verified, now you can login into this chat Application';
            header('location:index.php');
        }
        else
        {
            // Display an error if enabling the account fails
            $error = 'Something went wrong, try again....';
        }
    }
    else
    {
        // Display an error if the verification code is invalid
        $error = 'Something went wrong, try again....';
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

    <title>Email Verify | PHP Chat Application using Websocket</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <!-- Bootstrap core JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.js" integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</head>

<body>
    <div class="container">
        <br />
        <br />
        <h1 class="text-center">PHP Chat Application using Websocket</h1>
        
        <div class="row justify-content-md-center">
            <div class="col col-md-4 mt-5">
                <!-- Display error message if verification fails -->
                <div class="alert alert-danger">
                    <h2><?php echo $error; ?></h2>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
