<?php
declare(strict_types=1);

namespace Levis\App\Cli;

use Apex\CLi\{Cli, CliHelpScreen};

/**
 * Help
 */
class Help
{

    /**
     * Help
     */
    public function help(Cli $cli):CliHelpScreen
    {

        $help = new CliHelpScreen(
            title: 'Levis Commands',
            usage: './levis <COMMAND> [<ARGS>]',
            description: 'Various Levis commands to generate class files such as models, views and API endpoints.'
        );
        $help->setParamsTitle('CATEGORIES');

        $help->addParam('create', 'Comments to create various classes for models, views, HTTP controllers, etc.');

        // Return
        return $help;
    }

}


