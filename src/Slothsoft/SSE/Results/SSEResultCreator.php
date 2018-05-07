<?php
declare(strict_types = 1);
namespace Slothsoft\SSE\Results;

use Slothsoft\Farah\Module\Results\ResultCreator;
use Slothsoft\SSE\EventGenerator;
use Slothsoft\Farah\Module\Results\ResultInterface;

class SSEResultCreator extends ResultCreator
{

    public function createEventResult(EventGenerator $generator): ResultInterface
    {
        return $this->initResult(new EventResult($generator));
    }
}

