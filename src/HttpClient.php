<?php

namespace MichielKempen\LaravelHttpClient;

use GuzzleHttp\Client as Http;
use GuzzleHttp\ClientInterface as HttpInterface;
use Illuminate\Http\Request;

class HttpClient
{
    /**
     * The underlying Guzzle HTTP client.
     */
    protected HttpInterface $http;

    /**
     * The request options.
     */
    protected array $options = [];

    /**
     * Create a new HTTP client.
     */
    public function __construct()
    {
        $this->http = new Http();
        $this->options = [
            'http_errors' => false,
        ];
    }

    /**
     * Create a new HTTP client.
     */
    public static function new(): self
    {
        return new static();
    }

    /**
     * Set the base URL for the pending request.
     */
    public function withBaseUrl(string $url): self
    {
        return tap($this, fn () => $this->options['base_uri'] = $url);
    }

    /**
     * Add the given headers to the pending request.
     */
    public function withHeaders(array $headers): self
    {
        return tap($this, fn () => array_merge_recursive($this->options, ['headers' => $headers]));
    }

    /**
     * Specify the authorization token for the pending request.
     */
    public function withToken(string $token, string $type = 'Bearer'): self
    {
        return tap($this, fn () => $this->options['headers']['Authorization'] = trim($type.' '.$token));
    }

    /**
     * Indicate that TLS certificates should not be verified.
     */
    public function withoutTlsVerification(): self
    {
        return tap($this, fn () => $this->options['verify'] = false);
    }

    /**
     * Specify the timeout (in seconds) for the pending request.
     */
    public function withTimeout(int $seconds): self
    {
        return tap($this, fn () => $this->options['timeout'] = $seconds);
    }

    /**
     * Attach query parameters to the pending request.
     */
    public function withQuery(array $parameters): self
    {
        return tap($this, fn () => $this->options['query'] = $parameters);
    }

    /**
     * Attach a json body to the pending request.
     */
    public function withJsonBody(array $body): self
    {
        return tap($this, fn () => $this->options['json'] = $body);
    }

    /**
     * Attach a multipart body to the pending request.
     */
    public function withMultipartBody(array $body): self
    {
        return tap($this, fn () => $this->options['multipart'] = $body);
    }

    /**
     * Forward the given Laravel request to a different host.
     */
    public function forward(Request $request): HttpResponse
    {
        $this->withHeaders($request->headers->all());

        if ($request->method() === 'GET') {
            return $this->withQuery($request->all())->get($request->path());
        }

        if ($request->method() === 'HEAD') {
            return $this->withQuery($request->all())->head($request->path());
        }

        if ($request->getContentType() === 'multipart/form-data') {
            $this->withMultipartBody($this->generateMultipartBody($request));
        } else {
            $this->withJsonBody($request->input());
        }

        switch ($request->method()) {
            case 'POST':
                return $this->post($request->path());
            case 'PUT':
                return $this->put($request->path());
            case 'PATCH':
                return $this->patch($request->path());
            case 'DELETE':
                return $this->delete($request->path());
            default:
                throw new HttpException("Unknown HTTP method '{$request->method()}'.", 500);
        }
    }

    /**
     * Send a GET request.
     */
    public function get(string $url): HttpResponse
    {
        return $this->send('GET', $url);
    }

    /**
     * Send a HEAD request.
     */
    public function head(string $url): HttpResponse
    {
        return $this->send('HEAD', $url);
    }

    /**
     * Send a POST request.
     */
    public function post(string $url): HttpResponse
    {
        return $this->send('POST', $url);
    }

    /**
     * Send a PUT request.
     */
    public function put(string $url): HttpResponse
    {
        return $this->send('PUT', $url);
    }

    /**
     * Send a PATCH request.
     */
    public function patch(string $url): HttpResponse
    {
        return $this->send('PATCH', $url);
    }

    /**
     * Send a DELETE request.
     */
    public function delete(string $url): HttpResponse
    {
        return $this->send('DELETE', $url);
    }

    /**
     * Parse the Laravel request to a multipart payload.
     */
    protected function generateMultipartBody(Request $request): array
    {
        $multipart = [];

        foreach ($request->input() as $field => $value) {
            $multipart[] = [
                'name'     => $field,
                'contents' => $value,
            ];
        }

        foreach ($request->allFiles() as $field => $files) {
            $files = is_array($files) ? $files : [$files];
            foreach ($files as $file) {
                $multipart[] = [
                    'name'     => $field,
                    'contents' => fopen($file->path(), 'r'),
                    'filename' => $file->getClientOriginalName(),
                ];
            }
        }

        return $multipart;
    }

    /**
     * Send the pending request using the given HTTP method.
     */
    protected function send(string $method, string $url): HttpResponse
    {
        $response = $this->http->request($method, $url, $this->options);

        return new HttpResponse($response);
    }
}
