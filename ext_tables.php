<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

#t3lib_extMgm::allowTableOnStandardPages("tx_ppwlunchmenu_fare");

$TCA["tx_ppwlunchmenu_fare"] = Array (
	"ctrl" => Array (
		'title' => 'LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_fare',		
		'label' => 'fare_title',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		"sortby" => "sorting",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_ppwlunchmenu_fare.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, fare_title, fare_desc_german, fare_desc_english, fare_image, fare_translated",
	)
);




$TCA["tx_ppwlunchmenu_config"] = Array (
	"ctrl" => Array (
		'title' => 'LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_config',		
		'label' => 'config_title',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		"sortby" => "sorting",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_ppwlunchmenu_config.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, config_title, config_pid, show_uid, show_root, show_date",
	)
);




$TCA["tx_ppwlunchmenu_types"] = Array (
	"ctrl" => Array (
		'title' => 'LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_types',		
		'label' => 'type_title',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		"sortby" => "sorting",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_ppwlunchmenu_types.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, type_title, type_title_engl, stars",
	)
);




$TCA["tx_ppwlunchmenu_bill"] = Array (
	"ctrl" => Array (
		'title' => 'LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_bill',		
		'label' => 'draft_title',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		"default_sortby" => "ORDER BY crdate DESC",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",	
			"starttime" => "starttime",	
			"endtime" => "endtime",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_ppwlunchmenu_bill.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, draft_title, de_desc, default_desc, food_price, draft_uid, type_uid, translated",
	)
);




$TCA["tx_ppwlunchmenu_closed"] = Array (
	"ctrl" => Array (
		'title' => 'LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_closed',		
		'label' => 'uid',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_ppwlunchmenu_closed.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, date, closing_cause",
	)
);



$TCA["tx_ppwlunchmenu_closing_types"] = Array (
	"ctrl" => Array (
		'title' => 'LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_closing_types',		
		'label' => 'closing_title',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_ppwlunchmenu_closing_types.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, closing_title",
	)
);


if (TYPO3_MODE=="BE")	{
		
	t3lib_extMgm::addModule("web","txppwlunchmenuM1","before:info",t3lib_extMgm::extPath($_EXTKEY)."mod1/");
}


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';


t3lib_extMgm::addPlugin(array('LLL:EXT:ppw_lunchmenu/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Lunchmenusystem");
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1'] ='pi_flexform';

t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:'.$_EXTKEY . '/flexform.xml');


if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_ppwlunchmenu_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_ppwlunchmenu_pi1_wizicon.php';
?>
