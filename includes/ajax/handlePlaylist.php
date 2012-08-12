<?php
	// Playlist handler
	// Handles all the ajax calls for Playlists
	require_once('../global.inc.php');

	if(!isset($_POST['task']))
		die("We need to know what we're doing.");
	else {
		switch($_POST['task']) {
			case 'get': // Get playlists
				if($playLists = $PlaylistApp->getPlaylists()) {
					while($row = mysql_fetch_assoc($playLists)) {
						$output[] = $row;
					}
					$output['count'] = $count = mysql_num_rows($playLists);
					echo json_encode($output);
				} else 
					die('0');
			break;
			case 'save': // Save a playlist
				$title = $PlaylistApp->checkValue($_POST['title'], 's', 50, 5);
				$description = $PlaylistApp->checkValue($_POST['description'], 's', 255, 10);

				$response = $PlaylistApp->addPlaylist($title, $description);

				if($response) {
					$lastAdded = $PlaylistApp->getPlaylist(mysql_insert_id());
					echo json_encode(mysql_fetch_assoc($lastAdded));
				} else
					die('0');
			break;
			case 'delete': // Delete a playlist
				$playlistID = $PlaylistApp->checkValue($_POST['playlistID'], 'n');
				
				if($PlaylistApp->deletePlaylist($playlistID)) 
					die('1');
				else
					die('0');
			break;
			case 'update': // Update a playlist
				$title = $PlaylistApp->checkValue($_POST['title'], 's', 50, 5);
				$description = $PlaylistApp->checkValue($_POST['description'], 's', 255, 10);
				$playlistID = $PlaylistApp->checkValue($_POST['playlistID'], 'n');

				if($PlaylistApp->updatePlaylist($title, $description, $playlistID))
					die('1');
				else
					die('0');
			break;
		}
	}

?>