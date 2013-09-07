<?php

use Sabre\VObject;

/**
 * vCard validator
 *
 * Validates and tries to fix broken vCards before they're being
 * handed over to Sabre and written to storage.
 *
 * @copyright Copyright (C) 2013 Thomas Tanghus
 * @author Thomas Tanghus (http://tanghus.net/)
 */
class OC_Connector_Sabre_CardDAV_ValidatorPlugin extends Sabre_DAV_ServerPlugin {

	/**
	* Reference to Server class
	*
	* @var Sabre_DAV_Server
	*/
	protected $server;

	/**
	* Initializes the plugin and registers event handlers
	*
	* @param Sabre_DAV_Server $server
	* @return void
	*/
	public function initialize(Sabre_DAV_Server $server) {

		$this->server = $server;
		$server->subscribeEvent('beforeWriteContent', array($this, 'beforeWriteContent'), 90);
		$server->subscribeEvent('beforeCreateFile', array($this, 'beforeCreateFile'), 90);

	}

	/**
	* This method is triggered before a file gets updated with new content.
	*
	* This plugin uses this method to ensure that Card nodes receive valid
	* vcard data.
	*
	* @param string $path
	* @param Sabre_DAV_IFile $node
	* @param resource $data
	* @return void
	*/
	public function beforeWriteContent($path, Sabre_DAV_IFile $node, &$data) {

		if (!$node instanceof Sabre_CardDAV_ICard) {
			return;
		}

		$this->validateVCard($data);

	}

	/**
	* This method is triggered before a new file is created.
	*
	* This plugin uses this method to ensure that Card nodes receive valid
	* vcard data.
	*
	* @param string $path
	* @param resource $data
	* @param Sabre_DAV_ICollection $parentNode
	* @return void
	*/
	public function beforeCreateFile($path, &$data, Sabre_DAV_ICollection $parentNode) {

		if (!$parentNode instanceof Sabre_CardDAV_IAddressBook) {
			return;
		}

		$this->validateVCard($data);

	}

	/**
	* Checks if the submitted vCard data is in fact, valid.
	*
	* An exception is thrown if it's not.
	*
	* @param resource|string $data
	* @return void
	*/
	protected function validateVCard(&$data) {

		// If it's a stream, we convert it to a string first.
		if (is_resource($data)) {
			$data = stream_get_contents($data);
		}

		// Converting the data to unicode, if needed.
		$data = Sabre_DAV_StringUtil::ensureUTF8($data);

		try {

			$vobj = VObject\Reader::read($data);

		} catch (VObject\ParseException $e) {

			throw new Sabre_DAV_Exception_UnsupportedMediaType(
				'This resource only supports valid vcard data. Parse error: ' . $e->getMessage()
			);

		}

		if ($vobj->name !== 'VCARD') {
			throw new Sabre_DAV_Exception_UnsupportedMediaType(
				'This collection can only support vcard objects.'
			);
		}

		if (!isset($vobj->UID)) {
			$uid = substr(md5(rand().time()), 0, 10);
			\OCP\Util::writeLog('contacts', __METHOD__.', Adding UID: ' . $uid, \OCP\Util::DEBUG);
			$vobj->add('UID', $uid);
		}

	}
}