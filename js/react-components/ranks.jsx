(function (React, wp, _, $) {

	var RankPlayer = React.createClass({
		handleMouseOver: function (e) {
			this.props.onPlayerMouseOver(this.props.player.id);
		},
		handleMouseOut: function (e) {
			this.props.onPlayerMouseOver(0);
		},
		render: function () {
			var cssClass = 'player-' + this.props.player.position;
			if (this.props.player.id === this.props.highlighted) {
				cssClass += ' highlighted';
			}
			return (
				<li className={cssClass} onMouseOver={this.handleMouseOver} onMouseOut={this.handleMouseOut}>
					<span className="val">{this.props.player[this.props.showField]}</span>{this.props.player.name}
				</li>
			);
		}
	});

	var RankColumn = React.createClass({
		render: function () {
			var sortby = this.props.sortby;
			var sortedPlayers = _.sortBy(this.props.players, sortby);
			var highlighted = this.props.highlighted;
			var onPlayerMouseOver = this.props.onPlayerMouseOver;
			if (this.props.filter) {
				sortedPlayers = _.where(sortedPlayers, this.props.filter);
			}
			var playerNodes = sortedPlayers.map(function (player) {
				return (
					<RankPlayer player={player} showField={sortby} highlighted={highlighted}
								onPlayerMouseOver={onPlayerMouseOver}/>
				);
			});

			return (
				<ul>
					{playerNodes}
				</ul>
			);
		}
	});

	var RankPage = React.createClass({
		highlightPlayer: function (player_id) {
			this.setState({highlighted: player_id});
		},
		loadPlayersFromServer: function () {
			$.ajax({
				url: wp.ajax.url,
				dataType: 'json',
				data: 'action=ff_get_players',
				cache: false,
				success: function (resp) {
					this.setState({players: resp.data.players});
				}.bind(this),
				error: function (xhr, status, err) {
					console.error(wp.ajax.url, status, err.toString());
				}.bind(this)
			});
		},
		loadPicksFromServer: function () {
			$.ajax({
				url: wp.ajax.url,
				dataType: 'json',
				data: 'action=ff_get_draft_picks',
				cache: false,
				success: function (resp) {
					this.setState({draft_picks: resp.data.picks});
				}.bind(this),
				error: function (xhr, status, err) {
					console.error(wp.ajax.url, status, err.toString());
				}.bind(this)
			});
		},
		getInitialState: function () {
			return {players: [], draft_picks: [], highlighted: 0};
		},
		componentDidMount: function () {
			this.loadPlayersFromServer();
			this.loadPicksFromServer();
			setInterval(this.loadPicksFromServer, this.props.pollInterval);
		},
		render: function () {
			var draftedIds = _.map(this.state.draft_picks, function (pick) {
				return pick.player_id;
			});

			var players = _.reject(this.state.players, function (player) {
				return _.contains(draftedIds, player.id);
			});
			var filters = {
				qb: {position: 'QB'},
				rb: {position: 'RB'},
				wr: {position: 'WR'},
				te: {position: 'TE'},
				dst: {position: 'DST'},
				k: {position: 'K'},
			};

			return (
				<div className="row">

					<div className="col-xs-5" id="global-rank">
						<div>
							<h4>FP RANK</h4>
							<RankColumn players={players} sortby="rank_fp" highlighted={this.state.highlighted}
										onPlayerMouseOver={this.highlightPlayer}/>
						</div>
					</div>

					<div className="col-xs-5" id="global-adp">
						<div>
							<h4>FP ADP</h4>
							<RankColumn players={players} sortby="adp_fp" highlighted={this.state.highlighted}
										onPlayerMouseOver={this.highlightPlayer}/>
						</div>
					</div>

					<div className="col-xs-5" id="espn-adp">
						<div>
							<h4>ESPN ADP</h4>
							<RankColumn players={players} sortby="adp_espn" highlighted={this.state.highlighted}
										onPlayerMouseOver={this.highlightPlayer}/>
						</div>
					</div>

					<div className="col-xs-5" id="espn-rank">
						<div>
							<h4>ESPN RANK</h4>
							<RankColumn players={players} sortby="rank_espn" highlighted={this.state.highlighted}
										onPlayerMouseOver={this.highlightPlayer}/>
						</div>
					</div>


					<div className="col-xs-5" id="espn-rank-qb">
						<div>
							<h4>ESPN QB RANK</h4>
							<RankColumn players={players} sortby="rank_espn" filter={filters.qb}
										highlighted={this.state.highlighted} onPlayerMouseOver={this.highlightPlayer}/>
						</div>

						<div>
							<h4>ESPN TE RANK</h4>
							<RankColumn players={players} sortby="rank_espn" filter={filters.te}
										highlighted={this.state.highlighted} onPlayerMouseOver={this.highlightPlayer}/>
						</div>

					</div>

					<div className="col-xs-5" id="espn-rank-rb">
						<div>
							<h4>ESPN RB RANK</h4>
							<RankColumn players={players} sortby="rank_espn" filter={filters.rb}
										highlighted={this.state.highlighted} onPlayerMouseOver={this.highlightPlayer}/>
						</div>

						<div>
							<h4>ESPN DST RANK</h4>
							<RankColumn players={players} sortby="rank_espn" filter={filters.dst}
										highlighted={this.state.highlighted} onPlayerMouseOver={this.highlightPlayer}/>
						</div>
					</div>

					<div className="col-xs-5" id="espn-rank-wr">
						<div>
							<h4>ESPN WR RANK</h4>
							<RankColumn players={players} sortby="rank_espn" filter={filters.wr}
										highlighted={this.state.highlighted} onPlayerMouseOver={this.highlightPlayer}/>
						</div>

						<div>
							<h4>ESPN K RANK</h4>
							<RankColumn players={players} sortby="rank_espn" filter={filters.k}
										highlighted={this.state.highlighted} onPlayerMouseOver={this.highlightPlayer}/>
						</div>
					</div>


				</div>

			);
		}
	});

	var DraftOrder = React.createClass({
		getDefaultProps: function () {
			return {
				picks: []
			};
		},
		render: function () {
			var emptyPicks = _.filter(this.props.picks, function (pick) {
				return _.isNull(pick.player);
			});
			emptyPicks = emptyPicks.slice(0, 5);
			var first = true;
			var pickNodes = _.map(emptyPicks, function (pick) {
				var className = first ? 'upcoming-pick current-pick' : 'col-xs-3 upcoming-pick ';
				first = false;
				return (
					<div className="col-xs-6"><span className={className}><span
						className="num">{pick.pickNum}</span>{pick.team.name}</span></div>
				);
			});
			return (
				<div className="row upcoming-picks">
					<div className="col-xs-5"><h4>Upcoming Picks:</h4></div>
					{pickNodes}
				</div>
			);
		}
	});

	var DraftTeamRoster = React.createClass({
		render: function () {
			var team = this.props.team;
			var myPicks = _.filter(this.props.picks, function (pick) {
				return pick.team.key == team.key;
			});

			var classNames = this.props.currentPick && (this.props.currentPick.team == team) ? 'roster current' : 'roster';
			var pickNodes = _.map(myPicks, function (pick) {
				var img = null;
				//hiding the image for now since it takes too much room
				if (false && pick.player) {
					var src = "http://a.espncdn.com/combiner/i?img=/i/headshots/nfl/players/full/" + pick.player.id + ".png&w=45&h=29";
					img = (<img src={src}/>);
				}
				return (
					<li className="roster-position"><span className="num">{pick.pickNum}</span>{img}<span
						className="name">{pick.player ? pick.player.name + ', ' + pick.player.position : ''}</span></li>
				);
			});

			return (
				<div className={classNames}>
					<h4>{this.props.team.name}</h4>
					<ul>
						{pickNodes}
					</ul>
				</div>
			);
		}
	});

	var DraftBoardPage = React.createClass({
		getDefaultProps: function () {
			return {
				round: 16
			};
		},
		getInitialState: function () {
			return {
				picks: [],
				teams: [],
				players: []
			};
		},
		loadPlayersFromServer: function () {
			$.ajax({
				url: wp.ajax.url,
				dataType: 'json',
				data: 'action=ff_get_players',
				cache: false,
				success: function (resp) {
					this.setState({players: _.indexBy(resp.data.players, 'id')});
				}.bind(this),
				error: function (xhr, status, err) {
					console.error(wp.ajax.url, status, err.toString());
				}.bind(this)
			});
		},
		loadPicksFromServer: function () {
			$.ajax({
				url: wp.ajax.url,
				dataType: 'json',
				data: 'action=ff_get_draft_picks',
				cache: false,
				success: function (resp) {
					this.setState({picks: _.indexBy(resp.data.picks, 'pick_num')});
				}.bind(this),
				error: function (xhr, status, err) {
					console.error(wp.ajax.url, status, err.toString());
				}.bind(this)
			});
		},
		loadTeamsFromServer: function () {
			$.ajax({
				url: wp.ajax.url,
				dataType: 'json',
				data: 'action=ff_get_draft_teams',
				cache: false,
				success: function (resp) {
					this.setState({teams: resp.data.teams});
				}.bind(this),
				error: function (xhr, status, err) {
					console.error(wp.ajax.url, status, err.toString());
				}.bind(this)
			});
		},
		getAllPicks: function () {
			if ($.isEmptyObject(this.state.picks)) {
				return [];
			}
			if ($.isEmptyObject(this.state.players)) {
				return [];
			}
			if (this.state.teams.length === 0) {
				return [];
			}

			var teams = this.state.teams.slice(0),
				teamCount = teams.length,
				picks = this.state.picks,
				players = this.state.players,
				allPicks = [],
				rnd, player, pickNum;

			for (rnd = 0; rnd < this.props.rounds; rnd++) {
				_.each(teams, function (team, i) {
					pickNum = (teamCount * rnd) + i + 1;
					player = typeof picks[pickNum.toString()] === 'undefined' ? null : players[picks[pickNum.toString()].player_id];
					allPicks.push({
						team: team,
						pickNum: pickNum,
						player: player
					});
				});
				teams.reverse();
			}

			return allPicks;
		},
		componentDidMount: function () {
			this.loadPlayersFromServer();
			this.loadPicksFromServer();
			this.loadTeamsFromServer();
			setInterval(this.loadPicksFromServer, this.props.pollInterval);
		},
		render: function () {

			var allPicks = this.getAllPicks();
			var currentPick = _.find(allPicks, function (pick) {
				return _.isNull(pick.player);
			});

			var rosterColumns = [],
				numTeams = this.state.teams.length;

			//assuming teams is always even
			for (var i = 0; i < numTeams / 2; i++) {
				rosterColumns.push(
					<div className="col-xs-7">
						<DraftTeamRoster picks={allPicks} team={this.state.teams[i]} currentPick={currentPick}/>
						<DraftTeamRoster picks={allPicks} team={this.state.teams[i + (numTeams / 2 )]}
										 currentPick={currentPick}/>
					</div>
				);
			}


			return (
				<div>
					<DraftOrder picks={allPicks}/>

					<div className="row">
						{rosterColumns}
					</div>
				</div>
			);
		}
	});

	var page;
	if (page = document.getElementById('rank-page')) {
		React.render(<RankPage pollInterval={5000}/>, page);
	} else if (page = document.getElementById('draft-board')) {
		React.render(<DraftBoardPage rounds={16} pollInterval={2000}/>, page);
	}

})(React, _wpUtilSettings, _, jQuery);
