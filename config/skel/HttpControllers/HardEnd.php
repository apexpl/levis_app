<?php
declare(strict_types = 1);

namespace Levis\App\HttpControllers;

use Nyholm\Psr7\Response;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

/**
 * Http Controller - RestApi
 */
class HardEnd implements MiddlewareInterface
{

    /**
     * Process request
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $app): ResponseInterface
    {
        return new Response(body: "At Hard End");
    }

}


