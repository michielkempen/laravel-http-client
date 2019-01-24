<?php

namespace MichielKempen\LaravelHttpClient;

use Illuminate\Http\JsonResponse;

class HttpException extends \Exception
{
	/**
	 * Exception constructor.
	 *
	 * @param string $message
	 * @param int $statusCode
	 */
	public function __construct(string $message, int $statusCode)
	{
		parent::__construct($message, $statusCode);
	}

	/**
	 * Render the exception into an HTTP response.
	 *
	 * @return JsonResponse
	 */
	public function render()
	{
		return new JsonResponse([
			'message' => $this->getMessage(),
			'status' => $this->getCode(),
		], $this->getCode());
	}
}