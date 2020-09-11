<?php

namespace MichielKempen\LaravelHttpClient;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use stdClass;

class HttpResponse implements Responsable
{
	private Response $response;

	public function __construct(Response $response)
	{
		$this->response = $response;
	}

	public function status(): int
	{
		return $this->response->getStatusCode();
	}

	public function toObject(): stdClass
	{
		$body = (string) $this->response->getBody();

		return json_decode($body) ?? new stdClass;
	}

	public function toArray(): array
	{
		$body = (string) $this->response->getBody();

		return json_decode($body, true) ?? [];
	}

	public function toString(): string
	{
		return (string) $this->response->getBody();
	}

	public function toResponse($request)
	{
		return new JsonResponse($this->toArray(), $this->status());
	}
}
