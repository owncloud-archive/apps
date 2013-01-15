<?php

/**
 * ownCloud - App Framework
 *
 * @author Bernhard Posselt
 * @copyright 2012 Bernhard Posselt nukeawhale@gmail.com
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

// file that holds the classpath defintions
DEFINE('CLASSPATH_DIR', '../appinfo/classpath.php');


// mock class so we can load the defintions in the app directory
class OC {
    public static $CLASSPATH = array();
}


// to execute without owncloud, we need to create our own classloader
spl_autoload_register(function ($className){

    // load existing defintions
    $classPath = __DIR__ . '/' . CLASSPATH_DIR;
    require_once($classPath);

    if(array_key_exists($className, OC::$CLASSPATH)){
        require_once(__DIR__ . '/../../../' . OC::$CLASSPATH[$className]);
    }

});
