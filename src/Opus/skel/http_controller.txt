<?php
declare(strict_types = 1);

namespace ~namespace~;

use Apex\Svc\View;
use Nyholm\Psr7\Response;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

/**
 * Http Controller - ~alias~
 */
class ~class_name~ implements MiddlewareInterface
{

    #[Inject(View::class)]
    private View $view;

    /**
     * Process request
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $app): ResponseInterface
    {

        // Render template via auto-routing based on URI being viewed
        $html = $this->view->render();

        // Create PSR7 compliant response
        $response = new Response(
            body: $html
        );

        // Return
        return $response;
    }

}




