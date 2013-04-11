<?php  
OCP\JSON::callCheck();
$currentdir=$_REQUEST['dir'];
$uid=OCP\User::getUser();

function listdir($dir){
	$dir = stripslashes($dir);
	$list = \OC\Files\Filesystem::getdirectorycontent($dir);			
	if(sizeof($list)>0){
		$ret='';
		foreach( $list as $i ) {		
			if($i['type']=='dir' && $i['name']!='.') {
				$ret.='<li><a href="./?app=files&dir='.$dir.'/'.$i['name'].'" data-pathname="'.$dir.'/'.$i['name'].'">';
				$ret.=$i['name'].'</a>';
				$ret.=listdir($dir.'/'.$i['name']);
				$ret.='</li>';
			}			
		}
		if($ret!=''){
			$ret= '<ul data-path="'.$dir.'"><li></li>'.$ret.'</ul>';
		}
		return stripslashes($ret);
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
	$loglist = listdir('');
}
if($loglist!='' && $inilist==''){	
	$cache->set($dir_cache_file, $loglist);	
	\OC_Log::write('files_tree', 'cache saved to file ' . $dir_cache_file, \OC_Log::DEBUG);
}
/* Sendind results */
$dirs_stat = OC_Preferences::getValue($uid,'files_tree','dirs_stat','');
if($dirs_stat=='') $dirs_stat=array();
else $dirs_stat=unserialize($dirs_stat);
	
echo json_encode(
	array(
		'list'=>$loglist,
		'stat'=>$dirs_stat
	)
);