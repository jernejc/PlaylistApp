<?php
	// Tracks handler
	// Handles all the ajax call for tracks
	require_once('../global.inc.php');

	$data = array();

	if(!isset($_POST['task']))
		die("We need to know what we're doing.");
	else {
		switch($_POST['task']) {
			case 'get': // Getting tracks
				$playlistID = $PlaylistApp->checkValue($_POST['playlistID'], 'n');

				if($tracks = $PlaylistApp->getTracks($playlistID)) {
					while($row = mysql_fetch_assoc($tracks)) {
						$output[] = $row;
					}
					echo json_encode($output);
				} else 
					die('0');
			break;
			case 'save': // Saving a track
				$trackID = $PlaylistApp->checkValue($_POST['trackID'][0], 'n');
				$playlistID = $PlaylistApp->checkValue($_POST['playlistID'], 'n');

				if($PlaylistApp->insertTrack($trackID, $playlistID)) {
					$data['playlist_id'] = $playlistID;
					$data['track_id'] = $trackID;

					$PlaylistApp->insertAction(1, json_encode($data));

					die('1');
				}
				else
					die('0');
			break;
			case 'delete': // Deleting a track
				$playlistID = $PlaylistApp->checkValue($_POST['playlistID'], 'n');
				$trackID = $PlaylistApp->checkValue($_POST['trackID'], 'n');
				
				if($PlaylistApp->deleteTrack($trackID, $playlistID)) {
					$data['playlist_id'] = $playlistID;
					$data['track_id'] = $trackID;

					$PlaylistApp->insertAction(2, json_encode($data));

					die('1');
				}
				else
					die('0');
			break;
		}
	}

?>