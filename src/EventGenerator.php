<?php
declare(strict_types = 1);

namespace Slothsoft\SSE;

use Generator;
use Slothsoft\Core\IO\Writable\ChunkWriterInterface;
use Slothsoft\Core\IO\Writable\StringWriterInterface;

class EventGenerator implements ChunkWriterInterface, StringWriterInterface {
    
    const STREAM_EOL = "\n";
    
    const STREAM_FIELD_BLANK = ":%s\n";
    
    const STREAM_FIELD_ID = "id:%d\n";
    
    const STREAM_FIELD_TYPE = "event:%s\n";
    
    const STREAM_FIELD_DATA = "data:%s\n";
    
    const STREAM_FIELD_RETRY = "retry:%d\n";
    
    private Server $server;
    
    public function __construct(Server $server) {
        $this->server = $server;
    }
    
    public function toChunks(): Generator {
        $this->server->startRunning();
        yield $this->formatEvent([
            'type' => 'start',
            'data' => ''
        ]);
        while (! connection_aborted()) {
            $events = $this->server->fetchNewEvents($this->server->lastId);
            foreach ($events as $event) {
                $this->server->lastId = $event['id'];
                yield $this->formatEvent($event);
            }
            yield ''; // yield an empty string to indicate we need to sleep before proceeding
        }
    }
    
    /** @param array<array-key, mixed> $event */
    private function formatEvent(array $event): string {
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
    
    private function formatEventLine(string $pattern, string $data): string {
        if (! str_contains($data, self::STREAM_EOL)) {
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
    
    public function toString(): string {
        $data = '';
        foreach ($this->toChunks() as $chunk) {
            if ($chunk === '') {
                break;
            }
            $data .= $chunk;
        }
        return $data;
    }
}

