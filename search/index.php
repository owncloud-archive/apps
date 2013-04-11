<?php

// check if we are a user
OCP\User::checkLoggedIn();

// load files
OCP\Util::addStyle('files', 'files');
OCP\Util::addStyle('search', 'search');
OCP\Util::addscript('files', 'jquery.iframe-transport');
OCP\Util::addscript('files', 'jquery.fileupload');
OCP\Util::addscript('files', 'files');
OCP\Util::addscript('files', 'filelist');
OCP\Util::addscript('files', 'fileactions');
OCP\Util::addscript('files', 'keyboardshortcuts');

// activate link
OCP\App::setActiveNavigationEntry('search');

// get results
$query = (isset($_GET['query'])) ? $_GET['query'] : '';
$results = null;
if ($query) {
    $results = OC_Search::search($query);
}

// create HTML table
$files = array();
if (is_array($results)) {
    foreach ($results as $result) {
        // create file
        $_file = $result->fileData;
        // discard versions
        if (strpos($_file['path'], '_versions') === 0) {
            continue;
        }
        // get basename and extension
        $fileinfo = pathinfo($_file['name']);
        $_file['basename'] = $fileinfo['filename'];
        if (!empty($fileinfo['extension'])) {
            $_file['extension'] = '.' . $fileinfo['extension'];
        } else {
            $_file['extension'] = '';
        }
        // get date
        $_file['date'] = OCP\Util::formatDate($_file['mtime']);
        // get directory
        $_file['directory'] = str_replace('/' . $_file['name'], '', $_file['path']);
        // get permissions
        $_file['type'] = ($_file['mimetype'] == 'httpd/unix-directory') ? 'dir' : 'file';
        $permissions = OCP\PERMISSION_READ;
        if (!$_file['encrypted']) {
            $permissions |= OCP\PERMISSION_SHARE;
        }
        if ($_file['type'] == 'dir' && $_file['writable']) {
            $permissions |= OCP\PERMISSION_CREATE;
        }
        if ($_file['writable']) {
            $permissions |= OCP\PERMISSION_UPDATE | OCP\PERMISSION_DELETE;
        }
        $_file['permissions'] = $permissions;
        // add file
        $files[] = $_file;
    }
}
$list = new OCP\Template('files', 'part.list', '');
$list->assign('files', $files);
$list->assign('baseURL', OCP\Util::linkTo('files', 'index.php') . '?dir=');
$list->assign('downloadURL', OCP\Util::linkTo('files', 'download.php') . '?file=');

// populate main template
$tmpl = new OCP\Template('search', 'index', 'user');
$tmpl->assign('files', $files);
$tmpl->assign('fileList', $list->fetchPage());
$tmpl->assign('breadcrumb', $query);
$tmpl->assign('allowZipDownload', intval(OCP\Config::getSystemValue('allowZipDownload', true)));
$tmpl->printPage();