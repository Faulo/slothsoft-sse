<?php
declare(strict_types = 1);
namespace Slothsoft\SSE\API\JavaScript;

use PHPUnit\Framework\Constraint\IsEqual;
use Slothsoft\FarahTesting\FarahServerTestCase;
use Slothsoft\Farah\FarahUrl\FarahUrlAuthority;

final class ClientTest extends FarahServerTestCase {
    
    protected static function setUpServer(): void {
        self::$server->setModule(FarahUrlAuthority::createFromVendorAndModule('slothsoft', 'test-module'), 'test-files/test-module');
    }
    
    protected function setUpClient(): void {
        $this->client->request('GET', '/slothsoft@farah/example-page');
    }
    
    public function test_Client_open(): void {
        $arguments = [
            'test'
        ];
        
        $actual = $this->client->executeAsyncScript(<<<EOT
async function test(dbName) {
    const { default: SuT } = await import("/slothsoft@sse/js/Client");

    const client = new SuT("/slothsoft@test-module/sse", dbName);

    return await new Promise(resolve => {
        client.addEventListener("error", eve => resolve(eve.type));
        client.addEventListener("open", eve => resolve(eve.type));
    });
}

import("/slothsoft@farah/js/Test").then(Test => Test.run(test, arguments));
EOT, $arguments);
        
        $this->assertThat($actual, new IsEqual('open'));
    }
    
    public function test_Client_start(): void {
        $arguments = [
            'test'
        ];
        
        $actual = $this->client->executeAsyncScript(<<<EOT
async function test(dbName) {
    const { default: SuT } = await import("/slothsoft@sse/js/Client");
            
    const client = new SuT("/slothsoft@test-module/sse", dbName);
            
    return await new Promise(resolve => {
        client.addEventListener("error", eve => resolve(eve.type));
        client.addEventListener("start", eve => resolve(eve.type));
    });
}
            
import("/slothsoft@farah/js/Test").then(Test => Test.run(test, arguments));
EOT, $arguments);
        
        $this->assertThat($actual, new IsEqual('start'));
    }
}