<?php

/***************************************************************
 * Copyright notice
 *
 * (c) 2010 AOE media GmbH <dev@aoemedia.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class that sends the TYPO3 feuser cookie - only if a login was sucessful
 *
 * @author Tolleiv Nietsch
 * @author Thomas Schuster
 * @author Daniel Pötzinger
 */
class user_Tx_Cacheinfo_Hooks_Userauth {
	
	/**
	 * @param $content
	 * @param t3lib_userAuth $parent
	 * @return void
	 */
	public function writeLoginSessionCookie($content, t3lib_userAuth $parent) {
		$userIsLoggedIn = is_array ( $parent->user );
		$validLoginTypes = array ('fe', 'login' );
		t3lib_div::devLog ( 'loginType: ' . $parent->loginType, 'varnish', 0 );
		
		if (in_array ( strtolower ( $parent->loginType ), $validLoginTypes ) && $userIsLoggedIn) {
			t3lib_div::devLog ( 'call setSessionCookie()', 'varnish', 0 );
			$this->setSessionCookie ( $parent );
		}
	}
	
	/**
	 * Sets the session cookie for the current disposal.
	 * 
	 * @throws t3lib_exception
	 * @param t3lib_userAuth $userAuth
	 * @return void
	 * @see t3lib_userAuth (protected method)
	 */
	protected function setSessionCookie(t3lib_userAuth $userAuth) {
		$isSetSessionCookie = $userAuth->isSetSessionCookie ();
		$isRefreshTimeBasedCookie = $userAuth->isRefreshTimeBasedCookie ();
		
		if ($isSetSessionCookie || $isRefreshTimeBasedCookie) {
			$settings = $GLOBALS ['TYPO3_CONF_VARS'] ['SYS'];
			
			// Get the domain to be used for the cookie (if any):
			$cookieDomain = $this->getCookieDomain ();
			// If no cookie domain is set, use the base path:
			$cookiePath = ($cookieDomain ? '/' : t3lib_div::getIndpEnv ( 'TYPO3_SITE_PATH' ));
			// If the cookie lifetime is set, use it:
			$cookieExpire = ($isRefreshTimeBasedCookie ? $GLOBALS ['EXEC_TIME'] + $userAuth->lifetime : 0);
			// Use the secure option when the current request is served by a secure connection:
			$cookieSecure = ( bool ) $settings ['cookieSecure'] && t3lib_div::getIndpEnv ( 'TYPO3_SSL' );
			// Deliver cookies only via HTTP and prevent possible XSS by JavaScript:
			$cookieHttpOnly = ( bool ) $settings ['cookieHttpOnly'];
			
			// Do not set cookie if cookieSecure is set to "1" (force HTTPS) and no secure channel is used:
			if (( int ) $settings ['cookieSecure'] !== 1 || t3lib_div::getIndpEnv ( 'TYPO3_SSL' )) {
				setcookie ( $userAuth->name, $userAuth->id, $cookieExpire, $cookiePath, $cookieDomain, $cookieSecure, $cookieHttpOnly );
			} else {
				throw new t3lib_exception ( 'Cookie was not set since HTTPS was forced in $TYPO3_CONF_VARS[SYS][cookieSecure].', 1254325546 );
			}
			
			$devLogMessage = ($isRefreshTimeBasedCookie ? 'Hook Updated Cookie: ' : 'Hook Set Cookie: ') . $userAuth->id;
			t3lib_div::devLog ( $devLogMessage . ($cookieDomain ? ', ' . $cookieDomain : ''), 't3lib_userAuth' );
		}
	}
	
	/**
	 * Gets the domain to be used on setting cookies.
	 * The information is taken from the value in $TYPO3_CONF_VARS[SYS][cookieDomain].
	 *
	 * @return	string		The domain to be used on setting cookies
	 * @see t3lib_userAuth (protected method)
	 */
	protected function getCookieDomain() {
		$result = '';
		$cookieDomain = $GLOBALS ['TYPO3_CONF_VARS'] ['SYS'] ['cookieDomain'];
		// If a specific cookie domain is defined for a given TYPO3_MODE,
		// use that domain
		if (! empty ( $GLOBALS ['TYPO3_CONF_VARS'] [$this->loginType] ['cookieDomain'] )) {
			$cookieDomain = $GLOBALS ['TYPO3_CONF_VARS'] [$this->loginType] ['cookieDomain'];
		}
		
		if ($cookieDomain) {
			if ($cookieDomain {0} == '/') {
				$match = array ();
				$matchCnt = @preg_match ( $cookieDomain, t3lib_div::getIndpEnv ( 'TYPO3_HOST_ONLY' ), $match );
				if ($matchCnt === FALSE) {
					t3lib_div::sysLog ( 'The regular expression for the cookie domain (' . $cookieDomain . ') contains errors. The session is not shared across sub-domains.', 'Core', 3 );
				} elseif ($matchCnt) {
					$result = $match [0];
				}
			} else {
				$result = $cookieDomain;
			}
		}
		
		return $result;
	}
}
