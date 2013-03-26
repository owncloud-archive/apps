/**
* @package shorty-tracking an ownCloud url shortener plugin addition
* @category internet
* @author Christian Reiner
* @copyright 2012-2013 Christian Reiner <foss@christian-reiner.info>
* @license GNU Affero General Public license (AGPL)
* @link information http://apps.owncloud.com/content/show.php/Shorty+Tracking?content=152473
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
 * @file js/tracking.js
 * @brief Client side initialization of desktop actions
 * @access public
 * @author Christian Reiner
 */

// we use the late event $(window).load() instead of $(document).ready(), 
// since otherwise the binding of the ajax request token (CSRF protection)
// has not yet finished before we try to use it...
// TODO: OC-4 compatibility: use document.ready instead of window.load when dropping OC-4 compatibility
$(window).load(function(){
	var dfd = new $.Deferred();
	$.when(
		// load layout of dialog to show the list of tracked clicks
		OC.Shorty.Tracking.init()
	).done(function(){
	// bind actions to basic buttons
	OC.Shorty.Tracking.Dialog.List.find('#close').on('click',function(){
		OC.Shorty.WUI.Dialog.hide(OC.Shorty.Tracking.Dialog.List);
	});
	OC.Shorty.Tracking.Dialog.List.find('#list-of-clicks tr#titlebar').on('click',function(){
			OC.Shorty.WUI.List.Toolbar.toggle.apply(
				OC.Shorty.Runtime.Context.ListOfClicks,
				[OC.Shorty.Tracking.Dialog.List.find('#list-of-clicks').first()]
			);
	});
	OC.Shorty.Tracking.Dialog.List.find('#list-of-clicks #toolbar #reload')
		.on('click',function(){OC.Shorty.Tracking.build(false);});
	OC.Shorty.Tracking.Dialog.List.find('#shorty-footer #load')
		.on('click',function(){OC.Shorty.Tracking.build(true);});
	// when clicking inside cells: highlight 'associated' cells: cells with same content
	$(document).on('mouseenter','#list-of-clicks tbody tr td.associative span',[],function(){
		// look for cells inside the same column that have the same content (text)
		var cells=$(this).parents('tbody').find('tr td#'+$(this).parent().attr('id'));
		// add class 'associated' to matching columns
		cells.find('span:contains('+$(this).text()+')').addClass("associated");
		// add class 'DEsociated' to matching columns
		cells.find('span:not(.associated)').addClass("desociated");
	});
	// neutralize the hover effect of the previous lines
	$(document).on('mouseleave','#list-of-clicks tbody tr td.associative span.associated',[],function(){
		$(this).parents('tbody').find('tr td#'+$(this).parent().attr('id')+' span').removeClass("associated").removeClass("desociated");});
	// when clicking inside cells: set column filter
	$(document).on('click','#list-of-clicks tbody tr td.associative span',[],function(){
		var input=$(this).parents('table').find('thead tr#toolbar th#'+$(this).parent().attr('id')).find('input,select');
		var value;
		// open toolbar if still hidden
		if (input.parent().is(':hidden'))
			OC.Shorty.WUI.List.Toolbar.toggle.apply(OC.Shorty.Runtime.Context.ListOfClicks,[$('#list-of-clicks')]);
		// set filter value
		if(input.is('select')){
			// use technical value of the matching option instead of the translated value
			value=input.find('option:contains('+$(this).text()+')').first().attr('value');
			input.val(value).effect('pulsate');
		}else{ // fallback: text input field
			// value is not ranslated but literal, so use it directly
			value=$(this).text();
			input.val(value).effect('pulsate');
		} // if-else
		// apply filter value
		OC.Shorty.WUI.List.filter.apply(
			OC.Shorty.Runtime.Context.ListOfClicks,
			[	OC.Shorty.Tracking.Dialog.List.find('#list-of-clicks').first(),
				$(this).parent().attr('id'),
				input.val()
			]);
	});
	// column filter reaction
	OC.Shorty.Tracking.Dialog.List.find('#list-of-clicks').first()
		.find('thead tr#toolbar').find('th#time,th#address,th#host,th#user').find('#filter')
		.on('keyup',function(){
			OC.Shorty.WUI.List.filter.apply(
				OC.Shorty.Runtime.Context.ListOfClicks,
				[	OC.Shorty.Tracking.Dialog.List.find('#list-of-clicks').first(),
					$($(this).context.parentElement.parentElement).attr('id'),
					$(this).val()
				]
			);
		});
	// detect if the list has been scrolled to the bottom,
	// retrieve next chunk of clicks if so
	OC.Shorty.Tracking.Dialog.List.find('#list-of-clicks').first().find('tbody').on('scroll',OC.Shorty.Tracking.bottom);
	// status filter reaction
	OC.Shorty.Tracking.Dialog.List.find('#list-of-clicks').first().find('thead tr#toolbar th#result select')
		.on('change',function(){
			OC.Shorty.WUI.List.filter.apply(
				OC.Shorty.Runtime.Context.ListOfClicks,
				[	OC.Shorty.Tracking.Dialog.List.find('#list-of-clicks').first(),
					$(this).parents('th').attr('id'),
					$(this).find(':selected').val()
				]
			);
		});
	dfd.resolve();
	}).fail(dfd.reject);
	return dfd.promise();
}); // document.ready

/**
 * @class OC.Shorty.Files
 * @brief Subclass that serves as a collection of methods private to this plugin
 * @author Christian Reiner
 */
OC.Shorty.Tracking=
{
	/**
	* @brief Collection of dialog selectors used as a shortcut during the scripts
	* @access private
	* @author Christian Reiner
	*/
	Dialog:{
		/**
		* @brief Persistent jQuery object holding the list dialog implemented by this plugin
		* @access private
		* @author Christian Reiner
		*/
		List:{},
		/**
		* @brief Persistent jQuery object holding the click dialog implemented by this plugin
		* @access private
		* @author Christian Reiner
		*/
		Click:{}
	},
	/**
	* @brief Persistent referencing the Shorty this plugin currently deals with
	* @access private
	* @author Christian Reiner
	*/
	Entry:{},
	/**
	* @brief Persistent jQuery object describing the list in this plugins dialog
	* @access private
	* @author Christian Reiner
	*/
	Stats:{
		blocked:[],
		denied: [],
		granted:[]
	},
	/**
	* @method OC.Shorty.Tracking.bottom
	* @brief Decides if a scrolling event has reached the bottom of the list
	* @access private
	* @author Christian Reiner
	* @description
	* If the list has been scrolled to its bottom the retrieval of the next chunk of
	* clicks will be triggered.
	*/
	bottom: function(){
		// prevent additional events, whilst processing this one
		OC.Shorty.Tracking.Dialog.List.find('#list-of-clicks').first().find('tbody').off('scroll');
		// attempt to retrieve next chunk of clicks only if it makes sense
		if (	( ! OC.Shorty.Tracking.Dialog.List
						.find('#shorty-footer #scrollingTurn')
						.hasClass('disabled') )
			&&	($(this).scrollTop()+$(this).innerHeight()>=$(this)[0].scrollHeight) )
		{
			if (OC.Shorty.Debug) OC.Shorty.Debug.log("list scrolled towards its bottom");
			OC.Shorty.Tracking.build(true);
		}
		// rebind this method to the event
		OC.Shorty.Tracking.Dialog.List.find('#list-of-clicks').first().find('tbody')
			.on('scroll',OC.Shorty.Tracking.bottom);
	}, // OC.Shorty.Tracking.bottom
	/**
	* @method OC.Shorty.Tracking.build
	* @brief Builds the content of the list of tracked clicks
	* @return deferred.promise
	* @access private
	* @author Christian Reiner
	*/
	build: function(keep)
	{
		keep=keep||false;
		if (OC.Shorty.Debug) OC.Shorty.Debug.log("building tracking list");
		var dfd = new $.Deferred();
		var fieldset=OC.Shorty.Tracking.Dialog.List.find('fieldset');
		var offset=0;
		if (keep){
			if (OC.Shorty.Debug) OC.Shorty.Debug.log("keeping existing entries in list");
			// compute offset of next chunk to retrieve
			offset=OC.Shorty.Tracking.Dialog.List.find('#list-of-clicks').first().find('tbody tr').last().attr('id');
		}else{
			if (OC.Shorty.Debug) OC.Shorty.Debug.log("dropping existing entries in list");
			OC.Shorty.WUI.List.empty(OC.Shorty.Tracking.Dialog.List.find('#list-of-clicks').first());
			OC.Shorty.Tracking.Dialog.List.find('#shorty-footer #scrollingTurn').removeClass('disabled');
			OC.Shorty.Tracking.Dialog.List.find('#list-of-clicks').first().removeClass('scrollingTable');
			OC.Shorty.Tracking.Dialog.List.find('#list-of-clicks').first().find('tbody').css('height','');
		}
		$.when(
			// retrieve new entries
			OC.Shorty.Tracking.get(OC.Shorty.Tracking.Entry.attr('id'),offset)
		).pipe(function(response){
			OC.Shorty.WUI.List.fill.apply(
				OC.Shorty.Runtime.Context.ListOfClicks,
				[OC.Shorty.Tracking.Dialog.List.find('#list-of-clicks').first(),response.data]
			);
			// updte a few general informations
			OC.Shorty.Tracking.Dialog.List.find('#shorty-clicks').html(
			OC.Shorty.Tracking.Dialog.List.find('#list-of-clicks').first().find('tbody tr').length+'/'+response.stats[0]['length']);
			// offer load button if there is a rest of clicks left
			if (response.rest)
				 OC.Shorty.Tracking.Dialog.List.find('#shorty-footer #scrollingTurn').removeClass('disabled');
			else OC.Shorty.Tracking.Dialog.List.find('#shorty-footer #scrollingTurn').addClass('disabled');
		}).pipe(function(){
			$.when(
				// visualize table
				OC.Shorty.Tracking.Dialog.List.find('#list-of-clicks').first().removeClass('scrollingTable'),
				OC.Shorty.WUI.List.dim(OC.Shorty.Tracking.Dialog.List.find('#list-of-clicks').first(),true)
			).done(function(){
				// decide if table needs to become scrollable
				// if so compute the right size and apply it to the body
				// this appears to be the most 'working' control
				var bodyHeight	= OC.Shorty.Tracking.Dialog.List.find('#list-of-clicks tbody').outerHeight(true);
				var restHeight	= OC.Shorty.Tracking.Dialog.List.find('fieldset legend').outerHeight(true)
								+ OC.Shorty.Tracking.Dialog.List.find('#shorty-header').outerHeight(true)
								+ OC.Shorty.Tracking.Dialog.List.find('#titlebar').outerHeight(true)
								+ 40 // room for potentially visible #toolbar
								+ OC.Shorty.Tracking.Dialog.List.find('#shorty-footer').outerHeight(true)
								+ 80;// safety margin
				var roomHeight=$('#content').outerHeight();
				// make table scrollable, when more than ... entries
				if (roomHeight<bodyHeight+restHeight){
					// mark list as a scrollableTable
					OC.Shorty.Tracking.Dialog.List.find('#list-of-clicks').first()
						.addClass('scrollingTable');
					OC.Shorty.Tracking.Dialog.List.find('#list-of-clicks').first().find('tbody')
						.css('height',(roomHeight-restHeight-20)+'px');
					// this is a workaround to preserve column width inside the header when we modify the body
					$.each(OC.Shorty.Tracking.Dialog.List.find('#list-of-clicks thead th'),function(){
						var column=$(this).attr('id');
						$(this).css('width',$(this).parents('table').find('thead tr#toolbar td#'+column).css('width'));
						$(this).css('width',$(this).parents('table').find('tbody tr:first td#'+column).css('width'));
					});
				}
				// show sparkline at the right of the reference head
				OC.Shorty.Tracking.sparkle();
				dfd.resolve();
			}).fail(dfd.reject)
		}).done(dfd.resolve).fail(dfd.reject)
		return dfd.promise();
	}, // OC.Shorty.Tracking.build
	/**
	* @method OC.Shorty.Tracking.control
	* @brief Central control method, called by the app to hand over control
	* @param entry jQuery object holding the clicked entry, in this case a row in the list of Shortys
	* @return deferred.promise
	* @access public
	* @author Christian Reiner
	* @description This is the method specified as control in slot "registerActions".
	*/
	control:function(entry){
		if (OC.Shorty.Debug) OC.Shorty.Debug.log("tracking list controller");
		var dfd=new $.Deferred();
		// this is the shortys id
		OC.Shorty.Tracking.Entry=entry;
		// update lists reference bar content to improve intuitivity
		OC.Shorty.Tracking.Dialog.List.find('#shorty-title').html(entry.attr('data-title'));
		OC.Shorty.Tracking.Dialog.List.find('#shorty-status').html(t('shorty',entry.attr('data-status')));
		OC.Shorty.Tracking.Dialog.List.find('#shorty-until').html(
			((!entry.attr('data-until')) ? "-"+t('shorty',"never")+"-" : entry.attr('data-until'))
		);
		var clicks=OC.Shorty.Tracking.Dialog.List.find('#shorty-header #clicks');
		clicks.html(clicks.attr('data-slogan')+': '+entry.attr('data-clicks'));
		// prepare to (re-)fill the list
		$.when(
			OC.Shorty.WUI.List.empty(OC.Shorty.Tracking.Dialog.List),
			OC.Shorty.Tracking.Dialog.List.find('#shorty-footer #scrollingTurn').removeClass('disabled')
		).done(function(){
			OC.Shorty.WUI.Dialog.show(OC.Shorty.Tracking.Dialog.List)
			dfd.resolve();
		}).fail(function(){
			dfd.reject();
		})
		// load first content into the list
		OC.Shorty.Tracking.Stats.granted=[];
		OC.Shorty.Tracking.Stats.denied =[];
		OC.Shorty.Tracking.Stats.blocked=[];
		OC.Shorty.Tracking.build();
		return dfd.promise();
	}, // OC.Shorty.Tracking.control
	/**
	* @method OC.Shorty.Tracking.details
	* @brief Visualizes clicks details inside a popup
	* @access private
	* @author Christian Reiner
	*/
	details:function(element){
	if (OC.Shorty.Debug) OC.Shorty.Debug.log("visualizing details on click '"+element.attr('id')+"' in tracking list");
	var dfd = new $.Deferred();
	// use the existing 'share' dialog for this
	var entry =OC.Shorty.Tracking.Entry;
	var dialog=$('#shorty-tracking-click-dialog');
	// fill and dialog
	$.each(['title'],function(i,item){
		switch(item){
		default:
			dialog.find('#shorty-'+item)
				.text(entry.attr('data-'+item))
				.attr('data-'+item,entry.attr('data-'+item));

		} // switch
	})
	$.each(['result','address','host','user','time'],function(i,item){
		switch(item){
		case 'result':
			dialog.find('#click-'+item)
				.text(t('shorty_tracking',element.attr('data-'+item)))
				.attr('data-'+item,element.attr('data-'+item));
			break;

		case 'time':
			dialog.find('#click-'+item)
// 				.text(formatDate(1000*element.attr('data-'+item)))
				.text(dateTimeToHuman(element.attr('data-'+item),'- / -'))
				.attr('data-'+item,element.attr('data-'+item));
			break;

		default:
			dialog.find('#click-'+item)
				.text(element.attr('data-'+item))
				.attr('data-'+item,element.attr('data-'+item));
		} // switch
	})
	// move 'share' dialog towards entry
	dialog.appendTo(element.find('td#actions'));
	// open dialog
	$.when(
		OC.Shorty.WUI.Dialog.show(dialog)
	).done(dfd.resolve)
	return dfd.promise();
	}, // OC.Shorty.Tracking.details
	/**
	* @method OC.Shorty.Tracking.get
	* @brief Fetches a list of all registered clicks matching a specified Shorty
	* @param shorty string Id of the Shorty the click list is requested for
	* @param offset Numeric id of the last click that is already present in the list (ids being in chronological order!)
	* @return deferred.promise
	* @access private
	* @author Christian Reiner
	*/
	get:function(shorty,offset){
		if (OC.Shorty.Debug) OC.Shorty.Debug.log("loading clicks into tracking list");
		// no offset specified ? then start at the beginning
		offset = offset || 0;
		var dfd=new $.Deferred();
		// retrieve list template
		var data={shorty:shorty,offset:offset};
		$.ajax({
			type:     'GET',
			url:      OC.filePath('shorty_tracking','ajax','list.php'),
			cache:    false,
			data:     data,
			dataType: 'json'
		}).pipe(
			function(response){return OC.Shorty.Ajax.eval(response)},
			function(response){return OC.Shorty.Ajax.fail(response)}
		).done(function(response){
			dfd.resolve(response);
		}).fail(function(response){
			dfd.reject(response);
		})
		return dfd.promise();
	}, // OC.Shorty.Tracking.get
	/**
	* method OC.Shorty.Tracking.init
	* @brief Initializes the dialog this aplugin adds to the Shorty app
	* @return deferred.promise
	* @access public
	* @author Christian Reiner
	* @description The html content of the dialog is fetched via ajax
	*/
	init:function(){
		if (OC.Shorty.Debug) OC.Shorty.Debug.log("initializing tracking list");
		// check if dialogs already exist
		if (   $.isEmptyObject(OC.Shorty.Tracking.Dialog.List)
			&& $.isEmptyObject(OC.Shorty.Tracking.Dialog.Click) ){
			// two dialogs are used by this plugin
			var dialogs={
				'list':  OC.Shorty.Tracking.Dialog.List,
				'click': OC.Shorty.Tracking.Dialog.Click
			};
			// load dialogs from server
			var dfds=$.map(dialogs,function(obj,dialog){
				// load dialog layout via ajax and append it to the collection of dialogs in the controls
				return $.ajax({
					type:     'GET',
					url:      OC.filePath('shorty_tracking','ajax','layout.php'),
					data:     { dialog: dialog},
					cache:    false,
					dataType: 'json'
				}).pipe(
					function(response){return OC.Shorty.Ajax.eval(response)},
					function(response){return OC.Shorty.Ajax.fail(response)}
				).done(function(response){
					// create a fresh dialog and insert it alongside the existing dialogs in the top controls bar
					$('#controls').append(response.layout);
					obj=$('#controls #shorty-tracking-'+dialog+'-dialog').first();
					switch(dialog){
					case 'list':
						OC.Shorty.Tracking.Dialog.List=$('#controls #shorty-tracking-list-dialog').first();
						break;

					case 'click':
						OC.Shorty.Tracking.Dialog.Click=$('#controls #shorty-tracking-click-dialog').first();
						break;
					} // switch
				})
			}) // map
			return $.when.apply(null, dfds);
		}else{
			// dialogs already loaded, just clean them for usage
			OC.Shorty.Tracking.Dialog.List.find('#list-of-clicks tbody tr').remove();
			new Deferred().resolve();
		} // else
	},
	/**
	* @method OC.Shorty.Tracking.sparkle
	* @brief Creates a 'click sparkline' at the top right of the dialog
	* @author Christian Reiner
	*/
	sparkle:function(){
		var sparkline=OC.Shorty.Tracking.Dialog.List.find('#stats').first();
		// reset previous sparkline
		sparkline.find('canvas').remove();
		// set range of sparkline as [Shorty-creation...now]
		var rangeMin=Math.floor($.datepicker.formatDate('@',new Date(OC.Shorty.Tracking.Entry.attr('data-created')))/1000);
		var rangeMax=Math.ceil(0.5+$.datepicker.formatDate('@',new Date())/1000);
		var range   =rangeMax-rangeMin;
		// we need to compute a value notation the jquery sparkline extension understands:
		// []
		var granted=new Array();
		var denied =new Array();
		var blocked=new Array();
		var column, steps = 80;
		// initialize all columns as zero value
		for (column=0;column<=steps;column=column+1){
			granted[column]	= 0;
			denied[column]	= 0;
			blocked[column]	= 0;
		}
		// increment matching range column for each click
		$.each(OC.Shorty.Tracking.Stats.granted,function(i,time){granted[Math.round((time-rangeMin)/(range/steps))]++;});
		$.each(OC.Shorty.Tracking.Stats.denied, function(i,time){ denied[Math.round((time-rangeMin)/(range/steps))]++;});
		$.each(OC.Shorty.Tracking.Stats.blocked,function(i,time){blocked[Math.round((time-rangeMin)/(range/steps))]++;});
		// initialize stats sparkline
		var sparklineOpts={
			width:(steps*2)+'px',
			height:'1.6em',
			tooltipSkipNull:true,
			tooltipContainer:OC.Shorty.Tracking.Dialog.List,
			tooltipSuffix:' '+t('shorty_tracking','granted'),
			type:'line',
			numberDigitGroupSep:' '
		}
		$(stats).sparkline(
			granted,
			$.extend(
				{},
				sparklineOpts,{
					composite:false,
					tooltipSuffix:' '+t('shorty_tracking','granted'),
					lineColor:'green',
					fillColor:'limegreen',
				}
			)
		);
		$(stats).sparkline(
			denied,
			$.extend(
				{},
				sparklineOpts,{
					composite:true,
					tooltipSuffix:' '+t('shorty_tracking','denied'),
					lineColor:'darkorange',
					fillColor:false,
				}
			)
		);
		$(stats).sparkline(
			blocked,
			$.extend(
				{},
				sparklineOpts,{
					composite:true,
					tooltipSuffix:' '+t('shorty_tracking','blocked'),
					lineColor:'red',
					fillColor:false
				}
			)
		);
		$(stats).off('sparklineRegionChange');
		$(stats).on('sparklineRegionChange', function(ev) {
			var sparkline = ev.sparklines[0],
			region = sparkline.getCurrentRegionFields();
			value = region.y;
			$('.mouseoverregion').text("x="+region.x+" y="+region.y);
		}).on('mouseleave',function(){$('.mouseoverregion').text('');});
	} // OC.Shorty.Tracking.sparkle
} // OC.Shorty.Tracking

/**
 * @class OC.Shorty.Runtime.Context.ListOfClicks
 * @brief Catalog of callbacks required for list of shorty
 * @author Christian Reiner
 */
OC.Shorty.Runtime.Context.ListOfClicks={
	/**
	 * @class OC.Shorty.Runtime.Context.ListOfClicks.ColumnValueReference
	 * @brief collection of callback methods to use a list columns value
	 * @author Christian Reiner
	 * @description These callbacks are used in column filtering, a default for
	 * non existing methods here exists in the filtering function
	 */
	ColumnValueReference:{
		time:function(){return $(this).find('span').text();}
	},
	/**
	* @method OC.Shorty.Runtime.Context.ListOfClicks.ListAddEnrich
	* @brief Callback function replacing the default used in OC.Shorty.WUI.List.add()
	* @param row jQuery object Holding a raw clone of the 'dummy' entry in the list, meant to be populated by real values
	* @param set object This is the set of attributes describing a single registered click
	* @param hidden bool Indicats if new entries in lists should be held back for later highlighting (flashing) optically or not
	* @access public
	* @author Christian Reiner
	* @description This replacement uses the plugin specific column names.
	*/
	ListAddEnrich:function(row,set,hidden){
		// set row id to entry id
		row.attr('id',set.id);
		// hold back rows for later highlighting effect
		if (hidden) row.addClass('shorty-fresh'); // might lead to a pulsate effect later
		// add aspects as content to the rows cells
		$.each(['status','time','address','host','user','result'],function(j,aspect){
			// we wrap the cells content into a span tag
			var span=$('<span>');
			// enhance row with real set values
			if (typeof set[aspect]=='undefined')
				 row.attr('data-'+this,'');
			else row.attr('data-'+this,set[aspect]);
			// fill data into corresponsing column
			var title, content, classes=[];
			switch(aspect){
			case 'status':
				var icon;
				switch (set['result']){
				case 'blocked': icon='bad';     break;
				case 'denied':  icon='neutral'; break;
				case 'granted': icon='good';    break;
				default:        icon='blank';
				} // switch
				span.html('<img class="shorty-icon svg" width="16" src="'+OC.filePath('shorty','img/status',icon+'.png')+'">');
				break;

			case 'time':
				if (null==set[aspect])
					 span.text('-?-');
// 				else span.text(formatDate(1000*set[aspect]));
				else span.text(dateTimeToHuman(set[aspect],'- / -'));
				// add value to the sparkline value set in the header
				switch (set['result']){
					case 'blocked': OC.Shorty.Tracking.Stats.blocked.push(set[aspect]); break;
					case 'denied':  OC.Shorty.Tracking.Stats.denied.push (set[aspect]); break;
					case 'granted': OC.Shorty.Tracking.Stats.granted.push(set[aspect]); break;
				} // switch
				break;

			case 'result':
				span.text(t('shorty_tracking',set[aspect]));
				span.addClass('ellipsis');
				break;

			default:
				span.text(set[aspect]);
				span.addClass('ellipsis');
			} // switch
			row.find('td#'+aspect).empty().append(span);
		}) // each aspect
	}, // OC.Shorty.Runtime.Context.ListOfClicks.ListAddEnrich
	/**
	* @method OC.Shorty.Runtime.Context.ListOfClicks.ListAddInsert
	* @brief Inserts a cloned and enriched row into the table at a usage specific place
	* @access public
	* @author Christian Reiner
	* @description
	* New entries always get appended to the list of already existing entries,
	* since those are always sorted in a chronological order.
	*/
	ListAddInsert:function(list,row){
		list.find('tbody').append(row);
	}, // OC.Shorty.Runtime.Context.ListOfClicks.ListAddInsert
	/**
	* @method OC.Shorty.Runtime.Context.ListOfClicks.ListFillFilter
	* @brief Column filter rules specific to this plugins list
	* @access public
	* @author Christian Reiner
	*/
	ListFillFilter:function(list){
		if (OC.Shorty.Debug) OC.Shorty.Debug.log("using 'tracking' method to filter filled list");
		// filter list
		var toolbar=list.find('thead tr#toolbar');
		OC.Shorty.WUI.List.filter.apply(this,
			[list,'time',   toolbar.find('th#time    #filter').val()]);
		OC.Shorty.WUI.List.filter.apply(this,
			[list,'address',toolbar.find('th#address #filter').val()]);
		OC.Shorty.WUI.List.filter.apply(this,
			[list,'host',   toolbar.find('th#host    #filter').val()]);
		OC.Shorty.WUI.List.filter.apply(this,
			[list,'user',   toolbar.find('th#user    #filter').val()]);
		OC.Shorty.WUI.List.filter.apply(this,
			[list,'result', toolbar.find('th#result  select :selected').val()]);
	}, // OC.Shorty.Runtime.Context.ListOfClicks.ListFillFilter
	/**
	* @method OC.Shorty.Runtime.Context.ListOfClicks.ToolbarCheckFilter
	* @brief Callback used to check if any filters prevent closing a lists toolbar
	* @param toolbar jQueryObject The lists toolbar filters should be checked in
	* @return bool Indicates if an existing filter prevents the closing or not
	* @access public
	* @author Christian Reiner
	* @description
	* Used as replacement for the default used in OC.Shorty.WUI.List.Toolbar.toggle()
	* This version is private to this plugin and uses the filter names specific to
	* the list of tracked clicks.
	*/
	ToolbarCheckFilter:function(toolbar){
		return (  (  (toolbar.find('th#time,#address,#host,#user').find('div input#filter:[value!=""]').length)
					&&(toolbar.find('th#time,#address,#host,#user').find('div input#filter:[value!=""]').effect('pulsate')) )
				||(  (toolbar.find('th#result select :selected').val())
					&&(toolbar.find('#result').effect('pulsate')) ) );
		} // OC.Shorty.Runtime.Context.ListOfClicks.ToolbarCheckFilter
} // OC.Shorty.Runtime.Context.ListOfClicks
