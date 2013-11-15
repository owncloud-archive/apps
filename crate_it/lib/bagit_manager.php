<?php
namespace OCA\crate_it\lib;

class BagItManager{
	
	var $base_dir; 
	var $crate_dir;
	var $crate_root;
	var $manifest;
	
	var $selected_crate;
	var $bag;
	var $user;
	
	/**
	 * Set the values to following variables
	 * 
	 * $fascinator - fascinator url
	 * $base_dir - oc user's data directory
	 * $crate_root - crates directory ( $base_dir/crates )
	 * $selected_crate - currently selected crate
	 * $manifest - manifest which holds files info - id,title and absolute path
	 */
	private function __construct(){
		$this->user = \OCP\User::getUser();
		
		$config_file = \OC::$SERVERROOT.'/data/cr8it_config.json';
		if(file_exists($config_file)) {
			$configs = json_decode(file_get_contents($config_file), true); // convert it to an array.
			$this->fascinator = $configs['fascinator'];
		}
		else {
			echo "No configuration file";
			return;
		}
		
	    $this->base_dir = \OC::$SERVERROOT.'/data/'.$this->user;
	    $this->crate_root =$this->base_dir.'/crates'; 
		
		if(!file_exists($this->crate_root)){
			mkdir($this->crate_root);
		}
		if(empty($_SESSION['crate_id'])){
			$this->createCrate('default_crate');
			$this->selected_crate = 'default_crate';
			$_SESSION['crate_id'] = 'default_crate';
		}
		else {
			$this->initBag($_SESSION['crate_id']);
			$this->selected_crate = $_SESSION['crate_id'];
		}
		
	    $data_dir = $this->bag->getDataDirectory();
	    $this->manifest = $data_dir.'/manifest.json';

	}
	
	public static function getInstance(){
			return new BagItManager();
	}
	
	public function showPreviews(){
		return $this->fascinator['status'];
	}
	
	public function createCrate($name){
		if(empty($name)){
			return false;
		}
		$this->initBag($name);
		$fp = fopen($this->manifest, 'x');
		$entry = array('titles' => array(), 'description' => 'Please enter a description...',
			'vfs' => array('id' => 'rootfolder', 'label' => '/', 'folder' => true, 'children' => array()));
		fwrite($fp, json_encode($entry));
		fclose($fp);
		$this->bag->update();
		return $name;
	}
	
	public function switchCrate($name){
		if(empty($name)){
			return false;
		}
		$this->initBag($name);
		$this->selected_crate = $name;
		$_SESSION['crate_id'] = $name;
		return true;
	}
	
	private function initBag($name){
		$this->crate_dir = $this->crate_root.'/'.$name;
		$this->bag = new \BagIt($this->crate_dir);
	    $data_dir = $this->bag->getDataDirectory();
	    $this->manifest = $data_dir.'/manifest.json';		
	}
	
	public function getSelectedCrate(){
		return $this->selected_crate;
	}
	
	public function getCrateList(){
		$cratelist = array();
		if ($handle = opendir($this->crate_root)) {
			$filteredlist = array('.', '..', 'packages');
			while (false !== ($file = readdir($handle))) {
				if (!in_array($file, $filteredlist)) {
					array_push($cratelist, $file);
				}
			}
			closedir($handle);
		}
		return $cratelist;
	}

	public function getBaggedFiles() {
		return json_encode($this->getManifestData()['vfs']);
	}

	public function setBaggedFiles() {

	}


	public function addToBag($file) {
		$path_parts = pathinfo($file);
		$filename = $path_parts['filename'];
		
		if (\OC\Files\Filesystem::isReadable($file)) {
			// list($storage) = \OC\Files\Filesystem::resolvePath($file);
			// if ($storage instanceof \OC\Files\Storage\Local) {
			// 	$full_path = \OC\Files\Filesystem::getLocalFile($file);
			// 	if(!file_exists(\OC::$SERVERROOT.'/data/fullpath.txt')){
			// 		$fp = fopen(\OC::$SERVERROOT.'/data/fullpath.txt', 'w');
			// 		fwrite($fp, $full_path);
			// 		fclose($fp);
			// 	}
			// 	if($file === '/Shared' || is_dir($full_path))
			// 	{
			// 		return "Adding directories not supported yet";
			// 	}
			// }
		} elseif (!\OC\Files\Filesystem::file_exists($file)) {
			header("HTTP/1.0 404 Not Found");
			$tmpl = new OC_Template('', '404', 'guest');
			$tmpl->assign('file', $name);
			$tmpl->printPage();
		} else {
			header("HTTP/1.0 403 Forbidden");
			die('403 Forbidden');
		}
		
		$contents = json_decode(file_get_contents($this->manifest), true); // convert it to an array.
		$elements = &$contents['titles'];
		$vfs = &$contents['vfs']['children'];
		$this->addPath($elements, $file, $vfs);
		$fp = fopen($this->manifest, 'w');
		fwrite($fp, json_encode($contents));
		fclose($fp);
		
		// update the hashes
		$this->bag->update();
		return "File added to the crate ".$this->selected_crate;
	}

	private function getFullPath($file) {
		return \OC\Files\Filesystem::getLocalFile($file);
	}

	private function addPath(&$titles, $path, &$vfs) {
		if (\OC\Files\Filesystem::is_dir($path)) {
			$vfs_entry = array('label' => basename($path), 'id' => 'folder', 'children' => array());
			$vfs_contents = &$vfs_entry['children'];
			$paths = \OC\Files\Filesystem::getDirectoryContent($path);
			foreach ($paths as $sub_path) {
				$rel_path = substr($sub_path['path'], strlen('files/'));
				$this->addPath($titles, $rel_path, $vfs_contents);
			}
		} else {
			$full_path = $this->getFullPath($path);
			$id = md5($full_path);
			$file_entry = array('id' => $id, 'filename' => $full_path);
			$vfs_entry = array('id' => $id, 'label' => basename($path));
			array_push($titles, $file_entry);
		}
		array_push($vfs, $vfs_entry);
	}


	private function getTitle($file) {
		if (preg_match('/<title>([^<]+)<\/title>/', file_get_contents($file), $matches)
				&& isset($matches[1] )) {
			return $matches[1];
		}
		else {
			return "";
		}
	}
	
	public function clearBag(){
		//clear the manifest 
		$fp = fopen($this->manifest, 'w+');
		fclose($fp);
		$this->bag->update();
		
		if(file_exists($this->crate_root.'/packages/crate.zip')){
			unlink($this->crate_root.'/packages/crate.zip');
		}
	}
	
	public function updateOrder($neworder){
		$shuffledItems = array();
		//Get id and loop
		foreach ($neworder as $id) {
			foreach ($this->getItemList() as $item) {
				if($id === $item['id'])
				{
					array_push($shuffledItems, $item);
				}
			}
		}
		$newentry = array("titles" => $shuffledItems);
		$fp = fopen($this->manifest, 'w+');
		fwrite($fp, json_encode($newentry));
		fclose($fp);
		$this->bag->update();
	}
	
	public function renameCrate($new_name){
		rename($this->crate_dir, $this->crate_root.'/'.$new_name);
		$this->switchCrate($new_name);
		return true;
	}
	
	//TODO
	public function editTitle($id, $newvalue){
		//edit title here
		$contents = json_decode(file_get_contents($this->manifest), true);
		$items = &$contents['titles'];
		foreach ($items as &$item) {
			if($item['id'] === $id) {
				$item['title'] = $newvalue;
			}
		}
		$fp = fopen($this->manifest, 'w+');
		fwrite($fp, json_encode($contents));
		fclose($fp);
		$this->bag->update();
		//TODO handle exceptions and return suitable value
		return true;
	}
	

	public function setDescription($description) {
		$contents = json_decode(file_get_contents($this->manifest), true);
		$contents['description'] = $description;
		$fp = fopen($this->manifest, 'w+');
		fwrite($fp, json_encode($contents));
		fclose($fp);
		return true;
	}

	//remove an item from manifest
	public function removeItem($id){
		$contents = json_decode(file_get_contents($this->manifest), true);
		$items = &$contents['titles'];
	    
		for ($i = 0; $i < count($items); $i++) {
			if($items[$i]['id'] ==  $id)
			{
				array_splice($items, $i, 1);
			}
		}
		$fp = fopen($this->manifest, 'w+');
		fwrite($fp, json_encode($contents));
		fclose($fp);
		return true;
	}
	
	/**
	 * Get file path from file id
	 * 
	 * @param string $file_id
	 * @return string
	 */
	public function getPathFromFileId($file_id){
        foreach ($this->getItemList() as $value) {
	    	if($value['id'] === $file_id){
	    		$dir = $this->getParentDirectory($value['filename']);
                return $dir.'/'.$path_parts['basename'];
		    }
		}
	}
	
	/**
	 * Get the directory where a file resides.
	 * 
	 * $file The file path
	 */
	private function getParentDirectory($filePath){
		$path_parts = pathinfo($filePath);
		$dir_prefix = \OC::$SERVERROOT.'/data/';
		$dir = str_replace($dir_prefix, "", $path_parts['dirname']);
		$dir_parts = explode("/", $dir);
		if($this->user === $dir_parts[0]){
			$dir = str_replace($this->base_dir.'/files', "", $path_parts['dirname']);
		}
		else {
			$dir = '/Shared'.str_replace($dir_prefix.$dir_parts[0].'/files', "", $path_parts['dirname']);
		}
		return $dir;
	}
	
	private function getPreviewPath($full_path){
		$path_parts = pathinfo($full_path);
		$prev_file = $path_parts['filename'].'.htm';
		$temp_dir = \OC_Helper::tmpFolder();
		$storage_id = \OCA\file_previewer\lib\Solr::getStorageId('full_path:"'.md5($full_path).'"');
			
		$url = $this->fascinator['downloadURL'].$storage_id.'/'.$prev_file;
		$url = str_replace(' ', '%20', $url);
			
		//Download file
		$comm = "wget -p --convert-links -nH -P ".$temp_dir."previews ".$url;
		system($comm, $retval);
			
		if($retval === 0) {
			$prev_path = $temp_dir.'previews/portal/default/download/'.$storage_id;
			//make links to those htmls in temp dir
			return $prev_path."/".$prev_file;
		}
		else{
			return Null;
		}
	}
	
	public function createEpub(){
		//create temp html from manifest
		$pre_content = "<html><body><h1>Table of Contents</h1><p style='text-indent:0pt'>";
		
		$source_dir = $this->base_dir.'/files';
		
		$temp_dir = \OC_Helper::tmpFolder();

		foreach ($this->getItemList() as $value) {
			$path_parts = pathinfo($value['filename']);
			
			$prev_file = $path_parts['filename'].'.htm';
			$prev_title = $value['title'];
				
			$storage_id = \OCA\file_previewer\lib\Solr::getStorageId('full_path:"'.md5($value['filename']).'"');
			
			$url = $this->fascinator['downloadURL'].$storage_id.'/'.$prev_file;
			$url = str_replace(' ', '%20', $url);
			
			//Download file
			$comm = "wget -p --convert-links -nH -P ".$temp_dir."previews ".$url;
			system($comm, $retval);
			
			if($retval === 0) {
				$prev_path = $temp_dir.'previews/portal/default/download/'.$storage_id;
				//make links to those htmls in temp dir
				$pre_content .= "<a href='".$prev_path."/".$prev_file."'>".$prev_title."</a><br>";
			}
			
		}
		$epub_title = $temp_dir.$this->selected_crate.'.html';
		$manifest_html = $pre_content."</p></body></html>";
    	if (is_dir($temp_dir)) {
    		$fp = fopen($epub_title, 'w+');
			fwrite($fp, $manifest_html);
			fclose($fp);
			//feed it to calibre
			$command = 'ebook-convert '.$epub_title.' '.$temp_dir.'temp.epub --level1-toc //h:h1 --level2-toc //h:h2 --level3-toc //h:h3';
			system($command, $retval);
    	}
		//send the epub to user
		return $temp_dir.'temp.epub';
	}
	
	private function getItemList(){
		$contents = json_decode(file_get_contents($this->manifest), true); // convert it to an array.
		return $contents['titles'];
	}
	
	public function createZip(){
		$tmp_dir = \OC_Helper::tmpFolder();
		\OC_Helper::copyr($this->crate_dir, $tmp_dir);
		$bag = new \BagIt($tmp_dir);
		
		$manifest_data = $this->getManifestData();

		$metadata = '<html><head><title>'.$this->selected_crate.'</title></head><body><article>
					<h1><u>"'.$this->selected_crate.'" Data Package README file</u></h1>
					<section resource="creative work" typeof="http://schema.org/CreativeWork">
							  <h1>Package Title</h1>
							  <span property="http://schema.org/name http://purl.org/dc/elements/1.1/title">'.$this->selected_crate.'</span>
							  <h1>Package Creation Date</h1>
							  <span content="'.date("Y-m-d H:i:s").'" property="http://schema.org/dateCreated">'.date("F jS, Y").'</span>
							  <h1>Package File Name</h1>
							  <span property="http://schema.org/name">'.$this->selected_crate.'.zip</span>
							  <h1>ID</h1>
							  <span property="http://schema.org/name">'.$id.'</span>
							  <h1>Description</h1>
							  <span property="http://schema.org/description">'.$manifest_data['description'].'</span>
							  <h1>Software Information</h1>
							  <section property="http://purl.org/dc/terms/creator" typeof="http://schema.org/softwareApplication" resource="">
							  	<table>
							  		<tbody>
							  			<tr>
							  				<td>Generating Software Application</td>
							  				<td property="http://schema.org/name">Cr8it</td>
							  			</tr>
							  			<tr>
							  				<td>Software Version</td>
							  				<td property="http://schema.org/softwareVersion">v0.1</td>
							  			</tr>
							  			<tr>
							  				<td>URLs</td>
							  				<td>
							  					<span><a href="https://github.com/uws-eresearch/apps" property="http://schema.org/url">
							  								https://github.com/uws-eresearch/apps</a></span>
							  					<span><a href="http://eresearch.uws.edu.au/blog/projects/projectsresearch-data-repository/" property="http://schema.org/url">
							  								http://eresearch.uws.edu.au/blog/projects/projectsresearch-data-repository</a></span>
							  				</td>
							  			</tr>
							  		</tbody>
							  	</table>
							  </section>
						   </section>
						   <h1>Organisational Information</h1>
						   <section property="http://purl.org/dc/terms/references" typeof="http://schema.org/Organisation" resource="">
							  	<table>
							  		<tbody>
							  			<tr>
							  				<td></td>
							  				<td></td>
							  			</tr>
							  		</tbody>
							  	</table>
						   </section>
						   <h1>Summary of Files</h1>
							 <table>
							  		<thead>
							  			<tr>
							  				<th>Name</th>
							  				<th>Title</th>
							  				<th>Type</th>
							  				<th>Size</th>
							  				<th>Research</th>
							  				<th>Download</th>
							  				<th>View</th>
							  		    <tr>
							  		</thead>
							  		<tbody>';
		if(count($bag->getBagErrors(true)) == 0){
			foreach ($this->getItemList() as $item){
				$path_parts = pathinfo($item['filename']);
				$dir = $this->getParentDirectory($item['filename']);
				$bag->addFile($item['filename'], $dir.'/'.$path_parts['basename']);
				
				$name = empty($dir) ? $path_parts['basename'] : $dir.'/'.$path_parts['basename'];
				
				//Please note that this doesn't work in windows environments
				$file = escapeshellarg($item['filename']);
				$mime = shell_exec("file -bi " . $file);
				$mime = substr($mime, 0, strpos($mime,';'));
				
				$size = $this->humanReadableFileSize(filesize($item['filename']));
				$sec = '<tr>
							<td>'.$name.'</td>
							<td>'.$item['title'].'</td>
							<td>'.$mime.'</td>
							<td>'.$size.'</td>
							<td></td>
							<td><a href="">Download</a></td>
							<td><a href="">View</a></td>
						</tr>';
				$metadata .= $sec;
			}
			$metadata .= '</article></body></html>';
			//now add the readme file
			$readme = $tmp_dir.'data/README.html';
			$fp = fopen($readme, 'w+');
			fwrite($fp, $metadata);
			fclose($fp);
			$bag->update();
			$bag->package($tmp_dir.'/'.$this->selected_crate, 'zip');
			return $tmp_dir.'/'.$this->selected_crate.'.zip';
		} else {
			$errors = $bag->getBagErrors(true);
			print var_dump($errors);
		}
	}
	
	private function humanReadableFileSize($bytes, $decimals = 2) {
	  $sz = 'BKMGTP';
	  $factor = floor((strlen($bytes) - 1) / 3);
	  return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
	}
	
	public function lookUpMint($for_code, $level){
		
		try {
			
			//User needs to get the mint url from config
			$url = 'http://basset.uws.edu.au:9001/mint/ANZSRC_FOR/opensearch/lookup?count=999&level='.$level;
			
			//now call the mint
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$content = curl_exec($ch);
			
			//You get a json file as content. process this and show the result
			 
			$result = curl_getinfo($ch);
			curl_close($ch);
			
			if(empty($content))
			{
				$content = "No data available";
			}
			else {
				$content_array = json_decode($content);
				$results = $content_array->results;
				return $results;
			}
		} 
		catch (Exception $e) {
			die("error");
		}
	}

	public function getManifestData(){
		//read from manifest
		$fp = fopen($this->manifest, 'r');
		$contents = file_get_contents($this->manifest);
		$cont_array = json_decode($contents, true);
		fclose($fp);
		return $cont_array;
	}
	
	public function setManifestData($data){
		$fp = fopen($this->manifest, 'w+');
		fwrite($fp, json_encode($data));
		fclose($fp);
	}

}
