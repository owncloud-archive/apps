<?php  
OCP\JSON::callCheck();
$currentdir=$_REQUEST['dir'];
$uid=OCP\User::getUser();
$dirs_stat = OC_Preferences::getValue($uid,'files_tree','dirs_stat','');
if($dirs_stat=='') $dirs_stat=array();
else $dirs_stat=unserialize($dirs_stat);



function listdir($dir,$dirs_stat){	
	$list = \OC\Files\Filesystem::getdirectorycontent($dir);			
	if(sizeof($list)>0){
		$ret='';
		//$d=explode('/',$dir);
		foreach( $list as $i ) {		
			if($i['type']=='dir' && $i['name']!='.') {
				if(!isset($i['directory'])) $i['directory']=''; 
				$ret.='<li class="ui-droppable">
				 	<a href="./?app=files&dir='.$i['directory'].'/'.$i['name'].'" data-pathname="'.$i['directory'].'/'.$i['name'].'">'.$i['name'].'</a>'.listdir($dir.'/'.$i['name'],$dirs_stat).'
					</li>
				';
			}	
			
		}
		if($ret!=''){
			$class='class="collapsed"';
			if($dir=='' || (isset($dirs_stat[$dir]) && $dirs_stat[$dir]=='expanded'))  $class='class="expanded"';
			$ret= '<ul '.$class.' data-path="'.$dir.'"><li></li>'.$ret.'</ul>';
		}
		return $ret;
	}
}

/* Caching results */
$loglist='';
$inilist='';
$dir_cache_file='files_tree_cache';

$cache = new OC_Cache_File;

if(!isset($_REQUEST['refresh']) && null !== $loglist = $cache->get($dir_cache_file)){
	$inilist=$loglist;
}

if($loglist==''){
	$loglist = listdir('',$dirs_stat);
}
if($loglist!='' && $inilist==''){	
	$cache->set($dir_cache_file, $loglist);	
	\OC_Log::write('files_tree', 'cache saved to file ' . $dir_cache_file, \OC_Log::DEBUG);
}
/* Sendind results */
echo $loglist;