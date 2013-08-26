jQuery(function($) {
  var nextCalcPick = 0;
  
  $("#players").sortable({
    update: function() {
      var order = $("#players").sortable('toArray');
      $.post(ajax_url,
          {'draft_action': 'reorder_players', 'new_order': order}
      );
    }
  });
  $("#players").disableSelection();

  $('.note').blur(function() {
    text = $(this);
    player_id = text.attr('id').substring(5);

    $.post(ajax_url,
        {'draft_action': 'update_note', 'player_id': player_id, 'note': text.val()},
    function() {
    },
        'json'
        );
  }).click(function() {
    $(this).focus();
  });


  $('a.draft-player').click(function() {
    link = $(this);
    player_id = link.attr('id').substring(6);

    $.post(ajax_url,
        {'draft_action': 'draft_player', 'player_id': player_id},
    draft_player_callback,
        'json'
        );
    return false;
  });

  $('#auto_draft').click(function() {
    $.post(ajax_url,
        {'draft_action': 'auto_draft_player'},
    draft_player_callback,
        'json'
        );
    return false;
  });

  draft_player_callback = function(data) {
    if (data.success) {
      link = $('#draft-' + data.player_id);
      link.parent().parent().addClass('drafted');
      link.remove();
      $('#active_team').html(data.new_active_team);
      $('#roster-' + data.draft_team).html(data.draft_team_html);
      $('#pick_num').html(data.pick_num);
      $('#vbd').html(data.vbd_html);
      $('#upcoming').html(data.upcoming_html);
      updatePickProbability();
    } else {
      alert('Something went wrong.  Please refresh');
    }
  }

  $('#toggle_hide_drafted').click(function() {
    link = $(this);
    if (link.html() == 'Hide Drafted') {
      link.html('Show Drafted');
      $('#players').sortable('disable');
      $('.players').addClass('hide-drafted');
    } else {
      $('.players').removeClass('hide-drafted');
      $('#players').sortable('enable');
      link.html('Hide Drafted');
    }
    return false;
  });

  $('#toggle_hide_draftboard').click(function() {
    link = $(this);
    if (link.html() == 'Hide Draft Board') {
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
  
  updatePickProbability = function() {
    var nextNextPick = 0;
    var upcoming = $('#upcoming li');
    var j = 0, i = 0, $upcoming;
    
    for(; i < upcoming.length; i++) {
      $upcoming = $(upcoming[i]);
      if($upcoming.hasClass('mike')) {
        j++;
        if(j >= 2) {
          nextNextPick = parseInt($upcoming.html());
          break;
        }
      }
    }
    if(nextNextPick === nextCalcPick) {
      return;
    }
    
    nextCalcPick = nextNextPick;

    
    $("#ranking #players li.player").each(function() {
      var $this = $(this);
      var $meter = $this.find('div.meter');
      if($this.hasClass('drafted')) {
        $meter.css('display', 'none');
        return;
      }
      var adp = parseFloat($this.data('adp'));
      var sd = parseFloat($this.data('std')) / 10;
      var perc = 100 * chanceTaken(nextCalcPick, adp, sd);
      var $meter = $this.find('div.meter');
      if(perc <= 25) {
        $meter.removeClass('orange').addClass('red');
      } else if(perc <= 50) {
        $meter.addClass('red');
      }
      $this.find('.per-taken').html(Math.round(perc) + '%');
      $this.find('.meter span').css('width', (perc) + '%');
    });
  }

  var zProb = function(z) {
    var flag,
        b,
        s,
        HH,
        p;
        
    if (z < -7) {
      return 0.0;
    }
    if (z > 7) {
      return 1.0;
    }


    if (z < 0.0) {
      flag = true;
    }
    else
    {
      flag = false;
    }

    z = Math.abs(z);
    b = 0.0;
    s = Math.sqrt(2) / 3 * z;
    HH = .5;
    for (var i = 0; i < 12; i++) {
      a = Math.exp(-HH * HH / 9) * Math.sin(HH * s) / HH;
      b = b + a;
      HH = HH + 1.0;
    }
    p = .5 - b / Math.PI;
    if (!flag) {
      p = 1.0 - p;
    }
    return p;
  };

  var chanceTaken = function(nextPick, adp, sd) {
    if(!adp || !sd || nextPick < (adp - (3 * sd))) {
      return 1;
    }
    var z = (adp - nextPick) / sd;
    return Math.round(zProb(z) * 10000) / 10000;
  };
  updatePickProbability();
});


/**
 * next pick
 * adp
 * std dev
 */
