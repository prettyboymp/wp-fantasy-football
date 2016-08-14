(function(React, wp, _, $) {

  var RankPlayer = React.createClass({displayName: "RankPlayer",
    handleMouseOver: function(e) {
      this.props.onPlayerMouseOver(this.props.player.id);
    },
    handleMouseOut: function(e) {
      this.props.onPlayerMouseOver(0);
    },
    render: function() {
      var cssClass = 'player-' + this.props.player.position;
      if(this.props.player.id === this.props.highlighted) {
        cssClass += ' highlighted';
      }
      return (
          React.createElement("li", {className: cssClass, onMouseOver: this.handleMouseOver, onMouseOut: this.handleMouseOut}, 
            React.createElement("span", {className: "val"}, this.props.player[this.props.showField]), this.props.player.name
          )
        );
    }
  });

  var RankColumn = React.createClass({displayName: "RankColumn",
    render:  function() {
      var sortby = this.props.sortby;
      var sortedPlayers = _.sortBy(this.props.players, sortby);
      var highlighted = this.props.highlighted;
      var onPlayerMouseOver = this.props.onPlayerMouseOver;
      if(this.props.filter) {
        sortedPlayers = _.where(sortedPlayers, this.props.filter);
      }
      var playerNodes = sortedPlayers.map(function(player) {
        return (
            React.createElement(RankPlayer, {player: player, showField: sortby, highlighted: highlighted, onPlayerMouseOver: onPlayerMouseOver})
          );
      });

      return (
        React.createElement("ul", null, 
          playerNodes
        )
      );
    }
  });
    
  var RankPage = React.createClass({displayName: "RankPage",
    highlightPlayer: function(player_id) {
      this.setState({highlighted: player_id});
    },
    loadPlayersFromServer: function() {
      $.ajax({
        url: wp.ajax.url,
        dataType: 'json',
        data: 'action=ff_get_players',
        cache: false,
        success: function(resp) {
          this.setState({players:resp.data.players}); 
        }.bind(this),
        error: function(xhr, status, err) {
          console.error(wp.ajax.url, status, err.toString());
        }.bind(this)
      });
    },
    loadPicksFromServer: function() {
      $.ajax({
        url: wp.ajax.url,
        dataType: 'json',
        data: 'action=ff_get_draft_picks',
        cache: false,
        success: function(resp) {
          this.setState({draft_picks : resp.data.picks}); 
        }.bind(this),
        error: function(xhr, status, err) {
          console.error(wp.ajax.url, status, err.toString());
        }.bind(this)
      });
    },
    getInitialState: function() {
      return {players: [], draft_picks: [], highlighted: 0};
    },
    componentDidMount: function() {
      this.loadPlayersFromServer();
      this.loadPicksFromServer();
      setInterval(this.loadPicksFromServer, this.props.pollInterval);
    },
    render: function() {
      var draftedIds = _.map(this.state.draft_picks, function(pick){
        return pick.player_id;
      });

      var players = _.reject(this.state.players, function(player) {
        return _.contains(draftedIds, player.id);
      });
      var filters = {
        qb: {position:'QB'},
        rb: {position: 'RB'},
        wr: {position: 'WR'},
        te: {position: 'TE'},
        dst: {position: 'DST'},
        k: {position: 'K'},
      };

      return(
        React.createElement("div", {className: "row"}, 

            React.createElement("div", {className: "col-xs-5", id: "global-rank"}, 
              React.createElement("div", null, 
                React.createElement("h4", null, "FP RANK"), 
                React.createElement(RankColumn, {players: players, sortby: "rank_fp", highlighted: this.state.highlighted, onPlayerMouseOver: this.highlightPlayer})
              )
            ), 
        
            React.createElement("div", {className: "col-xs-5", id: "global-adp"}, 
              React.createElement("div", null, 
              React.createElement("h4", null, "FP ADP"), 
              React.createElement(RankColumn, {players: players, sortby: "adp_fp", highlighted: this.state.highlighted, onPlayerMouseOver: this.highlightPlayer})
              )
            ), 
        
            React.createElement("div", {className: "col-xs-5", id: "espn-adp"}, 
              React.createElement("div", null, 
              React.createElement("h4", null, "ESPN ADP"), 
              React.createElement(RankColumn, {players: players, sortby: "adp_espn", highlighted: this.state.highlighted, onPlayerMouseOver: this.highlightPlayer})
              )
            ), 
        
            React.createElement("div", {className: "col-xs-5", id: "espn-rank"}, 
              React.createElement("div", null, 
              React.createElement("h4", null, "ESPN RANK"), 
              React.createElement(RankColumn, {players: players, sortby: "rank_espn", highlighted: this.state.highlighted, onPlayerMouseOver: this.highlightPlayer})
              )
            ), 
        
        
            React.createElement("div", {className: "col-xs-5", id: "espn-rank-qb"}, 
              React.createElement("div", null, 
                React.createElement("h4", null, "ESPN QB RANK"), 
                React.createElement(RankColumn, {players: players, sortby: "rank_espn", filter: filters.qb, highlighted: this.state.highlighted, onPlayerMouseOver: this.highlightPlayer})
              ), 
          
              React.createElement("div", null, 
                React.createElement("h4", null, "ESPN TE RANK"), 
                React.createElement(RankColumn, {players: players, sortby: "rank_espn", filter: filters.te, highlighted: this.state.highlighted, onPlayerMouseOver: this.highlightPlayer})
              )
              
            ), 
        
            React.createElement("div", {className: "col-xs-5", id: "espn-rank-rb"}, 
              React.createElement("div", null, 
                React.createElement("h4", null, "ESPN RB RANK"), 
                React.createElement(RankColumn, {players: players, sortby: "rank_espn", filter: filters.rb, highlighted: this.state.highlighted, onPlayerMouseOver: this.highlightPlayer})
              ), 
          
              React.createElement("div", null, 
                React.createElement("h4", null, "ESPN DST RANK"), 
                React.createElement(RankColumn, {players: players, sortby: "rank_espn", filter: filters.dst, highlighted: this.state.highlighted, onPlayerMouseOver: this.highlightPlayer})
              )
            ), 
        
            React.createElement("div", {className: "col-xs-5", id: "espn-rank-wr"}, 
              React.createElement("div", null, 
                React.createElement("h4", null, "ESPN WR RANK"), 
                React.createElement(RankColumn, {players: players, sortby: "rank_espn", filter: filters.wr, highlighted: this.state.highlighted, onPlayerMouseOver: this.highlightPlayer})
              ), 
          
              React.createElement("div", null, 
                React.createElement("h4", null, "ESPN K RANK"), 
                React.createElement(RankColumn, {players: players, sortby: "rank_espn", filter: filters.k, highlighted: this.state.highlighted, onPlayerMouseOver: this.highlightPlayer})
              )
            )

            
        )

      );
    }
  });

  var DraftOrder = React.createClass({displayName: "DraftOrder",
    getDefaultProps: function () {
      return {
        picks: []
      };
    },
    render: function() {
      var emptyPicks = _.filter(this.props.picks, function(pick) {
        return _.isNull(pick.player);
      });
      emptyPicks = emptyPicks.slice(0, 5);
      var first = true;
      var pickNodes = _.map(emptyPicks, function(pick) {
        var className = first ? 'upcoming-pick current-pick' : 'col-xs-3 upcoming-pick ';
        first = false;
        return (
          React.createElement("div", {className: "col-xs-6"}, React.createElement("span", {className: className}, React.createElement("span", {className: "num"}, pick.pickNum), pick.team.name))
        );
      });
      return (
          React.createElement("div", {className: "row upcoming-picks"}, 
            React.createElement("div", {className: "col-xs-5"}, React.createElement("h4", null, "Upcoming Picks:")), 
            pickNodes
          )
      );
    }
  });
  
  var DraftTeamRoster = React.createClass({displayName: "DraftTeamRoster",
    render: function() {
      var team = this.props.team;
      var myPicks = _.filter(this.props.picks, function(pick) {
        return pick.team.key == team.key;
      });
      
      var classNames = this.props.currentPick && (this.props.currentPick.team == team) ? 'roster current' : 'roster';
      var pickNodes = _.map(myPicks, function(pick) {
        var img = null;
        if(pick.player) {
          var src = "http://a.espncdn.com/combiner/i?img=/i/headshots/nfl/players/full/" + pick.player.id+ ".png&w=45&h=29";
          img = (React.createElement("img", {src: src}));
        }                
        return (
          React.createElement("li", {className: "roster-position"}, React.createElement("span", {className: "num"}, pick.pickNum), img, React.createElement("span", {className: "name"}, pick.player ? pick.player.name + ', ' + pick.player.position : ''))
        );
      });
      
      return (
          React.createElement("div", {className: classNames}, 
            React.createElement("h4", null, this.props.team.name), 
            React.createElement("ul", null, 
              pickNodes
            )
          )
      );
    }
  });

  var DraftBoardPage = React.createClass({displayName: "DraftBoardPage",
   getDefaultProps: function () {
     return {
       round: 16
     };
   },
   getInitialState: function() {
     return {
       picks: [],
       teams: [],
       players: []
     };
   },
   loadPlayersFromServer: function() {
      $.ajax({
        url: wp.ajax.url,
        dataType: 'json',
        data: 'action=ff_get_players',
        cache: false,
        success: function(resp) {
          this.setState({players: _.indexBy(resp.data.players, 'id')});
        }.bind(this),
        error: function(xhr, status, err) {
          console.error(wp.ajax.url, status, err.toString());
        }.bind(this)
      });
    },
    loadPicksFromServer: function() {
      $.ajax({
        url: wp.ajax.url,
        dataType: 'json',
        data: 'action=ff_get_draft_picks',
        cache: false,
        success: function(resp) {
          this.setState({picks: _.indexBy(resp.data.picks,'pick_num')});
        }.bind(this),
        error: function(xhr, status, err) {
          console.error(wp.ajax.url, status, err.toString());
        }.bind(this)
      });
   },
   loadTeamsFromServer: function() {
      $.ajax({
        url: wp.ajax.url,
        dataType: 'json',
        data: 'action=ff_get_draft_teams',
        cache: false,
        success: function(resp) {
          this.setState({teams: resp.data.teams});
        }.bind(this),
        error: function(xhr, status, err) {
          console.error(wp.ajax.url, status, err.toString());
        }.bind(this)
      });
   },
   getAllPicks: function() {
    if($.isEmptyObject(this.state.picks)) {
      return [];
    }
    if($.isEmptyObject(this.state.players)) {
      return[];
    }
    if(this.state.teams.length === 0) {
      return[];
    }
    
    var teams = this.state.teams.slice(0),
          teamCount = teams.length,
          picks = this.state.picks,
          players = this.state.players,
          allPicks = [],
          rnd, player, pickNum;

    for(rnd = 0; rnd < this.props.rounds; rnd++) {
      _.each(teams, function(team, i) {
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
   componentDidMount: function() {
      this.loadPlayersFromServer();
      this.loadPicksFromServer();
      this.loadTeamsFromServer();
      setInterval(this.loadPicksFromServer, this.props.pollInterval);
   },
   render: function() {
     
     var allPicks = this.getAllPicks();
     var currentPick = _.find(allPicks, function(pick) {
       return _.isNull(pick.player);
     }); 
     
     var rosterColumns = [],
         numTeams = this.state.teams.length;
     
     //assuming teams is always even
     for(var i = 0; i < numTeams/2; i++) {
       rosterColumns.push(
        React.createElement("div", {className: "col-xs-7"}, 
          React.createElement(DraftTeamRoster, {picks: allPicks, team: this.state.teams[i], currentPick: currentPick}), 
          React.createElement(DraftTeamRoster, {picks: allPicks, team: this.state.teams[i + (numTeams / 2 )], currentPick: currentPick})
        )
       );
     }
     
     
     return (
      React.createElement("div", null, 
        React.createElement(DraftOrder, {picks: allPicks}), 

        React.createElement("div", {className: "row"}, 
          rosterColumns
        )
      )
     );
   }   
  });
  
  var page;
  if( page = document.getElementById('rank-page') ) {
    React.render(React.createElement(RankPage, {pollInterval: 5000}), page);
  } else if(page = document.getElementById('draft-board')) {
    React.render(React.createElement(DraftBoardPage, {rounds: 16, pollInterval: 2000}), page);
  }
  
})(React, _wpUtilSettings, _, jQuery);
