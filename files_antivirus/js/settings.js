var antivirusSettings = antivirusSettings || {
	statuses : [
		{ value : 0, title : t('files_antivirus', 'Clean')},
		{ value : 1, title : t('files_antivirus', 'Infected')},
		{ value : -1, title : t('files_antivirus', 'Unchecked')}
	],
	types : [
		{ value : 1, title : t('files_antivirus', 'Scanner exit status') },
		{ value : 2, title : t('files_antivirus', 'Scanner output') },
	],
	init : function(){
		$.post(OC.filePath('files_antivirus', 'ajax', 'settings.php'), {action : 'list'},
			function onSuccess(response){
				if (!response || !response.statuses){
					return;
				}
				for (var i = 0; i < response.statuses.length; i++) {
					antivirusSettings.renderRow(response.statuses[i]);
				}		
			}
		);
	},
	
	renderRow : function(data){
		var row = $('<tr />').data('id', data.id).appendTo($('#antivirus-statuses'));
		$('<td class="icon-checkmark shaded" />').appendTo(row);
		antivirusSettings.renderSelect(
				$('<td class="status-type" />').appendTo(row), 
				{options : antivirusSettings.types, current : data.status_type}
		);
		$('<td class="match editable" />').appendTo(row).text(  
				(data.status_type == 1 ? data.result : data.match)
		);
		$('<td class="description editable" />').appendTo(row).text(data.description);
		antivirusSettings.renderSelect(
				$('<td class="scan-result" />').appendTo(row),
				{ options : antivirusSettings.statuses, current : data.status }
		);
		
		$('<td class="icon-delete" />').appendTo(row);
	},
	
	onSave : function(){
		var node = $(this),
		row = $(node).parent(),
		data = {
			action : 'save',
			id : row.data('id'),
			status_type : row.find('.status-type select').val(),
			match : row.children('.match').text(),
			description : row.children('.description').text(),
			status : row.find('.scan-result select').val()
		};
		
		$.post(OC.filePath('files_antivirus', 'ajax', 'settings.php'), data,
			function onSuccess(response){
				if (response && response.id){
					row.data('id', response.id);
				}
				node.addClass('shaded');
			}
		);
	},
	
	onEdit : function(node){
		if ($(node).find('input').length){
			return;
		}
		var current = $(node).text();
		$(node).text('');
		$('<input />').val(current)
			.on('blur', function(){
				var newValue = $(this).val();
				if (newValue !== current){
					$(node).parents('tr').first().find('td.icon-checkmark').removeClass('shaded');
				}
				$(this).remove();
				$(node).text(newValue);
			})
			.on('keyup', function(event){
				if (event.keyCode === 27) {
					$(this).val(current);
					$(this).blur();
					event.preventDefault();
				}
				if (event.keyCode === 13) {
					$(this).blur();
					event.preventDefault();
				}
			})
			.on('keydown', function(){
				if (event.keyCode === 9) {
					$(this).parent('td').siblings('td.editable').first().trigger('click');
					event.preventDefault();
				}
			})
			.appendTo(node)
				.focus()
		;
		
	},
	
	deleteRow : function(){
		var row = $(this).parent();
		row.hide();
		$.post(OC.filePath('files_antivirus', 'ajax', 'settings.php'), {action : 'delete', id : row.data('id')},
			function onSuccess(response){
				row.remove();
			}
		);
	},
	
	renderSelect : function(parent, data){
		var select = $('<select />')
				.on('change', function(){
					$(this).parents('tr').first().find('td.icon-checkmark').removeClass('shaded');
				});
		for (var i=0; i<data.options.length; i++){
			var option = $('<option />');
			option.attr('value', data.options[i].value)
					.text(data.options[i].title)
			;
			if (data.options[i].value == data.current){
				option.attr('selected', '');
			}
			select.append(option);
		}
		parent.append(select);
	}
};


function av_mode_show_options(str){
	if ( str == 'daemon'){
		$('p.av_socket').hide('slow');
		$('p.av_host').show('slow');
		$('p.av_port').show('slow');
		$('p.av_path').hide('slow');
	} else if ( str == 'socket' ) {
		$('p.av_socket').show('slow');
		$('p.av_path').hide('slow');
		$('p.av_host').hide('slow');
		$('p.av_port').hide('slow');
        } else if (str == 'executable'){
		$('p.av_socket').hide('slow');
		$('p.av_host').hide('slow');
		$('p.av_port').hide('slow');
		$('p.av_path').show('slow');
	}
}
$(document).ready(function() {
	$('#antivirus-advanced').on('click', function () {
		$('.section-antivirus .spoiler').toggle();
		antivirusSettings.init();
	});
	
	
	$('#antivirus-reset').on('click', function (){
		$.post(OC.filePath('files_antivirus', 'ajax', 'settings.php'), {action : 'reset'},
			function onSuccess(){
				$('#antivirus-statuses tbody td').remove();
				antivirusSettings.init();
			});
	});
	$('#antivirus-clear').on('click', function (){
		$.post(OC.filePath('files_antivirus', 'ajax', 'settings.php'), {action : 'clear'},
			function onSuccess(){
				$('#antivirus-statuses tbody td').remove();
				antivirusSettings.init();
			});
	});
	
	$('#antivirus-add').on('click', function (){
		antivirusSettings.renderRow({
			id : '',
			status_type : 1,
			result : '',
			description : '',
			status : 0
		});
		$('#antivirus-statuses tbody tr:last-child td.editable').first().trigger('click');
	});
	
	$('#antivirus-statuses tbody').on('click', 'td.editable', function(){
		console.log(this);
		antivirusSettings.onEdit(this);
	});
	$('#antivirus-statuses tbody').on('click', 'td.icon-delete', antivirusSettings.deleteRow);
	$('#antivirus-statuses tbody').on('click', 'td.icon-checkmark', antivirusSettings.onSave);
	$("#av_mode").change(function () {
		var str = $("#av_mode").val();
		av_mode_show_options(str);
	});   
	$("#av_mode").change();
});
