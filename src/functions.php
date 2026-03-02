<?php

namespace Holgerk\MockerySimpleMock;

use Mockery;
use Mockery\MockInterface;
use ReflectionClass;
use ReflectionMethod;

/**
 * Creates a Mockery spy for the given class or interface that automatically
 * captures all calls to public methods. Each call is recorded in $capturedCalls
 * keyed by method name and then by parameter name, making assertions easy to
 * read. Return values for specific methods can be configured via $returnValues.
 *
 * @template T
 * @param class-string<T> $classString
 * @param array<string, list<array<string, mixed>>>|null $capturedCalls
 * @param array<string, mixed> $returnValues
 * @return T|MockInterface
 */
function simpleMock(string $classString, &$capturedCalls = null, $returnValues = []): MockInterface {
    // spy allows calls without explicit expectations, unlike a strict mock
    $mock = Mockery::spy($classString);
    $reflectionClass = new ReflectionClass($classString);
    array_map(
        function (ReflectionMethod $method) use($mock, &$capturedCalls, $returnValues) {
            $methodName = $method->getName();
            // reflect the mock to get the actual parameter list
            $reflectionMethod = new ReflectionMethod($mock, $methodName);
            $reflectionParams = $reflectionMethod->getParameters();
            $expectation = $mock->allows($methodName);
            $expectation
                ->withArgs(function (...$args) use(&$capturedCalls, $reflectionParams, $methodName) {
                    // record each call keyed by parameter name for readability;
                    // fall back to numeric index for variadic overflow
                    $call = [];
                    foreach ($args as $index => $value) {
                        $keyName = isset($reflectionParams[$index])
                            ? $reflectionParams[$index]->getName()
                            : $index;
                        $call[$keyName] = $value;
                    }
                    $capturedCalls[$methodName][] = $call;
                    // must return true to signal that the arguments are accepted
                    return true;
                });
            if (isset($returnValues[$methodName])) {
                $expectation
                    ->andReturn($returnValues[$methodName]);
            }
        },
        $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC)
    );
    return $mock;
}
