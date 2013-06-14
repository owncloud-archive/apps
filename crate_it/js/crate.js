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
	
	$('#crateList').disableSelection();
	
	$('#crateList li').editable(OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=edit_title', {
		id : 'elementid',
		name : 'new_title',
		indicator : '<img src='+OC.imagePath('crate_it', 'indicator.gif')+'>',
		tooltip : 'Double click to edit...',
		event : 'dblclick'
	});
	
	$('#download').click('click', function(event) { 
		if($('#crateList li').length == 0){
			OC.Notification.show('No items in the crate to package');
			setTimeout(function() {OC.Notification.hide();}, 1000);
			return;
		}
		OC.Notification.show('Your download is being prepared. This might take some time if the files are big');
		setTimeout(function() {OC.Notification.hide();}, 2000);
		window.location = OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=zip';
		
	});
	
	$('#epub').click(function(event) {
		//get all the html previews available, concatenate 'em all
		window.location = OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=epub';
	});
	
	$('#clear').click(function(event) {
		$.ajax(OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=clear');
		$('#crateList').empty();
	});
	
	$('#subbutton').click( function() {
	    $.ajax({
	        url: OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=create',
	        type: 'get',
	        dataType: 'text/html',
	        data: {'action':'create', 'crate_name':$('#crate_input #create').val()},
	        complete: function(data){
	        	$('#crates option').filter(function(){
					return $(this).attr("id") == data.responseText;
				}).prop('selected', true);
			}
	    });
	});
	
	var selected_crate ='';
	
	$.ajax({
		url: OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=get_crate',
		type: 'get',
		dataType: 'text/html',
		complete: function(data){
			selected_crate = data.responseText;
			$('#crates option').filter(function(){
				return $(this).attr("id") == selected_crate;
			}).prop('selected', true);
		}
	});
	
	$('#crates').change(function(){
		var id = $(this).find(':selected').attr("id");
		$.ajax({
			url: OC.linkTo('crate_it', 'ajax/bagit_handler.php')+'?action=switch&crate_id='+id,
			type: 'get',
			complete: function(data){
				$.ajax({
					url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
					type: 'get',
					dataType: 'text/html',
					data: {'action': 'get_items'},
					complete: function(data){
						$('#crateList').empty();
						var obj = JSON.parse(data.responseText);
						if(obj != null){
							var items = [];
							$.each(obj, function(key, value){
								items.push('<li id="'+value['id']+'">'+value['title']+'</li>');
							});
							$('#crateList').append(items.join(''));
						}
					}
				});
			}
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
	
});

