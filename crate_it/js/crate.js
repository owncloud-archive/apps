function buildFileTree(data) {
    $tree = $('#files').tree({
        data: data.vfs,
        autoOpen: false,
        dragAndDrop: true,
        usecontextmenu: true,
        onCanMoveTo: function(moved_node, target_node, position) {
                // Implementation of 'endsWith'
                return target_node.id.indexOf('folder', target_node.id.length - 'folder'.length) !== -1;
        },
    });

    $tree.jqTreeContextMenu($('#fileMenu'), {
        "add": function (node) {

            $("#dialog-add").dialog('open');
        },
        "edit": function (node) {
            $("#dialog-rename").dialog('open');
        },
        "delete": function(node) {
            $("#dialog-delete").dialog('option', 'buttons', [
                {text: 'Cancel',
                click: function() { $(this).dialog('close'); },
                },
                {text: 'Delete',
                click: function() {
                    // deleteNode(node);
                    $tree.tree('removeNode', node);
                    saveTree($tree);
                    $(this).dialog('close');
                }
            }]);
            $("#dialog-delete").dialog('open');
        }, 
    });

    $tree.bind('tree.move', function(e) {
        saveTree($tree);
    });

    expandRoot();

    return $tree;
}

function expandRoot() {
    var rootnode = $tree.tree('getNodeById', 'rootfolder'); // NOTE: also see getTree
    $tree.tree('openNode', rootnode);
}


function saveTree($tree) {
    $.ajax({
        url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
        type: 'post',
        dataType: 'html',
        data: {'action':'update_vfs', 'vfs': $tree.tree('toJson')},
        success: function(data){
            OC.Notification.show('Crate updated');
            setTimeout(OC.Notification.hide(), 3000);
        },
        error: function(data){
            OC.Notification.show(data.statusText);
            setTimeout(OC.Notification.hide(), 3000);
        }
    });
}


function removeFORCodes(){
	var first = $('#for_second_level option:first').detach();
	$('#for_second_level').children().remove();
	$('#for_second_level').append(first);
}

function hideMetadata(){
	if($('#crateList tr').length == 0){
		$('#metadata').hide();
	}
}

$(document).ready(function() {
	
	hideMetadata();
	
	$('#crateList').disableSelection();
	
	$('#download').click('click', function(event) { 
		if($('#crateList tr').length == 0){
			OC.Notification.show('No items in the crate to package');
			setTimeout(OC.Notification.hide(), 3000);
			return;
		}
		OC.Notification.show('Your download is being prepared. This might take some time if the files are big');
		setTimeout(OC.Notification.hide(), 3000);
		window.location = OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=zip';
		
	});
	
	$('#epub').click(function(event) {
		if($('#crateList tr').length == 0){
			OC.Notification.show('No items in the crate to package');
			setTimeout(OC.Notification.hide(), 3000);
			return;
		}
		//get all the html previews available, concatenate 'em all
		OC.Notification.show('Your download is being prepared. This might take some time');
		setTimeout(OC.Notification.hide(), 3000);
		window.location = OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=epub';
	});
	
	$('#clear').click(function(event) {
		$.ajax(OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=clear');
		$('#crateList').empty();
		hideMetadata();
	});
	
	
	$('#subbutton').click(function(event) {
	    $.ajax({
	        url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
	        type: 'get',
	        dataType: 'html',
	        data: {'action':'create', 'crate_name':$('#crate_input #create').val()},
	        success: function(data){
	        	$('#crate_input #create').val('');
	        	$("#crates").append('<option id="'+data+'" value="'+data+'" >'+data+'</option>');
	        	OC.Notification.show('Crate '+data+' successfully created');
				setTimeout(OC.Notification.hide(), 3000);
			},
			error: function(data){
				OC.Notification.show(data.statusText);
				setTimeout(OC.Notification.hide(), 3000);
				$('#crate_input #create').focus();
			}
	    });
	    return false;
	});
	
	$('#crateName').editable(OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=rename_crate', {
		name : 'new_name',
		indicator : '<img src='+OC.imagePath('crate_it', 'indicator.gif')+'>',
		tooltip : 'Double click to edit...',
		event : 'dblclick',
		style : 'inherit',
		height : '15px',
		callback : function(value, settings){
			$('#crates').find(':selected').text(value);
            $('#crates').find(':selected').prop("id", value);
            $('#crates').find(':selected').prop("value", value);
		}
	});
	
	$('#crates').change(function(){
		var id = $(this).find(':selected').attr("id");
		if(id === "choose"){
			$('#crateList').empty();
			$('#crateName').text("");
			$('#anzsrc_for').hide();
			return;
		}
		$.ajax({
			url: OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=switch&crate_id='+id,
			type: 'get',
			dataType: 'html',
			success: function(data){
				$.ajax({
					url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
					type: 'get',
					dataType: 'json',
					data: {'action': 'get_items'},
					success: function(data){
						$('#crateList').empty();
						$('#crateName').text(id);
                        console.log(data);
						if(data != null){
                            if($tree) {
                                $tree.tree('loadData', data.vfs);
                                expandRoot();
                            } else {
                                $tree = buildFileTree(data);
                            }
							$('#metadata').show();
                            $('#description').text(data.description);
						} else {
							hideMetadata();
						}
					},
					error: function(data){
						var e = data.statusText;
						alert(e);
					}
				});
			},
			error: function(data){
				var e = data.statusText;
				alert(e);
			}
		});
	});
	
	$('#for_top_level').change(function(){
		var id = $(this).find(':selected').attr("id");
		if(id === "select_top"){
			//remove all the child selects
			removeFORCodes();
			return;
		}
		//make a call to the backend, get next level codes, populate option
		$.ajax({
			url: OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=get_for_codes&level='+id,
			type: 'get',
			dataType: 'json',
			success: function(data){
				if(data !=null){
					removeFORCodes();
					for(var i=0; i<data.length; i++){
						$("#for_second_level").append('<option id="'+data[i]+'" value="'+data[i]+'" >'+data[i]+'</option>');
					}
				}
			},
			error: function(data){
				var e = data.statusText;
				alert(e);
			}
		});
	});

    $('#description').editable(OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=describe', { 
        name: 'description',
        type      : 'textarea',
        tooltip   : 'Please enter a description...',
        cancel    : 'Cancel',
        submit    : 'OK',
        indicator : '<img src='+OC.imagePath('crate_it', 'indicator.gif')+'>',
        rows: 6,
        cols: 100, // This doesn't seem to work correctly
     });
    
    $.ajax({
        url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
        type: 'get',
        dataType: 'json',
        data: {'action': 'get_items'},
        success: function(data){
            $('#description').text(data.description);
            $tree = buildFileTree(data);
        },
        error: function(data){
            var e = data.statusText;
            alert(e);
        }
    });

    $("#dialog-add").dialog({
        autoOpen: false,
    });

    $("#dialog-rename").dialog({
        autoOpen: false,
    });

    $("#dialog-delete").dialog({
        autoOpen: false,
    });
	
});	
