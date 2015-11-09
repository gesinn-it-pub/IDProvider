<?php
class IDProviderRandomApi extends ApiBase {

	/**
	 * Description of the allowed parameters
	 * @return array
	 */
	public function getAllowedParams() {
		$params = array(
			'type' => array(
				ApiBase::PARAM_TYPE => array(
					'uuid',
					'fakeid'
				),
				ApiBase::PARAM_REQUIRED => true,
			),
			'prefix' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_HELP_MSG => 'idp-apiparam-prefix',
			),
			'skipUniqueTest' => array(
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_HELP_MSG => 'idp-apiparam-skipuniquetest',
			),
		);

		foreach ($params as $name => $value ) {
			if (!isset($params[$name][ApiBase::PARAM_HELP_MSG])) {
				$params[$name][ApiBase::PARAM_HELP_MSG] = "idp-random-apiparam-" . strtolower($name);
			}
		}

		return $params;
	}


	/**
	 * Calculate and return the response
	 */
	public function execute() {

		$params = $this->extractRequestParams();

		try {
			$id = IDProviderFunctions::getRandom($params);
			$this->getResult()->addValue( null, 'id', $id );
		} catch (Exception $e) {
			$error = array(
				'code' => 'api_exception',
				'info' => $e->getMessage(),
			);
			$this->getResult()->addValue( null, 'error', $error );
		}
	}

	/**
	 * Some example queries
	 *
	 * @return array
	 */
	protected function getExamplesMessages() {
		return array(
			'action=idprovider-increment&padding=8' => 'idp-increment-example-1',
			'action=idprovider-increment&prefix=TestPrefix_&padding=5&wikipage=' => 'idp-increment-example-2'
		);
	}
}
