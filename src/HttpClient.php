<?php

namespace MichielKempen\LaravelHttpClient;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Psr\Http\Message\ResponseInterface as Response;

class HttpClient
{
    /**
     * @var ClientInterface
     */
    protected $client;

	/**
	 * @var bool
	 */
	protected $handleExceptions;

	/**
	 * @param string $host
	 * @param array $headers
	 * @param bool $handleExceptions
	 */
    public function __construct(string $host, array $headers = [], bool $handleExceptions = true)
    {
        $this->client = new Client([
            'base_uri' => $host,
            'headers' => $headers
        ]);

		$this->handleExceptions = $handleExceptions;
	}

	/**
	 * @param Request $request
	 * @return HttpResponse
	 * @throws HttpException
	 */
    public function request(Request $request): HttpResponse
    {
    	switch ($request->method()) {
			case 'GET':
				return $this->get($request->path(), $request->all());
			case 'POST':
				return $this->post($request->path(), $request->all());
			case 'PUT':
				return $this->put($request->path(), $request->all());
			case 'PATCH':
				return $this->patch($request->path(), $request->all());
			case 'DELETE':
				return $this->delete($request->path(), $request->all());
			default:
				throw new HttpException("Unknown HTTP method '{$request->method()}'.", 500);
		}
    }

	/**
	 * @param string $url
	 * @param array $parameters
	 * @return HttpResponse
	 * @throws HttpException
	 */
    public function get(string $url, array $parameters = []): HttpResponse
    {
        try {
			$response = $this->client->get($url, [
				'query' => $parameters,
			]);
        } catch (RequestException $exception) {
			$response = $exception->getResponse();
            $this->handleException($response);
        }

		return new HttpResponse($response);
    }

	/**
	 * @param string $url
	 * @param array $payload
	 * @return HttpResponse
	 * @throws HttpException
	 */
	public function post(string $url, array $payload = []): HttpResponse
	{
		try {
			$response = $this->client->post($url, [
				'json' => $payload,
			]);
		} catch (RequestException $exception) {
			$response = $exception->getResponse();
			$this->handleException($response);
		}

		return new HttpResponse($response);
	}

	/**
	 * @param string $url
	 * @param array $payload
	 * @return HttpResponse
	 * @throws HttpException
	 */
    public function put(string $url, array $payload = []): HttpResponse
    {
        try {
            $response = $this->client->put($url, [
            	'json' => $payload,
            ]);
        } catch (RequestException $exception) {
			$response = $exception->getResponse();
            $this->handleException($response);
        }

        return new HttpResponse($response);
    }

	/**
	 * @param string $url
	 * @param array $payload
	 * @return HttpResponse
	 * @throws HttpException
	 */
    public function patch(string $url, array $payload = []): HttpResponse
    {
        try {
            $response = $this->client->patch($url, [
            	'json' => $payload,
            ]);
        } catch (RequestException $exception) {
			$response = $exception->getResponse();
            $this->handleException($response);
        }

        return new HttpResponse($response);
    }

	/**
	 * @param string $url
	 * @param array $parameters
	 * @return HttpResponse
	 * @throws HttpException
	 */
	public function delete(string $url, array $parameters = []): HttpResponse
	{
		try {
			$response = $this->client->delete($url, [
				'query' => $parameters,
			]);
		} catch (RequestException $exception) {
			$response = $exception->getResponse();
			$this->handleException($response);
		}

		return new HttpResponse($response);
	}

	/**
	 * @param Response $response
	 * @throws HttpException
	 */
    protected function handleException(Response $response): void
    {
    	if($this->handleExceptions) {
			$body = (string) $response->getBody();
			$code = (int) $response->getStatusCode();

			$content = json_decode($body);

			$message = isset($content->message) ? $content->message : 'API request not processed.';

			throw new HttpException($message, $code);
		}
    }
}