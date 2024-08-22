<?php

/**
 * @package Metamorph
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Metamorph\Handler;

use DecodeLabs\Coercion;
use DecodeLabs\Idiom\Parser;
use DecodeLabs\Metamorph\MacroHandler;
use DecodeLabs\Metamorph\MacroHandlerTrait;
use DecodeLabs\Tagged\Buffer;
use Stringable;

class Idiom implements MacroHandler
{
    use MacroHandlerTrait;
    use HtmlTrait;

    protected const Macros = [
        'extended' => [
            'extended' => true
        ],
        'inline' => [
            'inline' => true
        ]
    ];

    protected bool $inline = false;
    protected bool $extended = false;


    /**
     * Set options
     *
     * @param array<string, mixed> $options
     */
    public function __construct(
        array $options
    ) {
        $this->inline = Coercion::toBool($options['inline'] ?? $this->inline);
        $this->extended = Coercion::toBool($options['extended'] ?? $this->extended);
        $this->resolveUrls = Coercion::toBool($options['resolveUrls'] ?? $this->resolveUrls);
    }


    /**
     * Convert markdown to HTML
     */
    public function convert(
        string $content,
        ?callable $setup = null
    ): string|Stringable|null {
        $parser = new Parser();
        $parser->setInline($this->inline);
        $parser->setExtended($this->extended);

        if ($setup) {
            $setup($parser);
        }

        $output = $parser->parse($content);
        $output = $this->resolveHtmlUrls((string)$output);

        return new Buffer($output);
    }
}
