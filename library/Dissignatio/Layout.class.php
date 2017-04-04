<?php
/**
 * Výkazy práce - trida pro layout
 *
 * @author Michal Basl (audant@bobb.cz)
 * @copyright 2011 Základní škola a Odborná škola Zbůc
 */
if (!defined('PROTECTED_CODE')): die('Nepovoleny pristup! / Hacking attempt!'); endif;

class Layout {

	private $_insert_leftPanelModules;

	private $_insert_contentPanelModules = array('start');

	private $_tmpl = null;

	private $_manager;

	private $_connector;

	function __construct() {
		$this->connectDb();

		if (!require_once(DIR_LIBRARY.'Dissignatio/layout/pet.class.php')) {
			throw new Exception('Project can not start template system!');
		}

		$this->_tmpl = new Pet;
		$this->_tmpl->title = htmlspecialchars(TMPL_TITLE);
		$this->_tmpl->header_title = htmlspecialchars(TMPL_HEADER);

		$this->addCss('style/css/ext-all.css');
		$this->addCss('style/css/treegrid.css');
		$this->addCss('style/css/timesheet.css');
		$this->addCss('style/css/Spinner.css');
		$this->addCss('style/css/icons.css');
		$this->addCss('style/css/statusbar.css');
		$this->addJscript('library/ExtJS/ext-base.js');
		$this->addJscript('library/ExtJS/ext-all.js');
		$this->addJscript('library/ExtJS/ext-base64.js');
		$this->addJscript('library/ExtJS/ux-all.js');
		$this->addJscript('library/ExtJS/ext-lang-cs.js');
	}

	private function connectDb() {
		if (!require_once(DIR_LIBRARY.'Dissignatio/layout/setup.project.php')) {
			throw new Exception('Project can not setup!');
		}
		$file_doctrine = DIR_LIBRARY.'Database/Doctrine.php';

		if (file_exists($file_doctrine)) {
			require_once $file_doctrine;
		}
		else throw new Exception('Doctrine can not start!');
		spl_autoload_register(array('Doctrine','autoload'));
		spl_autoload_register(array('Doctrine_Core', 'modelsAutoload'));
		$this->_manager = Doctrine_Manager::getInstance();
		$this->_manager->setAttribute(Doctrine::ATTR_VALIDATE, Doctrine::VALIDATE_ALL);
		$this->_manager->setAttribute(Doctrine::ATTR_EXPORT, Doctrine::EXPORT_ALL);
		$this->_manager->setAttribute(Doctrine::ATTR_AUTO_ACCESSOR_OVERRIDE, true);
		$loaded = Doctrine::loadModels(DIR_LIBRARY.'Database/Models', Doctrine_core::MODEL_LOADING_CONSERVATIVE);
		try {
			$this->_connector = $this->_manager->openConnection(DBTYPE.'://'.USERNAME.':'.PASSWORD.'@'.HOSTSPEC.'/'.DATABASE);
			$this->_connector->setCharset('utf8');
			//Doctrine::generateModelsFromDb(DIR_LIBRARY.'Database/Models', array(), array('generateTableClasses' => true));
		} catch (Exception $e) {
			throw $e;
		}
	}

	public function getDBmanager() {
		return $this->_manager;
	}

	public function getDBconnector() {
		return $this->_connector;
	}

	public function loginProject() {

		if (!require_once(DIR_LIBRARY.'Auth/Auth.class.php')) {
			throw new Exception('Project can not start template system!');
		}
		$auth = new Auth();
		$this->_tmpl->worker_name = Auth::getUserName();
		$this->_tmpl->worker_role = Auth::getUserRole();

		$this->addJscriptGvar(array('name'=>'gvar_user_name', 'value'=>$this->_tmpl->worker_name));
		$this->addJscriptGvar(array('name'=>'gvar_user_role', 'value'=>$this->_tmpl->worker_role));
		$this->addJscriptGvar(array('name'=>'gvar_wrk_id', 'value'=>null));
		$this->addJscriptGvar(array('name'=>'gvar_wrk_name', 'value'=>null));
		$this->addJscriptGvar(array('name'=>'gvar_prj_id', 'value'=>null));
		$this->addJscriptGvar(array('name'=>'gvar_tms_date', 'value'=>null));
		$this->addJscriptGvar(array('name'=>'gvar_tms_asg', 'value'=>null));
		$this->addJscriptGvar(array('name'=>'gvar_pst_id', 'value'=>null));
		$this->addJscriptGvar(array('name'=>'gvar_dcs_id', 'value'=>null));
		$this->addJscriptGvar(array('name'=>'gvar_dcs_link', 'value'=>null));
		
		$this->addModul('vykazy', array('grid', 'grid_noedit'));
		if (Auth::getUserRole()=='ADMIN') {
			$this->addModul('pracovnici', array('workersEditLayout'));
			$this->addModul('projekty', array('projectsEdit_panel'));
		}
		$this->addModul('dokumenty', array('documentEditLayout'));
		$this->_tmpl->timesheet_js = $this->getTimesheetJs();
	}

	public function addCss($css) {
		if (file_exists($css)) {
			$insert_css = $this->_tmpl->addLoop('insert_css');
			$insert_css->css_file = $css;
		}
	}

	public function addJscriptGvar($gvar) {
		$insert_gvar = $this->_tmpl->addLoop('insert_gvar');
		$insert_gvar->name = $gvar['name'];
		$insert_gvar->value = $gvar['value'];
	}

	public function addJscript($js) {
		if (file_exists($js)) {
			$insert_js = $this->_tmpl->addLoop('insert_js');
			$insert_js->js_file = $js;
		}
	}

	public function addModul($mod, $cnt) {
		$this->addJscript('library/Dissignatio/timesheet/timesheet.'.$mod.'.js');
		$this->_insert_leftPanelModules[] = 'leftPanel_'.$mod;
		$this->_insert_contentPanelModules = array_merge($this->_insert_contentPanelModules, $cnt);
	}

	public function getJson($arg) {
		if (!require_once(DIR_LIBRARY.'Dissignatio/'.$arg[0].'/'.$arg[1].'.'.$arg[2].'.php')) {
			throw new Exception('Project can not setup!');
		}

		$class = ucfirst($arg[1]).ucfirst($arg[2]);
		$instance = new $class();
		$function = 'getJson'.ucfirst($arg[3]);

		unset($arg[0],$arg[1],$arg[2],$arg[3]);
		foreach ($arg as $item) {
			$i = explode(':', $item);
			$var[$i[0]] = $i[1];
		}

		return $instance->$function($var);
	}

	public function getDocument($arg) {
		if (!require_once(DIR_LIBRARY.'Dissignatio/'.$arg[0].'/'.$arg[1].'.'.$arg[2].'.php')) {
			throw new Exception('Project can not setup!');
		}

		$class = ucfirst($arg[1]).ucfirst($arg[2]);
		$instance = new $class();
		$function = 'get'.ucfirst($arg[3]);

		unset($arg[0],$arg[1],$arg[2],$arg[3]);
		foreach ($arg as $item) {
			$i = explode(':', $item);
			$var[$i[0]] = $i[1];
		}

		return $instance->$function($var);
	}

	public function saveData($arg) {
		if (!require_once(DIR_LIBRARY.'Dissignatio/'.$arg[0].'/'.$arg[1].'.'.$arg[2].'.php')) {
			throw new Exception('Project can not setup!');
		}

		$class = ucfirst($arg[1]).ucfirst($arg[2]);
		$instance = new $class();
		$function = 'save'.ucfirst($arg[3]);

		return $instance->$function(array_merge($_POST, $_GET));
	}

	public function deleteData($arg) {
		if (!require_once(DIR_LIBRARY.'Dissignatio/'.$arg[0].'/'.$arg[1].'.'.$arg[2].'.php')) {
			throw new Exception('Project can not setup!');
		}

		$class = ucfirst($arg[1]).ucfirst($arg[2]);
		$instance = new $class();
		$function = 'delete'.ucfirst($arg[3]);

		return $instance->$function(array_merge($_POST, $_GET));
	}

	public function lockData($arg) {
		if (!require_once(DIR_LIBRARY.'Dissignatio/'.$arg[0].'/'.$arg[1].'.'.$arg[2].'.php')) {
			throw new Exception('Project can not setup!');
		}

		$class = ucfirst($arg[1]).ucfirst($arg[2]);
		$instance = new $class();
		$function = 'lock'.ucfirst($arg[3]);

		return $instance->$function(array_merge($_POST, $_GET));
	}

	public function unlockData($arg) {
		if (!require_once(DIR_LIBRARY.'Dissignatio/'.$arg[0].'/'.$arg[1].'.'.$arg[2].'.php')) {
			throw new Exception('Project can not setup!');
		}

		$class = ucfirst($arg[1]).ucfirst($arg[2]);
		$instance = new $class();
		$function = 'unlock'.ucfirst($arg[3]);

		return $instance->$function(array_merge($_POST, $_GET));
	}

	private function getTimesheetJs() {
		$tjs = new Pet;
		$tjs->setTemplate(DIR_LIBRARY.'Dissignatio/layout/homepage.js.tpl');
		$tjs->leftPanelModules = implode(",", $this->_insert_leftPanelModules);
		$tjs->contentPanelModules = implode(",", $this->_insert_contentPanelModules);
		return $tjs->fetch();
	}

	public function getHomepage() {
		$this->_tmpl->setTemplate(DIR_LIBRARY.'Dissignatio/layout/homepage.pet.tpl');
		return $this->_tmpl->fetch();
	}

	public function getHelp($arg) {
		$inhelp = new Pet;
		$inhelp->setTemplate(DIR_ROOT.'/help/inner.help.pet.tpl');
		$inhelp->title = htmlspecialchars(TMPL_TITLE);
		$inhelp->header_title = htmlspecialchars(TMPL_HEADER);

		$path = DIR_ROOT.'/svninfo.xml';
		if (file_exists($path)) {
			$xml = simplexml_load_file($path);
			$inhelp->svn_version = $xml->entry->commit['revision'];
			$inhelp->svn_date = $xml->entry->commit->date;
		}

		$inner_help = $inhelp->fetch();

		if ($arg == 'inner') {
			$out = $inner_help;
		} else {
			$enhelp = new Pet;
			$enhelp->setTemplate(DIR_ROOT.'/help/enveloped.help.pet.tpl');
			$enhelp->title = htmlspecialchars(TMPL_TITLE);
			$enhelp->inner_help = $inner_help;
			$out = $enhelp->fetch();
		}

		return $out;
	}

}

?>