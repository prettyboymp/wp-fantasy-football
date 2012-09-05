jQuery(function($) {
	$("#players").sortable({
		update: function() {
			var order = $("#players").sortable('toArray');
			$.post(ajax_url,
				{'draft_action':'reorder_players', 'new_order':order}
			);
		}
	});
	$("#players").disableSelection();

	$('.note').blur(function() {
		text = $(this);
		player_id = text.attr('id').substring(5);

		$.post(ajax_url,
			{'draft_action':'update_note', 'player_id':player_id, 'note':text.val()},
			function() {}	,
			'json'
		);
	}).click(function() {
		$(this).focus();
	});


	$('a.draft-player').click(function() {
		link = $(this);
		player_id = link.attr('id').substring(6);

		$.post(ajax_url,
			{'draft_action':'draft_player', 'player_id':player_id},
			draft_player_callback	,
			'json'
		);
		return false;
	});

	$('#auto_draft').click(function(){
		$.post(ajax_url,
			{'draft_action':'auto_draft_player'},
			draft_player_callback	,
			'json'
		);
		return false;
	});

	draft_player_callback = function(data) {
		if(data.success) {
			link =  $('#draft-'+data.player_id);
			link.parent().parent().addClass('drafted');
			link.remove();
			$('#active_team').html(data.new_active_team);
			$('#roster-'+data.draft_team).html(data.draft_team_html);
			$('#pick_num').html(data.pick_num);
		} else {
			alert('Something went wrong.  Please refresh');
		}
	}

	$('#toggle_hide_drafted').click(function(){
			link = $(this);
			if(link.html() == 'Hide Drafted') {
				link.html('Show Drafted');
				$('#players').sortable('disable');
				$('#players').addClass('hide-drafted');
			} else {
				$('#players').removeClass('hide-drafted');
				$('#players').sortable('enable');
				link.html('Hide Drafted');
			}
			return false;
	});

	$('#toggle_hide_draftboard').click(function(){
			link = $(this);
			if(link.html() == 'Hide Draft Board') {
				link.html('Show Draft Board');
				$('#teams').hide();
			} else {
				$('#teams').show();
				link.html('Hide Draft Board');
			}
			return false;
	});

	$('#toggle_qb').click(function() {
		toggle('QB');
		return false;
	});
	
	$('#toggle_rb').click(function() {
		toggle('RB');
		return false;
	});
	
	$('#toggle_wr').click(function() {
		toggle('WR');
		return false;
	});
	
	$('#toggle_te').click(function() {
		toggle('TE');
		return false;
	});
	
	$('#toggle_dst').click(function() {
		toggle('DST');
		return false;
	});
	
	$('#toggle_k').click(function() {
		toggle('K');
		return false;
	});
	
	function toggle(pos) {
		$('li.' + pos).toggle();
	}
});
