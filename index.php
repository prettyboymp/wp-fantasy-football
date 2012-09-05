<?php get_header(); ?>
<?php get_template_part('draft-actions'); ?>
<div id="ranking">
	<ol id="players">
		<?php $players = DraftAPI::get_players();?>
		<?php foreach($players as $player): ?>
			<li id="player-<?php echo $player->player_id ?>" class="<?php echo $player->player_position?><?php echo is_null($player->team_key) ? '' : ' drafted' ?>">
				<div>
					<span><a href="javascript:newWin('http://espn.go.com/nfl/player/_/id/<?php echo $player->player_id?>/')"
						instance="_ppc" playerid="<?php echo $player->player_id?>" playeridtype="sportsId" gameroot="ffl" content="tabs#ppc" tab="null" fpopwidth="490px" fpopheight="357px" cache="true"
						><?php echo $player->player_name ?></a></span>
					<br />
					<?php echo "{$player->player_position} &ndash;  {$player->team_name} &ndash; {$player->adp}"  ?>

					<?php if(is_null($player->team_key)) : ?>
						<a href="#" class="draft-player" id="draft-<?php echo $player->player_id?>">draft</a>
					<?php endif; ?>
				</div>
				<textarea class="note" id="note-<?php echo $player->player_id?>" cols="54" rows="2"><?php echo esc_html($player->note)?></textarea>

			</li>
		<?php endforeach;?>
	</ol>
</div>

<div id="teams">
	<?php $teams = DraftAPI::get_draft_teams();?>
	<ul>
		<?php foreach($teams as $team) : ?>
			<li>
				<span class="team_name"><?php echo $team->team_key?></span>
				<ul id="roster-<?php echo $team->team_key?>">
					<?php echo $team->get_roster_html(); ?>
				</ul>
			</li>
		<?php endforeach; ?>
	</ul>
</div>


<?php get_footer();