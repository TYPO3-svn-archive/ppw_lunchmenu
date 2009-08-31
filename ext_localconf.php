<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_ppwlunchmenu_fare=1
');

t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_ppwlunchmenu_config=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_ppwlunchmenu_types=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_ppwlunchmenu_bill=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_ppwlunchmenu_closed=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_ppwlunchmenu_closing_types=1
');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_ppwlunchmenu_pi1 = < plugin.tx_ppwlunchmenu_pi1.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_ppwlunchmenu_pi1.php','_pi1','list_type',1);
?>
