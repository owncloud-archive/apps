<?php

/**
 * ownCloud - user_ldap
 *
 * @author Brice Maron
 * @copyright 2011 Brice Maron brice __from__ bmaron _DOT_ net
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
$l=OCP\Util::getL10N('admin_dependencies_chk');
$tmpl = new OCP\Template( 'admin_dependencies_chk', 'settings');

$modules = array();

//Possible status are : ok, error, warning
$modules[] =array(
	'status' => function_exists('json_encode') ? 'ok' : 'error',
	'part'=> 'php-json',
	'modules'=> array('core'),
	'message'=> $l->t('The php-json module is needed by the many applications for inter communications'));

$modules[] =array(
	'status' => function_exists('curl_init') ? 'ok' : 'error',
	'part'=> 'php-curl',
	'modules'=> array('bookmarks'),
	'message'=> $l->t('The php-curl module is needed to fetch the page title when adding a bookmark'));

$modules[] =array(
	'status' => function_exists('imagepng') ? 'ok' : 'error',
	'part'=> 'php-gd',
	'modules'=> array('gallery'),
	'message'=> $l->t('The php-gd module is needed to create thumbnails of your images'));

$modules[] =array(
	'status' => function_exists('ldap_bind') ? 'ok' : 'error',
	'part'=> 'php-ldap',
	'modules'=> array('user_ldap'),
	'message'=> $l->t('The php-ldap module is needed connect to your ldap server'));

$modules[] =array(
	'status' => function_exists('bcadd') ? 'ok' : 'warning',
	'part'=> 'php-bcmath',
	'modules'=> array('user_ldap'),
	'message'=> $l->t('The php-bcmath module is needed to support AD primary groups'));

$modules[] =array(
	'status' => class_exists('ZipArchive') ? 'ok' : 'warning',
	'part'=> 'php-zip',
	'modules'=> array('admin_export','core'),
	'message'=> $l->t('The php-zip module is needed to download multiple files at once'));

$modules[] =array(
	'status' => function_exists('mb_detect_encoding') ? 'ok' : 'error',
	'part'=> 'php-mb_multibyte ',
	'modules'=> array('core'),
	'message'=> $l->t('The php-mb_multibyte module is needed to manage correctly the encoding.'));

$modules[] =array(
	'status' => function_exists('ctype_digit') ? 'ok' : 'error',
	'part'=> 'php-ctype',
	'modules'=> array('core'),
	'message'=> $l->t('The php-ctype module is needed validate data.'));

$modules[] =array(
	'status' => class_exists('DOMDocument') ? 'ok' : 'error',
	'part'=> 'php-xml',
	'modules'=> array('core'),
	'message'=> $l->t('The php-xml module is needed to share files with webdav.'));

$modules[] =array(
	'status' => ini_get('allow_url_fopen') == '1' ? 'ok' : 'error',
	'part'=> 'allow_url_fopen',
	'modules'=> array('core'),
	'message'=> $l->t('The allow_url_fopen directive of your php.ini should be set to 1 to retrieve knowledge base from OCS servers'));

$modules[] =array(
	'status' => class_exists('PDO') ? 'ok' : 'warning',
	'part'=> 'php-pdo',
	'modules'=> array('core'),
	'message'=> $l->t('The php-pdo module is needed to store owncloud data into a database.'));

$modules[] =array(
	'status' => function_exists('iconv') ? 'ok' : 'error',
	'part'=> 'php-iconv',
	'modules'=> array('files_texteditor','news','contacts'),
	'message'=> $l->t('The iconv module is needed to convert data into the correct charset.'));

$modules[] =array(
	'status' => function_exists('finfo_file') ? 'ok' : 'warning',
	'part'=> 'php-fileinfo',
	'modules'=> array('core'),
	'message'=> $l->t('The fileinfo module is highly recommended to enhance file analysis performance.'));

$modules[] =array(
	'status' => function_exists('bzopen') ? 'ok' : 'warning',
	'part'=> 'php-bz2',
	'modules'=> array('core'),
	'message'=> $l->t('The bz2 module is required for extraction of apps.'));

$modules[] =array(
	'status' => class_exists('Collator') ? 'ok' : 'warning',
	'part'=> 'php-intl',
	'modules'=> array('core'),
	'message'=> $l->t('The intl module increases language translation performance.'));

$modules[] =array(
	'status' => function_exists('mcrypt_cbc') ? 'ok' : 'warning',
	'part'=> 'php-mcrypt',
	'modules'=> array('files_encryption'),
	'message'=> $l->t('The mcrypt module increases file encryption performance.'));

$modules[] =array(
	'status' => function_exists('openssl_csr_export_to_file') ? 'ok' : 'warning',
	'part'=> 'php-openssl',
	'modules'=> array('core','files_encryption'),
	'message'=> $l->t('The openssl module is required for accessing HTTPS resources and for files encryption.'));

$modules[] =array(
	'status' => function_exists('ftp_alloc') ? 'ok' : 'warning',
	'part'=> 'php-ftp',
	'modules'=> array('files_external'),
	'message'=> $l->t('The ftp module is required for accessing a FTP storage.'));

$modules[] =array(
	'status' => function_exists('exif_imagetype') ? 'ok' : 'warning',
	'part'=> 'php-exif',
	'modules'=> array('gallery'),
	'message'=> $l->t('The exif module is required for image rotation in the pictures app.'));

$modules[] =array(
	'status' => function_exists('gmp_abs') ? 'ok' : 'warning',
	'part'=> 'php-gmp',
	'modules'=> array('files_external'),
	'message'=> $l->t('The gmp module is recommended to increase performance of SFTP storage access.'));

$modules[] =array(
	'status' => class_exists('Imagick') ? 'ok' : 'warning',
	'part'=> 'php-imagick',
	'modules'=> array('core'),
	'message'=> $l->t('The imagick module is required for preview generation of basic file types.'));

$modules[] =array(
	'status' => (function_exists('xcache_set') || function_exists('apc_add')) ? 'ok' : 'warning',
	'part'=> 'php-xcache, php-apc, php-apcu',
	'modules'=> array('core'),
	'message'=> $l->t('One of the xcache, apc or apcu modules is recommended to increase overall performance.'));

$modules[] =array(
	'status' => \OC_Helper::is_function_enabled('escapeshellcmd') ? 'ok' : 'warning',
	'part'=> 'escapeshellcmd',
	'modules'=> array('core'),
	'message'=> $l->t('The PHP escapeshellcmd function is needed for various internal calls like finding or executing system binaries on non-windows systems. Make sure it is not disabled in the disabled_functions of your php.ini '));

$modules[] =array(
	'status' => \OC_Helper::is_function_enabled('escapeshellarg') ? 'ok' : 'warning',
	'part'=> 'escapeshellarg',
	'modules'=> array('core'),
	'message'=> $l->t('The PHP escapeshellarg function is needed for various internal calls like finding or executing system binaries on non-windows systems. Make sure it is not disabled in the disabled_functions of your php.ini '));

$modules[] =array(
	'status' => \OC_Helper::is_function_enabled('shell_exec') ? 'ok' : 'warning',
	'part'=> 'shell_exec',
	'modules'=> array('core'),
	'message'=> $l->t('The PHP shell_exec function is needed to execute system binaries on non-windows systems. Make sure it is not disabled in the disabled_functions of your php.ini '));

$modules[] =array(
	'status' => \OC_Helper::is_function_enabled('exec') ? 'ok' : 'warning',
	'part'=> 'exec',
	'modules'=> array('core'),
	'message'=> $l->t('The PHP exec function is needed to execute system binaries on non-windows systems. Make sure it is not disabled in the disabled_functions of your php.ini '));

foreach($modules as $key => $module) {
	$enabled = false ;
	foreach($module['modules'] as $app) {
		if(OCP\App::isEnabled($app) || $app=='core') {
				$enabled = true;
		}
	}
	if($enabled == false) unset($modules[$key]);
}

OCP\UTIL::addStyle('admin_dependencies_chk', 'style');
$tmpl->assign( 'items', $modules );

return $tmpl->fetchPage();
