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
		// 1.) click & release => toggle navigation hidden or shown
		// 2.) click & hold => enter handle move mode
		// raise normal click handling
		$(handle.Selector).on('mouseup',function(){
			// start click reaction
			OC.FluXX.toggle(handle);
		});
		// only enter move mode after holding mouse down for an amount of time
		var timer=setTimeout(function(){
			// remove _this_ handler
			$(handle.Selector).off('mouseup');
			// enter handle move mode
			OC.FluXX.move(handle);
		},OC.FluXX.C_THRESHOLD);
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
		handle.Id=id;										// handles id
		handle.Orientation=orientation;	// vertical or horizontal
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
  * @method OC.FluXX.defaults
  * @brief Injects a few default style rules
  * @description
  * Firefox only animates transitions when the start style is explicitly set!
  * Instead of writing static rules we fetch those default rule settings from the live document
  * This way things should be more robust against changes in the OC layout
  * @author Christian Reiner
  */
  defaults: function(){
    OC.FluXX.style('#navigation',      'left:'+$('#navigation').css('left')+'; padding-top:'+$('#navigation').css('padding-top')+';');
    OC.FluXX.style('#content-wrapper', 'padding-left:'+$('#content-wrapper').css('padding-left')+'; padding-top:'+$('#content-wrapper').css('padding-top')+';');
    OC.FluXX.style('#controls',        'padding-right:'+$('#controls').css('padding-right')+';');
    OC.FluXX.style('#header',          'top:'+$('#header').css('top')+';');
    OC.FluXX.style('#fluxx-N',         'margin-top:'+$('#fluxx-N').css('margin-top')+'; left:'+$('#fluxx-N').css('left')+';');
    OC.FluXX.style('#fluxx-H',         'top:'+$('#fluxx-H').css('top')+';');
  }, // OC.FluXX.defaults
	/**
	* @method OC.FluXX.generate
	* @brief Generate handles DOM node
	* @author Christian Reiner
	*/
	generate:function(handle){
		// create a new handle node
		var id='fluxx-'+handle.Id;
		var node=$('<span />').attr('id',id).addClass('fluxx-handle fluxx-shown');
		var img=$('<img>').attr('draggable','false').addClass('svg');
		img.attr('src',OC.filePath('fluxx_compensator','img','actions/fluxx.svg'));
		node.append(img);
		switch (handle.Id){
			case 'N':
				node.addClass('fluxx-horizontal');
				// move to visible position
				OC.FluXX.style('#'+id,'margin-top:'+($('#header').outerHeight())+'px;');
				OC.FluXX.style('html.fluxx-state-N-shown #'+id,'left:'+($(handle.Anchor).outerWidth()-1.5)+'px;');
				break;
			case 'H':
				node.addClass('fluxx-vertical');
				// move to visible position
				OC.FluXX.style('html.fluxx-state-H-shown #'+id,'top:'+($(handle.Anchor).outerHeight()-1.5)+'px;');
				break;
			default:
				; // unknown handle...
		} // switch
		// explicitly inherit z-index from anchor
		OC.FluXX.style('#'+id,'z-index:'+(parseInt($(handle.Anchor).css('z-index'))+1)+';');
		$(handle.Anchor).after(node);
	}, // OC.FluXX.generate
	/**
	* @method OC.FluXX.hide
	* @brief Hide the panel if visible
	* @author Christian Reiner
	*/
	hide:function(handle){
		var dfd = new $.Deferred();
		if ($(handle.Selector).hasClass('fluxx-hidden'))
			dfd.resolve();
		else {
			// trigger action by marking handle with new class
			$(handle.Selector).addClass('fluxx-hidden').removeClass('fluxx-shown')
			// store current handle status inside user preferences
			OC.FluXX.preference(true,'fluxx-status-'+handle.Id,'hidden',null);
		}
		return dfd.promise();
	}, // OC.FluXX.hide
	/**
	 * @method OC.FluXX.init
	 * @brief Initializes state of the handles
	 * @author Christian Reiner
	 */
	init:function(){
		// dynamically load stylesheet to make sure it is loaded LAST
		// we detect owncloud version 6 by its additional 'wrapper' div inside the list of apps in the navigation
		if (0===$('#navigation #apps div.wrapper').length)
				 OC.addStyle('fluxx_compensator','dynamic-5');
		else OC.addStyle('fluxx_compensator','dynamic-6');
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
        event.preventDefault();
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
				// move handle up and down with the corresponding anchor element (the navigation panel in this case)
				handle.Position.Margin=parseInt($(handle.Selector).css('margin-top').replace(/[^-\d\.]/g, ''));
				; // case 'N';
		} // switch
		// general orientation specific values
		if (OC.FluXX.C_HORIZONTAL==handle.Orientation){
			handle.Position.Min=parseInt($(handle.Anchor).css('padding-top').replace(/[^-\d\.]/g, ''))
													+handle.Offset-handle.Position.Margin;
			handle.Position.Max=$(handle.Anchor).outerHeight()-$(handle.Anchor).position().top
													-$(handle.Selector).outerHeight()-handle.Preset-handle.Position.Margin;
		}
		else{
			handle.Position.Min=parseInt($(handle.Anchor).css('padding-left').replace(/[^-\d\.]/g, ''))
													+handle.Offset-handle.Position.Margin;
			handle.Position.Max=$(handle.Anchor).outerWidth()-$(handle.Anchor).position().left
													-$(handle.Selector).outerWidth()-handle.Preset-handle.Position.Margin;
		}
	}, // OC.FluXX.limit
	/**
	 * @method OC.FluXX.maximize
	 * @brief Reposition handle "close to max position" if it has been there before
	 * @param object handle: handle object as defined class internal
	 * @author Christian Reiner
	 */
	maximize:function(handle){
		// consider all handles
		$.each(OC.FluXX.Handle, function(){
			// act for all handles except the triggering one
			if (this!=handle){
				var closeToMax=(this.Position.Max-this.Position.Val);
				OC.FluXX.limit(this);
				// reposition close to max if been there before, just within limits otherwise
				var position=(closeToMax>20) ? this.Position.Val : this.Position.Max-closeToMax;
				OC.FluXX.position(this, position);
			}
		})
	}, // OC.FluXX.maximize
	/**
	* @method OC.FluXX.mode
	* @brief Set global app mode
	* @description
	* Depending on the active app the global html root element is marked with a css class.
	* That class controls any actions or compensations that might be required by that apps page layout.
	* This way all changes and animations can later be done purely in css, as opposed to js.
	* @author Christian Reiner
	*/
	mode:function(){
		// mark mode and active app as class of the html tag
		// this acts like a 'switch' command inside the dynamically loaded css
		var mode={
			bookmarks:       'bookmarks',
			bookmarks_index: 'bookmarks',
			files:           'files',
			files_index:     'files',
			notes:           'notes',
			notes_index:     'notes',
			media:           'media',
			media_index:     'media',
			calendar:        'calendar',
			calendar_index:  'calendar',
			contacts:        'contacts',
			contacts_index:  'contacts',
			gallery:         'gallery',
			gallery_index:   'gallery',
			shorty:          'shorty',
			shorty_index:    'shorty',
			tasks:           'tasks',
			tasks_index:     'tasks'
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
		$(handle.Selector).effect('highlight',{color:'#FFF'},'slow');
		// remove _outer_ reactions (2!) on mouseup
		$(document).off('mouseup');
		$(handle.Selector).off('mouseup');
		// compute limits
		OC.FluXX.limit(handle);
		// react on mouseup
		$(document).on('mouseup',function(){
			// remove _this_ handler
			$(document).off('mouseup');
			// remove reaction on mouse movements
			$(document).off('mousemove');
			// disable cursor move mode
			$('html').removeClass('fluxx-handle-move-'+handle.Id);
			// store final handle position
			OC.FluXX.preference(true,'fluxx-position-'+handle.Id,handle.Position.Val,null);
		});
		// reaction on mouse move: position handle
		$(document).on('mousemove',function(event){
			var delta;
			if (OC.FluXX.C_HORIZONTAL==handle.Orientation){
				// we have to correct the raw vertical mouse position by two factors:
				// 1. half the handles size,
				// 2. the start position of the anchor which is changed by the other handle and
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
		$(handle).css('visibility','hidden');
		// use specified value as new position, but only inside the given limits
		handle.Position.Val=(pos>handle.Position.Max)?handle.Position.Max:((pos<handle.Position.Min)?handle.Position.Min:pos);
		if (OC.FluXX.C_HORIZONTAL==handle.Orientation)
			$(handle.Selector).css('top',handle.Position.Val+'px');
		else
			$(handle.Selector).css('left',handle.Position.Val+'px');
		// show handle after having been repositioned
		$(handle).css('visibility','visible');
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
				;
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
				;
		}
	}, // OC.FluXX.preference
	/**
	* @method OC.FluXX.show
	* @brief Show the panel if visible
	* @author Christian Reiner
	*/
	show:function(handle){
		var dfd = new $.Deferred();
		if ($(handle.Selector).hasClass('fluxx-shown'))
			dfd.resolve();
		else {
			// mark handle with new class
			$(handle.Selector).addClass('fluxx-shown').removeClass('fluxx-hidden')
			// store current handle status inside user preferences
			OC.FluXX.preference(true,'fluxx-status-'+handle.Id,'shown',null);
		}
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
	* @method OC.FluXX.style
	* @brief Add style rule directly to the css definitions instead of using inline styles
	* @author Christian Reiner
	*/
	style:function(selector,rule){
		rule = '{' + rule + '}'
		var stylesheet = document.styleSheets[0];
		if (stylesheet.insertRule) {
			stylesheet.insertRule(selector + rule, stylesheet.cssRules.length);
		} else if (stylesheet.addRule) {
			stylesheet.addRule(selector, rule, -1);
		}
	},
	/**
	* @method OC.FluXX.swap
	* @brief Swaps the mode of the app between hidden and shown
	* @author Christian Reiner
	*/
	swap: function(handle){
		var dfd = new $.Deferred();
		// delay resolution until the animations have finished
		var events='transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd';
		// call action depending on the current mode
		if ($(handle.Selector).hasClass('fluxx-shown')) {
			$('.fluxx-handle').one(events,dfd.resolve);
			OC.FluXX.hide(handle);
			OC.FluXX.state(handle,false);
		} else {
			$('.fluxx-handle').one(events,dfd.resolve);
			OC.FluXX.show(handle);
			OC.FluXX.state(handle,true);
		}
		return dfd.promise();
	}, // OC.FluXX.swap
	/**
	* @method OC.FluXX.time
	* @brief waits for the transition style to load before starting the actual swapping of a handle
	* @author Christian Reiner
	*/
	time: function(handle){
		var dfd = new $.Deferred();
		// swap handle (animation)
		$.when(
			OC.FluXX.swap(handle)
		).done(function(){
			OC.FluXX.maximize(handle);
			// remove temporarily included transition rules
			$('head #fluxx-transitions').remove();
			dfd.resolve();
		}).fail(dfd.reject);
		return dfd.promise();
	}, // OC.FluXX.time
	/**
	* @method OC.FluXX.toggle
	* @brief Toggles the visibility of the navigation area
	* @author Christian Reiner
	*/
	toggle: function(handle){
		// temporarily include transition style rules if not yet present (should not be!)
		var transitions=OC.FluXX.transitions.clone().attr('rel','stylesheet').attr('id','fluxx-transitions').appendTo('head');
		// some safety catch for browsers that do not fire the load event when stuff is loaded (safari)
		var timer = setTimeout(function(){$('head #fluxx-transitions').off('load');OC.FluXX.time(handle);},500); // should be preloaded...
		// the more elegant approach however is to react on the load event (_if_ fired)
		$('head #fluxx-transitions').one('load',function(){clearTimeout(timer);OC.FluXX.time(handle);});
	} // OC.FluXX.toggle
} // OC.FluXX


$(document).ready(function(){
  // set a few default style rules to make transitions work in firefox
  OC.FluXX.defaults();
	// setup a prefetch relation to prepare toggling the transition styles without having to load them later
	OC.FluXX.transitions=$('<link/>',{
// 		'id':'fluxx-transitions',
		'rel':'prefetch',
		'type':'text/css',
		'href':OC.filePath('fluxx_compensator','css','transitions.css')
	});
	OC.FluXX.transitions.appendTo('head');
	// setup handle objects
	if ($('body#body-user, body#body-settings').length){
		if ($('body header > #header').length)
			OC.FluXX.Handle['H']=OC.FluXX.create('H', OC.FluXX.C_VERTICAL, 'body header > #header', 0, 0);
		if ($('body nav > #navigation').length)
			OC.FluXX.Handle['N']=OC.FluXX.create('N', OC.FluXX.C_HORIZONTAL, 'body nav > #navigation', 0, 0);
	}
	// initialize handles
	OC.FluXX.init();
	// initialize logic
	OC.FluXX.mode();
	// reposition the handles upon resize of the window
	$(window).on('resize',function(){
		$.each(OC.FluXX.Handle, function(){
			OC.FluXX.maximize(this);
		});
	});
})
