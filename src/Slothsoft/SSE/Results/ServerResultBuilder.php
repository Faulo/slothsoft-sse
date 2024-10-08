<?php
declare(strict_types = 1);
namespace Slothsoft\SSE\Results;

use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\ResultBuilderStrategyInterface;
use Slothsoft\Farah\Module\Result\ResultStrategies;
use Slothsoft\SSE\EventGenerator;
use Slothsoft\SSE\Server;

class ServerResultBuilder implements ResultBuilderStrategyInterface
{

    private $server;

    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    public function buildResultStrategies(ExecutableInterface $context, FarahUrlStreamIdentifier $type): ResultStrategies
    {
        $streamBuilder = new EventStreamBuilder(new EventGenerator($this->server));
        return new ResultStrategies($streamBuilder);
    }
}

