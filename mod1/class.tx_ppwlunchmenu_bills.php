<?php
  require_once(PATH_t3lib.'class.t3lib_tcemain.php');
  
  require_once(PATH_t3lib.'class.t3lib_tceforms.php');
  
  #require_once('../../../../typo3/sysext/rtehtmlarea/pi2/class.tx_rtehtmlarea_pi2.php');
  #require_once('./index.php');
  
  class tx_ppwlunchmenu_bills {
	  var $cruser_id;
	  var $maxEntries = 10;
	  var $insertID;
	  var $tceforms;
	  var $rowConfig;
	  
	  
	  
	  /**
	  * 
	  */
	  function main($PID, $time, $cruser_id, $rowConfig) {
		$this->rowConfig = $rowConfig;
		$this->init();
		$this->cruser_id = $cruser_id;
		$content = $this->getBillsOfFood($PID, $time);
		if(t3lib_div::GPvar('action') == 'editBill') {
			$content = $this->editDayEntry();
		} elseif (t3lib_div::GPvar('action') == 'addBill') {
			$content = $this->createDayEntry($PID);
		} elseif (t3lib_div::GPvar('action') == 'showForm') {
			$content = $this->getCreateForm($PID);
		}
		return $content;
		 
	  }
	  
	  
	  
	  /**
	  * 
	  */
	  function getLL($key) {
		return $GLOBALS['LANG']->getLL($key);
	  }
	  
	  /**
	  * 
	  */
	  function init() {
		$this->tceforms = t3lib_div::makeInstance('t3lib_TCEforms');
		$this->tceforms->initDefaultBEMode();
		$this->tceforms->doSaveFieldName = 'doSave';
		$this->tceforms->localizationMode = t3lib_div::inList('text,media',$this->localizationMode) ? $this->localizationMode : '';	// text,media is keywords defined in TYPO3 Core API..., see "l10n_cat"
		$this->tceforms->returnUrl = $this->R_URI;
		$this->tceforms->palettesCollapsed = !$this->MOD_SETTINGS['showPalettes'];
		$this->tceforms->disableRTE = $this->MOD_SETTINGS['disableRTE'];
		$this->tceforms->enableClickMenu = TRUE;
		$this->tceforms->enableTabMenu = TRUE;
		$GLOBALS['LANG']->includeLLFile('EXT:ppw_lunchmenu/mod1/locallang.xml');
	  }
	  
	  
	  /**
	  * 
	  */
	  function getBillsOfFood($PID, $time) {
		  
		  if(t3lib_div::GPvar('SelectDate1')) {
			$newDate = t3lib_div::trimExplode('.', t3lib_div::GPvar('SelectDate1'));
			$time = mktime('9','0','0',$newDate[1],$newDate[0],$newDate[2]);
		  }
		  
		  
		  $days = $this->getWeekDays($time);
		  if (t3lib_div::GPvar('saveClosed')) {
			$this->updateClosingDays($days, $PID);
          }
          if (t3lib_div::GPvar('action')) {
			  $table = 'tx_ppwlunchmenu_bill';
			  switch (t3lib_div::GPvar('action')) {
				case "deleteBill":		
					$this->DeleteEntry($table);
				break;
				case "hideBill":		
					$this->HideEntry($table);
				break;
				case "unhideBill":		
					$this->UnHideEntry($table);
				break;
			  }
		  }
		  
		  if(t3lib_div::GPvar('week')) {
			$param = '&week='.t3lib_div::GPvar('week').'&time='.$time;	
		  } else {
			$param = '&time='.$time;
		  }
		  
		  
          foreach ($days as $key => $value) {
            $checked[$key] = '';
			$closing[$key];
			$cause[$key];
			$closedDays = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*',
				'tx_ppwlunchmenu_closed',
				'date='.$value[2],
				'',
				'',
				''
			  );
			  $foundDay = $GLOBALS['TYPO3_DB']->sql_num_rows($closedDays);
			  if ($foundDay != 0) {
				$closing[$key] = $value[2];
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($closedDays);
				$cause[$key] = $row['closing_cause'];
			  }
		  }
		  
		  $crdateArray = array(
			'mon'   => $days['mon'][2], 
			'tue'   => $days['tue'][2], 
			'wed'   => $days['wed'][2], 
			'thu'   => $days['thu'][2], 
			'fri'   => $days['fri'][2]
		  );                                                                                   
		  $out = '';
		  $out .= '<script type="text/javascript" src="/typo3conf/ext/ppw_lunchmenu/res/functions.js"></script>';
          $out .= '<script type="text/javascript" src="/typo3conf/ext/ppw_lunchmenu/res/calendar/calendar.js"></script>';
		  $out .= '<link type="text/css" href="/typo3conf/ext/ppw_lunchmenu/res/calendar/calendar.css" rel="stylesheet">';
		  $out .= '<br /><form><label id="SelectDate">'.$this->getLL('bill.selectDate').'</label>&nbsp;<script type="text/javascript">NewCalendar("SelectDate1","","'.date('d.m.Y',$time).'","");</script><input type="submit" value="'.$this->getLL('bill.selectDateSubmit').'"/></form><br /><br />';
		  $out .= '<form method="post" name="billOverview">';
          $out .= '<table cellspacing="2" cellpadding="2" border="1" bordercolor="#fff" width="850">';
		  $out .= '<tr>';
		  
		  
		  $out .= '<th class="tableHeader" width="100">'.$this->getLL('bill.TH').'</th>';
		  $out .= '<th class="tableHeader" style="font-weight:normal;" width="100">'.$days['mon'][0].'<br />'.$days['mon'][1].'<br />';
		  $out .= '</th>';
		  
		  $out .= '<th class="tableHeader" style="font-weight:normal;" width="100">'.$days['tue'][0].'<br />'.$days['tue'][1].'<br />';
		  $out .= '</th>';
		  
		  $out .= '<th class="tableHeader" style="font-weight:normal;" width="100">'.$days['wed'][0].'<br />'.$days['wed'][1].'<br />';
		  $out .= '</th>';
		  
		  $out .= '<th class="tableHeader" style="font-weight:normal;" width="100">'.$days['thu'][0].'<br />'.$days['thu'][1].'<br />';
		  $out .= '</th>';
		  
		  $out .= '<th class="tableHeader" style="font-weight:normal;" width="100">'.$days['fri'][0].'<br />'.$days['fri'][1].'<br />';
		  $out .= '</th>';
		  $out .= '</tr>';
		  $out .= '<tr>';
		  
	  
		  $out .= '<th class="tableHeader" width="100">'.$this->getLL('bill.TH2').'</th>';
		  $out .= '<th class="tableHeader" style="font-weight:normal;" width="100"><select style="width:80px;" size="1" name="holiday[mon]"><option value="0">'.$this->getLL('bill.pleaseSelect').'</option>'.$this->getClosingTypes($cause['mon']).'</select>'; 
		  $out .= '<input type="hidden" name="closed[mon]" value="'.$days['mon'][2].'" />';
		  $out .= '</th>';
		  
		  $out .= '<th class="tableHeader" style="font-weight:normal;" width="100"><select style="width:80px;" size="1" name="holiday[tue]"><option value="0">'.$this->getLL('bill.pleaseSelect').'</option>'.$this->getClosingTypes($cause['tue']).'</select>'; 
		  $out .= '<input type="hidden" name="closed[tue]" value="'.$days['tue'][2].'" />';
		  $out .= '</th>';
		  
		  $out .= '<th class="tableHeader" style="font-weight:normal;" width="100"><select style="width:80px;" size="1" name="holiday[wed]"><option value="0">'.$this->getLL('bill.pleaseSelect').'</option>'.$this->getClosingTypes($cause['wed']).'</select>'; 
		  $out .= '<input type="hidden" name="closed[wed]" value="'.$days['wed'][2].'" />';
		  $out .= '</th>';
		  
		  $out .= '<th class="tableHeader" style="font-weight:normal;" width="100"><select style="width:80px;" size="1" name="holiday[thu]"><option value="0">'.$this->getLL('bill.pleaseSelect').'</option>'.$this->getClosingTypes($cause['thu']).'</select>'; 
		  $out .= '<input type="hidden" name="closed[thu]" value="'.$days['thu'][2].'" />';
		  $out .= '</th>';
		  
		  $out .= '<th class="tableHeader" style="font-weight:normal;" width="100"><select style="width:80px;" size="1" name="holiday[fri]"><option value="0">'.$this->getLL('bill.pleaseSelect').'</option>'.$this->getClosingTypes($cause['fri']).'</select>'; 
		  $out .= '<input type="hidden" name="closed[fri]" value="'.$days['fri'][2].'" />';
		  $out .= '</th>';
		  
		  $out .= '</tr>';
		  $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'tx_ppwlunchmenu_types',
			'deleted=0 AND hidden=0 AND pid='.$PID,
			'',
			'sorting DESC',
			''
		  );
		  while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$out .= '<tr>';
			$out .= '<td class="tableHeader">'.$row['type_title'].'</td>';
			foreach ($crdateArray as $key => $crdate) {
				
				$resBill = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'*',
					'tx_ppwlunchmenu_bill',
					'deleted=0 AND type_uid='.$row['uid'].' AND crdate='.$crdate.' AND pid='.$PID,
					'',
					'',
					''
				);
				$numRows = $GLOBALS['TYPO3_DB']->sql_num_rows($resBill);
				if ($numRows > 0) {
					$rowBill = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resBill);
					$out .= '<td align="center">';
					$out .= '<p class="bodytext">'.$rowBill['draft_title'].'</p><br />';
					$out .= '<img id="information_'.$row['uid'].'" onmouseover="document.getElementById(\'information_'.$row['uid'].'\').style.cursor= \'help\'" '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/helpbubble.gif','').' title="'.$this->getLL('bill.foodPrice').$rowBill['food_price'].' '.$this->getLL('bill.foodDesc').strip_tags($rowBill['de_desc']).'" />';
					$out .= '<a title="'.$this->getLL('bill.editTitle').'" href="index.php?action=editBill&uid='.$rowBill['uid'].$param.'"><img '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/edit2.gif','').' alt="'.$this->getLL('bill.editTitle').'"/></a>';
					
					if ($rowBill['hidden'] != 0) {
						$out .= '<a title="'.$this->getLL('bill.unhideTitle').'" href="index.php?action=unhideBill&uid='.$rowBill['uid'].$param.'"><img '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/button_unhide.gif','').' alt="'.$this->getLL('bill.unhideTitle').'"/></a>';
					} else {
						$out .= '<a title="'.$this->getLL('bill.hideTitle').'" href="index.php?action=hideBill&uid='.$rowBill['uid'].$param.'"><img '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/button_hide.gif','').' alt="'.$this->getLL('bill.hideTitle').'"/></a>';
					}
					                                                          
					$out .= '<a title="'.$this->getLL('bill.deleteTitle').'" href="javascript:checkDelete(\''.$rowBill['uid'].'\', \''.$param.'\', \''.$this->getLL('bill.confirm').'\')"><img '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/garbage.gif','').' alt="'.$this->getLL('bill.deleteTitle').'"/></a>';
					$out .= '</td>';	
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
					$out .= '<td align="center"><strong>'.$rowClosingType['closing_title'].'</strong></td>';
                } else {
					$out .= '<td align="center"><a title="'.$this->getLL('bill.newTitle').'" href="index.php?action=addBill&crdate='.$crdate.'&type='.$row['uid'].$param.'"><img '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/new_page.gif','').' alt="'.$this->getLL('bill.newTitle').'"/></a></td>'; 
				}
			}
			$out .= '</tr>';
		  }
		  
		  $out .= '<tr class="tableContent">';
		  $out .= '<td width="100"><a style="text-decoration:underline;" href="index.php?week=back&time='.$time.'" title="'.$this->getLL('bill.backWeekTitle').'">&laquo; '.$this->getLL('bill.Back').'</a></td><td colspan="7" align="right"><a href="index.php?week=forward&time='.$time.'" style="text-decoration:underline;" title="'.$this->getLL('bill.forwardWeekTitle').'">'.$this->getLL('bill.Forward').' &raquo;</a></td>';
		  $out .= '</tr>';

		  $out .= '</table><br /><input type="submit" name="saveClosed" value="'.$this->getLL('bill.Save').'"/> </form>';
		  return $out;
	  }
	  
	  
	  /**
	  * 
	  */
	  function getClosingTypes($cause) {
		global $LANG;
		$results = array();
		$res  = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'tx_ppwlunchmenu_closing_types',
			'deleted=0 AND hidden=0',
			'',
			'closing_title ASC',
			''
		);
		
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if($row['uid'] == $cause) {
				$selected = ' selected="selected" ';	
			} else {
				$selected = '';
			}
			$results .= '<option value="'.$row['uid'].'"'.$selected.'>'.$row['closing_title'].'</option>';
		}
		
		return $results;
	  }
	  
	  
	  /**
	  * 
	  */
	  function editDayEntry() {
		$hiddenFields = '';
		$linkParam = '';
		if(t3lib_div::GPvar('week')) {
			$hiddenFields = '<input type="hidden" name="week" value="'.t3lib_div::GPvar('week').'" />
							 <input type="hidden" name="time" value="'.t3lib_div::GPvar('time').'" />';
			$linkParam = '?uid='.t3lib_div::GPvar('uid').'&week='.t3lib_div::GPvar('week').'&time='.t3lib_div::GPvar('time');
		}
		if(t3lib_div::GPvar('saveBillClose_x')) {
			$this->updateEntry(t3lib_div::GPvar('uid'));
			header('Location: index.php'.$linkParam);	
		} elseif (t3lib_div::GPvar('saveBill_x')) {
			$this->updateEntry(t3lib_div::GPvar('uid'));
		} elseif (t3lib_div::GPvar('closeBill_x')) {
			header('Location: index.php'.$linkParam);	
		}
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'tx_ppwlunchmenu_bill',
			'uid='.t3lib_div::GPvar('uid'),
			'',
			'',
			''
		);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        
		if ($row['hidden'] !=0) {
			$hiddenChecked = 'checked="checked"';
		} else {
			$hiddenChecked = '';
		}
		
		$out = '';
		$out .= '
		<script language="javascript" type="text/javascript" src="tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
		<script language="javascript" type="text/javascript" src="tinymce/jscripts/tiny_mce/tiny_mce_src.js"></script>
		<script type="text/javascript">
		tinyMCE.init({
			mode : "textareas",
			theme : "advanced",
			theme_advanced_buttons1 : "bold,undo,redo",
			theme_advanced_buttons2 : "",
			theme_advanced_buttons3 : "",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_path_location : "bottom"
			
		});
		</script>
		<form name="editBill" method="post">';
		  
		  $out .= '<input type="image" class="c-inputButton" name="saveBill"'.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/savedok.gif','').' title="'.$this->getLL('bill.Save').'" />';
		  $out .= '<input type="image" class="c-inputButton" name="saveBillClose"'.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/saveandclosedok.gif','').' title="'.$this->getLL('bill.SaveClose').'" />';
		  $out .= '<input type="image" class="c-inputButton" name="closeBill"'.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/closedok.gif','').' title="'.$this->getLL('bill.Close').'" />';
	  $out .= '<input type="hidden" name="uid" value="'.t3lib_div::GPvar('uid').'" />'.$hiddenFields;
	  $out .= '<br />
	  <table border="0" cellspacing="0" cellpadding="0" width="440" class="typo3-TCEforms">
		<tr>
			<td colspan="2">
				<table border="0" cellspacing="0" cellpadding="0" width="100%" class="wrapperTable1">
					<!--
                    <tr  class="class-main12">
						<td><span class="nbsp">&nbsp;</span></td>
						<td width="99%"><span style="color:;" class="class-main14"><b>'.$this->getLL('bill.Hide').'</b></span></td>
					</tr>

					<tr class="class-main11">
						<td><span class="nbsp">&nbsp;</span></td>
						<td valign="top"><input type="checkbox" class="checkbox"  name="hideBill" '.$hiddenChecked.'/></td>
					</tr>
					-->
					<tr  class="class-main12">
						<td><span class="nbsp">&nbsp;</span></td>
						<td width="99%"><span style="color:;" class="class-main14"><b>'.$this->getLL('bill.Title').'</b></span></td>
					</tr>

					<tr  class="class-main11">
						<td nowrap="nowrap"><span class="nbsp">&nbsp;</span></td>
						<td valign="top"><input type="text" name="draftTitle" value="'.$row['draft_title'].'" size="78" class="formField1" maxlength="256"  /></td>
					</tr>
					<tr class="class-main12">
						<td><span class="nbsp">&nbsp;</span></td>
						<td width="99%"><span style="color:;" class="class-main14"><b>'.$this->getLL('bill.German').'</b></span></td>
					</tr>

					<tr  class="class-main11">
						<td nowrap="nowrap"><span class="nbsp">&nbsp;</span></td>
						<td valign="top">
							<textarea name="germanDesc" cols="75" rows="15">'.$row['de_desc'].'</textarea>
						</td>
					</tr>
					<tr  class="class-main12">
						<td><span class="nbsp">&nbsp;</span></td>
						<td width="99%"><span style="color:;" class="class-main14"><b>'.$this->getLL('bill.English').'</b></span></td>

					</tr>
					<tr  class="class-main11">
						<td nowrap="nowrap"><span class="nbsp">&nbsp;</span></td>
						<td valign="top">
							<textarea name="englishDesc" cols="75" rows="15">'.$row['default_desc'].'</textarea>
						</td>
					</tr>
                    <tr  class="class-main12">
						<td><span class="nbsp">&nbsp;</span></td>
						<td width="99%"><span style="color:;" class="class-main14"><b>'.$this->getLL('bill.Price').'</b></span></td>
					</tr>

					<tr  class="class-main11">
						<td nowrap="nowrap"><span class="nbsp">&nbsp;</span></td>
						<td valign="top"><input type="text" name="price" value="'.$row['food_price'].'" size="10" class="formField1" maxlength="10"  /></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>';
	
		
		  
		  $out .= '<input type="image" class="c-inputButton" name="saveBill"'.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/savedok.gif','').' title="'.$this->getLL('bill.Save').'" />';
		  $out .= '<input type="image" class="c-inputButton" name="saveBillClose"'.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/saveandclosedok.gif','').' title="'.$this->getLL('bill.SaveClose').'" />';
		  $out .= '<input type="image" class="c-inputButton" name="closeBill"'.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/closedok.gif','').' title="'.$this->getLL('bill.Close').'" />';
		   $out .= '</form>';
		return $out;
	  }
	  
	  
	  /**
	  * 
	  */
	  function createDayEntry($PID) {
		
        if (t3lib_div::GPvar('draft') ) {
			return $this->getCreateForm($PID);
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
		$out .= '<label class="formLabel" for="sword">'.$this->getLL('search.Sword').'</label> ';
		$out .= '<input type="text" value="'.t3lib_div::GPvar('sword').'" id="sword" name="sword" />';
		$out .= '<input type="submit" value="'.$this->getLL('search.Submit').'"/>';
		$out .= '</form></div><br />';
		$out .= '<form  name="new" method="post" action="index.php?action=showForm"><input id="submit" type="submit" value="'.$this->getLL('bill.noDraft').'" />&nbsp;<br /><br /><table cellspacing="2" cellpadding="2" border="1" bordercolor="#fff" width="850">';
		$out .= '<input type="hidden" name="step1" value="1" />
				<input type="hidden" name="crdate" value="'.t3lib_div::GPvar('crdate').'" />
				<input type="hidden" name="type" value="'.t3lib_div::GPvar('type').'" />
		';
        
        
		if(t3lib_div::GPvar('week')) {
			$out .= '<input type="hidden" name="week" value="'.t3lib_div::GPvar('week').'" />
			<input type="hidden" name="time" value="'.t3lib_div::GPvar('time').'" />';
			
		}
		if (strlen(t3lib_div::GPvar('sword')) >= 1) {
			$sword = t3lib_div::GPvar('sword');
			$tableContent = $this->getSearchResults($sword, $orderBy, $PID);
		} else {
			
			$tableContent .= $this->getFaresOverview($orderBy, $recordsPerPage ,$PID);
		}
		
		$out .= $this->getTableHeader($sword, $actPage);
		$out .= $tableContent;
		
		$out .= '</table><br /><br /><input id="next"  type="submit" value="'.$this->getLL('bill.noDraft').'" />&nbsp;</form>';
		return $out;
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
                $week = '';
                if(t3lib_div::GPvar('week')) {
			        $week = '&week='.t3lib_div::GPvar('week').'&time='.t3lib_div::GPvar('time');
		        }
				$out .= '<td align="center">'; 
				$out .= '<a href="index.php?action=showForm&step=1'.$week.'&crdate='.t3lib_div::GPvar('crdate').'&type='.t3lib_div::GPvar('type').'&draft='.$row['uid'].'"><img '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/add.gif','').' title="'.htmlentities($title).'" /></a>';
				$out .= '</td>';
				$out .= '<td >'.$row['fare_title'].'</td>';
				$out .= '<td>'.substr(strip_tags($row['fare_desc_german']),0,50).'&nbsp;...</td>';
				if($this->rowConfig['show_date'] != 0) {
					$out .= '<td align="center">'.date("d.m.Y", $row['crdate']).' '.date("H:i", $row['crdate']).' '.$LANG->getLL('td.Time').'</td>';
				}
				
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
					
                    $time = (t3lib_div::GPvar('time')) ? 'time='.t3lib_div::GPvar('time') : '';
					$out .= '<a href="index.php?action='.t3lib_div::GPvar('action').'&crdate='.t3lib_div::GPvar('crdate').'&type='.t3lib_div::GPvar('type').$time.'&page='.$i.'">'.$i.' </a>';
				}
				
				$out .= '</span><br /><br />';
				while($row = @$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					$out .= '<tr class="tableContent">';
					if($this->rowConfig['show_uid'] != 0) {
						$out .= '<td align="center">'.$row['uid'].'</td>';
					}
					$week = '';
                    if(t3lib_div::GPvar('week')) {
			            $week = '&week='.t3lib_div::GPvar('week').'&time='.t3lib_div::GPvar('time');
		            }
				    $out .= '<td align="center">'; 
				    $out .= '<a href="index.php?action=showForm&step=1'.$week.'&crdate='.t3lib_div::GPvar('crdate').'&type='.t3lib_div::GPvar('type').'&draft='.$row['uid'].'"><img '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/add.gif','').' title="'.htmlentities($title).'" /></a>';
				    $out .= '</td>';
					
                    $title = strip_tags($row['fare_desc_german']);													  
					$out .= '<td >'.$row['fare_title'].'&nbsp;<img id="information_'.$row['uid'].'" onmouseover="document.getElementById(\'information_'.$row['uid'].'\').style.cursor= \'help\'" '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/helpbubble.gif','').' title="'.htmlentities($title).'" /></td>';
					$out .= '<td>'.substr($title,0,50).'&nbsp;...</td>';
					if($this->rowConfig['show_date'] != 0) {
						$out .= '<td align="center">'.date("d.m.Y", $row['crdate']).' '.date("H:i", $row['crdate']).' '.$LANG->getLL('td.Time').'</td>';
					}
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
	  function getCreateForm($PID) {
        #print_r($_POST);
		$selectUID = t3lib_div::GPvar('draft');
		
		
		$row = $this->getFareElement($selectUID);
		$hiddenFields = '';
		$linkParam = '';
		$draftUID = (t3lib_div::GPvar('uid')) ? t3lib_div::GPvar('uid') : 1;
        if(t3lib_div::GPvar('week')) {
			$hiddenFields = '<input type="hidden" name="week" value="'.t3lib_div::GPvar('week').'" />
							 <input type="hidden" name="time" value="'.t3lib_div::GPvar('time').'" />';
			$linkParam = '&uid='.$draftUID.'&week='.t3lib_div::GPvar('week').'&time='.t3lib_div::GPvar('time');
		}
		$postFunction = $_POST['SET']['function'].$linkParam;
		
		if(t3lib_div::GPvar('saveBillClose_x')) {
			if(t3lib_div::GPvar('update')) {
				$this->updateEntry(t3lib_div::GPvar('update'));
			} else {
				$this->insertEntry($PID);
			}
			header('Location: index.php?&id=0&SET[function]='.$postFunction);
		} elseif (t3lib_div::GPvar('saveBill_x')) {
			if(t3lib_div::GPvar('update')) {
				$this->updateEntry(t3lib_div::GPvar('update'));
				$selectUID = t3lib_div::GPvar('update');
				$row = $this->getBillElement($selectUID);
				$hiddenFields .= '<input type="hidden" name="update" value="'.$selectUID.'" />';
			} else {
			
				$this->insertEntry($PID);
				if ($this->insertID) {
					$selectUID = $this->insertID;
					$row = $this->getBillElement($selectUID);
					$hiddenFields .= '<input type="hidden" name="update" value="'.$selectUID.'" />';
				}
			}
			
			
		} elseif (t3lib_div::GPvar('closeBill_x')) {
			
			header('Location: index.php?&id=0&SET[function]='.$postFunction);	
		} 
		
		
		if ($row['hidden'] !=0) {
			$hiddenChecked = 'checked="checked"';
		} else {
			$hiddenChecked = '';
		}
		
		$out = '';
		$out .= '
        <script language="javascript" type="text/javascript" src="tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
		<script language="javascript" type="text/javascript" src="tinymce/jscripts/tiny_mce/tiny_mce_src.js"></script>
		<script type="text/javascript">
		tinyMCE.init({
			mode : "textareas",
			theme : "advanced",
			theme_advanced_buttons1 : "bold,undo,redo",
			theme_advanced_buttons2 : "",
			theme_advanced_buttons3 : "",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_path_location : "bottom"
			
		});
		</script>
		<form name="addBill" method="post">';
		  $out .= '<input type="image" class="c-inputButton" name="saveBill"'.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/savedok.gif','').' title="'.$this->getLL('bill.Save').'" />';
		  $out .= '<input type="image" class="c-inputButton" name="saveBillClose"'.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/saveandclosedok.gif','').' title="'.$this->getLL('bill.SaveClose').'" />';
		  $out .= '<input type="image" class="c-inputButton" name="closeBill"'.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/closedok.gif','').' title="'.$this->getLL('bill.Close').'" />';
		   $out .= '<input type="hidden" name="type" value="'.t3lib_div::GPvar('type').'"/><input type="hidden" name="crdate" value="'.t3lib_div::GPvar('crdate').'"/><input type="hidden" name="uid" value="'.$draftUID.'"/><input type="hidden" name="draft" value="'.t3lib_div::GPvar('draft').'"/>'.$hiddenFields;
	  $out .= '<br />';
	  $out .= '<table border="0" cellspacing="0" cellpadding="0" width="440" class="typo3-TCEforms">
		<tr>
			<td colspan="2">
				<table border="0" cellspacing="0" cellpadding="0" width="100%" class="wrapperTable1">
					<tr  class="class-main12">
						<td><span class="nbsp">&nbsp;</span></td>
						<td width="99%"><span style="color:;" class="class-main14"><b>'.$this->getLL('bill.Hide').'</b></span></td>
					</tr>

					<tr class="class-main11">
						<td><span class="nbsp">&nbsp;</span></td>
						<td valign="top"><input type="checkbox" class="checkbox"  name="hideBill" '.$hiddenChecked.'/></td>
					</tr>
					<tr  class="class-main12">
						<td><span class="nbsp">&nbsp;</span></td>
						<td width="99%"><span style="color:;" class="class-main14"><b>'.$this->getLL('bill.Title').'</b></span></td>
					</tr>

					<tr  class="class-main11">
						<td nowrap="nowrap"><span class="nbsp">&nbsp;</span></td>
						<td valign="top"><input type="text" name="draftTitle" value="'.$row['title'].'" size="78" class="formField1" maxlength="256"  /></td>
					</tr>
					<tr class="class-main12">
						<td><span class="nbsp">&nbsp;</span></td>
						<td width="99%"><span style="color:;" class="class-main14"><b>'.$this->getLL('bill.German').'</b></span></td>
					</tr>

					<tr  class="class-main11">
						<td nowrap="nowrap"><span class="nbsp">&nbsp;</span></td>
						<td valign="top">
							<textarea name="germanDesc" cols="75" rows="15">'.$row['german'].'</textarea>
						</td>
					</tr>
					<tr  class="class-main12">
						<td><span class="nbsp">&nbsp;</span></td>
						<td width="99%"><span style="color:;" class="class-main14"><b>'.$this->getLL('bill.English').'</b></span></td>

					</tr>
					<tr  class="class-main11">
						<td nowrap="nowrap"><span class="nbsp">&nbsp;</span></td>
						<td valign="top">
							<textarea name="englishDesc" cols="75" rows="15">'.$row['english '].'</textarea>
						</td>
					</tr>
					<tr  class="class-main12">
						<td><span class="nbsp">&nbsp;</span></td>
						<td width="99%"><span style="color:;" class="class-main14"><b>'.$this->getLL('bill.Price').'</b></span></td>
					</tr>

					<tr  class="class-main11">
						<td nowrap="nowrap"><span class="nbsp">&nbsp;</span></td>
						<td valign="top"><input type="text" name="price"  size="10" class="formField1" maxlength="10" value="'.$row['price'].'" /></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>';
		  $out .= '<input type="image" class="c-inputButton" name="saveBill"'.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/savedok.gif','').' title="'.$this->getLL('bill.Save').'" />';
		  $out .= '<input type="image" class="c-inputButton" name="saveBillClose"'.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/saveandclosedok.gif','').' title="'.$this->getLL('bill.SaveClose').'" />';
		  $out .= '<input type="image" class="c-inputButton" name="closeBill"'.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/closedok.gif','').' title="'.$this->getLL('bill.Close').'" />';
		   $out .= '</form>';
		return $out;
	  }
	  
	  
		/**
		* 
		*/
		function getFareElement($UID) {
			$res = @$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*',
				'tx_ppwlunchmenu_fare',
				'uid='.$UID,
				'',
				'',
				''
			);
			$row = @$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$elements = array(
				'hidden'    => $row['hidden'],
				'title'     => $row['fare_title'],
				'german'    => $row['fare_desc_german'],
				'english'   => $row['fare_desc_english']
			);
			return $elements;
		}
		
		
		/**
		* 
		*/
		function getBillElement($UID) {
			
			$res = @$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*',
				'tx_ppwlunchmenu_bill',
				'uid='.$UID,
				'',
				'',
				''
			);
			$row = @$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$elements = array(
				'hidden'    => $row['hidden'],
				'title'     => $row['draft_title'],
				'german'    => $row['de_desc'],
				'english'   => $row['default_desc'],
				'price'     => $row['food_price']
			);
			return $elements;
		}
	  
	  
	  /**
	  * 
	  */
	  function getWeekDays($time) {
		  $weekDays = array();
		  $year = date('y', strtotime(date('Y-m-d 9:00', $time)));
		  $month = date('m', strtotime(date('Y-m-d 9:00', $time)));
		  $day = date('d', strtotime(date('Y-m-d 9:00', $time)));
		  
		  
		  switch (date('D', $time)) {
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
																				 
		  $weekDays['mon'][0] = $this->translateDay(date('l', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - $sub, $year)))));
		  $weekDays['mon'][1] = date('d.m.y', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - $sub, $year))));
		  $weekDays['mon'][2] = strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - $sub, $year)));
		  
		  $weekDays['tue'][0] = $this->translateDay(date('l', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-1), $year)))));
		  $weekDays['tue'][1] = date('d.m.y', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-1), $year))));
		  $weekDays['tue'][2] = strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-1), $year)));
		  
		  $weekDays['wed'][0] = $this->translateDay(date('l', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-2), $year)))));
		  $weekDays['wed'][1] = date('d.m.y', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-2), $year))));
		  $weekDays['wed'][2] = strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-2), $year)));
		  
		  $weekDays['thu'][0] = $this->translateDay(date('l', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-3), $year)))));
		  $weekDays['thu'][1] = date('d.m.y', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-3), $year))));
		  $weekDays['thu'][2] = strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-3), $year)));
		  
		  $weekDays['fri'][0] = $this->translateDay(date('l', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-4), $year)))));
		  $weekDays['fri'][1] = date('d.m.y', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-4), $year))));
		  $weekDays['fri'][2] = strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-4), $year)));
		  
		  $weekDays['sat'][0] = $this->translateDay(date('l', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-5), $year)))));
		  $weekDays['sat'][1] = date('d.m.y', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-5), $year))));
		  $weekDays['sat'][2] = strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day - ($sub-5), $year)));
		  
		  $weekDays['sun'][0] = $this->translateDay(date('l', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day + $add, $year)))));
		  $weekDays['sun'][1] = date('d.m.y', strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day + $add, $year))));
		  $weekDays['sun'][2] = strtotime(date('Y-m-d 9:00', mktime(9, 0, 0, $month, $day + $add, $year)));
		  return $weekDays;
	  }
	  
	  
      /**
      * 
      */
	  function translateDay($day) {

		  switch ($day) {
			case "Sunday":
				$translatedDay = $this->getLL('day.Sunday');
			break;
			case "Monday":
				$translatedDay = $this->getLL('day.Monday');
			break;
			case "Tuesday":
				$translatedDay = $this->getLL('day.Tuesday');
			break;
			case "Wednesday":
				$translatedDay = $this->getLL('day.Wednesday');
			break;
			case "Thursday":
				$translatedDay = $this->getLL('day.Thursday');
			break;
			case "Friday":
				$translatedDay = $this->getLL('day.Friday');
			break;
			case "Saturday":
				$translatedDay = $this->getLL('day.Saturday');
			break;
			
		}
		return $translatedDay;
	  }
	  
	  
	  /**
	  *                                      
	  */
	  function getTableHeader($sword, $actPage) {
		global $LANG;
		$out .= '<tr>';
		if($this->rowConfig['show_uid'] != 0) {
			$out .= '<th class="tableHeader">'.$LANG->getLL('th.ID').'</th>';	
		}
		$time = (t3lib_div::GPvar('time')) ? 'time='.t3lib_div::GPvar('time') : '';
        $out .= '<th class="tableHeader">'.$LANG->getLL('th.accept').'</th>';
        $out .= '<th class="tableHeader">'.$LANG->getLL('th.Title').'<br /><a href="index.php?action='.t3lib_div::GPvar('action').'&crdate='.t3lib_div::GPvar('crdate').'&type='.t3lib_div::GPvar('type').$time.'&sort=fare_title&way=DESC&sword='.$sword.'&page='.$actPage.'"><img alt="'.$LANG->getLL('sort.down').'" title="'.$LANG->getLL('sort.down').'" '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/button_down.gif','').' /></a><a href="index.php?action='.t3lib_div::GPvar('action').'&crdate='.t3lib_div::GPvar('crdate').'&type='.t3lib_div::GPvar('type').$time.'&sort=fare_title&way=ASC&sword='.$sword.'&page='.$actPage.'"><img alt="'.$LANG->getLL('sort.up').'" title="'.$LANG->getLL('sort.up').'" '.t3lib_iconWorks::skinImg('/typo3/sysext/t3skin/icons/gfx/button_up.gif','').' /></a></th>';
        
		$out .= '<th class="tableHeader">'.$LANG->getLL('th.Desc').'</th>';
		if($this->rowConfig['show_date'] != 0) {
			$out .= '<th class="tableHeader">'.$LANG->getLL('th.Time').'</th>';
		}
		
		$out .= '</tr>';
		return $out;
	  }
	  
	  
	  /**
	  * 
	  */
	  function HideEntry($table) {
		$updateArray = array(
			'tstamp'    =>  time(),
			'hidden'    =>  1
		);
		$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, 'uid='.t3lib_div::GPvar('uid'), $updateArray);
	  }
		
		
	  /**
	  * 
	  */
	  function UnHideEntry($table) {
		$updateArray = array(
			'tstamp'    =>  time(),
			'hidden'    =>  0
		);
		$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, 'uid='.t3lib_div::GPvar('uid'), $updateArray);
		}
		
		
	  /**
	  * 
	  */
	  function DeleteEntry($table) {
		$updateArray = array(
			'tstamp'    =>  time(),
			'deleted'    =>  1
		);
		$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, 'uid='.t3lib_div::GPvar('uid'), $updateArray);
	  }
	  
	  
	  /**
	  * 
	  */
	  function updateEntry($uid) {
		
		  $updateArray = array(
			'tstamp'        => time(),
			'draft_title'   => t3lib_div::GPvar('draftTitle'),
			'de_desc'   => t3lib_div::GPvar('germanDesc'),
			'default_desc'  => t3lib_div::GPvar('englishDesc'),
			'food_price'    => t3lib_div::GPvar('price')
		);
		if (t3lib_div::GPvar('hideBill')) {
			$updateArray['hidden'] = 1;
		} else {
			$updateArray['hidden'] = 0;	
		}
		$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_ppwlunchmenu_bill', 'uid='.$uid, $updateArray);
	  }
	  
	  
	  /**
	  * 
	  */
	  function insertEntry($PID) {
		  #print_r($_POST);
		  $fieldValues = array(
			'pid'           => $PID,
			'cruser_id'     => $this->cruser_id,
			'crdate'        => t3lib_div::GPvar('crdate'),
			'tstamp'        => time(),
			'draft_title '  => t3lib_div::GPvar('draftTitle'),
			'de_desc'   => t3lib_div::GPvar('germanDesc'),
			'default_desc'  => t3lib_div::GPvar('englishDesc'),
			'draft_uid'     => t3lib_div::GPvar('draft'),
			'type_uid'      => t3lib_div::GPvar('type'),
			'food_price'    => t3lib_div::GPvar('price'),
		);
		if (t3lib_div::GPvar('hideBill')) {
			$fieldValues['hidden'] = 1;
		} else {
			$fieldValues['hidden'] = 0;	
		}
		$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_ppwlunchmenu_bill', $fieldValues);
		$this->insertID = $GLOBALS['TYPO3_DB']->sql_insert_id($res);
	  }
      
      
	  /**
      * 
      */
	  function updateClosingDays($days, $PID) {
		  foreach(t3lib_div::GPvar('holiday') as $key => $value) {
			$date = $_POST['closed'][$key];
			$getClosingDay = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*',
				'tx_ppwlunchmenu_closed',
				'date='.$date,
				'',
				'',
				''
			);
			$count = $GLOBALS['TYPO3_DB']->sql_num_rows($getClosingDay);
			if ($_POST['holiday'][$key] == 0 && $count != 0) {
				$GLOBALS['TYPO3_DB']->exec_DELETEquery ('tx_ppwlunchmenu_closed', 'date='.$date.' AND crdate='.$date);
			
			} elseif($_POST['holiday'][$key] != 0 && $count == 0) {
				
				$fieldValues = array(
				'pid'           => $PID,
				'cruser_id'     => $this->cruser_id,
				'tstamp'        => $date,
				'crdate'        => $date,
				'date'          => $date,
				'closing_cause' => $_POST['holiday'][$key]
			   );
			   $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_ppwlunchmenu_closed', $fieldValues);
			} elseif($_POST['holiday'][$key] != 0 && $count != 0) {
				 $updateArray = array(
					'closing_cause' => $_POST['holiday'][$key]
				 );
				 $GLOBALS['TYPO3_DB']->exec_UPDATEquery ('tx_ppwlunchmenu_closed', 'date='.$date.' AND crdate='.$date, $updateArray);
			}
		  }
	  }
  }
?>