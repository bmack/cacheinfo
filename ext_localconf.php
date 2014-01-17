<?php

if (!defined ('TYPO3_MODE')) {
	die('Access denied.');
}

$extensionConfiguration = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['cacheinfo']);

// we don't want set the fe_user cookie by default
if (!is_array($extensionConfiguration) || !empty($extensionConfiguration['setOnlyCookieForLogin'])) {
	$TYPO3_CONF_VARS['FE']['dontSetCookie'] = TRUE;
}

// register hooks
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][] = 'AOE\\Cacheinfo\\Hooks\\Userauth->writeLoginSessionCookie';
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output']['cacheinfo'] = 'AOE\\Cacheinfo\\Hooks\\SendCacheDebugHeader->sendCacheDebugHeader';
