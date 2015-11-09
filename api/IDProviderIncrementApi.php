<?php
class IDProviderIncrementApi extends ApiBase {

	/**
	 * Description of the allowed parameters
	 * @return array
	 */
	public function getAllowedParams() {

		$params = array(
			'prefix' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_HELP_MSG => 'idp-apiparam-prefix',
			),
			'padding' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_MIN => 0,
			),
//			'start' => array(
//				ApiBase::PARAM_TYPE => 'integer',
//				ApiBase::PARAM_MIN => 0,
//			),
			'skipUniqueTest' => array(
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_HELP_MSG => 'idp-apiparam-skipuniquetest',
			),
		);

		// Add missing i18 help messages
		foreach ($params as $name => $value ) {
			if (!isset($params[$name][ApiBase::PARAM_HELP_MSG])) {
				$params[$name][ApiBase::PARAM_HELP_MSG] = "idp-increment-apiparam-" . strtolower($name);
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
			$id = IDProviderFunctions::getIncrement($params);
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
			'action=idprovider-increment&prefix=TestPrefix_&padding=5&skipUniqueTest=' => 'idp-increment-example-2'
		);
	}
}
