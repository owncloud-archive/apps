<?php

class OC_FilesTree{
	public static function listdir($dir,$dirs_stat){	
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
}
	