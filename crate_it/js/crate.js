function makeCrateListEditable(){
	$('#crateList li span').editable(OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=edit_title', {
		id : 'elementid',
		name : 'new_title',
		indicator : '<img src='+OC.imagePath('crate_it', 'indicator.gif')+'>',
		tooltip : 'Double click to edit...',
		event : 'dblclick',
		style : 'inherit'
	});
}

function makeViewButtonClickable(){
	$('#crateList li a').click('click', function(event){
		var id = event.target.id;
		if($(this).data("action") === 'delete'){
			return;
		}
		else{
			window.open(OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=preview&file_id='+id, '_blank');
		}
	});
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
	
	makeViewButtonClickable();
	
	$('#crateList').disableSelection();
	makeCrateListEditable();
	
	$('#download').click('click', function(event) { 
		if($('#crateList li').length == 0){
			OC.Notification.show('No items in the crate to package');
			setTimeout(function() {OC.Notification.hide();}, 3000);
			return;
		}
		OC.Notification.show('Your download is being prepared. This might take some time if the files are big');
		setTimeout(function() {OC.Notification.hide();}, 3000);
		window.location = OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=zip';
		
	});
	
	$('#epub').click(function(event) {
		if($('#crateList li').length == 0){
			OC.Notification.show('No items in the crate to package');
			setTimeout(function() {OC.Notification.hide();}, 3000);
			return;
		}
		//get all the html previews available, concatenate 'em all
		OC.Notification.show('Your download is being prepared. This might take some time');
		setTimeout(function() {OC.Notification.hide();}, 3000);
		window.location = OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=epub';
	});
	
	$('#clear').click(function(event) {
		$.ajax(OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=clear');
		$('#crateList').empty();
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
	        	$("#crates").append('<option id='+data+' value='+data+' >'+data+'</option>');
	        	OC.Notification.show('Crate '+data+' successfully created');
				setTimeout(function() {OC.Notification.hide();}, 3000);
	        	//$('#subbutton').attr('disabled', 'disabled');
	        	/*$('#crates option').filter(function(){
					return $(this).attr("id") == data;
				}).prop('selected', true);*/
			},
			error: function(data){
				OC.Notification.show(data.statusText);
				setTimeout(function() {OC.Notification.hide();}, 3000);
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
	
	$('#crateName').bind('dblclick', function() {
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
      });
	
	$('#crates').change(function(){
		var id = $(this).find(':selected').attr("id");
		if(id === "choose"){
			$('#crateList').empty();
			$('#crateName').text("");
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
						if(data != null){
							var items = [];
							$.each(data, function(key, value){
								items.push('<li id="'+value['id']+'"><span id="'+value['id']+'">'+value['title']+'</span><a id="'+
										value['id']+'" style="float:right;">View</a></li>');
							});
							$('#crateList').append(items.join(''));
						}
						makeCrateListEditable();
						makeViewButtonClickable();
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
	


