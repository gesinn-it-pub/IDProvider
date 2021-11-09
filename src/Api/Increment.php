<?php
/**
 * MediaWiki IDProvider Extension
 *
 * Provides (unique) IDs using different ID algorithms.
 *
 * @link https://github.com/gesinn-it/IDProvider
 *
 * @author gesinn.it GmbH & Co. KG
 * @license MIT
 */

namespace MediaWiki\Extension\IdProvider\Api;

use ApiBase;
use MediaWiki\Extension\IdProvider\IdProviderFactory;

class Increment extends ApiBase {

	/**
	 * @return array
	 */
	public function getAllowedParams(): array {
		$params = [
			'prefix' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_HELP_MSG => 'idp-apiparam-prefix',
			],
			'padding' => [
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_MIN => 0,
			],
			'skipUniqueTest' => [
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_HELP_MSG => 'idp-apiparam-skipuniquetest',
			],
		];

		// Add missing i18 help messages
		foreach ( $params as $name => $value ) {
			if ( !isset( $value[ApiBase::PARAM_HELP_MSG] ) ) {
				$params[$name][ApiBase::PARAM_HELP_MSG] =
					"idp-increment-apiparam-" . strtolower( $name );
			}
		}

		return $params;
	}

	public function execute() {
		$params = $this->extractRequestParams();
		$id = IdProviderFactory::increment( $params )->getId( $params );
		$this->getResult()->addValue( null, 'id', $id );
	}

	/**
	 * @return string[]
	 */
	public function getExamplesMessages(): array {
		return [
			'action=idprovider-increment&padding=8' => 'idp-increment-example-1',
			'action=idprovider-increment&prefix=TestPrefix_&padding=5&skipUniqueTest=' => 'idp-increment-example-2',
		];
	}
}
