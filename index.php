<?php
	require_once('includes/global.inc.php');

	// Retrieves a list of playlists for the current user.
	$userPlaylists = $PlaylistApp->getPlaylists();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
  <head>
  	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  	<title>PlaylistApp</title>
  	<link href="http://a1.sndcdn.com/favicon.ico?12d1f45" rel="shortcut icon" />
	<link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700,200' rel='stylesheet' type='text/css'>
	<link href="includes/css/sc-player-minimal.css" rel="stylesheet" type="text/css" />
	<link href='includes/css/css.css' rel='stylesheet' type='text/css'>
  </head>
  <body>
    <div id="wrapper">
	    <div id="head">
	    	<a href="http://www.soundcloud.com/logout" id="povezi" ><img src="includes/imgs/btn-disconnect-l.png" border="0"/> </a>
	    	<div id="notification"></div>
	    	<a href="javascript:var%20songs%20%3D%20document.querySelectorAll%28%27.mode%27%29%3B%20var%20link%3B%20for%28%20var%20i%20%3D%200%3B%20i%20%3C%20songs.length%3B%20i++%20%29%20%7B%20var%20link%20%3D%20document.createElement%28%27a%27%29%3B%20link.style.marginLeft%20%3D%20%278px%27%3B%20link.innerHTML%20%3D%20%27Add%20to%20playlist%27%3B%20link.setAttribute%28%27target%27%2C%27_blank%27%29%3B%20link.href%20%3D%20%27http%3A//soundcloud.mediatech.si/playlistapp/%3FtrackID%3D%27+%20songs%5Bi%5D.getAttribute%28%27data-sc-track%27%29%3B%20songs%5Bi%5D.querySelector%28%27.primary%27%29.appendChild%28link%29%3B%20void%280%29%3B%7D" id="bookmark" title="Add to playlist" />
	    		<img src="includes/imgs/bookmark.png" border="0">Add to playlist
	    	</a>
	    	<div class="clear"></div>
		    <p><input type="text" placeholder="Find some sound.." id="vnos" /></p>
		</div>
		<div id="content">
		    <div id="trackList">
			    <ul id="tracks">
			    	<p>Search for tracks and drag them to your playlist. <br />It couldn't be easier!<img src="includes/imgs/drag.png" border="0" align="right" id="drag"></p>
			    </ul>
			</div>
			<div id="playList">
				<div id="playlistsWrapper">
					<div id="add">
						<div class="heading">Add playlist</div>
						<input id="title" placeholder="Title.." />
						<textarea id="description" placeholder="Description.." ></textarea>
						<input type="hidden" id="edit" value="0"/>
						<p><a href="#" id="save">Save</a> &nbsp; <a href="#" id="cancel">Cancel</a></p>
					</div>
					<div id="actions">
						<span>Your playlists</span>
						<a class="next">&nbsp;</a>
						<a class="prev">&nbsp;</a>
					</div>
					<span id="playlistCount"></span>
					<div id="btns">
						<a href="#" id="addPlaylist"><img src="includes/imgs/add.png" alt="Add" width="25" border="0" /> Add</a>
					</div>
					<div class="clear"></div>
					<div class="scrollable vertical">
						<ul id="playlists">
						<?php 
						if($userPlaylists) {
						// We print out the current playlists that the user has.
						while($playlist = mysql_fetch_assoc($userPlaylists)) { ?>
							<li data-playlistid="<?php echo $playlist['playlist_id'] ?>">
								<div class="basicInfo">
									<div class="title"><?php echo $playlist['title'] ?></div>
									<div class="description"><?php echo $playlist['description'] ?></div>
								</div>
								<div class="playlistInfo">
									<div class="trackCount"><?php echo $playlist['count'] ?></div>
									<div class="playall">Play all</div>
								</div>
								<div class="manage">
									<div class="edit"></div>
									<div class="del"></div>
								</div>
								<div class="clear"></div>
							</li>
						<?php }
						} else {
							echo "<p align='center'>Add some playlists!</p>";
						}
						 ?>
						</ul>
					</div>
				</div>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<script type="text/javascript">
		/* We transfer some variables to javascript for later usage */
		var domain = '<?php echo $domain; ?>';
		var clientID = '<?php echo $clientID; ?>';
	</script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>
    <script type="text/javascript" src="includes/js/jquery.tools.min.js"></script>
	<script type="text/javascript" src="http://connect.soundcloud.com/sdk.js"></script>
	<script type="text/javascript" src="includes/js/soundcloud.player.api.js"></script>
	<script type="text/javascript" src="includes/js/sc-player.js"></script>
	<script type="text/javascript" src="includes/js/functions.js"></script>
	<script type="text/javascript" src="includes/js/init.js"></script>
	<script type="text/javascript">
		/* Dynamic */
		<?php 
		if(isset($_REQUEST['notification'])) { 
			// If set, we display the notification value to the user.	
		?>
			triggerNotification("<?php echo $_REQUEST['notification']; ?>");
		<?php } ?> 
		<?php 
		if(isset($_SESSION['bookmarketID'])) { 
			// If set, we display the bookmarked track to the user, se he can select a playlist to add it to
			?>
			SC.initialize({
				client_id: clientID,
				redirect_uri: domain
			});
			
			getTracks("", "", tracksDiv, <?php echo $_SESSION['bookmarketID']; ?>);
			triggerNotification("Please select a playlist.", 280, 3000);
		
		<?php unset($_SESSION['bookmarketID']); } ?>
	</script> 
  </body>
</html>