# Idiom

[![PHP from Packagist](https://img.shields.io/packagist/php-v/decodelabs/idiom?style=flat)](https://packagist.org/packages/decodelabs/idiom)
[![Latest Version](https://img.shields.io/packagist/v/decodelabs/idiom.svg?style=flat)](https://packagist.org/packages/decodelabs/idiom)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/idiom.svg?style=flat)](https://packagist.org/packages/decodelabs/idiom)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/decodelabs/idiom/integrate.yml?branch=develop)](https://github.com/decodelabs/idiom/actions/workflows/integrate.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/idiom?style=flat)](https://packagist.org/packages/decodelabs/idiom)

### Lightweight text to HTML parser - tags optional

Idiom provides a simple and easy to use text parser that can convert natural language with an optional subset of HTML tags to full markup ready for consumption.

_Get news and updates on the [DecodeLabs blog](https://blog.decodelabs.com)._

---


## Installation

Install the library via composer:

```bash
composer require decodelabs/idiom
```

## Usage

Parse a block of text with optional HTML tags into rendered HTML:

```php
use DecodeLabs\Idiom\Parser;

$content = <<<CONTENT
Idiom allows you to write naturally, converting paragraphs, lines and spaces to the relevant HTML elements.

You can include <strong>tags</strong> where necessary.

<ul>
    <li>Block elements like this can be used if extended mode is enabled</li>
    <li>Alternatively, content can be rendered as inline HTML with inline mode</li>
</ul>
CONTENT;

$parser = new Parser();
$parser->setExtended(true);
echo $parser->parse($content);
```

### Metamorph

Idiom also provides a [Metamorph](https://github.com/decodelabs/metamorph/) Handler so that it can be used via its simplified interface:

```php
use DecodeLabs\Metamorph;

echo Metamorph::{'idiom.extended'}($content);
```

## Output

The parsed HTML provided by Idiom is wrapped in a <code>Markup</code> interface from the [Tagged](https://github.com/decodelabs/tagged/) library such that output is handled correctly in all rendering contexts.


### Safety

Please note, Idiom does not yet exhaustively treat input for malicious entities - this parser should only be used with content considered _safe_.


## Licensing
Idiom is licensed under the MIT License. See [LICENSE](./LICENSE) for the full license text.
