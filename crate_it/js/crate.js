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

function makeActionButtonsClickable(){
	$('#crateList tr a').click('click', function(event){
		var id = this.parentNode.parentNode.parentNode.getAttribute('id');
		if($(this).data("action") === 'delete'){
			$.ajax({
				url:OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
				type:'get',
				dataType:'html',
				data:{'action':'delete', 'file_id':id},
				success:function(data){
					$('#crateList tr#'+id).remove();
					hideMetadata();
				},
				error:function(data){
					
				}
			});
		}
		else{
			window.open(OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=preview&file_id='+id, '_blank');
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
	
	$('#crateList').sortable({
		update: function (event, ui) {
            var neworder = [];
            ui.item.parent().children().each(function () {
                neworder.push(this.id);
            });
            $.get(OC.linkTo('crate_it', 'ajax/bagit_handler.php'),{'action':'update','neworder':neworder});
        }
	});
	
	hideMetadata();
	
	makeActionButtonsClickable();
	
	$('#crateList').disableSelection();
	makeCrateListEditable();
	
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

    $('#save_description').click(function() {
        var description = $('#description').val();
        if (description) {
            $.ajax({
                url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
                type: 'post',
                dataType: 'json',
                data: {'action': 'describe', 'description': description},
                success: function(data) {
                    OC.Notification.show('Description saved.');
                    setTimeout(OC.Notification.hide(), 3000);
                },
                error: function(data) {
                    OC.Notification.show('There was an error:' + data.statusText);
                    setTimeout(OC.Notification.hide(), 3000);
                    $('#description').focus();
                }
            });
        }
    });
	
	/*$('#subbutton').attr('disabled', 'disabled');
	$('#crate_input #create').keyup(function() {
        if($(this).val() != '') {
            $('#subbutton').removeAttr('disabled');
        }
     });*/
	
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
	        	//$('#subbutton').attr('disabled', 'disabled');
	        	/*$('#crates option').filter(function(){
					return $(this).attr("id") == data;
				}).prop('selected', true);*/
			},
			error: function(data){
				OC.Notification.show(data.statusText);
				setTimeout(OC.Notification.hide(), 3000);
				$('#crate_input #create').focus();
			}
	    });
	    return false;
	});
	
	/*$.ajax({
		url: OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=get_crate',
		type: 'get',
		dataType: 'html',
		success: function(data){
			$('#crates option').filter(function(){
				return $(this).attr("id") == data;
			}).prop('selected', true);
		},
		error: function(data){
			var e = data.statusText;
			alert(e);
		}
	});*/
	
	/*$('#crateName').bind('dblclick', function() {
        $(this).prop('contentEditable', true);
    }).blur(
        function() {
            $(this).prop('contentEditable', false);
            
            //change the name of the option
            $('#crates').find(':selected').text($('#crateName').text());
            $('#crates').find(':selected').prop("id", $('#crateName').text());
            $('#crates').find(':selected').prop("value", $('#crateName').text());
            $.ajax({
    			url: OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=rename_crate&new_name='+$('#crateName').text(),
    			type: 'get',
    			dataType: 'html',
    			success: function(data){
    				//alert("success");
    			},
    			error: function(data){
    				var e = data.statusText;
    				alert(e);
    			}
    		});
      });*/
	
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
						if(data != null && data.titles.length > 0){
							var items = [];
							$.each(data.titles, function(key, value){
								items.push('<tr id="'+value['id']+'"><td><span class="title" style="padding-right: 150px;">'+
										value['title']+'</span></td><td><div style="padding-right: 22px;"><a data-action="view">View</a></div></td>'+
										'<td><div><a data-action="delete" title="Delete"><img src="/owncloud/core/img/actions/delete.svg"></a></div></td></tr>');
							});
							$('#crateList').append(items.join(''));
							$('#metadata').show();
                            $('#description').val(data.description);
						} else {
							hideMetadata();
						}
						makeCrateListEditable();
						makeActionButtonsClickable();
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
		
	$('#search_people').click('click', function(event) { 
	    if($.trim($('#keyword').val()).length == 0){
		return;
	    }

            $.ajax({
                url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
                type: 'post',
                dataType: 'json',
                data: {'action': 'search_people', 'keyword': $.trim($('#keyword').val())},
                success: function(data) {
		    // populate list of results
		    for (var i = 0; i < data.length; i++) {
			var all_data = data[i]['result-metadata']['all'];
			var id = all_data['id'];
			var honorific = all_data['Honorific'][0];
			var given_name = all_data['Given_Name'][0];
			var family_name = all_data['Family_Name'][0];
			var email = all_data['Email'][0];
			$('#search_people_results').append('<li id="search_people_result"><input id="'
							   + id
							   + '" type="button" value="Add to creators" />'
							   + honorific + ' '
							   + given_name + ' '
							   + family_name
							   + ' &lt;' + email + '&gt;</li>');
		    }
                },
                error: function(data) {
                    OC.Notification.show('There was an error:' + data.statusText);
                    setTimeout(function() {
			OC.Notification.hide();
		    }, 3000);
                }
            });
		
	});

	
	
});	


	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/*
	
	$('#toc').jstree({
		"json_data" : {
			  "data" : [
			      {
			          "data" : "A node",
			          "metadata" : { id : 23 },
			          "children" : [ "Child 1", "A Child 2" ]
			      },
			      {
			          "attr" : { "id" : "li.node.id1" },
			          "data" : {
			              "title" : "Long format demo",
			              "attr" : { "href" : "#" }
			          }
			      }
			  ]
		},
		"plugins" : [ "themes", "json_data", "ui" ]
	}).bind("select_node.jstree", function (e, data) { alert(data.rslt.obj.data("id")); });*/
	
	/*$("#toc").tree({
        ui: {
            animation: 250,
            dots: false,
            theme_name: "classic"
        },
        plugins: {
            checkbox: {}
        },
        callback: {
            check_move : function(node, refNode, type, tree) {
                var rel = $(refNode).prev(".mime-type").attr("rel");
                return (rel != "application/x-fascinator-package");
            },
            onmove: function(node, refNode, type, tree, rollback) {
                jQuery.ajax({
                    type : "POST",
                    url : "$portalPath/actions/manifest.ajax",
                    success:
                        function(data, status) {
                            // We don't do anything on success
                        },
                    error:
                        function (req, status, e) {
                            var data = eval("(" + req.responseText + ")");
                            if (data.message == "Only registered users can access this API") {
                                alert("Please login first!");
                            }
                            alert("Error during move: " + data.message);
                        },
                    data: {
                        func: "move",
                        oid: "$oid",
                        id: "$portalId",
                        nodeId: $(node).attr("id"),
                        refNodeId: $(refNode).attr("id"),
                        parents: getParentIds(node),
                        refParents: getParentIds(refNode),
                        type: type
                    }
                });
            },
            onselect: function(node, tree) {
                var node = $(node);
                var id = node.attr("rel");
                if (id == "blank") {
                    $("#preview").hide();
                } else {
                    $("#content").load(
                        "$portalPath/detail/" + escape(id) + "/?preview=true&inPackage=true",
                        function(data, status, xhr) {
                            function fixLinks(selector, attrName) {
                                $(selector).each(function() {
                                    var attr = $(this).attr(attrName);
                                    if (attr != null) {
                                        // fix for IE7 attr() returning resolved URLs - strip base URL
                                        var href = window.location.href;
                                        hrefBase = href.substring(0, href.lastIndexOf("/"));
                                        attrBase = attr.substring(0, hrefBase.length);
                                        if (hrefBase == attrBase) {
                                            attr = attr.substring(hrefBase.length + 1);
                                        }
                                        if (attr.indexOf("#") != 0 && attr.indexOf("://") == -1 && attr.indexOf("/") != 0) {
                                            var relUrl = "$portalPath/download/" + id + "/";
                                            $(this).attr(attrName, relUrl + escape(attr));
                                        }
                                    }
                                });
                            }
                            fixLinks("#content a", "href");
                            fixLinks("#content img", "src");
                            $("#preview:hidden").fadeIn();
                        });
                }
                var item = node.children("a");
                $("#item-title").val(item.text());
                $("#item-hidden").attr("checked", item.hasClass("item-hidden"));
                $("#item-props:hidden").fadeIn(function() {
                    $("#item-title").focus();
                });
            }
        }
    });*/
	


