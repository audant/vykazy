<?php
/**
 * Výkazy práce - dokumenty
 *
 * @author Michal Basl (audant@bobb.cz)
 * @copyright 2011 Základní škola a Odborná škola Zbůc
 */
if (!defined('PROTECTED_CODE')): die('Nepovoleny pristup! / Hacking attempt!'); endif;

class LeftpanelDocuments {

	private function googleDocsConnect() {
		require_once DIR_ROOT.'/Zend/Loader.php';
		Zend_Loader::loadClass('Zend_Gdata');
		Zend_Loader::loadClass('Zend_Gdata_AuthSub');
		Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
		Zend_Loader::loadClass('Zend_Gdata_Docs');

		$user = 'dokumenty@spcnajdime.cz';
		$pass = 'u2uqa8Qbz6';

		$service = Zend_Gdata_Docs::AUTH_SERVICE_NAME;
		$httpClient = Zend_Gdata_ClientLogin::getHttpClient($user, $pass, $service);
		$gdClient = new Zend_Gdata_Docs($httpClient);
		return $gdClient;
	}

	public function getJsonList($var) {

		$gdClient = self::googleDocsConnect();
		$feed = $gdClient->getDocumentListFeed();
		$docs = array();
		foreach ($feed->entries as $entry) {
			$i++;
			foreach ($entry->getCategory() as $category) {
				if(strpos($category->getScheme(), 'kind')) $kind = $category->getLabel();
				if(strpos($category->getScheme(), 'folder')) $folder = $category->getLabel();
			}

			$links = array();
			foreach ($entry->getLink() as $link) {
				if (in_array($link->getRel(), array('alternate','self','edit'))) {
					$links[$link->getRel()] = $link->getHref(); //str_replace('en_US','cs_CS',$link->getHref());
				}
			}
			$title = (String)$entry->getTitle();
			if (!in_array($folder,array('DISABLED'))) {
				//$urlDocs =  ($kind == 'spreadsheet') ? $links['alternate'].'&rm=embedded' : $links['alternate'].'?rm=embedded';
				$docs[$folder][] = array(
								'docs'=>$title,
								'id'=>(count($docs)+1)*10000+count($docs[$folder])+1,
								'iconCls'=>'dcs_'.$kind,
								'kind'=>$kind,
								'click'=>edit,
								'link'=>base64_encode($links['alternate']), //base64_encode($urlDocs),
								'leaf'=>true);
			}
		}
		ksort($docs);

		foreach ($docs as $key => $doc) {
			$out[] = array(
						'docs'=>$key,
						'id'=>count($out)+1,
						'iconCls'=>'dcs_folder',
						'click'=>'category',
						'children'=>$doc);
		}

		return json_encode($out);
	}

}

?>