/**
* ownCloud - ocDownloader plugin
*
* @author Xavier Beurois
* @copyright 2012 Xavier Beurois www.djazz-lab.net
* 
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either 
* version 3 of the License, or any later version.
* 
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*  
* You should have received a copy of the GNU Lesser General Public 
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
* 
* Minified with http://fmarcia.info/jsmin/test.html
* 
*/

$(document).ready(function(){$('#provsel').chosen();$('#provsel').change(function(){if($(this).val()==0){$('#pr_url').css('display','none');}else{if($(this).val()=="web"){$('#pr_url').css('display','block');$('#pr_logo').attr('src',OC.imagePath('ocdownloader','providers/web.png'));$('#geturl').attr('rel','web');}else{$.ajax({type:'POST',url:OC.linkTo('ocdownloader','ajax/providers/get.php'),dataType:'json',data:{prov:$(this).val()},async:false,success:function(s){if(s.pr_id){$('#pr_url').css('display','block');$('#pr_logo').attr('src',OC.imagePath('ocdownloader','providers/'+s.pr_logo));$('#geturl').attr('rel','pr_'+s.pr_id);}}});}}});$("#geturl").button({text:true}).bind('click',function(){getUrlAction($(this));});function getUrlAction(elt){if($('#pr_txt_url').val()!=''){$('#action').html('<img src="'+OC.imagePath('ocdownloader','loader.gif')+'" />');$.ajax({type:'POST',url:OC.linkTo('ocdownloader','ajax/download/geturl.php'),dataType:'json',data:{url:$('#pr_txt_url').val(),pr:elt.attr('rel')},async:false,error:function(e){alert(e.responseText);},success:function(s){$('#pr_txt_url').val('');$('#action').html('<button rel="'+elt.attr('rel')+'" id="geturl" title="'+elt.attr('title')+'">'+elt.attr('title')+'</button>');$("#geturl").button({text:true}).bind('click',function(){getUrlAction($(this));});if(s.error){$('#result_state').css('color','#FF0000');$('#result_state').html(s.error);}else{if(s.ok){$('#result_state').css('color','#01CA30');$('#result_state').html(s.ok);}}}});}else{alert('Please, provide a file URL !!');}}});