<?php
	session_start();
	$page = $_SESSION["page"];
	session_destroy();
	header("Location: $page");
?>