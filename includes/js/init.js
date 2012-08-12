/**
	Javascrpit "init" file.
	Here we set up all the different javascript events, that set of different ajax calls to the hanlder, or just simply display some data to the user.
*/

var listLength = 30;
var tracksDiv = $('#tracks');
var playlistsDiv = $('#playlists');
var editForm = $('#add');

$.scPlayer.defaults.apiKey = clientID;

$(document).ready(function() {

	SC.initialize({
	    client_id: clientID,
	    redirect_uri: domain
	  });

	/** Scrollable list of playlists **/

	$(".scrollable").scrollable({ vertical: true, mousewheel: true }); // If the list of playlists is longer then 5, we make it "scrollable" for a better user experience

	var scrollable = $(".scrollable").data("scrollable");
	if(scrollable.getSize() < 4) {
		$('.next').addClass('disabled');
	}
	var size = 5;

	scrollable.onSeek(function(event, index) {

		if (this.getIndex() >= this.getSize() - size) {
			$("a.next").addClass("disabled");
		}

	});

	scrollable.onBeforeSeek(function(event, index) {

		if (this.getIndex() >= this.getSize() - size) {
			if (index > this.getIndex()) {
				return false;
			}
		}

	});

	/** Main search input **/

	$('#vnos').keyup(function(e){ // On every "keyup" we perform a track search on SoundCloud with the given search term.

		$.scPlayer.defaults.autoPlay = false;
		$('#playlists li').removeClass('hovered');

		clearTimeout($.data(this, 'timer'));
		var searchTerm = $('#vnos').val();

		if(searchTerm.length > 1) {
			tracksDiv.html(' ');
			$(this).data('timer', setTimeout(function(){getTracks(searchTerm, listLength, tracksDiv)}, 250));
		}

	});

	/** 
		Edit and Add form events 
	**/

	$('#addPlaylist').click(function(e){ // We open the add new playlist form to the user, if he clicks the "Add playlist" button
    	editForm.slideToggle(200);
    	editForm.find('#edit').val('0');
    	e.preventDefault();
    });

	$('#save').click(function(e){ // If the users saves the playlist form, we send the data to the handler
		var title = $('#title').val();
		var description = $('#description').val();
		var editPlaylistID = editForm.find('#edit').val();

		if(title.length < 4) {
			triggerNotification('Title to short!');
			return false
		}

		if(description.length < 11) {
			triggerNotification('Description to short!');
			return false
		}

		if(editPlaylistID == 0) // We check if the playlist ID is set, otheriwse we insert a new one
			addPlaylist(title,description,playlistsDiv);
		else
			updatePlayist(title,description,editPlaylistID);

		scrollable.begin(200);

		e.preventDefault();
	});

    $('#cancel').click(function(e){ // We close the form and remove all the values, if he clicks "Cancel"
    	editForm.slideUp(200);
    	editForm.find('#title').val(' ');
    	editForm.find('#description').val(' ');
    	editForm.find('.heading').text('Add playlist');
    	editForm.find('#edit').val('0');

    	$('#playlists li').removeClass('hovered');

    	e.preventDefault();
    });

    /**
		Playlist container events 
    **/

    $('#playlists').on("click", ".del", function(e){ // When a user clicks the delete icon next to each playlist, we remove it and set the delete flag to 1.
    	
    	var answer = confirm("Are you sure?")
    	if (answer){
			var playlistRow = $(this).parent().parent();
			var playlistID = playlistRow.attr('data-playlistid');
			
			deletePlaylist(playlistID, playlistRow);
		}

		//getTracks(searchTerm, 10, tracksDiv);
		
		e.preventDefault();

	}).on("click", ".edit", function(e){ // When a user clicks the edit icon next to each playlist, we display the edit form.

    	var playlistRow = $(this).parent().parent();
		var playlistID = playlistRow.attr('data-playlistid');
		var editForm = $('#add');

		//Reset the form data
		editForm.find('#title').val('');
		editForm.find('#description').val('');
		editForm.find('#edit').val('0');

		// Set the active status, so the user will know which playlist he's currently editing.
		$('#playlists li').removeClass('hovered');
		playlistRow.addClass('hovered');

		// We collect the current playlist data and apply it to the form
		title = playlistRow.find('.title').text();
		description = playlistRow.find('.description').html();

		editForm.find('#title').val(title);
		editForm.find('#description').val(description);
		editForm.find('#edit').val(playlistID);
		editForm.find('.heading').text('Editing: '+title);

		editForm.slideDown(200);

		e.preventDefault();

	}).on("hover", "li", function(e){ // On playlist hover, we display the edit and delete icon.

    	$(this).children('.manage').fadeToggle(50);

    }).on("click", ".playall", function(){ // The play all button loads the tracks from the playlist and automatically stars playing them.
    	$.scPlayer.defaults.autoPlay = true;
    	playlist = $(this).parent().parent();

    	playlistID = playlist.attr('data-playlistid');
    	$('#playlists li').removeClass('hovered');
    	playlist.addClass('hovered');

    	// Load tracks for the selected playlist
    	getPlaylistTracks(playlistID);
    });

    $('#playlists li').droppable({ // Sets the current list of playlists as droppable objects, that accept only tracks elements (#tracks li)
        hoverClass:'hovered',
        accept:'#tracks li',
        drop: addTrack
    });

    /** 
		Track container events
    **/

    $('#tracks').on("hover", "li", function(e) { // On hover we display the remove track icon, if its in the current playlist.
    	
    	$(this).children('.removeTrack').fadeToggle(50);
    
    }).on("click", ".removeTrack", function(e) { // On remove track icon click, we remove the given track from the DOM and the database as well.
    	
    	trackID = $(this).parent().attr('data-trackid');
    	playlistID = $(this).attr('data-playlistid');
    	
    	deleteTrack(trackID,playlistID);
    	$(this).parent().slideUp(200).delay(150).remove();

    });

    $(document).bind('soundcloud:onMediaEnd', function(event, data) { // When the SoundCloud player finishes playing the current track, we find the next one and play it for the user.
		
		var prevId   = data.mediaId;
		var track = $('[data-trackid="'+prevId+'"]');
		var next = track.next().find('a.sc-play');
		
		if(next)
			next.trigger('click');

	});

    // Bookmarklet

    $('#bookmark').click(function(){ // We display a short notification to the user, if he clicks the boorkmarklet icon.
    	triggerNotification("Drag me to your bookmars.")
    });
	
	//console.log('javascript:' + escape("var songs = document.querySelectorAll('.player'); var link; for( var i = 0; i < songs.length; i++ ) { var link = document.createElement('a'); link.style.marginLeft = '8px'; link.innerHTML = 'Add to playlist'; link.setAttribute('target','_blank'); link.href = 'http://soundcloud.mediatech.si/playlistapp/?trackID='+ songs[i].getAttribute('data-sc-track'); songs[i].querySelector('.primary').appendChild(link); void(0);}"));

});