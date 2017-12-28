<?php
namespace Slothsoft\CMS;

use Slothsoft\SSE\VCTServer;
$sseName = $this->httpRequest->getInputValue('name');
$sseMode = $this->httpRequest->getInputValue('mode');
$lastId = $this->httpRequest->getInputValue('lastId');
if ($id = $this->httpRequest->getHeader('HTTP_LAST_EVENT_ID')) {
    $lastId = $id;
}
$userId = null;

$sse = new VCTServer($sseName);
$sse->init($lastId, $userId);

$ret = null;

switch ($sseMode) {
    case 'push':
        $sse->dispatchEvent($this->httpRequest->getInputValue('type'), $this->httpRequest->getInputJSON());
        $this->httpResponse->setStatus(HTTPResponse::STATUS_NO_CONTENT);
        $this->progressStatus = self::STATUS_RESPONSE_SET;
        break;
    case 'pull':
        $ret = $sse->getStream();
        break;
    case 'last':
        break;
}

return $ret;