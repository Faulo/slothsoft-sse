<?php
declare(strict_types = 1);
namespace Slothsoft\SSE\Results;

use Psr\Http\Message\StreamInterface;
use Slothsoft\Core\Calendar\Seconds;
use Slothsoft\Farah\Module\Results\ResultBase;
use Slothsoft\Farah\Streams\GeneratorStream;
use Slothsoft\Farah\Streams\WaitingStream;
use Slothsoft\SSE\EventGenerator;

class EventResult extends ResultBase
{
    private $generator;
    public function __construct(EventGenerator $generator) {
        $this->generator = $generator;
    }
    
    public function lookupHash() : string
    {
        return '';
    }

    public function lookupStream() : StreamInterface
    {
        return new WaitingStream(
            new GeneratorStream($this->generator),
            (int) (100 * Seconds::MILLISECOND * Seconds::USLEEP_FACTOR),
            [
                'interval' => (int) (1 * Seconds::SECOND * Seconds::USLEEP_FACTOR),
                'content' => ":\n",
            ]
        );
    }

    public function lookupMimeType() : string
    {
        return 'text/event-stream';
    }

    public function lookupCharset(): string
    {
        return 'UTF-8';
    }

    public function lookupFileName(): string
    {
        return 'events.txt';
    }

    public function lookupChangeTime(): int
    {
        return 0;
    }

}
