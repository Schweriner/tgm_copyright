<?php

// TODO: Implement a userFunc which can set the field to required if metadata is empty!

$fieldConf = $GLOBALS['TCA']['sys_file_reference']['columns']['title'];
$fieldConf['label'] = 'Copyright';
$fieldConf['config']['placeholder'] = '__row|uid_local|metadata|copyright';


$tmp_tgm_copyright_columns = [
	'copyright' => $fieldConf,
	'uid_foreign' => [
		'config' => [
			'type' => 'passthrough'
		]
	],
];


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_file_reference',$tmp_tgm_copyright_columns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('sys_file_reference','imageoverlayPalette','copyright','after:alternative');
