<?php
declare(strict_types = 1);

namespace Levis\App\RestApi\Models;

/**
 * Api Route
 */
class ApiRoute
{

    /**
     * Constructor
     */
    public function __construct(
        private string $class_name = '',
        private string $method = 'process',
        private ?object $middleware = null,
        private array $params = []
    ) { 

    }

    /**
     * Get class
     */
    public function getClassName():string
    {
        return $this->class_name;
    }

    /**
     * Get method
     */
    public function getMethod():string
    {
        return $this->method;
    }

    /**
     * Get object
     */
    public function getMiddleware():?object
    {
        return $this->middleware;
    }

    /**
     * Get params
     */
    public function getParams():array
    {
        return $this->params;
    }

    /**
     * Set class name
     */
    public function setClassName(string $class_name):void
    {
        $this->class_name = $class_name;
    }

    /**
     * Set method
     */
    public function setMethod(string $method):void
    {
        $this->method = $method;
    }

    /**
     * Set middleware
     */
    public function setMiddleware(object $middleware):void
    {
        $this->middleware = $middleware;
    }

    /**
     * Set params
     */
    public function setParams(array $params):void
    {
        $this->params = $params;
    }

}

