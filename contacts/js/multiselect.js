/**
 * @param 'createCallback' A function to be called when a new entry is created. Two arguments are supplied to this function:
 *	The select element used and the value of the option. If the function returns false addition will be cancelled. If it returns
 *	anything else it will be used as the value of the newly added option.
 * @param 'createText' The placeholder text for the create action.
 * @param 'title' The title to show if no options are selected.
 * @param 'checked' An array containing values for options that should be checked. Any options which are already selected will be added to this array.
 * @param 'labels' The corresponding labels to show for the checked items.
 * @param 'oncheck' Callback function which will be called when a checkbox/radiobutton is selected. If the function returns false the input will be unchecked.
 * @param 'onuncheck' @see 'oncheck'.
 * @param 'singleSelect' If true radiobuttons will be used instead of checkboxes.
 */
(function( $ ){
	var multiSelectId=-1;
	$.fn.multiSelect=function(options) {
		multiSelectId++;
		var settings = {
			'createCallback':false,
			'createText':false,
			'singleSelect':false,
			'title':this.attr('title'),
			'checked':[],
			'labels':[],
			'oncheck':false,
			'onuncheck':false,
			'minWidth': 'default;'
		};
		$(this).attr('data-msid', multiSelectId);
		$.extend(settings,options);
		$.each(this.children(),function(i,option) {
			// If the option is selected, but not in the checked array, add it.
			if($(option).attr('selected') && settings.checked.indexOf($(option).val()) === -1) {
				settings.checked.push($(option).val());
				settings.labels.push($(option).text().trim());
			}
			// If the option is in the checked array but not selected, select it.
			else if(settings.checked.indexOf($(option).val()) !== -1 && !$(option).attr('selected')) {
				$(option).attr('selected', 'selected');
				settings.labels.push($(option).text().trim());
			}
		});
		var button=$('<div class="multiselect button"><span>'+settings.title+'</span><span>▾</span></div>');
		var span=$('<span/>');
		span.append(button);
		button.data('id',multiSelectId);
		button.selectedItems=[];
		this.hide();
		this.before(span);
		if(settings.minWidth=='default') {
			settings.minWidth=button.width();
		}
		button.css('min-width',settings.minWidth);
		settings.minOuterWidth=button.outerWidth()-2;
		button.data('settings',settings);

		if(!settings.singleSelect && settings.checked.length>0) {
			button.children('span').first().text(settings.labels.join(', '));
		} else if(settings.singleSelect) {
			button.children('span').first().text(this.find(':selected').text());
		}

		var self = this;
		self.menuDirection = 'down';
		button.click(function(event){

			var button=$(this);
			if(button.parent().children('ul').length>0) {
				if(self.menuDirection === 'down') {
					button.parent().children('ul').slideUp(400,function() {
						button.parent().children('ul').remove();
						button.removeClass('active');
					});
				} else {
					button.parent().children('ul').fadeOut(400,function() {
						button.parent().children('ul').remove();
						button.removeClass('active').removeClass('up');
					});
				}
				return;
			}
			var lists=$('ul.multiselectoptions');
			lists.slideUp(400,function(){
				lists.remove();
				$('div.multiselect').removeClass('active');
				button.addClass('active');
			});
			button.addClass('active');
			event.stopPropagation();
			var options=$(this).parent().next().children();
			var list=$('<ul class="multiselectoptions"/>').hide().appendTo($(this).parent());
			var inputType = settings.singleSelect ? 'radio' : 'checkbox';
			function createItem(element, checked){
				element=$(element);
				var item=element.val();
				var id='ms'+multiSelectId+'-option-'+item;
				var input=$('<input type="' + inputType + '"/>');
				input.attr('id',id);
				if(settings.singleSelect) {
					input.attr('name', 'ms'+multiSelectId+'-option');
				}
				var label=$('<label/>');
				label.attr('for',id);
				label.text(element.text() || item);
				if(settings.checked.indexOf(item)!=-1 || checked) {
					input.attr('checked', true);
				}
				if(checked){
					if(settings.singleSelect) {
						settings.checked = [item];
						settings.labels = [item];
					} else {
						settings.checked.push(item);
						settings.labels.push(item);
					}
				}
				input.change(function(){
					var value = $(this).attr('id').substring(String('ms'+multiSelectId+'-option').length+1);
					var label = $(this).next().text().trim();
					if($(this).is(':checked')) {
						if(settings.singleSelect) {
							settings.checked = [];
							settings.labels = [];
							$.each(self.find('option'), function() {
								$(this).removeAttr('selected');
							});
						}
						element.attr('selected','selected');
						if(typeof settings.oncheck === 'function') {
							if(settings.oncheck(value)===false) {
								$(this).attr('checked', false);
								return;
							}
						}
						settings.checked.push(value);
						settings.labels.push(label);
					} else {
						var index=settings.checked.indexOf(value);
						element.attr('selected',null);
						if(typeof settings.onuncheck === 'function') {
							if(settings.onuncheck(value)===false) {
								$(this).attr('checked',true);
								return;
							}
						}
						settings.checked.splice(index,1);
						settings.labels.splice(index,1);
					}
					var oldWidth=button.width();
					button.children('span').first().text(settings.labels.length > 0
						? settings.labels.join(', ')
						: settings.title);
					var newOuterWidth=Math.max((button.outerWidth()-2),settings.minOuterWidth)+'px';
					var newWidth=Math.max(button.width(),settings.minWidth);
					var pos=button.position();
					button.css('height',button.height());
					button.css('white-space','nowrap');
					button.css('width',oldWidth);
					button.animate({'width':newWidth},undefined,undefined,function(){
						button.css('width','');
					});
					list.animate({'width':newOuterWidth,'left':pos.left+3});
				});
				var li=$('<li></li>');
				li.append(input).append(label);
				return li;
			}
			$.each(options,function(index,item){
				list.append(createItem(item));
			});
			button.parent().data('preventHide',false);
			if(settings.createText){
				var li=$('<li>+ <em>'+settings.createText+'<em></li>');
				li.click(function(event){
					li.empty();
					var input=$('<input class="new">');
					li.append(input);
					input.focus();
					input.css('width',button.innerWidth());
					button.parent().data('preventHide',true);
					input.keypress(function(event) {
						if(event.keyCode == 13) {
							event.preventDefault();
							event.stopPropagation();
							var value = $(this).val();
							var exists = false;
							$.each(options,function(index, item) {
								if ($(item).val() == value || $(item).text() == value) {
									exists = true;
									return false;
								}
							});
							if (exists) {
								return false;
							}
							var li=$(this).parent();
							var val = $(this).val();
							var select=button.parent().next();
							if(typeof settings.createCallback === 'function') {
								var response = settings.createCallback(select, val);
								console.log('response', response);
								if(response === false) {
									return false;
								} else if(typeof response !== 'undefined') {
									val = response;
								}
							}
							if(settings.singleSelect) {
								$.each(select.find('option:selected'), function() {
									$(this).removeAttr('selected');
								});
							}
							$(this).remove();
							li.text('+ '+settings.createText);
							li.before(createItem(this));
							var option=$('<option selected="selected"/>');
							option.text($(this).val()).val(val).attr('selected', 'selected');
							select.append(option);
							li.prev().children('input').prop('checked', true).trigger('change');
							button.parent().data('preventHide',false);
							button.children('span').first().text(settings.labels.length > 0
								? settings.labels.join(', ')
								: settings.title);
							if(self.menuDirection === 'up') {
								var list = li.parent();
								list.css('top', list.position().top-li.outerHeight());
							}
						}
					});
					input.blur(function() {
						event.preventDefault();
						event.stopPropagation();
						$(this).remove();
						li.text('+ '+settings.createText);
						setTimeout(function(){
							button.parent().data('preventHide',false);
						},100);
					});
				});
				list.append(li);
			}
			var pos=button.position();
			if($(document).height() > button.offset().top+button.outerHeight() + list.children().length * button.height()) {
				list.css('top',pos.top+button.outerHeight()-5);
				list.css('left',pos.left+3);
				list.css('width',(button.outerWidth()-2)+'px');
				list.addClass('down');
				button.addClass('down');
				list.slideDown();
			} else {
				list.css('top', pos.top - list.height());
				list.css('left', pos.left+3);
				list.css('width',(button.outerWidth()-2)+'px');
				list.detach().insertBefore($(this));
				list.addClass('up');
				button.addClass('up');
				list.fadeIn();
				self.menuDirection = 'up';
			}
			list.click(function(event) {
				event.stopPropagation();
			});
		});
		$(window).click(function() {
			if(!button.parent().data('preventHide')) {
				// How can I save the effect in a var?
				if(self.menuDirection === 'down') {
					button.parent().children('ul').slideUp(400,function() {
						button.parent().children('ul').remove();
						button.removeClass('active').removeClass('down');
					});
				} else {
					button.parent().children('ul').fadeOut(400,function() {
						button.parent().children('ul').remove();
						button.removeClass('active').removeClass('up');
					});
				}
			}
		});

		return span;
	};
})( jQuery );