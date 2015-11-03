<?php
class IDProviderApi extends ApiQueryBase {


    /**
     * Description of the allowed parameters
     * @return array
     */
    public function getAllowedParams() {
        return array(
            'type' => array(
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => true,
            ),
            'prefix' => array(
                ApiBase::PARAM_TYPE => 'string',
            ),
			'padding' => array(
				ApiBase::PARAM_TYPE => 'integer',
			),
            'wikipage' => array(
                ApiBase::PARAM_TYPE => 'boolean',
            ),
        );
    }


    /**
     *
     */
	public function execute() {

		$params = $this->extractRequestParams();

        $id = null;
        $error = null;

        $wikipage = $params['wikipage'] ?: false;

        try {
            $id = $this->getId($params);
        } catch (Exception $e) {
			$error = $e->getMessage();
		}

        // Build return array
        if ($id && !$error) {
			$response = array(
                'id' => $id,
            );
        } else {
			if (!$error) {
				$error = 'Unspecified error.';
			}
            $response = array(
                'error' => $error,
            );
        }

		$this->getResult()->addValue( null, $this->getModuleName(), $response );
	}

	/**
	 * Calls the corresponding provider function according to the given type
	 *
	 * @param $type
	 * @param $prefix
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
    public function getId($params) {

		if (!isset($params['type'])) {
			throw new Exception('No type declared');
		}

		$type = $params['type'];

		// UUID Provider
        if ($type === 'uuid') {
			return $prefix . IDProviderFunctions::getUUID();

		// Increment Provider
		} else if ($type === 'increment') {
			return IDProviderFunctions::getIncrement($params['prefix'], $params['padding']);

		// No valid option
        } else {
            throw new Exception('Unknown type');
        }

    }

    /**
     * Some example queries
     *
     * @TODO
     *
     * @return array
     */
	protected function getExamplesMessages() {
		return array(
			'action=query&list=example'
				=> 'apihelp-query+example-example-1',
			'action=query&list=example&key=do'
				=> 'apihelp-query+example-example-2',
		);
	}
}
