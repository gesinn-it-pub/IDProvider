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

class Random extends ApiBase {

	/**
	 * @return array
	 */
	public function getAllowedParams(): array {
		$params = [
			'type' => [
				ApiBase::PARAM_TYPE => [
					'uuid',
					'fakeid',
				],
				ApiBase::PARAM_REQUIRED => true,
			],
			'prefix' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_HELP_MSG => 'idp-apiparam-prefix',
			],
			'skipUniqueTest' => [
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_HELP_MSG => 'idp-apiparam-skipuniquetest',
			],
		];

		foreach ( $params as $name => $value ) {
			if ( !isset( $value[ApiBase::PARAM_HELP_MSG] ) ) {
				$params[$name][ApiBase::PARAM_HELP_MSG] =
					"idp-random-apiparam-" . strtolower( $name );
			}
		}

		return $params;
	}

	public function execute() {
		$params = $this->extractRequestParams();
		$id = IdProviderFactory::random( $params )->getId( $params );
		$this->getResult()->addValue( null, 'id', $id );
	}

	/**
	 * @return string[]
	 */
	public function getExamplesMessages(): array {
		return [
			'action=idprovider-random&type=uuid' => 'idp-random-example-1',
			'action=idprovider-random&type=fakeid' => 'idp-random-example-2',
		];
	}
}
