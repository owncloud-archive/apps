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
	// setup limits for the handles position
	OC.FluXX.limit();
	// setup handle object
	OC.FluXX.create();
	// store some references to handle and moved objects
	OC.FluXX.Handle=$('#fluxx');
	OC.FluXX.Offset=$('#navigation').css('width');
	// hide or show the navigation in a persistent manner
	OC.AppConfig.getValue('fluxx_compensator','fluxx-status','shown',function(status){
		if ('hidden'==status)
			OC.FluXX.hide();
		else
			OC.FluXX.show();
	});
	// reposition the handle upon resize of the window
	$(window).on('resize',function(){
		OC.FluXX.limit();
		OC.FluXX.position(OC.FluXX.Position.Y);
	});
	// handle mouse reactions
	// 1.) click => toggle navigation hidden or shown
	// 2.) hold => enter vertical handle move mode
	OC.FluXX.Handle.on('mousedown',function(){
		OC.FluXX.click();
	});
})

/**
 * @class OC.FluXX
 * @brief Activity implementation library
 * @author Christian Reiner
 */
OC.FluXX={
	/**
	* @object OC.FluXX.Handle
	* @brief Static reference to the handle object inside the DOM
	* @author Christian Reiner
	*/
	Handle:{},
	/**
	* @object OC.FluXX.Offset
	* @brief Offset value the pages content gets moved by (width of navigation area)
	* @author Christian Reiner
	*/
	Offset:{},
	/**
	* @object OC.FluXX.Position
	* @brief A set of limits controling the position of the handle
	* @author Christian Reiner
	*/
	Position:{
		Y:0,
		Ymin:0,
		Ymax:0
	},
	/**
	* @method OC.FluXX.click
	* @brief Hide the navigation area if visible
	* @author Christian Reiner
	*/
	click:function(){
		// 1.) click => toggle navigation hidden or shown
		// 2.) hold => enter vertical handle move mode
		// so only enter move mode after holding mouse down for an amount of time
		var timer=setTimeout(function(){
			OC.FluXX.move();
		},500);
		// raise normal click handling
		OC.FluXX.Handle.on('mouseup',function(){
			// remove _this_ handler
			OC.FluXX.Handle.off('mouseup');
			// start click reaction
			OC.FluXX.toggle();
		});
		// make sure to cancel move mode if mouse is released before 1 second has passed
		$(document).on('mouseup',function(){
			// don't enter move mode
			clearTimeout(timer);
			// remove _this_ handler
			$(document).off('mouseup');
			// remove _above_ handler
			OC.FluXX.Handle.off('mouseup');
		});
		return false;
	}, // OC.FluXX.click
	/**
	* @method OC.FluXX.create
	* @brief Hide the navigation area if visible
	* @author Christian Reiner
	*/
	create:function(){
		// construct handle object
		var handle=$('<span id="fluxx" class="fluxx-shown" />');
		var img   =$('<img  id="fluxx" class="svg" draggable="false">');
		img.attr('src',OC.filePath('fluxx_compensator','img','actions/left.svg'));
		handle.append(img);
		// inject handle object into navigation areaa
		$('body > nav > #navigation').append(handle);
		// position handle object horizontally
		$('#fluxx').css('left',$('body > nav > #navigation').css('width'));
		// position handle object vertically
		OC.AppConfig.getValue('fluxx_compensator','fluxx-position',OC.FluXX.Position.Ymax,function(y){
			OC.FluXX.position(y);
		});
	}, // OC.FluXX.create
	/**
	* @method OC.FluXX.hide
	* @brief Hide the navigation area if visible
	* @author Christian Reiner
	*/
	hide:function(){
		var dfd = new $.Deferred();
		OC.FluXX.stylish(false);
		if (OC.FluXX.Handle.hasClass('fluxx-shown')){
			$.when(
				OC.FluXX.Handle.addClass('fluxx-hidden'),
				OC.FluXX.Handle.removeClass('fluxx-shown')
			).done(function(){
				dfd.resolve();
				OC.FluXX.Handle.find('img')
					.attr('src',OC.filePath('fluxx_compensator','img','actions/right.svg'));
				// store current handle status inside user preferences
				OC.AppConfig.setValue('fluxx_compensator','fluxx-status','hidden');
			}).fail(dfd.reject)}
		else dfd.resolve();
		return dfd.promise();
	}, // OC.FluXX.hide
	/**
	* @method OC.FluXX.limit
	* @brief Hide the navigation area if visible
	* @author Christian Reiner
	*/
	limit:function(){
		OC.FluXX.Position.Ymin=37;
		OC.FluXX.Position.Ymax=$('body > nav > #navigation').height()-$('body > nav > #navigation').position().top-37;
	}, // OC.FluXX.limit
	/**
	* @method OC.FluXX.move
	* @brief Hide the navigation area if visible
	* @author Christian Reiner
	*/
	move:function(){
		// enable cursor move mode
		$('html').addClass('fluxx-handle-move');
		OC.FluXX.Handle.effect('highlight',{color:'#FFF'},800);
		// remove _outer_ reactions (2!) on mouseup
		$(document).off('mouseup');
		OC.FluXX.Handle.off('mouseup');
		// react on mouseup
		$(document).on('mouseup',function(){
			// remove _this_ handler
			$(document).off('mouseup');
			// remove reaction on mouse movements
			$(document).off('mousemove');
			// disable cursor move mode
			$('html').removeClass('fluxx-handle-move');
			OC.FluXX.Handle.css('cursor','pointer');
			OC.FluXX.Handle.find('img').css('cursor','inherit');
			// store final handle position
			OC.AppConfig.setValue('fluxx_compensator','fluxx-position',OC.FluXX.Handle.position().top);
		});
		// reaction on mouse move: position handle
		$(document).on('mousemove',function(event){
			OC.FluXX.position(event.pageY-60);
		});
	}, // OC.FluXX.move
	/**
	* @method OC.FluXX.position
	* @brief Hide the navigation area if visible
	* @author Christian Reiner
	*/
	position:function(y){
		// hide handle whilst being repositioned
		$('body > nav > #navigation').css('overflow','hidden !important');
		OC.FluXX.Position.Y=(y>OC.FluXX.Position.Ymax)?OC.FluXX.Position.Ymax:((y<OC.FluXX.Position.Ymin)?OC.FluXX.Position.Ymin:y);
		$('#fluxx').css('top',OC.FluXX.Position.Y+'px');
		// show handle after having been repositioned
		$('body > nav > #navigation').css('overflow','visible');
	}, // OC.FluXX.position
	/**
	* @method OC.FluXX.show
	* @brief Hide the navigation area if visible
	* @author Christian Reiner
	*/
	show:function(){
		var dfd = new $.Deferred();
		OC.FluXX.stylish(true);
		if (OC.FluXX.Handle.hasClass('fluxx-hidden')){
			$.when(
				OC.FluXX.Handle.addClass('fluxx-shown'),
				OC.FluXX.Handle.removeClass('fluxx-hidden')
			).done(function(){
				dfd.resolve();
				OC.FluXX.Handle.find('img')
					.attr('src',OC.filePath('fluxx_compensator','img','actions/left.svg'));
				// store current handle status inside user preferences
				OC.AppConfig.setValue('fluxx_compensator','fluxx-status','shown');
			}).fail(dfd.reject)}
		else dfd.resolve();
		return dfd.promise();
	}, // OC.FluXX.show
	/**
	* @method OC.FluXX.stylish
	* @brief Hide the navigation area if visible
	* @author Christian Reiner
	*/
	stylish:function(shown){
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
		var index=$('body > nav > #navigation #apps .active').parents('li').attr('data-id');
		// mark current mode (active app) as class of the html element
		if (index && mode[index]){
			$('html').addClass('fluxx-mode-'+mode[index]);
		}else{
			$('html').addClass('fluxx-modeless');
		}
		// mark the current state (hidden or shown) as class of the html element
		if (shown){
			$('html').removeClass('fluxx-state-hidden').addClass('fluxx-state-shown');
		}else{
			$('html').removeClass('fluxx-state-shown').addClass('fluxx-state-hidden');
		}
	}, // OC.FluXX.stylish
	/**
	* @method OC.FluXX.swap
	* @brief Swaps the mode of the app between hidden and shown
	* @author Christian Reiner
	*/
	swap: function(){
		var dfd = new $.Deferred();
		// call action depending on the current mode
		if (OC.FluXX.Handle.hasClass('fluxx-shown')){
			$.when(
				OC.FluXX.hide()
			).done(dfd.resolve)}
		else{
			$.when(
				OC.FluXX.show()
			).done(dfd.resolve)}
		// make sure temporary transition style rules are removed, preferably upon event, time based as catchall
		var timer=setTimeout(function(){$('head link#fluxx-transitions').remove();},10000);
		$('body > nav > #navigation').on('webkitTransitionEnd oTransitionEnd transitionEnd',function(){
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
	toggle: function(){
		var dfd = new $.Deferred();
		// temporarily include transition style rules if not yet present (should not be!)
		if ($('head link#fluxx-transitions').length)
			OC.FluXX.swap();
		else
			$('<link/>',{
				id:'fluxx-transitions',
				rel:'stylesheet',
				type:'text/css',
				href:OC.filePath('fluxx_compensator','css','transitions.css'),
				onLoad:'OC.FluXX.swap()'
			}).appendTo('head');
		return dfd.promise();
	} // OC.FluXX.toggle
}
