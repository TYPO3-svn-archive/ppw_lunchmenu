<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Stefan Horenkamp <horenkamp@pietzpluswild.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


	
	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);	
require ("conf.php");
require ($BACK_PATH."init.php");
require ($BACK_PATH."template.php");

define("BACK_PATH",$BACK_PATH);
$LANG->includeLLFile("EXT:ppw_lunchmenu/mod1/locallang.xml");
require_once(PATH_t3lib.'class.t3lib_scbase.php');
require_once(PATH_t3lib.'class.t3lib_tceforms.php');
require_once(PATH_t3lib.'class.t3lib_tsparser.php');
if (!defined(PATH_tslib)) {
	if (file_exists(PATH_site.'tslib/'."class.tslib_content.php")) {
	define('PATH_tslib', PATH_site.'tslib/');
	} else {
	define('PATH_tslib', PATH_site.'typo3/sysext/cms/tslib/');
	}
}
require_once (PATH_tslib."class.tslib_content.php");
require_once('./class.tx_ppwlunchmenu_bills.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]


/**
 * Module 'ppw_lunchmenu' for the 'ppw_lunchmenu' extension.
 *
 * @author	Stefan Horenkamp <horenkamp@pietzpluswild.de>
 * @package	TYPO3
 * @subpackage	tx_ppwlunchmenu
 */
class  tx_ppwlunchmenu_module1 extends t3lib_SCbase {
				var $pageinfo;
				var $maxEntries = 10;
				var $newDraftUID;
				var $timeWeek = 604800;
				var $rowConfig; 
				
				
				/**
				 * Initializes the Module
				 * @return	void
				 */
				function init()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
					$this->cObj = t3lib_div::makeInstance("tslib_cObj");
					parent::init();
					
				}

				/**
				 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
				 *
				 * @return	void
				 */
				function menuConfig()	{
					global $LANG;
					$this->MOD_MENU = Array (
						'function' => Array (
							'2' => $LANG->getLL('function2'),
							'1' => $LANG->getLL('function5'),
							
						)
					);
										
					$res  = @$GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'*',
						'tx_ppwlunchmenu_config',
						'deleted=0 AND hidden=0',
						'',
						'',
						''
					);
					
					while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
						$this->MOD_MENU['function'][$row['config_pid'].'_'.$row['config_title'].'_100'] = $LANG->getLL('function4').': '.$row['config_title'];     
						$this->MOD_MENU['function'][$row['config_pid'].'_'.$row['config_title'].'_200'] = $LANG->getLL('function1').': '.$row['config_title'];     
						$this->MOD_MENU['function'][$row['config_pid'].'_'.$row['config_title'].'_300'] = $LANG->getLL('function3').': '.$row['config_title'];     
						
					}
					parent::menuConfig();
				}

				/**
				 * Main function of the module. Write the content to $this->content
				 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
				 *
				 * @return	[type]		...
				 */
				function main()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS,$TBE_STYLES;
					
					$TBE_STYLES['stylesheet1'] = '/sysext/t3skin/stylesheets/stylesheet_post.css';
					$TBE_STYLES['stylesheet2'] = t3lib_extMgm::extRelPath('ppw_lunchmenu').'mod1/css/style.css';
					// Access check!
					// The page will show only if there is a valid page and if this page may be viewed by the user
					$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
					$access = is_array($this->pageinfo) ? 1 : 0;
					
					if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id) || ($BE_USER->user['uid'] && !$this->id))	{

							// Draw the header.
						$this->doc = t3lib_div::makeInstance('mediumDoc');
						$this->doc->backPath = $BACK_PATH;
						$this->doc->form='<form action="" method="POST">';

							// JavaScript
						$this->doc->JScode = '
							<script language="javascript" type="text/javascript">
								script_ended = 0;
								function jumpToUrl(URL)	{
									document.location = URL;
								}
							</script>
						';
						$this->doc->postCode='
							<script language="javascript" type="text/javascript">
								script_ended = 1;
								if (top.fsMod) top.fsMod.recentIds["web"] = 0;
							</script>
						';

						$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

						$this->content.=$this->doc->startPage($LANG->getLL('title'));
						$this->content.=$this->doc->header($LANG->getLL('title'));
						$this->content.=$this->doc->spacer(5);
						$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
						$this->content.=$this->doc->divider(5);


						// Render content:
						$this->moduleContent();


						// ShortCut
						if ($BE_USER->mayMakeShortcut())	{
							$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
						}

						$this->content.=$this->doc->spacer(10);
					} else {
							// If no access or if ID == zero

						$this->doc = t3lib_div::makeInstance('mediumDoc');
						$this->doc->backPath = $BACK_PATH;

						$this->content.=$this->doc->startPage($LANG->getLL('title'));
						$this->content.=$this->doc->header($LANG->getLL('title'));
						$this->content.=$this->doc->spacer(5);
						$this->content.=$this->doc->spacer(10);
					}
				}

				/**
				 * Prints out the module HTML
				 *
				 * @return	void
				 */
				function printContent()	{

					$this->content.=$this->doc->endPage();
					echo $this->content;
				}

				/**
				 * Generates the module content
				 *
				 * @return	void
				 */
				function moduleContent()	{
					global $LANG, $BE_USER;
					$content = '';
					if(!$this->getIsConfigured()) {
						
						$content .= $this->createNewConfig();
						$this->content .= $this->doc->section($LANG->getLL('menu.install').':',$content,0,1);
						
						return;
					}
					$res  = @$GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'*',
						'tx_ppwlunchmenu_config',
						'deleted=0 AND hidden=0',
						'',
						'',
						''
					);
					while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
						if($this->MOD_SETTINGS['function'] == $row['config_pid'].'_'.$row['config_title'].'_100'){
							$array = explode('_', $this->MOD_SETTINGS['function']);
							$PID = $array[0];
							$resConfig= $GLOBALS['TYPO3_DB']->exec_SELECTquery(
								'*',
								'tx_ppwlunchmenu_config',
								'deleted=0 AND config_pid='.$PID,
								'',
								'crdate DESC',
								'0,1'
							); 
							$this->rowConfig = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resConfig);
							$content .= $this->showFoodTypes($PID);
							$this->content.=$this->doc->section($LANG->getLL('menu.Types').' '.$array[1].':',$content,0,1);
						} elseif ($this->MOD_SETTINGS['function'] == $row['config_pid'].'_'.$row['config_title'].'_200') {
							$array = explode('_', $this->MOD_SETTINGS['function']);
							$PID = $array[0];
							$resConfig= $GLOBALS['TYPO3_DB']->exec_SELECTquery(
								'*',
								'tx_ppwlunchmenu_config',
								'deleted=0 AND config_pid='.$PID,
								'',
								'crdate DESC',
								'0,1'
							); 
							$this->rowConfig = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resConfig);
							$content .= $this->getFares($PID);
							$this->content.=$this->doc->section($LANG->getLL('menu.Fares').' '.$array[1].':',$content,0,1);
							
						} elseif ($this->MOD_SETTINGS['function'] == $row['config_pid'].'_'.$row['config_title'].'_300') {
							$array = explode('_', $this->MOD_SETTINGS['function']);
							$PID = $array[0];
							$resConfig= $GLOBALS['TYPO3_DB']->exec_SELECTquery(
								'*',
								'tx_ppwlunchmenu_config',
								'deleted=0 AND config_pid='.$PID,
								'',
								'crdate DESC',
								'0,1'
							); 
							$this->rowConfig = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resConfig);
							$install = t3lib_div::makeInstance('tx_ppwlunchmenu_bills');
							if (t3lib_div::GPvar('week') == 'back') {
								$time = strtotime(date('Y-m-d 9:00', t3lib_div::GPvar('time')-$this->timeWeek));
								$param = '&week='.t3lib_div::GPvar('week').'&time='.$time;
									
							} elseif (t3lib_div::GPvar('week') == 'forward') {
								$time = strtotime(date('Y-m-d 9:00', t3lib_div::GPvar('time')+$this->timeWeek));
							} else {
								$time = strtotime(date('Y-m-d 9:00', time()));
							}
							
							
							if (t3lib_div::GPvar('uid')) {
								$time = t3lib_div::GPvar('time');
							} 
							
							$content = $install->main($PID, $time, $BE_USER->user['uid'], $this->rowConfig);
							#$content .= $this->getFares($PID);
							$this->content.=$this->doc->section($LANG->getLL('menu.BillOfFare').' '.$array[1].':',$content,0,1);
						}
					}
					
					
					switch($this->MOD_SETTINGS['function'])	{
						case 1:
							$content .= $this->showHolidayManagement();
							$this->content.=$this->doc->section($LANG->getLL('menu.Holiday').':',$content,0,1);
						break;
						case 2:
							$content .= $this->installLunchMenu();
							$this->content.=$this->doc->section($LANG->getLL('menu.install').':',$content,0,1);
						break;
					   
					}
				}
				
				
				/**
				* 
				*/
				function installLunchMenu() {
					global $LANG;
					switch (t3lib_div::GPvar('action')) {
						case "hide":
							$this->HideDraft(t3lib_div::GPvar('table'));
						break;
						case "unhide":
							$this->UnHideDraft(t3lib_div::GPvar('table'));
						break;
						case "delete":
							$this->DeleteDraft(t3lib_div::GPvar('table'));
						break;
					}
					$res  = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'*',
						'tx_ppwlunchmenu_config',
						'deleted=0 AND hidden=0',
						'',
						'',
						''
					);	
					$count = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
					if ($count !=0) {
						$out = '';
						$out .= '<table cellspacing="2" cellpadding="2" border="1" bordercolor="#fff" width="850">';
						$out .= $this->getConfigHeader('config');
						$results = $this->getConfigs();
						$out .= $results['out'];
						$PID = $results['pid'];
						$out .= '</table>'; 
						$out .= '<br /><form method="post"><input type="submit" onclick="window.location.href=\'/typo3/alt_doc.php?returnUrl=/typo3conf/ext/ppw_lunchmenu/mod1/index.php?&id=0&SET[function]=2&edit[tx_ppwlunchmenu_config]['.$PID.']=new\'; return false;" value="'.$LANG->getLL('config.Submit').'"/></form>'; 
						return $out;
					} else {
						return $this->createNewConfig();
					} 
				}
				
				
				/**
				* 
				*/
				function showFoodTypes($PID) {
					$res  = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'*',
						'tx_ppwlunchmenu_types',
						'deleted=0 AND hidden=0 AND pid='.$PID,
						'',
						'',
						''
					);
					$countTypes = $GLOBALS['TYPO3_DB']->sql_num_rows($res);

					if ($countTypes != 0) {
						return $this->displayTypes($PID);
					} else {
						return $this->createNewType($PID);
					}
					
				}
				
				
				function showHolidayManagement(){
					$res  = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'*',
						'tx_ppwlunchmenu_closing_types',
						'deleted=0 AND hidden=0',
						'',
						'',
						''
					);
					$count = $GLOBALS['TYPO3_DB']->sql_num_rows($res);

					if ($count != 0) {
						return $this->displayHolidays();
					} else {
						return $this->createNewHoliday();
					}	
				}
				
				/**
				* 
				*/
				function createNewHoliday() {
					global $LANG, $BE_USER;
					 $conf = array(
						'itemFormElName' => 'sysfolder',
						
						'fieldChangeFunc' => array(''),
						'fieldConf' => array(
							'config' => array(
								'type' => 'select',
								"items" => Array (
									Array("",0),
								),
								'foreign_table' => 'pages',
								'foreign_table_where' => 'AND pages.hidden=0 AND pages.deleted=0 AND pages.doktype=254',
								'size' => 1,
								#'autoSizeMax' => 10,
								'minitems' => 0,
								'maxitems' => 1
							)
						)
					);
					$form = t3lib_div::makeInstance('t3lib_TCEforms');
					$input = $form->getSingleField_typeSelect('','sysfolder',array(),$conf); 
					
					$out .= '<script type="text/javascript">
							function changeValue() {
							
								var storage = document.forms[0].elements[\'sysfolder\'].value;
								window.location.href=\'/typo3/alt_doc.php?returnUrl=/typo3conf/ext/ppw_lunchmenu/mod1/index.php?&id=0&SET[function]=2&edit[tx_ppwlunchmenu_closing_types][\'+storage+\']=new\';
							}
							</script>';
					$out .= '<p class="bodytext">'.$LANG->getLL('error.NoHoliday').'</p>';
					$out .= '<br /><form method="post" onsubmit="changeValue();return false;"><label for="storage" style="padding-right:20px;font-size:10px;font-weight:bold;">'.$LANG->getLL('holiday.PID').'</label>';
					$out .= $input.'<br /><br />';
					$out .= '<input type="submit" onclick="changeValue();return false;" value="'.$LANG->getLL('holiday.Submit').'"/>';
					$out .= '</form>';
					 
					#$this->tceforms->getSingleField_typeSelect('','tx_mmforum_install[conf][0]['.$fieldname.']',array(),$conf);
					return $out;
				}
				
				
				/**
				* 
				*/
				function createNewType($PID) {
					global $LANG, $BE_USER;
					 $conf = array(
						'itemFormElName' => 'sysfolder',
						
						'fieldChangeFunc' => array(''),
						'fieldConf' => array(
							'config' => array(
								'type' => 'select',
								"items" => Array (
									Array("",0),
								),
								'foreign_table' => 'pages',
								'foreign_table_where' => 'AND pages.hidden=0 AND pages.deleted=0 AND pages.doktype=254',
								'size' => 1,
								#'autoSizeMax' => 10,
								'minitems' => 0,
								'maxitems' => 1
							)
						)
					);
				
					
					$form = t3lib_div::makeInstance('t3lib_TCEforms');
					$input = $form->getSingleField_typeSelect('','sysfolder',array(),$conf); 
					
					
					$out .= '<script type="text/javascript">
							function changeValueType() {
							
								var storage = document.forms[0].elements[\'sysfolder\'].value;
								window.location.href=\'/typo3/alt_doc.php?returnUrl=/typo3conf/ext/ppw_lunchmenu/mod1/index.php?&id=0&SET[function]=1&edit[tx_ppwlunchmenu_types][\'+storage+\']=new\';
							}
							</script>';
					$out .= '<p class="bodytext">'.$LANG->getLL('error.NoType').'</p>';
					$out .= '<br /><form method="post" onsubmit="changeValueType();return false;"><input type="hidden" name="sysfolder" value="'.$PID.'"/>';
					$out .= '<input type="submit" onclick="changeValueType();return false;" value="'.$LANG->getLL('type.Submit').'"/>';
					$out .= '</form>';
					 
					#$this->tceforms->getSingleField_typeSelect('','tx_mmforum_install[conf][0]['.$fieldname.']',array(),$conf);
					return $out;
				}
				
				
				/**
				* 
				*/
				function displayTypes($PID) {
					global $LANG;
					switch (t3lib_div::GPvar('action')) {
						case "hide":
							$this->HideDraft(t3lib_div::GPvar('table'));
						break;
						case "unhide":
							$this->UnHideDraft(t3lib_div::GPvar('table'));
						break;
						case "delete":
							$this->DeleteDraft(t3lib_div::GPvar('table'));
						break;
					}
					$res  = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'*',
						'tx_ppwlunchmenu_types',
						'deleted=0 AND hidden=0 AND pid='.$PID,
						'',
						'',
						''
					);	
					$count = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
					if ($count !=0) {
						$out = '';
						$out .= '<table cellspacing="2" cellpadding="2" border="1" bordercolor="#fff" width="850">';
						$out .= $this->getConfigHeader('types');
						$results = $this->getTypes($PID);
						$out .= $results['out'];
						$PID = $results['pid'];
						$out .= '</table>'; 
						$out .= '<br /><form method="post"><input type="submit" onclick="window.location.href=\'/typo3/alt_doc.php?returnUrl=/typo3conf/ext/ppw_lunchmenu/mod1/index.php?&id=0&SET[function]=2&edit[tx_ppwlunchmenu_types]['.$PID.']=new\'; return false;" value="'.$LANG->getLL('type.Submit').'"/></form>'; 
						return $out;
					} else {
						return $this->createNewType($PID);
					} 
				}
				
				
				function displayHolidays() {
					global $LANG;
					switch (t3lib_div::GPvar('action')) {
						case "hide":
							$this->HideDraft(t3lib_div::GPvar('table'));
						break;
						case "unhide":
							$this->UnHideDraft(t3lib_div::GPvar('table'));
						break;
						case "delete":
							$this->DeleteDraft(t3lib_div::GPvar('table'));
						break;
					}
					$res  = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'*',
						'tx_ppwlunchmenu_closing_types',
						'deleted=0 AND hidden=0',
						'',
						'closing_title ASC',
						''
					);	
					$count = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
					if ($count !=0) {
						$out = '';
						$out .= '<table cellspacing="2" cellpadding="2" border="1" bordercolor="#fff" width="400">';
						$out .= $this->getConfigHeader('holiday');
						$results = $this->getHolidays();
						$out .= $results['out'];
						$PID = $results['pid'];
						$out .= '</table>'; 
						$out .= '<br /><form method="post"><input type="submit" onclick="window.location.href=\'/typo3/alt_doc.php?returnUrl=/typo3conf/ext/ppw_lunchmenu/mod1/index.php?&id=0&SET[function]=2&edit[tx_ppwlunchmenu_closing_types]['.$PID.']=new\'; return false;" value="'.$LANG->getLL('holiday.Submit').'"/></form>'; 
						return $out;
					} else {
						return $this->createNewHoliday();
					}	
				}
				
				
				/**
				* 
				*/
				function getFares($PID) {
					global $LANG;
					switch (t3lib_div::GPvar('action')) {
						case "hide":
							$this->HideDraft(t3lib_div::GPvar('table'));
						break;
						case "unhide":
							$this->UnHideDraft(t3lib_div::GPvar('table'));
						break;
						case "delete":
							$this->DeleteDraft(t3lib_div::GPvar('table'));
						break;
					}
					
					if (t3lib_div::GPvar('sort')) {
						$orderBy = t3lib_div::GPvar('sort').' '.t3lib_div::GPvar('way');
					} else{
						$orderBy = 'crdate ASC';
					}
					if (t3lib_div::GPvar('page')) {
					  if (t3lib_div::GPvar('page') != 1) {
						  $actPage = t3lib_div::GPvar('page');
						  $recordsPerPage = ($actPage*$this->maxEntries)-$this->maxEntries;
					  } else {
						  $actPage = 1;
						  $recordsPerPage = 0;
					  }
					} else{
						$actPage = 1;
						$recordsPerPage = 0;
					}
					
					$out = '';
					$out .= '<div class="formField"><form method="post">';
					$out .= '<label class="formLabel" for="sword">'.$LANG->getLL('search.Sword').'</label> ';
					$out .= '<input type="text" value="'.t3lib_div::GPvar('sword').'" id="sword" name="sword" />';
					$out .= '<input type="submit" value="'.$LANG->getLL('search.Submit').'"/>';
					$out .= '</form></div><br />';
					$out .= '<table cellspacing="2" cellpadding="2" border="1" bordercolor="#fff" width="850">';
					if (strlen(t3lib_div::GPvar('sword')) >= 1) {
						$sword = t3lib_div::GPvar('sword');
						$tableContent = $this->getSearchResults($sword, $orderBy, $PID);
					} else {
						$tableContent .= $this->getFaresOverview($orderBy, $recordsPerPage ,$PID);
					}
					
					$out .= $this->getTableHeader($sword, $actPage);
					$out .= $tableContent;
					$out .= '</table>';
					$out .= '<br /><form method="post"><input type="submit" onclick="window.location.href=\'/typo3/alt_doc.php?returnUrl=/typo3conf/ext/ppw_lunchmenu/mod1/index.php?&id=0&SET[function]=1&edit[tx_ppwlunchmenu_fare]['.$PID.']=new\'; return false;" value="'.$LANG->getLL('new.Submit').'"/></form>';

					return $out; 
				}
				
				
				/**
				* 
				*/
				function createNewConfig() {
					global $LANG, $BE_USER;
					 $conf = array(
						'itemFormElName' => 'sysfolder',
						
						'fieldChangeFunc' => array(''),
						'fieldConf' => array(
							'config' => array(
								'type' => 'select',
								"items" => Array (
									Array("",0),
								),
								'foreign_table' => 'pages',
								'foreign_table_where' => 'AND pages.hidden=0 AND pages.deleted=0 AND pages.doktype=254',
								'size' => 1,
								#'autoSizeMax' => 10,
								'minitems' => 0,
								'maxitems' => 1
							)
						)
					);
					$form = t3lib_div::makeInstance('t3lib_TCEforms');
					$input = $form->getSingleField_typeSelect('','sysfolder',array(),$conf); 
					
					$out .= '<script type="text/javascript">
							function changeValue() {
							
								var storage = document.forms[0].elements[\'sysfolder\'].value;
								window.location.href=\'/typo3/alt_doc.php?returnUrl=/typo3conf/ext/ppw_lunchmenu/mod1/index.php?&id=0&SET[function]=2&edit[tx_ppwlunchmenu_config][\'+storage+\']=new\';
							}
							</script>';
					$out .= '<p class="bodytext">'.$LANG->getLL('error.NoConfig').'</p>';
					$out .= '<br /><form method="post" onsubmit="changeValue();return false;"><label for="storage" style="padding-right:20px;font-size:10px;font-weight:bold;">'.$LANG->getLL('config.PID').'</label>';
					$out .= $input.'<br /><br />';
					$out .= '<input type="submit" onclick="changeValue();return false;" value="'.$LANG->getLL('config.Submit').'"/>';
					$out .= '</form>';
					 
					#$this->tceforms->getSingleField_typeSelect('','tx_mmforum_install[conf][0]['.$fieldname.']',array(),$conf);
					return $out;	
				}
				
				
				/**
				* 
				*/
				function getConfigs() {
					global $LANG;
					$results = array();
					$res  = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'*',
						'tx_ppwlunchmenu_config',
						'deleted=0',
						'',
						'',
						''
					);
					while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
						
						$results['out'] .= '<tr class="tableContent">';
						$results['out'] .= '<td align="center">'.$row['uid'].'</td>';
						$results['out'] .= '<td >'.$row['config_title'].'</td>';
						$rootLine = t3lib_BEfunc::BEgetRootLine($row['config_pid']);
						unset($rootLine[0]);
						sort($rootLine);
						reset($rootLine);
						$path = '/';
						foreach ($rootLine as $key) {
							$path .= $key['title'].'/';
						}
						$results['out'] .= '<td>'.$path.'</td>';
						$results['out'] .= '<td align="center">'.date("d.m.Y H:i ", $row['crdate']).$LANG->getLL('td.Time').'</td>';
						$results['out'] .= '<td align="center">';
						
						$results['out'] .= '&nbsp;<a href="#" onclick="window.location.href=\'/typo3/alt_doc.php?returnUrl=/typo3conf/ext/ppw_lunchmenu/mod1/index.php&edit[tx_ppwlunchmenu_config]['.$row['uid'].']=edit\'; return false;"><img '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/edit2.gif','').' alt="'.$LANG->getLL('option.Edit').'" title="'.$LANG->getLL('option.Edit').'"/></a>';
						
						if($row['hidden'] != 0) {
							$results['out'] .= '&nbsp;<a href="index.php?record='.$row['uid'].'&action=unhide&table=tx_ppwlunchmenu_config"><img '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/button_unhide.gif','').' alt="'.$LANG->getLL('option.Unhide').'" title="'.$LANG->getLL('option.Unhide').'"/></a>';
						} else {
							$results['out'] .= '&nbsp;<a href="index.php?record='.$row['uid'].'&action=hide&table=tx_ppwlunchmenu_config"><img '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/button_hide.gif','').' alt="'.$LANG->getLL('option.Hide').'" title="'.$LANG->getLL('option.Hide').'"/></a>';
						}
						$results['out'] .= '&nbsp;<a href="index.php?record='.$row['uid'].'&action=delete&table=tx_ppwlunchmenu_config"><img '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/garbage.gif','').' alt="'.$LANG->getLL('option.Delete').'" title="'.$LANG->getLL('option.Delete').'"/></a>';
						$results['out'] .= '</td>';
						$results['out'] .= '</tr>';
						$pid = $row['pid'];
					}
					$results['pid'] = $pid;
					return $results;
				}
				
				
				/**
				* 
				*/
				function getHolidays() {
					global $LANG;
					$results = array();
					$res  = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'*',
						'tx_ppwlunchmenu_closing_types',
						'deleted=0',
						'',
						'closing_title ASC',
						''
					);
					$count = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
					while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
						
						$results['out'] .= '<tr class="tableContent">';
						$results['out'] .= '<td >'.$row['closing_title'].'</td>';
						
						
						$results['out'] .= '<td align="center">';
						$results['out'] .= '&nbsp;<a href="#" onclick="window.location.href=\'/typo3/alt_doc.php?returnUrl=/typo3conf/ext/ppw_lunchmenu/mod1/index.php&edit[tx_ppwlunchmenu_closing_types]['.$row['uid'].']=edit\'; return false;"><img '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/edit2.gif','').' alt="'.$LANG->getLL('option.Edit').'" title="'.$LANG->getLL('option.Edit').'"/></a>';
						
						if($row['hidden'] != 0) {
							$results['out'] .= '&nbsp;<a href="index.php?record='.$row['uid'].'&action=unhide&table=tx_ppwlunchmenu_closing_types"><img '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/button_unhide.gif','').' alt="'.$LANG->getLL('option.Unhide').'" title="'.$LANG->getLL('option.Unhide').'"/></a>';
						} else {
							$results['out'] .= '&nbsp;<a href="index.php?record='.$row['uid'].'&action=hide&table=tx_ppwlunchmenu_closing_types"><img '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/button_hide.gif','').' alt="'.$LANG->getLL('option.Hide').'" title="'.$LANG->getLL('option.Hide').'"/></a>';
						}
						$results['out'] .= '&nbsp;<a href="index.php?record='.$row['uid'].'&action=delete&table=tx_ppwlunchmenu_closing_types"><img '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/garbage.gif','').' alt="'.$LANG->getLL('option.Delete').'" title="'.$LANG->getLL('option.Delete').'"/></a>';

						$results['out'] .= '</td>';
						$results['out'] .= '</tr>';
						$pid = $row['pid'];
					}
					$results['pid'] = $pid;
					return $results;	
				}
				
				
				
				/**
				* 
				*/
				function getTypes($PID) {
					global $LANG;
					$results = array();
					$res  = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'*',
						'tx_ppwlunchmenu_types',
						'deleted=0 AND pid='.$PID,
						'',
						'sorting DESC',
						''
					);
					$count = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
					while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
						
						$results['out'] .= '<tr class="tableContent">';
						if($this->rowConfig['show_uid'] != 0) {
							$results['out'] .= '<td align="center">'.$row['uid'].'</td>';
						}
						$results['out'] .= '<td >'.$row['type_title'].'</td>';
						
						if($this->rowConfig['show_root'] != 0) {
							$rootLine = t3lib_BEfunc::BEgetRootLine($row['pid']);
							#print_r($rootLine);
							unset($rootLine[0]);
							sort($rootLine);
							reset($rootLine);
							$path = '/';
							foreach ($rootLine as $key) {
								$path .= $key['title'].'/';
							}
							$results['out'] .= '<td>'.$path.'</td>';
						}
						if($this->rowConfig['show_date'] != 0) {
							$results['out'] .= '<td align="center">'.date("d.m.Y H:i ", $row['crdate']).$LANG->getLL('td.Time').'</td>';
						}
						
						$results['out'] .= '<td align="center">';
						$results['out'] .= '&nbsp;<a href="#" onclick="window.location.href=\'/typo3/alt_doc.php?returnUrl=/typo3conf/ext/ppw_lunchmenu/mod1/index.php&edit[tx_ppwlunchmenu_types]['.$row['uid'].']=edit\'; return false;"><img '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/edit2.gif','').' alt="'.$LANG->getLL('option.Edit').'" title="'.$LANG->getLL('option.Edit').'"/></a>';
						
						if($row['hidden'] != 0) {
							$results['out'] .= '&nbsp;<a href="index.php?record='.$row['uid'].'&action=unhide&table=tx_ppwlunchmenu_types"><img '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/button_unhide.gif','').' alt="'.$LANG->getLL('option.Unhide').'" title="'.$LANG->getLL('option.Unhide').'"/></a>';
						} else {
							$results['out'] .= '&nbsp;<a href="index.php?record='.$row['uid'].'&action=hide&table=tx_ppwlunchmenu_types"><img '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/button_hide.gif','').' alt="'.$LANG->getLL('option.Hide').'" title="'.$LANG->getLL('option.Hide').'"/></a>';
						}
						$results['out'] .= '&nbsp;<a href="index.php?record='.$row['uid'].'&action=delete&table=tx_ppwlunchmenu_types"><img '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/garbage.gif','').' alt="'.$LANG->getLL('option.Delete').'" title="'.$LANG->getLL('option.Delete').'"/></a>';

						$results['out'] .= '</td>';
						$results['out'] .= '</tr>';
						$pid = $row['pid'];
					}
					$results['pid'] = $pid;
					return $results;
				}
				
				
				
				/**
				* 
				*/
				function getSearchResults($sword, $orderBy, $PID) {
					global $LANG;
					$NewSword = $sword;

                    
					$res  = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'*',
						'tx_ppwlunchmenu_fare',
						'deleted=0 AND pid='.$PID.' AND (uid LIKE "'.$NewSword.'" OR fare_title LIKE "%'.$NewSword.'%")',
						'',
						$orderBy,
						''
					);
                    
					$countNum = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
					
					if ($countNum != 0){
						while($row = @$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
							$out .= '<tr class="tableContent">';
							
							if($this->rowConfig['show_uid'] != 0) {
								$out .= '<td align="center">'.$row['uid'].'</td>';
							}
                            $title = strip_tags($row['fare_desc_german']);													  
							$out .= '<td >'.$row['fare_title'].'&nbsp;<img id="information_'.$row['uid'].'" onmouseover="document.getElementById(\'information_'.$row['uid'].'\').style.cursor= \'help\'" '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/helpbubble.gif','').' title="'.htmlentities($title).'" /></td>';
							$out .= '<td>'.substr($title,0,50).'&nbsp;...</td>';
							if($this->rowConfig['show_date'] != 0) {
								$out .= '<td align="center">'.date("d.m.Y", $row['crdate']).' '.date("H:i", $row['crdate']).' '.$LANG->getLL('td.Time').'</td>';
							}
							$out .= '<td align="center">';
							
							$out .= '&nbsp;<a href="#" onclick="window.location.href=\'/typo3/alt_doc.php?returnUrl=/typo3conf/ext/ppw_lunchmenu/mod1/index.php?sword='.$sword.'&edit[tx_ppwlunchmenu_fare]['.$row['uid'].']=edit\'; return false;"><img '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/edit2.gif','').' alt="'.$LANG->getLL('option.Edit').'" title="'.$LANG->getLL('option.Edit').'"/></a>';
							
							if($row['hidden'] != 0) {
								
								$out .= '&nbsp;<a href="index.php?record='.$row['uid'].'&action=unhide&sword='.$sword.'&table=tx_ppwlunchmenu_fare"><img '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/button_unhide.gif','').'" alt="'.$LANG->getLL('option.Unhide').'" title="'.$LANG->getLL('option.Unhide').'"/></a>';
							} else {
								$out .= '&nbsp;<a href="index.php?record='.$row['uid'].'&action=hide&sword='.$sword.'&table=tx_ppwlunchmenu_fare"><img '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/button_hide.gif','').' alt="'.$LANG->getLL('option.Hide').'" title="'.$LANG->getLL('option.Hide').'"/></a>';
							}
							$out .= '&nbsp;<a href="index.php?record='.$row['uid'].'&action=delete&sword='.$sword.'&table=tx_ppwlunchmenu_fare"><img '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/garbage.gif','').' alt="'.$LANG->getLL('option.Delete').'" title="'.$LANG->getLL('option.Delete').'"/></a>';
							$out .= '</td>';
							$out .= '</tr>';
						}    
					} else {
						$out = '<tr class="tableContent"><td colspan="5">'.$LANG->getLL('error.NoResults').'</td></tr>';    
					} 
					return $out;         
					
					
				}
				
				
				/**
				* 
				*/
				function getFaresOverview($orderBy, $recordsPerPage, $PID) {
					global $LANG;
					$res  = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'*',
						'tx_ppwlunchmenu_fare',
						'deleted=0 AND pid='.$PID,
						'',
						$orderBy,
						$recordsPerPage.','.$this->maxEntries
					);
					$resAllCount  = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'*',
						'tx_ppwlunchmenu_fare',
						'deleted=0 AND pid='.$PID,
						'',
						'',
						''
					);    
					
					$count = @$GLOBALS['TYPO3_DB']->sql_num_rows($resAllCount);
					$countRecords = ceil($count/$this->maxEntries);
					if ($count) {
						$out = '<span style="font-size:12px;"><strong>Seite: </strong>';
						for($i=1; $i <= $countRecords; $i++){
							$sort = (t3lib_div::GPvar('sort')) ? t3lib_div::GPvar('sort') : 'crdate';
							$way = (t3lib_div::GPvar('way')) ? t3lib_div::GPvar('way') : 'DESC';
							$out .= '<a href="index.php?sort='.$sort.'&way='.$way.'&page='.$i.'">'.$i.' </a>';
						}
						
						$out .= '</span><br /><br />';
						while($row = @$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
							$out .= '<tr class="tableContent">';
							if($this->rowConfig['show_uid'] != 0) {
								$out .= '<td align="center">'.$row['uid'].'</td>';
							}
							$title = strip_tags($row['fare_desc_german']);													  
							$out .= '<td >'.$row['fare_title'].'&nbsp;<img id="information_'.$row['uid'].'" onmouseover="document.getElementById(\'information_'.$row['uid'].'\').style.cursor= \'help\'" '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/helpbubble.gif','').' title="'.htmlentities($title).'" /></td>';
							$out .= '<td>'.substr($title,0,50).'&nbsp;...</td>';
							if($this->rowConfig['show_date'] != 0) {
								$out .= '<td align="center">'.date("d.m.Y", $row['crdate']).' '.date("H:i", $row['crdate']).' '.$LANG->getLL('td.Time').'</td>';
							}
							
							$out .= '<td align="center">';
							
							$out .= '&nbsp;<a href="#" onclick="window.location.href=\'/typo3/alt_doc.php?returnUrl=/typo3conf/ext/ppw_lunchmenu/mod1/index.php?&id=0&SET[function]=1&edit[tx_ppwlunchmenu_fare]['.$row['uid'].']=edit\'; return false;"><img '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/edit2.gif','').' alt="'.$LANG->getLL('option.Edit').'" title="'.$LANG->getLL('option.Edit').'"/></a>';
							
							if($row['hidden'] != 0) {
								$out .= '&nbsp;<a href="index.php?record='.$row['uid'].'&action=unhide&table=tx_ppwlunchmenu_fare"><img '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/button_unhide.gif','').' alt="'.$LANG->getLL('option.Unhide').'" title="'.$LANG->getLL('option.Unhide').'"/></a>';
							} else {
								$out .= '&nbsp;<a href="index.php?record='.$row['uid'].'&action=hide&table=tx_ppwlunchmenu_fare"><img '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/button_hide.gif','').' alt="'.$LANG->getLL('option.Hide').'" title="'.$LANG->getLL('option.Hide').'"/></a>';
							}
							$out .= '&nbsp;<a href="index.php?record='.$row['uid'].'&action=delete&table=tx_ppwlunchmenu_fare"><img '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/garbage.gif','').' alt="'.$LANG->getLL('option.Delete').'" title="'.$LANG->getLL('option.Delete').'"/></a>';
							$out .= '</td>';
							$out .= '</tr>';
						}
					} else {
						$out = '<tr class="tableContent"><td colspan="5">'.$LANG->getLL('error.NoDrafts').'</td></tr>'; 
					}
					return $out;
				}
				
				
				
				
				
				/**
				*                                      
				*/
				function getTableHeader($sword, $actPage) {
					global $LANG;
					$out .= '<tr>';
					if($this->rowConfig['show_uid'] != 0) {
						$out .= '<th class="tableHeader">'.$LANG->getLL('th.ID').'<br /><a href="index.php?sort=uid&way=DESC&sword='.$sword.'&page='.$actPage.'"><img alt="'.$LANG->getLL('sort.down').'" title="'.$LANG->getLL('sort.down').'" '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/button_down.gif','').' /></a><a href="index.php?sort=uid&way=ASC&sword='.$sword.'&page='.$actPage.'"><img alt="'.$LANG->getLL('sort.up').'" title="'.$LANG->getLL('sort.up').'" '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/button_up.gif','').' /></a></th>';
					}          
					$out .= '<th class="tableHeader">'.$LANG->getLL('th.Title').'<br /><a href="index.php?sort=fare_title&way=DESC&sword='.$sword.'&page='.$actPage.'"><img alt="'.$LANG->getLL('sort.down').'" title="'.$LANG->getLL('sort.down').'" '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/button_down.gif','').' /></a><a href="index.php?sort=fare_title&way=ASC&sword='.$sword.'&page='.$actPage.'"><img alt="'.$LANG->getLL('sort.up').'" title="'.$LANG->getLL('sort.up').'" '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/button_up.gif','').' /></a></th>';
					$out .= '<th class="tableHeader" align="left">'.$LANG->getLL('th.Desc').'</th>';
					if($this->rowConfig['show_date'] != 0) {
						$out .= '<th class="tableHeader">'.$LANG->getLL('th.Time').'<br /><a href="index.php?sort=crdate&way=DESC&sword='.$sword.'&page='.$actPage.'"><img alt="'.$LANG->getLL('sort.down').'" title="'.$LANG->getLL('sort.down').'" '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/button_down.gif','').' /></a><a href="index.php?sort=crdate&way=ASC&sword='.$sword.'&page='.$actPage.'"><img alt="'.$LANG->getLL('sort.up').'" title="'.$LANG->getLL('sort.up').'" '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/button_up.gif','').' /></a></th>';
					}					
					$out .= '<th class="tableHeader">'.$LANG->getLL('th.option').'</th>';
					$out .= '</tr>';
					return $out;
				}
				
				
				/**
				* 
				*/
				function getConfigHeader($type) {
					global $LANG;
					switch ($type) {
						case "types" :
							$title = $LANG->getLL('th.typesTitle');
							$out .= '<tr>';
							if($this->rowConfig['show_uid'] != 0) {
								$out .= '<th class="tableHeader">'.$LANG->getLL('th.ID').'</th>';
							}
							$out .= '<th class="tableHeader">'.$title.'</th>';
							if($this->rowConfig['show_root'] != 0) {
								$out .= '<th class="tableHeader">'.$LANG->getLL('th.Path').'</th>'; 
							}
							if($this->rowConfig['show_date'] != 0) {
								$out .= '<th class="tableHeader">'.$LANG->getLL('th.Time').'</th>';
							}
							$out .= '<th class="tableHeader">'.$LANG->getLL('th.option').'</th>';
							$out .= '</tr>';
						break;
						case "config":
							$title = $LANG->getLL('th.configTitle');
							$out .= '<tr>';
							$out .= '<th class="tableHeader">'.$LANG->getLL('th.ID').'</th>';
							$out .= '<th class="tableHeader">'.$title.'</th>';
							$out .= '<th class="tableHeader">'.$LANG->getLL('th.Path').'</th>'; 
							$out .= '<th class="tableHeader">'.$LANG->getLL('th.Time').'</th>';
							$out .= '<th class="tableHeader">'.$LANG->getLL('th.option').'</th>';
							$out .= '</tr>';
						break;
						case "holiday":
							$title = $LANG->getLL('th.holidayTitle');
							$out .= '<tr>';
							$out .= '<th class="tableHeader">'.$title.'</th>';
							$out .= '<th class="tableHeader">'.$LANG->getLL('th.option').'</th>';
							$out .= '</tr>';
						break;
					}
					return $out;	
				}
				
				
				
				/**
				* 
				*/
				function HideDraft($table) {
					$updateArray = array(
						'tstamp'    =>  time(),
						'hidden'    =>  1
					);
					$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, 'uid='.t3lib_div::GPvar('record'), $updateArray);
				}
				
				
				/**
				* 
				*/
				function UnHideDraft($table) {
					$updateArray = array(
						'tstamp'    =>  time(),
						'hidden'    =>  0
					);
					$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, 'uid='.t3lib_div::GPvar('record'), $updateArray);
				}
				
				
				/**
				* 
				*/
				function DeleteDraft($table) {
					$updateArray = array(
						'tstamp'    =>  time(),
						'deleted'    =>  1
					);
					$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, 'uid='.t3lib_div::GPvar('record'), $updateArray);
				}
				
				
				/**
				* 
				*/
				function getAllDrafts(){
					$res  = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'*',
						'tx_ppwlunchmenu_fare',
						'deleted=0',
						'',
						'',
						''
					);    
					return $allRecords = @$GLOBALS['TYPO3_DB']->sql_num_rows($res);
				}
				
				
				/**
				* 
				*/
				function getIsConfigured(){
					$res  = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'*',
						'tx_ppwlunchmenu_config',
						'deleted=0 AND hidden=0',
						'',
						'',
						''
					);	
					$count = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
					if ($count != 0) {
						return true;
					} else {
						return false;
					}
				}
				
			}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ppw_lunchmenu/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ppw_lunchmenu/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_ppwlunchmenu_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>                       

