<?php
declare(strict_types = 1);
namespace Slothsoft\SSE;

use PHPUnit\Framework\TestCase;

/**
 * UnisonServerTest
 *
 * @see UnisonServer
 *
 * @todo auto-generated
 */
class UnisonServerTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(UnisonServer::class), "Failed to load class 'Slothsoft\SSE\UnisonServer'!");
    }
}