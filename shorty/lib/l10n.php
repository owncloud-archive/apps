<?php
/**
* @package shorty an ownCloud url shortener plugin
* @category internet
* @author Christian Reiner
* @copyright 2011-2013 Christian Reiner <foss@christian-reiner.info>
* @license GNU Affero General Public license (AGPL)
* @link information http://apps.owncloud.com/content/show.php/Shorty?content=150401 
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
 * @class OC_Shorty_L10n
 * @brief Convenient translation singleton
 * @access public
 * @author Christian Reiner
 */
class OC_Shorty_L10n
{
	/**
	* @var OC_Shorty_L10n::dictionary
	* @brief An internal dictionary file filled from the translation files provided.
	* @access private
	* @author Christian Reiner
	*/
	private $dictionary;

		/**
	* @var OC_Shorty_L10n::instance
	* @brief Internal array of singleton objects
	* @access private
	* @author Christian Reiner
	*/
	static private $instance = array();

	/**
	* @method OC_Shorty_L10n::__construct
	* @brief
	* @access protected
	* @author Christian Reiner
	*/
	protected function __construct ( $app='shorty' ) { $this->dictionary = new OC_L10n($app); }

	/**
	* @method OC_Shorty_L10n::identity
	* @brief Used for late state binding to identify the class
	* @description This method must be reimplemented without change in all derived classes
	* @access protected
	* @author Christian Reiner
	*/
	static protected function identity ( ) { return __CLASS__; }

	/**
	* @method OC_Shorty_L10n::instantiate
	* @brief Used during late state binding to instantiates an object of the own class
	* @description This method must be reimplemented without change in all derived classes
	* @access protected
	* @author Christian Reiner
	*/
	static protected function instantiate ( ) { return new OC_Shorty_L10n; }

	/**
	* @method OC_Shorty_L10n::t
	* @brief Translates a given string into the users session language and fills any placeolders
	* @param string phrase: Phrase to be translated
	* @param … Further arguments used as filling tokens in the tradition of printf strategies
	* @return string: Translated phrase or the original phrase incase no translation could be found
	* @access public
	* @author Christian Reiner
	*/
	static public function t ( $phrase )
	{
		// create singleton instance, if required
		if ( ! isset(self::$instance[static::identity()]) )
			self::$instance[static::identity()] = static::instantiate ( );
		// handle different styles of how arguments can be handed over to this method
		switch ( func_num_args() )
		{
			case 1:
				return htmlspecialchars ( self::$instance[static::identity()]->dictionary->t ( $phrase, array() ) );

			case 2:
				$arg = func_get_arg(1);
				if ( is_array($arg) )
					return htmlspecialchars ( self::$instance[static::identity()]->dictionary->t ( $phrase, $arg ) );
				else return htmlspecialchars ( self::$instance[static::identity()]->dictionary->t ( $phrase, array($arg) ) );

			default:
				$args = func_get_args();
				array_shift ( $args );
				return htmlspecialchars ( self::$instance[static::identity()]->dictionary->t($phrase,$args) );
		}
  }
} // class OC_Shorty_L10n
?>
