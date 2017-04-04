<?php
/**
 * Výkazy práce
 *
 * @author Michal Basl (audant@bobb.cz)
 * @copyright 2011 Základní škola a Odborná škola Zbůch
 */
define('PROTECTED_CODE', True);
define('DIR_ROOT', dirname(__FILE__));
define('DIR_LIBRARY', DIR_ROOT.'/library/');

try {

	if (!require_once(DIR_LIBRARY.'Dissignatio/Layout.class.php')) {
		throw new Exception('Project layout can not start!');
	}

	if (!require_once(DIR_LIBRARY.'Log/Log.class.php')) {
		throw new Exception('Log systemt can not start!');
	}
	$log = new Log(array('error'), 'dissignatio', FALSE);

	$layout = new Layout;
	$layout->loginProject();

	if (!empty($_GET)) {
		switch (key($_GET)) {
			case 'json':
				header('Content-type: application/json');
				echo $layout->getJson(explode('.',$_GET[key($_GET)]));
				break;
			case 'save':
				header('Content-type: application/json');
				echo $layout->saveData(explode('.',$_GET[key($_GET)]));
				break;
			case 'delete':
				header('Content-type: application/json');
				echo $layout->deleteData(explode('.',$_GET[key($_GET)]));
				break;
			case 'lock':
				header('Content-type: application/json');
				echo $layout->lockData(explode('.',$_GET[key($_GET)]));
				break;
			case 'unlock':
				header('Content-type: application/json');
				echo $layout->unlockData(explode('.',$_GET[key($_GET)]));
				break;
			case 'help':
				echo $layout->getHelp($_GET['help']);
				break;
			case 'document':
				header('Content-type: text/html');
				echo $layout->getDocument(explode('.',$_GET[key($_GET)]));
				break;
			case 'pdf':
				if (!require_once(DIR_LIBRARY.'Dissignatio/Printer.class.php')) {
					throw new Exception('Printer layout can not start!');
				}
				$printer = new Printer();
				$printer->setPrintOut(explode('.',str_replace('null', '', $_GET[key($_GET)])));
				$printer->getPdf();
				break;
		}

	}
	else echo $layout->getHomepage();

} catch (Exception $e) {
	echo $e->getMessage();
	$log->error($e->getMessage());
	exit;
}
?>
