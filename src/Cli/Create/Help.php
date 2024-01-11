<?php
declare(strict_types=1);

namespace Levis\App\Cli\Create;

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
            title: 'Levis Create Commands',
            usage: './levis create <COMMAND> [<ARGS>]',
            description: 'Various Levis commands to generate class files such as models, views and API endpoints.'
        );
        $help->setParamsTitle('COMMANDS');

        $help->addParam('api', 'Create new REST API endpoint.');
        $help->addParam('cli', 'Create new CLI bsed command.');
        $help->addParam('http-controller', 'Create new HTTP controller class.');
        $help->addParam('model', 'Create new model class.');
        $help->addParam('test', 'Create new unit test class.');
        $help->addParam('view', 'Create new view.');

        // Return
        return $help;
    }

}


