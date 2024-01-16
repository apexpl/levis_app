<?php
declare(strict_types = 1);

namespace Levis\App\Boot;

use Apex\Container\Interfaces\ApexContainerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Log\LoggerInterface;
use Apex\Db\Interfaces\DbInterface;
use Apex\Router\Interfaces\RouterInterface;
use Apex\Mercury\Interfaces\EmailerInterface;
use Apex\Syrus\Interfaces\LoaderInterface;
use Monolog\Handler\StreamHandler;

/**
 * Container builder
 */
class Container
{

    /**
     * Build container
     */
    public static function build(array $config, string $boot_type = 'app', bool $first_boot = true):ApexContainerInterface
    {

        // Load container
        $cntr = new \Apex\Container\Container(use_attributes: true);

        // Get iniital items
        $items = require(SITE_PATH . '/config/container.php');
        $services = $items['services'] ?? [];
        unset($items['services']);

        // Load initial items into container
        $cntr->buildContainer('', $items);

        // Set system items
        $cntr = self::setSystemItems($cntr, $config, $boot_type);

        // Set database credentials
        $cntr = self::setDatabaseCredentials($cntr, $config);

        // Mark items as services
        $cntr = self::markServices($cntr, $services);

        // Define necessary aliases
        if ($first_boot === true) {
            $cntr = self::defineAliases($cntr, $items, $boot_type);
        }


        // Set into Di wrapper
        \Apex\Container\Di::setContainer($cntr);

        // Return
        return $cntr;
    }

    /**
     * Set system items
     */
    private static function setSystemItems(ApexContainerInterface $cntr, array $config, string $boot_type = 'app'):ApexContainerInterface
    {

        // Initialize
        $router_middleware = $boot_type == 'test' ? "Levis\\App\\HttpControllers" : "App\\HttpControllers";

        // Define items
        $sys_items = [ 
            RouterInterface::class => [\Apex\Router\Router::class, [
                'routes_yaml_file' => SITE_PATH . '/config/routes.yml',
                'middleware_namespace' => $router_middleware
            ]],
            LoggerInterface::class => function() { 
                return new \Monolog\Logger('app', [
                    new StreamHandler(SITE_PATH . '/logs/debug.log', \Monolog\Logger::DEBUG), 
                    new StreamHandler(SITE_PATH . '/logs/app.log', \Monolog\Logger::INFO), 
                    new StreamHandler(SITE_PATH . '/logs/error.log', \Monolog\Logger::ERROR) 
                ]);
            },
            EmailerInterface::class => [\Apex\Mercury\Email\Emailer::class, ['smtp' => ($config['smtp'] ?? [])]],
            \Apex\Syrus\Syrus::class => [\Apex\Syrus\Syrus::class, ['container_file' => null]],
            LoaderInterface::class => \Levis\App\Utils\SyrusAdapter::class,
            'syrus.template_dir' => SITE_PATH . '/views', 
            'syrus.site_yml' => SITE_PATH . '/config/routes.yml', 
            'syrus.require_http_method' => true,
            'syrus.theme_uri' => '/theme', 
            'syrus.php_namespace' => "Views", 
            'syrus.enable_autorouting' => true, 
            'syrus.auto_extract_title' => true, 
            'syrus.use_cluster' => false,
            'syrus.tag_namespaces' => ["Apex\\Syrus\\Tags"]
        ];

        // Set system items into container
        foreach ($sys_items as $item => $value) { 
            $cntr->set($item, $value);
        }

        // Return
        return $cntr;
    }

    /**
     * Set database credentials
     */
    private static function setDatabaseCredentials(ApexContainerINterface $cntr, array $config):ApexContainerInterface
    {

        // Initialize
        $dbinfo = $config['database'] ?? null;
        if ($dbinfo === null || (!is_array($dbinfo)) || (!isset($dbinfo['driver']))) {
            return $cntr;
        }

        // Get driver
        $driver = match (strtolower($dbinfo['driver'])) {
            'mysql' => \Apex\Db\Drivers\mySQL\mySQL::class,
            'postgresql' => \Apex\Db\Drivers\PostgreSQL\PostgreSQL::class,
            'sqlite' => \Apex\Db\Drivers\SQLite\SQLite::class,
            default => ''
        };

        // Check for no driver
        if ($driver == '') {
            return $cntr;
        } elseif (str_ends_with($driver, 'SQLite') && str_starts_with($dbinfo['dbname'], '~')) {
            $dbinfo['dbname'] = preg_replace("/^~/", SITE_PATH, $dbinfo['dbname']);
        }

        // Set in container
        $cntr->set(DbInterface::class, [$driver, ['params' => $dbinfo]]);
        $cntr->markItemAsService(DbInterface::class);
        $cntr->markItemAsService(\Levis\Svc\Db::class);

        // Return
        return $cntr;
    }

    /**
     * Mark items as services
     */
    private static function markServices(ApexContainerInterface $cntr, array $services):ApexContainerInterface
    {

        // Define services
        $sys_services = [
            \Apex\Armor\Armor::class, 
            \Apex\Syrus\Syrus::class,
            \Levis\Svc\Container::class,
            \Levis\Svc\Convert::class,
            \Levis\Svc\Emailer::class,
            \Levis\Svc\HttpClient::class,
            \Levis\Svc\Logger::class,
            \Levis\Svc\View::class,
            EmailerInterface::class,
            LoggerInterface::class
        ];
        $services = array_merge($services, $sys_services);

        // Mark items as services
        foreach ($services as $class_name) { 
            $cntr->markItemAsService($class_name);
        }

        // Return
        return $cntr;
    }

    /**
     * Define aliases
     */
    private static function defineAliases(ApexContainerInterface $cntr, array $items, string $boot_type = 'app'):ApexContainerInterface
    {

        // Set aliases
        $aliases = [ 
            \Levis\Svc\App::class => \Levis\App\App::class, 
            \Levis\Svc\Container::class => ContainerInterface::class, 
            \Levis\Svc\Convert::class => \Levis\App\Utils\Convert::class,
            \Levis\Svc\Db::class => DbInterface::class,
            \Levis\Svc\Emailer::class => EmailerInterface::class,
            \Levis\Svc\HttpClient::class => HttpClientInterface::class,
            \Levis\Svc\Logger::class => LoggerInterface::class,
            \Levis\Svc\View::class => \Apex\Syrus\Syrus::class
        ];

        // If unit ests are being executed
        if ($boot_type == 'test') {
            $aliases[\Apex\Cli\Cli::class] = \Levis\App\Utils\Tests\CliStub::class;
        }

        // Mark aliases
        foreach ($aliases as $item => $alias) { 
            $cntr->addAlias($item, $alias);
        }
        //$cntr->addAlias(\Apex\Syrus\Syrus::class, \Levis\Svc\View::class, false);

        // Return
        return $cntr;
    }

}

