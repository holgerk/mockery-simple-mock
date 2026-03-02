<?php

namespace Holgerk\MockerySimpleMock\Tests;

use Mockery\MockInterface;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use function Holgerk\MockerySimpleMock\simpleMock;

class SimpleMockTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_returns_mock_interface(): void
    {
        $mock = simpleMock(SomeService::class);

        self::assertInstanceOf(MockInterface::class, $mock);
    }

    public function test_captures_calls_with_named_parameters(): void
    {
        $mock = simpleMock(SomeService::class, $capturedCalls);

        $mock->greet('Alice', 42);

        self::assertSame([['name' => 'Alice', 'age' => 42]], $capturedCalls['greet']);
    }

    public function test_captures_multiple_calls(): void
    {
        $mock = simpleMock(SomeService::class, $capturedCalls);

        $mock->greet('Alice', 1);
        $mock->greet('Bob', 2);

        self::assertSame([
            ['name' => 'Alice', 'age' => 1],
            ['name' => 'Bob', 'age' => 2],
        ], $capturedCalls['greet']);
    }

    public function test_captures_calls_to_multiple_methods(): void
    {
        $mock = simpleMock(SomeService::class, $capturedCalls);

        $mock->greet('Alice', 1);
        $mock->process(99);

        self::assertSame([['name' => 'Alice', 'age' => 1]], $capturedCalls['greet']);
        self::assertSame([['id' => 99]], $capturedCalls['process']);
    }

    public function test_returns_configured_return_value(): void
    {
        $mock = simpleMock(SomeService::class, $capturedCalls, [
            'greet' => 'Hello, Alice!',
        ]);

        $result = $mock->greet('Alice', 42);

        self::assertSame('Hello, Alice!', $result);
    }

    public function test_returns_null_by_default(): void
    {
        $mock = simpleMock(SomeService::class);

        $result = $mock->greet('Alice', 42);

        self::assertNull($result);
    }

    public function test_captured_calls_is_null_when_not_passed(): void
    {
        $mock = simpleMock(SomeService::class);
        $mock->greet('Alice', 42);

        // No assertion needed — just verifying no error is thrown when
        // $capturedCalls is omitted.
        self::assertInstanceOf(MockInterface::class, $mock);
    }

    public function test_works_with_interface(): void
    {
        $mock = simpleMock(SomeInterface::class, $capturedCalls, [
            'compute' => 7,
        ]);

        $result = $mock->compute(3, 4);

        self::assertSame(7, $result);
        self::assertSame([['a' => 3, 'b' => 4]], $capturedCalls['compute']);
    }
}

// ---------------------------------------------------------------------------
// Fixtures
// ---------------------------------------------------------------------------

class SomeService
{
    public function greet(string $name, int $age): ?string
    {
        return "Hello, $name! You are $age years old.";
    }

    public function process(int $id): void {}
}

interface SomeInterface
{
    public function compute(int $a, int $b): int;
}
