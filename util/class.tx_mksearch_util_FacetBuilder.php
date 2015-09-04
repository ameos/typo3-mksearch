<?php
/**
 * 	@package tx_mksearch
 *  @subpackage tx_mksearch_util
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2011 DMK E-Business GmbH <dev@dmk-ebusiness.de>
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

/**
 * benötigte Klassen einbinden
 */

tx_rnbase::load('tx_mksearch_model_Facet');

/**
 * Der FacetBuilder erstellt aus den Rohdaten
 * der Facets passende Objekte für das Rendering.
 *
 * @package tx_mksearch
 * @subpackage tx_mksearch_util
 * @author Hannes Bochmann <dev@dmk-ebusiness.de>
 * @author Michael Wagner <dev@dmk-ebusiness.de>
 */
class tx_mksearch_util_FacetBuilder {

	/**
	 *
	 * @var tx_mksearch_util_KeyValueFacet|NULL
	 */
	private $keyValueFacetInstance = NULL;

	/**
	 * get singelton
	 *
	 * @param string $class
	 * @return tx_mksearch_util_FacetBuilder
	 */
	public static function getInstance($class = '') {
		static $instance;
		$class = empty($class) ? 'tx_mksearch_util_FacetBuilder' : $class;
		if (!$instance[$class]) {
			$instance[$class] = tx_rnbase::makeInstance($class);
		}
		return $instance[$class];
	}

	/**
	 * @return tx_mksearch_util_KeyValueFacet
	 */
	protected function getKeyValueFacetInstance() {
		if ($this->keyValueFacetInstance === NULL) {
			$this->keyValueFacetInstance = tx_rnbase::makeInstance(
				'tx_mksearch_util_KeyValueFacet'
			);
		}
		return $this->keyValueFacetInstance;
	}

	/**
	 * Baut die Daten für die Facets zusammen
	 *
	 * @param array|stdClass $facetData Alle Daten von Solr
	 * @return array[] Ausgabedaten
	 */
	public function buildFacets($facetData) {

		$facetGroups = $this->buildFieldFacets($facetData->facet_fields);
		$facetGroups = array_merge($facetGroups, $this->buildQueryFacets($facetData->facet_queries));
		// TODO: RANGE-Facet integrieren

		return $facetGroups;
	}

	/**
	 * Query-Facets kommen von Solr nicht in Gruppen strukturiert. Damit wir mehrere Query-Gruppen unterscheiden
	 * können, müssen die Queries IMMER mit einem Key angelegt werden. Folgende Form:
	 *
	 *  <str name="facet.query">{!key="date_lastweek"}datetime:[NOW-7DAYS/DAY TO NOW]</str>
	 *	<str name="facet.query">{!key="date_lastmonth"}datetime:[NOW-1MONTH/MONTH TO NOW]</str>
	 *
	 * Damit splitten gruppieren wir nach dem String vor dem ersten Unterstrich.
	 *
	 * @param array[stdClass] $facetData Query-Facet Daten von Solr
	 * @return array[tx_rnbase_model_base] Ausgabedaten
	 */
	protected function buildQueryFacets($facetData) {
		$facetGroups = array();
		if (!$facetData) {
			return $facetGroups;
		}

		$uid = 0;
		foreach ($facetData As $key => $value) {
			list($groupName, $queryName) = explode('_', $key, 2);
			if(!array_key_exists($groupName, $facetGroups)) {
				$facetGroups[$groupName] = tx_rnbase::makeInstance(
						'tx_rnbase_model_base',
						array(
								'uid' => ++$uid,
								'field' => $groupName,
								'items' => array(),
						)
				);
			}
			$facetGroups[$groupName]->record['items'][] = $this->getSimpleFacet($groupName,
							$key, $value, tx_mksearch_model_Facet::TYPE_QUERY);

		}

		return array_values($facetGroups);
	}
	/**
	 * Baut die Daten für die Field-Facets zusammen
	 *
	 * @param array|stdClass $facetData Field-Facet Daten von Solr
	 * @return array Ausgabedaten
	 */
	protected function buildFieldFacets($facetData) {
		$facetGroups = array();
		if (!$facetData) {
			return $facetGroups;
		}
		$uid = 0;
		foreach ($facetData As $field => $facetGroup) {
			if (empty($facetGroups[$field])) {
				$facetGroups[$field] = tx_rnbase::makeInstance(
					'tx_rnbase_model_base',
					array(
						'uid' => ++$uid,
						'field' => $field,
						'items' => array(),
					)
				);
			}
			foreach ($facetGroup As $id => $count) {
				$facetGroups[$field]->record['items'][] = $this->getSimpleFacet($field, $id, $count);
			}
		}
		return array_values($facetGroups);
	}

	protected function buildGroupedFacet() {

	}

	/**
	 * Liefert eine simple Facette zurück
	 *
	 * @param string $field
	 * @param int $id
	 * @param int $count
	 * @return tx_mksearch_model_Facet
	 */
	protected function getSimpleFacet($field, $id, $count, $facetType = tx_mksearch_model_Facet::TYPE_FIELD) {
		if ($this->getKeyValueFacetInstance()->checkValue($id)) {
			$exploded = $this->getKeyValueFacetInstance()->explodeFacetValue($id);
			$id = $exploded['key'];
			$title = $exploded['value'];
		}
		else {
			$title = $id;
		}
		$facet = tx_rnbase::makeInstance('tx_mksearch_model_Facet', $field, $id, $title, $count);
		$facet->setFacetType($facetType);
		return $facet;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mksearch/util/class.tx_mksearch_util_FacetBuilder.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mksearch/util/class.tx_mksearch_util_FacetBuilder.php']);
}
