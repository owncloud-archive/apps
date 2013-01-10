/**
* @package flux-compensator an ownCloud app
* @category base
* @author Christian Reiner
* @copyright 2012-2013 Christian Reiner <foss@christian-reiner.info>
* @license GNU Affero General Public license (AGPL)
* @link information
* @link repository https://svn.christian-reiner.info/svn/app/oc/flux-compensator
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
 * @file js/slider.js
 * @brief Client side activity library
 * @author Christian Reiner
 */

// add slider to navigation area
$(document).ready(function(){
	// construct slider object
	var slider=$('<span id="flux-compensator" class="flux-compensator-shown">');
	var img   =$('<img  id="flux-compensator" class="svg" draggable="false">');
	img.attr('src',OC.filePath('flux_compensator','img','actions/slide-left.svg'));
	slider.append(img);
	// inject slider object into navigation areaa
	$('#navigation').append(slider);
	// store some references to slider and moved objects
	OC.Flux.Handle=$('#flux-compensator');
	OC.Flux.Offset=$('#navigation').css('width');
	OC.Flux.Move=$('#navigation,#content');
	OC.Flux.Zoom=$('#content');
	// position slider object horizontally
	$('#flux-compensator').css('left',$('#navigation').css('width'));
	// position slider object vertically
	// for this we consider a default value, an optional stored value and min and max values
	var topMin=37;
	var topMax=$('#navigation').height()-$('#navigation').position().top-37;
	OC.AppConfig.getValue('flux-compensator','flux-compensator-position',topMax,function(top){
		top=(top>topMax)?topMax:((top<topMin)?topMin:top);
		$('#flux-compensator').css('top',top+'px');
		// visualize handle by allowing overflow of the navigation area
		$('#navigation').css('overflow','visible');
	});
	// hide or show the navigation in a persistent manner
	OC.AppConfig.getValue('flux-compensator','flux-compensator-status','shown',function(status){
		if ('hidden'==status)
			OC.Flux.hide();
		else
			OC.Flux.show();
	});
	// handle mouse reactions
	// 1.) click => toggle navigation hidden or shown
	// 2.) hold => enter vertical handle move mode
	OC.Flux.Handle.on('mousedown',function(){
		OC.Flux.click(topMin,topMax);
	});
})

/**
 * @class OC.Flux
 * @brief Activity implementation library
 * @author Christian Reiner
 */
OC.Flux={
	/**
	* @object OC.Flux.Handle
	* @brief Static reference to the slider object inside the DOM
	* @author Christian Reiner
	*/
	Handle:{},
	/**
	* @object OC.Flux.Move
	* @brief Static reference to the objects inside the DOM that must be moved
	* @author Christian Reiner
	*/
	Move:{},
	/**
	* @object OC.Flux.Offset
	* @brief Offset value the pages content gets moved by (width of navigation area)
	* @author Christian Reiner
	*/
	Offset:{},
	/**
	* @object OC.Flux.Zoom
	* @brief Static reference to the objects inside the DOM that must be scaled
	* @author Christian Reiner
	*/
	Zoom:{},
	/**
	* @method OC.Flux.click
	* @brief Hide the navigation area if visible
	* @author Christian Reiner
	*/
	click:function(topMin,topMax){
		// 1.) click => toggle navigation hidden or shown
		// 2.) hold => enter vertical handle move mode
		// so only enter move mode after holding mouse down for an amount of time
		var timer=setTimeout(function(){
			// enable cursor move mode
			$('html').addClass('flux-compensator-handle-move');
			OC.Flux.Handle.effect('highlight',{color:'#FFF'},300);
			// remove _outer_ reactions (2!) on mouseup
			$(document).off('mouseup');
			OC.Flux.Handle.off('mouseup');
			// react on mouseup
			$(document).on('mouseup',function(){
				// remove _this_ handler
				$(document).off('mouseup');
				// remove reaction on mouse movements
				$(document).off('mousemove');
				// disable cursor move mode
				$('html').removeClass('flux-compensator-handle-move');
				OC.Flux.Handle.css('cursor','pointer');
				OC.Flux.Handle.find('img').css('cursor','inherit');
				// store final handle position
				OC.AppConfig.setValue('flux-compensator','flux-compensator-position',OC.Flux.Handle.position().top);
			});
			// reaction on mouse move: position handle
			$(document).on('mousemove',function(event){
				var top=event.pageY-60;
				top=(top>topMax)?topMax:((top<topMin)?topMin:top);
				OC.Flux.Handle.css('top',top+'px');
			});
		},500);
		// raise normal click handling
		OC.Flux.Handle.on('mouseup',function(){
			// remove _this_ handler
			OC.Flux.Handle.off('mouseup');
			// start click reaction
			OC.Flux.toggle();
		});
		// make sure to cancel move mode if mouse is released before 1 second has passed
		$(document).on('mouseup',function(){
			// don't enter move mode
			clearTimeout(timer);
			// remove _this_ handler
			$(document).off('mouseup');
			// remove _above_ handler
			OC.Flux.Handle.off('mouseup');
		});
		return false;
	}, // OC.Flux.click
	/**
	* @method OC.Flux.hide
	* @brief Hide the navigation area if visible
	* @author Christian Reiner
	*/
	hide:function(){
		var dfd = new $.Deferred();
		OC.Flux.stylish(false);
		if (OC.Flux.Handle.hasClass('flux-compensator-shown')){
			$.when(
				OC.Flux.Handle.addClass('flux-compensator-hidden'),
				OC.Flux.Handle.removeClass('flux-compensator-shown'),
				OC.Flux.Zoom.animate({width:"+="+OC.Flux.Offset},'fast'),
				OC.Flux.Move.animate({left: "-="+OC.Flux.Offset},'fast')
			).done(function(){
				dfd.resolve();
				OC.Flux.Handle.find('img')
					.attr('src',OC.filePath('flux_compensator','img','actions/slide-right.svg'));
				// store current slider status inside user preferences
				OC.AppConfig.setValue('flux-compensator','flux-compensator-status','hidden');
			}).fail(dfd.reject)}
		else dfd.resolve();
		return dfd.promise();
	}, // OC.Flux.hide
	/**
	* @method OC.Flux.show
	* @brief Hide the navigation area if visible
	* @author Christian Reiner
	*/
	show:function(){
		var dfd = new $.Deferred();
		OC.Flux.stylish(true);
		if (OC.Flux.Handle.hasClass('flux-compensator-hidden')){
			$.when(
				OC.Flux.Handle.addClass('flux-compensator-shown'),
				OC.Flux.Handle.removeClass('flux-compensator-hidden'),
				OC.Flux.Zoom.animate({width:"-="+OC.Flux.Offset},'fast'),
				OC.Flux.Move.animate({left: "+="+OC.Flux.Offset},'fast')
			).done(function(){
				dfd.resolve();
				OC.Flux.Handle.find('img')
					.attr('src',OC.filePath('flux_compensator','img','actions/slide-left.svg'));
				// store current slider status inside user preferences
				OC.AppConfig.setValue('flux-compensator','flux-compensator-status','shown');
			}).fail(dfd.reject)}
		else dfd.resolve();
		return dfd.promise();
	}, // OC.Flux.show
	/**
	* @method OC.Flux.stylish
	* @brief Hide the navigation area if visible
	* @author Christian Reiner
	*/
	stylish:function(shown){
		// dynamically load stylesheet to make sure it is loaded LAST
		OC.addStyle('flux_compensator','dynamic');
		// mark slider-mode and active app as class of the html tag
		// this acts like a 'switch' command inside the dynamically loaded css
		var mode={
			files_index:	'files',
			media_index:	'media',
			calendar_index:	'calendar',
			contacts_index:	'contacts',
			gallery_index:	'gallery',
			shorty_index:	'shorty'
		};
		var index=$('#navigation #apps .active').parents('li').attr('data-id');
		// mark current mode (active app) as class of the html element
		if (index && mode[index]){
			$('html').addClass('ns-mode-'+mode[index]);
		}else{
			$('html').addClass('ns-modeless');
		}
		// mark the current state (hidden or shown) as class of the html element
		if (shown){
			$('html').removeClass('ns-state-hidden').addClass('ns-state-shown');
		}else{
			$('html').removeClass('ns-state-shown').addClass('ns-state-hidden');
		}
	}, // OC.Flux.stylish
	/**
	* @method OC.Flux.toggle
	* @brief Toggles the visibility of the navigation area
	* @author Christian Reiner
	*/
	toggle: function(){
		var dfd = new $.Deferred();
		if (OC.Flux.Handle.hasClass('flux-compensator-shown')){
			$.when(
				OC.Flux.hide()
			).done(dfd.resolve)}
		else{
			$.when(
				OC.Flux.show()
			).done(dfd.resolve)}
		return dfd.promise();
	} // OC.Flux.toggle
}
