<?php
class IDProviderApi extends ApiQueryBase {

    /**
     * Constructor is optional. Only needed if we give
     * this module properties a prefix (in this case we're using
     * "ex" as the prefix for the module's properties.
     * Query modules have the convention to use a property prefix.
     * Base modules generally don't use a prefix, and as such don't
     * need the constructor in most cases.
     *
     * @param ApiQuery $query
     * @param string $moduleName
     */
	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'idp' );
	}

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

        $prefix = $params['prefix'] ?: '';
        $type = $params['type'] ?: 'randomid';
        $wikipage = $params['wikipage'] ?: false;


        $r = array(
            'type' => $type,
            'prefix' => $prefix,
        );



		$this->getResult()->addValue( null, $this->getModuleName(), $r );
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
