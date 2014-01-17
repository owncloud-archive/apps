<?php
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('files_pdfviewer');

header("Content-type: application/pdf");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

$dir = isset($_GET['dir']) ? $_GET['dir'] : '';
$file = isset($_GET['file']) ? $_GET['file'] : '';

$uid = OCP\User::getUser();
$fileview = new \OC\Files\View('/' . $uid . '/files');
$path=$dir . "/" . $file;
$absPath = $fileview->toTmpFile($path);
$tmpDir = get_temp_dir();
$defaultParameters = ' --headless --nologo --nofirststartwizard --invisible --norestore -convert-to pdf -outdir ';
$clParameters = \OCP\Config::getSystemValue('preview_office_cl_parameters', $defaultParameters);
$exec = "libreoffice " . $clParameters . escapeshellarg($tmpDir) . ' ' . escapeshellarg($absPath);
$export = 'export HOME=/' . $tmpDir;
shell_exec($export . "\n" . $exec);
$pdf = $absPath . ".pdf" ;
ob_clean();
flush();
readfile($pdf);
unlink($pdf);
unlink($absPath);
exit;
