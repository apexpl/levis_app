<?php
declare(strict_types = 1);

namespace Levis\App\Utils\Tests;

use Levis\App\App;
use Levis\Svc\{Di, Container, Db};
use Levis\App\Utils\Tests\CliStub;
use PHPUnit\Framework\TestCase;
use Nyholm\Psr7\Response;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ResponseInterface;

/**
 * APex test case
 */
class LevisTestCase extends TestCase
{

    // Properties
    protected App $app;
    protected Container $cntr;
        private ?CliStub $cli = null;

    /**
     * Constructor
     */
    public function __construct()
    {

        // Load phpUnit
        parent::__construct("na");

        // Init app
        $this->app = Di::get(App::class);
        $this->cntr = $this->app->getContainer();
    }

    /**
     * Send CLI Command
     */
    protected function levis(string $command, array $inputs = [], bool $do_confirm = true):string
    {

        /// Check if loaded
        if ($this->cli === null) {
            $this->cli = $this->cntr->make(CliStub::class);
            $this->cntr->set(\Apex\Cli\Cli::class, $this->cli);
        }

        // Get response
        $args = explode(' ', $command);
        $res = $this->cli->run($args, $inputs, $do_confirm);
        return $res;
    }

    /**
     * Send test http request
     */
    public function http(string $uri, string $method = 'GET', ?array $post = null, array $get = [], array $cookie = [], array $headers = [], ?string $host = null):ResponseInterface
    {

        // Set variables for request
        $server = [
            'REQUEST_URI' => $uri,
            'HTTP_HOST' => $host === null ? $this->app->config('core.domain_name') : $host,
            'REQUEST_METHOD' => strtoupper($method)
        ];

        // Set env variables
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['REQUEST_METHOD'] = strtoupper($method);

        // Create factory
        $factory = new \Nyholm\Psr7\Factory\Psr17Factory();
        $request_creator = new ServerRequestCreator($factory, $factory, $factory, $factory);

        // Create the server request
        $request = $request_creator->fromArrays(
            $server, 
            $headers, 
            $cookie,
            $get,
            $post
        );

        // Send http request and return
        $res = $this->app->handle($request);
        $this->res_body = $res->getBody()->getContents();
        return $res;
    }

    /**
     * Invoke private / protected method
     */
    public function invokeMethod(object $object, string $method_name, array $params = []):mixed
    { 

        // Get method via reflection
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($method_name);
        $method->setAccessible(true);

        // Call method, and return results
        return $method->invokeArgs($object, $params);
    }

    /**
     * Wait for an exception
     */
    public function waitException(string $message, ?string $exception_class = null):void
    {

        // Get class name
        if ($exception_class === null) {
            $exception_class = \Exception::class;
        }

        // Expect exception
        $this->expectException($exception_class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage($message);
    }

    /**
     * Junk method so phpUnit doesn't give warnings.
     */
    public function test_junk():void
    {
        $this->assertTrue(true);
    }


}


