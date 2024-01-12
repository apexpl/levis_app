<?php
declare(strict_types = 1);

namespace Levis\App\Boot;

use Nyholm\Psr7Server\ServerRequestCreator;
use Apex\Container\Interfaces\ApexContainerInterface;
use Psr\Http\Message\{ServerRequestInterface, UriInterface};
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Boot loader for Levis, helps initialize the request, sanitize inputs, et tal.
 */
class Bootloader extends RequestInputs
{

    // Properties
    protected ?ApexContainerInterface $cntr = null;
    protected ServerRequestInterface $request;
    protected string $path;
    protected string $content_type = 'text/html';
    protected string $boot_type = 'app';

    /**
     * Load request
     */
    protected function bootload(string $boot_type = 'app')
    {

        // Initialize
        $first_boot = $this->initialize();

        // Load configuration
        $this->loadConfigVars();

        // Build DI container
        $this->cntr = Container::build($this->_config, $boot_type, $first_boot);

        // Generate PSR7 compliant http request
        $this->setRequest();
    }

    /**
     * Initialize
     */
    private function initialize():bool
    {

        // Check if already initialized
        if (defined('SITE_PATH')) {
            return false;
        }

        // Define site_path
        $obj = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
        define('SITE_PATH', realpath(dirname($obj->getFileName()) . '/../../'));

        // Set time zone
        date_default_timezone_set('UTC');

        // Set error reporting
        $error_handlers = new ErrorHandlers();
        error_reporting(E_ALL);
        set_error_handler([$error_handlers, 'error']);
        set_exception_handler([$error_handlers, 'handleException']);

        // Set INI variables
        ini_set('pcre.backtrack_limit', '4M');
        ini_set('zlib.output_compression_level', '2');

        // Return
        return true;
    }

    /**
     * Load config vars
     */
    private function loadConfigVars():void
    {

        // Load file
        try {
            $this->_config = Yaml::parseFile(SITE_PATH . '/config/config.yml');
        } catch (ParseException $e) { 
            throw new ParseException("Unable to parse 'config.yml' YAML file, error: " . $e->getMessage());
        }

    }

    /**
     * Generate PSR7 compliant http request
     */
    public function setRequest(?ServerRequestInterface $request = null):void
    {

        // Generate server request, if one not specified
        if ($request === null) {
            $factory = new \Nyholm\Psr7\Factory\Psr17Factory();
            $creator = new ServerRequestCreator($factory, $factory, $factory, $factory);
            $this->request = $creator->fromGlobals();
        } else {
            $this->request = $request;
        }

        // Set inputs
        $this->inputs = [
            'get' => $this->request->getQueryParams() ?? [],
            'post' => $this->request->getParsedBody() ?? [],
            'cookie' => $this->request->getCookieParams() ?? [],
            'files' => $this->request->getUploadedFiles() ?? [],
            'server' => $this->request->getServerParams() ?? [],
            'path_params' => []
        ];

        // Set addl properties
        $this->cntr->set(UriInterface::class, $this->request->getUri());
        $this->cntr->set(ServerRequestInterface::class, $this->request);
        $this->path = $this->request->getUri()->getPath();
    }

}

