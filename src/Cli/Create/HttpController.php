<?php
declare(strict_types = 1);

namespace Levis\App\Cli\Create;

use Levis\Svc\{Convert, Container};
use Apex\Cli\{Cli, CliHelpScreen};
use Levis\App\Opus\Builder;
use Apex\Router\RouterConfig;
use Apex\Cli\Interfaces\CliCommandInterface;

/**
 * Create REST API endpoint
 */
class HttpController implements CliCommandInterface
{

    #[Inject(Convert::class)]
    private Convert $convert;

    #[Inject(Container::class)]
    private Container $cntr;

    #[Inject(Builder::class)]
    private Builder $builder;

    /**
     * Process
     */
    public function process(Cli $cli, array $args):void
    {

        // Initialize
        $alias = implode('/', array_map(function ($part) {
            return $this->convert->case($part, 'title');
        }, explode('/', ($args[0] ?? ''))));

        // Get route
        $opt = $cli->getArgs(['route']);
        $route = $opt['route'] ?? '';

        // Check
        if ($alias == '') {
            $cli->error("You did not specify a filename.");
            return;
        } elseif (file_exists(SITE_PATH . "/src/HttpControllers/" . $alias . ".php")) { 
            $cli->error("The HTTP controller already exists with alias, $alias.");
            return;
        }

        // Build
        $files = $this->builder->build('http_controller', 'HttpControllers/' . $alias, []);

        // Add route, if needed
        if ($route != '') {
            $router_config = $this->cntr->make(RouterConfig::class, ['routes_yaml_file' => SITE_PATH . '/config/routes.yml']);
            $router_config->add($route, $alias, 'default');
        }

        // Success message
        $cli->success("Successfully created new HTTP controller which is now available at:", $files);
    }

    /**
     * Help
     */
    public function help(Cli $cli):CliHelpScreen
    {

        $help = new CliHelpScreen(
            title: 'Create HTTP Controller',
            usage: './levis create http-controller <FILENAME> [--route=<ROUTE>]',
            description: 'Create new HTTP controller'
        );

        // Params
        $help->addParam('filename', 'The filename of the HTTP controller, relative to the /src/HttpControllers/ directory.');
        $help->addFlag('--route', "The uri / route to add into the routes.yml file denoting which HTTP requests are sent to this controller.");
        $help->addExample('./levis create http-controller view-orders --route order');

        // Return
        return $help;
    }

}


