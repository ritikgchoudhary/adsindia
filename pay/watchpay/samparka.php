<?php
	$conn = mysqli_connect('195.35.6.124', 'a22com', 'a22com', 'a22com');
	
	if (!$conn) {
		echo "Error: " . mysqli_connect_error();
		exit();
	}
	
	date_default_timezone_set("Asia/Kolkata"); 
?>