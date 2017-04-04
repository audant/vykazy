<?php
/**
 * Výkazy práce - pracovnici
 *
 * @author Michal Basl (audant@bobb.cz)
 * @copyright 2011 Základní škola a Odborná škola Zbůc
 */
if (!defined('PROTECTED_CODE')): die('Nepovoleny pristup! / Hacking attempt!'); endif;

class LeftpanelWorkers {

	private $log;

	function __construct() {
		global $log;
		$this->log =& $log;
	}

	public function getJsonList($var) {
		$qer = Doctrine_Query::create()
		->from('Workers w')
		->addWhere('w.wrk_state = ?', 'E')
		->orWhere('w.wrk_state = ?', 'D')
		->orderBy('w.wrk_name')
		->execute();

		foreach ($qer as $wrk) {
			if ($wrk->wrk_role == 'ADMIN') $icon = 'admin';
			else $icon = 'user';
			$out[] = array(
				'worker'=>$wrk->wrk_name,
				'id'=>$wrk->wrk_id,
				'iconCls'=>$wrk->wrk_state=='E' ? $icon.'_edit' : $icon,
				'click'=>'edit',
				'leaf'=>true);
		}
		return json_encode($out);
	}

	public function saveNewWorker($var) {
		$heslo = substr(md5(rand()),0, 5);
		$nick = explode(' ', strtolower($var['wrk_name']));

		$wrk = new Workers();
		$wrk->wrk_name = $var['wrk_name'];
		$wrk->wrk_pass = md5($heslo);
		$wrk->wrk_nick = substr($nick[0], 0, 4).substr($nick[1], 0, 2);
		try {
			$wrk->save();
			return json_encode(array('success'=>true, 'worker'=>array_merge($wrk->toArray(), array('password'=>$heslo))));
		} catch (Exception $e) {
			$this->log->error('['.__CLASS__.'.'.__FUNCTION__.']: '.$e->getMessage());
			return json_encode(array('success'=>false, 'error'=>$e->getMessage()));
		}

	}
}
?>