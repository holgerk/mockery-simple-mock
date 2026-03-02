<?php

namespace Holgerk\MockerySimpleMock;

use Mockery;
use Mockery\MockInterface;
use ReflectionClass;
use ReflectionMethod;

/**
 * @template T
 * @param class-string<T> $classString
 * @return T|MockInterface
 */
function simpleMock(string $classString, &$capturedCalls = null, $returnValues = []): MockInterface {
    $mock = Mockery::spy($classString);
    $reflectionClass = new ReflectionClass($classString);
    array_map(
        function (ReflectionMethod $method) use($mock, &$capturedCalls, $returnValues) {
            $methodName = $method->getName();
            $reflectionMethod = new ReflectionMethod($mock, $methodName);
            $reflectionParams = $reflectionMethod->getParameters();
            $expectation = $mock->allows($methodName);
            $expectation
                ->withArgs(function (...$args) use(&$capturedCalls, $reflectionParams, $methodName) {
                    $call = [];
                    foreach ($args as $index => $value) {
                        $keyName = isset($reflectionParams[$index])
                            ? $reflectionParams[$index]->getName()
                            : $index;
                        $call[$keyName] = $value;
                    }
                    $capturedCalls[$methodName][] = $call;
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
