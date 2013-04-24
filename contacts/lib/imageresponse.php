<?php
/**
 * @author Thomas Tanghus, Bart Visscher
 * Copyright (c) 2013 Thomas Tanghus (thomas@tanghus.net)
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Contacts;
use OCA\AppFramework\Http\Response;


/**
 * A renderer for images
 */
class ImageResponse extends Response {
	/**
	 * @var OC_Image
	 */
	protected $image;

	public function __construct($image = null) {
		//\OCP\Util::writeLog('contacts', __METHOD__.' request: '.print_r($request, true), \OCP\Util::DEBUG);
		parent::__construct($request);
		$this->setImage($image);
	}

	public function setImage(\OC_Image $image) {
		if(!$image->valid()) {
			throw new InvalidArgumentException(__METHOD__. ' The image resource is not valid.');
		}
		$this->image = $image;
		$this->addHeader('Content-Type', $image->mimeType());
	}

	/**
	 * Return the image data stream
	 * @return Image data
	 */
	public function render() {
		if(is_null($this->image)) {
			throw new BadMethodCallException(__METHOD__. ' Image must be set either in constructor or with setImage()');
		}
		return $this->image->data();
	}

}