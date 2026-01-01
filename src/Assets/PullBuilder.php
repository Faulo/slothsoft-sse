<?php
declare(strict_types = 1);
namespace Slothsoft\SSE\Assets;

use Slothsoft\Core\DBMS\DatabaseException;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\ExecutableBuilderStrategyInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\SSE\Server;
use Slothsoft\SSE\Results\ServerResultBuilder;

class PullBuilder implements ExecutableBuilderStrategyInterface {
    
    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        try {
            $tableName = $args->get('name');
            
            $sse = $this->createServer($tableName);
            
            $lastId = (int) $args->get('lastId');
            try {
                $sse->init($lastId);
            } catch (DatabaseException $e) {}
            
            $resultBuilder = new ServerResultBuilder($sse);
            
            return new ExecutableStrategies($resultBuilder);
        } catch (\Throwable $e) {
            file_put_contents(__FILE__ . '.log', (string) $e);
            throw $e;
        }
    }
    
    protected function createServer(string $tableName): Server {
        return new Server($tableName);
    }
}

