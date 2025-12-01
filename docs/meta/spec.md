# Idiom — Package Specification

> **Cluster:** `content`
> **Language:** `php`
> **Milestone:** `m6`
> **Repo:** `https://github.com/decodelabs/idiom`
> **Role:** SimpleTags parser

This document describes the purpose, contracts, and design of **Idiom** within the Decode Labs ecosystem.

It is aimed at:

- Developers **using** Idiom in their own applications or libraries.
- Contributors **maintaining or extending** Idiom.
- Tools and AI assistants that need to reason about its behaviour.

---

## 1. Overview

### 1.1 Purpose

Idiom provides a lightweight text-to-HTML parser that converts natural language text with an optional subset of HTML tags into full markup ready for consumption. It allows users to write naturally, automatically converting paragraphs, line breaks, and spaces to relevant HTML elements while preserving allowed inline and block HTML tags. The parser supports both inline and block modes, with an extended mode that enables additional block-level HTML elements. Idiom integrates with Tagged for markup output and Metamorph for content transformation workflows.

### 1.2 Non-Goals

Idiom does **not**:

- Provide a full-featured markdown parser (see other packages for markdown support)
- Perform exhaustive security sanitization (input should be considered safe)
- Support all HTML tags (only a curated subset of inline and block tags)
- Handle complex HTML structures or nested block elements extensively
- Provide WYSIWYG editing capabilities
- Handle HTML entity encoding/decoding beyond basic operations

---

## 2. Role in the Ecosystem

### 2.1 Cluster & Positioning

- **Cluster:** `content` (see Chorus taxonomy)
- Idiom is a content transformation package that sits between raw text input and rendered HTML output. It builds on Tagged for markup generation and integrates with Metamorph for content transformation pipelines. It provides a simpler alternative to full markdown parsers for use cases where only basic formatting is needed.

### 2.2 Typical Usage Contexts

Typical places Idiom appears:

- User-generated content that needs basic HTML formatting
- Simple text-to-HTML conversion workflows
- Content transformation pipelines via Metamorph
- Places where full markdown is overkill but basic formatting is needed
- Converting plain text with minimal HTML tags to structured markup

Idiom is intended to be used whenever you need to convert natural language text with optional HTML tags into properly structured HTML markup without the complexity of a full markdown parser.

---

## 3. Public Surface

> This section focuses on the conceptual API, not every symbol.

### 3.1 Key Types

The primary public types are:

- `DecodeLabs\Idiom\Parser`
  The main parser class that converts text to HTML. Supports inline and extended modes, with configurable tag whitelisting.

- `DecodeLabs\Metamorph\Handler\Idiom`
  Metamorph handler implementation that allows Idiom to be used via Metamorph's simplified interface with macro support ('extended' and 'inline').

### 3.2 Main Entry Points

The main usage pattern is creating a Parser and parsing content:

```php
use DecodeLabs\Idiom\Parser;

$parser = new Parser();
$parser->setExtended(true);
$markup = $parser->parse($content);
```

For Metamorph integration:

```php
use DecodeLabs\Metamorph;

$html = Metamorph::{'idiom.extended'}($content);
```

---

## 4. Dependencies

### 4.1 Direct Decode Labs Dependencies

From `composer.json`:

- `decodelabs/coercion`
  Type coercion utilities for option handling in Metamorph handler.

- `decodelabs/tagged`
  Markup generation system. Parser returns Tagged Markup instances (Buffer) for proper rendering integration.

**Optional integration:**

- `decodelabs/metamorph` (optional, dev dependency)
  Detected at runtime if installed, used for content transformation pipeline integration via Handler implementation.

### 4.2 External Dependencies

None required for runtime operation.

See `composer.json` for supported PHP versions.

---

## 5. Behaviour & Contracts

### 5.1 Invariants

- Parser always returns Tagged Markup instances (Buffer) or null for empty input
- Only whitelisted HTML tags are preserved in output
- Inline mode converts newlines to `<br />` tags
- Block mode wraps content in paragraph tags and handles block elements
- Extended mode enables additional block-level HTML elements
- Empty or null input returns null
- Content is trimmed before processing
- Non-whitelisted tags are stripped using `strip_tags()`
- Multiple consecutive newlines are converted to paragraph breaks
- Block elements are normalized to prevent nested paragraph tags

### 5.2 Input & Output Contracts

**Parser::parse(?string content):**
- **Input:** Text content with optional HTML tags, or null
- **Output:** Tagged Markup instance (Buffer) containing HTML, or null if input is empty
- **Preconditions:** None
- **Postconditions:** Output is valid HTML with only whitelisted tags, properly structured paragraphs or inline formatting

**Parser::setInline(bool inline):**
- **Input:** Boolean flag to enable inline mode
- **Output:** Returns self for fluent chaining
- **Preconditions:** None
- **Postconditions:** Parser will use inline mode for subsequent parse() calls

**Parser::setExtended(bool extended):**
- **Input:** Boolean flag to enable extended mode
- **Output:** Returns self for fluent chaining
- **Preconditions:** None
- **Postconditions:** Parser will allow extended block tags for subsequent parse() calls (only in block mode)

**Metamorph\Handler\Idiom::convert(string content, ?callable setup = null):**
- **Input:** Text content and optional setup callback for Parser configuration
- **Output:** Tagged Buffer instance or null
- **Preconditions:** None
- **Postconditions:** Content is parsed according to handler's inline/extended settings, URLs are resolved if enabled

---

## 6. Error Handling

### 6.1 Exception Types

Idiom does not throw exceptions in normal operation. The parser handles edge cases gracefully:

- Empty or null input returns null
- Invalid HTML tags are stripped silently
- Malformed content is processed as best as possible

The Metamorph handler may throw exceptions from Metamorph's URL resolution if configured, but these are not from Idiom itself.

### 6.2 Error Strategy

Idiom uses a permissive error strategy. Invalid or malformed input is processed as best as possible rather than throwing exceptions. Tags that aren't whitelisted are stripped, and content structure is normalized to produce valid HTML output. This approach prioritizes producing usable output over strict validation.

---

## 7. Configuration & Extensibility

### 7.1 Configuration

Parser configuration is done via methods:

- `setInline(bool)`: Enable inline mode (newlines become `<br />`, no paragraph wrapping)
- `setExtended(bool)`: Enable extended mode (allows additional block-level HTML tags like headings, lists, tables)

Metamorph handler configuration via options:

- `inline`: Boolean to enable inline mode
- `extended`: Boolean to enable extended mode
- `resolveUrls`: Boolean to enable URL resolution in output (via HtmlTrait)

### 7.2 Extension Points

Idiom supports limited extension via:

- **Metamorph handler setup callback:** Provide a callable to configure the Parser instance before parsing
- **Custom tag whitelisting:** The tag lists (Tags, ExtendedTags, ContainerTags) are protected constants that could be extended in subclasses, though this is not the primary extension mechanism

The parser is designed to be simple and focused, so extensive customization is not a primary goal. For more complex parsing needs, consider other content transformation packages.

---

## 8. Interactions with Other Packages

Idiom is designed to integrate with:

- **`decodelabs/tagged`**
  Uses Tagged for markup output. Parser returns Tagged Buffer instances for proper rendering integration.

- **`decodelabs/metamorph`** (optional)
  Provides Handler implementation that allows Idiom to be used in Metamorph content transformation pipelines. The handler supports macros ('extended', 'inline') and URL resolution.

Design assumptions:

- Tagged is available for markup generation
- Metamorph is optional but provides convenient integration when available
- Input content is considered safe (no exhaustive security sanitization)

---

## 9. Usage Examples

### 9.1 Basic Block Parsing

```php
use DecodeLabs\Idiom\Parser;

$content = <<<CONTENT
This is a paragraph.

This is another paragraph with <strong>bold text</strong>.
CONTENT;

$parser = new Parser();
$markup = $parser->parse($content);

// Output: <p>This is a paragraph.</p><p>This is another paragraph with <strong>bold text</strong>.</p>
```

### 9.2 Extended Mode with Block Elements

```php
use DecodeLabs\Idiom\Parser;

$content = <<<CONTENT
<h2>Heading</h2>

This is a paragraph.

<ul>
    <li>List item one</li>
    <li>List item two</li>
</ul>
CONTENT;

$parser = new Parser();
$parser->setExtended(true);
$markup = $parser->parse($content);
```

### 9.3 Inline Mode

```php
use DecodeLabs\Idiom\Parser;

$content = "Line one\nLine two with <em>emphasis</em>";

$parser = new Parser();
$parser->setInline(true);
$markup = $parser->parse($content);

// Output: Line one<br />
// Line two with <em>emphasis</em>
```

### 9.4 Metamorph Integration

```php
use DecodeLabs\Metamorph;

// Extended mode
$html = Metamorph::{'idiom.extended'}($content);

// Inline mode
$html = Metamorph::{'idiom.inline'}($content);

// Custom configuration
$html = Metamorph::idiom($content, function($parser) {
    $parser->setExtended(true);
});
```

---

## 10. Implementation Notes (For Contributors)

### 10.1 Internal Architecture

At a high level, Idiom:

- Uses `strip_tags()` with whitelisted tag lists to sanitize input
- Processes content in two modes: inline (simple newline-to-br conversion) and block (paragraph wrapping and block element handling)
- Uses regex-based parsing for block mode to identify and preserve block elements
- Handles `<pre>` tags specially by temporarily replacing them during processing to avoid breaking their content
- Normalizes whitespace and HTML structure (removes empty paragraphs, normalizes br tags, prevents nested block elements)
- Converts multiple spaces to non-breaking spaces in non-extended mode to preserve formatting

Contributors should:

- Maintain the whitelist approach for security (only allow known-safe tags)
- Preserve the simplicity of the parser (avoid over-engineering)
- Keep Tagged integration for output
- Ensure block element normalization prevents malformed HTML
- Handle edge cases gracefully without throwing exceptions

### 10.2 Performance Considerations

- Uses `strip_tags()` which is efficient for tag filtering
- Regex operations are used for block parsing, which may be slower for very large content
- `<pre>` tag handling involves string manipulation that could be optimized
- Multiple regex passes are performed in block mode, which could be consolidated
- No caching is performed; each parse() call processes content fresh

### 10.3 Gotchas & Historical Decisions

- **Security note:** The README explicitly states that Idiom does not exhaustively treat input for malicious entities - this parser should only be used with content considered safe. This is an important limitation.
- **Tag whitelisting:** Only a curated subset of HTML tags is allowed. This is intentional for security and simplicity, but means some common tags (like `<div>`) are not supported.
- **Extended mode limitation:** Extended mode only works in block mode, not inline mode. This is by design to maintain inline mode simplicity.
- **Space handling:** In non-extended mode, spaces are converted to non-breaking spaces to preserve formatting, which may not always be desired.
- **Pre tag handling:** `<pre>` tags are temporarily replaced during processing to avoid breaking their content, which adds complexity but is necessary for correctness.

---

## 11. Testing & Quality

### 11.1 Testing Strategy

Tests should cover:

- Basic paragraph wrapping in block mode
- Inline mode newline-to-br conversion
- Extended mode block element preservation
- Tag whitelisting (allowed tags preserved, disallowed tags stripped)
- Empty and null input handling
- Multiple consecutive newlines
- Pre tag handling and preservation
- Space-to-nbsp conversion in non-extended mode
- Block element normalization (preventing nested paragraphs)
- Metamorph handler integration
- URL resolution in Metamorph handler
- Edge cases with malformed HTML

### 11.2 Quality Signals

From the Decode Labs package index (at time of writing):

- **Code:** 2.5
- **Readme:** 2
- **Docs:** 0
- **Tests:** 0

Idiom is an early-stage package with basic functionality. The code quality is functional but minimal, and the README provides basic usage examples. Comprehensive documentation (this spec) and test coverage are planned but not yet implemented. The package serves its purpose as a simple text-to-HTML parser but has known limitations around security and tag support that should be considered when using it.

---

## 12. Roadmap & Future Ideas

Non-binding ideas:

- Comprehensive test suite covering all parsing modes and edge cases
- Enhanced security sanitization for safer handling of untrusted input
- Support for additional HTML tags based on use cases
- Performance optimizations for large content
- Better handling of nested block elements
- Configuration options for space handling
- Support for HTML attributes in whitelisted tags
- Custom tag whitelist configuration
- Better error reporting for malformed content

---

## 13. References

- **Chorus docs:**
  - Architecture principles
  - Package taxonomy & clusters
  - Backwards compatibility strategy (once published)

- **Related packages:**
  - `decodelabs/tagged` (markup generation output)
  - `decodelabs/metamorph` (optional content transformation integration)

- **Repository:**
  - `https://github.com/decodelabs/idiom`

---

> This spec is intended to stay in sync with the **actual behaviour** of the package.
> When you make significant changes to the public surface or semantics, please update this document and, where applicable, add or update ADRs in Chorus.

