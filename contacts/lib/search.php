<?php

namespace OCA\Contacts;

class SearchProvider extends \OC_Search_Provider{
	function search($query) {
		$searchresults = array(	);
		$results = \OCP\Contacts::search($query, array('N', 'FN', 'EMAIL', 'NICKNAME', 'ORG'));
		$l = new \OC_l10n('contacts');
		foreach($results as $result) {
			$vcard = VCard::find($result['id']);
			$link = \OCP\Util::linkTo('contacts', 'index.php').'#' . $vcard['id'];
			$props = array();
			foreach(array('EMAIL', 'NICKNAME', 'ORG') as $searchvar) {
				if(isset($result[$searchvar]) && count($result[$searchvar]) > 0 && strlen($result[$searchvar][0]) > 3) {
					$props = array_merge($props, $result[$searchvar]);
				}
			}
			
			$searchresults[]=new \OC_Search_Result($vcard['fullname'], implode(', ', $props), $link, (string)$l->t('Contact'));//$name,$text,$link,$type
		}
		return $searchresults;
	}
}
