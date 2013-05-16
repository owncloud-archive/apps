<?php
namespace OCA\crate_it\lib;

class BagItManager{
	
	private static $instance;
	
	var $base_dir; 
	var $bag_dir;
	var $crate_root;
	
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
			return;
		}
		
		//add the file urls to fetch.txt so when you package the bag,
		//you can populate the data dir with those files
		$fetch_items = $this->bag->fetch->getData();
		$file_exists = false;
		foreach ($fetch_items as $item){
			if($item['url'] === $input_dir.$file){
				$file_exists = true;
				break;
			}
		}
		if(!$file_exists){
			$this->bag->fetch->add($input_dir.$file, $data_dir.$file);
		}
		
		// update the hashes
		$this->bag->update();
	}
	
	public function clearBag(){
		$this->bag->fetch->clear();
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
		
		$items = array();
		$fetch_items = $this->bag->fetch->getData();
		foreach ($fetch_items as $item){
			array_push($items, $item['filename']);
		}
		return $items;
	}
}