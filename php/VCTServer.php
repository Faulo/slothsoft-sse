<?php
/***********************************************************************
 * \SSE\VCTServer v1.00 29.05.2014 © Daniel Schulz
 * 
 * 	Changelog:
 *		v1.00 29.05.2014
 *			public function __construct($serverName)
 ***********************************************************************/
namespace Slothsoft\SSE;

use Slothsoft\Core\Game\Name;

class VCTServer extends Server
{

    protected $userId;

    protected $userName;

    public function __construct($serverName)
    {
        parent::__construct(sprintf('vct: %s', $serverName), 'sse');
    }

    protected function install()
    {
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
        $this->dbmsTable->createTable($sqlCols, $sqlKeys);
    }

    public function init($lastId = null, $userId = null)
    {
        parent::init($lastId);
        
        $this->userId = $userId;
        if (! $this->userId) {
            $this->userId = sha1($_SERVER['REQUEST_TIME_FLOAT'] . '-' . $_SERVER['REMOTE_ADDR']);
        }
    }

    public function startRunning()
    {
        parent::startRunning();
        if (! $this->userName) {
            if ($list = Name::generate()) {
                $this->userName = reset($list);
            }
        }
        return json_encode([
            'userId' => $this->userId,
            'userName' => $this->userName,
            'lastId' => $this->lastId
        ]);
    }

    public function stopRunning()
    {
        parent::stopRunning();
        $this->dispatchEvent('abort', $this->userId);
    }

    public function dispatchEvent($type, $data)
    {
        return $this->dbmsTable->insert([
            'user' => $this->userId,
            'type' => $type,
            'data' => $data,
            'create-time' => time()
        ]);
    }

    public function fetchNewEvents($lastId)
    {
        $ret = $this->dbmsTable->select(true, 
            // sprintf('id > %d', $lastId),
            sprintf('id > %d AND user != "%s"', $lastId, $this->userId), 'ORDER BY id');
        foreach ($ret as &$arr) {
            $arr['id'] = (int) $arr['id'];
        }
        return $ret;
    }
}