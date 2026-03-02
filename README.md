# mockery-simple-mock

[![Tests](https://github.com/holgerk/mockery-simple-mock/actions/workflows/tests.yml/badge.svg)](https://github.com/holgerk/mockery-simple-mock/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/holgerk/mockery-simple-mock.svg)](https://packagist.org/packages/holgerk/mockery-simple-mock)
[![PHP Version](https://img.shields.io/packagist/php-v/holgerk/mockery-simple-mock.svg)](https://packagist.org/packages/holgerk/mockery-simple-mock)
[![License](https://img.shields.io/packagist/l/holgerk/mockery-simple-mock.svg)](https://packagist.org/packages/holgerk/mockery-simple-mock)

A single helper function that wraps [Mockery](https://github.com/mockery/mockery) to make mocking and call capturing straightforward.

## Installation

```bash
composer require holgerk/mockery-simple-mock
```

## Usage

```php
use function Holgerk\MockerySimpleMock\simpleMock;
```

### Basic mock

```php
$mock = simpleMock(MyService::class);
```

### Capture calls

Pass a variable by reference to collect every call made to any public method,
keyed by method name and then by parameter name:

```php
$mock = simpleMock(MyService::class, $capturedCalls);

$mock->greet('Alice', 42);

// $capturedCalls['greet'] === [['name' => 'Alice', 'age' => 42]]
```

Multiple calls to the same method are appended in order:

```php
$mock->greet('Alice', 1);
$mock->greet('Bob', 2);

// $capturedCalls['greet'] === [
//     ['name' => 'Alice', 'age' => 1],
//     ['name' => 'Bob', 'age' => 2],
// ]
```

### Asserting calls with assertEquals

A key advantage of captured calls over traditional mock expectations is that
`assertEquals` shows you a full diff between what you expected and what actually
happened. With expectation-style assertions you typically only learn that a call
was wrong; with captured calls you see exactly which argument differed and by how
much.

```php
$mock = simpleMock(MyService::class, $capturedCalls);

$mock->greet('Alice', 42);
$mock->greet('Bob', 7);

assertEquals([
    ['name' => 'Alice', 'age' => 42],
    ['name' => 'Bob',   'age' => 7],
], $capturedCalls['greet']);
```

> **Tip:** Writing out the expected array by hand can be tedious. My
> [holgerk/assert-golden](https://github.com/holgerk/assert-golden) package
> removes that step by recording the actual value directly into your source code
> on the first run, turning it into the expectation automatically.

When a call is wrong, PHPUnit prints a structured diff like:

```
Failed asserting that two arrays are equal.
--- Expected
+++ Actual
@@ @@
 [
     ['name' => 'Alice', 'age' => 42],
-    ['name' => 'Bob', 'age' => 7],
+    ['name' => 'Bob', 'age' => 99],
 ]
```

### Configure return values

Pass an array of `methodName => returnValue` pairs as the third argument:

```php
$mock = simpleMock(MyService::class, $capturedCalls, [
    'greet' => 'Hello, Alice!',
]);

$result = $mock->greet('Alice', 42); // 'Hello, Alice!'
```

## Requirements

- PHP >= 8.1
- mockery/mockery >= 1.5
