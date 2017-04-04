<?php
/**
 * Výkazy práce - vykaz prace
 *
 * @author Michal Basl (audant@bobb.cz)
 * @copyright 2011 Základní škola a Odborná škola Zbůc
 */
if (!defined('PROTECTED_CODE')): die('Nepovoleny pristup! / Hacking attempt!'); endif;

class RightpanelTimesheets {

	private $dny = array('Po', 'Út', 'St', 'Čt', 'Pá', 'So', 'Ne');

	private $log;

	function __construct() {
		global $log;
		$this->log =& $log;
	}

	public function getJsonGrid($var) {
		$tms = $this->getTimesheet($var);
		return json_encode(array('timesheets'=>$tms['tms']));
	}

	public function getJsonFormReportAddPositions($var) {
		$qer = Doctrine_Query::create()
		->select('a.asg_id, p.pst_id, CONCAT(\'(\',p.pst_code,\') \',p.pst_name) as pst_name')
		->from('Assignment a')
		->leftJoin('a.Position p')
		->addWhere('a.asg_wrk = ?', $var['wrk_id'])
		->addWhere('a.asg_prj = ?', $var['prj_id'])
		->addWhere('a.asg_state = ?', true)
		->execute(array(), Doctrine::HYDRATE_SCALAR);
		return json_encode($qer);
	}

	public function getTimesheet($var) {
		$date = explode('/', $var['tms_date']);
		$tms = $this->getEmptyTimesheet($var);

		$qer = Doctrine_Query::create()
		->select()
		->from('Timesheets t')
		->leftJoin('t.Assignment a');
		if (array_key_exists('tms_asg', $var)&&!empty($var['tms_asg'])) {
			$qer->addWhere('a.asg_id = ?', $var['tms_asg']);
		} else {
			$qer->addWhere('a.asg_wrk = ?', $var['wrk_id'])
			->addWhere('a.asg_prj = ?', $var['prj_id'])
			->addWhere('a.asg_pozice = ?', $var['pst_id']);
		}
		$qer->addWhere('t.tms_date BETWEEN ? AND ?', array(date('Y-m-d', strtotime('1.'. $date[1].'.'.$date[0])),date('Y-m-d', strtotime(date('t', strtotime('1.'. $date[1].'.'.$date[0])).'.'.$date[1].'.'.$date[0]))))
		->orderBy('t.tms_date');
		$timesheets = $qer->execute();

		if ($timesheets->count() > 0) {
			foreach ($timesheets as $item) {
				$numDay = date('j', strtotime($item->tms_date));
				$tms[$numDay-1] = array(
					'tms_id'=>$item->tms_id,
					'tms_idat'=>date('Y/m', strtotime($item->tms_date)),
					'tms_date'=>$numDay.' '.$this->dny[date('N', strtotime($item->tms_date))-1],
					'tms_time'=>date('H:i', strtotime($item->tms_time)),
					'tms_desc'=>$item->tms_desc,
					'tms_state'=>$item->tms_state,
					'wrk_id'=>$var['wrk_id'],
					'prj_id'=>$var['prj_id'],
					'asg_id'=>$var['asg_id']);
			}
		}

		return array('tms'=>$tms);
	}

	public function saveGrid($data) {
		if (empty($data['tms_id'])) {
			$result = 'NEW';
			if (empty($data['asg_id'])) {
				$asg = Doctrine_Query::create()
				->select()
				->from('Assignment a')
				->addWhere('a.asg_wrk = ?', $data['wrk_id'])
				->addWhere('a.asg_prj = ?', $data['prj_id'])
				->addWhere('a.asg_pozice = ?', $data['pst_id'])
				->execute()
				->getFirst();
				$data['asg_id'] = $asg->asg_id;
			}
			$tms = new Timesheets();
			$tms->tms_asg = $data['asg_id'];
			$tms->tms_state = 'W';
			$tms->tms_date = date('Y-m-d', strtotime($data['tms_idat'].'/'.($data['row']+1)));
			$tms->$data['field'] = $data['value'];
		} else {
			$result = 'UPDATE';
			$tms = Doctrine_Core::getTable('Timesheets')->find($data['tms_id']);
			$tms->$data['field'] = $data['value'];
		}
		try {
			$tms->save();
			return json_encode(array('result'=>$result, 'row'=>$tms->toArray(false)));
		} catch (Exception $e) {
			$this->log->error('['.__CLASS__.'.'.__FUNCTION__.']: '.$e->getMessage());
			return json_encode(array('result'=>'KO', 'row'=>$e->getMessage()));
		}
	}

	public function deleteGrid($data) {
		$tms = Doctrine_Core::getTable('Timesheets')->find($data['tms_id']);
		try {
			$tms->delete();
			return json_encode(array('success'=>true));
		} catch (Exception $e) {
			$this->log->error('['.__CLASS__.'.'.__FUNCTION__.']: '.$e->getMessage());
			return json_encode(array('success'=>false, 'error'=>$e->getMessage()));
		}
	}

	public function lockGrid($data) {
		$date = explode('/', $data['tms_date']);
		if (empty($data['asg_id'])) {
			$asg = Doctrine_Query::create()
			->select()
			->from('Assignment a')
			->addWhere('a.asg_wrk = ?', $data['wrk_id'])
			->addWhere('a.asg_prj = ?', $data['prj_id'])
			->addWhere('a.asg_pozice = ?', $data['pst_id'])
			->execute()
			->getFirst();
			$data['asg_id'] = $asg->asg_id;
		}
		$qer = Doctrine_Query::create()
		->update('Timesheets t')
		->set('t.tms_state', '?', 'D')
		->where('t.tms_asg = ?', $data['asg_id'])
		->addWhere('t.tms_date BETWEEN ? AND ?', array(date('Y-m-d', strtotime('1.'. $date[1].'.'.$date[0])),date('Y-m-d', strtotime(date('t', strtotime('1.'. $date[1].'.'.$date[0])).'.'.$date[1].'.'.$date[0]))));
		try {
			$out = array('result'=>'LOCKED', 'SQL'=>$qer->getSqlQuery(), 'Params'=>$qer->getParams());
			$qer->execute();
			return json_encode($out);
		} catch (Exception $e) {
			$this->log->error('['.__CLASS__.'.'.__FUNCTION__.']: '.$e->getMessage());
			return json_encode(array('result'=>'KO', 'row'=>$e->getMessage()));
		}
	}

	public function unlockGrid($data) {
		$date = explode('/', $data['tms_date']);
		if (empty($data['asg_id'])) {
			$asg = Doctrine_Query::create()
			->select()
			->from('Assignment a')
			->addWhere('a.asg_wrk = ?', $data['wrk_id'])
			->addWhere('a.asg_prj = ?', $data['prj_id'])
			->addWhere('a.asg_pozice = ?', $data['pst_id'])
			->execute()
			->getFirst();
			$data['asg_id'] = $asg->asg_id;
		}
		$qer = Doctrine_Query::create()
		->update('Timesheets t')
		->set('t.tms_state', '?', 'E')
		->where('t.tms_asg = ?', $data['asg_id'])
		->addWhere('t.tms_date BETWEEN ? AND ?', array(date('Y-m-d', strtotime('1.'. $date[1].'.'.$date[0])),date('Y-m-d', strtotime(date('t', strtotime('1.'. $date[1].'.'.$date[0])).'.'.$date[1].'.'.$date[0]))));
		try {
			$out = array('result'=>'UNLOCKED', 'SQL'=>$qer->getSqlQuery(), 'Params'=>$qer->getParams());
			$qer->execute();
			return json_encode($out);
		} catch (Exception $e) {
			$this->log->error('['.__CLASS__.'.'.__FUNCTION__.']: '.$e->getMessage());
			return json_encode(array('result'=>'KO', 'row'=>$e->getMessage()));
		}
	}

	/**
	 * Funkce vrací prázdné pole výkazu
	 * @param unknown_type $date YYYY/MM
	 * @return array
	 */
	public function getEmptyTimesheet($var) {
		$date = explode('/', $var['tms_date']);
		$y = $date[0];
		$m = $date[1];
		$t = date('t', strtotime('1.'. $m.'.'.$y));

		for ($i=1; $i<=$t; $i++) {
			$out[] = array(
			'tms_id'=>null,
			'tms_idat'=>date('Y/m', strtotime($i.'.'. $m.'.'.$y)),
			'tms_date'=>$i.' '.$this->dny[date('N', strtotime($i.'.'. $m.'.'.$y))-1],
			'tms_time'=>null,
			'tms_desc'=>null,
			'tms_state'=>'C',
			'wrk_id'=>$var['wrk_id'],
			'prj_id'=>$var['prj_id']);
		}

		return $out;
	}

	private function convTimeToMinutes($time) {
		$hour = date('H', strtotime($time));
		$min = date('i', strtotime($time));
		return ($hour * 60) + $min;
	}

}

?>