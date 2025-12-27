<?php

namespace Tests\Unit;

use App\Decorators\ContentSanitizationDecorator;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ContentSanitizationDecoratorTest extends TestCase {

    private function callSanitizeTitle(string $input): string {
        $req = Request::create('/fake', 'POST', ['title' => $input]);
        $decorator = new ContentSanitizationDecorator($req);

        $ref = new ReflectionClass($decorator);
        $m = $ref->getMethod('sanitizeTitle');
        $m->setAccessible(true);

        return $m->invoke($decorator, $input);
    }

    public function test_strip_html_tags(): void {
        $out = $this->callSanitizeTitle('Hello <b>World</b>');
        $this->assertSame('Hello World', $out);
    }

    public function test_remove_dangerous_brackets(): void {
        $out = $this->callSanitizeTitle('a < > { } b');
        $this->assertSame('a b', $out);
    }

    public function test_remove_script_block(): void {
        $out = $this->callSanitizeTitle('ok <script>alert(1)</script> done');
        $this->assertSame('ok alert(1) done', $out);
    }

    public function test_normalize_whitespace_and_null_bytes(): void {
        $out = $this->callSanitizeTitle("  a \0   b   ");
        $this->assertSame('a b', $out);
    }
}
