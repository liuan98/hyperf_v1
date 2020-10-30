<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace App\Exception\Handler;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class AppExceptionHandler extends ExceptionHandler
{
    private $logger;//
    public function __construct()
    {
        $container = ApplicationContext::getContainer();
        $this->logger = $container->get(LoggerFactory::class)->get('http');
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        //$this->logger->warning(sprintf('AppExceptionHandler_error::__call failed, because ' . $throwable->getCode() . $throwable->getMessage()));
        if($throwable->getCode() >= 500) $this->logger->error(sprintf('AppExceptionHandler_error::__call failed, because ' . $throwable->getCode() .'|'. $throwable->getMessage()));
        $data = json_encode([
            'code' => $throwable->getCode(),
            'message' => $throwable->getMessage(),
        ], JSON_UNESCAPED_UNICODE);

        // 阻止异常冒泡
        //$this->stopPropagation();
        return $response->withStatus(500)->withBody(new SwooleStream($data));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
