<?php
declare(strict_types = 1);

namespace Slothsoft\SSE;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionParameter;

/**
 * ServerTest
 *
 * @see Server
 */
final class ServerTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Server::class), "Failed to load class 'Slothsoft\SSE\Server'!");
    }
    
    public function testDispatchEventHasStringParameters(): void {
        $dispatch = new ReflectionMethod(Server::class, 'dispatchEvent');
        $parameterTypes = array_map(static fn(ReflectionParameter $parameter): string => (string) $parameter->getType(), $dispatch->getParameters());
        
        self::assertSame(['string', 'string'], $parameterTypes);
    }
}
