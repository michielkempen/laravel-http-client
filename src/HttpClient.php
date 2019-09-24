<?php

namespace MichielKempen\LaravelHttpClient;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface as Response;
use Illuminate\Http\Request;

class HttpClient
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var bool
     */
    protected $handleExceptions = true;

    /**
     * @var bool
     */
    protected $returnRawResponse = false;

    /**
     * @param string $host
     * @param array $headers
     */
    public function __construct(string $host, array $headers = [])
    {
        $this->client = new Client([
            'base_uri' => $host,
            'headers' => $headers
        ]);
    }

    /**
     * @return HttpClient
     */
    public function withExceptionHandling(): self
    {
        $this->handleExceptions = true;

        return $this;
    }

    /**
     * @return HttpClient
     */
    public function withoutExceptionHandling(): self
    {
        $this->handleExceptions = false;

        return $this;
    }

    /**
     * @return HttpClient
     */
    public function returnRawResponse(): self
    {
        $this->returnRawResponse = true;

        return $this;
    }

    /**
     * @param Request $request
     * @return HttpResponse
     * @throws HttpException
     */
    public function request(Request $request)
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
     * @return HttpResponse|Response
     * @throws HttpException
     */
    public function get(string $url, array $parameters = [])
    {
        try {
            $response = $this->client->get($url, [
                'query' => $parameters,
            ]);
        } catch (RequestException $exception) {
            $response = $this->handleException($exception);
        }

        return $this->returnRawResponse ? $response : new HttpResponse($response);
    }

    /**
     * @param string $url
     * @param array $payload
     * @return HttpResponse|Response
     * @throws HttpException
     */
    public function post(string $url, array $payload = [])
    {
        try {
            $response = $this->client->post($url, [
                'json' => $payload,
            ]);
        } catch (RequestException $exception) {
            $response = $this->handleException($exception);
        }

        return $this->returnRawResponse ? $response : new HttpResponse($response);
    }

    /**
     * @param string $url
     * @param array $payload
     * @return HttpResponse|Response
     * @throws HttpException
     */
    public function put(string $url, array $payload = [])
    {
        try {
            $response = $this->client->put($url, [
                'json' => $payload,
            ]);
        } catch (RequestException $exception) {
            $response = $this->handleException($exception);
        }

        return $this->returnRawResponse ? $response : new HttpResponse($response);
    }

    /**
     * @param string $url
     * @param array $payload
     * @return HttpResponse|Response
     * @throws HttpException
     */
    public function patch(string $url, array $payload = [])
    {
        try {
            $response = $this->client->patch($url, [
                'json' => $payload,
            ]);
        } catch (RequestException $exception) {
            $response = $this->handleException($exception);
        }

        return $this->returnRawResponse ? $response : new HttpResponse($response);
    }

    /**
     * @param string $url
     * @param array $parameters
     * @return HttpResponse|Response
     * @throws HttpException
     */
    public function delete(string $url, array $parameters = [])
    {
        try {
            $response = $this->client->delete($url, [
                'query' => $parameters,
            ]);
        } catch (RequestException $exception) {
            $response = $this->handleException($exception);
        }

        return $this->returnRawResponse ? $response : new HttpResponse($response);
    }

    /**
     * @param RequestException $exception
     * @return Response
     * @throws HttpException
     */
    protected function handleException(RequestException $exception): Response
    {
        if(! $this->handleExceptions && $exception->hasResponse()) {
            return $exception->getResponse();
        }

        if($exception instanceof ConnectException) {
            throw new HttpException($exception->getHandlerContext()['error'], 500);
        }

        if($exception->hasResponse()) {
            $response = $exception->getResponse();

            $status = $response->getStatusCode();
            $payload = json_decode($response->getBody()->getContents(), true) ?? [];
            $message = $payload['message'] ?? 'API request not processed.';

            throw new HttpException($message, $status, $payload);
        }

        throw $exception;
    }
}
