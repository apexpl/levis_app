<?php
declare(strict_types = 1);

namespace Levis\App\RestApi;

use Levis\Svc\{Convert, Container, App, Di};
use Levis\App\RestApi\Models\ApiRoute;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Yaml\Yaml;
use Apex\App\Exceptions\ApexYamlException;

/**
 * Api Router
 */
class Router
{

    #[Inject(Convert::class)]
    private Convert $convert;

    #[Inject(Container::class)]
    private Container $cntr;

    #[Inject(App::class)]
    private App $app;

    /**
     * Lookup method
     */
    public function lookup(ServerRequestInterface $request):?ApiRoute
    {

        // Get path
        $parts = explode('/', preg_replace("/^api\//", "", trim($this->app->getPath(), '/')));
        if (count($parts) == 0) { 
            return null;
        }
        $method = strtolower($request->getMethod());

        // Check for individual class
        if ($route = $this->checkIndividualClass($method, $parts)) { 
            return $route;
        }

        // Return
        return null;
    }

    /**
     * Check individual route
     */
    private function checkIndividualClass(string $method, array $parts):?ApiRoute
    {

        // Get class name
        $parts = array_map(fn ($part) => $this->convert->case($part, 'title'), $parts);
        $class_name = "\\App\\Api\\" . implode("\\", $parts);
        if ($this->app->getBootType() == 'test') {
            $class_name = "\\Levis" . $class_name;
        }

        // Check if class exists
        if (!class_exists($class_name)) { 
            return null;
        }

        // Load object, check for acl level
        $obj = $this->cntr->make($class_name);

        // Check method
        if (!method_exists($obj, $method)) { 
            return null;
        }

        // Create Api route
        $route = $this->cntr->make(ApiRoute::class, [
            'class_name' => $class_name,
            'method' => $method,
            'middleware' => $obj
        ]);

        // Return
        return $route;
    }

}


