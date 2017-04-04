<?php
/**
 * Výkazy práce - pracovnici
 *
 * @author Michal Basl (audant@bobb.cz)
 * @copyright 2011 Základní škola a Odborná škola Zbůc
 */
if (!defined('PROTECTED_CODE')): die('Nepovoleny pristup! / Hacking attempt!'); endif;

class LeftpanelTimesheets {

	private $_usr_dur = false;
	private $_prj_dur = false;

	public function getJsonList($var) {
		$qer = Doctrine_Query::create()->from('Workers w');
		switch ($var['workers']) {
			case 'E':
				$qer->addWhere('w.wrk_state = ?', 'E');
				break;
			case 'D':
				$qer->addWhere('w.wrk_state = ?', 'D');
				break;
			default:
				$qer->addWhere('w.wrk_state IN("E","D")');
				break;
		}
		if (Auth::getUserRole()!='ADMIN') {
			$qer->addWhere('w.wrk_id = ?', Auth::getUserId());
		}
		$qer->orderBy('w.wrk_name');
		$worker = $qer->execute();

		foreach ($worker as $wrk) {
			$projects = $this->getProjects($wrk->wrk_id, $var['prj_id']);
			$line = array(
						'task'=>$wrk->wrk_name,
						'display'=>$wrk->wrk_name,
						'iconCls'=>'folder_user',
						'click'=>'none');

			if ($var['wrk_id']==$wrk->wrk_id) $line = array_merge($line, array('expanded'=>true));

			if (empty($projects)) {
				$out[] = array_merge($line, array('duration'=>'&nbsp;', 'leaf'=>'true'));
			} else {
				$duration = $this->_usr_dur ? $this->convMinutesToTime($projects['duration']) : '&nbsp;';
				$out[] = array_merge($line, array('duration'=>$duration, 'children'=>$projects['children']));
			}

		}

		return json_encode($out);
	}

	private function getProjects($wrk_id, $prj_id) {
		$qer = Doctrine_Query::create()
		->select()
		->from('Projects p')
		->leftJoin('p.Assignment a')
		->addWhere('a.asg_wrk = ?', $wrk_id)
		->addWhere('p.prj_state IN("E","C")')
		->orderBy('p.prj_name')
		->execute();

		foreach ($qer as $prj) {
			$line = array(
					'task'=>$prj->prj_name,
					'display'=>'Projekt: '.$prj->prj_name,
					'iconCls'=>$prj->prj_state=='E' ? 'brick_edit' : 'brick_close',
					'click'=>'none');

			if ($prj_id==$prj->prj_id) $line = array_merge($line, array('expanded'=>true));

			$sheets = $this->getTimesheets($wrk_id, $prj->prj_id, $prj->prj_state);
			if (empty($sheets)) {
				$out[] = array_merge($line, array('leaf'=>'true','duration'=>'&nbsp;'));
			} else {
				$dur[] = $sheets['duration'];
				$duration = $this->_prj_dur ? $this->convMinutesToTime($sheets['duration']) : '&nbsp;';
				$out[] = array_merge($line, array('duration'=>$duration,'children'=>$sheets['children']));
			}
		}

		if (!empty($out)) $output = array('duration'=>array_sum($dur), 'children'=>$out);
		else $output = null;

		return $output;
	}

	private function getTimesheets($wrk_id, $prj_id, $prj_state) {
		$dur = array();
		$qer = Doctrine_Query::create()
		->select('DATE_FORMAT(t.tms_date,\'%Y/%m\') AS tms_name')
		->addSelect('p.pst_code AS tms_pozice')
		->addSelect('(SUM(DATE_FORMAT(t.tms_time,\'%H\'))*60 + SUM(DATE_FORMAT(t.tms_time,\'%i\'))) AS tms_sum')
		->addSelect('t.tms_state')
		->addSelect('a.asg_uvazek')
		->from('Timesheets t')
		->leftJoin('t.Assignment a')
		->leftJoin('a.Position p')
		->addWhere('a.asg_wrk = ?', $wrk_id)
		->addWhere('a.asg_prj = ?', $prj_id)
		->groupBy('DATE_FORMAT(t.tms_date,\'%Y/%m\')')
		->addGroupBy('p.pst_code')
		->execute();

		foreach ($qer as $tms) {
			$dur[] = $tms->tms_sum;
			$out[] = array(
					'task'=>$tms->tms_name,
					'duration'=>$this->convMinutesToTime($tms->tms_sum),
					'iconCls'=>$tms->tms_state=='D' ? 'report_close' : 'report_edit',
					'click'=>$tms->tms_state=='D' ? 'report_close' : 'report_edit',
					'worker'=>$wrk_id,
					'project'=>$prj_id,
					'display'=>$tms->tms_name.' '.$tms->tms_pozice.' ('.$tms->Assignment->asg_uvazek.')',
					'tms_asg'=>$tms->tms_asg,
					'leaf'=>true);
		}
		if ($prj_state=='E') $out[] = array(
					'task'=>null,
					'display'=>'Nový výkaz',
					'duration'=>'&nbsp;',
					'iconCls'=>'report_add',
					'click'=>'report_add',
					'worker'=>$wrk_id,
					'project'=>$prj_id,
					'tms_asg'=>null,
					'leaf'=>true);

		if (!empty($out)) $output = array('duration'=>array_sum($dur), 'children'=>$out);
		else $output = null;

		return $output;
	}

	private function convMinutesToTime($mm) {
		return sprintf("%02d:%02d", floor($mm/60), $mm%60);
	}

}
?>