$(function(){

	function processElements($elem){
		$elem.find('.avatar').each(function(){
			var $this = $(this);
			$this.avatar($this.data('user'), 32);
		});
		$elem.find('.tooltip').tipsy({gravity:'s', fade:true});
	}

	var $container = $('#container');
	processElements($container);

	$container.imagesLoaded(function(){
		$container.find('.boxcontainer').masonry({
			itemSelector: '.box',
			isAnimated: true
		});
	});

	$container.infinitescroll({
			navSelector  : '#page-nav',    // selector for the paged navigation
			nextSelector : '#page-nav a',  // selector for the NEXT link (to page 2)
			itemSelector : '.group',     // selector for all items you'll retrieve
			pixelsFromNavToBottom: 150,
			extraScrollPx: 50,
			prefill: true,
			path : function(page){
				return OC.filePath('activity', 'ajax', 'fetch.php') + '?page=' + page;
			},
			loading: {
				finishedMsg: t('activity', 'No more activities to load.'),
				msgText: t('activity', 'Loading older activities'),
				img: OC.filePath('core', 'img', 'loading-small.gif')
			}
		},
		// trigger Masonry as a callback
		function( newGroups ) {
			// hide new items while they are loading
			var $newGroups = $( newGroups );
			var $newBoxes;

			// check whether first new group has the same date
			// as the last group we had before
			// If that's the case, we'll merge its boxes into the last group's
			// container.
			var $firstNewGroup = $newGroups.first();
			var $lastGroup = $firstNewGroup.prevAll('.group:first');
			var $appendedBoxes;
			if ( $lastGroup.data('date') === $firstNewGroup.data('date') ){
				// append the boxes
				$appendedBoxes = $firstNewGroup.find('.box').addClass('loading');
				var $lastBoxContainer = $lastGroup.find('.boxcontainer');

				$lastBoxContainer.append($appendedBoxes);
				processElements($appendedBoxes);
				$lastBoxContainer.masonry('appended', $appendedBoxes, true);
				$appendedBoxes.imagesLoaded(function(){
					// append the boxes into the last group
					$appendedBoxes.toggleClass('loading loaded');
				});
				// remove from list to process
				$newGroups.slice(1);
				// discard the ajax-returned header
				$firstNewGroup.remove();
			}

			$newBoxes = $newGroups.find('.box').addClass('loading');

			processElements($newBoxes);
			$newGroups.find('.boxcontainer').masonry();
			// ensure that images load before adding to masonry layout
			$newBoxes.imagesLoaded(function(){
				// show elems now they're ready
				$newBoxes.toggleClass('loading loaded');
			});
		}
	);
});

