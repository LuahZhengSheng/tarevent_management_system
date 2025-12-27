<?php

namespace Tests\Unit;

use App\Decorators\ContentSanitizationDecorator;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ContentSanitizationDecoratorContentTest extends TestCase
{
    private function callSanitizeContent(string $input): string
    {
        $req = Request::create('/fake', 'POST', ['content' => $input]);
        $decorator = new ContentSanitizationDecorator($req);

        $ref = new ReflectionClass($decorator);
        $m = $ref->getMethod('sanitizeContent');
        $m->setAccessible(true);

        return $m->invoke($decorator, $input);
    }

    public function test_removes_script_tags_and_content(): void
    {
        $out = $this->callSanitizeContent('<p>ok</p><script>alert(1)</script><p>done</p>');
        $this->assertSame('<p>ok</p><p>done</p>', $out);
    }

    public function test_removes_iframe(): void
    {
        $out = $this->callSanitizeContent('<p>a</p><iframe src="https://evil.com">x</iframe><p>b</p>');
        $this->assertSame('<p>a</p><p>b</p>', $out);
    }

    public function test_removes_object_and_embed(): void
    {
        $out1 = $this->callSanitizeContent('<p>a</p><object data="x">obj</object><p>b</p>');
        $this->assertSame('<p>a</p><p>b</p>', $out1);

        $out2 = $this->callSanitizeContent('<p>a</p><embed src="x">emb</embed><p>b</p>');
        $this->assertSame('<p>a</p><p>b</p>', $out2);
    }

    public function test_strips_disallowed_tags_but_keeps_allowed_tags(): void
    {
        $out = $this->callSanitizeContent('<p>ok</p><div>NO</div><strong>yes</strong><span>NO</span>');
        // div/span 不在 ALLOWED_TAGS，会被 strip 掉，但里面的文字会保留
        $this->assertSame('<p>ok</p>NO<strong>yes</strong>NO', $out);
    }

    public function test_removes_dangerous_event_handler_attributes(): void
    {
        $out = $this->callSanitizeContent('<p onclick="alert(1)" onmouseover="x()">ok</p>');
        $this->assertSame('<p>ok</p>', $out);
    }

    public function test_removes_style_attributes(): void
    {
        $out = $this->callSanitizeContent('<p style="color:red">ok</p>');
        $this->assertSame('<p>ok</p>', $out);
    }

    public function test_removes_html_comments(): void
    {
        $out = $this->callSanitizeContent('<p>a</p><!-- hidden --><p>b</p>');
        $this->assertSame('<p>a</p><p>b</p>', $out);
    }

    public function test_filters_javascript_protocol_in_href(): void
    {
        $out = $this->callSanitizeContent('<a href="javascript:alert(1)">x</a>');
        // javascript: 会先被移除，href 变成 "alert(1)"，之后 sanitizeUrls 会判定不合法协议并移除整个 href 属性
        $this->assertSame('<a>x</a>', $out);
    }

    public function test_filters_data_html_protocol_in_href(): void
    {
        $out = $this->callSanitizeContent('<a href="data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==">x</a>');
        // data:text/html 会被移除，剩下的不是 http/https/mailto/相对路径，sanitizeUrls 会移除 href
        $this->assertSame('<a>x</a>', $out);
    }

    public function test_allows_http_https_mailto_and_relative_urls(): void
    {
        $out1 = $this->callSanitizeContent('<a href="https://example.com/path?q=1&x=2">x</a>');
        $this->assertSame('<a href="https://example.com/path?q=1&amp;x=2">x</a>', $out1);

        $out2 = $this->callSanitizeContent('<a href="mailto:test@example.com">x</a>');
        $this->assertSame('<a href="mailto:test@example.com">x</a>', $out2);

        $out3 = $this->callSanitizeContent('<a href="/relative/path">x</a>');
        $this->assertSame('<a href="/relative/path">x</a>', $out3);
    }

    public function test_removes_invalid_url_protocols_like_ftp(): void
    {
        $out = $this->callSanitizeContent('<a href="ftp://example.com/file">x</a>');
        $this->assertSame('<a>x</a>', $out);
    }

    public function test_removes_null_bytes(): void
    {
        $out = $this->callSanitizeContent("<p>a\0b</p>");
        $this->assertSame('<p>ab</p>', $out);
    }
}
