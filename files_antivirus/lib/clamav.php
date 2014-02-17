<?php

/**
* ownCloud - files_antivirus
*
* @author Manuel Deglado
* @copyright 2012 Manuel Deglado manuel.delgado@ucr.ac.cr
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

class OC_Files_Antivirus {
	
	// The file was not checked (e.g. because the AV daemon wasn't running).
	const SCANRESULT_UNCHECKED = 1;
	// The file was checked and found to be clean.
	const SCANRESULT_CLEAN = 0;
	// The file was checked and found to be infected.
	const SCANRESULT_INFECTED = 1;

	public static function av_scan($path) {
		$path=$path[\OC\Files\Filesystem::signal_param_path];
		if ($path != '') {
			$files_view = \OCP\Files::getStorage("files");
			if ($files_view->file_exists($path)) {
				$result = self::clamav_scan($files_view, $path);
				switch($result) {
					case self::SCANRESULT_UNCHECKED:
						//TODO: Show warning to the user: The file can not be checked
						break;
					case self::SCANRESULT_INFECTED:
						//remove file
						$files_view->unlink($path);
						OCP\JSON::error(array("data" => array( "message" => "Virus detected! Can't upload the file." )));
						$email = OCP\Config::getUserValue(OCP\User::getUser(), 'settings', 'email', '');
						\OCP\Util::writeLog('files_antivirus', 'Email: '.$email, \OCP\Util::DEBUG);
						if (!empty($email) ) {
							$tmpl = new OCP\Template('files_antivirus', 'notification');
							$tmpl->assign('file', $path);
							$tmpl->assign('host', OCP\Util::getServerHost());
							$tmpl->assign('user', OCP\User::getDisplayName());
							$msg = $tmpl->fetchPage();
							$from = OCP\Util::getDefaultEmailAddress('security-noreply');
							OCP\Util::sendMail($email, OCP\User::getUser(), 'Malware detected', $msg, $from, 'ownCloud', 1);
						}
						exit();
						break;

					case self::SCANRESULT_CLEAN:
						//do nothing
						break;
				}
			}
		}
	}

	public static function clamav_scan($fileView, $filepath) {
		$av_mode = \OCP\Config::getAppValue('files_antivirus', 'av_mode', 'executable');
		$result = 0;
		\OCP\Util::writeLog('files_antivirus', 'Scanning file : '.$filepath, \OCP\Util::DEBUG);
		switch($av_mode) {
			case 'daemon':
				return self::_clamav_scan_via_daemon($fileView, $filepath, false);
			case 'socket':
				return self::_clamav_scan_via_daemon($fileView, $filepath, true);
			case 'executable':
				return self::_clamav_scan_via_exec($fileView, $filepath);
		}
	}

	private static function _clamav_scan_get_socket_connection() {
		$av_socket = \OCP\Config::getAppValue( 'files_antivirus', 'av_socket', '' );
        $shandler = stream_socket_client('unix://' . $av_socket, $errno, $errstr, 5);
		if (!$shandler) {
			\OCP\Util::writeLog('files_antivirus', 'Cannot connect to "' . $av_socket . '": ' . $errstr . ' (code ' . $errno . ')', \OCP\Util::ERROR);
			return false;
		}
		return $shandler;
	}

	private static function _clamav_scan_get_daemon_connection() {
		$av_host = \OCP\Config::getAppValue('files_antivirus', 'av_host', '');
		$av_port = \OCP\Config::getAppValue('files_antivirus', 'av_port', '');
		$shandler = ($av_host && $av_port) ? @fsockopen($av_host, $av_port) : false;
		if (!$shandler) {
			\OCP\Util::writeLog('files_antivirus', 'The clamav module is not configured for daemon mode.', \OCP\Util::ERROR);
			return false;
		}
		return $shandler;
	}

	private static function _clamav_scan_via_daemon($fileView, $filepath, $useSocket = false) {
		$av_chunk_size = \OCP\Config::getAppValue('files_antivirus', 'av_chunk_size', '1024');
		if ($useSocket){
			$shandler = self::_clamav_scan_get_socket_connection();
		}
		else{
			$shandler = self::_clamav_scan_get_daemon_connection();
		}

		if(!$shandler) {
			return false;
		}

		$fhandler = $fileView->fopen($filepath, "r");
		if(!$fhandler) {
			\OCP\Util::writeLog('files_antivirus', 'File could not be open.', \OCP\Util::ERROR);
			return false;
		}

		// request scan from the daemon
		fwrite($shandler, "nINSTREAM\n");
		while (!feof($fhandler)) {
			$chunk = fread($fhandler, $av_chunk_size);
			$chunk_len = pack('N', strlen($chunk));
			fwrite($shandler, $chunk_len.$chunk);
		}
		fwrite($shandler, pack('N', 0));
		$response = fgets($shandler);
		\OCP\Util::writeLog('files_antivirus', 'Response :: '.$response, \OCP\Util::DEBUG);
		fclose($shandler);
		fclose($fhandler);

		// clamd returns a string response in the format:
		// filename: OK
		// filename: <name of virus> FOUND
		// filename: <error string> ERROR

		if (preg_match('/.*: OK$/', $response)) {
			return self::SCANRESULT_CLEAN;
		}
		elseif (preg_match('/.*: (.*) FOUND$/', $response, $matches)) {
			$virus_name = $matches[1];
			\OCP\Util::writeLog('files_antivirus', 'Virus detected in file. Clamav reported the virus: '.$virus_name, \OCP\Util::WARN);
			return self::SCANRESULT_INFECTED;
		}
		else {
			// try to extract the error message from the response.
			preg_match('/.*: (.*) ERROR$/', $response, $matches);
			$error_string = $matches[1]; // the error message given by the daemon
			\OCP\Util::writeLog('files_antivirus', 'File could not be scanned. Clamscan reported: '.$error_string, \OCP\Util::WARN);
			return self::SCANRESULT_UNCHECKED;
		}
	}

	private static function _clamav_scan_via_exec($fileView, $filepath) {
		$av_chunk_size = \OCP\Config::getAppValue('files_antivirus', 'av_chunk_size', '1024');
		\OCP\Util::writeLog('files_antivirus', 'Exec scan: '.$filepath, \OCP\Util::DEBUG);
		// get the path to the executable
		$av_path = \OCP\Config::getAppValue('files_antivirus', 'av_path', '/usr/bin/clamscan');

		// check that the executable is available
		if (!file_exists($av_path)) {
			\OCP\Util::writeLog('files_antivirus', 'The clamscan executable could not be found at '.$av_path, \OCP\Util::ERROR);
			return self::SCANRESULT_UNCHECKED;
		}

		$fhandler = $fileView->fopen($filepath, "r");
		if(!$fhandler) {
			\OCP\Util::writeLog('files_antivirus', 'File could not be open.', \OCP\Util::ERROR);
			return self::SCANRESULT_UNCHECKED;
		}

		// using 2>&1 to grab the full command-line output.
		$cmd = escapeshellcmd($av_path) ." - 2>&1";
		$descriptorSpec = array(
			0 => array("pipe","r"), // STDIN
			1 => array("pipe","w")  // STDOUT
		);
		$process = proc_open($cmd, $descriptorSpec, $pipes);
		if (!is_resource($process)) {
			\OCP\Util::writeLog('files_antivirus', 'Error starting clamscan process', \OCP\Util::ERROR);
			fclose($fhandler);
			return self::SCANRESULT_UNCHECKED;
		}

		// write to stdin
		$shandler = $pipes[0];

		while (!feof($fhandler)) {
			$chunk = fread($fhandler, $av_chunk_size);
			fwrite($shandler, $chunk);
		}

		fclose($shandler);
		fclose($fhandler);

		$output = stream_get_contents($pipes[1]);

		fclose($pipes[1]);

		$result = proc_close($process);

		/**
		 * clamscan return values (documented from man clamscan)
		 *  0 : No virus found.
		 *  1 : Virus(es) found.
		 *  X : Error.
		 * TODO: add errors?
		 */
		switch($result) {
			case 0:
				\OCP\Util::writeLog('files_antivirus', 'Result CLEAN!', \OCP\Util::DEBUG);
				return self::SCANRESULT_CLEAN;

			case 1:
				$line = 0;
				$report = array();
				$output = explode("\n", $output);
				while ( strpos($output[$line], "--- SCAN SUMMARY ---") === FALSE ) {
					if (preg_match('/.*: (.*) FOUND$/', $output[$line], $matches)) {
						$report[] = $matches[1];
					}
					$line++;
				}
				\OCP\Util::writeLog('files_antivirus', 'Virus detected in file.  Clamscan reported: '.implode(', ', $report), \OCP\Util::WARN);
				return self::SCANRESULT_INFECTED;

			default:
				$descriptions = array(
				    40 => "Unknown option passed.",
				    50 => "Database initialization error.",
				    52 => "Not supported file type.",
				    53 => "Can't open directory.",
				    54 => "Can't open file. (ofm)",
				    55 => "Error reading file. (ofm)",
				    56 => "Can't stat input file / directory.",
				    57 => "Can't get absolute path name of current working directory.",
				    58 => "I/O error, please check your file system.",
				    62 => "Can't initialize logger.",
				    63 => "Can't create temporary files/directories (check permissions).",
				    64 => "Can't write to temporary directory (please specify another one).",
				    70 => "Can't allocate memory (calloc).",
				    71 => "Can't allocate memory (malloc).",
				);
				$description = (array_key_exists($result, $descriptions)) ? $descriptions[$result] : 'unknown error';

				\OCP\Util::writeLog('files_antivirus', 'File could not be scanned.  Clamscan reported: '.$description, \OCP\Util::WARN);
				return self::SCANRESULT_UNCHECKED;
		}
	}
}
