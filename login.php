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
			$username = $_POST["username"];
			$password = $_POST["password"];
		}
		else{
			$username = '';
			$password = '';
		}
		if(!empty($username)){
			echo '<a class="logout" href="logout.php">Logout</a>';
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
                <label for="username">Username:</label><br />
                <input type="text" name="username" value=<?php echo $username;?>><br />
            </div>
			<br>
            <div class="field">
                <label for="password">Password:</label><br />
                <input type="password" name="password" value=<?php echo $password;?>><br />
            </div>
			<br>
            <div class="field">
                <input class="button" type="submit" value="Login" />
            </div>
			<br><br>
			<div class="register_link">
                <a href="register.php">Create an account</a>
            </div>
			<br>
	</form>
	<?php
		if($_SERVER["REQUEST_METHOD"] == "POST"){
			#error_reporting(E_ERROR | E_PARSE);
			if(
				empty($_POST["username"]) ||
				empty($_POST["password"])){
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

			#Check if password matches username
			$query = "SELECT *
						FROM User
						WHERE username='" . $_POST["username"] . "'
						AND password='" . $_POST["password"] . "'";
			$val = $con->query($query);
			if($val->num_rows <= 0){
				echo '<div class="register_error"> Username and password do not match </div>';
			}
			else{
				$_SESSION["username"] = $_POST["username"];
				$con->close();
				header("Location: profile.php");
			}

			$con->close();
			}
		}
	?>
</body>
</html>
