<?php
declare(strict_types = 1);

namespace Levis\App\HttpControllers;

use Nyholm\Psr7\Response;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

/**
 * Http Controller - RestApi
 */
class ViewOrder implements MiddlewareInterface
{

    /**
     * Process request
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $app): ResponseInterface
    {

        // Get body
        $order_id = $app->pathParam('order_id');
        return new Response(
            body: "Order: $order_id"
        );

    }

}

