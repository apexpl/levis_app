<?php
declare(strict_types=1);

namespace Levis\App\Cli\Create;

use Levis\Svc\Db;
use Apex\Cli\{Cli, CliHelpScreen};
use Levis\App\Opus\ModelBuilder;
use Apex\Cli\Interfaces\CliCommandInterface;

/**
 * Create model
 */
class Model implements CliCommandInterface
{

    #[Inject(Db::class)]
    private Db $db;

    #[Inject(ModelBuilder::class)]
    private ModelBuilder $builder;

    /**
     * Process
     */
    public function process(Cli $cli, array $args):void
    {

        // Get args
        $opt = $cli->getArgs(['dbtable']);
        $filename = trim(($args[0] ?? ''), '/');
        $dbtable = $opt['dbtable'] ?? '';
        $magic = $opt['nomagic'] ?? true;

        // Perform checks
        if ($filename == '') {
            $cli->error("You did not specify a filename to create.");
            return;
        } else if ($dbtable == '' || !$this->db->checkTable($dbtable)) {
            $cli->error("The database table '$dbtable' does not exist.");
            return;
        }

        // Format filename
        //$filename = 'src/' . ltrim(rtrim($filename, '.php'), 'src/') . '.php');
        $filename = 'src/' . ltrim(rtrim($filename, '.php'), 'src/') . '.php';
        if (file_exists(SITE_PATH . '/' . $filename)) {
            $cli->error("File already exists at, $filename");
            return;
        }

        // Build
        $files = $this->builder->build($filename, $dbtable, $magic);

        // Success message
        $cli->success("Successfully created new model from database table '$dbtable' which is now available at:", $files);
    }

    /**
     * Help
     */
    public function help(Cli $cli):CliHelpScreen
    {

        $help = new CliHelpScreen(
            title: 'Generate Model',
            usage: './levis create model <FILENAME> --dbtable=<TABLE> [--nomagic]',
            description: 'Generate a new model class.'
        );

        // Params
        $help->addParam('filename', 'File location of the new model class, relative to the /src/ directory.');
        $help->addFlag('--dbtable', 'The name of the database table to use to generate property names.');
        $help->addFlag('--nomagic', "If present, will generate model with hard coded get / set methods and instead allow properties to be accessed directly.  .  Otherwise, will generate model with hard coded get / set methods.");

        // Examples
        $help->addExample('./levis create MyShop/Models/Products --dbtable shop_products');

        // Return
        return $help;
    }

}








    
