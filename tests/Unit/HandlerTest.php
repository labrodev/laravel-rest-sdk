<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Labrodev\RestSdk\Exceptions\RequestAttemptLimitReached;
use Labrodev\RestSdk\Exceptions\RequestFailed;
use Labrodev\RestSdk\Services\Handler;
use Labrodev\RestSdk\Tests\Fixtures\ItemPayloadType;

const API_URL = 'https://api.example.test';

function captureRequestOptions(Handler $handler): array
{
    $capturedOptions = [];

    Http::fake(function (Request $request, array $options) use (&$capturedOptions) {
        $capturedOptions = $options;

        return Http::response(['data' => []]);
    });

    $handler->run(payloadType: ItemPayloadType::Show);

    return $capturedOptions;
}

it('applies the configured connect and request timeouts', function () {
    $options = captureRequestOptions(new Handler(apiUrl: API_URL, connectTimeout: 2, timeout: 5));

    expect($options['connect_timeout'])->toBe(2)
        ->and($options['timeout'])->toBe(5);
});

it('keeps the laravel default timeouts when none are configured', function () {
    $options = captureRequestOptions(new Handler(apiUrl: API_URL));

    expect($options['connect_timeout'])->toBe(10)
        ->and($options['timeout'])->toBe(30);
});

it('returns the retried response after a request timeout', function () {
    Http::fake([
        '*' => Http::sequence()
            ->push(['error' => 'timeout'], 408)
            ->push(['data' => ['id' => 1]], 200),
    ]);

    $response = (new Handler(apiUrl: API_URL, retryDelaySeconds: 0))
        ->run(payloadType: ItemPayloadType::Show);

    expect($response->status())->toBe(200)
        ->and($response->json('data.id'))->toBe(1);

    Http::assertSentCount(2);
});

it('retries a request timeout no more than the attempts limit', function () {
    Http::fake([
        '*' => Http::sequence()
            ->push(status: 408)
            ->push(status: 408)
            ->push(status: 408)
            ->push(status: 200),
    ]);

    $handler = new Handler(apiUrl: API_URL, requestLimitAttempts: 2, retryDelaySeconds: 0);

    expect(fn () => $handler->run(payloadType: ItemPayloadType::Show))
        ->toThrow(RequestAttemptLimitReached::class);

    Http::assertSentCount(3);
});

it('does not retry a request timeout when the attempts limit is zero', function () {
    Http::fake(['*' => Http::response(status: 408)]);

    $handler = new Handler(apiUrl: API_URL, requestLimitAttempts: 0, retryDelaySeconds: 0);

    expect(fn () => $handler->run(payloadType: ItemPayloadType::Show))
        ->toThrow(RequestAttemptLimitReached::class);

    Http::assertSentCount(1);
});

it('waits the configured delay before retrying a request timeout', function () {
    Http::fake([
        '*' => Http::sequence()
            ->push(status: 408)
            ->push(status: 200),
    ]);

    $startedAt = microtime(true);

    (new Handler(apiUrl: API_URL, retryDelaySeconds: 1))->run(payloadType: ItemPayloadType::Show);

    expect(microtime(true) - $startedAt)->toBeGreaterThanOrEqual(1.0);
});

it('throws request failed for other unsuccessful statuses without retrying', function () {
    Http::fake(['*' => Http::response(status: 500)]);

    expect(fn () => (new Handler(apiUrl: API_URL))->run(payloadType: ItemPayloadType::Show))
        ->toThrow(RequestFailed::class);

    Http::assertSentCount(1);
});
