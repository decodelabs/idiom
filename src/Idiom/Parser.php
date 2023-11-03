<?php

/**
 * @package Chirp
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Idiom;

use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\Markup;

class Parser
{
    public const TAGS = [
        'a', 'abbr', 'b', 'br', 'cite', 'code', 'del', 'em',
        'i', 'img', 'ins', 'q', 'small', 'span', 'strong',
        'sub', 'sup', 'time', 'u', 'var'
    ];

    public const EXTENDED_TAGS = [
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li',
        'table', 'thead', 'tbody', 'tr', 'th', 'td',
        'dl', 'dt', 'dd',
        'p', 'br', 'hr', 'pre', 'blockquote',
        'address', 'figure', 'figcaption'
    ];

    public const CONTAINER_TAGS = [
        'ul', 'ol',
        'table', 'thead', 'tbody', 'tr'
    ];

    protected bool $inline = false;
    protected bool $extended = false;


    /**
     * Set inline
     *
     * @return $this
     */
    public function setInline(
        bool $inline
    ): static {
        $this->inline = $inline;
        return $this;
    }

    /**
     * Is inline
     */
    public function isInline(): bool
    {
        return $this->inline;
    }

    /**
     * Set extended
     *
     * @return $this
     */
    public function setExtended(
        bool $extended
    ): static {
        $this->extended = $extended;
        return $this;
    }

    /**
     * Is extended
     */
    public function isExtended(): bool
    {
        return $this->extended;
    }


    /**
     * Convert text to HTML
     */
    public function parse(
        ?string $content
    ): ?Markup {
        if (null === ($content = $this->prepareContent($content))) {
            return null;
        }

        if ($this->inline) {
            $output = $this->parseInline($content);
        } else {
            $output = $this->parseBlock($content);
        }

        return new Buffer($output);
    }

    /**
     * Prepare content ready for conversion
     */
    protected function prepareContent(
        ?string $content
    ): ?string {
        if ($content === null) {
            return null;
        }

        $content = trim($content);

        if (!strlen($content)) {
            return null;
        }

        $tags = [];
        $extended = $this->extended && !$this->inline;

        foreach (self::TAGS as $tag) {
            $tags[] = '<' . $tag . '>';
        }

        if ($extended) {
            foreach (self::EXTENDED_TAGS as $tag) {
                $tags[] = '<' . $tag . '>';
            }
        }

        // Strip tags
        $content = strip_tags($content, implode('', $tags));

        // Sort out spaces
        if (!$extended) {
            $content = preg_replace('/(\s) /', '$1&nbsp;', $content) ?? $content;
            $content = preg_replace('/ (\s)/', '&nbsp;$1', $content) ?? $content;
        }

        return $content;
    }


    /**
     * Parse as inline content
     */
    protected function parseInline(
        string $content
    ): string {
        return str_replace("\n", '<br />' . "\n", $content);
    }

    /**
     * Parse as block content
     */
    protected function parseBlock(
        string $content
    ): string {
        $content = rtrim($content) . "\n";
        $preTags = [];

        if (
            $this->extended &&
            strpos($content, '<pre') !== false
        ) {
            $parts = explode('</pre>', $content);
            $last = array_pop($parts);
            $content = '';
            $i = 0;

            foreach ($parts as $part) {
                if (false === ($start = strpos($part, '<pre'))) {
                    $content .= $part;
                    continue;
                }

                $name = '<pre st-placeholder-' . $i . '></pre>';
                $preTags[$name] = substr($part, $start) . '</pre>';
                $content .= substr($part, 0, $start) . $name;
                $i++;
            }

            $content .= $last;
        }

        if ($this->extended) {
            $blockReg = '(?:' . implode('|', self::EXTENDED_TAGS) . ')';
        } else {
            $blockReg = '(?:p)';
        }

        $containerReg = '(?:' . implode('|', self::CONTAINER_TAGS) . ')';

        // Double <br>s
        $content = preg_replace('|<br\s*/?>\s*<br\s*/?>|', "\n\n", $content) ?? $content;

        // Line break around block elements
        $content = preg_replace('!(<' . $blockReg . '[\s/>])!', "\n\n" . '$1', $content) ?? $content;
        $content = preg_replace('!(</' . $blockReg . '>)!', '$1' . "\n\n", $content) ?? $content;

        // Standardise new lines
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $content = preg_replace("/\n\n+/", "\n\n", $content) ?? $content;

        // Split
        $parts = preg_split('/\n\s*\n/', $content, -1, \PREG_SPLIT_NO_EMPTY);

        if ($parts !== false) {
            $content = '';

            foreach ($parts as $part) {
                $part = trim($part);

                if ($this->extended && !preg_match('!</?' . $containerReg . '[^>]*>!', $part)) {
                    // Sort out spaces
                    $part = preg_replace('/(\s) /', '$1&nbsp;', $part) ?? $part;
                    $part = preg_replace('/ (\s)/', '&nbsp;$1', $part) ?? $part;
                }

                if (!preg_match('!</?' . $blockReg . '[^>]*>!', $part)) {
                    $part = '<p>' . $part . '</p>';
                }

                $content .= $part . "\n";
            }
        }



        // Remove empties
        $content = preg_replace('|<p>\s*</p>|', '', $content) ?? $content;

        // Normalize blocks;
        $content = preg_replace('!<p>\s*(</?' . $blockReg . '[^>]*>)!', '$1', $content) ?? $content;
        $content = preg_replace('!(</?' . $blockReg . '[^>]*>)\s*</p>!', '$1', $content) ?? $content;

        // Normalize <br>s
        $content = str_replace(['<br>', '<br/>'], '<br />', $content);
        $content = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $content) ?? $content;
        $content = preg_replace('!(</?' . $blockReg . '[^>]*>)\s*<br />!', '$1', $content) ?? $content;
        $content = preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)!', '$1', $content) ?? $content;
        $content = preg_replace("|\n</p>$|", '</p>', $content) ?? $content;


        // Replace pres
        if (
            $this->extended &&
            !empty($preTags)
        ) {
            $content = str_replace(
                array_keys($preTags),
                array_values($preTags),
                $content
            );
        }

        return $content;
    }
}
