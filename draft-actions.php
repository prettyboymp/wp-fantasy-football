<div id="header">
	&nbsp; &nbsp;
	<a href="#" id="auto_draft">Auto Draft</a>
	|
	<a href="<?php echo add_query_arg('draft_action', 'undo'); ?>">Undo</a>
	|
	<a href="#" id="toggle_hide_drafted">Hide Drafted</a>
	|
	<a href="#" id="toggle_hide_draftboard">Hide Draft Board</a>
	|
	<a href="<?php echo add_query_arg('draft_action', 'restart'); ?>">Restart Draft</a>
	|
	<a href="<?php echo add_query_arg('draft_action', 'mock_draft'); ?>">Mock Full Draft</a>
	|
	Active Team: <span id="active_team"><?php echo DraftAPI::get_active_draft_team()->team_key ?></span>
	|
	Pick # <span id="pick_num"><?php echo DraftAPI::get_pick_number()?></span>
	|
	<a id="toggle_qb" href="#">QB</a>
	<a id="toggle_rb" href="#">RB</a>
	<a id="toggle_wr" href="#">WR</a>
	<a id="toggle_te" href="#">TE</a>
	<a id="toggle_dst" href="#">DST</a>
	<a id="toggle_k" href="#">K</a>
</div>