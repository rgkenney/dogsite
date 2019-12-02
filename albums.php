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
		$_SESSION["page"] = "albums.php";
		if(isset($_SESSION["username"])){
			$username = $_SESSION["username"];
		}
		else{
			$username = '';
		}
		if(!empty($username)){
			echo '<a class="post" href="postimage.php">Post an Image</a>';
			echo '<a class="logout" href="logout.php">Logout</a>';
		}
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
        <li class="header"><a class="highlighted">Albums</a></li>
        <?php
			if(!empty($_SESSION["username"])){
				echo '<li class="header"><a class="link" href="profile.php">Profile</a></li>';
			}
			else{
				echo '<li class="header"><a class="link" href="login.php">Profile</a></li>';
			}
		?>
    </ul>

	<?php
		#Login to database
		$con = new mysqli("localhost","root","password");
		if(!$con){
			die("Error connecting");
		}

		#Get database
		if(!$con->select_db("sitedb")){
			$con->query("CREATE DATABASE sitedb");
		}
		$query = "SELECT *
				FROM Album
				INNER JOIN inAlbum ON Album.albumID=inAlbum.albumID
				INNER JOIN Post ON inAlbum.postID=Post.postID
				ORDER BY created DESC";
		$val = $con->query($query);

		echo '<div style="padding-top: 250px; width: 100%">';
		$current_album = '';
		echo '<div  class="album">';
		while($entry = $val->fetch_assoc()){
			if(empty($current_album)){
				$current_album = $entry["name"];
				echo '</div>';
				echo '<div  class="album">';
				echo '<div class="name">';
					echo '<div style="float: left; margin-left: 50px; font-size: 25px;">';
						echo $entry["name"];
					echo '</div>';
					echo '<div style="float: right; margin-right: 50px; font-size: 25px;">';
						echo 'Created by: ' . $entry["username"];
					echo '</div>';
				echo '</div>';
			}
			else if(strcmp($entry["name"], $current_album) != 0){
				$current_album = $entry["name"];
				echo '</div>';
				echo '<div  class="album">';
				echo '<div class="name">';
					echo '<div style="float: left; margin-left: 50px; font-size: 25px;">';
						echo $entry["name"];
					echo '</div>';
					echo '<div style="float: right; margin-right: 50px; font-size: 25px;">';
						echo 'Created by: ' . $entry["username"];
					echo '</div>';
				echo '</div>';
			}

			echo '<div class="image_box">';
					echo '<a href="' . $entry["link"] .'">';
					echo '<img style="border: 3px solid #ffffff;" height=300 src="' . $entry["image_src"] . '" alt="Image" /></a>';
			echo '</div>';

			/*
			$query = "SELECT postID
					FROM inAlbum
					WHERE albumID='" . $entry["albumID"] . "'";
			$val2 = $con->query($query);

			echo '<div  class="album">';
			if($val2->num_rows != 0){
				echo '<div class="name">';
					echo '<div style="float: left; margin-left: 50px; font-size: 25px;">';
						echo $entry["name"];
					echo '</div>';
					echo '<div style="float: right; margin-right: 50px; font-size: 25px;">';
						echo 'Created by: ' . $entry["username"];
					echo '</div>';
				echo '</div>';
			}
			while($entry2 = $val2->fetch_assoc()){
				$query = "SELECT *
						FROM Post
						WHERE postID='" . $entry2["postID"] . "'";
				$val3 = $con->query($query);
				$entry3 = $val3->fetch_assoc();
				echo '<div class="image_box">';
					echo '<a href="' . $entry3["link"] .'">';
					echo '<img style="border: 3px solid #ffffff;" height=300 src="' . $entry3["image_src"] . '" alt="Image" /></a>';
				echo '</div>';
			}
			echo '</div>';
			*/
		}
		echo '</div>';
		$con->close();
	?>
</body>
</html>
