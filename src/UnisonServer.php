<?php
declare(strict_types = 1);
/**
 * *********************************************************************
 * \SSE\UnisonServer v1.00 29.05.2014 © Daniel Schulz
 *
 * Changelog:
 * v1.00 29.05.2014
 * public function __construct($serverName)
 * *********************************************************************
 */

namespace Slothsoft\SSE;

use Slothsoft\Core\Calendar\Seconds;
use Slothsoft\Core\DBMS\Table;
use Stringable;

class UnisonServer extends Server {
    
    public function __construct(string $serverName) {
        parent::__construct(sprintf('unison: %s', $serverName));
    }
    
    protected function install(Table $table): void {
        $sqlCols = [
            'id' => 'int NOT NULL AUTO_INCREMENT',
            'type' => 'varchar(32) NULL',
            'data' => 'text NULL',
            'create-time' => 'double NOT NULL DEFAULT "0"'
        ];
        $sqlKeys = [
            'id',
            'type'
        ];
        $table->createTable($sqlCols, $sqlKeys);
    }
    
    public function dispatchEvent(?string $type, string|int|float|bool|Stringable|null $data): ?int {
        return $this->dbmsTable?->insert([
            'type' => $type,
            'data' => $data,
            'create-time' => microtime(true)
        ]);
    }
    
    /** @return iterable<int, array{id: int, ...}> */
    public function fetchNewEvents(int $lastId): iterable {
        foreach (parent::fetchNewEvents($lastId) as $key => $event) {
            $this->_parseEvent($event);
            yield $key => $event;
        }
    }
    
    /** @return array{id: int, ...}|null */
    public function fetchLastEvent(): ?array {
        if ($ret = parent::fetchLastEvent()) {
            $this->_parseEvent($ret);
        }
        return $ret;
    }
    
    /**
     * @param array<array-key, mixed> $event
     * @param-out ($event is array{id: int, ...} ? array{id: int, ...} : array<array-key, mixed>) $event
     */
    public function _parseEvent(array &$event): void {
        $data = $event['data'] ?? null;
        if (! is_string($data)) {
            return;
        }
        $status = json_decode($data, true);
        $createTime = $event['create-time'] ?? null;
        if (is_array($status) && ! empty($status['playing']) && is_numeric($status['progress'] ?? null) && is_numeric($createTime)) {
            $status['progress'] += microtime(true) - $createTime + 100 * Seconds::MILLISECOND;
            $updatedData = json_encode($status);
            if ($updatedData !== false) {
                $event['data'] = $updatedData;
            }
        }
    }
}
