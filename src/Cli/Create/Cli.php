<?php
declare(strict_types = 1);

namespace Levis\App\Cli\Create;

use Levis\Svc\Convert;
use Apex\Cli\CLi as CliUtils;
use Apex\Cli\CliHelpScreen;
use Levis\App\Opus\Builder;
use Apex\Cli\Interfaces\CliCommandInterface;

/**
 * Create CLI command
 */
class Cli implements CliCommandInterface
{

    #[Inject(Convert::class)]
    private Convert $convert;

    #[Inject(Builder::class)]
    private Builder $builder;

    /**
     * Process
     */
    public function process(CliUtils $cli, array $args):void
    {

        // Initialize
        $alias = implode('/', array_map(function ($part) {
            return $this->convert->case($part, 'title');
        }, explode('/', ($args[0] ?? ''))));

        // Check
        if ($alias == '') {
            $cli->error("You did not specify a filename.");
            return;
        } elseif (file_exists(SITE_PATH . "/src/Console/" . $alias . ".php")) { 
            $cli->error("The CLI command already exists with alias, $alias.");
            return;
        }

        // Build
        $files = $this->builder->build('cli', 'Console/' . $alias, [
            'alias' => $alias
        ]);

        // Success message
        $cli->success("Successfully created new CLI command which is now available at:", $files);
    }

    /**
     * Help
     */
    public function help(CliUtils $cli):CliHelpScreen
    {

        $help = new CliHelpScreen(
            title: 'Create CLI Command',
            usage: './levis create cli <FILENAME>',
            description: 'Create new CLI command.'
        );

        // Params
        $help->addParam('filename', 'The filename of the CLI command, relative to the /src/Console/ directory.");
        $help->addExample('./levis create cli invoices/generate');

        // Return
        return $help;
    }

}


