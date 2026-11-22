<?php
declare(strict_types = 1);

namespace Slothsoft\SSE;

use PHPUnit\Framework\TestCase;

/**
 * ServerTest
 *
 * @see Server
 */
final class ServerTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Server::class), "Failed to load class 'Slothsoft\SSE\Server'!");
    }
    
    /** @runInSeparateProcess */
    public function testStringOnlyDispatchOverrideIsCompatible(): void {
        $server = new class() extends Server {
            public function dispatchEvent(string $type, string $data): int {
                return 1;
            }
        };
        
        self::assertSame(1, $server->dispatchEvent('message', 'hello'));
    }
}
