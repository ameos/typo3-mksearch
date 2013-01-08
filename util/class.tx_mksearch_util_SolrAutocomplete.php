<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012 das Medienkombinat <kontakt@das-medienkombinat.de>
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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_rnbase_configurations');
tx_rnbase::load('tx_mksearch_util_ServiceRegistry');		

/**
 * @author Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
 * @package TYPO3
 * @subpackage tx_mksearch
 */
class tx_mksearch_util_SolrAutocomplete {
	
	/**
	 * @var string
	 */
	protected static $autocompleteConfId = 'autocomplete.';
	
	/**
	 * example TS config:
	 * myConfId {
	 * 	usedIndex = 1
	 * 	autocomplete {
	 * 		actionLink {
	 * 			useKeepVars = 1
	 * 			useKeepVars.add = ::type=540
	 * 			absurl = 1
	 * 			noHash = 1
	 * 		}
	 * 	}
	 * }
	 * 
	 * 
	 * @param tx_rnbase_configurations $configurations
	 * @param string $confId
	 * 
	 * @return tx_rnbase_util_Link
	 */
	public static function getAutocompleteActionLinkByConfigurationsAndConfId(
		tx_rnbase_configurations $configurations, $confId
	) {
		$link = $configurations->createLink();
		$link->initByTS(
			$configurations,
			$confId . self::$autocompleteConfId . 'actionLink.',
			array(
				'ajax' => 1, //set always true
				'usedIndex' => intval($configurations->get($confId.'usedIndex'))
			)
		);
		
		return $link;
	}
	
	/**
	 * example TS config:
	 * myConfId {
	 * 	autocomplete {
	 * 		minLength = 2
	 * 		elementSelector = "#mksearch_term"
	 * 	}
	 * }
	 * @param array $configArray
	 * @param tx_rnbase_util_Link $link
	 * 
	 * @return string
	 */
	public static function getAutocompleteJsByConfigurationsConfIdAndLink(
		tx_rnbase_configurations $configurations, $confId, tx_rnbase_util_Link $link
	) {
		$configArray = $configurations->get($confId . self::$autocompleteConfId);
		
		return '
		<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery('.$configArray['elementSelector'].').autocomplete({
				source: function( request, response ) {
					jQuery.ajax({
						url: "'.$link->makeUrl(false).'&mksearch[term]="+encodeURIComponent(request.term),
						dataType: "json",
						success: function( data ) {
							var suggestions = [];
							jQuery.each(data.suggestions, function(key, value) {
								jQuery.each(value, function(key, suggestion) {
									suggestions.push(suggestion.uid);
								});
							});
							response( jQuery.map( suggestions, function( item ) {
								return {
									label: item,
									value: item
								};
							}));
						}
					});
				},
				minLength: '.$configArray['minLength'].'
			});
		});
		jQuery(".ui-autocomplete.ui-menu.ui-widget.ui-widget-content.ui-corner-all").show();
		</script>
		';
	}
}