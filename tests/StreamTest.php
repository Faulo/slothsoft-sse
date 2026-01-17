<?php
declare(strict_types = 1);
namespace Slothsoft\SSE;

use PHPUnit\Framework\TestCase;

/**
 * StreamTest
 *
 * @see Stream
 *
 * @todo auto-generated
 */
final class StreamTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Stream::class), "Failed to load class 'Slothsoft\SSE\Stream'!");
    }
}