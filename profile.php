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

		if(isset($_SESSION["username"])){
			$username = $_SESSION["username"];
		}
		else{
			header("Location: login.php");
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

		echo "<div class='profile'>";
		echo "<u>Name:</u> " . $row["firstname"] . " " . $row["lastname"] . "<br><br>";
		echo "<u>Username:</u> " . $row["username"] . "<br><br>";
		echo "<u>Joined:</u> " . $row["join_date"] . "<br><br>";


		#Aggregate query, average likes per post
		$query = "SELECT AVG(likes) as average
				FROM Post
				WHERE username='" . $username . "'
				GROUP BY username";
		$val = $con->query($query);
		$row = $val->fetch_assoc();
		echo "<u>Average post popularity:</u><br>";
		$avg = round($row["average"], 0);
		if($avg == 0){
			echo  "No likes";
		}
		else if($avg == 1){
			echo "~1 like";
		}
		else{
			echo "~" . $avg . " likes";
		}

		#Aggregate query, number of posts liked
		echo "<br><u>Posts liked:</u><br>";
		$query = "SELECT COUNT(postID) as likes
				FROM Liked
				WHERE username='" . $username . "'
				GROUP BY username";
		$val = $con->query($query);
		$row = $val->fetch_assoc();
		$sum = $row["likes"];
		if($sum == 0){
			echo  "0 posts liked";
		}
		else if($sum == 1){
			echo "1 post liked";
		}
		else{
			echo $sum . " posts liked";
		}
		echo "<form action='updateinfo.php'> <input class='button' type='submit' value='Update Information' /> </form>";
		echo "<br></div>";

		$query = "SELECT postID
				FROM Favorited
				WHERE username='" . $username . "'";
		$val = $con->query($query);

		echo '<div  class="album">';
			echo '<div class="favorites">';
					echo '<div style="text-align: center; margin-left: 50px; font-size: 25px;">';
						echo 'Favorites';
					echo '</div>';
			echo '</div>';

			while($entry = $val->fetch_assoc()){
				$query = "SELECT *
						FROM Post
						WHERE postID='" . $entry["postID"] . "'";
				$val2 = $con->query($query);

				$entry2 = $val2->fetch_assoc();
				echo '<div class="image_box">';
					echo '<a href="' . $entry2["link"] .'">';
					echo '<img style="border: 3px solid #ffffff;" height=300 src="' . $entry2["image_src"] . '" alt="Image" /></a>';
				echo '</div>';
			}
		echo '</div>';


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

	<?php
		if(!empty($username)){
			echo '<a class="post" href="postimage.php">Post an Image</a>';
			echo '<a class="logout" href="logout.php">Logout</a>';
		}
	?>

	
</body>
</html>
