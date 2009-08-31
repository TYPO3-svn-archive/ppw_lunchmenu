<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Stefan Horenkamp <horenkamp@pietzpluswild.de>
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
/**
 * Plugin 'Lunchmenusystem' for the 'ppw_lunchmenu' extension.
 *
 * @author	Stefan Horenkamp <horenkamp@pietzpluswild.de>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_ppwlunchmenu_pi1 extends tslib_pibase {
	var $prefixId = 'tx_ppwlunchmenu_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_ppwlunchmenu_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'ppw_lunchmenu';	// The extension key.
	var $pi_checkCHash = TRUE;
	
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_initPIflexForm();
		$this->actTime = mktime('9','0','0',date('m'),date('d'),date('Y'));
		$this->storagePID = $this->pi_getPidList($this->cObj->data['pages'],$this->conf["recursive"]);
		$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId] = "\n\n<script  src=\"".t3lib_extMgm::siteRelPath("ppw_lunchmenu")."res/calendar/calendar.js\" type=\"text/javascript\"></script>";
		
		$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId] .= "\n<link href=\"".t3lib_extMgm::siteRelPath("ppw_lunchmenu")."res/calendar/calendar.css\" rel=\"stylesheet\" type=\"text/css\">";
		$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId] .= "\n<link href=\"".$this->conf['cssFile']."\" rel=\"stylesheet\" type=\"text/css\">";
		
		if($this->piVars['language']) {
			$this->key = $this->piVars['language'];
			$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId] .= "\n<meta name=\"language\" content=\"".$this->piVars['language']."\" />";
		} else {
			$this->key = ($GLOBALS['TSFE']->config["config"]["language"]) ? $GLOBALS['TSFE']->config["config"]["language"] : 'default';
			$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId] .= "\n<meta name=\"language\" content=\"".$this->key."\" />";
		}
		
		$this->imagePath = t3lib_extMgm::siteRelPath("ppw_lunchmenu")."res/images/";
		
		
		$selectedOption = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'what_to_display', 'sDEF');
		switch($selectedOption) {
			case "SINGLE":
				if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'weekPID', 'sDEF')) {
                    $this->weekPID = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'weekPID', 'sDEF');
					if($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pdfPID', 'sDEF')) {
						$this->pdfPID = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pdfPID', 'sDEF');
                        $this->cal = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'cal', 'sDEF');
						$content = $this->showSingleDay();                                                           
					} else {
						$content = $this->pi_getLL('error.noPdfPID');
					}
				} else {
					$content = $this->pi_getLL('error.noWeekPID');
				}
			break;
			case "WEEK":
				if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'singlePID', 'sDEF')) {
					$this->singlePID = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'singlePID', 'sDEF');
					if($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pdfPID', 'sDEF')) {
						$this->pdfPID = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pdfPID', 'sDEF');
						$content = $this->showWeek();
					} else {
						$content = $this->pi_getLL('error.noPdfPID');
					}
				} else {
					$content = $this->pi_getLL('error.noSinglePID');
				}
			break;
			case "PDF":
				$content = $this->showPDF();
			break;
			default:
				$content = $this->pi_getLL('error.noSelectedOption');
			break;
		}
		
		return $this->pi_wrapInBaseClass($content);
	}
	
	
	/**
	* 
	*/
	function showPDF() {
		$template = $this->cObj->fileResource($this->conf['templateFile']);
		$template = $this->cObj->getSubpart($template, "###PDF_".strtoupper($this->piVars['output'])."_VIEW###");
		$this->key = $this->piVars['language'];
		$this->actTime = $this->piVars['time'];
		$weekDays = $this->getWeekDays();
		$marker['###SITENAME###'] = $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.pdfName'], $this->LOCAL_LANG_charset[$this->key]['lll.pdfName']);
		$marker['###COMPANY###'] = $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.pdfCompany'], $this->LOCAL_LANG_charset[$this->key]['lll.pdfCompany']);
		$marker['###STYLE_TABLE###'] = '';
        if(t3lib_extMgm::isLoaded("pdf_generator2")) {
			$marker['###STYLE_TABLE###'] = 'style="padding-left:20%;"';
		} 
		$marker['###DAY_MON###'] = $weekDays['mon'][0].', '.date('d.m.Y', $weekDays['mon'][2]);
		$marker['###DAY_TUE###'] = $weekDays['tue'][0].', '.date('d.m.Y', $weekDays['tue'][2]);
		$marker['###DAY_WED###'] = $weekDays['wed'][0].', '.date('d.m.Y', $weekDays['wed'][2]);
		$marker['###DAY_THU###'] = $weekDays['thu'][0].', '.date('d.m.Y', $weekDays['thu'][2]);
		$marker['###DAY_FRI###'] = $weekDays['fri'][0].', '.date('d.m.Y', $weekDays['fri'][2]);
		
		$marker['###MON_ENTRY###'] = $this->getDayEntryPDF($weekDays['mon'][2]);
		$marker['###TUE_ENTRY###'] = $this->getDayEntryPDF($weekDays['tue'][2]);
		$marker['###WED_ENTRY###'] = $this->getDayEntryPDF($weekDays['wed'][2]);
		$marker['###THU_ENTRY###'] = $this->getDayEntryPDF($weekDays['thu'][2]);
		$marker['###FRI_ENTRY###'] = $this->getDayEntryPDF($weekDays['fri'][2]);
		if(t3lib_div::GPvar('type') == '123') {
			$marker['###STYLE###'] = 'align="center"';
		} else {
			$marker['###STYLE###'] = 'style="padding-left:20%"';
		}
		
		$marker['###FOOTER###'] = $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.pdfFooter'], $this->LOCAL_LANG_charset[$this->key]['lll.pdfFooter']);
		$marker['###FOOTER###'] = str_replace('###BR###', '<br />', $marker['###FOOTER###']);
		$marker['###NOTICE###'] = $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.pdfNotice'], $this->LOCAL_LANG_charset[$this->key]['lll.pdfNotice']);
		$content = $this->cObj->substituteMarkerArrayCached($template, $marker);
		return $content;	
	}
	
	
	/**
	* 
	*/
	function getDayEntryPDF($tstamp) {
		$resClosed = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'tx_ppwlunchmenu_closed',
			'pid='.$this->storagePID.' AND date='.$tstamp,
			'',
			'',
			''
		);
		
		if ($isClosed = $GLOBALS['TYPO3_DB']->sql_num_rows($resClosed)) {
			$rowClosed = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resClosed);
			$resType = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*',
				'tx_ppwlunchmenu_closing_types',
				'uid='.$rowClosed['closing_cause'],
				'',
				'',
				''
			); 
			$rowType = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resType);
			return '<tr><td colspan="3"><strong>'.$rowType['closing_title'].'</strong></td></tr>';	
		} else {
			$resFood = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*',
				'tx_ppwlunchmenu_bill',
				'deleted=0 AND hidden=0 AND crdate='.$tstamp.' AND pid='.$this->storagePID,
				'',
				'food_price ASC',
				''
			); 
			$content = '';
			while($rowFood = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resFood)) {
				$resType = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'*',
					'tx_ppwlunchmenu_types',
					'uid='.$rowFood['type_uid'].' AND pid='.$this->storagePID,
					'',
					'',
					''
				); 
				$rowTypes = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resType);
				switch($this->key) {
                    case "de":
                        $title = $rowTypes['type_title'];
                    break;
                    case "default":
                        $title = $rowTypes['type_title'];
                        if($rowTypes['type_title_engl'] != '') {
                            $title = $rowTypes['type_title_engl'];
                        }
                    break;
                }
                $content .= '<tr><td width="120" valign="top">'.$title.'</td>';
				$content .= '<td valign="top" width="60" align="center"><strong>'.$rowFood['food_price'].'&nbsp;</strong></td>';
				if(strlen($rowFood[$this->key.'_desc']) < 1) {
					$foodText = $rowFood['de_desc'];
				} else {
					$foodText = $rowFood[$this->key.'_desc'];
				}
				$content .= '<td align="center">'.$foodText.'</td></tr>';
			}
			return $content;
		}
		
	}
	
	
	/**
	* 
	*/
	function showSingleDay() {
        
        if($this->piVars['date']) {
			$newDate = t3lib_div::trimExplode('.',$this->piVars['date']);
			$this->actTime = @mktime('9','0','0',$newDate[1],$newDate[0],$newDate[2]);
            
		} 
		if($this->piVars['prev']) {
			$this->actTime = $this->piVars['time']-86400;
		}
		if($this->piVars['forward']) {
			$this->actTime = $this->piVars['time']+86400;
		}
        
		$template = $this->cObj->fileResource($this->conf['templateFile']);
		$template = $this->cObj->getSubpart($template, "###SINGLE_VIEW###");
		#$marker['###DATE###'] = date('d.m.Y', $this->actTime);
        if($this->cal) {
            $marker['###CAL###'] = "<script type=\"text/javascript\">NewCalendar(\"".$this->prefixId."[date]\",'','".date('d.m.Y', $this->actTime)."','');</script>";
        } else {
            $marker['###CAL###'] = '<input type="text" class="date_ddmmyyyy" name="'.$this->prefixId.'[date]" value="'.date('d.m.Y', $this->actTime).'"/>';
        }
        
		$marker['###LANGUAGE-KEY###'] = '';
		$Language = '';
		foreach(array_keys($this->LOCAL_LANG) as $languageKey) {
			$languageText = $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.language_'.$languageKey], $this->LOCAL_LANG_charset[$this->key]['lll.language_'.$languageKey]);
			if($this->key == $languageKey) {
				$Language = '|&nbsp;<img title="'.$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.languageTitle'], $this->LOCAL_LANG_charset[$this->key]['lll.languageTitle']).'" alt="'.$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.languageTitle'], $this->LOCAL_LANG_charset[$this->key]['lll.languageTitle']).'"border="0" width="17" height="9" src="'.$this->imagePath.'flags/'.$languageKey.'.gif" />&nbsp;';		
                $Language = str_replace('###LANGUAGE###', $languageText, $Language);
			} else {
				$Language = '|&nbsp;<a title="'.$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.languageTitle'], $this->LOCAL_LANG_charset[$this->key]['lll.languageTitle']).'" href="'.$this->pi_linkTP_keepPIvars_url($overrulePIvars=array('language' => $languageKey),$cache=1,$clearAnyway=0).'"><img title="'.$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.languageTitle'], $this->LOCAL_LANG_charset[$this->key]['lll.languageTitle']).'" alt="'.$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.languageTitle'], $this->LOCAL_LANG_charset[$this->key]['lll.languageTitle']).'" src="'.$this->imagePath.'flags/'.$languageKey.'.gif" border="0" width="17" height="9"/></a>&nbsp;';
				$Language = str_replace('###LANGUAGE###', $languageText, $Language);
			}
			$marker['###LANGUAGE-KEY###'] .= $Language;
		}
		$resTypes = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'tx_ppwlunchmenu_types',
			'deleted=0 AND hidden=0 AND pid='.$this->storagePID,
			'',
			'sorting DESC',
			''
		);
		
		$resClosed = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'closing_cause',
			'tx_ppwlunchmenu_closed',
			'pid='.$this->storagePID.' AND date='.$this->actTime,
			'',
			'',
			''
		);
		
		if ($isClosed = @$GLOBALS['TYPO3_DB']->sql_num_rows($resClosed)) {
		    list($closingID) = @$GLOBALS['TYPO3_DB']->sql_fetch_row($resClosed);
            $resCause = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			    'closing_title',
			    'tx_ppwlunchmenu_closing_types',
			    'uid='.$closingID,
			    '',
			    '',
			    ''
		    );
            list($closingTitle) = @$GLOBALS['TYPO3_DB']->sql_fetch_row($resCause);
            $marker['###ENTRY###'] = '<tr><th class="ppw-speiseplan">'.$closingTitle.'</th></tr>';
		} else {
			$types = '';   
			$food = '';
			while($rowTypes = @$GLOBALS['TYPO3_DB']->sql_fetch_assoc($resTypes)) {
				switch($this->key) {
                    case "de":
                        $title = $rowTypes['type_title'];
                    break;
                    case "default":
                        $title = $rowTypes['type_title'];
                        if($rowTypes['type_title_engl'] != '') {
                            $title = $rowTypes['type_title_engl'];
                        }
                    break;
                }
                $starImage = '<img src="'.$this->imagePath.'mue_'.$rowTypes['stars'].'.gif" width="84" height="31" alt="" />';
				
				$types .= '<th class="ppw-speiseplan">'.$GLOBALS['TSFE']->csConv($title).'</th>';
				$resFood = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'*',
					'tx_ppwlunchmenu_bill',
					'deleted=0 AND hidden=0 AND type_uid='.$rowTypes['uid'].' AND crdate='.$this->actTime.' AND pid='.$this->storagePID,
					'',
					'',
					''
				);
				$rowFood = @$GLOBALS['TYPO3_DB']->sql_fetch_assoc($resFood);
				if(is_array($rowFood)) {
					$foodImage = $this->getImageFromDraft($rowFood['draft_uid']);
					$foodPrice = $GLOBALS['TSFE']->csConv($rowFood['food_price']);      
					if(strlen($rowFood[$this->key.'_desc']) != 0) {
						$foodText = strip_tags($rowFood[$this->key.'_desc'],"<br>");	
					} else {
						$foodText = strip_tags($rowFood['de_desc'],"<br>");	
					}
					$food .= '<td class="ppw-speiseplan">'.$starImage.$foodImage.'<div class="ppw-speiseplan-text">'.$foodText.'</div><strong>'.$foodPrice.'&nbsp;</strong></td>';
				} else {
					$starImage = '<img src="'.$this->imagePath.'mue_0.gif" width="84" height="31" alt="" />';
					$food .= '<td class="ppw-speiseplan"><div class="ppw-speiseplan-text">'.$starImage.'<br />'.$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.noEntry'], $this->LOCAL_LANG_charset[$this->key]['lll.noEntry']).'</div></td>';   
				}
			}
			$marker['###ENTRY###'] = '<tr>'.$types.'</tr><tr>'.$food.'</tr>';
		}
		
		/*
		$marker['###LLL:PAGEHEADER###'] = $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.beginPageHeader'], $this->LOCAL_LANG_charset[$this->key]['lll.beginPageHeader']);		 
		$marker['###LLL:PAGEHEADER###'] .= $this->translateDay(date('D',$this->actTime));
		$marker['###LLL:PAGEHEADER###'] .= date(', d.', $this->actTime);
		$marker['###LLL:PAGEHEADER###'] .= $this->translateMonth(date('m',$this->actTime));
		$marker['###LLL:PAGEHEADER###'] .= date(' Y', $this->actTime);
		$marker['###LLL:SELECT_DATE###'] = $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.selectDate'], $this->LOCAL_LANG_charset[$this->key]['lll.selectDate']);
		*/
		$marker['###PREFIX###'] = $this->prefixId;
		$marker['###ACTION###'] = $this->pi_linkTP_keepPIvars_url($overrulePIvars=array('language' => $this->key),$cache=0,$clearAnyway=1);
		
		$marker['###LLL:SUBMIT###'] = $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.submitDate'], $this->LOCAL_LANG_charset[$this->key]['lll.submitDate']);
		$marker['###LLL:SUBMIT_TITLE###'] = $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.submitDateTitle'], $this->LOCAL_LANG_charset[$this->key]['lll.submitDateTitle']);
		$marker['###IMAGE:PATH###'] = $this->imagePath;
		$overrulePIvars=array(
			'prev'      => 1,
			'time'      => $this->actTime,
			'language'  => $this->key
		);
		$prevLink = $this->pi_linkTP_keepPIvars($GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.prevDay'], $this->LOCAL_LANG_charset[$this->key]['lll.prevDay']),$overrulePIvars,$cache=1,$clearAnyway=1);
		$linkParts = split("href", $prevLink);
		unset($prevLink);
		$prevLink = $linkParts[0]."id=\"ppw-link\" title=\"".$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.prevDayTitle'], $this->LOCAL_LANG_charset[$this->key]['lll.prevDayTitle'])."\" href".$linkParts[1];
		$marker['###PREV_LINK###'] = $prevLink;
		$overrulePIvars=array(
			'forward'   => 1,
			'time'      => $this->actTime,
			'language'  => $this->key
		);
		$forwardLink = $this->pi_linkTP_keepPIvars($GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.forwardDay'], $this->LOCAL_LANG_charset[$this->key]['lll.forwardDay']),$overrulePIvars,$cache=1,$clearAnyway=1);
		$linkParts = split("href", $forwardLink);
		unset($forwardLink);
		$forwardLink = $linkParts[0]."id=\"ppw-link\" title=\"".$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.forwardDayTitle'], $this->LOCAL_LANG_charset[$this->key]['lll.forwardDayTitle'])."\" href".$linkParts[1];
		$marker['###FORWARD_LINK###'] = $forwardLink;
		
		$link = $this->pi_linkToPage($GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.weekLink'], $this->LOCAL_LANG_charset[$this->key]['lll.weekLink']),$this->weekPID,$target='_self',$urlParameters=array());
		$linkParts = split("href", $link);
		unset($link);
        $weekDays = $this->getWeekDays();
		$weekTitel = $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.weekPageheader'], $this->LOCAL_LANG_charset[$this->key]['lll.weekPageheader']);
		$weekTitel = str_replace('###MONDAY###', date('d.', $weekDays['mon'][2]).$this->translateMonth(date('m',$weekDays['mon'][2])).' '.date('Y', $weekDays['mon'][2]), $weekTitel);
		
        $weekTitel = str_replace('###SUNDAY###', date('d.', $weekDays['fri'][2]).$this->translateMonth(date('m',$weekDays['fri'][2])).' '.date('Y', $weekDays['fri'][2]), $weekTitel);
		
        $weekLink = $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.weekLinkTitle'], $this->LOCAL_LANG_charset[$this->key]['lll.weekLinkTitle']);
		$weekLink = str_replace('###ACT_WEEK###', $weekTitel, $weekLink);
        
        $link = $linkParts[0]."id=\"ppw-link\" title=\"".$weekLink."\" href".$linkParts[1];
		$marker['###WEEK_LINK###'] = $link;
		
		
		if(t3lib_extMgm::isLoaded("pdf_generator2")) {
			$PDFlink = '<a id="ppw-link" href="index.php?id='.$this->pdfPID.'&type=123&tx_ppwlunchmenu_pi1[time]='.$this->actTime.'&tx_ppwlunchmenu_pi1[language]='.$this->key.'&tx_ppwlunchmenu_pi1[output]=week" target="_blank" title="'.$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.PdfLinkTitle'], $this->LOCAL_LANG_charset[$this->key]['lll.PdfLinkTitle']).'"><img src="typo3/gfx/fileicons/pdf.gif" width="17" height="15" border="0" alt="'.$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.PdfLink'], $this->LOCAL_LANG_charset[$this->key]['lll.PdfLink']).'" title="'.$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.PdfLinkTitle'], $this->LOCAL_LANG_charset[$this->key]['lll.PdfLinkTitle']).'"/></a>';
		} else {
			$PDFlink = '<a id="ppw-link" href="index.php?id='.$this->pdfPID.'&tx_ppwlunchmenu_pi1[time]='.$this->actTime.'&tx_ppwlunchmenu_pi1[language]='.$this->key.'&tx_ppwlunchmenu_pi1[output]=week" target="_blank" title="'.$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.PdfLinkTitle'], $this->LOCAL_LANG_charset[$this->key]['lll.PdfLinkTitle']).'"><img src="typo3/gfx/fileicons/pdf.gif" border="0" width="17" height="15" alt="'.$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.PdfLink'], $this->LOCAL_LANG_charset[$this->key]['lll.PdfLink']).'" title="'.$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.PdfLinkTitle'], $this->LOCAL_LANG_charset[$this->key]['lll.PdfLinkTitle']).'" /></a>';
		}
		$marker['###PDF_LINK###'] = $PDFlink;
		$content = $this->cObj->substituteMarkerArrayCached($template, $marker);
		return $content;
	}
	
	
	function showWeek(){
		if($this->piVars['prev']) {
			$this->actTime = $this->piVars['time']-604800;
		}
		if($this->piVars['forward']) {
			$this->actTime = $this->piVars['time']+604800;
		}
		$weekDays = $this->getWeekDays();
		
		$crdateArray = array(
			'mon'   => $weekDays['mon'][2], 
			'tue'   => $weekDays['tue'][2], 
			'wed'   => $weekDays['wed'][2], 
			'thu'   => $weekDays['thu'][2], 
			'fri'   => $weekDays['fri'][2]
		);
		
		$template = $this->cObj->fileResource($this->conf['templateFile']);
		$template = $this->cObj->getSubpart($template, "###WEEK_VIEW###");
		$marker['###LLL:PAGEHEADER###'] = $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.weekPageheader'], $this->LOCAL_LANG_charset[$this->key]['lll.weekPageheader']);
		$marker['###LLL:PAGEHEADER###'] = str_replace('###MONDAY###', date('d.', $weekDays['mon'][2]).$this->translateMonth(date('m',$weekDays['mon'][2])).' '.date('Y', $weekDays['mon'][2]), $marker['###LLL:PAGEHEADER###']);
		$marker['###LLL:PAGEHEADER###'] = str_replace('###SUNDAY###', date('d.', $weekDays['fri'][2]).$this->translateMonth(date('m',$weekDays['fri'][2])).' '.date('Y', $weekDays['fri'][2]), $marker['###LLL:PAGEHEADER###']);
		
		$marker['###LANGUAGE-KEY###'] = '';
		$Language = '';
		foreach(array_keys($this->LOCAL_LANG) as $languageKey) {
			$languageText = $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.language_'.$languageKey], $this->LOCAL_LANG_charset[$this->key]['lll.language_'.$languageKey]);
			if($this->key == $languageKey) {
				$Language = '|&nbsp;<img title="'.$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.languageTitle'], $this->LOCAL_LANG_charset[$this->key]['lll.languageTitle']).'" alt="'.$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.languageTitle'], $this->LOCAL_LANG_charset[$this->key]['lll.languageTitle']).'"border="0" width="17" height="9" src="'.$this->imagePath.'flags/'.$languageKey.'.gif" />&nbsp;';		
                $Language = str_replace('###LANGUAGE###', $languageText, $Language);
			} else {
				$Language = '|&nbsp;<a title="'.$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.languageTitle'], $this->LOCAL_LANG_charset[$this->key]['lll.languageTitle']).'" href="'.$this->pi_linkTP_keepPIvars_url($overrulePIvars=array('language' => $languageKey),$cache=1,$clearAnyway=0).'"><img title="'.$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.languageTitle'], $this->LOCAL_LANG_charset[$this->key]['lll.languageTitle']).'" alt="'.$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.languageTitle'], $this->LOCAL_LANG_charset[$this->key]['lll.languageTitle']).'" src="'.$this->imagePath.'flags/'.$languageKey.'.gif" border="0" width="17" height="9"/></a>&nbsp;';
				$Language = str_replace('###LANGUAGE###', $languageText, $Language);
			}
			$marker['###LANGUAGE-KEY###'] .= $Language;
		}
		$days = '<th class="ppw-speiseplan-woche">&nbsp;</th>';
		foreach($weekDays as $key => $value) {
			$days .= '<th class="ppw-speiseplan-woche">'.$weekDays[$key][0].'</th>';
			$closing[$key];
			$cause[$key];
			$closedDays = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*',
				'tx_ppwlunchmenu_closed',
				'pid='.$this->storagePID.' AND date='.$value[2],
				'',
				'',
				''
			  );
			$foundDay = $GLOBALS['TYPO3_DB']->sql_num_rows($closedDays);
			if ($foundDay != 0) {
				$rowClosing = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($closedDays);
				$closing[$key] = $value[2];
				$cause[$key] = $rowClosing['closing_cause'];
			}	
		}
        
		$marker['###ENTRY###'] .= '<tr>'.$days.'</tr>';

		$resTypes = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'tx_ppwlunchmenu_types',
			'deleted=0 AND hidden=0 AND pid='.$this->storagePID,
			'',
			'sorting DESC',
			''
		);
		$countTypes = $GLOBALS['TYPO3_DB']->sql_num_rows($resTypes);
		while ($rowTypes = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resTypes)) {
			$starImage = '<img src="'.$this->imagePath.'mue_'.$rowTypes['stars'].'.gif" width="84" height="31" alt="" />';
			switch($this->key) {
                case "de":
                    $title = $rowTypes['type_title'];
                break;
                case "default":
                    $title = $rowTypes['type_title'];
                    if($rowTypes['type_title_engl'] != '') {
                        $title = $rowTypes['type_title_engl'];
                    }
                break;
            }
            
            $out .= '<tr>';
			$out .= '<td class="ppw-speiseplan-woche-menue">'.$starImage.'<br />'.$GLOBALS['TSFE']->csConv($title).'</td>';
			foreach ($crdateArray as $key => $crdate) {
				$resBill = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'*',
					'tx_ppwlunchmenu_bill',
					'deleted=0 AND type_uid='.$rowTypes['uid'].' AND crdate='.$crdate.' AND pid='.$this->storagePID,
					'',
					'',
					''
				);
				$numRows = $GLOBALS['TYPO3_DB']->sql_num_rows($resBill);
				if ($numRows > 0) {
					$rowBill = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resBill);
					if(strlen($rowBill[$this->key.'_desc']) != 0) {
						$out .= '<td class="ppw-speiseplan-woche">';
						$foodImage = $this->getImageFromDraft($rowBill['draft_uid']);
						#$foodPrice = str_replace(chr(128),"€",$rowBill['food_price']);      
						$foodPrice = $GLOBALS['TSFE']->csConv($rowBill['food_price']);      
						$out .= $foodImage.'<div class="ppw-speiseplan-text-woche">'.strip_tags($rowBill[$this->key.'_desc'],"<br>").'</div><strong>'.$foodPrice.'&nbsp;</strong>';	
						$out .= '</td>';	
					} else {
						$out .= '<td class="ppw-speiseplan-woche">';
						$foodImage = $this->getImageFromDraft($rowBill['draft_uid']);
						#$foodPrice = str_replace(chr(128),"€",$rowBill['food_price']);      
						$foodPrice = $GLOBALS['TSFE']->csConv($rowBill['food_price']);      
						$out .= $foodImage.'<div class="ppw-speiseplan-text-woche">'.strip_tags($rowBill['de_desc'],"<br>").'</div><strong>'.$foodPrice.'&nbsp;</strong>';	
						$out .= '</td>';	
					}
				} elseif (@in_array($crdate, $closing)) {
					$resClosingType = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'closing_title',
						'tx_ppwlunchmenu_closing_types',
						'uid='.$cause[$key],
						'',
						'',
						''
					); 
					$rowClosingType = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resClosingType);
					if(is_array($test[$key][$rowClosingType['closing_title']])) {
						$out .= '';
					} else {
						$test[$key][$rowClosingType['closing_title']] = array($crdate);
						$out .= '<td class="ppw-speiseplan-woche" rowspan="'.$countTypes.'"><div class="ppw-speiseplan-text-woche"><strong>'.$rowClosingType['closing_title'].'</strong></div></td>';
					}
				} else {
					$out .= '<td class="ppw-speiseplan-woche"><div class="ppw-speiseplan-text-woche">'.$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.noEntry'], $this->LOCAL_LANG_charset[$this->key]['lll.noEntry']).'</div></td>'; 
				}
			}
			$out .= '</tr>';
		}     
		$marker['###ENTRY###'] .= $out;
		$marker['###LLL:WEEK###'] = $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.Week'], $this->LOCAL_LANG_charset[$this->key]['lll.Week']);
		
		
		$overrulePIvars=array(
			'prev'      => 1,
			'time'      => $this->actTime,
			'language'  => $this->key
		);
		$prevLink = $this->pi_linkTP_keepPIvars($GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.prevWeek'], $this->LOCAL_LANG_charset[$this->key]['lll.prevDay']),$overrulePIvars,$cache=1,$clearAnyway=1);
		$linkParts = split("href", $prevLink);
		unset($prevLink);
		$prevLink = $linkParts[0]."id=\"ppw-link\" title=\"".$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.prevWeekTitle'], $this->LOCAL_LANG_charset[$this->key]['lll.prevDayTitle'])."\" href".$linkParts[1];
		$marker['###PREV_LINK###'] = $prevLink;
		$overrulePIvars=array(
			'forward'   => 1,
			'time'      => $this->actTime,
			'language'  => $this->key
		);
		$forwardLink = $this->pi_linkTP_keepPIvars($GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.forwardWeek'], $this->LOCAL_LANG_charset[$this->key]['lll.forwardDay']),$overrulePIvars,$cache=1,$clearAnyway=1);
		$linkParts = split("href", $forwardLink);
		unset($forwardLink);
		$forwardLink = $linkParts[0]."id=\"ppw-link\" title=\"".$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.forwardWeekTitle'], $this->LOCAL_LANG_charset[$this->key]['lll.forwardDayTitle'])."\" href".$linkParts[1];
		$marker['###FORWARD_LINK###'] = $forwardLink;
		$link = $this->pi_linkToPage($GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.singleLink'], $this->LOCAL_LANG_charset[$this->key]['lll.weekLink']),$this->singlePID,$target='_self',$urlParameters=array());
		$linkParts = split("href", $link);
		unset($link);
        $weekTitle = $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.singleLinkTitle'], $this->LOCAL_LANG_charset[$this->key]['lll.weekLinkTitle']);
        $weekTitle = str_replace('###ACT_DAY###', date('d.m.Y'), $weekTitle);
		$link = $linkParts[0]."id=\"ppw-link\" title=\"".$weekTitle."\" href".$linkParts[1];
		$marker['###WEEK_LINK###'] = $link;
		
		
        
        
		if(t3lib_extMgm::isLoaded("pdf_generator2")) {
			$PDFlink = '<a id="ppw-link" href="index.php?id='.$this->pdfPID.'&type=123&tx_ppwlunchmenu_pi1[time]='.$this->actTime.'&tx_ppwlunchmenu_pi1[language]='.$this->key.'&tx_ppwlunchmenu_pi1[output]=week" target="_blank" title="'.$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.PdfLinkTitle'], $this->LOCAL_LANG_charset[$this->key]['lll.PdfLinkTitle']).'"><img src="typo3/gfx/fileicons/pdf.gif" width="17" height="15" border="0" alt="'.$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.PdfLink'], $this->LOCAL_LANG_charset[$this->key]['lll.PdfLink']).'" title="'.$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.PdfLinkTitle'], $this->LOCAL_LANG_charset[$this->key]['lll.PdfLinkTitle']).'"/></a>';
		} else {
			$PDFlink = '<a id="ppw-link" href="index.php?id='.$this->pdfPID.'&tx_ppwlunchmenu_pi1[time]='.$this->actTime.'&tx_ppwlunchmenu_pi1[language]='.$this->key.'&tx_ppwlunchmenu_pi1[output]=week" target="_blank" title="'.$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.PdfLinkTitle'], $this->LOCAL_LANG_charset[$this->key]['lll.PdfLinkTitle']).'"><img src="typo3/gfx/fileicons/pdf.gif" border="0" width="17" height="15" alt="'.$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.PdfLink'], $this->LOCAL_LANG_charset[$this->key]['lll.PdfLink']).'" title="'.$GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['lll.PdfLinkTitle'], $this->LOCAL_LANG_charset[$this->key]['lll.PdfLinkTitle']).'" /></a>';
		}
		
		
		$marker['###PDF_LINK###'] = $PDFlink;
		
		$content = $this->cObj->substituteMarkerArrayCached($template, $marker);
		return $content;	
	}
	
	
	function getImageFromDraft($draftUID) {
		$imageFile = '';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'tx_ppwlunchmenu_fare',
			'deleted=0 AND hidden=0 AND uid='.$draftUID,
			'',
			'',
			''
		);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		if(is_file($this->conf['imagePath'].$row['fare_image'])) {
			$imgTSConfig = $this->conf['thumbImage.'];
			$imgTSConfig['file'] = $this->conf['imagePath'].$row['fare_image'];
			$imgTSConfig['altText'] = $row['fare_image'];
			$imgTSConfig['titleText'] = $row['fare_title'];
			$imgTSConfig['params'] = 'class="ppw-foodImage"';
			$imageFile = '<br />'.$this->cObj->IMAGE($imgTSConfig);       
		}
		return $imageFile;
	}
	
	
	/**
	* 
	*/
	function translateDay($day) {
		switch($day) {
			case "Mon":
				return $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['day.Monday'], $this->LOCAL_LANG_charset[$this->key]['day.Monday']);
			break;
			case "Tue":
				return $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['day.Tuesday'], $this->LOCAL_LANG_charset[$this->key]['day.Tuesday']);
			break;
			case "Wed":
				return $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['day.Wednesday'], $this->LOCAL_LANG_charset[$this->key]['day.Wednesday']);
			break;
			case "Thu":
				return $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['day.Thursday'], $this->LOCAL_LANG_charset[$this->key]['day.Thursday']);
			break;
			case "Fri":
				return $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['day.Friday'], $this->LOCAL_LANG_charset[$this->key]['day.Friday']);
			break;
			case "Sat":
				return $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['day.Saturday'], $this->LOCAL_LANG_charset[$this->key]['day.Saturday']);
			break;
			case "Sun":
				return $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['day.Sunday'], $this->LOCAL_LANG_charset[$this->key]['day.Sunday']);
			break;
		}
	}
	
	
	/**
	* 
	*/
	function translateMonth($month) {
		switch($month) {
			case "01":
				return $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['month.Jan'], $this->LOCAL_LANG_charset[$this->key]['month.Jan']);
			break;
			case "02":
				return $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['month.Feb'], $this->LOCAL_LANG_charset[$this->key]['month.Feb']);
			break;
			case "03":
				return $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['month.Mar'], $this->LOCAL_LANG_charset[$this->key]['month.Mar']);
			break;
			case "04":
				return $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['month.Apr'], $this->LOCAL_LANG_charset[$this->key]['month.Apr']);
			break;
			case "05":
				return $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['month.May'], $this->LOCAL_LANG_charset[$this->key]['month.May']);
			break;
			case "06":
				return $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['month.Jun'], $this->LOCAL_LANG_charset[$this->key]['month.Jun']);
			break;
			case "07":
				return $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['month.Jul'], $this->LOCAL_LANG_charset[$this->key]['month.Jul']);
			break;
			case "08":
				return $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['month.Aug'], $this->LOCAL_LANG_charset[$this->key]['month.Aug']);
			break;
			case "09":
				return $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['month.Sep'], $this->LOCAL_LANG_charset[$this->key]['month.Sep']);
			break;
			case "10":
				return $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['month.Oct'], $this->LOCAL_LANG_charset[$this->key]['month.Oct']);
			break;
			case "11":
				return $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['month.Nov'], $this->LOCAL_LANG_charset[$this->key]['month.Nov']);
			break;
			case "12":
				return $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->key]['month.Dec'], $this->LOCAL_LANG_charset[$this->key]['month.Dec']);
			break;
		}
	}
	
	
	function getWeekDays() {
		$weekDays = array();
		$year = date('y', strtotime(date('Y-m-d 9:00', $this->actTime)));
		$month = date('m', strtotime(date('Y-m-d 9:00', $this->actTime)));
		$day = date('d', strtotime(date('Y-m-d 9:00', $this->actTime)));
		switch (date('D', $this->actTime)) {
			case "Mon":
				$add = 6;
				$sub = 0;
			break;
			case "Tue":
				$add = 5;
				$sub = 1;
			break;
			case "Wed":
				$add = 4;
				$sub = 2;
			break;
			case "Thu":
				$add = 3;
				$sub = 3;
			break;
			case "Fri":
				$add = 2;
				$sub = 4;
			break;
			case "Sat":
				$add = 1;
				$sub = 5;
			break;                                                                                                                                                                                                                                                          
			case "Sun":
				$add = 0;
				$sub = 6;
			break;
		}
																				 
		$weekDays['mon'][0] = $this->translateDay(date('D', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - $sub, $year)))));
		$weekDays['mon'][1] = date('d.m.y', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - $sub, $year))));
		$weekDays['mon'][2] = strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - $sub, $year)));
		  
		$weekDays['tue'][0] = $this->translateDay(date('D', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-1), $year)))));
		$weekDays['tue'][1] = date('d.m.y', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-1), $year))));
		$weekDays['tue'][2] = strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-1), $year)));
		  
		$weekDays['wed'][0] = $this->translateDay(date('D', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-2), $year)))));
		$weekDays['wed'][1] = date('d.m.y', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-2), $year))));
		$weekDays['wed'][2] = strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-2), $year)));
		  
		$weekDays['thu'][0] = $this->translateDay(date('D', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-3), $year)))));
		$weekDays['thu'][1] = date('d.m.y', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-3), $year))));
		$weekDays['thu'][2] = strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-3), $year)));
		  
		$weekDays['fri'][0] = $this->translateDay(date('D', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-4), $year)))));
		$weekDays['fri'][1] = date('d.m.y', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-4), $year))));
		$weekDays['fri'][2] = strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-4), $year)));
		 
		/* 
		$weekDays['sat'][0] = $this->translateDay(date('D', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-5), $year)))));
		$weekDays['sat'][1] = date('d.m.y', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-5), $year))));
		$weekDays['sat'][2] = strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-5), $year)));
		  
		$weekDays['sun'][0] = $this->translateDay(date('D', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day + $add, $year)))));
		$weekDays['sun'][1] = date('d.m.y', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day + $add, $year))));
		$weekDays['sun'][2] = strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day + $add, $year)));
		*/
		return $weekDays;
	  } 
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ppw_lunchmenu/pi1/class.tx_ppwlunchmenu_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ppw_lunchmenu/pi1/class.tx_ppwlunchmenu_pi1.php']);
}

?>
