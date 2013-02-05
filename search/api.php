<?php

// @TODO remove after debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');

function pr($thing) {
    echo '<pre>';
    if (is_null($thing))
        echo 'NULL';
    elseif (is_bool($thing))
        echo $thing ? 'TRUE' : 'FALSE';
    else
        print_r($thing);
    echo '</pre>' . "\n";
    return ($thing) ? true : false; // for testing purposes
}

/**
 * Returns the HTTP request URL
 * @staticvar string $url
 * @return string
 */
function get_url() {
    $url = 'http';
    // check https
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
        $url .= 's';
    }
    $url .= '://';
    // get server name
    if (isset($_SERVER['SERVER_NAME'])) {
        $url .= $_SERVER['SERVER_NAME'];
    }
    // check port
    if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80') {
        $url .= ':' . $_SERVER['SERVER_PORT'];
    }
    // add uri
    if (isset($_SERVER['REQUEST_URI'])) {
        $url .= $_SERVER['REQUEST_URI'];
    }
    return $url;
}

/**
 * Returns the filename (a.k.a. tokens, but not yet in array
 * form) after the anchor and up to the '?'.
 * @example
 * For a URL like "http://www.example.com/s.php/Resource/14/Version:b?etc", the URI
 * returned will be "Resource/14/Version:b".
 * @return string
 */
function get_file($url, $anchor = 'api.php') {
    $anchored = strpos($url, $anchor);
    if ($anchored === false)
        throw new Exception('Anchor not found in URL', 400);
    $start = $anchored + strlen($anchor) + 1;
    $end = @strpos($url, '?', $start);
    if ($end === false)
        $end = strlen($url);
    $anchored_url = substr($url, $start, $end - $start);

    return $anchored_url;
}

function get_http_method() {
    // check for parameter
    if (array_key_exists('method', $_GET)) {
        return strtouuper($_GET['method']);
    }
    // else:
    return strtoupper($_SERVER['REQUEST_METHOD']);
}

// check if we are a user
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('search');

// find file
$path = get_file(get_url());
$pathinfo = pathinfo($path);
$basename = $pathinfo['basename'];
$dirname = $pathinfo['dirname'];
if (!OC_Filesystem::file_exists($path)) {
    // error
}

// switch on action
switch (get_http_method()) {
    case 'POST':

        break;
    case 'PUT':

        break;
    case 'GET':
        OC_Files::get($dirname, $basename, false);
        break;
    case 'DELETE':
        $result = (bool) OC_files::delete($dirname, $basename);
        send('DELETE', $result);
        break;
    default:
        // error
        break;
}
