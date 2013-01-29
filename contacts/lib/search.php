<?php

namespace OCA\Contacts;

class SearchProvider extends \OC_Search_Provider{
	function search($query) {
		$results = \OCP\Contacts::search($query, array('N', 'FN', 'EMAIL', 'NICKNAME', 'ORG'));
		$l = new \OC_l10n('contacts');
		foreach($results as $result) {
			$vcard = VCard::find($result['id']);
			$link = \OCP\Util::linkTo('contacts', 'index.php').'#' . $vcard['id'];
			$results[]=new \OC_Search_Result($vcard['fullname'], '', $link, (string)$l->t('Contact'));//$name,$text,$link,$type
		}
		return $results;
	}
}
