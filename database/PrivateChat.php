<?php

// PrivateChat.php

class PrivateChat
{
	private $chat_message_id; // Stores the unique ID of the chat message
	private $to_user_id; // Stores the recipient user's ID
	private $from_user_id; // Stores the sender user's ID
	private $chat_message; // Stores the chat message content
	private $timestamp; // Stores the timestamp of the message
	private $status; // Stores the status of the message (e.g., read/unread)
	protected $connect; // Database connection instance

	public function __construct()
	{
		// Include the database connection file
		require_once('Database_connection.php');

		// Create a new database connection
		$db = new Database_connection();

		// Assign the connection to the class property
		$this->connect = $db->connect();
	}

	// Setter and Getter for chat_message_id
	function setChatMessageId($chat_message_id)
	{
		$this->chat_message_id = $chat_message_id;
	}

	function getChatMessageId()
	{
		return $this->chat_message_id;
	}

	// Setter and Getter for to_user_id
	function setToUserId($to_user_id)
	{
		$this->to_user_id = $to_user_id;
	}

	function getToUserId()
	{
		return $this->to_user_id;
	}

	// Setter and Getter for from_user_id
	function setFromUserId($from_user_id)
	{
		$this->from_user_id = $from_user_id;
	}

	function getFromUserId()
	{
		return $this->from_user_id;
	}

	// Setter and Getter for chat_message
	function setChatMessage($chat_message)
	{
		$this->chat_message = $chat_message;
	}

	function getChatMessage()
	{
		return $this->chat_message;
	}

	// Setter and Getter for timestamp
	function setTimestamp($timestamp)
	{
		$this->timestamp = $timestamp;
	}

	function getTimestamp()
	{
		return $this->timestamp;
	}

	// Setter and Getter for status
	function setStatus($status)
	{
		$this->status = $status;
	}

	function getStatus()
	{
		return $this->status;
	}

	// Function to retrieve all chat messages between two users
	function get_all_chat_data()
	{
		$query = "
		SELECT a.user_name as from_user_name, b.user_name as to_user_name, chat_message, timestamp, status, to_user_id, from_user_id  
			FROM chat_message 
		INNER JOIN chat_user_table a 
			ON chat_message.from_user_id = a.user_id 
		INNER JOIN chat_user_table b 
			ON chat_message.to_user_id = b.user_id 
		WHERE (chat_message.from_user_id = :from_user_id AND chat_message.to_user_id = :to_user_id) 
		OR (chat_message.from_user_id = :to_user_id AND chat_message.to_user_id = :from_user_id)
		";

		// Prepare the query
		$statement = $this->connect->prepare($query);

		// Bind parameters
		$statement->bindParam(':from_user_id', $this->from_user_id);
		$statement->bindParam(':to_user_id', $this->to_user_id);

		// Execute the query
		$statement->execute();

		// Return the fetched chat data
		return $statement->fetchAll(PDO::FETCH_ASSOC);
	}

	// Function to save a new chat message into the database
	function save_chat()
	{
		$query = "
		INSERT INTO chat_message 
			(to_user_id, from_user_id, chat_message, timestamp, status) 
			VALUES (:to_user_id, :from_user_id, :chat_message, :timestamp, :status)
		";

		// Prepare the query
		$statement = $this->connect->prepare($query);

		// Bind parameters
		$statement->bindParam(':to_user_id', $this->to_user_id);
		$statement->bindParam(':from_user_id', $this->from_user_id);
		$statement->bindParam(':chat_message', $this->chat_message);
		$statement->bindParam(':timestamp', $this->timestamp);
		$statement->bindParam(':status', $this->status);

		// Execute the query
		$statement->execute();

		// Return the last inserted ID
		return $this->connect->lastInsertId();
	}

	// Function to update the status of a specific chat message
	function update_chat_status()
	{
		$query = "
		UPDATE chat_message 
			SET status = :status 
			WHERE chat_message_id = :chat_message_id
		";

		// Prepare the query
		$statement = $this->connect->prepare($query);

		// Bind parameters
		$statement->bindParam(':status', $this->status);
		$statement->bindParam(':chat_message_id', $this->chat_message_id);

		// Execute the query
		$statement->execute();
	}

	// Function to mark all unread messages from a user as read
	function change_chat_status()
	{
		$query = "
		UPDATE chat_message 
			SET status = 'Yes' 
			WHERE from_user_id = :from_user_id 
			AND to_user_id = :to_user_id 
			AND status = 'No'
		";

		// Prepare the query
		$statement = $this->connect->prepare($query);

		// Bind parameters
		$statement->bindParam(':from_user_id', $this->from_user_id);
		$statement->bindParam(':to_user_id', $this->to_user_id);

		// Execute the query
		$statement->execute();
	}

}

?>
