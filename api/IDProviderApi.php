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

        try {
            $id = IDProviderFunctions::getId($params);
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
