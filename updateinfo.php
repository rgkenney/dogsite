<!DOCTYPE html>
<html lang="en-us">
<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="main.css" type="text/css">
    <style type="text/css">
    </style>
</head>
<body>
    <div class="title">dogs.</div>
	<?php
		session_start();
		$_SESSION["page"] = "login.php";
		if(isset($_SESSION["username"])){
			$username = $_SESSION["username"];
		}
		else{
			header("Location: login.php");
		}

		if(!empty($username)){
			echo '<a class="post" href="postimage.php">Post an Image</a>';
			echo '<a class="logout" href="logout.php">Logout</a>';
		}

		$con = new mysqli("localhost","root","password");
		if(!$con){
			die("Error connecting");
		}

		#Get database
		if(!$con->select_db("sitedb")){
			$con->query("CREATE DATABASE sitedb");
		}

		$query = "SELECT *
				FROM User
				WHERE username='" . $username . "'";
		$val = $con->query($query);
		$row = $val->fetch_assoc();
		$firstname = $row["firstname"];
		$lastname = $row["lastname"];
	?>

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
			<br>
            <div class="field">
                <label for="last_name">Last Name:</label><br />
                <input type="text" name="last_name" value=<?php echo $lastname;?>><br />
            </div>
			<br>
            <div class="field">
                <label for="password">New Password:</label><br />
                <input type="password" name="password"><br />
            </div>
			<br>
            <div class="field">
                <label for="confirm">Confirm New Password:</label><br />
                <input type="password" name="confirm"><br />
            </div>
			<br>
            <div class="field">
                <input class="button" type="submit" value="Update" />
            </div>
			<br><br>

	</form>
	<?php
		if($_SERVER["REQUEST_METHOD"] == "POST"){
			#error_reporting(E_ERROR | E_PARSE);

			$firstname = $_POST["first_name"];
			$lastname = $_POST["last_name"];
			$password = $_POST["password"];
			$confirm = $_POST["confirm"];
			if(empty($firstname) ||
				empty($lastname)){
					echo '<div class="register_error"> Please fill out the required fields </div>';
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
				#Username exists; insert into db
				if(empty($password)){
					$query = "UPDATE User 
							SET firstname='" . $firstname . 
							"', lastname='" . $lastname .
							"' WHERE username='" . $username . "'";
				}
				else{
					$query = "UPDATE User 
							SET firstname='" . $firstname . 
							"', lastname='" . $lastname .
							"', password='" . $password .
							"' WHERE username='" . $username . "'";
				}
				$con->query($query);
				$con->close();
				header("Location: profile.php");
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
