<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_ppwlunchmenu_fare"] = Array (
	"ctrl" => $TCA["tx_ppwlunchmenu_fare"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,fare_title,fare_desc_german,fare_desc_english,fare_image,fare_translated"
	),
	"feInterface" => $TCA["tx_ppwlunchmenu_fare"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.xml:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"fare_title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_fare.fare_title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,trim",
			)
		),
		"fare_desc_german" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_fare.fare_desc_german",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"fare_desc_english" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_fare.fare_desc_english",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"fare_image" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_fare.fare_image",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],	
				"max_size" => 1000,	
				"uploadfolder" => "uploads/tx_ppwlunchmenu",
				"show_thumbs" => 1,	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"fare_translated" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_fare.fare_translated",		
			"config" => Array (
				"type" => "check",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, fare_title, fare_desc_german;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_ppwlunchmenu/rte/], fare_desc_english;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_ppwlunchmenu/rte/], fare_image, fare_translated")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_ppwlunchmenu_config"] = Array (
	"ctrl" => $TCA["tx_ppwlunchmenu_config"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,config_title,config_pid,show_uid,show_root,show_date"
	),
	"feInterface" => $TCA["tx_ppwlunchmenu_config"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.xml:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"config_title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_config.config_title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,trim",
			)
		),
		"config_pid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_config.config_pid",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "pages",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"show_uid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_config.show_uid",		
			"config" => Array (
				"type" => "radio",
				"items" => Array (
					Array("LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_config.show_uid.I.0", "0"),
					Array("LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_config.show_uid.I.1", "1"),
				),
			)
		),
		"show_root" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_config.show_root",		
			"config" => Array (
				"type" => "radio",
				"items" => Array (
					Array("LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_config.show_root.I.0", "0"),
					Array("LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_config.show_root.I.1", "1"),
				),
			)
		),
		"show_date" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_config.show_date",		
			"config" => Array (
				"type" => "radio",
				"items" => Array (
					Array("LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_config.show_date.I.0", "0"),
					Array("LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_config.show_date.I.1", "1"),
				),
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, config_title, config_pid, show_uid, show_root, show_date")
		
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_ppwlunchmenu_types"] = Array (
	"ctrl" => $TCA["tx_ppwlunchmenu_types"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,type_title,stars"
	),
	"feInterface" => $TCA["tx_ppwlunchmenu_types"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.xml:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"type_title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_types.type_title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,trim",
			)
		),
        "type_title_engl" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_types.type_title_engl",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
		"stars" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_types.stars",		
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_types.stars.I.0", "0"),
					Array("LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_types.stars.I.1", "1"),
					Array("LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_types.stars.I.2", "2"),
					Array("LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_types.stars.I.3", "3"),
				),
				"size" => 1,	
				"maxitems" => 1,
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, type_title, type_title_engl, stars")
		
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_ppwlunchmenu_bill"] = Array (
	"ctrl" => $TCA["tx_ppwlunchmenu_bill"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,starttime,endtime,draft_title,de_desc,default_desc,food_price,draft_uid,type_uid,translated"
	),
	"feInterface" => $TCA["tx_ppwlunchmenu_bill"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.xml:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"starttime" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.xml:LGL.starttime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"default" => "0",
				"checkbox" => "0"
			)
		),
		"endtime" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.xml:LGL.endtime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => "0",
				"range" => Array (
					"upper" => mktime(0,0,0,12,31,2020),
					"lower" => mktime(0,0,0,date("m")-1,date("d"),date("Y"))
				)
			)
		),
		"draft_title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_bill.draft_title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,trim",
			)
		),
		"de_desc" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_bill.de_desc",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"default_desc" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_bill.default_desc",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"food_price" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_bill.food_price",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
		"draft_uid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_bill.draft_uid",		
			"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("",0),
				),
				"foreign_table" => "tx_ppwlunchmenu_fare",	
				"foreign_table_where" => "AND tx_ppwlunchmenu_fare.pid=###STORAGE_PID### ORDER BY tx_ppwlunchmenu_fare.uid",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"type_uid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_bill.type_uid",		
			"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("",0),
				),
				"foreign_table" => "tx_ppwlunchmenu_types",	
				"foreign_table_where" => "AND tx_ppwlunchmenu_types.pid=###STORAGE_PID### ORDER BY tx_ppwlunchmenu_types.uid",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"translated" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_bill.translated",		
			"config" => Array (
				"type" => "radio",
				"items" => Array (
					Array("LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_bill.translated.I.0", "0"),
					Array("LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_bill.translated.I.1", "1"),
				),
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, draft_title, de_desc, default_desc, food_price, draft_uid, type_uid, translated")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "starttime, endtime")
	)
);



$TCA["tx_ppwlunchmenu_closed"] = Array (
	"ctrl" => $TCA["tx_ppwlunchmenu_closed"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,date,closing_cause"
	),
	"feInterface" => $TCA["tx_ppwlunchmenu_closed"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.xml:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"date" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_closed.date",		
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "datetime",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"closing_cause" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_closed.closing_cause",		
			"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("",0),
				),
				"foreign_table" => "tx_ppwlunchmenu_closing_types",	
				"foreign_table_where" => "ORDER BY tx_ppwlunchmenu_closing_types.uid",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,	
				"wizards" => Array(
					"_PADDING" => 2,
					"_VERTICAL" => 1,
					"add" => Array(
						"type" => "script",
						"title" => "Create new record",
						"icon" => "add.gif",
						"params" => Array(
							"table"=>"tx_ppwlunchmenu_closing_types",
							"pid" => "###CURRENT_PID###",
							"setValue" => "prepend"
						),
						"script" => "wizard_add.php",
					),
				),
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, date, closing_cause")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_ppwlunchmenu_closing_types"] = Array (
	"ctrl" => $TCA["tx_ppwlunchmenu_closing_types"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,closing_title"
	),
	"feInterface" => $TCA["tx_ppwlunchmenu_closing_types"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.xml:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"closing_title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ppw_lunchmenu/locallang_db.xml:tx_ppwlunchmenu_closing_types.closing_title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, closing_title")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>
