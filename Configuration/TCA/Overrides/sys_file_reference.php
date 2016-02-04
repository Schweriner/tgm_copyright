<?php

// TODO: Implement a userFunc which can set the field to required if metadata is empty!

$tmp_tgm_copyright_columns = array(
	'copyright' => array(
        'l10n_mode' => 'prefixLangTitle',
		'exclude' => 1,
		'label' => 'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.copyright',
		'config' => array(
			'type' => 'input',
			'size' => 20,
            'default' => '',
            'placeholder' => '__row|uid_local|metadata|copyright',
            'mode' => 'useOrOverridePlaceholder',
            'eval' => 'null',
        ),
	),
);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_file_reference',$tmp_tgm_copyright_columns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('sys_file_reference','imageoverlayPalette','--linebreak--,copyright','after:description');