/**
* Trigger notification
* Adds the given value to the notification element and displays it to the user.
*
* @param string text Text to display.
* @param int speed The speed at the element is displayed.
* @param int delay The time the element is displayed.
*
*/
function triggerNotification(text, speed, delay){
    if (speed == undefined||delay == undefined){
        var speed = 280;
        var delay = 2000;
    }
    $('#notification').html('<div id="innerContent">'+ text +'</div>');
    $('#notification').slideToggle(speed).delay(delay).slideToggle(speed);
}

/**
* Add playlist
* Adds a playlists to the database and displays it to the user.
*
* @param string title Playlist title.
* @param string description Playlist description.
* @param object playlistsDiv The HTML element that contains all the user playlists.
*
*/
function addPlaylist(title, description, playlistsDiv) {
  
    $.post(domain+'includes/ajax/handlePlaylist.php', 
    { 
        title: title, 
        description: description,
        task: 'save' 
    }, 
    function(response){
        if(response) {

            $("p").remove(":contains('Add some playlists!')");

            var basicInfo     = $('<div class="basicInfo"></div>');
            var title         = $('<div class="title">'+response.title.trunc(44)+'</div>');
            var description   = $('<div class="description">'+response.description.trunc(250)+'</div>');
            
            var tracksInfo    = $('<div class="playlistInfo"></div>');
            var trackCount    = $('<div class="trackCount">0</div>');
            var playAll       = $('<div class="playall">Play all</div>');
            var manage        = $('<div class="manage"><div class="edit"></div><div class="del"></div></div>');
            var clear         = $('<div class="clear"></div>');

            var wrapper       = $('<li data-playlistID="'+response.playlist_id+'"></li>');

            basicInfo.append(title).append(description);
            tracksInfo.append(trackCount).append(playAll);

            wrapper.append(basicInfo).append(tracksInfo).append(manage).append(clear);
            playlistsDiv.prepend(wrapper).hide().fadeIn(250);

            wrapper.droppable({
                hoverClass:'hovered',
                accept:'#tracks li',
                drop: addTrack
            });

            $('#add').delay(200).slideUp(200);
            $('#title').val('');
            $('#description').val('');

            triggerNotification("Playlist was added!");

        } else {
            triggerNotification("Playlist wasn't added!");
        } 
    },'json');

}

/**
* Delete playlist
* Deletes the given playlists and removes it from the playlist list.
*
* @param int playlistID The playlist ID
* @param object playlistRow The HTML element that contains the playlist data.
*
*/
function deletePlaylist(playlistID, playlistRow) {
  
    $.post(domain+'includes/ajax/handlePlaylist.php', 
    { 
        playlistID:playlistID,
        task: 'delete' 
    }, 
    function(response){
        if(response == 1) {
            triggerNotification("Playlist was deleted.");
            playlistRow.fadeOut(150).remove();
            
            if($('#playlists li').length == 0)
                $('#playlists').append("<p align='center'>Add some playlists!</p>").hide().fadeIn(150);
        } else {
            triggerNotification("Playlist wasn't deleted.");
        } 
    });

}

/**
* Update playlist
* Updates the given playlists.
*
* @param string title Playlist title
* @param description title Playlist description
* @param int playlistID The playlist ID
*
*/
function updatePlayist(title, description, playlistID) {
  
    $.post(domain+'includes/ajax/handlePlaylist.php', 
    { 
        title:title,
        description:description,
        playlistID:playlistID,
        task: 'update' 
    }, 
    function(response){
        if(response == 1) {
            var playlistRow = $('[data-playlistid="'+playlistID+'"]');
            playlistRow.find('.title').text(title);
            playlistRow.find('.description').text(description);
           
            triggerNotification("Playlist was updated.");
            playlistRow.removeClass('.hovered');

            $('#cancel').trigger('click');
        } else {
            triggerNotification("Playlist wasn't updated.");
        } 
    });

}

/**
* Get tracks
* Returns a set of tracks from SoundCloud and displays them to the user.
*
* @param string searchTerm Users search term.
* @param int limit The number of tracks being retrieved.
* @param object tracksDiv The HTML element that contains the tracks data.
* @param string ids IDs of tracks to display
* @param int playlistID The playlist ID to which the current track belongs too.
*
*/
function getTracks(searchTerm, limit, tracksDiv, ids, playlistID) {

    if(!ids) {
        var filter = {
            q : searchTerm,
            limit : limit
        }
    } else {
        var filter = {
            ids : ids
        }
    }

    SC.get('/tracks/', filter, function(tracks, error) {
        if(tracks) {
        tracksDiv.html('');

        $.each(tracks, function(key, track){

            var pictureUrl;
            var genre;
            var wrapper;

            if(track.artwork_url == null)
                pictureUrl = 'includes/imgs/no-image.jpg';
            else
                pictureUrl = track.artwork_url;

            if(track.genre == null)
                genre = "Undefined";
            else
                genre = track.genre.trunc(15);

            var title   = $('<span class="title">'+track.title.trunc(40)+'</span>');
            var stats   = $('<div class="stats">'+track.playback_count+' <img src="includes/imgs/plays.png"> '+track.comment_count+' <img src="includes/imgs/comments.png"> '+track.favoritings_count+' <img src="includes/imgs/favourites.png"> '+genre+' <img src="includes/imgs/gerne.png"></div>');
            var player  = $('<div></div>');
            var clear   = $('<div class="clear"></div>');
            var wrapper = $('<li class="trackClass" data-trackID="'+track.id+'"><img src="'+pictureUrl+'" border="0" alt="'+track.title+'" align="left" class="img"/></li>');
        
            wrapper.append(title).append(player).append(stats).append(clear);
            if(playlistID) {
                var remove = $('<div class="removeTrack" data-playlistID="'+playlistID+'"></div>');
                wrapper.append(remove);
            }
            tracksDiv.append(wrapper);

            player.scPlayer({
                links: [{url: track.permalink_url, title: track.title}]
            });

            wrapper.draggable({
                stack: '#tracks li',
                revert: true,
                cursor: 'move',
                containment: '#content'
            });
        });

        if(error)
            tracksDiv.html('<p align="center">No tracks found.</p>');

        } else {
            console.log("The get statement must be wrong.");
            //triggerNotification(error);
        }

    });
}

/**
* Get playlist tracks
* Returns a set of tracks from SoundCloud for a specific playlist.
*
* @param int playlistID The selected playlist ID
*
*/
function getPlaylistTracks(playlistID) {
    $.post(domain+'includes/ajax/handleTrack.php',  
    { 
        playlistID: playlistID,
        task:'get'
    }, 
    function(response) {
        
        if(response) {
            var ids = "";
           
            $.each(response, function(key, track) {

                if(key+1 != response.length)
                    ids += track.track_id + ", ";
                else 
                    ids += track.track_id;
            });

            getTracks("", "", tracksDiv, ids, playlistID);
        }
        else {
            triggerNotification("No tracks were found!");
            var playlist = $('[data-playlistid="'+playlistID+'"]');

        }
    }, 'json');
}

/**
* Add track
* Adds a given track to a given playlist. Handles the drop event, by getting the droped track ID and setting it to the selected playlist.
*
* @param object event The event object
* @param object ui The draggable object ( the track )
*
*/
function addTrack(event, ui) {

    var trackID = [ui.draggable.attr('data-trackID')];
    var playlist = $(this);
    var currentCount = parseInt(playlist.find('.trackCount').text());

    ui.draggable.fadeOut(100);
    $('body').css('cursor', 'auto');
    
    $.post(domain+'includes/ajax/handleTrack.php',  
    { 
        trackID: trackID,
        playlistID: playlist.attr('data-playlistid'),
        task:'save'
    }, 
    function(response) {
        if(response == 1) { 
            playlist.find('.trackCount').text(currentCount+1);
            $('.hovered').removeClass('hovered'); 
           
            ui.draggable.draggable( 'option', 'revert', false );
            ui.draggable.draggable( 'disabled');
            ui.draggable.remove();

            triggerNotification("Track was successfully added.")
        }
        else {
            triggerNotification("The track is already in there.");
            ui.draggable.data('hasBeenDropped', true);
            ui.draggable.fadeIn(100)
        }
    });
             
}

/**
* Delete track
* Removes the given track from a playlist set.
*
* @param int trackID
* @param int playlistID
*
*/
function deleteTrack(trackID, playlistID) {
    var track = $(this).parent();
    var playlist = $('[data-playlistid="'+playlistID+'"]');
    var currentCount = parseInt(playlist.find('.trackCount').text());

    $.post(domain+'includes/ajax/handleTrack.php',  
    { 
        trackID: trackID,
        playlistID: playlistID,
        task:'delete'
    }, 
    function(response) {

        if(response == 1) {
            playlist.find('.trackCount').text(currentCount-1);
            triggerNotification("Track removed.");
            track.slideUp(250).delay(250).remove();
        }
        else
            triggerNotification("Track wasn't removed.");

    });
}

/**
* Trunc
* Truncates a given string (Found this function in some StackOverflow debate.)
*
*/
String.prototype.trunc = 
function(n){
    return this.substr(0,n-1)+(this.length>n?'&hellip;':'');
};