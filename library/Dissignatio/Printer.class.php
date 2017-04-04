<?php
/**
 * Vykazy prace - trida pro tiskove sestavy
 *
 * @author Michal Basl (audant@bobb.cz)
 * @copyright 2011 Základní škola a Odborná škola Zbůc
 */
if (!defined('PROTECTED_CODE')): die('Nepovoleny pristup! / Hacking attempt!'); endif;

class Printer {

	private $_pdf;
	private $_document;
	private $_undiak = Array('ä'=>'a', 'Ä'=>'A', 'á'=>'a', 'Á'=>'A', 'à'=>'a', 'À'=>'A', 'ã'=>'a', 'Ã'=>'A', 'â'=>'a', 'Â'=>'A', 'č'=>'c', 'Č'=>'C', 'ć'=>'c', 'Ć'=>'C', 'ď'=>'d', 'Ď'=>'D', 'ě'=>'e', 'Ě'=>'E', 'é'=>'e', 'É'=>'E', 'ë'=>'e', 'Ë'=>'E', 'è'=>'e', 'È'=>'E', 'ê'=>'e', 'Ê'=>'E', 'í'=>'i', 'Í'=>'I', 'ï'=>'i', 'Ï'=>'I', 'ì'=>'i', 'Ì'=>'I', 'î'=>'i', 'Î'=>'I', 'ľ'=>'l', 'Ľ'=>'L', 'ĺ'=>'l', 'Ĺ'=>'L', 'ń'=>'n', 'Ń'=>'N', 'ň'=>'n', 'Ň'=>'N', 'ñ'=>'n', 'Ñ'=>'N', 'ó'=>'o', 'Ó'=>'O', 'ö'=>'o', 'Ö'=>'O', 'ô'=>'o', 'Ô'=>'O', 'ò'=>'o', 'Ò'=>'O', 'õ'=>'o', 'Õ'=>'O', 'ő'=>'o', 'Ő'=>'O', 'ř'=>'r', 'Ř'=>'R', 'ŕ'=>'r', 'Ŕ'=>'R', 'š'=>'s', 'Š'=>'S', 'ś'=>'s', 'Ś'=>'S', 'ť'=>'t', 'Ť'=>'T', 'ú'=>'u', 'Ú'=>'U', 'ů'=>'u', 'Ů'=>'U', 'ü'=>'u', 'Ü'=>'U', 'ù'=>'u', 'Ù'=>'U', 'ũ'=>'u', 'Ũ'=>'U', 'û'=>'u', 'Û'=>'U', 'ý'=>'y', 'Ý'=>'Y', 'ž'=>'z', 'Ž'=>'Z', 'ź'=>'z', 'Ź'=>'Z');

	function __construct() {
		if (!require_once(DIR_LIBRARY.'FPDF/fpdf.php')) {
			throw new Exception('PDF support can not start!');
		}
		$this->_document = 'print';
		$this->_pdf=new FPDF('P','mm','A4');
		$this->_pdf->SetAuthor(TMPL_HEADER, true);
		$this->_pdf->SetLeftMargin(20);
		$this->_pdf->SetRightMargin(20);
		$this->_pdf->SetAutoPageBreak(true, 10);
		$this->_pdf->SetDisplayMode('fullwidth');
		$this->_pdf->AliasNbPages();
		$this->_pdf->AddPage();
	}

	public function setPrintOut($arg) {
		if (!require_once(DIR_LIBRARY.'Dissignatio/printer/printout.'.$arg[0].'.php')) {
			throw new Exception('Print-out can not setup!');
		}
		$printClass = 'PrintOut'.ucfirst(strtolower($arg[0]));
		unset($arg[0]);
		$printout = new $printClass($this->_pdf, $arg);
		$this->_document = $printout->getDocumentName();
	}

	public function getPdf() {
		$this->_pdf->Output(strtr($this->_document, $this->_undiak).'.pdf', 'D');
	}

}
?>