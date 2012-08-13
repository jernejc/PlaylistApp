<?php

/**
 * The main application class. Contains all the major methods for managing playlists, tracks and users.
 *
 * @author    Jernej Čop <j@eee.si>
 * @copyright 2012 Jernej Čop <j@eee.si>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://github.com/mptre/php-soundcloud
 */

class PlaylistApp {

	/**
	* User ID is fetched from the session in the construct function for later usage.
	*
	* @var int
	* @access private
	*/
	private $userID;


	/**
	* Class constructor
	* We set the $userID variable with the user ID from the session.
	*
	* @return void
	* @access public
	*/
	function __construct() {
        $this->userID = $_SESSION['user']['id'];
    }

	
	/**
	* Get playlists
	* Retrieves all the playlists for the current user, with delete flag 0.
	*
	* @return mixed
	* @access public
	*/
	public function getPlaylists() {
		$sql = "SELECT p.*, (SELECT COUNT(*) FROM `tracks_playlists` WHERE `playlist_id` = p.`playlist_id`) AS count FROM `playlists` as p WHERE `user_id` = $this->userID AND p.`deleted` <> 1";
		return $this->excecuteQuery($sql);
	}

	/**
	* Get playlist
	* Retrieves a specific playlists for the given ID.
	*
	* @param int $playlistID The wanted playlist ID.
	*
	* @return mixed
	* @access public
	*/
	public function getPlaylist($playlistID) {
		$sql = "SELECT * FROM `playlists` WHERE `playlist_id` = $playlistID LIMIT 1";
		return $this->excecuteQuery($sql);
	}

	/**
	* Add playlist
	* Adds a new playlist for the current user
	*
	* @param string $title Playlist title.
	* @param string $description Playlist description.
	*
	* @return boolean
	* @access public
	*/
	public function addPlaylist($title, $description) {
		$sql = "INSERT INTO `playlists` (`title`, `description`, `user_id` ) VALUES ( '$title', '$description', $this->userID )";		
		
		$result = mysql_query($sql);

		if($result != FALSE)
			return $result;
		else
			return FALSE;
	}

	/**
	* Update playlist
	* Updates an existing playlists with the new title and description.
	*
	* @param string $title Playlist title.
	* @param string $description Playlist description.
	* @param int $playlistID Playlist ID.
	*
	* @return boolean
	* @access public
	*/
	public function updatePlaylist($title, $description, $playlistID) {
		$sql = "UPDATE `playlists` SET `title` = '$title', `description` = '$description' WHERE `playlist_id` = $playlistID";
		$result = mysql_query($sql);

		if($result != FALSE)
			return $result;
		else
			return FALSE;
	}
	/**
	* Delete playlist
	* Deletes a given playlist, by settings it's delete flag to 1.
	*
	* @param int $playlistID Playlist ID.
	*
	* @return boolean
	* @access public
	*/
	public function deletePlaylist($playlistID) {
		$sql = "UPDATE `playlists` SET `deleted` = 1 WHERE `playlist_id` = $playlistID";
		
		$result = mysql_query($sql);

		if($result != FALSE)
			return $result;
		else
			return FALSE;
	}

	/**
	* Get tracks
	* Returns a set of tracks for a given playlist ID.
	*
	* @param int $playlistID Playlist ID.
	*
	* @return mixed
	* @access public
	*/
	public function getTracks($playlistID) {
		$sql = "SELECT `track_id` FROM `tracks_playlists` WHERE `playlist_id` = $playlistID";
		return $this->excecuteQuery($sql);
	}

	/**
	* Insert track
	* Inserts a new track into the database
	*
	* @param int $playlistID Playlist ID.
	*
	* @return boolean
	* @access public
	*/
	public function insertTrack($trackID, $playlistID) {
		$sql = "INSERT INTO `tracks_playlists` (`track_id`, `playlist_id`) VALUES ($trackID, $playlistID)";
		$result = mysql_query($sql);
		
		if($result != FALSE)
			return $result;
		else
			return FALSE;
	}

	/**
	* Remove track
	* Removes a track from a playlist
	*
	* @param int $trackID Track ID.
	* @param int $playlistID Playlist ID.
	*
	* @return boolean
	* @access public
	*/
	public function deleteTrack($trackID, $playlistID) {
		$sql = "DELETE FROM `tracks_playlists` WHERE `track_id` = $trackID AND `playlist_id` = $playlistID";
		$result = mysql_query($sql);

		if($result != FALSE)
			return $result;
		else
			return FALSE;
	}

	/**
	* Insert user
	* Inserts the current user in the database. We save the access token as well, it might come in handy sometime in the future.
	*
	* @param string $accessToken Access token.
	*
	* @return boolean
	* @access public
	*/
	public function insertUser($accessToken) {
		$sql = "INSERT INTO `users` (`user_id`, `access_token`) VALUES ($this->userID, '$accessToken')";
		return $this->excecuteQuery($sql);
	}

	/**
	* Get user
	* Performs a database check if a user with the current ID exists.
	*
	* @return mixed
	* @access public
	*/
	public function getUser() {
		$sql = "SELECT * FROM `users` WHERE `user_id` = $this->userID LIMIT 1";
		return $this->excecuteQuery($sql);
	}

	/**
	* Check value
	* Checks the given value if it fits the rest of the parameters ( string / numeric, length etc.)
	*
	* @param string $value The value that is checked.
	* @param string $type What type should the value be (string or numeric).
	* @param int $maxLength Max length of the value.
	* @param int $minLength Min length of the value.
	*
	* @return string
	* @access public
	*/
	public function checkValue($value, $type, $maxLength = "", $minLength = "") {
		
		if(!empty($maxLength) or !empty($minLength)) {
			if(strlen($value) > $maxLength) 
				die("Value to long");
			if(strlen($value) < $minLength) 
				die("Value to short");
		}

		switch ($type) {
			case 'n': 	// Numeric
				if(is_numeric($value)) {
					return $value;
				}
				else
					die("Please provide a numeric value.".var_dump($value));
			break;
			case 's': // String
				return strip_tags(mysql_real_escape_string(trim($value)));
			break;
		}
	}

	/**
	* Excecute query
	* Excecutes a given mysql query.
	*
	* @param string $sql The SQL statement to be excecuted.
	*
	* @return mixed
	* @access public
	*/
	private function excecuteQuery($sql) {
		$result = mysql_query($sql);
		$rowCount = mysql_num_rows($result);

		if($rowCount != 0)
			return $result;
		else
			return FALSE;
	}
}


?>