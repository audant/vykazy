<?php
/**
 * Výkazy práce - projekty
 *
 * @author Michal Basl (audant@bobb.cz)
 * @copyright 2011 Základní škola a Odborná škola Zbůc
 */
if (!defined('PROTECTED_CODE')): die('Nepovoleny pristup! / Hacking attempt!'); endif;

class RightpanelProjects {

	private $log;

	function __construct() {
		global $log;
		$this->log =& $log;
	}

	public function getJsonProject($var) {
		$project = Doctrine_Core::getTable('Projects')->findBy('prj_id', $var['prj_id'])->toArray();
		return json_encode(array('project'=>$project));
	}

	public function getJsonMonitors($var) {
		$qer = Doctrine_Core::getTable('Monitors')->findBy('mnt_prj', $var['prj_id']) ->toArray();
		return json_encode(array('monitors'=>$qer));
	}

	public function saveProject($var) {
		try {
			$project = Doctrine_Core::getTable('Projects')->find($var['prj_id']);
			$project->prj_name = $var['prj_name'];
			$project->prj_nazev = $var['prj_nazev'];
			$project->prj_podpora = $var['prj_podpora'];
			$project->prj_regc = $var['prj_regc'];
			$project->prj_state = $var['prj_state'];
			$project->save();
			return json_encode(array('success'=>true, 'project'=>$project->toArray()));
		} catch (Exception $e) {
			$this->log->error('['.__CLASS__.'.'.__FUNCTION__.']: '.$e->getMessage());
			return json_encode(array('success'=>false, 'error'=>$e->getMessage()));
		}
	}

	public function saveMonitor($var) {
		try {
			$monitor = Doctrine_Core::getTable('Monitors')->find($var['mnt_id']);
			$monitor->$var['field'] = $var['value'];
			$monitor->save();
			return json_encode(array('success'=>true, 'monitor'=>$monitor->toArray()));
		} catch (Exception $e) {
			$this->log->error('['.__CLASS__.'.'.__FUNCTION__.']: '.$e->getMessage());
			return json_encode(array('success'=>false, 'error'=>$e->getMessage()));
		}
	}

	public function saveNewMonitor($var) {
		try {
			$date = getdate();
			$mnt = new Monitors();
			$mnt->mnt_prj = $var['prj_id'];
			$mnt->mnt_name = 'Nová zpráva';
			$mnt->mnt_estm = $date['mon'];
			$mnt->mnt_esty = $date['year'];
			$mnt->mnt_letm = $date['mon'];
			$mnt->mnt_lety = $date['year'];
			$mnt->save();
			return json_encode(array('success'=>true, 'monitor'=>$mnt->toArray()));
		} catch (Exception $e) {
			$this->log->error('['.__CLASS__.'.'.__FUNCTION__.']: '.$e->getMessage());
			return json_encode(array('success'=>false, 'error'=>$e->getMessage()));
		}
	}

	public function deleteMonitor($var) {
		try {
			Doctrine_Core::getTable('Monitors')->findBy('mnt_id', $var['mnt_id'])->delete();
			return json_encode(array('success'=>true));
		} catch (Exception $e) {
			$this->log->error('['.__CLASS__.'.'.__FUNCTION__.']: '.$e->getMessage());
			return json_encode(array('success'=>false, 'error'=>$e->getMessage()));
		}
	}

}
?>