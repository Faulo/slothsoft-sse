<?php
declare(strict_types = 1);
namespace Slothsoft\SSE\Results;

use Psr\Http\Message\StreamInterface;
use Slothsoft\Core\Calendar\Seconds;
use Slothsoft\Farah\Module\Result\ResultInterface;
use Slothsoft\Farah\Module\Result\StreamBuilderStrategy\StreamBuilderStrategyInterface;
use Slothsoft\Farah\Streams\GeneratorStream;
use Slothsoft\Farah\Streams\WaitingStream;
use Slothsoft\SSE\EventGenerator;

class EventStreamBuilder implements StreamBuilderStrategyInterface
{

    private $generator;

    public function __construct(EventGenerator $generator)
    {
        $this->generator = $generator;
    }
    
    public function buildStreamFileName(ResultInterface $context): string
    {
        return 'events.txt';
    }

    public function buildStreamChangeTime(ResultInterface $context): int
    {
        return 0;
    }

    public function buildStreamIsBufferable(ResultInterface $context): bool
    {
        return false;
    }

    public function buildStream(ResultInterface $context): StreamInterface
    {
        return new WaitingStream(new GeneratorStream($this->generator), (int) (100 * Seconds::MILLISECOND * Seconds::USLEEP_FACTOR), [
            'interval' => (int) (10 * Seconds::SECOND * Seconds::USLEEP_FACTOR),
            'content' => ":\n"
        ]);
    }

    public function buildStreamMimeType(ResultInterface $context): string
    {
        return 'text/event-stream';
    }

    public function buildStreamHash(ResultInterface $context): string
    {
        return '';
    }

    public function buildStreamCharset(ResultInterface $context): string
    {
        return 'UTF-8';
    }

}

