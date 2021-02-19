<?php

namespace MichielKempen\LaravelHttpClient;

use GuzzleHttp\Exception\ConnectException;
use Orchestra\Testbench\TestCase;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class HttpClientTest extends TestCase
{
    /*
     * TEST DIFFERENT RESPONSE CODES
     */

    /** @test */
    public function it_can_handle_200_responses(): void
    {
        $response = HttpClient::new()
            ->withBaseUrl('https://jsonplaceholder.typicode.com')
            ->get('todos/1');

        $this->assertInstanceOf(HttpResponse::class, $response);
        $this->assertInstanceOf(SymfonyResponse::class, $response->toResponse());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->clientErrorOccurred());
        $this->assertFalse($response->serverErrorOccurred());
        $this->assertFalse($response->errorOccurred());
    }

    /** @test */
    public function it_can_handle_300_responses(): void
    {
        $response = HttpClient::new()
            ->withBaseUrl('https://httpstat.us')
            ->get('301');

        $this->assertInstanceOf(HttpResponse::class, $response);
        $this->assertInstanceOf(SymfonyResponse::class, $response->toResponse());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->clientErrorOccurred());
        $this->assertFalse($response->serverErrorOccurred());
        $this->assertFalse($response->errorOccurred());
    }

    /** @test */
    public function it_can_handle_400_responses(): void
    {
        $response = HttpClient::new()
            ->withBaseUrl('https://httpstat.us')
            ->get('404');

        $this->assertInstanceOf(HttpResponse::class, $response);
        $this->assertInstanceOf(SymfonyResponse::class, $response->toResponse());

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->clientErrorOccurred());
        $this->assertFalse($response->serverErrorOccurred());
        $this->assertTrue($response->errorOccurred());
    }

    /** @test */
    public function it_can_handle_500_responses(): void
    {
        $response = HttpClient::new()
            ->withBaseUrl('https://httpstat.us')
            ->get('500');

        $this->assertInstanceOf(HttpResponse::class, $response);
        $this->assertInstanceOf(SymfonyResponse::class, $response->toResponse());

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->clientErrorOccurred());
        $this->assertTrue($response->serverErrorOccurred());
        $this->assertTrue($response->errorOccurred());
    }

    /*
     * TEST DIFFERENT RESPONSE TYPES
     */

    /** @test */
    public function it_can_handle_json_responses(): void
    {
        $response = HttpClient::new()
            ->withBaseUrl('https://jsonplaceholder.typicode.com')
            ->get('todos/1');

        $this->assertEquals('application/json; charset=utf-8', $response->getContentType());
        $this->assertTrue($response->containsJson());

        $body = "{\n  \"userId\": 1,\n  \"id\": 1,\n  \"title\": \"delectus aut autem\",\n  \"completed\": false\n}";
        $object = (object) ['userId' => 1, 'id' => 1, 'title' => 'delectus aut autem', 'completed' => false];
        $array = ['userId' => 1, 'id' => 1, 'title' => 'delectus aut autem', 'completed' => false];

        $this->assertEquals($body, $response->getBody());
        $this->assertEquals($object, $response->toObject());
        $this->assertEquals($array, $response->toArray());
    }

    /** @test */
    public function it_can_handle_html_responses(): void
    {
        $response = HttpClient::new()
            ->withBaseUrl('https://httpstat.us')
            ->get('');

        $this->assertEquals('text/html; charset=utf-8', $response->getContentType());
        $this->assertFalse($response->containsJson());

        $this->assertStringContainsString('<html>', $response->getBody());
    }

    /** @test */
    public function it_can_handle_image_responses(): void
    {
        $response = HttpClient::new()
            ->withBaseUrl('https://images.unsplash.com')
            ->get('photo-1613685759501-489a44136fbc');

        $this->assertEquals('image/jpeg', $response->getContentType());
        $this->assertFalse($response->containsJson());
    }

    /** @test */
    public function it_can_handle_empty_responses(): void
    {
        $response = HttpClient::new()
            ->withBaseUrl('https://httpstat.us')
            ->get('404');

        $this->assertNull($response->getContentType());
        $this->assertFalse($response->containsJson());

        $this->assertEquals('', $response->getBody());
        $this->assertEquals((object) [], $response->toObject());
        $this->assertEquals([], $response->toArray());
    }

    /*
     * TEST DIFFERENT REQUEST METHODS
     */

    /** @test */
    public function it_can_send_a_get_request(): void
    {
        $response = HttpClient::new()
            ->withBaseUrl('https://reqres.in')
            ->withQuery(['page' => 2])
            ->get('/api/users');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function it_can_send_a_head_request(): void
    {
        $response = HttpClient::new()
            ->withBaseUrl('https://reqres.in')
            ->withQuery(['page' => 2])
            ->head('/api/users');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function it_can_send_a_post_request(): void
    {
        $response = HttpClient::new()
            ->withBaseUrl('https://reqres.in')
            ->withJsonBody(['name' => 'morpheus', 'job' => 'leader'])
            ->post('/api/users');

        $this->assertEquals(201, $response->getStatusCode());
    }

    /** @test */
    public function it_can_send_a_put_request(): void
    {
        $response = HttpClient::new()
            ->withBaseUrl('https://reqres.in')
            ->withJsonBody(['name' => 'morpheus', 'job' => 'zion resident'])
            ->put('/api/users/2');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function it_can_send_a_patch_request(): void
    {
        $response = HttpClient::new()
            ->withBaseUrl('https://reqres.in')
            ->withJsonBody(['name' => 'morpheus', 'job' => 'zion resident'])
            ->patch('/api/users/2');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function it_can_send_a_delete_request(): void
    {
        $response = HttpClient::new()
            ->withBaseUrl('https://reqres.in')
            ->delete('/api/users/2');

        $this->assertEquals(204, $response->getStatusCode());
    }

    /*
     * TEST TIMEOUT
     */

    /** @test */
    public function it_throws_an_exception_when_the_timeout_is_exceeded(): void
    {
        $this->expectException(ConnectException::class);

        HttpClient::new()
            ->withBaseUrl('https://reqres.in')
            ->withTimeout(2)
            ->withQuery(['delay' => 5])
            ->get('/api/users');
    }

    /*
     * TEST HEADERS
     */

    // TODO

    /*
     * TEST TLS VERIFICATION
     */

    // TODO

    /*
     * TEST TOKEN
     */

    // TODO

    /*
     * TEST MULTIPART REQUESTS
     */

    // TODO

    /*
     * TEST REQUEST FORWARDING
     */

    // TODO
}
