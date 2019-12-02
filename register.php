<!DOCTYPE html>
<html lang="en-us">
<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="main.css" type="text/css">
    <style type="text/css">
    </style>
</head>
<body>
	<?php
		session_start();
		$_SESSION["page"] = "login.php";
		if(isset($_POST["username"])){
			$firstname = trim($_POST["first_name"]);
			$lastname = trim($_POST["last_name"]);
			$username = trim($_POST["username"]);
			$password = $_POST["password"];
			$confirm = $_POST["confirm"];
		}
		else{
			$firstname = '';
			$lastname = '';
			$username = '';
			$password = '';
			$confirm = '';
		}
	?>
    <div class="title">dogs.</div>

	<form class="search" action="images.php" method="get">
		<input class="search_bar" type="text" name="breed" placeholder="Search for dog breed">
		<div>
		<select name="sort" class="search_order">
			<option value="highest">Highest Rated</option>
			<option value="lowest">Lowest Rated</option>
			<option value="newest">Newest</option>
			<option value="oldest">Oldest</option>
		</select>
		<select name="time" class="search_time">
			<option value="hour">Past Hour</option>
			<option value="day" selected>Past Day</option>
			<option value="week">Past Week</option>
		</select>
		<button class="search_button" type="submit">Search</button>
		</div>
	</form>

    <ul class="navbar">
        <li class="header"><a class="link" href="images.php">Images</a></li>
        <li class="header"><a class="link" href="albums.php">Albums</a></li>
        <li class="header"><a class="highlighted">Profile</a></li>
    </ul>

    <form class="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
            <div class="field">
                <label for="first_name">First Name:</label><br />
                <input type="text" name="first_name" value=<?php echo $firstname;?>><br />
            </div>
            <div class="field">
                <label for="last_name">Last Name:</label><br />
                <input type="text" name="last_name" value=<?php echo $lastname;?>><br />
            </div>
            <div class="field">
                <label for="username">Username:</label><br />
                <input type="text" name="username" value=<?php echo $username;?>><br />
            </div>
            <div class="field">
                <label for="password">Password:</label><br />
                <input type="password" name="password" value=<?php echo $password;?>><br />
            </div>
            <div class="field">
                <label for="confirm">Confirm Password:</label><br />
                <input type="password" name="confirm" value=<?php echo $confirm;?>><br />
            </div>
			<br>
            <div class="field">
                <input class="button" type="submit" value="Register" />
            </div>
			<br>
	</form>
	<?php
		if($_SERVER["REQUEST_METHOD"] == "POST"){
			#error_reporting(E_ERROR | E_PARSE);

			if(empty($firstname) ||
				empty($lastname) ||
				empty($username) ||
				empty($password) ||
				empty($confirm)){
					echo '<div class="register_error"> Please fill out all fields </div>';
			}
			else{
	
			#Login to database
			$con = new mysqli("localhost","root","password");
			if(!$con){
				die("Error connecting");
			}

			#Get database
			if(!$con->select_db("sitedb")){
				$con->query("CREATE DATABASE sitedb");
			}


			#Check if user table exists
			if(!$con->query("DESCRIBE 'User'")){
				$query = "
					CREATE TABLE User (
					username char(20) PRIMARY KEY,
					firstname VARCHAR(20) NOT NULL,
					lastname VARCHAR(20) NOT NULL,
					password VARCHAR(100) NOT NULL,
					join_date DATE
					)
				";
				$con->query($query);
			}
			#Check if password matches confirm
			if(strcmp($password, $confirm) == 0){
				#Check if username exists
				$query = "SELECT *
						FROM User
						WHERE username='" . $username . "'";
				$val = $con->query($query);
				if($val->num_rows > 0){
					echo '<div class="register_error"> Username already exists </div>';
				}
				else{
					#Username exists; insert into db
					$query = "INSERT INTO User
					(username, firstname, lastname, password, join_date) VALUES ('"
					. $username . "', '" . $firstname .
					"', '" . $lastname . "', '" . $password .  "', NOW())";

					$con->query($query);
					$_SESSION["username"] = $username;
					$con->close();
					header("Location: profile.php");
				}
			}
			else{
				echo '<div class="register_error"> Passwords do not match </div>';
			}


			$con->close();
			}
		}
	?>
</body>
</html>
