<?php
declare(strict_types = 1);

namespace Levis\App\Cli\Create;

use Levis\Svc\Container;
use Apex\Cli\{Cli, CliHelpScreen};
use Levis\App\Opus\Builder;
use Apex\Router\RouterConfig;
use Apex\Cli\Interfaces\CliCommandInterface;

/**
 * Create view
 */
class View implements CliCommandInterface
{

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
        $opt = $cli->getArgs(['route']);
        $route = $opt['route'] ?? '';
        $uri = preg_replace("/\.html$/", "", trim(strtolower(($args[0] ?? '')), '/'));

        // Check
        if ($uri == '' || !filter_var('https://domain.com/' . $uri, FILTER_VALIDATE_URL)) { 
            $cli->error("Invalid uri specified, $uri");
            return;
        } elseif (file_exists(SITE_PATH . "/views/html/$uri.html")) { 
            $cli->error("The view already exists with uri, $uri");
            return;
        }

        // Get parent namespace
        $parts = explode('/', $uri);
        $alias = array_pop($parts);
        $parent_nm = count($parts) > 0 ? "\\" . implode("\\", $parts) : '';

            // Build view
        $files = $this->builder->build('view', $uri, [
            'uri' => $uri, 
            'alias' => $alias, 
            'parent_namespace' => $parent_nm
        ]);

        // Add route, if needed
        if ($route != '') {
            $router_config = $this->cntr->make(RouterConfig::class, ['routes_yaml_file' => SITE_PATH . '/config/routes.yml']);
            $router_config->add($route, 'PublicSite', 'default');
        }

        // Success message
        $cli->success("Successfully created new view for URI $uri, and files are now available at:", $files);
    }

    /**
     * Help
     */
    public function help(Cli $cli):CliHelpScreen
    {

        $help = new CliHelpScreen(
            title: 'Create View',
            usage: './levis create view <URI> [--route=<ROUTE>]',
            description: 'Create a new view.'
        );

        // Params
        $help->addParam('uri', 'The URI of the new view, as will be viewed within the web browser and placed relative to the /views/html/ directory.');
        $help->addFlag('--route', "Optional route, and if defined will add new entry into /config/routes.yml file.");
        $help->addExample('./levis create view admin/products/add');

        // Return
        return $help;
    }

}


