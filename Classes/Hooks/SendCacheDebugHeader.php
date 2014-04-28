<?php

namespace AOE\Cacheinfo\Hooks;

/***************************************************************
*  Copyright notice
*
*  (c) 2010 AOE media GmbH <dev@aoemedia.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 3 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Hook class for TYPO3 - 
 * Sends HTTP headers for debuging caching situations *
 *
 * @package cacheinfo
 * @subpackage Hooks
 * @author Oliver Hader
 * @author Thomas Schuster
 * @author Daniel Pötzinger
 * @author Benjamin Mack
 */
class SendCacheDebugHeader {

	/**
	 * cookies used for debug infos
	 * @var \string
	 */
	const HTTP_Debug_HeaderKey = 'X-T3CacheInfo';

	/**
	 * cookie used to tell proxys that they can cache
	 * @var \string
	 */
	const HTTP_Cacheallowed_HeaderKey = 'X-T3Cache';

	/**
	 * Tell the proxy that a cookie is set by the current request
	 * @var \string
	 */
	const HTTP_TYPO3UserCookie_HeaderKey = 'X-T3SetCookie';

	/**
	 * TODO: description
	 * @var \string
	 */
	const HTTP_CacheTags_HeaderKey = 'X-T3CacheTags';

	/**
	 * Sends HTTP headers for debuging caching situations. 
	 *
	 * @param       array           $parameters Parameters delivered by the calling parent object (not used here)
	 * @param       \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController        $parent The calling parent object
	 * @return      void
	 */
	public function sendCacheDebugHeader(array $parameters, \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $parent) {

		$cacheDebug = array();
		$cachingAllowed = FALSE;
		if ($parent->cacheContentFlag) {
			$cacheDebug[] = 'cacheContentFlag';
		} else {
			$cacheDebug[] = 'noCacheContentFlag';
		}

		// Notify about the state of pages.fe_login_mode
		if ($parent->loginAllowedInBranch) {
			$cacheDebug[] = 'loginAllowedInBranch';
		} else {
			$cacheDebug[] = 'noLoginAllowedInBranch';
		}

		if ($parent->no_cache) {
			$cacheDebug[] = '!no_cache!';
		}

		if ($parent->isStaticCacheble()) {
			$cacheDebug[] = 'staticCacheable';
		}

		if ($parent->isClientCachable) {
			$cachingAllowed = TRUE;
			$cacheDebug[] = 'ClientCache';
		} else {
			$cacheDebug[] = 'noClientCache';
		}

		if ($parent->isUserOrGroupSet()) {
			$cacheDebug[] = 'userOrGroupSet';
		}

		if ($parent->isINTincScript()) {
			$cachingAllowed = FALSE;
			$cacheDebug[] = '_INT';
			$cacheDebug = array_merge($cacheDebug, $this->getIntScriptsDescription($parent->config['INTincScript']));
		}

		// @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
		$frontEndUser = $GLOBALS['TSFE']->fe_user;

		// ->loginSessionStarted
		// ->dontSetCookie
		// ->user

		if ($this->isFrontendUserActive($frontEndUser)) {
			$cacheDebug[] = 'loggedin';
			$cachingAllowed = FALSE;
		} else {
			$cacheDebug[] = 'not_loggedin';
		}

		if ($this->isFrontendUserLoggingIn($frontEndUser)) {
			$cacheDebug[] = 'loggingin';
		}
		if ($this->isFrontendUserLoggingOut($frontEndUser)) {
			$cacheDebug[] = 'loggingout';
		}

		if (isset($parent->pageCacheTags) && is_array($parent->pageCacheTags)) {
			header('X-CacheTags: |'.implode('|', $parent->pageCacheTags).'|');
		}


		if (count($cacheDebug)) {
			header(self::HTTP_Debug_HeaderKey . ': ' . implode(',', $cacheDebug));
		}

		if (($this->isFrontendUserLoggingIn($frontEndUser)) && $this->isFrontendUserActive($frontEndUser)) {
			// user just logged in, pass through varnish, do not discard cookies
			header(self::HTTP_TYPO3UserCookie_HeaderKey . ': 1' );
		}

		if ($cachingAllowed) {
			header(self::HTTP_Cacheallowed_HeaderKey . ': 1' );
		}
	}

	/**
	 * getIntScriptsDescription
	 *
	 * @return array with "speaking" description of user ints
	 * @param array
	 */
	private function getIntScriptsDescription(array $scriptsConfig) {
		$ints = array();
		foreach ($scriptsConfig as $confs) {
			foreach ($confs['conf'] as $confKey => $typoScriptConfiguration) {
				if ($confKey == 'userFunc' && is_string($typoScriptConfiguration)) {
					$ints[] = $typoScriptConfiguration;
				}
				if (is_array($typoScriptConfiguration) && isset($typoScriptConfiguration['userFunc'])) {
					$ints[] = $typoScriptConfiguration ['userFunc'];
				}
			}
		}
		return $ints;
	}

	/**
	 * Determines whether a valid frontend user session is currently active.
	 *
	 * @param \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $frontendUser
	 * @return boolean
	 */
	protected function isFrontendUserActive($frontendUser) {
		return (isset($frontendUser->user['uid']) && $frontendUser->user['uid']);
	}

	/**
	 * Determines whether a frontend user currently tries to log in.
	 *
	 * @param       $frontEndUser
	 * @return      boolean
	 */
	protected function isFrontendUserLoggingIn($frontEndUser) {
		$loginData = $frontEndUser->getLoginFormData();
		return (isset($loginData['uident']) && $loginData['uident'] && $loginData['status'] === 'login');
	}

	/**
	 * Determines whether a frontend user currently tries to log out.
	 *
	 * @param       $frontEndUser
	 * @return      boolean
	 */
	protected function isFrontendUserLoggingOut($frontEndUser) {
		$loginData = $frontEndUser->getLoginFormData();
		return (isset($loginData['status']) && $loginData['status'] == 'logout');
	}
}

