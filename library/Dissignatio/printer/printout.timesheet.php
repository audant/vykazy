<?php
/**
 * Výkazy práce - tisková sestava výkazu práce
 *
 * @author Michal Basl (audant@bobb.cz)
 * @copyright 2011 Základní škola a Odborná škola Zbůc
 */
if (!defined('PROTECTED_CODE')): die('Nepovoleny pristup! / Hacking attempt!'); endif;

class PrintOutTimesheet {

	private $_document;

	private $_pdf;
	private $_arg;

	private $font;
	private $fvar;
	private $fs_p;
	private $fs_h1;

	private $cellh_p;
	private $cellh_h1;

	private $_dur = array();

	function __construct($pdf, $arg) {
		$this->_pdf =& $pdf;
		foreach ($arg as $var) {
			$item = explode(':', $var);
			$this->_arg[$item[0]] = $item[1];
		}
		$this->setPrintOut();
		$this->printHeader();
		$this->printBody();
		$this->printFooter();
	}

	public function getDocumentName() {
		return $this->_document;
	}

	private function setPrintOut() {
		$this->font = 'FreeSans';
		$this->fvar = '';
		$this->fs_p = 7;
		$this->fs_h1 = 10;
		$this->cellh_p = 3.5;
		$this->cellh_h1 = 6;
		$this->_pdf->SetFillColor(175, 175, 175);
	}

	private function printHeader() {
		$qer = Doctrine_Query::create()->select()->from('Workers w')->leftJoin('w.Assignment a')->addWhere('w.wrk_id = ?', $this->_arg['wrk_id']);
		if (empty($this->_arg['tms_asg'])) {
			$qer->addWhere('a.asg_pozice = ?', $this->_arg['pst_id'])
			->addWhere('a.asg_prj = ?', $this->_arg['prj_id']);
		} else {
			$qer->addWhere('a.asg_id = ?', $this->_arg['tms_asg']);
		}
		$worker = $qer->execute()->getFirst();
		$assign = $worker->Assignment->getFirst();
		$position = Doctrine_Core::getTable('Position')->findOneBy('pst_id', $assign->asg_pozice);
		$project = Doctrine_Core::getTable('Projects')->findOneBy('prj_id', $this->_arg['prj_id']);
		$txt_mrok = explode('/', $this->_arg['tms_date']);

		$date = explode('/', $this->_arg['tms_date']);
		$qer = Doctrine_Query::create()
		->select()
		->from('Monitors m')
		->addWhere('m.mnt_prj = ?', $this->_arg['prj_id'])
		->addWhere('? BETWEEN m.mnt_esty AND m.mnt_lety', $date[0])
		->addWhere('? BETWEEN m.mnt_estm AND m.mnt_letm', $date[1]);
		$mnt = $qer->execute()->getFirst();

		$date = explode('/', $this->_arg['tms_date']);

		$qer = Doctrine_Query::create()
		->select()
		->from('Assignment a')
		->leftJoin('a.Position p')
		->leftJoin('a.Timesheets t')
		->addWhere('a.asg_state = ?', 1)
		->addWhere('a.asg_wrk = ?', $this->_arg['wrk_id'])
		->addWhere('a.asg_id != ?', $assign->asg_id)
		->addWhere('t.tms_date BETWEEN ? AND ?', array(date('Y-m-d', strtotime('1.'. $date[1].'.'.$date[0])),date('Y-m-d', strtotime(date('t', strtotime('1.'. $date[1].'.'.$date[0])).'.'.$date[1].'.'.$date[0]))))
		->groupBy('t.tms_asg');
		//$oth = $qer->execute();
		
		$otherHours = 0;
		$otherMsg = array();
		foreach ($qer->execute() as $oth) {
			$otherHours += $oth->asg_uvazek;
			$otherMsg[] = $oth->Position->pst_name;
		}

		$this->_document = strtoupper(trim(str_replace(array('.', '/'), '', $project->prj_regc))).'_PracovniVykaz_'.str_replace('/', '', $this->_arg['tms_date']).'_'.trim(str_replace(' ', '', $worker->wrk_name));
		$this->_pdf->document_name = $this->_document;

		$border = 1;
		$col1 = 55;
		$col2 = 55;

		$this->_pdf->Image(DIR_ROOT.'/style/images/projekt_eu_cb.jpg', 39, 5, 134);
		$this->_pdf->SetFont($this->font,'',$this->fs_p);
		$this->_pdf->SetY(5);
		$this->_pdf->Cell(0,$this->cellh_p,'Příloha č. 12 Monitorovací zprávy OP VK',0,1,'R');
		$this->_pdf->Ln(20);


		$this->_pdf->SetFont($this->font,'B',$this->fs_h1);
		$this->_pdf->Cell(0,$this->cellh_h1,'PRACOVNÍ VÝKAZ',0,1,'C');

		$this->_pdf->SetFont($this->font,'',$this->fs_p);
		$this->_pdf->Cell($col1,$this->cellh_p,'Registrační číslo projektu:',$border,0,'L',TRUE);
		$this->_pdf->Cell(0,$this->cellh_p,$project->prj_regc,$border,1);

		$x = $this->_pdf->GetX();
		$y = $this->_pdf->GetY();
		$this->_pdf->SetX($x+$col1);
		$this->_pdf->MultiCell(0,$this->cellh_p,$project->prj_nazev,$border,1);
		$h = $this->_pdf->GetY(); $this->_pdf->SetXY($x, $y);
		$this->_pdf->Cell($col1,($h-$y),'Název projektu:',$border,1,'L',TRUE);

		$this->_pdf->Cell($col1,$this->cellh_p,'Název příjemce podpory:',$border,0,'L',TRUE);
		$this->_pdf->Cell(0,$this->cellh_p,$project->prj_podpora,$border,1);

		$this->_pdf->Cell($col1,$this->cellh_p,'Pořadové číslo Monitorovací zprávy:',$border,0,'L',TRUE);
		$this->_pdf->Cell(0,$this->cellh_p,$mnt->mnt_name,$border,1);

		$this->_pdf->Ln($this->cellh_p / 2);

		$this->_pdf->Cell($col1,$this->cellh_p,'Zaměstnanec:',$border,0,'L',TRUE);
		$this->_pdf->Cell(0,$this->cellh_p,$worker->wrk_name,$border,1);

		$this->_pdf->Cell($col1,$this->cellh_p,'Pracovní pozice:',$border,0,'L',TRUE);
		$this->_pdf->Cell(0,$this->cellh_p,$position->pst_name,$border,1);

		$this->_pdf->Cell($col1,$this->cellh_p,'Vykazovaný měsíc a rok:',$border,0,'L',TRUE);
		$this->_pdf->Cell(0,$this->cellh_p,$txt_mrok[1].'/'.$txt_mrok[0],$border,1);

		$this->_pdf->Ln($this->cellh_p / 2);

		$this->_pdf->Cell($col2,$this->cellh_p,'Druh pracovního poměru:',$border,0,'L',TRUE);
		$this->_pdf->Cell(0,$this->cellh_p,$assign->asg_pracpom,$border,1);

		$this->_pdf->Cell($col2,$this->cellh_p,'Výše měsíčního úvazku pro projekt v hodinách:',$border,0,'L',TRUE);
		$this->_pdf->Cell(0,$this->cellh_p,$assign->asg_uvazek.' hodin',$border,1);

		$this->_pdf->Cell($col2,$this->cellh_p,'Další úvazek v projektech příjemce/partnera:',$border,0,'L',TRUE);
		$this->_pdf->Cell(0,$this->cellh_p,$otherHours.' hodin  '.implode(', ', $otherMsg),$border,1);

		$this->_pdf->Cell($col2,$this->cellh_p,'Úvazek v další činnosti pro příjemce/partnera:',$border,0,'L',TRUE);
		$this->_pdf->Cell(0,$this->cellh_p,$assign->asg_dalsiuvaz.' hodin',$border,1);

	}

	private function printBody() {

		if (!require_once(DIR_LIBRARY.'Dissignatio/layout/rightpanel.timesheets.php')) {
			throw new Exception('Project layout can not start!');
		}
		$rpt = new RightpanelTimesheets();
		$timesheet = $rpt->getTimesheet($this->_arg);

		$border = 1;
		$col1 = 11;
		$col2 = 10;
		$col3 = 63;
		$border = 1;
		$this->_pdf->SetFont($this->font,'',$this->fs_p);
		$this->_pdf->Ln($this->cellh_p / 2);

		$this->_pdf->Cell(0,$this->cellh_p,'Přehled odpracovaných hodin',1,1,'L',true);

		$x = $this->_pdf->GetX();
		$y = $this->_pdf->GetY();

		$this->_pdf->SetFont($this->font,'',$this->fs_p - 1);
		for($i=0; $i<=1; $i++) {
			$left = $i * 86;
			$this->_pdf->SetXY($x+$left, $y);
			$this->_pdf->MultiCell($col1,+2,'Den v měsíci  ',1,'L',true);
			$this->_pdf->SetXY($x+$left+$col1, $y);
			$this->_pdf->MultiCell($col2,2,'Počet odprac. hodin',1,'L',true);
			$this->_pdf->SetXY($x+$left+$col1+$col2, $y);
			$this->_pdf->Cell($col3,6,'Popis vykonaných aktivit',1,1,'L',true);
			//$this->_pdf->SetFont($this->font,'',$this->fs_p);
		}
		$this->_pdf->SetX($x);

		$i = 1;
		$left = 0;
		$oy = $this->_pdf->GetY();
		foreach ($timesheet['tms'] as $row) {
			if($i==16) {
				$left = 86;
				$leftY = $this->_pdf->GetY();
				$this->_pdf->SetY($oy);
			}
			$x = $this->_pdf->GetX()+$left;
			$y = $this->_pdf->GetY();
			$this->_dur[] = $this->convTimeToMinutes($row['tms_time']);
			$this->_pdf->SetX($x+$col1+$col2);
			if(is_null($row['tms_desc']))
			$this->_pdf->MultiCell($col3,($this->cellh_p - 1.5),null,$border,1);
			else
			$this->_pdf->MultiCell($col3,($this->cellh_p - 1),$row['tms_desc'],$border,1);
			$h = $this->_pdf->GetY();
			$this->_pdf->SetXY($x, $y);
			$date = explode(' ', trim($row['tms_date']));
			$this->_pdf->Cell($col1,($h-$y),$date[0].'.',$border,0,'C');
			$this->_pdf->Cell($col2,($h-$y),$row['tms_time'],$border,1,'C');
			$i++;
		}
		$rightY = $this->_pdf->GetY();

		if ($leftY > $rightY) $this->_pdf->SetY($leftY);

		$this->_pdf->SetFont($this->font,'B',$this->fs_p);
		$this->_pdf->Ln($this->cellh_p / 2);
		$this->_pdf->Cell(1+$col2+$left,$this->cellh_p,'Celkem:','LTB',0,'L',true);
		$this->_pdf->Cell(0,$this->cellh_p,$this->convMinutesToTime(array_sum($this->_dur)).' hodin','TRB',1,'L',true);
	}

	private function printFooter() {

		$border = 1;
		$col1 = 40;
		$col2 = 44;
		$col3 = 2;

		$this->_pdf->Ln($this->cellh_p / 2);

		$this->_pdf->SetFont($this->font,'',$this->fs_p - 1);
		$this->_pdf->Cell($col1+$col2,$this->cellh_p,'Dovolená',$border,0,'L',true);
		$this->_pdf->Cell($col3,$this->cellh_p,'',0,0);
		$this->_pdf->Cell($col1+$col2,$this->cellh_p,'Placená prac.nesch. hrazená zam-telem (např.náhrada mzdy za 4-14 den nemoci)',$border,1,'L',true);

		$this->_pdf->SetFont($this->font,'',$this->fs_p);
		$this->_pdf->Cell($col1,$this->cellh_p,'Termíny dovolené:',$border,0,'L',TRUE);
		$this->_pdf->Cell($col2,$this->cellh_p,'',$border,0);
		$this->_pdf->Cell($col3,$this->cellh_p,'',0,0);
		$this->_pdf->Cell($col1,$this->cellh_p,'Termíny neschopnosti',$border,0,'L',TRUE);
		$this->_pdf->Cell($col2,$this->cellh_p,'',$border,1);

		$this->_pdf->Cell($col1,$this->cellh_p,'Počet dní celkem:',$border,0,'L',TRUE);
		$this->_pdf->Cell($col2,$this->cellh_p,'',$border,0);
		$this->_pdf->Cell($col3,$this->cellh_p,'',0,0);
		$this->_pdf->Cell($col1,$this->cellh_p,'Počet dní celkem:',$border,0,'L',TRUE);
		$this->_pdf->Cell($col2,$this->cellh_p,'',$border,1);

		$this->_pdf->Cell($col1,$this->cellh_p,'Počet hodin dovolené:',$border,0,'L',TRUE);
		$this->_pdf->Cell($col2,$this->cellh_p,'',$border,0);
		$this->_pdf->Cell($col3,$this->cellh_p,'',0,0);
		$this->_pdf->Cell($col1,$this->cellh_p,'Počet hodin neschopnosti:',$border,0,'L',TRUE);
		$this->_pdf->Cell($col2,$this->cellh_p,'',$border,1);

		$this->_pdf->Ln($this->cellh_p / 2);
		$this->_pdf->SetFont($this->font,'B',$this->fs_p);

		$this->_pdf->Cell(10+$col1+$col2+$col3,$this->cellh_p,'Součet hodin souvisejících s projektem:','LTB',0,'L',true);
		$this->_pdf->Cell(0,$this->cellh_p,$this->convMinutesToTime(array_sum($this->_dur)).' hodin','RTB',1,'L',true);

		$this->_pdf->Ln($this->cellh_p / 2);
		$this->_pdf->SetFont($this->font,'',$this->fs_p);

		$this->_pdf->Cell($col1,$this->cellh_p,'Datum:',$border,0,'L',TRUE);
		$this->_pdf->Cell($col2,$this->cellh_p,'',$border,0);
		$this->_pdf->Cell($col3,$this->cellh_p,'',0,0);
		$this->_pdf->Cell($col1,$this->cellh_p,'Datum:',$border,0,'L',TRUE);
		$this->_pdf->Cell($col2,$this->cellh_p,'',$border,1);

		$this->_pdf->Ln($this->cellh_p / 2);
		$this->_pdf->Cell($col1,$this->cellh_p,'Podpis pracovníka:',$border,0,'L',TRUE);
		$this->_pdf->Cell($col2,$this->cellh_p,'',$border,0);
		$this->_pdf->Cell($col3,$this->cellh_p,'',0,0);
		$this->_pdf->Cell($col1,$this->cellh_p,'Podpis nadřízeného pracovníka:',$border,0,'L',TRUE);
		$this->_pdf->Cell($col2,$this->cellh_p,'',$border,1);

		$this->_pdf->Ln($this->cellh_p / 2);
		$this->_pdf->SetFont($this->font,'IB',$this->fs_p - 2);
		$this->_pdf->MultiCell(0,2.5,'Pozn.: Popis vykonaných aktivit/činností musí být podrobný a přiřazen k jednotlivým dnům, kdy byla činnost skutečně vykonávána (aktivity typu administrativní činnost nebudou uznány), řádky je možné rozšířit',0,1);
	}

	private function convTimeToMinutes($time) {
		$tm = explode(':', $time);
		return ($tm[0] * 60) + $tm[1];
	}

	private function convMinutesToTime($mm) {
		return sprintf("%02d:%02d", floor($mm/60), $mm%60);
	}

}
?>