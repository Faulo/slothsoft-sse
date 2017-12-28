<?php
namespace Slothsoft\Farah;

use Slothsoft\SSE\UnisonServer;
$sseName = $this->httpRequest->getInputValue('name');
$sseMode = $this->httpRequest->getInputValue('mode');
$lastId = $this->httpRequest->getInputValue('lastId');
if ($id = $this->httpRequest->getHeader('HTTP_LAST_EVENT_ID')) {
    $lastId = $id;
}

$sse = new UnisonServer($sseName);
$sse->init($lastId);

$ret = null;

switch ($sseMode) {
    case 'push':
        $sse->dispatchEvent($this->httpRequest->getInputValue('type'), $this->httpRequest->getInput());
        $this->httpResponse->setStatus(HTTPResponse::STATUS_NO_CONTENT);
        $this->progressStatus = self::STATUS_RESPONSE_SET;
        break;
    case 'pull':
        $ret = $sse->getStream();
        break;
    case 'last':
        $ret = $sse->fetchLastEvent();
        $ret = HTTPFile::createFromJSON($ret);
        break;
}

return $ret;