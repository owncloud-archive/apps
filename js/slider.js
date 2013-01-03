/**
* @package navigation-slider an ownCloud app
* @category base
* @author Christian Reiner
* @copyright 2012-2013 Christian Reiner <foss@christian-reiner.info>
* @license GNU Affero General Public license (AGPL)
* @link information
* @link repository https://svn.christian-reiner.info/svn/app/oc/navigation-slider
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
	var slider=$('<span id="navigation-slider" class="navigation-slider-shown">');
	var img   =$('<img  id="navigation-slider" draggable="false">');
	img.attr('src',OC.filePath('navigation_slider','img','actions/slide-left.png'));
	slider.append(img);
	// inject slider object into navigation areaa
	$('#navigation').append(slider);
	// store some references to slider and moved objects
	OC.NavigationSlider.Handle=$('#navigation-slider');
	OC.NavigationSlider.Offset=$('#navigation').css('width');
	OC.NavigationSlider.Move=$('#navigation,#content');
	OC.NavigationSlider.Zoom=$('#content');
	// position slider object horizontally
	$('#navigation-slider').css('left',$('#navigation').css('width'));
	// position slider object vertically
	// for this we consider a default value, an optional stored value and min and max values
	var topMin=36;
	var topMax=$('#navigation').height()-$('#navigation').position().top-37;
	OC.AppConfig.getValue('navigation-slider','navigation-slider-position',topMax,function(top){
		top=(top>topMax)?topMax:((top<topMin)?topMin:top);
		$('#navigation-slider').css('top',top+'px');
		// visualize handle by allowing overflow of the navigation area
		$('#navigation').css('overflow','visible');
	});
	// a few corrections to general style defintions
	$('#controls').css({position:'relative',top:0});
	// hide or show the navigation in a persistent manner
	OC.AppConfig.getValue('navigation-slider','navigation-slider-status','shown',function(status){
		if ('hidden'==status)
			OC.NavigationSlider.hide();
		else
			OC.NavigationSlider.show();
	});
	// mouse reactions:
	// 1.) click => toggle navigation hidden or shown
	// 2.) hold => enter vertical handle move mode
	OC.NavigationSlider.Handle.on('mousedown',function(){
		// only enter move mdoe after holding mouse down 1 second
		var timer=setTimeout(function(){
			// enable cursor move mode
			$('html').addClass('navigation-slider-handle-move');
			OC.NavigationSlider.Handle.effect('highlight',{color:'#FFF'},300);
			// remove _outer_ reactions (2!) on mouseup
			$(document).off('mouseup');
			OC.NavigationSlider.Handle.off('mouseup');
			// react on mouseup
			$(document).on('mouseup',function(){
				// remove _this_ handler
				$(document).off('mouseup');
				// remove reaction on mouse movements
				$(document).off('mousemove');
				// disable cursor move mode
				$('html').removeClass('navigation-slider-handle-move');
				OC.NavigationSlider.Handle.css('cursor','pointer');
				OC.NavigationSlider.Handle.find('img').css('cursor','inherit');
				// store final handle position
				OC.AppConfig.setValue('navigation-slider','navigation-slider-position',OC.NavigationSlider.Handle.position().top);
			});
			// reaction on mouse move: position handle
			$(document).on('mousemove',function(event){
				var top=event.pageY-60;
				top=(top>topMax)?topMax:((top<topMin)?topMin:top);
				OC.NavigationSlider.Handle.css('top',top+'px');
			});
		},1000);
		// raise normal click handling
		OC.NavigationSlider.Handle.on('mouseup',function(){
			// remove _this_ handler
			OC.NavigationSlider.Handle.off('mouseup');
			// start click reaction
			OC.NavigationSlider.toggle();
		});
		// make sure to cancel move mode if mouse is released before 1 second has passed
		$(document).on('mouseup',function(){
			// don't enter move mode
			clearTimeout(timer);
			// remove _this_ handler
			$(document).off('mouseup');
			// remove _above_ handler
			OC.NavigationSlider.Handle.off('mouseup');
		});
	});
})

/**
 * @class OC.NavigationSlider
 * @brief Activity implementation library
 * @author Christian Reiner
 */
OC.NavigationSlider={
	/**
	* @object OC.NavigationSlider.Handle
	* @brief Static reference to the slider object inside the DOM
	* @author Christian Reiner
	*/
	Handle:{},
	/**
	* @object OC.NavigationSlider.Move
	* @brief Static reference to the objects inside the DOM that must be moved
	* @author Christian Reiner
	*/
	Move:{},
	/**
	* @object OC.NavigationSlider.Offset
	* @brief Offset value the pages content gets moved by (width of navigation area)
	* @author Christian Reiner
	*/
	Offset:{},
	/**
	* @object OC.NavigationSlider.Zoom
	* @brief Static reference to the objects inside the DOM that must be scaled
	* @author Christian Reiner
	*/
	Zoom:{},
	/**
	* @method OC.NavigationSlider.hide
	* @brief Hide the navigation area if visible
	* @author Christian Reiner
	*/
	hide:function(){
		var dfd = new $.Deferred();
		if (OC.NavigationSlider.Handle.hasClass('navigation-slider-shown')){
			$.when(
				OC.NavigationSlider.Handle.addClass('navigation-slider-hidden'),
				OC.NavigationSlider.Handle.removeClass('navigation-slider-shown'),
				OC.NavigationSlider.Zoom.animate({width:"+="+OC.NavigationSlider.Offset},'fast'),
				OC.NavigationSlider.Move.animate({left: "-="+OC.NavigationSlider.Offset},'fast')
			).done(function(){
				dfd.resolve();
				OC.NavigationSlider.Handle.find('img')
					.attr('src',OC.filePath('navigation_slider','img','actions/slide-right.png'));
				// store current slider status inside user preferences
				OC.AppConfig.setValue('navigation-slider','navigation-slider-status','hidden');
			}).fail(dfd.reject)}
		else dfd.resolve();
		return dfd.promise();
	}, // OC.NavigationSlider.hide
	/**
	* @method OC.NavigationSlider.show
	* @brief Hide the navigation area if visible
	* @author Christian Reiner
	*/
	show:function(){
		var dfd = new $.Deferred();
		if (OC.NavigationSlider.Handle.hasClass('navigation-slider-hidden')){
			$.when(
				OC.NavigationSlider.Handle.addClass('navigation-slider-shown'),
				OC.NavigationSlider.Handle.removeClass('navigation-slider-hidden'),
				OC.NavigationSlider.Zoom.animate({width:"-="+OC.NavigationSlider.Offset},'fast'),
				OC.NavigationSlider.Move.animate({left: "+="+OC.NavigationSlider.Offset},'fast')
			).done(function(){
				dfd.resolve();
				OC.NavigationSlider.Handle.find('img')
					.attr('src',OC.filePath('navigation_slider','img','actions/slide-left.png'));
				// store current slider status inside user preferences
				OC.AppConfig.setValue('navigation-slider','navigation-slider-status','shown');
			}).fail(dfd.reject)}
		else dfd.resolve();
		return dfd.promise();
	}, // OC.NavigationSlider.show
	/**
	* @method OC.NavigationSlider.toggle
	* @brief Toggles the visibility of the navigation area
	* @author Christian Reiner
	*/
	toggle: function(){
		var dfd = new $.Deferred();
		if (OC.NavigationSlider.Handle.hasClass('navigation-slider-shown')){
			$.when(
				OC.NavigationSlider.hide()
			).done(dfd.resolve)}
		else{
			$.when(
				OC.NavigationSlider.show()
			).done(dfd.resolve)}
		return dfd.promise();
	} // OC.NavigationSlider.toggle
}
