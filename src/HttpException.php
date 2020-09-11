<?php

namespace MichielKempen\LaravelHttpClient;

use Illuminate\Http\JsonResponse;

class HttpException extends \Exception
{
    private ?array $payload;

    public function __construct(string $message, int $statusCode = 0, ?array $payload = null)
    {
        parent::__construct($message, $statusCode);

        $this->payload = $payload;
    }

    public function getPayload(): ?array
    {
        return $this->payload;
    }

    public function render(): JsonResponse
    {
        return new JsonResponse(
            $this->payload ?? ['message' => $this->getMessage(), 'status' => $this->getCode()],
            $this->getCode()
        );
    }
}
