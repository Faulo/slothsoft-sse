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

use Slothsoft\Core\DBMS\Manager;
use Exception;

class Server
{

    private $dbName;

    private $tableName;

    private $dbmsTable;

    public $lastId;

    public $isRunning = false;

    public function __construct($tableName = 'temp', $dbName = 'sse')
    {
        $this->dbName = $dbName;
        $this->tableName = $tableName;
    }

    public function __destruct()
    {
        if ($this->isRunning) {
            $this->stopRunning();
        }
    }

    private function install()
    {
        $sqlCols = [
            'id' => 'int NOT NULL AUTO_INCREMENT',
            'type' => 'varchar(32) NULL',
            'data' => 'text NULL'
        ];
        $sqlKeys = [
            'id',
            'type'
        ];
        $this->dbmsTable->createTable($sqlCols, $sqlKeys);
    }

    public function init($lastId = null)
    {
        $this->lastId = (int) $lastId;
        
        try {
            $this->dbmsTable = Manager::getTable($this->dbName, $this->tableName);
            if (! $this->dbmsTable->tableExists()) {
                $this->install();
            }
            if (! $this->lastId) {
                $res = $this->dbmsTable->select('id', '', 'ORDER BY id DESC LIMIT 1');
                $this->lastId = (int) current($res);
            }
        } catch (Exception $e) {
            $this->dbmsTable = null;
            throw $e;
        }
    }

    public function getStream()
    {
        return new Stream($this);
    }

    public function startRunning()
    {
        $this->isRunning = true;
        return json_encode('');
    }

    public function stopRunning()
    {
        $this->isRunning = false;
    }

    public function dispatchEvent($type, $data)
    {
        if (! $this->dbmsTable) {
            return false;
        }
        return $this->dbmsTable->insert([
            'type' => $type,
            'data' => $data
        ]);
    }

    public function fetchNewEvents($lastId): iterable
    {
        if (! $this->dbmsTable) {
            return [];
        }
        $events = $this->dbmsTable->select(true, sprintf('id > %d', $lastId), 'ORDER BY id');
        foreach ($events as $event) {
            $event['id'] = (int) $event['id'];
            yield $event;
        }
    }

    public function fetchLastEvent()
    {
        if (! $this->dbmsTable) {
            return null;
        }
        $ret = $this->dbmsTable->select(true, null, 'ORDER BY id DESC LIMIT 1');
        foreach ($ret as &$arr) {
            $arr['id'] = (int) $arr['id'];
        }
        return count($ret) ? reset($ret) : null;
    }
}