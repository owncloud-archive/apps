function viewSgf(dir, file) {

        //tinker a valid sgf URL for the eidogo lib
        var location = fileDownloadPath(dir, file);
        $('.actions,#file_action_panel').fadeOut('slow').promise().done(function() {
                // sgf action toolbar
                var sgfToolbarHtml = '<div class="crumb last">'+file+'</div>';
                // Change breadcrumb classes
                $('#controls .last').removeClass('last');
                $('#controls').append(sgfToolbarHtml);
        });


        // fade out file list
        $('table').fadeOut('slow').promise().done(function(){;
                // inject div container to load the player
                var sgfviewer ='<div id="player-container" style="margin-top:45px;"></div>';
                $('table').after(sgfviewer);

                // load sgf player
                var player = new eidogo.Player({
                        container:"player-container",
                        theme:"standard",
                        sgfUrl: location,
                        enableShortcuts: true,
                        problemMode: false,
                        showComments:    true,
                        showPlayerInfo:  true,
                        showGameInfo:    true,
                        showTools:       true,
                        markCurrent:     true,
                        markVariations:  true
                });
        });

}


// do the file manager file extension recognition magic
$(document).ready(function() {
        if(typeof FileActions!=='undefined'){

                var supportedMimes = new Array(
                        'application/sgf');
                for (var i = 0; i < supportedMimes.length; ++i){
                        var mime = supportedMimes[i];
                        FileActions.register(mime,'View','',function(filename){
                                viewSgf($('#dir').val(),filename);
                        });
                        FileActions.setDefault(mime,'View');
                }
        }

});
