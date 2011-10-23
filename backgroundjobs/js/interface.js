/**
* ownCloud - Background Job
*
* @author Jakob Sack
* @copyright 2011 Jakob Sack owncloud@jakobsack.de
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
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/
$(document).ready(function(){
	/*-------------------------------------------------------------------------
	 * Deleting reports
	 *-----------------------------------------------------------------------*/
	$('#backgroundjobs_deletereport').live('click',function(){
		var id = $(this).parents('tr').first().data('id');
		$.post('ajax/deletereport.php',{'id':id},function(jsondata){
			if(jsondata.status == 'success'){
				$('#backgroundjobs_reportstable [data-id="'+jsondata.data.id+'"]').remove();
			}
			else{
				alert(jsondata.data.message);
			}
		});
		return false;
	});
});
 
