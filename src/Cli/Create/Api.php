<?php
declare(strict_types = 1);

namespace Levis\App\Cli\Create;

use Levis\Svc\Convert;
use Apex\Cli\{Cli, CliHelpScreen};
use Levis\App\Opus\Builder;
use Apex\Cli\Interfaces\CliCommandInterface;

/**
 * Create REST API endpoint
 */
class Api implements CliCommandInterface
{

    #[Inject(Convert::class)]
    private Convert $convert;

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

        // Check
        if ($alias == '') {
            $cli->error("You did not specify a filename.");
            return;
        } elseif (file_exists(SITE_PATH . "/src/Api/" . $alias . ".php")) { 
            $cli->error("The CLI command already exists with alias, $alias.");
            return;
        }

        // Build
        $files = $this->builder->build('api', 'Api/' . $alias, []);

        // Success message
        $cli->success("Successfully created new REST API endpoint which is now available at:", $files);
    }

    /**
     * Help
     */
    public function help(Cli $cli):CliHelpScreen
    {

        $help = new CliHelpScreen(
            title: 'Create REST API Endpoint',
            usage: './levis create api <FILENAME>',
            description: 'Create new REST API endpoint'
        );

        // Params
        $help->addParam('filename', 'The filename of the REST API endpoint, relative to the /src/Api/ directory.');
        $help->addExample('./levis create api orders/create');

        // Return
        return $help;
    }

}


