<?php

$temp_metacolumns = [
	'copyright' => [
		'exclude' => 1,
		'label' => 'Copyright',
		'config' => [
			'type' => 'input',
			'size' => 20,
			'eval' => 'trim'
        ],
    ],
];


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_file_metadata',$temp_metacolumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('sys_file_metadata','','copyright','after:source');