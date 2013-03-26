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
 * @file appinfo/app.php
 * @brief Basic registration of plugin at ownCloud
 * @author Christian Reiner
 */

OC::$CLASSPATH['OC_Shorty_Exception']     = 'shorty/lib/exception.php';
OC::$CLASSPATH['OC_Shorty_L10n']          = 'shorty/lib/l10n.php';
OC::$CLASSPATH['OC_Shorty_Tools']         = 'shorty/lib/tools.php';
OC::$CLASSPATH['OC_Shorty_Type']          = 'shorty/lib/type.php';
OC::$CLASSPATH['OC_Shorty_Query']         = 'shorty/lib/query.php';
OC::$CLASSPATH['OC_ShortyTracking_L10n']  = 'shorty_tracking/lib/l10n.php';
OC::$CLASSPATH['OC_ShortyTracking_Hooks'] = 'shorty_tracking/lib/hooks.php';
OC::$CLASSPATH['OC_ShortyTracking_Query'] = 'shorty_tracking/lib/query.php';

try
{
	// only plug into the mother app 'Shorty' if that one is installed AND has the minimum required version:
	// minimim requirement currently is as specified below:
	$SHORTY_VERSION_MIN = '0.3.15';
	if ( OCP\App::isEnabled('shorty') )
	{
		// check Shorty version: installed version required
		$insV = explode ( '.', OCP\App::getAppVersion('shorty') );
		$reqV = explode ( '.', $SHORTY_VERSION_MIN );
		if (  (sizeof($reqV)==sizeof($insV))
			&&(		  ($reqV[0]<$insV[0])
				||	( ($reqV[0]==$insV[0])&&($reqV[1]<$insV[1]) )
				||	( ($reqV[0]==$insV[0])&&($reqV[1]==$insV[1])&&($reqV[2]<=$insV[2]) ) ) )
		{
			OCP\Util::connectHook ( 'OC_Shorty', 'post_deleteShorty', 'OC_ShortyTracking_Hooks', 'deleteShortyClicks');
			OCP\Util::connectHook ( 'OC_Shorty', 'registerClick',     'OC_ShortyTracking_Hooks', 'registerClick');
			OCP\Util::connectHook ( 'OC_Shorty', 'registerActions',   'OC_ShortyTracking_Hooks', 'registerActions');
			OCP\Util::connectHook ( 'OC_Shorty', 'registerIncludes',  'OC_ShortyTracking_Hooks', 'registerIncludes');
			OCP\Util::connectHook ( 'OC_Shorty', 'registerQueries',   'OC_ShortyTracking_Hooks', 'registerQueries');
		}
		else throw new OC_Shorty_Exception ( "App 'Shorty Tracking' requires app 'Shorty' in version >= %s.%s.%s !", $reqV );
	}
	else throw new OC_Shorty_Exception ( "App 'Shorty Tracking' requires app 'Shorty' to be installed !" );
}
catch ( Exception $e )
{
	OC_App::disable    ( 'shorty_tracking' );
	OCP\Util::writeLog ( 'shorty_tracking', "Disabled because runtime requirement not met: ".$e->getMessage(), OCP\Util::WARN );
}
?>
