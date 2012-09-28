<?php

/**
 * ownCloud - Mail app
 *
 * @author Thomas Müller
 * @copyright 2012 Thomas Müller thomas.mueller@tmit.eu
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
 */
namespace OCA_Mail;

class Message{

	// input $mbox = IMAP conn, $mid = message id
	function __construct($conn, $folder_id, $message_id) {
		$this->conn = $conn;
		$this->folder_id = $folder_id;
		$this->message_id = $message_id;

		$this->getmsg();
	}

	// output all the following:
	// the message may in $htmlmsg, $plainmsg, or both
	public $header = NULL;
	public $htmlmsg = '';
	public $plainmsg = '';
	public $charset = '';
	public $attachments = array();

	private $conn, $folder_id, $message_id;


	private function getmsg() {

		$headers = array();

		$fetch_query = new \Horde_Imap_Client_Fetch_Query();
		$fetch_query->envelope();
//		$fetch_query->fullText();
		$fetch_query->bodyText();
		$fetch_query->flags();
		$fetch_query->seq();
		$fetch_query->size();
		$fetch_query->uid();
		$fetch_query->imapDate();

		$headers = array_merge($headers, array(
			'importance',
			'list-post',
			'x-priority'
		));
		$headers[] = 'content-type';

		$fetch_query->headers('imp', $headers, array(
			'cache' => true,
			'peek'  => true
		));

		// $list is an array of Horde_Imap_Client_Data_Fetch objects.
		$ids = new \Horde_Imap_Client_Ids($this->message_id);
		$headers = $this->conn->fetch($this->folder_id, $fetch_query, array('ids' => $ids));

		$this->plainmsg = $headers[$this->message_id]->getBodyText();
//
//		// HEADER
//		$this->header = $this->conn->fetchHeader($this->folder_id, $this->message_id);
//
//		// BODY
//		$bodystructure= $this->conn->getStructure($this->folder_id, $this->message_id);
//		$a= \rcube_imap_generic::getStructurePartData($bodystructure, 0);
//		if ($a['type'] == 'multipart') {
//			for ($i=0; $i < count($bodystructure); $i++) {
//				if (!is_array($bodystructure[$i]))
//					break;
//				$this->getpart($bodystructure[$i],$i+1);
//			}
//		} else {
//			// get part no 1
//			$this->getpart($bodystructure,1);
//		}
	}

	function extract_params($p) {
		// PARAMETERS
		// get all parameters, like charset, filenames of attachments, etc.
		$params = array();
		for ($i=0; $i < count($p); $i++) {
			if (!is_array($p[$i]))
				continue;
			$params[ strtolower( $p[$i][0] ) ] = $p[$i][1];
		}

		return $params;
	}
	
	function getpart($p,$partno) {

		// $partno = '1', '2', '2.1', '2.1.3', etc if multipart, 0 if not multipart

		$params = $this->extract_params($p);

		// DECODE DATA
		$data = $this->conn->handlePartBody($this->folder_id, $this->message_id, false, $partno);

		// Any part may be encoded, even plain text messages, so check everything.
		if (strtolower($p[5])=='quoted_printable') {
			$data = quoted_printable_decode($data);
		}
		if (strtolower($p[5])=='base64') {
			$data = base64_decode($data);
		}
		// no need to decode 7-bit, 8-bit, or binary

		//
		// convert the data  
		//
		if (isset( $params['charset'])) {
			$data = mb_convert_encoding($data, "UTF-8", $params['charset']);
		}

		// ATTACHMENT
		// Any part with a filename is an attachment,
		// so an attached text file (type 0) is not mistaken as the message.
		if (isset($params['filename']) || isset($params['name'])) {
			// filename may be given as 'Filename' or 'Name' or both
			$filename = ($params['filename'])? $params['filename'] : $params['name'];
			//
			// TODO: decode necessary
			//
//			$filename = OC_SimpleMail_Helper::decode($filename);

			$this->attachments[$filename] = $data;  // this is a problem if two files have same name
		}

		// TEXT
		elseif ($p[0]=='text' && $data) {
			// Messages may be split in different parts because of inline attachments,
			// so append parts together with blank row.
			if (strtolower($p[1])=='plain') {
				$this->plainmsg .= trim($data) ."\n\n";
			} else {
				$this->htmlmsg .= $data ."<br><br>";
				$this->charset = $params['charset'];  // assume all parts are same charset
			}
		}

		// EMBEDDED MESSAGE
		// Many bounce notifications embed the original message as type 2,
		// but AOL uses type 1 (multipart), which is not handled here.
		// There are no PHP functions to parse embedded messages,
		// so this just appends the raw source to the main message.
		elseif ($p[0]=='message' && $data) {
			$this->plainmsg .= trim($data) ."\n\n";
		}

		//
		// TODO: is recursion necessary???
		//

		// SUBPART RECURSION
//		if ($p->parts) {
//			foreach ($p->parts as $partno0=>$p2)
//			$this->getpart($mbox,$mid,$p2,$partno.'.'.($partno0+1));  // 1.2, 1.2.1, etc.
//		}
	}

	private function get_attachment_info() {
		$attachment_info = array();
		foreach ($this->attachments as $filename => $data) {
			// TODO: mime-type ???
			array_push($attachment_info, array("filename" => $filename, "size" => strlen($data)));
		}

		return $attachment_info;
	}

	public function as_array() {
		$mail_body = $this->plainmsg;
		$mail_body = ereg_replace("\n","<br>",$mail_body);

		if (empty($this->plainmsg) && !empty($this->htmlmsg)) {
			$mail_body = "<br/><h2>Only Html body available!</h2><br/>";
		}

		//
		// TODO: where do I get these flags
		//
		$flags = array('SEEN' => True, 'ANSWERED' => False, 'FORWARDED' => False, 'DRAFT' => False, 'HAS_ATTACHMENTS' => True);

		//
		// TODO: decode header values
		//
		return array(
			'from' => $this->header->from,
			'to' => $this->header->to,
			'subject' => $this->header->subject,
			'date' => $this->header->timestamp,
			'size' => $this->header->size,
			'flags' => $flags,
			'body' => $mail_body,
			'attachments' => $this->get_attachment_info(),
			'header' => 'TODO: add the header'
		);
	}
}
