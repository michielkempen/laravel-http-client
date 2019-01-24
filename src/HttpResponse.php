<?php

namespace MichielKempen\HttpClient;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Psr\Http\Message\ResponseInterface as Response;
use stdClass;

class HttpResponse implements Responsable
{
	/**
	 * @var Response
	 */
	private $response;

	/**
	 * ApiResponse constructor.
	 *
	 * @param Response $response
	 */
	public function __construct(Response $response)
	{
		$this->response = $response;
	}

	/**
	 * @return int
	 */
	public function status(): int
	{
		$status = $this->response->getStatusCode();

		return $status;
	}

	/**
	 * @return stdClass
	 */
	public function toObject(): stdClass
	{
		$body = (string) $this->response->getBody();

		$content = json_decode($body) ?? new stdClass;

		return $content;
	}

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		$body = (string) $this->response->getBody();

		$content = json_decode($body, true) ?? [];

		return $content;
	}

	/**
	 * Create an HTTP response that represents the object.
	 *
	 * @param  Request $request
	 * @return JsonResponse
	 */
	public function toResponse($request)
	{
		return new JsonResponse($this->toArray(), $this->status());
	}
}