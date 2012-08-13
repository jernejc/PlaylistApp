<?php
	session_start();

	$absPath = "/home/mediat/public_html/soundcloud/playlistapp/";

	// We include the config file and all the neceseary classes
	require_once($absPath.'config.php');
	require_once($absPath.'includes/classes/Soundcloud.php');
	require_once($absPath.'includes/classes/PlaylistApp.php');

	// DB stuff..
	$connection = mysql_connect($dbHost, $dbUser, $dbPassword);
	if (!$connection) {
		die('Could not connect: ' . mysql_error());
	}
	mysql_select_db($dbName);

	// We check the trackID variable for the bookmarklet feature
	// If it's set, we store it in a session variable so we doesn't loose the value 
	if(isset($_REQUEST['trackID'])) {
		$_SESSION['bookmarketID'] = $_GET['trackID'];
		unset($_REQUEST['trackID']);
	}

	// We begin the Soundcloud authetication by creating an instance of the Soundcloud class
	$client = new Services_Soundcloud($clientID,$clientSecret,$domain);

	// If we don't have the access token in the session, or the code variable to retrieve it, we redirect the user to SoundCloud for authetication
	if(!isset($_GET['code']) and !isset($_SESSION['token'])) {
		header("Location: " . $client->getAuthorizeUrl());
		exit();
	} else {
		// If the code variable is set, we use it to retrieve the access token, otherwise we set the token by getting it from the session
		if(isset($_GET['code']) and !isset($_SESSION['token'])) {
			$code = $_GET['code'];
			$_SESSION['token'] = $client->accessToken($code);
		} else {
			$client->setAccessToken($_SESSION['token']['access_token']);
		}
	}

	// We get the user data and store them in the session for later usage (if not already previously done already)
	if(!isset($_SESSION['user'])) {
		try {
		    $curUser = json_decode($client->get('me'), true);
		} catch (Services_Soundcloud_Invalid_Http_Response_Code_Exception $e) {
		    exit($e->getMessage());
		}

		$_SESSION['user'] = $curUser;

		//var_dump($_SESSION['user']);
	}

	// We initialize the main class
	$PlaylistApp = new PlaylistApp();

	// Check if the have the current user in the database. If we don't, we add him.
	if(!$PlaylistApp->getUser()) {
		$PlaylistApp->insertUser($_SESSION['token']['access_token']);
	}
?>