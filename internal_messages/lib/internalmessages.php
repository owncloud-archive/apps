<?php

/**
 * ownCloud - internal_messages
 *
 * @author Jorge Rafael Garc�a Ramos
 * @copyright 2012 Jorge Rafael Garc�a Ramos <kadukeitor@gmail.com>
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

class OC_INT_MESSAGES
{
    const flag_group_part = 'gp';
    const flag_group_mesg = 'gm';

    public static function delMessage ( $id , $folder )
    {
        if ($folder == 'inbox') {
            $query = OCP\DB::prepare('UPDATE `*PREFIX*internal_messages` SET `message_delto`=? WHERE `message_id`=?');
            $query->execute(Array(1,$id));
        }
        if ($folder == 'outbox') {
            $query = OCP\DB::prepare('UPDATE `*PREFIX*internal_messages` SET `message_delowner`=? WHERE `message_id`=?');
            $query->execute(Array(1,$id));
        }
    }

    public static function delMessageInConversation( $msg_owner , $id )
    {       
	    if(OCP\USER::getUser() == $msg_owner){
		    $query = OCP\DB::prepare('UPDATE `*PREFIX*internal_messages` SET `message_delowner`=? WHERE `message_id`=?');
		    $query->execute(Array(1,$id));
            }else{
 		    $query = OCP\DB::prepare('UPDATE `*PREFIX*internal_messages` SET `message_delto`=? WHERE `message_id`=?');
		    $query->execute(Array(1,$id));
	    }
    }	

    public static function sendMessage ( $msgfrom , $msgto , $msgcontent , $msgflag = '', $group_conv_id)
    {	

	if(!is_array($msgto[0])){

		$query  = OCP\DB::prepare('(SELECT DISTINCT `message_to` AS `user` FROM `*PREFIX*internal_messages` WHERE `group_conv_id` = ?) UNION (SELECT `message_owner` AS `user` FROM `*PREFIX*internal_messages` WHERE `group_conv_id` = ?)');
		$result = $query->execute(Array( $group_conv_id,$group_conv_id ));
		$users   = $result->fetchAll();
		

		$msgto = array();
		$msgto[0] = array();
		
		foreach($users as $user){

		  if($msgfrom == $user['user']){
			continue;			
		  }
	
		  array_push($msgto[0],$user['user']);
		}		
	}

		
	if ( is_array($msgto[0]) ) {

	     if( count($msgto[0]) > 1 && $group_conv_id == 0){
		$group_conv_id = time();
	     }

             foreach ($msgto[0] as $user) {
                $query = OCP\DB::prepare('INSERT INTO `*PREFIX*internal_messages` (`message_owner`,`message_to`,`message_timestamp`,`message_content`,`group_conv_id`) VALUES (?,?,?,?,?)');
                $query->execute(Array($msgfrom,$user,time(),$msgcontent,$group_conv_id));
	     }
         }

         if ( is_array($msgto[1]) ) {
             foreach ($msgto[1] as $group) {
                $groupUsers = OC_Group::usersInGroup( $group );
                foreach ($groupUsers as $user) {
                    if ($user != $msgfrom) {
                        $query = OCP\DB::prepare('INSERT INTO `*PREFIX*internal_messages` (`message_owner`,`message_to`,`message_timestamp`,`message_content`,`message_flag`) VALUES (?,?,?,?,?)');
                        $query->execute(Array($msgfrom,$user,time(),$msgcontent,self::flag_group_part));
                    }
                }
                $query = OCP\DB::prepare('INSERT INTO `*PREFIX*internal_messages` (`message_owner`,`message_to`,`message_timestamp`,`message_content`,`message_flag`) VALUES (?,?,?,?,?)');
                $query->execute(Array($msgfrom,$group.'(group)',time(),$msgcontent, self::flag_group_mesg ));
            }
         }

         return true;
    }

    public static function unreadMessages($user)
    {
	
	$query  = OCP\DB::prepare('SELECT * FROM `*PREFIX*internal_messages` WHERE `message_to` = ? AND `message_delto` = 0 AND `message_read` = 0');
	$result = $query->execute(Array( $user ));
	$msgs   = $result->fetchAll();
	return count($msgs);
    }

    public static function unreadMessagesOf($user,$current_user)
    {
        $query  = OCP\DB::prepare('SELECT * FROM `*PREFIX*internal_messages` WHERE `message_owner` = ? AND `message_to` = ? AND `message_delto` = 0 AND `message_read` = 0 AND `group_conv_id` = 0');
        $result = $query->execute(Array( $user,$current_user ));
        $msgs   = $result->fetchAll();

        return count($msgs);

    }

    public static function unreadMessagesOfGroupConv($current_user,$id)
    {
        $query  = OCP\DB::prepare('SELECT * FROM `*PREFIX*internal_messages` WHERE `message_to` = ? AND `group_conv_id` = ? AND `message_delto` = 0 AND `message_read` = 0');
        $result = $query->execute(Array( $current_user,$id ));
        $msgs   = $result->fetchAll();

        return count($msgs);

    }


    public static function readMessages($msg_id)
    {
        $query  = OCP\DB::prepare('UPDATE `*PREFIX*internal_messages` SET `message_read` = 1 WHERE `message_id` = ?');
        $result = $query->execute(Array( $msg_id ));
        return $result;
    }

    public static function searchMessages ( $user , $pattern , $folder )
    {
        if ($folder == 'inbox') {
            $query  = OCP\DB::prepare('SELECT * FROM `*PREFIX*internal_messages` WHERE `message_to` = ? AND `message_delto` = 0 AND `message_content` LIKE ? ORDER by `message_timestamp` DESC');
            $result = $query->execute(Array( $user ,'%'.$pattern.'%'));
            $msgs   = $result->fetchAll();
        } else {
            $query  = OCP\DB::prepare('SELECT * FROM `*PREFIX*internal_messages` WHERE `message_owner` = ? AND `message_delowner` = 0 AND `message_flag` != ? AND `message_content` LIKE ? ORDER by `message_timestamp` DESC');
            $result = $query->execute(Array( $user , self::flag_group_part , '%'.$pattern.'%'));
            $msgs   = $result->fetchAll();
        }

        if ( count($msgs) ) {

                if ($folder == 'inbox') { $title  = "<p id=ubication_label>Received Messages</p>" ; } else { $title  = "<p id=ubication_label>Sent Messages</p>" ; }

                $data = $title ;
                $data.= "<table>";
                $data.= "<tbody>";

                foreach ($msgs as $message) {

                    if ($folder == 'inbox') {
                        if ($message['message_read']) { $read = "<tr class=read>"; } else { $read = "<tr class=unread>" ; }
                        $to = '' ;
                        $reply = "<a href=javascript:void(0) msg_owner=".$message['message_owner']." class=\"message_action message_reply\" original-title=Reply><img src=". OC::$WEBROOT . "/apps/internal_messages/img/reply.png></a>" ;
                    } else {
                        $read = "<tr>" ;
                        $to   = " > " . $message['message_to'] ;
                        $reply = '';
                    }

                    $date_time = OCP\Util::formatDate($message['message_timestamp']);
                    $msg_date = substr($date_time,0, ( strlen($date_time) - 7 ) );
                    $msg_time = substr($date_time,-6);

                    $data.= $read ;
                    $data.= "<td><img id=message_photo src=". OC::$WEBROOT . "/?app=user_photo&getfile=ajax%2Fshowphoto.php&user=" . $message['message_owner']."></td>" ;
                    $data.= "<td id=msg_content width=100%>";
                    $data.= "<p id=cell_user>".$message['message_owner'].$to."</p>" ;
                    $data.= "<p message_id=".$message['message_id']." name=message_content>".$message['message_content']."</p>" ;
                    $data.= "</td>";
                    $data.= "<td>" ;
                    $data.= "<p id=cell_date>". $msg_date  ."</p>" ;
                    $data.= "<p id=cell_time>". $msg_time  ."</p>" ;
                    $data.= "</td>" ;
                    $data.= "<td>" ;
                    $data.= $reply ;
                    $data.= "<a href=javascript:void(0) msg_id=".$message['message_id']." class=\"message_action message_delete\" original-title=Delete><img src=". OC::$WEBROOT . "/apps/internal_messages/img/delete.png></a>" ;
                    $data.= "</td>" ;

                    $data.= "</tr>" ;
                }
                $data.= "</tbody>";
                $data.= "</table>";

            } else {

                $data = "<p id=ubication_label>the SEARCHED text doesn't appear..</p>" ;

            }

        return  $data;

    }

    public static function folderMessages( $user , $folder)
    {
        if ($folder == 'inbox') {
            $query  = OCP\DB::prepare('SELECT * FROM `*PREFIX*internal_messages` WHERE `message_to` = ? AND `message_delto` = 0 ORDER by `message_timestamp` DESC');
            $result = $query->execute(Array( $user ));
            $msgs   = $result->fetchAll();
        } else {
            $query  = OCP\DB::prepare('SELECT * FROM `*PREFIX*internal_messages` WHERE `message_owner` = ? AND `message_delowner` = 0 AND `message_flag` != ? ORDER by `message_timestamp` DESC');
            $result = $query->execute(Array( $user , self::flag_group_part ));
            $msgs   = $result->fetchAll();
        }

        if ( count($msgs) ) {

                if ($folder == 'inbox') { $title  = "<p id=ubication_label>Received Messages</p>" ; } else { $title  = "<p id=ubication_label>Sent Messages</p>" ; }

                $data = $title ;
                $data.= "<table>";
                $data.= "<tbody>";

                foreach ($msgs as $message) {

                    if ($folder == 'inbox') {
                        if ($message['message_read']) { $read = "<tr class=read>"; } else { $read = "<tr class=unread>" ; }
                        $to = '' ;
                        $reply = "<a href=javascript:void(0) msg_owner=".$message['message_owner']." class=\"message_action message_reply\" original-title=Reply><img src=". OC::$WEBROOT . "/apps/internal_messages/img/reply.png></a>" ;
                    } else {
                        $read = "<tr>" ;
                        $to   = " > " . $message['message_to'] ;
                        $reply = '';
                    }

                    $date_time = OCP\Util::formatDate($message['message_timestamp']);
                    $msg_date = substr($date_time,0, ( strlen($date_time) - 7 ) );
                    $msg_time = substr($date_time,-6);

                    $data.= $read ;
                    $data.= "<td><img id=message_photo src=". OC::$WEBROOT . "/?app=user_photo&getfile=ajax%2Fshowphoto.php&user=" . $message['message_owner']."></td>" ;
                    $data.= "<td id=msg_content width=100%>";
                    $data.= "<p id=cell_user>".$message['message_owner'].$to."</p>" ;
                    $data.= "<p message_id=".$message['message_id']." name=message_content>".$message['message_content']."</p>" ;
                    $data.= "</td>";
                    $data.= "<td>" ;
                    $data.= "<p id=cell_date>". $msg_date  ."</p>" ;
                    $data.= "<p id=cell_time>". $msg_time  ."</p>" ;
                    $data.= "</td>" ;
                    $data.= "<td>" ;
                    $data.= $reply ;
                    $data.= "<a href=javascript:void(0) msg_id=".$message['message_id']." class=\"message_action message_delete\" original-title=Delete><img src=". OC::$WEBROOT . "/apps/internal_messages/img/delete.png></a>" ;
                    $data.= "</td>" ;

                    $data.= "</tr>" ;
                }
                $data.= "</tbody>";
                $data.= "</table>";

            } else {

                if ($folder == 'inbox') { $data = "<p id=ubication_label>No messages in your INBOX</p>" ; } else { $data = "<p id=ubication_label>Not outbox any message</p>" ; }

            }

        return  $data;

    }



    public static function createConversation($current_user , $user_received){
		
		$query  = OCP\DB::prepare('(SELECT * FROM `*PREFIX*internal_messages` WHERE `message_to` = ? AND `group_conv_id` = 0 AND `message_owner` = ? AND `message_delowner` = 0 AND `ignore_conv` = 0) UNION 						   (SELECT * FROM `*PREFIX*internal_messages` WHERE `message_to` = ? AND `group_conv_id` = 0 AND `message_owner` = ? AND `message_delto` = 0 AND `ignore_conv` = 0)  ORDER by `message_timestamp` ASC');
		$result = $query->execute(Array( $user_received , $current_user ,   $current_user ,$user_received ));
		$msgs   = $result->fetchAll();
 		$data = $title ;
                $data.= "<table id=conversation>";
                $data.= "<tbody>";	
	
                foreach ($msgs as $message) {
		   	
		    $date_time = OCP\Util::formatDate($message['message_timestamp']);
                    $msg_date = substr($date_time,0, ( strlen($date_time) - 7 ) );
                    $msg_time = substr($date_time,-6);
		    
		    if($message['message_owner'] != $current_user && $message['message_read'] == 0){
			$data.= "<tr class=unread>";
		    }else{
			$data.= "<tr class=read>";
		    }
		  
 	                        
                    $data.= "<td><img id=message_photo src=". OC::$WEBROOT . "/?app=user_photo&getfile=ajax%2Fshowphoto.php&user=" . $message['message_owner']."></td>" ;
                    $data.= "<td id=msg_content width=100%>";

                    $data.= "<p id=cell_user>". $message['message_owner'] ."</p>" ;
                    $data.= "<p message_id=".$message['message_id']." name=message_content>".$message['message_content']."</p>" ;
                    $data.= "</td>";
                    $data.= "<td>" ;
                    $data.= "<p id=cell_date>". $msg_date  ."</p>" ;
                    $data.= "<p id=cell_time>". $msg_time  ."</p>" ;
                    $data.= "</td>" ;
                    $data.= "<td>" ;
                    $data.= "<a class=\"message_action message_delete_conv\" href=javascript:void(0) msg_id=".$message['message_id']." msg_owner=".$message['message_owner']. " partner=".$user_received." original-title=Delete><img src=". OC::$WEBROOT . "/apps/internal_messages/img/delete.png></a>" ;
             
		    $data.= "</td>" ;
                    $data.= "</tr>" ;
                }
	
		$data.= "<tr id=reply_message>" ;
 		$data.= "<td><img id=message_photo src=". OC::$WEBROOT . "/?app=user_photo&getfile=ajax%2Fshowphoto.php&user=" . $current_user."></td>" ;
                $data.= "<td id=msg_content width=100%>";
                $data.= "<p id=cell_user>".$current_user."</p>";
		$data.= "<textarea id=conversation_content placeholder=\"Write a reply ...\" cols=50 rows=5 style=\"width: 95%;\"></textarea></td>";
		$group_conv_id = $message['group_conv_id'] == 0 ? " " : " group_conv_id={$message['group_conv_id']} ";

		$data.= "<td colspan=2 style=\"text-align: right; padding-top: 7em; padding-right: 9em\"> <a class=\"button conversation_reply\" msg_owner={$user_received} {$group_conv_id}  href=\"#\"> &nbsp;Reply&nbsp; </a></td>";
           	
		$data.= "</tr>";
                $data.= "</tbody>";
                $data.= "</table>";

	return $data;

   }


	public static function createGroupConversation($current_user,$conv_id){
		$lastTimeStamp = '';
		$query  = OCP\DB::prepare('(SELECT * FROM `*PREFIX*internal_messages` WHERE `group_conv_id` = ? AND `message_owner` = ?) UNION
(SELECT * FROM `*PREFIX*internal_messages` WHERE `group_conv_id` = ? AND `message_to` = ? ) ORDER by `message_timestamp` ASC');
		$result = $query->execute(Array($conv_id,$current_user,$conv_id,$current_user));
		$msgs   = $result->fetchAll();
 		$data = $title ;
                $data.= "<table id=conversation>";
                $data.= "<tbody>";
		
                foreach ($msgs as $message) {
		   	
		    if($lastTimeStamp == $message['message_timestamp']){
			continue;
		    }else{
			$lastTimeStamp = $message['message_timestamp'];
		    }

		    $date_time = OCP\Util::formatDate($message['message_timestamp']);
                    $msg_date = substr($date_time,0, ( strlen($date_time) - 7 ) );
                    $msg_time = substr($date_time,-6);
		    
		    if($message['message_owner'] != $current_user && $message['message_read'] == 0){
			$data.= "<tr class=unread>";
		    }else{
			$data.= "<tr class=read>";
		    }
		  
 	                        
                    $data.= "<td><img id=message_photo src=". OC::$WEBROOT . "/?app=user_photo&getfile=ajax%2Fshowphoto.php&user=" . $message['message_owner']."></td>" ;
                    $data.= "<td id=msg_content width=100%>";

                    $data.= "<p id=cell_user>". $message['message_owner'] ."</p>" ;
                    $data.= "<p message_id=".$message['message_id']." name=message_content>".$message['message_content']."</p>" ;
                    $data.= "</td>";
                    $data.= "<td>" ;
                    $data.= "<p id=cell_date>". $msg_date  ."</p>" ;
                    $data.= "<p id=cell_time>". $msg_time  ."</p>" ;
                    $data.= "</td>" ;
                    $data.= "<td>" ;
                    $data.= "<a class=\"message_action message_delete_conv\" href=javascript:void(0) msg_owner=".$message['message_owner']." original-title=Delete><img src=". OC::$WEBROOT . "/apps/internal_messages/img/delete.png></a>" ;
             
		    $data.= "</td>" ;
                    $data.= "</tr>" ;
                }
	
		$data.= "<tr id=reply_message>" ;
 		$data.= "<td><img id=message_photo src=". OC::$WEBROOT . "/?app=user_photo&getfile=ajax%2Fshowphoto.php&user=" . $current_user."></td>" ;
	        $data.= "<td id=msg_content width=100%>";
	        $data.= "<p id=cell_user>".$current_user."</p>";
		$data.= "<textarea id=conversation_content placeholder=\"Write a reply ...\" cols=50 rows=5 style=\"width: 95%;\"></textarea></td>";


		$data.= "<td colspan=2 style=\"text-align: right; padding-top: 7em; padding-right: 9em\"> <a class=\"button conversation_group_reply\" conv_id=".$message['group_conv_id']."   href=\"#\"> &nbsp;Reply&nbsp; </a></td>";
	   	
		$data.= "</tr>";
	        $data.= "</tbody>";
	        $data.= "</table>";

	return $data;

   }

   public static function getMessagedUsers($current_user){

        $query  = OCP\DB::prepare('(SELECT DISTINCT `message_to` AS `user` FROM `*PREFIX*internal_messages` WHERE `message_owner`=? AND `message_delowner` = 0 AND `group_conv_id`=0) UNION (SELECT DISTINCT `message_owner` AS `user` FROM `*PREFIX*internal_messages` WHERE `message_to`=? AND `message_delto` = 0 AND `group_conv_id`=0)');
        $result = $query->execute(Array($current_user , $current_user ));
	$users   = $result->fetchAll();
        
        $data = $title ;
        $data.= "<table id=messaged_users>";
        $data.= "<tbody>";
        
        foreach ($users as $user){
                            
            $displayUser = $user['user'];          
	    $toBeRead = OC_INT_MESSAGES::unreadMessagesOf($displayUser,$current_user);

	    if($toBeRead > 0){
                $data.= "<tr class=\"users updates\" ref_owner=".$displayUser.">";
	    }else{
                $data.= "<tr class=\"users\" ref_owner=".$displayUser.">";
            }
           
            $data.= "<td><a class=\"preffered_user\" href=javascript:void(0)  ref_owner=".$displayUser."><img id=message_photo src=". OC::$WEBROOT . "/?app=user_photo&getfile=ajax%2Fshowphoto.php&user=".$displayUser."></a></td>" ;
            $data.= "<td id=msg_content width=100%> <a class=\"preffered_user\" href=javascript:void(0)  ref_owner=".$displayUser.">";
            $data.= "<p id=cell_user>".$displayUser."(".$toBeRead.")</p>" ;
            $data.= "</a></td>";
            $data.= "</tr>" ;
            
        }

 	$query  = OCP\DB::prepare('SELECT DISTINCT `group_conv_id` FROM `*PREFIX*internal_messages` WHERE `message_owner` = ?  AND `group_conv_id` > 0 UNION SELECT DISTINCT `group_conv_id` FROM `*PREFIX*internal_messages` WHERE `message_to` = ? AND `group_conv_id` > 0');
        $result = $query->execute(Array($current_user,$current_user));
	$group_conv_ids  = $result->fetchAll();
	
	foreach($group_conv_ids as $id){

		$query  = OCP\DB::prepare('(SELECT DISTINCT `message_to` AS `user` FROM `*PREFIX*internal_messages` WHERE `group_conv_id` = ?) UNION (SELECT `message_owner` AS `user` FROM `*PREFIX*internal_messages` WHERE `group_conv_id` = ?)');
		$result = $query->execute(Array( $id['group_conv_id'],$id['group_conv_id'] ));
		$owners  = $result->fetchAll();

		$ownerList = '';
		foreach($owners as $owner){
		   if($owner['user'] == $current_user){
		   	continue;
		   }
		   $ownerList .= $owner['user'].",";
		}
		
		$ownerList = rtrim($ownerList,",");
	        $toBeRead = OC_INT_MESSAGES::unreadMessagesOfGroupConv($current_user,$id['group_conv_id']);

		    if($toBeRead > 0){
			$data.= "<tr class=\"users updates\" >";
		    }else{
			$data.= "<tr class=\"users\">";
		    }
	   
		$data.= "<td><a class=\"preffered_user\"  href=javascript:void(0) ><img id=message_photo src=". OC::$WEBROOT . "/?app=user_photo&getfile=ajax%2Fshowphoto.php&user=".str_replace(",","&",$ownerList)."></a></td>" ;
		$data.= "<td id=msg_content width=100%> <a class=\"preffered_user\"  group_conv_id=".$id['group_conv_id']." href=javascript:void(0) >";
		$data.= "<p id=cell_user>".$ownerList."(".$toBeRead.")</p>" ;
		$data.= "</a></td>";
		$data.= "</tr>" ; 
	}	


        $data.= "</tbody>";
        $data.= "</table>";
       
        return $data;
   }


}
