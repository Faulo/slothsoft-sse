<?php
declare(strict_types = 1);
namespace Slothsoft\SSE;

use PHPUnit\Framework\TestCase;

/**
 * ServerTest
 *
 * @see Server
 *
 * @todo auto-generated
 */
class ServerTest extends TestCase {

    public function testClassExists(): void {
        $this->assertTrue(class_exists(Server::class), "Failed to load class 'Slothsoft\SSE\Server'!");
    }
}