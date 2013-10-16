<?php
namespace OCA\crate_it\lib;

class BagItManager{
	
	var $base_dir; 
	var $preview_dir;
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
	    $this->preview_dir = \OC::$SERVERROOT.'/data/previews/'.$this->user.'/files';
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
		
		//create manifest file if it doesn't exist
		if(!file_exists($this->manifest)){
			$fp = fopen($this->manifest, 'x');
			fclose($fp);
			$this->bag->update();
		}
	}
	
	public static function getInstance(){
			return new BagItManager();
	}
	
	public function createCrate($name){
		if(empty($name)){
			return false;
		}
		$this->initBag($name);
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
	
	public function addToBag($file) {
		$path_parts = pathinfo($file);
		$filename = $path_parts['filename'];
		
		if (\OC\Files\Filesystem::isReadable($file)) {
			list($storage) = \OC\Files\Filesystem::resolvePath($file);
			if ($storage instanceof \OC\Files\Storage\Local) {
				$full_path = \OC\Files\Filesystem::getLocalFile($file);
				/*if(!file_exists(\OC::$SERVERROOT.'/data/fullpath.txt')){
					$fp = fopen(\OC::$SERVERROOT.'/data/fullpath.txt', 'w');
					fwrite($fp, $full_path);
					fclose($fp);
				}*/
				if($file === '/Shared' || is_dir($full_path))
				{
					return "Adding directories not supported yet";
				}
			}
		} elseif (!\OC\Files\Filesystem::file_exists($file)) {
			header("HTTP/1.0 404 Not Found");
			$tmpl = new OC_Template('', '404', 'guest');
			$tmpl->assign('file', $name);
			$tmpl->printPage();
		} else {
			header("HTTP/1.0 403 Forbidden");
			die('403 Forbidden');
		}
		
		//TODO we need to get preview from file_previewer app 
		$preview_file = $this->getPreviewPath($full_path);
		/*if(empty($preview_file))
		{
			return "No preview available. File not added to crate.";
		}*/
		$title = $this->getTitle($preview_file);
		if(empty($title)){
			$title = $file;
		}
		
		//$id = hash('sha256', $full_path);
		$id = md5($full_path);
		if(filesize($this->manifest) == 0) {
			$fp = fopen($this->manifest, 'w');
			$entry = array("titles" => array(array('id' => $id, 'title' => $title,
					'filename' => $full_path)));
			fwrite($fp, json_encode($entry));
			fclose($fp);
		}
		else {
			$contents = json_decode(file_get_contents($this->manifest), true); // convert it to an array.
			$elements = &$contents['titles'];
			foreach ($elements as $item) {
				if($item['id'] === $id) {
					return "File is already in the crate ".$this->selected_crate;
				}
			}
			array_push($elements, array('id' => $id, 'title' => $title,
							'filename' => $full_path));
			$fp = fopen($this->manifest, 'w');
			fwrite($fp, json_encode($contents));
			fclose($fp);
		}
		
		// update the hashes
		$this->bag->update();
		return "File added to the crate ".$this->selected_crate;
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
		//TODO handle exceptions and return suitable value
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
	
	public function getPreview($file_id){
        foreach ($this->getItemList() as $value) {
	    	if($value['id'] === $file_id){
		    	$path_parts = pathinfo($value['filename']);
		        $dir_prefix = \OC::$SERVERROOT.'/data/';
                $dir = str_replace($dir_prefix, "", $path_parts['dirname']);
                $dir_parts = explode("/", $dir);
                if($this->user === $dir_parts[0]){
                	$dir = str_replace($this->base_dir.'/files', "", $path_parts['dirname']);
                }
                else {
                    $dir = '/Shared'.str_replace($dir_prefix.$dir_parts[0].'/files', "", $path_parts['dirname']);
                }
                return $dir.'/'.$path_parts['basename'];
		    }
		}
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
		
		if(count($bag->getBagErrors(true)) == 0){
			foreach ($this->getItemList() as $item){
				$path_parts = pathinfo($item['filename']);
				$bag->addFile($item['filename'], $item['title']);
			}
			$bag->update();
			$bag->package($tmp_dir.'/'.$this->selected_crate, 'zip');
			return $tmp_dir.'/'.$this->selected_crate.'.zip';
		}
		else
		{
			$err = $bag->getBagErrors(true);
			print $err;
		}
	}
	
	public function getFetchData(){
		//read from manifest
		$fp = fopen($this->manifest, 'r');
		$contents = file_get_contents($this->manifest);
		$cont_array = json_decode($contents, true);
		fclose($fp);
		return array_values($cont_array["titles"]);
	}
	
}