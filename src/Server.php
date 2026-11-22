<?php
declare(strict_types = 1);
/**
 * *********************************************************************
 * ServerSentEvents v1.00 07.05.2014 © Daniel Schulz
 *
 * Changelog:
 * v1.00 07.05.2014
 * public function __construct($serverName)
 * public function init($user = null, $lastId = null)
 * public function dispatchEvent($type, $data)
 * public function sendEvent($type, $data, $user = null, $id = null)
 * public function run()
 * *********************************************************************
 */

namespace Slothsoft\SSE;

use Exception;
use Slothsoft\Core\DBMS\Manager;
use Slothsoft\Core\DBMS\Table;
use Stringable;
use UnexpectedValueException;

class Server {
    
    private string $dbName;
    
    private string $tableName;
    
    protected ?Table $dbmsTable = null;
    
    public int $lastId = 0;
    
    public bool $isRunning = false;
    
    public function __construct(string $tableName = 'temp', string $dbName = 'sse') {
        $this->dbName = $dbName;
        $this->tableName = $tableName;
    }
    
    public function __destruct() {
        if ($this->isRunning) {
            $this->stopRunning();
        }
    }
    
    protected function install(Table $table): void {
        $sqlCols = [
            'id' => 'int NOT NULL AUTO_INCREMENT',
            'type' => 'varchar(32) NULL',
            'data' => 'text NULL'
        ];
        $sqlKeys = [
            'id',
            'type'
        ];
        $table->createTable($sqlCols, $sqlKeys);
    }
    
    public function init(int $lastId = 0): void {
        $this->lastId = $lastId;
        
        try {
            $table = Manager::getTable($this->dbName, $this->tableName);
            if (! $table->tableExists()) {
                $this->install($table);
            }
            if (! $this->lastId) {
                $res = $table->select('id', '', 'ORDER BY id DESC LIMIT 1') ?? [];
                $this->lastId = (int) current($res);
            }
            $this->dbmsTable = $table;
        } catch (Exception $e) {
            $this->dbmsTable = null;
            throw $e;
        }
    }
    
    public function getStream(): Stream {
        return new Stream($this);
    }
    
    public function startRunning(): string {
        $this->isRunning = true;
        return '""';
    }
    
    public function stopRunning(): void {
        $this->isRunning = false;
    }
    
    public function dispatchEvent(?string $type, string|int|float|bool|Stringable|null $data): ?int {
        return $this->dbmsTable?->insert([
            'type' => $type,
            'data' => $data
        ]);
    }
    
    /** @return iterable<int, array{id: int, ...}> */
    public function fetchNewEvents(int $lastId): iterable {
        yield from $this->selectEventRows(sprintf('id > %d', $lastId), 'ORDER BY id');
    }
    
    /** @return array{id: int, ...}|null */
    public function fetchLastEvent(): ?array {
        return $this->selectEventRows('', 'ORDER BY id DESC LIMIT 1')[0] ?? null;
    }
    
    /** @return list<array{id: int, ...}> */
    protected function selectEventRows(string $query, string $suffix): array {
        $rows = $this->dbmsTable?->select(true, $query, $suffix) ?? [];
        $events = [];
        foreach ($rows as $row) {
            if (! is_array($row) || ! isset($row['id'])) {
                throw new UnexpectedValueException('The event query returned a row without an ID.');
            }
            $row['id'] = (int) $row['id'];
            $events[] = $row;
        }
        return $events;
    }
}
