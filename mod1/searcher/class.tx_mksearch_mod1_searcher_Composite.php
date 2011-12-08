<?php
/**
 * 	@package tx_mksearch
 *  @subpackage tx_mksearch_mod1
 *
 *  Copyright notice
 *
 *  (c) 2011 das MedienKombinat GmbH <kontakt@das-medienkombinat.de>
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
 */
require_once(t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php'));
tx_rnbase::load('tx_mksearch_mod1_searcher_abstractBase');

/**
 * Searcher for keywords
 *
 * @package tx_mksearch
 * @subpackage tx_mksearch_mod1
 * @author Michael Wagner <michael.wagner@das-medienkombinat.de>
 */
class tx_mksearch_mod1_searcher_Composite extends tx_mksearch_mod1_searcher_abstractBase {
	
/**
	 * Liefert die Funktions-Id
	 */
	public function getSearcherId() {
		return 'composite';
	}
	
	/**
	 * Liefert den Service.
	 *
	 * @return t3lib_svbase
	 */
	public function getService() {
		return tx_mksearch_util_ServiceRegistry::getIntCompositeService();
	}

	/**
	 * Kann von der Kindklasse überschrieben werden, um weitere Filter zu setzen.
	 *
	 * @param 	array 	$fields
	 * @param 	array 	$options
	 */
	protected function prepareFieldsAndOptions(array &$fields, array &$options) {
		parent::prepareFieldsAndOptions($fields, $options);
		if(isset($this->options['pid'])) {
			$fields['CMP.pid'][OP_EQ_INT] = $this->options['pid'];
		}
// 		$options['debug'] = 1;
	}
	
	/**
	 * @return 	tx_mksearch_mod1_decorator_Keyword
	 */
	protected function getDecorator(&$mod){
		return tx_rnbase::makeInstance('tx_mksearch_mod1_decorator_Composite', $mod);
	}
	
	/**
	 * Liefert die Spalten für den Decorator.
	 * @param 	tx_mksearch_mod1_decorator_Keyword 	$oDecorator
	 * @return 	array
	 */
	protected function getColumns(&$oDecorator){
		return array(
			'uid' => array(
				'title' => 'label_uid',
				'decorator' => $oDecorator
			),
			'title' => array(
				'title' => 'label_tableheader_title',
				'decorator' => $oDecorator, 'width' => 90
			),
			'indices' => array(
				'title' => 'label_tableheader_indices',
				'decorator' => $oDecorator, 'width' => 90
			),
			'configs' => array(
				'title' => 'label_tableheader_configs',
				'decorator' => $oDecorator, 'width' => 90
			),
			'actions' => array(
				'title' => 'label_tableheader_actions',
				'decorator' => $oDecorator, 'width' => 90
			),
		);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see tx_mksearch_mod1_searcher_abstractBase::getSearchColumns()
	 */
	protected function getSearchColumns() {
		return array(
			'CFG.uid', 'CFG.title', 'CFG.extkey', 'CFG.contenttype', 'CFG.configuration',
			'CMP.uid', 'CMP.title', 'CMP.description',
			'INDX.uid', 'INDX.title', 'INDX.name', 'INDX.description', 'INDX.engine', 'INDX.configuration'
		);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mksearch/mod1/searcher/class.tx_mksearch_mod1_searcher_Composite.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mksearch/mod1/searcher/class.tx_mksearch_mod1_searcher_Composite.php']);
}
?>