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
			'wikipage' => array(
				ApiBase::PARAM_TYPE => 'boolean',
			),
		);

		foreach ($params as $name => $value ) {
			$params[$name][ApiBase::PARAM_HELP_MSG] = "idp-random-apiparam-$name";
		}

		return $params;
	}


	/**
	 * Calculate and return the response
	 */
	public function execute() {

		$id = null;
		$error = null;

		$params = $this->extractRequestParams();

		$type = $params['type'] ?: 'uuid';

		try {

			if ($type === 'uuid') {
				$id = IDProviderFunctions::getUUID();

			} else if ($type === 'fakeid') {
				$id = IDProviderFunctions::getFakeId();

			} else { // No valid option
				throw new Exception('Unknown type');
			}


		} catch (Exception $e) {
			$error = $e->getMessage();
		}

		// Build return array
		if ($id && !$error) {
			$this->getResult()->addValue( null, 'id', $id );
		} else {
			if (!$error) {
				$error = 'Unspecified error.';
			}
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
