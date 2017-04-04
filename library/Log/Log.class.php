<?php
/**
 * Trida zajistujici logovani aplikace
 * @author basm
 * @since 2009
 */
if (!defined('PROTECTED_CODE')): die('Nepovoleny pristup! / Hacking attempt!'); endif;

class Log {

	private $firephp = False;
	private $project;
	private $levels;

	private $text_prefix;
	private $text_postfix;

	private $msg_info = array();
	private $msg_error = array();

	private $file_log = False;
	private $file_info = False;
	private $file_warn = False;
	private $file_error = False;
	private $file_auth = False;

	/**
	 * Konstruktor tridy
	 * - zkontroluje co vse chceme logovat a vytvori instance prislusnych souboru
	 * - inicializuje rozsireni FirePHP pro vypis do logu Firefox rozsireni
	 *
	 * @param $levels
	 * @param $project
	 * @return unknown_type
	 */
	function __construct($levels, $project, $fphp = True) {
		if ($fphp) {
			require_once(DIR_LIBRARY.'Log/FirePHP.class.php');
			ob_start();
			$this->firephp = FirePHP::getInstance(True);
		}
		$this->project = $project;
		$this->levels = $levels;
		$this->text_prefix = StrFTime('%Y-%m-%d %H:%M', Time()).' ['.strtoupper($project).'] ';
		$this->text_postfix = "\r\n";
		foreach ($this->levels as $level) {
			$logpath = DIR_ROOT.'/logs/';
			$logfile = strtolower($project).'.'.$level.'.'.StrFTime('%Y-%m-%d', Time()).'.log';
			switch ($level) {
				case 'log':
					$this->file_log = fopen($logpath.$logfile, 'a+');
					break;
				case 'info':
					$this->file_info = fopen($logpath.$logfile, 'a+');
					break;
				case 'warn':
					$this->file_warn = fopen($logpath.$logfile, 'a+');
					break;
				case 'error':
					$this->file_error = fopen($logpath.$logfile, 'a+');
					break;
				case 'auth':
					$this->file_auth = fopen($logpath.$logfile, 'a+');
					break;
			}
			$this->log('Initializing log file '.$logfile);
		}
	}

	public function closeLog() {
		foreach ($this->levels as $level) {
			switch ($level) {
				case 'log':
					fclose($this->file_log);
					break;
				case 'info':
					fclose($this->file_info);
					break;
				case 'warn':
					fclose($this->file_warn);
					break;
				case 'error':
					fclose($this->file_error);
					break;
				case 'auth':
					fclose($this->file_auth);
					break;
			}
			if ($this->firephp) {
				$logfile = strtolower($this->project).'.'.$level.'.'.StrFTime('%Y-%m-%d', Time()).'.log';
				$this->firephp->log($this->text_prefix.'Log: Conclusion log file '.$logfile);
			}
		}
	}

	/**
	 * Privatni logovacio metoda obstaravajici zapis logovacich textu
	 * @param $type
	 * @param $text
	 * @param $logfile
	 * @return unknown_type
	 */
	private function writeLog($type, $text, $logfile) {
		if ($logfile) {
			fwrite($logfile, $this->text_prefix.$text.$this->text_postfix);
		}
		if ($this->firephp) {
			$this->firephp->$type($this->text_prefix.$type.': '.$text);
		}
	}

	/**
	 * Verejne  logovaci metody
	 * @param $text
	 * @param $pole = null
	 */
	public function log($text, $pole = null) {
		if(is_null($pole)) {
			$this->writeLog('Log', $text, $this->file_log);
		}
		else {
			if ($this->firephp) {
				$this->firephp->log($pole, $this->text_prefix.'Log: '.$text);
			}
		}
	}

	public function info($text) {
		$this->writeLog('Info', $text, $this->file_info);
		array_push($this->msg_info, array('text'=>$text));
	}

	public function warn($text) {
		$this->writeLog('Warn', $text, $this->file_warn);
		array_push($this->msg_error, array('text'=>$text));
	}

	public function error($text) {
		$this->writeLog('Error', $text, $this->file_error);
		array_push($this->msg_error, array('text'=>$text));
	}

	public function auth($text) {
		$this->writeLog('Info', $text, $this->file_auth);
	}

	/**
	 * Ziskani informacnich udalosti z tridy
	 * @return msg_info Array()
	 */
	public function getMsgInfo() {
		return $this->msg_info;
	}

	/**
	 * Ziskani chybovych udalosti z tridy
	 * @return msg_error Array()
	 */
	public function getMsgError() {
		return $this->msg_error;
	}

}
?>