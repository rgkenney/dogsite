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

		#Check if user table exists
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
        <li class="header"><a class="link" href="images.php">Images</a></li>
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

    <form class="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
            <div>
                <label for="image_src">Image file (Link):</label><br>
				<input type="text" placeholder="Link" name="image_link"><br>
            </div>
            <div class="field">
                <input class="button" type="submit" value="Upload" />
            </div>
			<br><br>

	</form>
	<?php
		if($_SERVER["REQUEST_METHOD"] == "POST"){
			#error_reporting(E_ERROR | E_PARSE);

			$link = $_POST["image_link"];

			#Check if image is provided
			if(empty($link)){
					echo '<div class="register_error"> Please provide a link to a post </div>';
			}
			else{
				#Append beginning portion if needed
				if(preg_match('/reddit.*/', $link, $matches) == 1){
					$link = 'https://www.' . $matches[0];
				}
				if(preg_match('/imgur.*/', $link, $matches) == 1){
					$link = 'https://' . $matches[0];
				}

				#Regex to determine if the site matches
				$regex = '/((reddit.com\/r\/)(.+)(\/comments\/)(.+)(\/)(.+)$)|((imgur.com\/gallery\/)(.+)$)/';
				if(preg_match($regex, $link, $matches) == 1){
					$page = curl_init($link);
					curl_setopt($page, CURLOPT_RETURNTRANSFER, 1);
					$result = curl_exec($page);

					#Look for post image source
					$reddit_regex ='/(<img alt="Post image")(.*?)(src=")(.*?)(")(.*?)(>)/';
					$imgur_regex = '/(link rel="image_src" href=")(.*?)(")/';

					$img_src = '';
					if(preg_match($reddit_regex, $result, $matches) == 1){
						#$matches[4] gets the image source
						$img_src = $matches[4];
					}
					elseif(preg_match($imgur_regex, $result, $matches) == 1){
						#$matches[4] gets the image source
						$img_src = $matches[2];
					}
					else{
						echo '<div class="register_error">Image not found</div>';
					}

					if(!empty($img_src)){
						#Check if database already has link

						$query = "SELECT *
								FROM Post
								WHERE link='"
								. $link . "'";
						$val = $con->query($query);
						if($val->num_rows == 0){
							#Links to upload; start with initial link
							$links = array();
							array_push($links, $link);

							#Create unique key
							$key = md5(microtime().rand());
							$query = "SELECT *
									FROM Post
									WHERE postID='"
									. $key . "'";
							while(($con->query($query))->num_rows != 0){
								$key = md5(microtime().rand());
								$query = "SELECT *
										FROM Post
										WHERE postID='"
										. $key . "'";
							}
							#Upload post
							$query = "INSERT INTO Post
									(postID, username, link, image_src, posted, likes, favorites, reports)
									VALUES ('" . $key . "', '" . $username . "', '" . $link . "', '" . $img_src .
									"', NOW(), 0, 0, 0)";
							$con->query($query);

							header("Location: images.php");
							#If imgur post, end here
							if(preg_match($imgur_regex, $result, $matches) == 1){
								die();
							}

							#Crawl subreddit for more images
							ini_set('max_execution_time', 60); #Do it for 1 minute max
							$regex = '/https:\/\/www\.reddit.com\/r\/.+?\//';
							preg_match($regex, $link, $matches);
							$newlink = $matches[0];
							echo '<div class="register_error">' . $newlink .'</div>';
							$regex = '/"permalink":"([^=]+?)"/';
							$page = curl_init($newlink);
							curl_setopt($page, CURLOPT_RETURNTRANSFER, 1);
							$result = curl_exec($page);
							preg_match_all($regex, $result, $matches, PREG_PATTERN_ORDER);

							#Check each found link's title and comments to see if it fits
							foreach($matches[1] as $val){
								$page = curl_init($val);
								curl_setopt($page, CURLOPT_RETURNTRANSFER, 1);
								$result = curl_exec($page);
								#Check title
								$add = FALSE;
								if(preg_match('/dog|pup|golden|boy|woof|bark|breed/', $val, $newmatch) == 1){
									$add = TRUE;
								}
								else{
									#Go further and check post comments
									if(preg_match('/"t":"(.*?)(dog|pup|golden| boy |woof|bark|breed)(.*?)"/', $result, $newmatch) == 1){
										$add = TRUE;
									}
								}
								#Post image if valid
								if($add){
									echo '<div class="register_error">' . $val . '</div>';
									if(preg_match($reddit_regex, $result, $matches) == 1){
										#$matches[4] gets the image source
										$img_src = $matches[4];

										#Create unique key
										$key = md5(microtime().rand());
										$query = "SELECT *
											FROM Post
											WHERE postID='"
											. $key . "'";
										while(($con->query($query))->num_rows != 0){
											$key = md5(microtime().rand());
											$query = "SELECT *
													FROM Post
													WHERE postID='"
													. $key . "'";
										}

										#Check if link already exists
										$query = "SELECT *
											FROM Post
											WHERE link='"
											. $val . "'";
										$newval = $con->query($query);
										if($newval->num_rows == 0){
											#Upload post
											$query = "INSERT INTO Post
													(postID, username, link, image_src, posted, likes, favorites, reports)
													VALUES ('" . $key . "', '" . $username . "', '" . $val . "', '" . $img_src .
													"', NOW(), 0, 0, 0)";
											$con->query($query);
										}
									}
								}
							}
							$con->close();
						}
						else{
							echo '<div class="register_error">Post already exists</div>';
						}
					}
				}
				else{
					echo '<div class="register_error"> Invalid link (Must be reddit or imgur post link)</div>';
				}
				$con->close();
			}

			$con->close();
		}
	?>
</body>
</html>
