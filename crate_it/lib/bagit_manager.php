<?php
namespace OCA\crate_it\lib;

class BagItManager{
	
	private static $instance;
	
	var $base_dir; 
	var $bag_dir;
	var $crate_root;
	var $manifest;
	
	var $bag;
	var $user;
	
	private function __construct(){
		$this->user = \OCP\User::getUser();
	    $this->base_dir = \OC::$SERVERROOT.'/data/'.$this->user;
	    $this->crate_root =$this->base_dir.'/crate_it'; 
		
		if(!file_exists($this->crate_root)){
			mkdir($this->crate_root);
		}
		$this->bag_dir = $this->crate_root.'/crate';
		$this->bag = new \BagIt($this->bag_dir);
		
	    //$this->manifest = $this->bag_dir.'/manifest.json';
	    $data_dir = $this->bag->getDataDirectory();
	    $this->manifest = $data_dir.'/manifest.json';
		
		//create manifest file if it doesn't exist
		if(!file_exists($this->manifest)){
			$fp = fopen($this->manifest, 'x');
			fclose($fp);
		}
	}
	
	public static function getInstance(){
		if(!self::$instance){
			self::$instance = new BagItManager();
		}
		return self::$instance;
	}
	
	public function addToBag($dir, $file){
		
		$input_dir = $this->base_dir.'/files';
		$data_dir = 'data';
		
		if(basename($dir) === 'Shared'){
			//TODO need to fetch the url from relevant location
		}
		else if(substr($dir, -1) === '/'){
			$input_dir .= '/';
			$data_dir .= '/';
		}
		else{
			$input_dir .= $dir.'/';
			$data_dir .= $dir.'/';
		}
		if(is_dir($input_dir.'/'.$file)){
			return "Cannot add a directory";
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
			$entry = array("title" => array($file));
			if(filesize($this->manifest) == 0) {
				$fp = fopen($this->manifest, 'w');
				fwrite($fp, json_encode($entry));
				fclose($fp);
			}
			else {
				$contents = json_decode(file_get_contents($this->manifest), true); // convert it to an array.
				$element = $contents['title'];
				array_push($element, $file);
				$contents['title'] = $element;
				$fp = fopen($this->manifest, 'w');
				fwrite($fp, json_encode($contents));
				fclose($fp);
			}
		}
		
		// update the hashes
		$this->bag->update();
		return "File added to crate";
	}
	
	public function clearBag(){
		$this->bag->fetch->clear();
		
		//clear the manifest as well
		$fp = fopen($this->manifest, 'w+');
		//$entry = json_decode(fread($fp), true); // convert it to an array.
		
		//unset($my_var["title"]);
		//fwrite($fp, json_encode($entry));
		fclose($fp);
		
		if(file_exists($this->crate_root.'/packages/crate.zip')){
			unlink($this->crate_root.'/packages/crate.zip');
		}
	}
	
	public function createZip(){
		
		$bag_items = $this->bag->fetch->getData();
		if(count($bag_items) === 0)
		{
			return null;
		}
		$tmp = \OC_Helper::tmpFolder();
		\OC_Helper::copyr($this->bag_dir, $tmp);
		
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
	}
	
	public function getFetchData(){
		
		//$items = array();
		//$fetch_items = $this->bag->fetch->getData();
		
		//read from manifest
		$fp = fopen($this->manifest, 'r');
		$contents = file_get_contents($this->manifest);
		$cont_array = json_decode($contents);
		
		
		foreach ($cont_array as $key=>$value){
			$items = $value;
		}
		return $items;
	}
	
	//GUI tree related operations
	//When init, create manifest.json
	
}