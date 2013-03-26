<?php
/**
* @package shorty-tracking an ownCloud url shortener plugin addition
* @category internet
* @author Christian Reiner
* @copyright 2012-2013 Christian Reiner <foss@christian-reiner.info>
* @license GNU Affero General Public license (AGPL)
* @link information http://apps.owncloud.com/content/show.php/Shorty+Tracking?content=152473
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the license, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.
* If not, see <http://www.gnu.org/licenses/>.
*
*/

/**
 * @file lib/l10n.php
 * Translation singleton
 * @author Christian Reiner
 */

/**
 * @class OC_ShortyTracking_L10n
 * @brief Convenient translation singleton, based on the class in the Shorty app
 * @access public
 * @author Christian Reiner
 */
class OC_ShortyTracking_L10n extends OC_Shorty_L10n
{
	/**
	* @var OC_ShortyTracking_L10n::dictionary
	* @brief An internal dictionary file filled from the translation files provided.
	* @access private
	* @author Christian Reiner
	*/

	/**
	* @method OC_ShortyTracking_L10n::__construct
	* @brief
	* @access protected
	* @author Christian Reiner
	*/
	protected function __construct ( $app='shorty_tracking' ) { parent::__construct($app); }

	/**
	* @method OC_ShortyTracking_L10n::identity
	* @brief Used for late state binding to identify the class
	* @description This method must be reimplemented without change in all derived classes
	* @access protected
	* @author Christian Reiner
	*/
	static protected function identity ( ) { return __CLASS__; }

	/**
	* @method OC_ShortyTracking_L10n::instantiate
	* @brief Used during late state binding to instantiates an object of the own class
	* @description This method must be reimplemented without change in all derived classes
	* @access protected
	* @author Christian Reiner
	*/
	static protected function instantiate ( ) { return new OC_ShortyTracking_L10n; }

} // class OC_ShortyTracking_L10n
?>
