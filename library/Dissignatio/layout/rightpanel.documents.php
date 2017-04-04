<?php
/**
 * Výkazy práce - dokumenty
 *
 * @author Michal Basl (audant@bobb.cz)
 * @copyright 2011 Základní škola a Odborná škola Zbůc
 */
if (!defined('PROTECTED_CODE')): die('Nepovoleny pristup! / Hacking attempt!'); endif;

class RightpanelDocuments {

	private $log;

	function __construct() {
		global $log;
		$this->log =& $log;
	}

	public function getDocs($var) {
		$urlDocs =  ($var['dcs_kind'] == 'spreadsheet') ? base64_decode($var['dcs_link']).'&rm=embedded' : base64_decode($var['dcs_link']).'?rm=embedded';
		
		$html = new Pet;
		$html->setTemplate(DIR_LIBRARY.'Dissignatio/layout/document.iframe.pet.tpl');
		$html->dcs_id = $var['dcs_id'];
		$html->dcs_url = $urlDocs;
		$html->dcs_icon = $var['dcs_icon'];
		return json_encode(array('htmlContent'=>$html->fetch()));
	}
}
?>