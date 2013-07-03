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
			return "Please specify name";
		}
		$this->initBag($name);
		return "New crate created successfully";
	}
	
	public function switchCrate($name){
		if(empty($name)){
			return "Please specify name";
		}
		$this->initBag($name);
		$this->selected_crate = $name;
		$_SESSION['crate_id'] = $name;
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
	
	public function addToBag($dir, $file){
		
		$input_dir = $this->base_dir.'/files';
		$data_dir = 'data';
		$title = '';
		$path_parts = pathinfo($file);
		$filename = $path_parts['filename'];
		
		if($file === 'Shared' || is_dir($input_dir.'/'.$file)){
			return "Adding directories not supported yet";
		}
		
		if(basename($dir) === 'Shared'){
			//TODO need to fetch the url from relevant location
			return "Adding shared files not supported yet";
		}
		else if(substr($dir, -1) === '/'){
			$input_dir .= '/';
			$data_dir .= '/';
			$relative_path = $file;
			//Get the title from the preview
			$preview_file = $this->preview_dir.'/'.$file.'/'.$filename.'.html';
			$title = $this->getTitle($preview_file);
			if(empty($title)){
				$title = $file;
			}
		}
		else{
			$input_dir .= $dir.'/';
			$data_dir .= $dir.'/';
			$relative_path = substr($dir, 1).'/'.$file;
			$preview_file = $this->preview_dir.'/'.substr($dir, 1).'/'.$file.'/'.$filename.'.html';
			$title = $this->getTitle($preview_file);
			if(empty($title)){
				$title = substr($dir, 1).'/'.$file;
			}
		}
		
		//add the file urls to fetch.txt so when you package the bag,
		//you can populate the data dir with those files
		$fetch_items = $this->bag->fetch->getData();
		$file_exists = false;
		foreach ($fetch_items as $item) {
			if($item['url'] === $input_dir.$file) {
				$file_exists = true;
				break;
			}
		}
		if($file_exists) {
			return "File is already in crate";
		}
		else {
			$this->bag->fetch->add($input_dir.$file, $data_dir.$file);
			
			//add an entry to manifest as well
			//TODO id and title
			$id = hash('sha256', $relative_path);
			
			$entry = array("titles" => array(array('id' => $id, 'title' => $title,
					'filename' => $input_dir.$file)));
			if(filesize($this->manifest) == 0) {
				$fp = fopen($this->manifest, 'w');
				fwrite($fp, json_encode($entry));
				fclose($fp);
			}
			else {
				$contents = json_decode(file_get_contents($this->manifest), true); // convert it to an array.
				$elements = &$contents['titles'];
				array_push($elements, array('id' => $id, 'title' => $title,
				'filename' => $input_dir.$file));
				//$contents['titles'] = $elements;
				$fp = fopen($this->manifest, 'w');
				fwrite($fp, json_encode($contents));
				fclose($fp);
			}
		}
		
		// update the hashes
		$this->bag->update();
		return "File added to crate";
	}
	
	private function getTitle($file) {
		if (preg_match('/<title>(.+)<\/title>/', file_get_contents($file), $matches)
				&& isset($matches[1] )) {
			return $matches[1];
		}
		else {
			return "";
		}
	}
	
	public function clearBag(){
		$this->bag->fetch->clear();
		
		//clear the manifest as well
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
	
	//TODO
	public function editTitle($id, $newvalue){
		//edit title here
		$contents = json_decode(file_get_contents($this->manifest), true);
		$items = &$contents['titles'];
		foreach ($items as &$item) {
			if($item['id'] === $id){
				$item['title'] = $newvalue;
			}
		}
		$fp = fopen($this->manifest, 'w+');
		fwrite($fp, json_encode($contents));
		fclose($fp);
		//TODO handle exceptions and return suitable value
		return true;
	}
	
	public function createEpub(){
		//create temp html from manifest
		$pre_content = "<html><body><h1>Table of Contents</h1><p style='text-indent:0pt'>";
		
		$source_dir = $this->base_dir.'/files';
		
		$tempfile = tempnam(sys_get_temp_dir(),'');
		if (file_exists($tempfile)) {
			unlink($tempfile);
		}
		mkdir($tempfile);
		
		foreach ($this->getItemList() as $value) {
			$path_parts = pathinfo($value['filename']);
			
			$prev_file = $path_parts['filename'].'.htm';
				
			//get html files from the fascinator - do a solr search get storage id
			//Save them to a tmp folder
			if($source_dir === $path_parts['dirname']) {
				$query = 'full_path:"/data/'.$this->user.'/files/'.$path_parts['basename'] .'"';
			}
			else {
				$s = substr($path_parts['dirname'], strlen($source_dir));
				$query = 'full_path:"/data/'.$this->user.'/files'. $s.'/'.$path_parts['basename'] .'"';
			}
			
			$storage_id = \OCA\file_previewer\lib\Solr::getStorageId($query);
			
			$url = $this->fascinator['downloadURL'].$storage_id.'/'.$prev_file;
			
			//Download file
			$comm = "wget -p --convert-links -nH -P ".$tempfile."/previews ".$url;
			system($comm, $retval);
			
			if($retval === 0) {
				$prev_path = $tempfile.'/previews/portal/default/download/'.$storage_id;
				//make links to those htmls in temp dir
				$pre_content .= "<a href='".$prev_path."/".$prev_file."'>".$prev_file."</a><br>";
			}
			
		}
		$manifest_html = $pre_content."</p></body></html>";
		
    	if (is_dir($tempfile)) {
    		$fp = fopen($tempfile.'/manifest.html', 'w+');
			fwrite($fp, $manifest_html);
			fclose($fp);
			//feed it to calibre
			$command = 'ebook-convert '.$tempfile.'/manifest.html '.$tempfile.'/temp.epub --level1-toc //h:h1 --level2-toc //h:h2 --level3-toc //h:h3';
			system($command, $retval);
    	}
		//send the epub to user
		return $tempfile.'/temp.epub';
		
	}
	
	private function getItemList(){
		$contents = json_decode(file_get_contents($this->manifest), true); // convert it to an array.
		return $contents['titles'];
	}
	
	public function createZip(){
		
		$bag_items = $this->bag->fetch->getData();
		if(count($bag_items) === 0)
		{
			return null;
		}
		$tmp = \OC_Helper::tmpFolder();
		\OC_Helper::copyr($this->crate_dir, $tmp);
		
		//create a bag at the outputDir
		$bag = new \BagIt($tmp);
		
		if(count($bag->getBagErrors(true)) == 0){
			//use the fetch file to add data to bag, but don't use $bag->fetch->download(), 
			//yea I know it's weird but have to do at this time
			$fetch_items = $bag->fetch->getData();
			foreach ($fetch_items as $item){
				$bag->addFile($item['url'], $item['filename']);
			}
			$bag->update();
		
			//TODO see if there's one already
			//check if it's latest, if so only create the package
			if(!file_exists($this->crate_root.'/packages')){
				mkdir($this->crate_root.'/packages');
			}
			$zip_file = $this->crate_root.'/packages/crate';
			$bag->package($zip_file, 'zip');
			
			return $zip_file.'.zip';
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