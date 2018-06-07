<?php
declare(strict_types = 1);
/**
 * *********************************************************************
 * \SSE\Stream v1.00 29.05.2014 � Daniel Schulz
 *
 * Changelog:
 * 29.05.2014
 * initial release
 * *********************************************************************
 */
namespace Slothsoft\SSE;

use Slothsoft\Core\Calendar\Seconds;
use Slothsoft\Core\IO\HTTPStream;

class Stream extends HTTPStream
{

    const STREAM_EOL = "\n";

    const STREAM_FIELD_BLANK = ":%s\n";

    const STREAM_FIELD_ID = "id:%d\n";

    const STREAM_FIELD_TYPE = "event:%s\n";

    const STREAM_FIELD_DATA = "data:%s\n";

    const STREAM_FIELD_RETRY = "retry:%d\n";

    protected $ownerServer;

    protected $eventStack;

    public function __construct(Server $ownerServer)
    {
        $this->ownerServer = $ownerServer;
        $this->mime = 'text/event-stream';
        $this->encoding = 'UTF-8';
        // $this->headerList['connection'] = 'close';
        $this->hasStarted = false;
        $this->eventStack = [];
        $this->sleepDuration = 100 * Seconds::MILLISECOND;
        $this->heartbeatContent = ":\n";
        $this->heartbeatInterval = 10 * Seconds::SECOND;
    }

    protected function parseStatus()
    {
        if ($this->ownerServer->isRunning) {
            if ($eventList = $this->ownerServer->fetchNewEvents($this->ownerServer->lastId)) {
                foreach ($eventList as $event) {
                    $this->ownerServer->lastId = $event['id'];
                    $this->eventStack[] = $event;
                }
            }
        } else {
            $this->eventStack[] = [
                'type' => 'start',
                'data' => $this->ownerServer->startRunning()
            ];
        }
        $this->status = count($this->eventStack) ? self::STATUS_CONTENT : self::STATUS_RETRY;
    }

    protected function parseContent()
    {
        $this->content = '';
        foreach ($this->eventStack as $event) {
            $this->sendEvent($event);
        }
        $this->eventStack = [];
    }

    protected function sendEvent(array $event)
    {
        $sendEOL = false;
        if (isset($event['id'])) {
            $this->sendEventLine(self::STREAM_FIELD_ID, $event['id']);
            $sendEOL = true;
        }
        if (isset($event['type'])) {
            $this->sendEventLine(self::STREAM_FIELD_TYPE, $event['type']);
            $sendEOL = true;
        }
        if (isset($event['data'])) {
            $this->sendEventLine(self::STREAM_FIELD_DATA, $event['data']);
            $sendEOL = true;
        }
        if (isset($event['retry'])) {
            $this->sendEventLine(self::STREAM_FIELD_RETRY, $event['retry']);
            $sendEOL = true;
        }
        if (isset($event['comment'])) {
            $this->sendEventLine(self::STREAM_FIELD_BLANK, $event['comment']);
        }
        if ($sendEOL) {
            $this->content .= self::STREAM_EOL;
        }
    }

    protected function sendEventLine($pattern, $data = null)
    {
        if (is_string($data) and strpos($data, self::STREAM_EOL) !== false) {
            $dataList = explode(self::STREAM_EOL, $data);
            foreach ($dataList as $data) {
                $this->content .= sprintf($pattern, $data);
            }
        } else {
            $this->content .= sprintf($pattern, $data);
        }
    }
}