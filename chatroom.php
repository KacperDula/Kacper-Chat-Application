<?php 
// Start the session to track user login state
session_start();

// Redirect to login page if user is not logged in
if(!isset($_SESSION['user_data']))
{
	header('location:index.php');
}

// Include the ChatUser and ChatRooms classes for handling chat data
require('database/ChatUser.php');
require('database/ChatRooms.php');

// Create ChatRooms object to retrieve chat messages
$chat_object = new ChatRooms;
$chat_data = $chat_object->get_all_chat_data();

// Create ChatUser object to retrieve user data
$user_object = new ChatUser;
$user_data = $user_object->get_user_all_data();
?>

<!DOCTYPE html>
<html>
<head>
	<title>Chat application in PHP using WebSocket programming</title>

	<!-- Bootstrap core CSS -->
    <link href="vendor-front/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="vendor-front/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="vendor-front/parsley/parsley.css"/>

    <!-- Bootstrap core JavaScript -->
    <script src="vendor-front/jquery/jquery.min.js"></script>
    <script src="vendor-front/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor-front/jquery-easing/jquery.easing.min.js"></script>
    <script type="text/javascript" src="vendor-front/parsley/dist/parsley.min.js"></script>

	<style type="text/css">
		/* Styling for chat application */
		html, body {
		  height: 100%;
		  width: 100%;
		  margin: 0;
		}
		#wrapper {
			display: flex;
		  	flex-flow: column;
		  	height: 100%;
		}
		#remaining {
			flex-grow : 1;
		}
		#messages {
			height: 200px;
			background: whitesmoke;
			overflow: auto;
		}
		#chat-room-frm {
			margin-top: 10px;
		}
		#user_list {
			height: 450px;
			overflow-y: auto;
		}
		#messages_area {
			height: 650px;
			overflow-y: auto;
			background-color:#e6e6e6;
		}
	</style>
</head>
<body>
	<div class="container">
		<br />
        <h3 class="text-center">Realtime One-to-One Chat App using Ratchet WebSockets with PHP & MySQL</h3>
        <br />
		<div class="row">
			<!-- Chat Room Section -->
			<div class="col-lg-8">
				<div class="card">
					<div class="card-header">
						<div class="row">
							<div class="col col-sm-6">
								<h3>Chat Room</h3>
							</div>
							<div class="col col-sm-6 text-right">
								<a href="privatechat.php" class="btn btn-success btn-sm">Private Chat</a>
							</div>
						</div>
					</div>
					<div class="card-body" id="messages_area">
					<?php
					// Loop through chat messages and display them
					foreach($chat_data as $chat)
					{
						// Check if the message sender is the logged-in user
						if(isset($_SESSION['user_data'][$chat['userid']]))
						{
							$from = 'Me';
							$row_class = 'row justify-content-start';
							$background_class = 'text-dark alert-light';
						}
						else
						{
							$from = $chat['user_name'];
							$row_class = 'row justify-content-end';
							$background_class = 'alert-success';
						}

						// Display chat messages
						echo '
						<div class="'.$row_class.'">
							<div class="col-sm-10">
								<div class="shadow-sm alert '.$background_class.'">
									<b>'.$from.' - </b>'.$chat["msg"].'
									<br />
									<div class="text-right">
										<small><i>'.$chat["created_on"].'</i></small>
									</div>
								</div>
							</div>
						</div>
						';
					}
					?>
					</div>
				</div>

				<!-- Chat Message Form -->
				<form method="post" id="chat_form" data-parsley-errors-container="#validation_error">
					<div class="input-group mb-3">
						<textarea class="form-control" id="chat_message" name="chat_message" placeholder="Type Message Here" data-parsley-maxlength="1000" data-parsley-pattern="/^[a-zA-Z0-9\s]+$/" required></textarea>
						<div class="input-group-append">
							<button type="submit" name="send" id="send" class="btn btn-primary"><i class="fa fa-paper-plane"></i></button>
						</div>
					</div>
					<div id="validation_error"></div>
				</form>
			</div>

			<!-- User Profile & User List Section -->
			<div class="col-lg-4">
				<?php
				$login_user_id = '';

				// Fetch logged-in user details
				foreach($_SESSION['user_data'] as $key => $value)
				{
					$login_user_id = $value['id'];
				?>
				<input type="hidden" name="login_user_id" id="login_user_id" value="<?php echo $login_user_id; ?>" />
				<div class="mt-3 mb-3 text-center">
					<img src="<?php echo $value['profile']; ?>" width="150" class="img-fluid rounded-circle img-thumbnail" />
					<h3 class="mt-2"><?php echo $value['name']; ?></h3>
					<a href="profile.php" class="btn btn-secondary mt-2 mb-2">Edit</a>
					<input type="button" class="btn btn-primary mt-2 mb-2" name="logout" id="logout" value="Logout" />
				</div>
				<?php
				}
				?>

				<!-- User List Display -->
				<div class="card mt-3">
					<div class="card-header">User List</div>
					<div class="card-body" id="user_list">
						<div class="list-group list-group-flush">
						<?php
						if(count($user_data) > 0)
						{
							foreach($user_data as $key => $user)
							{
								$icon = '<i class="fa fa-circle text-danger"></i>';

								if($user['user_login_status'] == 'Login')
								{
									$icon = '<i class="fa fa-circle text-success"></i>';
								}

								// Display other online users except the logged-in user
								if($user['user_id'] != $login_user_id)
								{
									echo '
									<a class="list-group-item list-group-item-action">
										<img src="'.$user["user_profile"].'" class="img-fluid rounded-circle img-thumbnail" width="50" />
										<span class="ml-1"><strong>'.$user["user_name"].'</strong></span>
										<span class="mt-2 float-right">'.$icon.'</span>
									</a>
									';
								}
							}
						}
						?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>

<!-- WebSocket JavaScript -->
<script type="text/javascript">
	
	$(document).ready(function(){

		var conn = new WebSocket('ws://localhost:8080');
		conn.onopen = function(e) {
		    console.log("Connection established!");
		};

		conn.onmessage = function(e) {
		    var data = JSON.parse(e.data);
		    var row_class = data.from == 'Me' ? 'row justify-content-start' : 'row justify-content-end';
		    var background_class = data.from == 'Me' ? 'text-dark alert-light' : 'alert-success';

		    var html_data = "<div class='"+row_class+"'><div class='col-sm-10'><div class='shadow-sm alert "+background_class+"'><b>"+data.from+" - </b>"+data.msg+"<br /><div class='text-right'><small><i>"+data.dt+"</i></small></div></div></div></div>";

		    $('#messages_area').append(html_data);
		    $("#chat_message").val("");
		};
	});
	
</script>
</html>
