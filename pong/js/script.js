$( document ).ready(function() {

    var size        = document.getElementById('size');
    var sound       = document.getElementById('sound');
    var stats       = document.getElementById('stats');
    var footprints  = document.getElementById('footprints');
    var predictions = document.getElementById('predictions');

    var pong = Game.start('game', Pong, {
      sound:       sound.checked,
      stats:       stats.checked,
      footprints:  footprints.checked,
      predictions: predictions.checked
    });

    Game.addEvent(sound,       'change', function() { pong.enableSound(sound.checked);           });
    Game.addEvent(stats,       'change', function() { pong.showStats(stats.checked);             });
    Game.addEvent(footprints,  'change', function() { pong.showFootprints(footprints.checked);   });
    Game.addEvent(predictions, 'change', function() { pong.showPredictions(predictions.checked); });

});


