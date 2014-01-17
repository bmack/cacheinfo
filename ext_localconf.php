<?php

$conf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['cacheinfo']);

// we don't wantt set the fe_user cookie by default
if (isset($conf['setOnlyCookieForLogin']) && $conf['setOnlyCookieForLogin'] == 1) {
	$TYPO3_CONF_VARS['FE']['dontSetCookie'] = TRUE;
}

// register hooks
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][] = 'AOE\\Cacheinfo\\Hooks\\Userauth->writeLoginSessionCookie';
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output']['cacheinfo'] = 'AOE\\Cacheinfo\\Hooks\\SendCacheDebugHeader->sendCacheDebugHeader';
