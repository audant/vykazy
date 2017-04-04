<?php
/**
 * Výkazy práce - projekty
 *
 * @author Michal Basl (audant@bobb.cz)
 * @copyright 2011 Základní škola a Odborná škola Zbůc
 */
if (!defined('PROTECTED_CODE')): die('Nepovoleny pristup! / Hacking attempt!'); endif;

class LeftpanelProjects {

	public function getJsonList($var) {
		$qer = Doctrine_Query::create()
		->from('Projects p')
		->addWhere('p.prj_state IN("E","C","D")')
		->orderBy('p.prj_name')
		->execute();

		foreach ($qer as $prj) {
			switch ($prj->prj_state) {
				case 'C':
					$icon = 'brick_close';
					break;
				case 'D':
					$icon = 'brick_delete';
					break;
				default:
					$icon = 'brick_edit';
					break;
			}
			$out[] = array(
				'project'=>$prj->prj_name,
				'id'=>$prj->prj_id,
				'iconCls'=>$icon,
				'click'=>'edit',
				'leaf'=>true);
		}
		return json_encode($out);
	}

}

?>