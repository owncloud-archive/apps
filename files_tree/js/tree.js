/**
* ownCloud - Files Tree
*
* @author Bastien Ho (EELV - Urbancube)
* @copyleft 2012 bastienho@urbancube.fr
* @projeturl http://ecolosites.eelv.fr/files-tree
*
* Free Software under creative commons licence
* http://creativecommons.org/licenses/by-nc/3.0/
* Attribution-NonCommercial 3.0 Unported (CC BY-NC 3.0)
* 
* You are free:
* to Share — to copy, distribute and transmit the work
* to Remix — to adapt the work
*
* Under the following conditions:
* Attribution — You must attribute the work in the manner specified by the author or licensor (but not in any way that
* suggests  that they endorse you or your use of the work).
* Noncommercial — You may not use this work for commercial purposes.
*
*/
function FileTree(){
	tree=this;
	$('#fileTable').css('width','86%');
	$('#emptyfolder').css('margin-left','20%');
	$('#content').prepend('<div id="files_tree"><div id="dir_browser"><span class="loading">'+t('files_tree','Loading')+'</span></div><div id="files_tree_switcher"></div><div id="files_tree_refresh" class="bt"></div></div>');
	$('#files_tree_switcher').click(function(){tree.toggle()});
	$('#dir_browser').css('width',$('#files_tree').width()-25).css('height',$('#files_tree').height()-40);
	tree.browse('','');
	$('#files_tree_refresh').css('background-image', 'url('+OC.imagePath('files_tree', 'refresh.svg')+')').click(function(){
		$('#dir_browser').html('<span class="loading">'+t('files_tree','Resfreshing files tree')+'</span>');
		tree.browse('','&refresh=1');		
	});
	tree.sync();
}
FileTree.prototype={	
	toggle:function(){
		if($('#files_tree').width()==10){
			$('#fileList').parent().animate({width:'85%'},500);
			$('#files_tree').animate({width:'14%'},500);
		}
		else{
			$('#files_tree').animate({width:10},500);
			$('#fileList').parent().animate({width:$('#content').width()-11},500);
		}
	},
	sync:function(){
		if($('#fileList').parent().css('display')=='none'){
			$('#files_tree').css('display','none');
		}
		else{
			$('#files_tree').css('display','block');
		}
		$('#files_tree').css('height',Math.max($('#content').outerHeight(),$('#fileList').parent().outerHeight()+38));
		$('#dir_browser').css('width',$('#files_tree').width()-25).css('height',$('#files_tree').outerHeight()-38);
		setTimeout('tree.sync()',2000);
	},
	browse:function(dir,refresh){
		$.ajax({
			type: 'POST',
			url:'./?app=files_tree&getfile=ajax/explore.php&dir='+dir+refresh,
			dataType: 'json',
			async: true,
			success: function (k) {
				$('#dir_browser').html(k.list);
				$('#dir_browser ul').attr('class','collapsed');	
				var stats = k.stat;
				if(k.stat){
					for(var f in k.stat){
						$('#dir_browser ul').filterAttr('data-path',f).attr('class',k.stat[f]);						
					}
				}			
				$('#dir_browser ul ul li:first-child').click(function(){
					tree.toggle_dir($(this).parent());					
				});	
				tree.collex();	
				tree.rescan();
			}
		});	
	},
	// For AJAX Navigation
	browseContent:function(url){
		url=url.replace('app_files&dir=','');
		var lastModified = new Date();
		$("#fileList").fadeOut(500,function(){
			$.ajax({
				type: 'GET',
				url:url,
				dataType: 'html',
				async: true,
				success: function (data) {
					
    				$('#dropdown').remove();
					document.title = $(data).filter('title').text(); 
					$('#dir').val( $(data).find("#dir").val());
				    $('#controls .crumb').remove();	
				    var crumb='';		    
				    $(data).find("#controls .crumb").each(function(){
				    	crumb+=$(this).wrap('<div></div>').parent().html();
				    });
				     $('#controls').prepend(crumb);	
					FileList.update($(data).find("#fileList").html());
					$('#fileList td.filename').each(function(){
						FileActions.display($(this));
					});
				  tree.browse('','');
				  $("#fileList").fadeIn(500);
				}				
			});	
		});
		
	},
	rescan:function(){
		var lechem='';
		var la_path = $('#dir').val().split('/');
		$('#dir_browser li').css('background-image', 'url('+OC.imagePath('files_tree', 'closed.png')+')');
		$('#dir_browser ul.expanded').parent().css('background-image', 'url('+OC.imagePath('files_tree', 'open.png')+')');
		for(var ledir in la_path){
			le_dir=la_path[ledir];
			//if(ledir=='') ledir='/';
			if(ledir>0) lechem+='/';
			lechem+=le_dir;
			$('#dir_browser ul').filterAttr('data-path', lechem).attr('class','expanded');					
			$('#dir_browser a').filterAttr('data-pathname', lechem).css('font-weight','700');
		}		
		$('#dir_browser a').filterAttr('data-pathname', lechem).parent('li').css('background-image', 'url('+OC.imagePath('files_tree', 'open.png')+')');
		$('#dir_browser a,#controls .crumb a, #fileList tr[data-type=dir] a').click(function(event){
			event.preventDefault();
			location.hash = this.pathname+this.search;
			return false;
			//$(this).attr('href', top.location.host+top.location.pathname+'#'+$(this).attr('href').replace('?','#'));
		});
		/*$('#fileList tr').filterAttr('data-type','dir').find('a').each(function(){
			$(this).attr('href',$(this).attr('href').replace('?','#'));
		});*/
		// FOR AJAX NAVIGATION :  
	},
	toggle_dir:function(ul){
		ul.toggleClass('expanded').toggleClass('collapsed');
		if(ul.attr('class')=='expanded'){
			ul.parent('li').css('background-image', 'url('+OC.imagePath('files_tree', 'open.png')+')');
		}
		else{
			ul.parent('li').css('background-image', 'url('+OC.imagePath('files_tree', 'closed.png')+')');
		}
		tree.collex();
		//$(this).parent().;
		$.ajax({
			type: 'POST',
			url: './?app=files_tree&getfile=ajax/save.php&d='+ul.data('path')+'&s='+ul.attr('class'),
			dataType: 'html',
			async: true,
			success: function (k) {
				//nothing to do		
			}
		});
	},
	collex:function(){
		$('ul.collapsed').children('li:first-child').stop().attr('class','c');
		$('ul.expanded').children('li:first-child').stop().attr('class','o');
	}
};

$(document).ready(function(){
  if($('#fileList').length>0) {
	var the_tree=new FileTree();
	// AJAX NAVIGATION
	function on_hashchange(event) {
		var url = window.location.hash.substring(1);
		if (!event || event.type === "DOMContentLoaded")
				return;
		the_tree.browseContent(url);
	}
	$(window).bind('hashchange', on_hashchange);
	on_hashchange(true);
  }
});
