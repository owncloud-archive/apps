/**
* @package fluxx-compensator an ownCloud app
* @category base
* @author Christian Reiner
* @copyright 2012-2013 Christian Reiner <foss@christian-reiner.info>
* @license GNU Affero General Public license (AGPL)
* @link information http://apps.owncloud.com/content/show.php?content=157091
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the license, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.
* If not, see <http://www.gnu.org/licenses/>.
*
*/

/**
 * @file js/fluxx.js
 * @brief Client side activity library
 * @author Christian Reiner
 */

// add handle to navigation area
$(document).ready(function(){
	// setup handle objects
	if ($('body#body-user, body#body-settings').length){
		if ($('body header > #header').length)
			OC.FluXX.Handle['H']=OC.FluXX.create('H', OC.FluXX.C_VERTICAL, 'body header > #header', 0, 0);
		if ($('body nav > #navigation').length)
			OC.FluXX.Handle['N']=OC.FluXX.create('N', OC.FluXX.C_HORIZONTAL, 'body nav > #navigation', 0, 0);
	}
	// initilize handles
	OC.FluXX.init();
	// initilize scripts
	OC.FluXX.mode();
	// reposition the handles upon resize of the window
	$(window).on('resize',function(){
		$.each(OC.FluXX.Handle, function(){
			OC.FluXX.maximize(this);
		});
	});
})

/**
 * @class OC.FluXX
 * @brief Activity implementation library
 * @author Christian Reiner
 */
OC.FluXX={
	C_THRESHOLD: 500,
	C_HORIZONTAL: false,
	C_VERTICAL:   true,
	/**
	* @object OC.FluXX.Handle
	* @brief Static reference to the handle object inside the DOM
	* @author Christian Reinerowncloud-core-master-2013-01-08/apps/fluxx_compensator
	*/
 	Handle:{},
	/**
	 * @method OC.FluXX.click
	 * @brief Handle click actions on a handle object
	 * @param object handle: handle object as defined class internal
	 * @author Christian Reiner
	 */
	click:function(handle){
			// 1.) click => toggle navigation hidden or shown
		// 2.) hold => enter vertical handle move mode
		// so only enter move mode after holding mouse down for an amount of time
		var timer=setTimeout(function(){
			OC.FluXX.move(handle);
		},OC.FluXX.C_THRESHOLD);
		// raise normal click handling
		$(handle.Selector).on('mouseup',function(){
			// remove _this_ handler
			$(handle.Selector).off('mouseup');
			// start click reaction
			OC.FluXX.toggle(handle);
		});
		// make sure to cancel move mode if mouse is released before C_THRESHOLD duration has passed (500ms)
		$(document).on('mouseup',function(){
			// don't enter move mode
			clearTimeout(timer);
			// remove _this_ handler
			$(document).off('mouseup');
			// remove _above_ handler
			$(handle.Selector).off('mouseup');
		});
		return false;
	}, // OC.FluXX.click
	/**
	* @method OC.FluXX.create
	* @brief Create js handle objects
	* @author Christian Reiner
	*/
	create:function(id, orientation, anchor, offset, preset){
		var handle={};
		handle.Anchor=anchor;						// selector to anchor handle onto
		handle.Id=id;								// handles id
		handle.Orientation=orientation;				// vertical or horizontal
		handle.Offset=offset;						// offset from min position
		handle.Preset=preset;						// preset from max position
		handle.Position={Val:0,Margin:0,Min:0,Max:0};// initial position values
		handle.Selector='body #fluxx-'+id;			// handle's own selector
		// generate DOM node
		OC.FluXX.generate(handle);
		// compute position limits
		OC.FluXX.limit(handle);
		// position handle object
		OC.FluXX.preference(false,'fluxx-position-'+handle.Id,handle.Position.Max,function(pos){
			OC.FluXX.position(handle, pos);
		});
		// re-compute position limits
		OC.FluXX.maximize(handle);
		return handle;
	}, // OC.FluXX.create
	/**
	* @method OC.FluXX.generate
	* @brief Generate handles DOM node
	* @author Christian Reiner
	*/
	generate:function(handle){
		// create a new handle node
		var node=$('<span id="fluxx-'+handle.Id+'" class="fluxx-handle fluxx-shown" />');
		var img=$('<img class="svg" draggable="false">');
		img.attr('src',OC.filePath('fluxx_compensator','img','actions/fluxx.svg'));
		node.append(img);
		if (handle.Orientation==OC.FluXX.C_HORIZONTAL){
			node.addClass('fluxx-horizontal');
			// move to visible position
			node.css('left',($(handle.Anchor).outerWidth()-1.5)+'px');
		}
		else{
			node.addClass('fluxx-vertical');
			// move to visible position
			node.css('top',($(handle.Anchor).outerHeight()-1.5)+'px');
		}
		$(handle.Anchor).prepend(node);
	}, // OC.FluXX.generate
	/**
	* @method OC.FluXX.hide
	* @brief Hide the panel if visible
	* @author Christian Reiner
	*/
	hide:function(handle){
		var dfd = new $.Deferred();
		if (!$(handle.Selector).hasClass('fluxx-hidden')){
			$.when(
				// mark handle with new classmargin-top
				$(handle.Selector).addClass('fluxx-hidden').removeClass('fluxx-shown')
			).done(function(){
				dfd.resolve();
				// store current handle status inside user preferences
				OC.FluXX.preference(true,'fluxx-status-'+handle.Id,'hidden',null);
			}).fail(dfd.reject)
			// recalculate handle positions
			$.each(OC.FluXX.Handle, function(){OC.FluXX.limit(this);});
		}
		else dfd.resolve();
		return dfd.promise();
	}, // OC.FluXX.hide
	/**
	 * @method OC.FluXX.init
	 * @brief Initializes state of the handles
	 * @author Christian Reiner
	 */
	init:function(){
		// dynamically load stylesheet to make sure it is loaded LAST
		OC.addStyle('fluxx_compensator','dynamic');
		// initialize created handles
		$.each(OC.FluXX.Handle, function(){
			var handle=this;
			// hide or show the navigation in a persistent manner
			OC.FluXX.preference(false,'fluxx-status-'+handle.Id,'shown',function(status){
				if ('hidden'==status){
					OC.FluXX.hide(handle);
					OC.FluXX.state(handle, false);
					OC.FluXX.limit(handle);
				}else{
					OC.FluXX.show(handle);
					OC.FluXX.state(handle, true);
					OC.FluXX.limit(handle);
				}
			});
			// handle mouse reactions
			// 1.) click => toggle navigation hidden or shown
			// 2.) hold => enter vertical handle move mode
			$(handle.Selector).on('mousedown',function(event){
				// swallow click event
				event.stopPropagation();
				OC.FluXX.click(handle);
			});
		});
	},
	/**
	* @method OC.FluXX.limit
	* @brief Compute limits for the handles positions
	* @author Christian Reiner
	*/
	limit:function(handle){
		// some handle specific corrections
		switch (handle.Id){
			case 'N':
				handle.Position.Margin=$(handle.Selector).css('margin-top').replace(/[^-\d\.]/g, '');
console.log('limit(): ',handle.Id,handle.Position);
				;// case 'N';
		}// switch
		// general orientation specific values
		if (OC.FluXX.C_HORIZONTAL==handle.Orientation){
			handle.Position.Min=$(handle.Anchor).css('padding-top').replace(/[^-\d\.]/g, '')
								+handle.Offset-handle.Position.Margin;
			handle.Position.Max=$(handle.Anchor).outerHeight()-$(handle.Anchor).position().top
								-$(handle.Selector).outerHeight()-handle.Preset-handle.Position.Margin;
		}
		else{
			handle.Position.Min=$(handle.Anchor).css('padding-left').replace(/[^-\d\.]/g, '')
								+handle.Offset-handle.Position.Margin;
			handle.Position.Max=$(handle.Anchor).outerWidth()-$(handle.Anchor).position().left
								-$(handle.Selector).outerWidth()-handle.Preset-handle.Position.Margin;
		}
	}, // OC.FluXX.limit
	/**
	 * @method OC.FluXX.maximize
	 * @brief Reposition handle "close to max position" if it has been there before
	 * @param object handle: handle object as defined class internal
	 * @author Christian Reinermargin-top
	 */
	maximize:function(handle){
		// consider all handles
		$.each(OC.FluXX.Handle, function(){
			var candidate=this;
			// act for all handles except the triggering one
			if (candidate!=handle){
				var closeToMax=(candidate.Position.Max-candidate.Position.Val);
				OC.FluXX.limit(candidate);
				// reposition close to max if been there before, just within limits otherwise
				var position=(closeToMax>20)?candidate.Position.Val:candidate.Position.Max-closeToMax;
				OC.FluXX.position(candidate, position);
			}
		})
	}, // OC.FluXX.maximize
	/**
	* @method OC.FluXX.mode
	* @brief Set global app mode
	* @description 
	* Depending on the active app the global html root element is marked with a css class. 
	* That class controls any actions or compensations that might be required by that apps apge layout. 
	* This way all changes and animations can later be done purely in css, as opposed to js. 
	* @author Christian Reiner
	*/
	mode:function(){
		// mark mode and active app as class of the html tag
		// this acts like a 'switch' command inside the dynamically loaded css
		var mode={
			bookmarks_index:	'bookmarks',
			files_index:		'files',
			notes_index:		'notes',
			media_index:		'media',
			calendar_index:		'calendar',
			contacts_index:		'contacts',
			gallery_index:		'gallery',
			shorty_index:		'shorty'
		};
		var index=$('body nav #navigation #apps').find('li .active').parents('li').attr('data-id');
		// mark current mode (active app) as class of the html element
		if (index && mode[index]){
			$('html').addClass('fluxx-mode-'+mode[index]);
		}else{
			$('html').addClass('fluxx-modeless');
		}
	}, // OC.FluXX.mode
	/**
	* @method OC.FluXX.move
	* @brief Moves the handle when dragging it.
	* @author Christian Reiner
	*/
	move:function(handle){
		// enable cursor move mode
		$('html').addClass('fluxx-handle-move-'+handle.Id);
		$(handle.Selector).effect('highlight',{color:'#FFF'},400);
		// remove _outer_ reactions (2!) on mouseup
		$(document).off('mouseup');
		$(handle.Selector).off('mouseup');
		// react on mouseup
		$(document).on('mouseup',function(){
			// remove _this_ handler
			$(document).off('mouseup');
			// remove reaction on mouse movements
			$(document).off('mousemove');
			// disable cursor move mode
			$('html').removeClass('fluxx-handle-move-'+handle.Id);
			$(handle.Selector).css('cursor','pointer');
			$(handle.Selector).find('img').css('cursor','inherit');
			// store final handle position
			OC.FluXX.preference(true,'fluxx-position-'+handle.Id,handle.Position.Val,null);
		});
		// reaction on mouse move: position handle
		$(document).on('mousemove',function(event){
			var delta;
			if (OC.FluXX.C_HORIZONTAL==handle.Orientation){
				// we have to correct the raw vertical mouse position by two factors: 
				// 1. half the handles size and 2. the start position of the anchor which is changed by the other handle
				delta=$(handle.Selector).height()/2.0;	// correction by half the handles size
				OC.FluXX.position(handle, event.pageY-delta-handle.Position.Margin);
				handle.Position.Val=$(handle.Selector).position().top;
			}
			else{
				delta=$(handle.Selector).width()/2.0;	// correction by half the handles size
				OC.FluXX.position(handle, event.pageX-delta-handle.Position.Margin);
				handle.Position.Val=$(handle.Selector).position().left;
			}
		});
	}, // OC.FluXX.move
	/**
	* @method OC.FluXX.position
	* @brief Position handle upon drag action
	* @author Christian Reiner
	*/
	position:function(handle, pos){
		// hide handle whilst being repositioned
		$(handle.Anchor).css('overflow','hidden !important');
		// use specified value as new position, but only inside the given limits
		handle.Position.Val=(pos>handle.Position.Max)?handle.Position.Max:((pos<handle.Position.Min)?handle.Position.Min:pos);
		if (OC.FluXX.C_HORIZONTAL==handle.Orientation)
			$(handle.Selector).css('top',handle.Position.Val+'px');
		else
			$(handle.Selector).css('left',handle.Position.Val+'px');
		// show handle after having been repositioned
		$(handle.Anchor).css('overflow','visible');
	}, // OC.FluXX.position
	/**
	 * @method OC.FluXX.preference
	 * @brief Get or set a personal preference
	 * @author Christian Reiner
	 */
	preference:function(set, key, value, callback){
		switch(set){
			case true:
				// set a preference
				$.when(
					$.post(OC.filePath('fluxx_compensator', 'ajax', 'preference.php'), {'key':key, 'value':value})
				).done(function(result){
					if (callback)
						callback(result.value);
					return result.value;
				}).fail(function(){
					return value;
				})
			default:
			case false:
				// get a preference
				$.when(
					$.getJSON(OC.filePath('fluxx_compensator','ajax','preference.php')+'?key='+encodeURIComponent(key)+'&value='+encodeURIComponent(value))
				).done(function(result){
					if (callback)
						callback(result.value);
					return result.value;
				}).fail(function(){
					return value;
				})
		}
	}, // OC.FluXX.preference
	/**
	* @method OC.FluXX.show
	* @brief Show the panel if visible
	* @author Christian Reiner
	*/
	show:function(handle){
		var dfd = new $.Deferred();
		if (!$(handle.Selector).hasClass('fluxx-shown')){
			$.when(
				// mark handle with new class
				$(handle.Selector).addClass('fluxx-shown').removeClass('fluxx-hidden')
			).done(function(){
				dfd.resolve();
				// store current handle status inside user preferences
				OC.FluXX.preference(true,'fluxx-status-'+handle.Id,'shown',null);
			}).fail(dfd.reject)
			// recalculate handle positions
			$.each(OC.FluXX.Handle, function(){OC.FluXX.limit(this);});
		}
		else dfd.resolve();
		return dfd.promise();
	}, // OC.FluXX.show
	/**
	* @method OC.FluXX.state
	* @brief Mark current state of the compensator
	* @author Christian Reiner
	*/
	state:function(handle,shown){
		// mark the current state (hidden or shown) as class of the html element
		if (shown){
			$('html').removeClass('fluxx-state-'+handle.Id+'-hidden').addClass('fluxx-state-'+handle.Id+'-shown');
		}else{
			$('html').removeClass('fluxx-state-'+handle.Id+'-shown').addClass('fluxx-state-'+handle.Id+'-hidden');
		}
	}, // OC.FluXX.state
	/**
	* @method OC.FluXX.swap
	* @brief Swaps the mode of the app between hidden and shown
	* @author Christian Reiner
	*/
	swap: function(handle){
		var dfd = new $.Deferred();
		// call action depending on the current mode
		if ($(handle.Selector).hasClass('fluxx-shown'))
			$.when(
				OC.FluXX.hide(handle),
				OC.FluXX.state(handle,false)
			).done(dfd.resolve)
		else
			$.when(
				OC.FluXX.show(handle),
				OC.FluXX.state(handle,true)
			).done(dfd.resolve)
		return dfd.promise();
	}, // OC.FluXX.swap
	/**
	* @method OC.FluXX.toggle
	* @brief Toggles the visibility of the navigation area
	* @author Christian Reiner
	*/
	toggle: function(handle){
		var dfd = new $.Deferred();
		// temporarily include transition style rules if not yet present (should not be!)
		if ($('head link#fluxx-transitions').length)
			OC.FluXX.swap(handle);
		else{
			$('<link/>',{
				id:'fluxx-transitions',
				rel:'stylesheet',
				type:'text/css',
				href:OC.filePath('fluxx_compensator','css','transitions.css')
			}).appendTo('head');
			$('head link#fluxx-transitions').on('load',function(){
				$('head link#fluxx-transitions').off('load');
				OC.FluXX.swap(handle);
				// make sure temporary transition style rules are removed, preferably upon event, time based as catchall
				OC.FluXX.wait(handle,function(){
					$('head link#fluxx-transitions').remove();
					OC.FluXX.maximize(handle);
				});
			});
		}
		return dfd.promise();
	}, // OC.FluXX.toggle
	/**
	 * @method OC.FluXX.wait
	 * @brief Wait for transitions to finish
	 * @author Christian Reiner
	 */
	wait: function(handle,callback){
		// safety-catch in case we somehow miss the trnsitionend event...
		var timer=setTimeout(function(){$('head link#fluxx-transitions').remove();},10000);
		// when all planned transitions have finished...
		$(handle.Selector).on('webkitTransitionEnd oTransitionEnd transitionEnd',function(){
			// remove transition styles to prevent interfering with other transitions
			$(handle.Selector).off('webkitTransitionEnd oTransitionEnd transitionEnd');
			// remove safety-catch from above
			clearTimeout(timer);
			// finally execute call callback
			callback();
		});
	} // OC.FluXX.wait
}
