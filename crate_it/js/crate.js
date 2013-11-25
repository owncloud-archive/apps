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


function togglePostCrateToSWORD() {
    $.ajax({
        url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
        type: 'post',
        dataType: 'json',
        data: {'action': 'validate_metadata'},
        success: function(data) {
            $('#post').removeAttr("disabled");
        },
        error: function(data) {
	    $('#post').attr("title", "You cannot post this crate until metadata(title, description, creator) are all set");
            $('#post').attr("disabled", "disabled");
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
            setTimeout(OC.Notification.hide(), 3000);
        },
        error: function(data){
            OC.Notification.show(data.statusText);
            setTimeout(OC.Notification.hide(), 3000);
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
		setTimeout(function() {
		    OC.Notification.hide();
		}, 3000);
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
		setTimeout(function() {
		    OC.Notification.hide();
		}, 3000);
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
			setTimeout(OC.Notification.hide(), 3000);
			return;
		}
		OC.Notification.show('Your download is being prepared. This might take some time if the files are big');
		setTimeout(OC.Notification.hide(), 3000);
		window.location = OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=zip';
		
	});
	
	$('#post').click('click', function(event) { 
	    if($('#crateList tr').length == 0){
		OC.Notification.show('No items in the crate to package');
		setTimeout(function() {
		    OC.Notification.hide();
		}, 3000);
		return;
	    }

            $.ajax({
                url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
                type: 'post',
                dataType: 'json',
                data: {'action': 'postzip'},
                success: function(data) {
                    OC.Notification.show('Crate posted successfully');
                    setTimeout(function() {
			OC.Notification.hide();
		    }, 3000);
                },
                error: function(data) {
                    OC.Notification.show('There was an error:' + data.statusText);
                    setTimeout(function() {
			OC.Notification.hide();
		    }, 3000);
                }
            });
		
	});

	$('#epub').click(function(event) {
		if(treeHasNoFiles()){
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
				setTimeout(function() {
				    OC.Notification.hide();
				}, 3000);
			    }
			});
		    });
                },
                error: function(data) {
                    OC.Notification.show('There was an error:' + data.statusText);
                    setTimeout(function() {
			OC.Notification.hide();
		    }, 3000);
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
        maxlength: 4000,
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
	
    activateRemoveCreatorButtons();
    makeCreatorsEditable();

});	
