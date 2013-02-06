/**
* @package fluxx-compensator an ownCloud app
* @category base
* @author Christian Reiner
* @copyright 2012-2013 Christian Reiner <foss@christian-reiner.info>
* @license GNU Affero General Public license (AGPL)
* @link information
* @link repository https://svn.christian-reiner.info/svn/app/oc/fluxx-compensator
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
	OC.FluXX.Handle['H']=OC.FluXX.create('H', OC.FluXX.C_VERTICAL,   1, 'body > header > #header');
	OC.FluXX.Handle['N']=OC.FluXX.create('N', OC.FluXX.C_HORIZONTAL, 1, 'body > nav > #navigation');
	// initialize created handles
	$.each(OC.FluXX.Handle, function(){
		var handle=this;
		// hide or show the navigation in a persistent manner
		OC.AppConfig.getValue('fluxx_compensator','fluxx-status-'+handle.Id,'shown',function(status){
			if ('hidden'==status){
				OC.FluXX.hide(handle);
				OC.FluXX.state(handle, false);}
			else{
				OC.FluXX.show(handle);
				OC.FluXX.state(handle, true);}
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
	// reposition the handles upon resize of the window
	$(window).on('resize',function(){
		$.each(OC.FluXX.Handle, function(){
			var handle=this;
			var closeToMax=(handle.Position.Max-handle.Position.Val);
			OC.FluXX.limit(handle);
			// reposition close to max if been there before, just within limits otherwise
			var position=(closeToMax>10)?handle.Position.Val:handle.Position.Max-closeToMax;
			OC.FluXX.position(handle, position);
		});
	});
})

/**
 * @class OC.FluXX
 * @brief Activity implementation library
 * @author Christian Reiner
 */
OC.FluXX={
	C_WAIT: 500,
	C_HORIZONTAL:	false,
	C_VERTICAL:		true,
	/**
	* @object OC.FluXX.Handle
	* @brief Static reference to the handle object inside the DOM
	* @author Christian Reinerowncloud-core-master-2013-01-08/apps/fluxx_compensator
	*/
	Handle:{},
	/**
	* @method OC.FluXX.click
	* @brief Handle click actions on a handle object
	* @param object handle: handle object as defined clas internal
	* @author Christian Reiner
	*/
	click:function(handle){
		// 1.) click => toggle navigation hidden or shown
		// 2.) hold => enter vertical handle move mode
		// so only enter move mode after holding mouse down for an amount of time
		var timer=setTimeout(function(){
			OC.FluXX.move(handle);
		},OC.FluXX.C_WAIT);
		// raise normal click handling
		$(handle.Selector).on('mouseup',function(){
			// remove _this_ handler
			$(handle.Selector).off('mouseup');
			// start click reaction
			OC.FluXX.toggle(handle);
		});
		// make sure to cancel move mode if mouse is released before C_WAIT duration has passed (500ms)
		$(document).on('mouseup',function(){
			// don't enter move mode
			clearTimeout(timer);
			// remove _this_ handler
			$(document).off('mouseup');
			// remove _above_ handler
			$(handle.Selector).off('mouseup');
		});
		return false;
	}, // OC.FluXX.clickhandle.Id
	/**
	* @method OC.FluXX.create
	* @brief Hide the navigation area if visible
	* @author Christian Reiner
	*/
	create:function(id, orientation, offset, anchor){
		var handle={};
		handle.Anchor=anchor;
		handle.Id=id;
		handle.Offset=offset;
		handle.Orientation=orientation;
		handle.Position={Val:0,Min:0,Max:0};
		handle.Selector='body #fluxx-'+id;
		// compute position limits
		OC.FluXX.limit(handle);
		// generate DOM node
		OC.FluXX.generate(handle);
		// position handle object
		OC.AppConfig.getValue('fluxx_compensator','fluxx-position-'+handle.Id,handle.Position.Max,function(pos){
			OC.FluXX.position(handle, pos);
		});
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
			node.css('left',($(handle.Anchor).outerWidth()-1)+'px');
		}
		else{
			node.addClass('fluxx-vertical');
			node.css('top',($(handle.Anchor).outerHeight()-1)+'px');
		}
		$(handle.Anchor).append(node);
	}, // OC.FluXX.generate
	/**
	* @method OC.FluXX.hide
	* @brief Hide the navigation area if visible
	* @author Christian Reiner
	*/
	hide:function(handle){
		var dfd = new $.Deferred();
		OC.FluXX.stylish(handle);
		if ($(handle.Selector).hasClass('fluxx-shown')){
			$.when(
				$(handle.Selector).addClass('fluxx-hidden'),
				$(handle.Selector).removeClass('fluxx-shown')
			).done(function(){
				dfd.resolve();
				// store current handle status inside user preferences
				OC.AppConfig.setValue('fluxx_compensator','fluxx-status-'+handle.Id,'hidden');
			}).fail(dfd.reject)}
		else dfd.resolve();
		return dfd.promise();
	}, // OC.FluXX.hide
	/**
	* @method OC.FluXX.limit
	* @brief Hide the navigation area if visible
	* @author Christian Reiner
	*/
	limit:function(handle){
		handle.Position.Min=handle.Offset;
		if (OC.FluXX.C_HORIZONTAL==handle.Orientation)
			handle.Position.Max=$(handle.Anchor).outerHeight()-$(handle.Anchor).position().top-handle.Offset-$(handle.Selector).outerHeight();
		else
			handle.Position.Max=$(handle.Anchor).outerWidth()-$(handle.Anchor).position().top-handle.Offset-$(handle.Selector).outerWidth();
	}, // OC.FluXX.limit
	/**
	* @method OC.FluXX.move
	* @brief Hide the navigation area if visible
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
			OC.AppConfig.setValue('fluxx_compensator','fluxx-position-'+handle.Id,handle.Position.Val);
		});
		// reaction on mouse move: position handle
		$(document).on('mousemove',function(event){
			if (OC.FluXX.C_HORIZONTAL==handle.Orientation){
				// we have to correct the raw mouse position by two factors: 
				// 1. half the handles size and 2. the start position of the anchor which is changed by the other handle
				var delta=($(handle.Selector).height()/2);
				if (handle.Orientation==OC.FluXX.C_HORIZONTAL)
					delta+=$(handle.Anchor).position().top;
				OC.FluXX.position(handle, event.pageY-delta);
				handle.Position.Val=$(handle.Selector).position().top;
			}
			else{
				OC.FluXX.position(handle, event.pageX-$(handle.Selector).width()/2);
				handle.Position.Val=$(handle.Selector).position().left;
			}
		});
	}, // OC.FluXX.move
	/**
	* @method OC.FluXX.position
	* @brief Hide the navigation area if visible
	* @author Christian Reiner
	*/
	position:function(handle, pos){
		// hide handle whilst being repositioned
		$(handle.Anchor).css('overflow','hidden !important');
		// use specified x as new position, ob only inside the given limits
		handle.Position.Val=(pos>handle.Position.Max)?handle.Position.Max:((pos<handle.Position.Min)?handle.Position.Min:pos);
		if (OC.FluXX.C_HORIZONTAL==handle.Orientation)
			$(handle.Selector).css('top',handle.Position.Val+'px');
		else
			$(handle.Selector).css('left',handle.Position.Val+'px');
		// show handle after having been repositioned
		$(handle.Anchor).css('overflow','visible');
	}, // OC.FluXX.position
	/**
	* @method OC.FluXX.show
	* @brief Hide the navigation area if visible
	* @author Christian Reiner
	*/
	show:function(handle){
		var dfd = new $.Deferred();
		OC.FluXX.stylish(handle);
		if ($(handle.Selector).hasClass('fluxx-hidden')){
			$.when(
				$(handle.Selector).addClass('fluxx-shown'),
				$(handle.Selector).removeClass('fluxx-hidden')
			).done(function(){
				dfd.resolve();
				// store current handle status inside user preferences
				OC.AppConfig.setValue('fluxx_compensator','fluxx-status-'+handle.Id,'shown');
			}).fail(dfd.reject)}
		else dfd.resolve();
		return dfd.promise();
	}, // OC.FluXX.show
	/**
	* @method OC.FluXX.state
	* @brief mark current state of the compensator
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
	* @method OC.FluXX.stylish
	* @brief Hide the navigation area if visible
	* @author Christian Reiner
	*/
	stylish:function(handle){
		// dynamically load stylesheet to make sure it is loaded LAST
		OC.addStyle('fluxx_compensator','dynamic');
		// mark mode and active app as class of the html tag
		// this acts like a 'switch' command inside the dynamically loaded css
		var mode={
			files_index:	'files',
			notes_index:	'notes',
			media_index:	'media',
			calendar_index:	'calendar',
			contacts_index:	'contacts',
			gallery_index:	'gallery',
			shorty_index:	'shorty'
		};
		var index=$(handle.Selector).find('#apps .active').parents('li').attr('data-id');
		// mark current mode (active app) as class of the html element
		if (index && mode[index]){
			$('html').addClass('fluxx-mode-'+mode[index]);
		}else{
			$('html').addClass('fluxx-modeless');
		}
	}, // OC.FluXX.stylish
	/**
	* @method OC.FluXX.swap
	* @brief Swaps the mode of the app between hidden and shown
	* @author Christian Reiner
	*/
	swap: function(handle){
		var dfd = new $.Deferred();
		// call action depending on the current mode
		if ($(handle.Selector).hasClass('fluxx-shown')){
			$.when(
				OC.FluXX.hide(handle),
				OC.FluXX.state(handle,false)
			).done(dfd.resolve)}
		else{
			$.when(
				OC.FluXX.show(handle),
				OC.FluXX.state(handle,true)
			).done(dfd.resolve)}
		// make sure temporary transition style rules are removed, preferably upon event, time based as catchall
		var timer=setTimeout(function(){$('head link#fluxx-transitions').remove();},10000);
		$(handle.Selector).on('webkitTransitionEnd oTransitionEnd transitionEnd',function(){
			clearTimeout(timer);
			$('head link#fluxx-transitions').remove();
		});
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
		else
			$('<link/>',{
				id:'fluxx-transitions',
				rel:'stylesheet',
				type:'text/css',
				href:OC.filePath('fluxx_compensator','css','transitions.css'),
				onLoad:"OC.FluXX.swap(OC.FluXX.Handle['"+handle.Id+"']);"
			}).appendTo('head');
		return dfd.promise();
	} // OC.FluXX.toggle
}
