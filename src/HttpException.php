<?php

namespace MichielKempen\LaravelHttpClient;

use Illuminate\Http\JsonResponse;

class HttpException extends \Exception
{
    /**
     * @var null|array
     */
    private $payload;

    /**
     * Exception constructor.
     *
     * @param string $message
     * @param int $statusCode
     * @param array $payload
     */
    public function __construct(string $message, int $statusCode = 0, ?array $payload = null)
    {
        parent::__construct($message, $statusCode);

        $this->payload = $payload;
    }

    /**
     * @return array|null
     */
    public function getPayload(): ?array
    {
        return $this->payload;
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @return JsonResponse
     */
    public function render()
    {
        return new JsonResponse(
            $this->payload ?? ['message' => $this->getMessage(), 'status' => $this->getCode()],
            $this->getCode()
        );
    }
}
