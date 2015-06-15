<?php namespace App\Exceptions;

use Illuminate\Http\Exception\HttpResponseException;

class ApiException extends HttpResponseException {

    public $httpOutput;
    public $httpCode;

	/**
	 * Create a new HTTP response exception instance.
	 *
	 * @param array $httpOutput
	 * @param int   $httpCode
	 * @return void
	 */
	public function __construct($httpOutput, $httpCode)
    {
        $this->httpOutput = $httpOutput;
        $this->httpCode   = $httpCode;
        parent::__construct(response()->json($httpOutput, $httpCode));
	}

}
