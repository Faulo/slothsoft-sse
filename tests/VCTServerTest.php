<?php
declare(strict_types = 1);

namespace Slothsoft\SSE;

use PHPUnit\Framework\TestCase;

/**
 * VCTServerTest
 *
 * @see VCTServer
 */
final class VCTServerTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(VCTServer::class), "Failed to load class 'Slothsoft\SSE\VCTServer'!");
    }
    
    public function testEventsAreUnavailableBeforeInitialization(): void {
        $server = new VCTServer('test');
        
        self::assertSame([], $server->fetchNewEvents(0));
        self::assertNull($server->fetchLastEvent());
        self::assertNull($server->dispatchEvent('test', 'data'));
    }
}
