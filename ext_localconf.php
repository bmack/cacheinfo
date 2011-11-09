<?php

//include because it needs to be prefixed with user_
require_once(t3lib_extMgm::extPath('cacheinfo').'Classes/Hooks/Userauth.php');
$conf = unserialize ( $GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cacheinfo'] );
if (isset($conf['setOnlyCookieForLogin']) && $conf['setOnlyCookieForLogin'] == 1) {
	$TYPO3_CONF_VARS['FE']['dontSetCookie'] = TRUE;    // we don't wantt set the fe_user cookie by default
}
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][] = 'user_Tx_Cacheinfo_Hooks_Userauth->writeLoginSessionCookie';
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output']['cacheinfo'] = 'EXT:cacheinfo/Classes/Hooks/SendCacheDebugHeader.php:&tx_Cacheinfo_Hooks_SendCacheDebugHeader->sendCacheDebugHeader';


