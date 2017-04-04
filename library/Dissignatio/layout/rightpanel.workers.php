<?php
/**
 * Výkazy práce - pracovníci
 *
 * @author Michal Basl (audant@bobb.cz)
 * @copyright 2011 Základní škola a Odborná škola Zbůc
 */
if (!defined('PROTECTED_CODE')): die('Nepovoleny pristup! / Hacking attempt!'); endif;

class RightpanelWorkers {

	private $log;

	function __construct() {
		global $log;
		$this->log =& $log;
	}

	public function getJsonWorker($var) {
		$worker = Doctrine_Core::getTable('Workers')->findByWrk_id($var['wrk_id'])->toArray();
		$worker[0]['wrk_pass'] = '********';
		return json_encode(array('worker'=>$worker));
	}

	public function getJsonGrid($var) {
		$qer = Doctrine_Query::create()
		->select('a.asg_id, a.asg_prj, asg_state, a.asg_pozice, a.asg_pracpom, a.asg_uvazek, a.asg_dalsiuvaz, a.asg_dalsicin, r.prj_name, p.pst_code, CONCAT(\'(\',p.pst_code,\') \',p.pst_name) as pst_name')
		->from('Assignment a')
		->leftJoin('a.Projects r')
		->leftJoin('a.Position p')
		->where('a.asg_wrk = ?', $var['wrk_id'])
		->execute(array(), Doctrine::HYDRATE_SCALAR);
		return json_encode(array('assignment'=>$qer));
	}

	public function getJsonWorkersNewAsgPrj() {
		$qer = Doctrine_Query::create()
		->select('r.prj_id, r.prj_name')
		->from('Projects r')
		->where('r.prj_state = ?', 'E')
		->execute(array(), Doctrine::HYDRATE_SCALAR);
		return json_encode($qer);
	}

	public function getJsonWorkersNewAsgPst() {
		$qer = Doctrine_Query::create()
		->select('p.pst_id, p.pst_code, CONCAT(\'(\',p.pst_code,\') \',p.pst_name) as pst_name')
		->from('Position p')
		->execute(array(), Doctrine::HYDRATE_SCALAR);
		return json_encode($qer);
	}

	public function saveNewAssignment($var) {
		$qer = Doctrine_Core::getTable('Assignment')
		->createQuery()
		->addWhere('asg_wrk = ?', $var['wrk_id'])
		->addWhere('asg_prj = ?', $var['r_prj_id'])
		->addWhere('asg_pozice = ?', $var['p_pst_id'])
		->execute();

		$asg = new Assignment();
		$asg->asg_wrk = $var['wrk_id'];
		$asg->asg_prj = $var['r_prj_id'];
		$asg->asg_pozice = $var['p_pst_id'];

		try {
			if ($qer->count()==0) {
				$asg->save();
			}
			else throw new Exception('Pracovník je na tento projekt již přiřazen na stejné pozici.');
			return json_encode(array('success'=>true, 'asignment'=>$asg->toArray()));
		} catch (Exception $e) {
			$this->log->error('['.__CLASS__.'.'.__FUNCTION__.']: '.$e->getMessage());
			return json_encode(array('success'=>false, 'error'=>$e->getMessage()));
		}
	}

	public function saveAssignment($var) {
		try {
			switch ($var['value']) {
				case 'true':
					$var['value'] = 1;
					break;
				case 'false':
					$var['value'] = 0;
					break;
			}
			$asg = Doctrine_Core::getTable('Assignment')->find($var['asg_id']);
			$asg->$var['field'] = $var['value'];
			$asg->save();
			return json_encode(array('success'=>true));
		} catch (Exception $e) {
			$this->log->error('['.__CLASS__.'.'.__FUNCTION__.']: '.$e->getMessage());
			return json_encode(array('success'=>false, 'error'=>$e->getMessage()));
		}
	}

	public function saveWorker($var) {
		if (!empty($var['wrk_id'])) {
			$worker = Doctrine_Core::getTable('Workers')->find($var['wrk_id']);
		}
		else $worker = new Workers();
		$worker->wrk_state = $var['wrk_state'];
		$worker->wrk_role = $var['wrk_role'];
		$worker->wrk_name = $var['wrk_name'];
		$worker->wrk_nick = $var['wrk_nick'];
		if (!empty($var['wrk_pass'])) $worker->wrk_pass = md5($var['wrk_pass']);
		try {
			$worker->save();
			return json_encode(array('success'=>true, 'worker'=>$worker->toArray()));
		} catch (Exception $e) {
			$this->log->error('['.__CLASS__.'.'.__FUNCTION__.']: '.$e->getMessage());
			return json_encode(array('success'=>false, 'error'=>$e->getMessage()));
		}
	}

	public function deleteAssignment($var) {
		$reported = Doctrine_Core::getTable('Timesheets')
		->createQuery()
		->addWhere('tms_asg = ?', $var['asg_id'])
		->execute()
		->count();
		$asg = Doctrine_Core::getTable('Assignment')->findBy('asg_id', $var['asg_id']);
		try {
			if ($reported > 0) throw new Exception('Na tento projekt a pozici již bylo vykázáno!');
			else $asg->delete();
			return json_encode(array('success'=>true));
		} catch (Exception $e) {
			$this->log->error('['.__CLASS__.'.'.__FUNCTION__.']: '.$e->getMessage());
			return json_encode(array('success'=>false, 'error'=>$e->getMessage()));
		}
	}

	public function deleteWorker($var) {
		try {
			$asgmnt = Doctrine_core::getTable('Assignment')
			->createQuery()
			->addWhere('asg_wrk = ?', $var['wrk_id'])
			->execute()
			->count();
			$wrk = Doctrine_Core::getTable('Workers')->findBy('wrk_id', $var['wrk_id']);
			if ($asgmnt > 0) throw new Exception('Tento pracovník je přiřazený na projekty!');
			else $wrk->delete();
			return json_encode(array('success'=>true));
		} catch (Exception $e) {
			$this->log->error('['.__CLASS__.'.'.__FUNCTION__.']: '.$e->getMessage());
			return json_encode(array('success'=>false, 'error'=>$e->getMessage()));
		}
	}

}
?>