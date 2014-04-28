<?php

if (!defined ('TYPO3_MODE')) {
	die('Access denied.');
}

// "only set cookie when logged in" works in 6.2 out-of-the-box
if (version_compare('6.2', TYPO3_branch, '>')) {

	$extensionConfiguration = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['cacheinfo']);
	// we don't want set the fe_user cookie by default
	if (!is_array($extensionConfiguration) || !empty($extensionConfiguration['setOnlyCookieForLogin'])) {
		$TYPO3_CONF_VARS['FE']['dontSetCookie'] = TRUE;
	}

	$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output']['cacheinfo'] = 'AOE\\Cacheinfo\\Hooks\\SendCacheDebugHeader->sendCacheDebugHeader';
}

// register hook for sending cookie
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][] = 'AOE\\Cacheinfo\\Hooks\\Userauth->writeLoginSessionCookie';
