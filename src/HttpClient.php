<?php

namespace MichielKempen\LaravelHttpClient;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
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
            $this->handleException($exception);
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
            $this->handleException($exception);
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
            $this->handleException($exception);
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
            $this->handleException($exception);
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
            $this->handleException($exception);
        }

        return new HttpResponse($response);
    }

    /**
     * @param Response $response
     * @throws HttpException
     */
    protected function handleException(RequestException $exception): void
    {
        if(! $this->handleExceptions) {
            return;
        }

        if($exception instanceof ConnectException) {
            throw new HttpException($exception->getHandlerContext()['error'], 500);
        }

        if($exception instanceof ClientException || $exception instanceof ServerException) {
            $response = $exception->getResponse();

            $status = $response->getStatusCode();
            $payload = json_decode($response->getBody()->getContents(), true) ?? [];
            $message = $payload['message'] ?? 'API request not processed.';

            throw new HttpException($message, $status, $payload);
        }

        throw $exception;
    }
}
