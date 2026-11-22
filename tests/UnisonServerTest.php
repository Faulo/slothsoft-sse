<?php
declare(strict_types = 1);

namespace Slothsoft\SSE;

use PHPUnit\Framework\TestCase;

/**
 * UnisonServerTest
 *
 * @see UnisonServer
 */
final class UnisonServerTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(UnisonServer::class), "Failed to load class 'Slothsoft\SSE\UnisonServer'!");
    }
    
    public function testFetchNewEventsAdjustsPlaybackProgressAndKeepsNullData(): void {
        $server = new class('test') extends UnisonServer {
            
            /** @return list<array{id: int, ...}> */
            protected function selectEventRows(string $query, string $suffix): array {
                return [
                    [
                        'id' => 1,
                        'data' => '{"playing":true,"progress":10}',
                        'create-time' => microtime(true) - 2,
                    ],
                    [
                        'id' => 2,
                        'data' => null,
                    ],
                ];
            }
        };
        
        $events = [];
        foreach ($server->fetchNewEvents(0) as $event) {
            $events[] = $event;
        }
        
        self::assertSame([1, 2], array_column($events, 'id'));
        self::assertSame(['id' => 2, 'data' => null], $events[1]);
        $data = $events[0]['data'] ?? null;
        self::assertIsString($data);
        $status = json_decode($data, true);
        self::assertIsArray($status);
        self::assertArrayHasKey('progress', $status);
        self::assertGreaterThan(12, $status['progress']);
    }
}
