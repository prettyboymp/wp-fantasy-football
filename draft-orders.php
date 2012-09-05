<?php get_header(); ?>
<?php get_template_part('draft-actions'); ?>
<div id="ranking2" style="padding-top: 20px;">
	<?php $teams = DraftAPI::get_draft_teams();?>
	<?php foreach($teams as $team) : ?>
		<div style="float: left; width: 200px;">
			<ol id="players">
				<?php foreach($team->get_draft_order() as $player): ?>
					<li id="player-<?php echo $player->player_id ?>" class="<?php echo $player->player_position?><?php echo is_null($player->team_key) ? '' : ' drafted' ?>">
						<div>
							<span><a href="javascript:newWin('http://sports.espn.go.com/nfl/players/fantasy?playerId=<?php echo $player->player_id?>)"><?php echo $player->player_name ?></a></span>
							<br />
							<?php echo "{$player->player_position} &ndash;  {$player->team_name} &ndash; {$player->adp}"  ?>

							<?php if(is_null($player->team_key)) : ?>
								<a href="#" class="draft-player" id="draft-<?php echo $player->player_id?>">draft</a>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach;?>
			</ol>
		</div>
	<?php endforeach; ?>
</div>
<?php get_footer();