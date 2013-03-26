/**
* @package shorty an ownCloud url shortener plugin
* @category internet
* @author Christian Reiner
* @copyright 2011-2013 Christian Reiner <foss@christian-reiner.info>
* @license GNU Affero General Public license (AGPL)
* @link information http://apps.owncloud.com/content/show.php/Shorty?content=150401
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
 * @file js/init.js
 * @brief Client side initialization of desktop actions
 * @description
 * This file takes care of initializing all activities required by the app to
 * work as expected, namely reactions to events like mouse cicks, keyboard 
 * presses and the like.
 * @author Christian Reiner
 */

$(document).ready(function(){
	// initialize status dictionary since that _might_ require an ajax request
	OC.Shorty.Status.fetch();
	// TODO: OC4 compatibility: remove following setInterval command when dropping OC4 compatibility
	// refresh the ajax request token in regular intervals
	// required to make use of long lasting sessions whilst using CSRF protection with a small tokens lifetime
	// handle this inside the app only, if the feature is NOT present in OC core
 	if (typeof OC.Request==undefined){
		if (OC.Shorty.Debug)
			OC.Shorty.Debug.log("Info","relying on app internal implementation to refresh the request token");
		setInterval(OC.Shorty.Request.Refresh, 1000*60*56.87); // ~57 minutes, close to the timeout of 1 hour
		// again: note that this is not required from OC 4.5 on upwards
		// Shortys token refresh strategy has been accepted into the core
	}else{
		if (OC.Shorty.Debug)
			OC.Shorty.Debug.log("Info","relying on core implementation to refresh the request token");
	}
	// close any open dialog when the canvas is clicked
	$(document).on('click','#content>*',[],function(e){e.stopPropagation();});
	$(document).on('click','#content',[],function(){
		$.each($('.shorty-dialog:visible'),function(){
			OC.Shorty.WUI.Dialog.hide($(this));
		});
	});
	// make messengers closeable
	$(document).on('click','#content .shorty-messenger',[],function(){
		OC.Shorty.WUI.Messenger.hide($(this));
	});
	// button to open the 'add' dialog
	$(document).on('click','#controls #add',[],function(){
		OC.Shorty.WUI.Dialog.toggle($('#dialog-add'))
	});
	// close button in dialogs
	$(document).on('click','.shorty-dialog #close',[],function(){
		OC.Shorty.WUI.Dialog.hide($(this).parents('form').first());
	});
	// status selection in embedded share dialog
	$(document).on('change','.shorty-embedded#dialog-share #status',[],function(){
		OC.Shorty.Action.Url.status($(this).siblings('#id').val(),$(this).val());
	});
	// refresh click count when clicking source or relay in embedded share dialog
	$(document).on('click','.shorty-embedded#dialog-share .shorty-usages a#source',[],function(){
		OC.Shorty.WUI.Sums.increment.apply(
			OC.Shorty.Runtime.Context.ListOfShortys,
			[$(this).parents('tr')]);
	});
	// collapsible items in embedded dialogs
	$(document).on('click','.shorty-embedded .shorty-collapsible span',[],function(e){
		var container=$(this).parent();
		if (container.hasClass('collapsed'))
			 container.removeClass('collapsed').find('.shorty-collapsible-tail').slideDown('fast');
		else container.addClass('collapsed').find('.shorty-collapsible-tail').slideUp('fast');
	});
	$(document).on('click','#controls .shorty-dialog #meta #explanation.filled',[], function(e){
		$(e.currentTarget).closest('.shorty-dialog').find('input#title').val($(e.currentTarget).html());
	});
	// button (row click) to open the toolbar row in the list
	$(document).on('click','#list-of-shortys #titlebar',[],function(){
		OC.Shorty.WUI.List.Toolbar.toggle.apply(
			OC.Shorty.Runtime.Context.ListOfShortys,
			[$('#list-of-shortys')]);
	});
	// button to reload the list
	$(document).on('click','#list-of-shortys #toolbar #reload',[],OC.Shorty.WUI.List.build);
	// sort buttons
	$(document).on('click','#list-of-shortys #toolbar shorty-sorter',[],function(){
		OC.Shorty.WUI.List.sort.apply(
			OC.Shorty.Runtime.Context.ListOfShortys,
			[$('#list-of-shortys')]);
	});
	// add date picker options
	$.datepicker.setDefaults({
		dateFormat :'yy-mm-dd',
		changeMonth: true,
		changeYear: true,
// 		minDate: '+1',
		firstDay: 1,
		showOtherMonths: true,
		selectOtherMonths: true,
		showOn: 'button',
		buttonImage: $('#controls #until').first().attr('icon'),
		buttonImageOnly: true
	});
	$('#controls #dialog-add #until:not([readonly])').datepicker();
	// bind usage to the usage icons
	$(document).on('click','#dialog-share img.shorty-usage',[],function(e){
		e.stopPropagation();
		OC.Shorty.WUI.Entry.send(e,$(this));
	});
	$(document).on('click','.shorty-list tbody tr td:not(#actions)',[],function(e){
		// hide any open embedded dialog
		OC.Shorty.WUI.Dialog.hide($('.shorty-embedded').first());
		// highlight clicked entry
		OC.Shorty.WUI.List.highlight($(this).parents('table'),$(this).parent('tr'));
	});
	$(document).on('click','.shorty-list tbody tr td#actions span.shorty-actions a',[],function(e){
		OC.Shorty.WUI.Entry.click(e,$(this));
	});
	// pretty select boxes where applicable (class controlled)
	$('.chosen').chosen();
	// filter actions
	var list=$('#list-of-shortys');
	// title & target filter reaction
	$(document).on('keyup','#list-of-shortys thead tr#toolbar th#target,th#title #filter',[],function(){
		OC.Shorty.WUI.List.filter.apply(
			OC.Shorty.Runtime.Context.ListOfShortys,
			[list,$($(this).context.parentElement.parentElement).attr('id'),$(this).val()]);
	});
	// status filter reaction
	$(document).on('change','#list-of-shortys thead tr#toolbar th#status select',[],function(){
		OC.Shorty.WUI.List.filter.apply(
			OC.Shorty.Runtime.Context.ListOfShortys,
			[list,$(this).parents('th').attr('id'),$(this).find(':selected').val()]);
	});
	// column sorting reaction
	$(document).on('click','#list-of-shortys thead tr#toolbar div img.shorty-sorter',[],function(){
		OC.Shorty.WUI.List.sort(list,$(this).attr('data-sort-code'));
	});
	// open preferences popup when button is clicked
	$(document).on('click keydown','#controls-preferences.settings',[],function() {
		$.when(
			OC.Shorty.Status.versionCompare('>=','4.80')
		).pipe(function(result){
			if (result)
				OC.appSettings({appid:'shorty',loadJS:'preferences.js',scriptName:'preferences.php'});
			else
				window.location.href=OC.linkTo('settings','personal.php');
		});
	});
	// prevent vertical scroll bar in content area triggered by the additional controls bar handle
	$('#content').height(($('#content').height()-$('#controls #controls-handle').height())+'px');
}); // document.ready

