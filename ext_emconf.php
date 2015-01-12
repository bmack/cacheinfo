<?php

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Cache Info',
	'description' => 'Adjust server responses and adds cache debug headers to make more pages cachable (e.g. varnish or nginx)',
	'category' => 'fe',
	'shy' => 0,
	'version' => '1.0.0',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 1,
	'lockType' => '',
	'author' => 'AOE and Benni Mack',
	'author_email' => 'benni@typo3.org',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.2.0-7.9.99'
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);

?>
