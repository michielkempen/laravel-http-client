<?php

namespace MichielKempen\LaravelHttpClient;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface as Response;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class HttpResponse implements Responsable
{
    /**
     * The underlying PSR response.
     */
    protected Response $response;

    /**
     * Create a new HTTP response.
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Get the status code of the response.
     */
    public function getStatusCode(): int
    {
        return (int) $this->response->getStatusCode();
    }

    /**
     * Determine if the request was successful.
     */
    public function isSuccessful(): bool
    {
        return $this->getStatusCode() >= 200 && $this->getStatusCode() < 300;
    }

    /**
     * Determine if a client error occurred.
     */
    public function clientErrorOccurred(): bool
    {
        return $this->getStatusCode() >= 400 && $this->getStatusCode() < 500;
    }

    /**
     * Determine if a server error occurred.
     */
    public function serverErrorOccurred(): bool
    {
        return $this->getStatusCode() >= 500;
    }

    /**
     * Determine if a client or server error occurred.
     */
    public function errorOccurred(): bool
    {
        return $this->serverErrorOccurred() || $this->clientErrorOccurred();
    }

    /**
     * Get the content type of the response.
     */
    public function getContentType(): ?string
    {
        $header = $this->response->getHeaderLine('Content-Type');

        return $header === '' ? null : $header;
    }

    /**
     * Determine if the content type of the response is JSON.
     */
    public function containsJson(): bool
    {
        return Str::contains($this->getContentType(), 'application/json');
    }

    /**
     * Get the body of the response.
     */
    public function getBody(): string
    {
        return (string) $this->response->getBody();
    }

    /**
     * Get the JSON decoded body of the response as an object.
     */
    public function toObject(): object
    {
        $body = (string) $this->response->getBody();

        return json_decode($body) ?? (object) [];
    }

    /**
     * Get the JSON decoded body of the response as an array.
     */
    public function toArray(): array
    {
        $body = (string) $this->response->getBody();

        return json_decode($body, true) ?? [];
    }

    /**
     * Get the Symfony representation of the response.
     */
    public function toResponse($request = null): SymfonyResponse
    {
        return (new HttpFoundationFactory())->createResponse($this->response);
    }

    /**
     * Get the body of the response.
     */
    public function __toString(): string
    {
        return $this->getBody();
    }
}
