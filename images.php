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
		$_SESSION["page"] = "images.php";
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

		#Login to database
		$con = new mysqli("localhost","root","password");
		if(!$con){
			die("Error connecting");
		}

		#Get database
		if(!$con->select_db("sitedb")){
			$con->query("CREATE DATABASE sitedb");
		}

		#Check if Post table exists
		if(!$con->query("DESCRIBE 'Post'")){
			$query = "
				CREATE TABLE Post (
				postID char(32) PRIMARY KEY,
				username char(20) NOT NULL,
				link char(200) NOT NULL,
				image_src char(200) NOT NULL,
				posted DATETIME NOT NULL,
				likes INT NOT NULL,
				favorites INT NOT NULL,
				reports INT NOT NULL
				)
			";
			$con->query($query);
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
        <li class="header"><a class="highlighted">Images</a></li>
        <li class="header"><a class="link" href="albums.php">Albums</a></li>
		<?php
			if(!empty($_SESSION["username"])){
				echo '<li class="header"><a class="link" href="profile.php">Profile</a></li>';
			}
			else{
				echo '<li class="header"><a class="link" href="login.php">Profile</a></li>';
			}
		?>
    </ul>

	<script>
		//Send form inputs to php file to handle database actions
		function submitData(key, action){
			if (window.XMLHttpRequest) {
				xmlhttp = new XMLHttpRequest();
			} else {
				xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			}
			breed = document.getElementById("breed" + key).value;
			breed = breed.replace(/[^a-zA-Z]/gi, '').toLowerCase();
			album = document.getElementById("album" + key).value;
			album = album.toLowerCase();
			xmlhttp.open("get","handler.php?key=" + key + "&action=" + action + "&breed=" + breed + "&album=" + album,true);
			xmlhttp.send();
		}
	</script>

	<?php
		#Determine search value and sorting method
		if(isset($_GET["breed"])){
			$search = $_GET["breed"];
		}
		else{
			$search = '';
		}

		if(isset($_GET["sort"])){
			$sort = $_GET["sort"];
		}
		else{
			$sort = "highest";
		}

		if(isset($_GET["time"])){
			$time = $_GET["time"];
		}
		else{
			$time = '';
		}

		#Get time portion of query (default to 1 day)
		$time_query = " posted >= DATE_ADD(NOW(), INTERVAL -1 DAY) ";
		if(strcmp($time,"hour") == 0){
			$time_query = " posted >= DATE_ADD(NOW(), INTERVAL -1 HOUR) ";
		}
		else if(strcmp($time,"week") == 0){
			$time_query = " posted >= DATE_ADD(NOW(), INTERVAL -7 DAY) ";
		}


		#Base query
		$query = 'SELECT * FROM POST WHERE' . $time_query;
		#Get search parameter (if it exists)
		if(!empty($search)){
			#Strip Search paramter
			$search = preg_replace("/[^a-zA-Z]/", "", $search);
			$search = strtolower($search);
			#Determine breed of each post
			$query = "SELECT * 
					FROM Post
					WHERE" . $time_query . "
					AND postID IN(
						SELECT postID
						FROM BreedVote
						WHERE BreedVote.breed='" . $search . "'
						GROUP BY postID)";
		}

		#Set sorting method
		if(strcmp($sort, "newest") == 0 ){
			$query = $query .
					" ORDER BY posted DESC";
		}
		else if(strcmp($sort, "oldest") == 0 ){
			$query = $query .
					" ORDER BY posted ASC";
		}
		else if(strcmp($sort, "highest") == 0){
			$query = $query .
					" ORDER BY likes DESC";			
		}
		else if(strcmp($sort, "lowest") == 0){
			$query = $query . 
					" ORDER BY likes ASC";	
		}

		#Get images from database
		$val = $con->query($query);
		echo '<div class="register_error">' . $con->error . '</div>';
		$counter = 0;

		#Start table to display images
		echo '<table style="padding-top: 250px;">';
		echo '<tr>';
		#Make a table entry for each image
		while($entry = $val->fetch_assoc()){
			#Once 3 images are in a row, start a new row
			if($counter == 3){
				echo '</tr><tr>';
				$counter = 0;
			}
			echo '<td>';
				echo '<div class="image_post">';
					echo '<div>';
						echo '<a href="' . $entry["link"] .'">';
						echo '<img style="border: 3px solid #ffffff;" width=100% src="' . $entry["image_src"] . '" alt="Image" /></a>';
					echo '</div>';
					echo '<div class="button_box">';
						$key = $entry["postID"];
						echo '<button onclick="submitData(\'' . $key . '\',\'upvote\')">Like</button>';
						echo '<button onclick="submitData(\'' . $key . '\',\'favorite\')">Favorite</button>';
						echo '<button onclick="submitData(\'' . $key . '\',\'report\')">Report</button>';
						echo '<input id="album' . $key .'" type="text" name="album" placeholder="Add to or create album">';
						echo '<button onclick="submitData(\'' . $key . '\',\'album\')">Add</button>';
						echo '<input id="breed' . $key .'" type="text" name="breed" placeholder="What breed is this dog?">';
						echo '<button onclick="submitData(\'' . $key . '\',\'breed\')">Submit</button>';
					echo '</div>';
				echo '</div>';
			echo '</td>';
			++$counter;
		}
		echo '</tr></table>';
		if($val->num_rows == 0){
			echo '<div class="register_error">No results found</div>';
		}
	?>

</body>
</html>
