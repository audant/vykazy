<?php
/**
 * Trida hlidajici prihlaseneho uzivatele a jeho opravneni
 * @author basm
 * @since 2009
 */
if (! defined ( 'PROTECTED_CODE' )) :
	die ( 'Nepovoleny pristup! / Hacking attempt!' );

 endif;
class Auth {
	function __construct() {
		session_cache_expire ( 14400 );
		session_start ();
		
		if (isset ( $_POST ['auth'] ) && $_POST ['auth'] == 'login') {
			$this->logIn ( $_POST ['username'], $_POST ['password'] );
		}
		if (isset ( $_GET ['auth'] ) && $_GET ['auth'] == 'logout') {
			$this->logOut ();
			header ( 'Location:index.php' );
		}
		if (! $_SESSION ['Authorization']) {
			$this->getLoginForm ();
		}
	}
	private function getLoginForm() {
		$error = array (
				'Chyba systému!',
				'Uživatel nenalezen!',
				'Vyplňte údaje!' 
		);
		$pet = new Pet ();
		$pet->title = htmlspecialchars ( TMPL_TITLE );
		$pet->header_title = htmlspecialchars ( TMPL_HEADER );
		$pet->logerror = htmlspecialchars ( $error [$_GET ['e']] );
		$pet->setTemplate ( DIR_LIBRARY . 'Auth/login.pet.tpl' );
		echo $pet->fetch ();
		exit ();
	}
	private function logIn($username, $password) {
		if (! empty ( $username ) && ! empty ( $password )) {
			$user = Doctrine_Query::create ()->from ( 'Workers w' )->addWhere ( 'w.wrk_state = ?', 'E' )->addWhere ( 'w.wrk_nick = ?', $username )->addWhere ( 'w.wrk_pass = ?', md5 ( $password ) )->execute ();
			if ($user->count () == 1) {
				$_SESSION ['Authorization'] ['userId'] = $user [0]->wrk_id;
				$_SESSION ['Authorization'] ['userNick'] = $user [0]->wrk_nick;
				$_SESSION ['Authorization'] ['userName'] = $user [0]->wrk_name;
				$_SESSION ['Authorization'] ['userRole'] = $user [0]->wrk_role;
				header ( 'Location:index.php' );
			} else {
				header ( 'Location:index.php?e=1' );
			}
		} else {
			header ( 'Location:index.php?e=2' );
		}
	}
	private function logOut() {
		if (! session_unregister ( 'Authorization' )) {
		} else {
			session_destroy ();
		}
	}
	public static function getUserName() {
		return $_SESSION ['Authorization'] ['userName'];
	}
	public static function getUserId() {
		return $_SESSION ['Authorization'] ['userId'];
	}
	public static function getUserRole() {
		return $_SESSION ['Authorization'] ['userRole'];
	}
}

?>