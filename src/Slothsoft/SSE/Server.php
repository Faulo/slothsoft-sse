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

class Server
{

    protected $dbName;

    protected $tableName;

    protected $dbmsTable;

    public $lastId;

    public $isRunning = false;

    public function __construct($tableName = 'temp', $dbName = 'sse')
    {
        $this->dbName = $dbName;
        $this->tableName = $tableName;
        $this->dbmsTable = Manager::getTable($this->dbName, $this->tableName);
        if (! $this->dbmsTable->tableExists()) {
            $this->install();
        }
    }

    public function __destruct()
    {
        if ($this->isRunning) {
            $this->stopRunning();
        }
    }

    protected function install()
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
        if (! $this->lastId) {
            $res = $this->dbmsTable->select('id', null, 'ORDER BY id DESC LIMIT 1');
            $this->lastId = (int) current($res);
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
        return $this->dbmsTable->insert([
            'type' => $type,
            'data' => $data
        ]);
    }

    public function fetchNewEvents($lastId)
    {
        $ret = $this->dbmsTable->select(true, sprintf('id > %d', $lastId), 'ORDER BY id');
        foreach ($ret as &$arr) {
            $arr['id'] = (int) $arr['id'];
        }
        return $ret;
    }

    public function fetchLastEvent()
    {
        $ret = $this->dbmsTable->select(true, null, 'ORDER BY id DESC LIMIT 1');
        foreach ($ret as &$arr) {
            $arr['id'] = (int) $arr['id'];
        }
        return count($ret) ? reset($ret) : null;
    }
}