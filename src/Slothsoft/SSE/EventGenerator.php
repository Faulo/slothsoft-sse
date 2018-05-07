<?php
declare(strict_types = 1);
namespace Slothsoft\SSE;

use Slothsoft\Core\IO\Writable\ChunkWriterInterface;
use Generator;

class EventGenerator implements ChunkWriterInterface
{

    const STREAM_EOL = "\n";

    const STREAM_FIELD_BLANK = ":%s\n";

    const STREAM_FIELD_ID = "id:%d\n";

    const STREAM_FIELD_TYPE = "event:%s\n";

    const STREAM_FIELD_DATA = "data:%s\n";

    const STREAM_FIELD_RETRY = "retry:%d\n";

    private $server;

    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    public function toChunks(): Generator
    {
        $this->server->startRunning();
        yield $this->formatEvent([
            'type' => 'start',
            'data' => ''
        ]);
        while (! connection_aborted()) {
            $eventList = $this->server->fetchNewEvents($this->server->lastId);
            foreach ($eventList as $event) {
                $this->server->lastId = $event['id'];
                yield $this->formatEvent($event);
            }
            yield ''; // yield an empty string to indicate we need to sleep before proceeding
        }
    }

    private function formatEvent(array $event): string
    {
        $ret = '';
        $sendEOL = false;
        if (isset($event['id'])) {
            $ret .= $this->formatEventLine(self::STREAM_FIELD_ID, (string) $event['id']);
            $sendEOL = true;
        }
        if (isset($event['type'])) {
            $ret .= $this->formatEventLine(self::STREAM_FIELD_TYPE, (string) $event['type']);
            $sendEOL = true;
        }
        if (isset($event['data'])) {
            $ret .= $this->formatEventLine(self::STREAM_FIELD_DATA, (string) $event['data']);
            $sendEOL = true;
        }
        if (isset($event['retry'])) {
            $ret .= $this->formatEventLine(self::STREAM_FIELD_RETRY, (string) $event['retry']);
            $sendEOL = true;
        }
        if (isset($event['comment'])) {
            $ret .= $this->formatEventLine(self::STREAM_FIELD_BLANK, (string) $event['comment']);
        }
        if ($sendEOL) {
            $ret .= self::STREAM_EOL;
        }
        return $ret;
    }

    private function formatEventLine(string $pattern, string $data = null): string
    {
        if (strpos($data, self::STREAM_EOL) === false) {
            return sprintf($pattern, $data);
        } else {
            $ret = '';
            $dataList = explode(self::STREAM_EOL, $data);
            foreach ($dataList as $data) {
                $ret .= sprintf($pattern, $data);
            }
            return $ret;
        }
    }
}

