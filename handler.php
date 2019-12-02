<?php
	session_start();
	$postID = $_GET['key'];
	$username = $_SESSION['username'];
	$action = $_GET['action'];
	$breed = $_GET['breed'];
	$album= $_GET['album'];

	#Do nothing if not logged in
	if(empty($username)){
		die("Not logged in");
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

	#Check for action; check table and insert for action
	if(strcmp($action,"upvote") == 0){

		if(!$con->query("DESCRIBE 'Liked'")){
			$query = "
				CREATE TABLE Liked (
				postID char(32),
				username char(20),
				PRIMARY KEY(postID, username)
				)
			";
			$con->query($query);
		}

		$query = "INSERT INTO Liked
			(postID, username)
			VALUES ('" . $postID . "', '" . $username . "')";

		#If new like, add to post like count
		if($con->query($query)){
			$query = "UPDATE Post
				SET likes=likes+1
				WHERE postID='" . $postID . "'";
			$con->query($query);
		}
	}
	if(strcmp($action,"favorite") == 0){

		if(!$con->query("DESCRIBE 'Favorited'")){
			$query = "
				CREATE TABLE Favorited (
				postID char(32),
				username char(20),
				PRIMARY KEY(postID, username)
				)
			";
			$con->query($query);
		}

		$query = "INSERT INTO Favorited
			(postID, username)
			VALUES ('" . $postID . "', '" . $username . "')";
		$con->query($query);
	}
	if(strcmp($action,"report") == 0){

		if(!$con->query("DESCRIBE 'Reported'")){
			$query = "
				CREATE TABLE Reported (
				postID char(32),
				username char(20),
				PRIMARY KEY(postID, username)
				)
			";
			$con->query($query);
		}

		$query = "INSERT INTO Reported
			(postID, username)
			VALUES ('" . $postID . "', '" . $username . "')";
		$con->query($query);

		#Check if report threshold is met; if so delete post and related entries
		$REPORT_THRESHOLD = 1;
		$query = "SELECT *
				FROM Reported
				WHERE postID='" . $postID . "'";
		$val = $con->query($query);
		if($val->num_rows == $REPORT_THRESHOLD){
			$query = "DELETE FROM Post WHERE postID='" . $postID . "'";
			$con->query($query);
			$query = "DELETE FROM Liked WHERE postID='" . $postID . "'";
			$con->query($query);
			$query = "DELETE FROM Favorited WHERE postID='" . $postID . "'";
			$con->query($query);
			$query = "DELETE FROM Reported WHERE postID='" . $postID . "'";
			$con->query($query);
			$query = "DELETE FROM BreedVote WHERE postID='" . $postID . "'";
			$con->query($query);
			$query = "DELETE FROM inAlbum WHERE postID='" . $postID . "'";
			$con->query($query);
		}
	}
	if((strcmp($action,"breed") == 0) && !empty($breed)){
		if(!$con->query("DESCRIBE 'BreedVote'")){
			$query = "
				CREATE TABLE BreedVote (
				postID char(32),
				username char(20),
				breed varchar(30),
				PRIMARY KEY(postID, username)
				)";
			$con->query($query);
		}
		#Update or insert vote
		$query = "SELECT *
				FROM BreedVote
				WHERE postID='" . $postID . "' AND 
				username='" . $username . "'";
		$val = $con->query($query);
		if($val->num_rows == 0){
			$query = "INSERT INTO BreedVote
				(postID, username, breed)
				VALUES ('" . $postID . "', '" . $username . "', '" . $breed ."')";
			$con->query($query);
		}
		else{
			$query = "UPDATE BreedVote
				SET breed='" . $breed . "' 
				WHERE postID='" . $postID . "' AND 
				username='" . $username . "'";
			$con->query($query);
		}
	}
	if((strcmp($action,"album") == 0) && !empty($album)){

		#Create required tables if needed
		if(!$con->query("DESCRIBE 'Album'")){
			$query = "
				CREATE TABLE Album (
				albumID char(32) PRIMARY KEY,
				username char(20),
				name varchar(30),
				created DATETIME
				)";
			$con->query($query);
		}
		if(!$con->query("DESCRIBE 'inAlbum'")){
			$query = "
				CREATE TABLE inAlbum (
				postID char(32),
				albumID char(32),
				PRIMARY KEY(postID, albumID)
				)";
			$con->query($query);
		}

		#Check if album already exists for username
		$query = "SELECT *
				FROM Album
				WHERE username='" . $username . "'
				AND name='" . $album . "'";
		$var = $con->query($query);

		#Create album if it doesn't exist
		if($var->num_rows == 0){
									
			#Create unique key
			$key = md5(microtime().rand());
			$query = "SELECT *
					FROM Album
					WHERE albumID='"
					. $key . "'";
			while(($con->query($query))->num_rows != 0){
				$key = md5(microtime().rand());
				$query = "SELECT *
						FROM Album
						WHERE albumID='"
						. $key . "'";
			}

			$query = "INSERT INTO Album
			(albumID, username, name, created)
			VALUES ('" . $key . "', '" . $username . "'
			, '" . $album . "', NOW())";
			$con->query($query);
		}
		#Get key for album name
		$query = "SELECT *
				FROM Album
				WHERE username='" . $username . "' 
				AND name='" . $album . "'";
		$val = $con->query($query);
		while($entry = $val->fetch_assoc()){
			$key = $entry["albumID"];
		}


		$query = "INSERT INTO inAlbum
			(postID, albumID)
			VALUES ('" . $postID . "', '" . $key . "')";
		$con->query($query);
	}
	$con->close();
?>