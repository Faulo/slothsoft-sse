<?php
declare(strict_types = 1);
namespace Slothsoft\SSE;

use PHPUnit\Framework\TestCase;

/**
 * EventGeneratorTest
 *
 * @see EventGenerator
 *
 * @todo auto-generated
 */
class EventGeneratorTest extends TestCase {

    public function testClassExists(): void {
        $this->assertTrue(class_exists(EventGenerator::class), "Failed to load class 'Slothsoft\SSE\EventGenerator'!");
    }
}