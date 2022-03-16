# Idiom

[![PHP from Packagist](https://img.shields.io/packagist/php-v/decodelabs/idiom?style=flat)](https://packagist.org/packages/decodelabs/idiom)
[![Latest Version](https://img.shields.io/packagist/v/decodelabs/idiom.svg?style=flat)](https://packagist.org/packages/decodelabs/idiom)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/idiom.svg?style=flat)](https://packagist.org/packages/decodelabs/idiom)
[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/decodelabs/idiom/PHP%20Composer)](https://github.com/decodelabs/idiom/actions/workflows/php.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/idiom?style=flat)](https://packagist.org/packages/decodelabs/idiom)

Lightweight text to HTML parser - tags optional


## Installation

Install the library via composer:

```bash
composer require decodelabs/idiom
```

### PHP version

_Please note, the final v1 releases of all Decode Labs libraries will target **PHP8** or above._

Current support for earlier versions of PHP will be phased out in the coming months.


## Usage

Parse a block of text with optional HTML tags into rendered HTML:

```php
use DecodeLabs\Idiom\Parser;

$content = <<<CONTENT
Idiom allows you to write naturally, converting paragraphs, lines and spaces to the relevant HTML elements.

You can include <strong>tags</strong> where necessary.
CONTENT;

$parser = new Parser();
echo $parser->parse($content);
```

### Metamorph

Idiom also provides a [Metamorph](https://github.com/decodelabs/metamorph/) Handler so that it can be used via its simplified interface:

```php
use DecodeLabs\Metamorph;

echo Metamorph::idiom($myTweet);

// Also aliased in Metamorph as "SimpleTags"
echo Metamorph::simpleTags($myTweet);
```

## Output

The parsed HTML provided by Idiom is now wrapped in a <code>Markup</code> interface from the [Tagged](https://github.com/decodelabs/tagged/) library such that output is handled correctly in all rendering contexts.



## Licensing
Idiom is licensed under the MIT License. See [LICENSE](./LICENSE) for the full license text.
