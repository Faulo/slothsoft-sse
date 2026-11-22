<?php
declare(strict_types = 1);
/**
 * *********************************************************************
 * \SSE\VCTServer v1.00 29.05.2014 © Daniel Schulz
 *
 * Changelog:
 * v1.00 29.05.2014
 * public function __construct($serverName)
 * *********************************************************************
 */

namespace Slothsoft\SSE;

use JsonException;
use Slothsoft\Core\DBMS\Table;
use Slothsoft\Core\Game\Name;
use Stringable;

class VCTServer extends Server {
    
    protected string $userId;
    
    protected string $userName = '';
    
    public function __construct(string $serverName) {
        parent::__construct(sprintf('vct: %s', $serverName));
    }
    
    protected function install(Table $table): void {
        $sqlCols = [
            'id' => 'int NOT NULL AUTO_INCREMENT',
            'user' => 'CHAR(40) CHARACTER SET ascii COLLATE ascii_bin NULL',
            'type' => 'varchar(32) NULL',
            'data' => 'text NULL',
            'create-time' => 'int NOT NULL DEFAULT "0"'
        ];
        $sqlKeys = [
            'id',
            'user',
            'type',
            'create-time'
        ];
        $table->createTable($sqlCols, $sqlKeys);
    }
    
    public function init(int $lastId = 0, ?string $userId = null): void {
        parent::init($lastId);
        
        if (! $userId) {
            $userId = sha1($_SERVER['REQUEST_TIME_FLOAT'] . '-' . $_SERVER['REMOTE_ADDR']);
        }
        
        $this->userId = $userId;
    }
    
    /** @throws JsonException */
    public function startRunning(): string {
        if (! $this->userName) {
            if ($list = Name::generate()) {
                $this->userName = reset($list);
            }
        }
        $data = json_encode([
            'userId' => $this->userId,
            'userName' => $this->userName,
            'lastId' => $this->lastId
        ], JSON_THROW_ON_ERROR);
        parent::startRunning();
        return $data;
    }
    
    public function stopRunning(): void {
        parent::stopRunning();
        $this->dispatchEvent('abort', $this->userId);
    }
    
    public function dispatchEvent(?string $type, string|int|float|bool|Stringable|null $data): ?int {
        return $this->dbmsTable?->insert([
            'user' => $this->userId,
            'type' => $type,
            'data' => $data,
            'create-time' => time()
        ]);
    }
    
    /** @return iterable<int, array{id: int, ...}> */
    public function fetchNewEvents(int $lastId): iterable {
        if ($this->dbmsTable === null) {
            return [];
        }
        $userId = $this->dbmsTable->escape($this->userId);
        return $this->selectEventRows(sprintf('id > %d AND user != "%s"', $lastId, $userId), 'ORDER BY id');
    }
}
