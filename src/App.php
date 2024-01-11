<?php
declare(strict_types=1);

namespace Levis\App;

use Levis\App\Boot\Bootloader;
use Apex\Router\Interfaces\RouterInterface;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface, UriInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Apex\Container\Interfaces\ApexContainerInterface;

/**
 * Central app class for Levis
 */
class App extends Bootloader implements RequestHandlerInterface
{

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->bootload();
        $this->cntr->set(__CLASS__, $this);
    }

    /**
     * Get container
     */
    public function getContainer():ApexContainerInterface
    {
        return $this->cntr;
    }

    /**
     * Get server request
     */
    public function getRequest():ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * Handle request
     */
    public function handle(ServerRequestInterface $request):ResponseInterface
    {

        // Lookup route
        $router = $this->cntr->make(RouterInterface::class);
        $res = $router->lookup($request);
        $this->path = $res->getPathTranslated();

        // Set variables
        $this->replacePathParams($res->getPathParams());
        $controller_class = $res->getMiddleWare();
        $controller = $this->cntr->make($controller_class);

        // Process request
        $response = $controller->process($request, $this);

        // Return
        return $response;
    }

    /**
     * Output response
     */
    public function outputResponse(ResponseInterface $response):void
    {

        // Set status
        http_response_code($response->getStatusCode());

        // Set headers
        $headers = $response->getHeaders();
        foreach ($headers as $key => $values) { 
            $line = $key . ': ' . $response->getHeaderLine($key);
            header($line);
        }

        // Send body
        echo $response->getBody();
    }

    /**
     * Get path
     */
    public function getPath():string
    {
        return $this->path;
    }

    /**
     * Get host
     */
    public function getHost():string
    {
        return $this->request->getUri()->getHost();
    }

    /**
     * Get port
     */
    public function getPort():int
    {
        return $this->request->getUri()->getPort();
    }

    /**
     * get method
     */
    public function getMethod():string
    {
        return $this->request->getMethod();
    }

    /**
     * Get content type
     */
    public function getContentType():string
    {
        return $this->content_type;
    }

}

