function hideNotification(delayTime) {
    setTimeout(function() {
	OC.Notification.hide();
    }, delayTime);
}

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
            $("#dialog-add").dialog('option', 'buttons', [
                {text: 'Cancel',
                click: function() { $(this).dialog('close'); },
                },
                {text: 'Add',
                click: function() {
                    $tree.tree('addNodeAfter', {
                        id: 'folder',
                        label: $('#add-folder').val(),
                    }, node);
                    saveTree($tree);
                    $(this).dialog('close');
                }
            }]);
            $("#dialog-add").dialog('open');
        },
        "rename": function (node) {
            $("#dialog-rename").dialog('option', 'buttons', [
                {text: 'Cancel',
                click: function() { $(this).dialog('close'); },
                },
                {text: 'Rename',
                click: function() {
                    $tree.tree('updateNode', node, $('#rename-item').val());
                    saveTree($tree);
                    $(this).dialog('close');
                }
            }]);
            $("#dialog-rename").dialog('open');
        },
        "delete": function(node) {
            $("#dialog-delete").dialog('option', 'buttons', [
                {text: 'Cancel',
                click: function() { $(this).dialog('close'); },
                },
                {text: 'Delete',
                click: function() {
                    $tree.tree('removeNode', node);
                    saveTree($tree);
                    $(this).dialog('close');
                    // updateCrateSize();
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


function updateCrateSize() {
    $.ajax({
        url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
        type: 'post',
        dataType: 'json',
        data: {'action': 'crate_size'},
        success: function(data) {
            $('#crate_size_human').text(data['human']);
            crate_size_mb = data['size'] / (1024 * 1024);
            var msg = null;
            if (max_zip_mb > 0 && crate_size_mb > max_zip_mb) {
                msg = 'WARNING: Crate size exceeds zip file limit: ' + max_zip_mb + ' MB';
                $('#download').attr("disabled", "disabled");
                if (max_sword_mb > 0 && crate_size_mb > max_sword_mb) {
                    msg += ', and SWORD limit: ' + max_sword_mb + 'MB';
                    $('#post').attr("disabled", "disabled");
                }
                msg += '.';
            } else if (max_sword_mb > 0 && crate_size_mb > max_sword_mb) {
                msg = 'WARNING: Crate size exceeds SWORD limit: ' + max_sword_mb + 'MB.';
                $('#post').attr("disabled", "disabled");
            }
            if (msg) {
                OC.Notification.show(msg);
                setTimeout(function() { OC.Notification.hide(); }, 6000);
            } else {
                $('#post').removeAttr("disabled");
                $('#download').removeAttr("disabled");
            }
        },
        error: function(data) {}
    });    
}

function togglePostCrateToSWORD() {
    $.ajax({
        url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
        type: 'post',
        dataType: 'json',
        data: {'action': 'validate_metadata'},
        success: function(data) {
	    if (data.status == "Success") {
		$('#post').removeAttr("title");
		$('#post').removeAttr("disabled");
	    }
	    else {
		$('#post').attr("title", "You cannot post this crate until metadata(title, description, creator) are all set");
		$('#post').attr("disabled", "disabled");
	    }		
        },
        error: function(data) {
            OC.Notification.show(data.statusText);
	    hideNotification(3000);
        }
    });
}

function makeCrateListEditable(){
	$('#crateList .title').editable(OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=edit_title', {
		name : 'new_title',
		indicator : '<img src='+OC.imagePath('crate_it', 'indicator.gif')+'>',
		tooltip : 'Double click to edit...',
		event : 'dblclick',
		style : 'inherit',
		submitdata : function(value, settings){
			return {'elementid':this.parentNode.parentNode.getAttribute('id')};
		}
	});
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
            updateCrateSize();
            hideNotification(3000);
        },
        error: function(data){
            OC.Notification.show(data.statusText);
            hideNotification(3000);
        }
    });
}

function treeHasNoFiles() {
    var children = $tree.tree('getNodeById', 'rootfolder').children;
    return children.length == 0;
}

function removeFORCodes(){
	var first = $('#for_second_level option:first').detach();
	$('#for_second_level').children().remove();
	$('#for_second_level').append(first);
}

function hideMetadata(){
	if(treeHasNoFiles()){
		$('#metadata').hide();
	}
}

function activateRemoveCreatorButton(buttonObj) {
    buttonObj.click('click', function(event) {
	// Remove people from backend
	var id = $(this).attr("id");
	creator_id = id.replace("creator_", "");
	
	$.ajax({
	    url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
	    type: 'post',
	    dataType: 'json',
	    data: {
		'action': 'remove_people',
		'creator_id': creator_id,
		'full_name': $(this).parent().text()
	    },
	    success: function(data) {
		buttonObj.parent().remove();
		togglePostCrateToSWORD();
	    },
	    error: function(data) {
		OC.Notification.show('There was an error:' + data.statusText);
		hideNotification(3000);
	    }
	});
    });
}

function activateRemoveCreatorButtons() {
    $("input[id^='creator_']").click('click', function(event) {
	// Remove people from backend
	var input_element = $(this);
	var id = input_element.attr("id");
	creator_id = id.replace("creator_", "");
	
	$.ajax({
	    url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
	    type: 'post',
	    dataType: 'json',
	    data: {
		'action': 'remove_people',
		'creator_id': creator_id,
		'full_name': input_element.parent().text()
	    },
	    success: function(data) {
		input_element.parent().remove();
		togglePostCrateToSWORD();
	    },
	    error: function(data) {
		OC.Notification.show('There was an error:' + data.statusText);
		hideNotification(3000);
	    }
	});
    });
}

function makeCreatorsEditable(){
    $('#creators .full_name').editable(OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=edit_creator', {
	id : 'creator_id',
	name : 'new_full_name',
	indicator : '<img src='+OC.imagePath('crate_it', 'indicator.gif')+'>',
	tooltip : 'Double click to edit...',
	event : 'dblclick',
	style : 'inherit'
    });
}

function makeCreatorEditable(creatorObj) {
    creatorObj.editable(OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=edit_creator', {
	id : 'creator_id',
	name : 'new_full_name',
	indicator : '<img src='+OC.imagePath('crate_it', 'indicator.gif')+'>',
	tooltip : 'Double click to edit...',
	event : 'dblclick',
	style : 'inherit'
    });
}

$(document).ready(function() {

    togglePostCrateToSWORD();
	
	$('#download').click('click', function(event) { 
	    if(treeHasNoFiles()){
    		OC.Notification.show('No items in the crate to package');
    		hideNotification(3000);
    		return;
	    }
	    OC.Notification.show('Your download is being prepared. This might take some time if the files are big');
	    hideNotification(3000);
	    window.location = OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=zip';
	    
	});
	
	$('#post').click('click', function(event) { 
        
	    if(treeHasNoFiles()){
    		OC.Notification.show('No items in the crate to package');
    		hideNotification(3000);
		    return;
	    }

        var sword_collection = $('#sword_collection').val();

        $.ajax({
            url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
            type: 'post',
            dataType: 'json',
            data: {'action': 'postzip',
                    'sword_collection': sword_collection},
            success: function(data) {
                OC.Notification.show('Crate posted successfully');
                hideNotification(3000);
            },
            error: function(data) {
                OC.Notification.show('There was an error:' + data.statusText);
                hideNotification(3000);
            }
        });
		
	});

	$('#delete').click('click', function(event) { 
	    var decision = confirm("All data of this crate will lost, are you sure?");

	    if (decision == true) {
		$.ajax({
                    url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
                    type: 'post',
                    dataType: 'json',
                    data: {'action': 'delete_crate'},
                    success: function(data) {
			if (data.status == "Success") {
			    OC.Notification.show('Crate deleted');
			    hideNotification(3000);
			    location.reload();
			}
			else {
			    OC.Notification.show('There was an error:' + data.msg);
			    hideNotification(3000);
			}		
                    }
		});
	    }
	});

	$('#epub').click(function(event) {
		if(treeHasNoFiles()){
			OC.Notification.show('No items in the crate to package');
			hideNotification(3000);
		}
		//get all the html previews available, concatenate 'em all
		OC.Notification.show('Your download is being prepared. This might take some time');
		hideNotification(3000);
		window.location = OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=epub';
	});
	
	$('#clear').click(function(event) {
            var children = $tree.tree('getNodeById', 'rootfolder').children;
            children.forEach(function(node) {
		$tree.tree('removeNode', node);
            });
            saveTree($tree);
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
				hideNotification(3000);
			},
			error: function(data){
				OC.Notification.show(data.statusText);
				hideNotification(3000);
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
		$.ajax({
			url: OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=switch&crate_id='+id,
			type: 'get',
			dataType: 'html',
	        success: function(data) { location.reload() },
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
		
	$('#search_people').click('click', function(event) { 
	    if($.trim($('#keyword').val()).length == 0){
		$('#search_people_results').empty();
		return;
	    }

            $.ajax({
                url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
                type: 'post',
                dataType: 'json',
                data: {'action': 'search_people', 'keyword': $.trim($('#keyword').val())},
                success: function(data) {
		    // populate list of results
		    $('#search_people_results').empty();
		    for (var i = 0; i < data.length; i++) {
			var all_data = data[i]['result-metadata']['all'];
			var id = all_data['id'];
			var honorific = $.trim(all_data['Honorific'][0]);
			var given_name = $.trim(all_data['Given_Name'][0]);
			var family_name = $.trim(all_data['Family_Name'][0]);
			var email = $.trim(all_data['Email'][0]);
			var full_name = "";
			if (honorific)
			    full_name = full_name + honorific + ' ';
			if (given_name)
			    full_name = full_name + given_name + ' ';
			if (family_name)
			    full_name = full_name + family_name;
			if (email)
			    full_name = full_name + ' ' + email;
			$('#search_people_results').append('<li><input id="'
							   + 'search_people_result_' + id
							   + '" type="button" value="Add to creators" />'
							   + '<span id="' + id + '" class="full_name">'
							   + full_name + '</span></li>');
		    }
		    $("input[id^='search_people_result_']").click('click', function(event) {
			// Add people to backend
			var input_element = $(this);
			var id = input_element.attr("id");
			creator_id = id.replace("search_people_result_", "");
			
			$.ajax({
			    url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
			    type: 'post',
			    dataType: 'json',
			    data: {
				'action': 'save_people',
				'creator_id': creator_id,
				'full_name': input_element.parent().text()
			    },
			    success: function(data) {
				$('#creators').append('<li><input id="'
						      + 'creator_' + creator_id
						      + '" type="button" value="Remove" />'
						      + '<span id="' + creator_id + '" class="full_name">'
						      + input_element.parent().text() + '</span></li>');
				input_element.parent().remove();
				
				activateRemoveCreatorButton($('#creator_' + creator_id));
				makeCreatorEditable($('#' + creator_id));
				togglePostCrateToSWORD();
			    },
			    error: function(data) {
				OC.Notification.show('There was an error:' + data.statusText);
				hideNotification(3000);
			    }
			});
		    });
                },
                error: function(data) {
                    OC.Notification.show('There was an error:' + data.statusText);
                    hideNotification(3000);
                }
            });
		
	});

    var description_length = $('#description_length').text();

    $('#edit_description').click(function(event) {
	var old_description = $('#description').text();
	$('#description').text('');
	$('#description').html('<textarea id="crate_description" style="width: 40%;" placeholder="Enter a description of the research data package for this Crate">' + old_description + '</textarea><br/><input id="save_description" type="button" value="Save" /><input id="cancel_description" type="button" value="Cancel" />');
	$('#save_description').click(function(event) {
	    $.ajax({
		url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
		type: 'post',
		dataType: 'json',
		data: {
		    'action': 'describe',
		    'crate_description': $('#crate_description').val()
		},
		success: function(data) {
		    $('#description').html('');
		    $('#description').text(data.description);
		    togglePostCrateToSWORD();
		},
		error: function(data) {
		    OC.Notification.show('There was an error:' + data.statusText);
		    hideNotification(3000);
		}
	    });
	});
	$('#cancel_description').click(function(event) {
	    $('#description').html('');
	    $('#description').text(old_description);
	});
    });

    $.ajax({
        url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
        type: 'get',
        dataType: 'json',
        data: {'action': 'get_items'},
        success: function(data){
            $tree = buildFileTree(data);
        },
        error: function(data){
            var e = data.statusText;
            alert(e);
        }
    });

    max_sword_mb = parseInt($('#max_sword_mb').text());
    max_zip_mb = parseInt($('#max_zip_mb').text());
    crate_size_mb = 0;

    updateCrateSize();    

    $("#dialog-add").dialog({
        autoOpen: false,
    });

    $("#dialog-rename").dialog({
        autoOpen: false,
    });

    $("#dialog-delete").dialog({
        autoOpen: false,
    });
	
    $("#dialog-help").dialog({
        autoOpen: false,
        minWidth: 600,
        position: { my: "right top",
                    at: "right top",
                    of: '#help_button' },
    });

    $('#help_button').on('click', function() {
        $("#dialog-help").dialog('open');
    });

    activateRemoveCreatorButtons();
    makeCreatorsEditable();

});	
