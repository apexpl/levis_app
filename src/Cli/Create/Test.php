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
class Test implements CliCommandInterface
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
        $alias = $this->convert->case(($args[0] ?? ''), 'title');

        // Check
        if ($alias == '') {
            $cli->error("You did not specify a filename.");
            return;
        } elseif (file_exists(SITE_PATH . "/tests/" . $alias . 'Test.php')) {
            $cli->error("The test already exists with alias, $alias.");
            return;
        }

        // Build
        $files = $this->builder->build('test', $alias, [
            'alias' => $alias
        ]);

        // Success message
        $cli->success("Successfully created new test which is now available at:", $files);
    }

    /**
     * Help
     */
    public function help(Cli $cli):CliHelpScreen
    {

        $help = new CliHelpScreen(
            title: 'Create test class',
            usage: './levis create test <ALIAS>',
            description: 'Create new unit test class'
        );

        // Params
        $help->addParam('alias', 'The alias of the test class, relative to the /tests/ directory.');
        $help->addExample('./levis create test orders');

        // Return
        return $help;
    }

}


