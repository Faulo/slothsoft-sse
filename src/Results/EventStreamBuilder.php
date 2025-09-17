<?php
declare(strict_types = 1);
namespace Slothsoft\SSE\Results;

use Slothsoft\Core\Calendar\Seconds;
use Slothsoft\Core\IO\Writable\ChunkWriterInterface;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
use Slothsoft\Core\IO\Writable\StreamWriterInterface;
use Slothsoft\Core\IO\Writable\StringWriterInterface;
use Slothsoft\Core\IO\Writable\Adapter\StreamWriterFromChunkWriter;
use Slothsoft\Farah\Module\Result\ResultInterface;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\StreamBuilderStrategyInterface;
use Slothsoft\SSE\EventGenerator;
use Slothsoft\SSE\WaitingGenerator;
use BadMethodCallException;
use Generator;

class EventStreamBuilder implements StreamBuilderStrategyInterface, ChunkWriterInterface {
    
    private WaitingGenerator $generator;
    
    public function __construct(EventGenerator $generator) {
        $usleep = (int) (100 * Seconds::MILLISECOND * Seconds::USLEEP_FACTOR);
        $heartbeat = [
            'interval' => (int) (10 * Seconds::SECOND * Seconds::USLEEP_FACTOR),
            'content' => ":\n"
        ];
        $this->generator = new WaitingGenerator($generator, $usleep, $heartbeat);
    }
    
    public function buildStreamFileName(ResultInterface $context): string {
        return 'events.txt';
    }
    
    public function buildStreamFileStat(ResultInterface $context): array {
        return [];
    }
    
    public function buildStreamIsBufferable(ResultInterface $context): bool {
        return false;
    }
    
    public function buildStreamMimeType(ResultInterface $context): string {
        return 'text/event-stream';
    }
    
    public function buildStreamHash(ResultInterface $context): string {
        return '';
    }
    
    public function buildStreamCharset(ResultInterface $context): string {
        return 'UTF-8';
    }
    
    public function buildStreamFileStatistics(ResultInterface $context): array {
        return [];
    }
    
    public function buildStreamWriter(ResultInterface $context): StreamWriterInterface {
        return new StreamWriterFromChunkWriter($context->lookupChunkWriter());
    }
    
    public function buildFileWriter(ResultInterface $context): FileWriterInterface {
        throw new BadMethodCallException('EventStream is assumed to be infinite.');
    }
    
    public function buildDOMWriter(ResultInterface $context): DOMWriterInterface {
        throw new BadMethodCallException('EventStream is assumed to be infinite.');
    }
    
    public function buildStringWriter(ResultInterface $context): StringWriterInterface {
        throw new BadMethodCallException('EventStream is assumed to be infinite.');
    }
    
    public function buildChunkWriter(ResultInterface $context): ChunkWriterInterface {
        return $this;
    }
    
    public function toChunks(): Generator {
        return $this->generator->toChunks();
    }
}

