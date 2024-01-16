<?php
declare(strict_types = 1);

namespace Levis\App\Cli;

use Levis\Svc\{App, Db};
use Apex\Cli\{Cli, CliHelpScreen};
use Apex\Cli\Interfaces\CliCommandInterface;

/**
 * DUmp database
 */
class DumpDb implements CliCommandInterface
{

    #[Inject(App::class)]
    private App $app;

    #[Inject(Db::class)]
    private Db $db;

    /**
     * Process
     */
    public function process(Cli $cli, array $args):void
    {

        // Initialize
        $file = $args[0] ?? 'dump.sql';

        // Check if file exists
        if (file_exists(SITE_PATH . '/' . $file)) {
            $cli->send("The SQL dump file at $file already exists on the local machine.  This operation will overwrite the file.");
            if (!$cli->getConfirm("Are you sure you want to continue?")) {
                $cli->send("Ok, goodbye.\r\n\r\n");
                return;
            }
            unlink(SITE_PATH . '/' . $file);
        }

        // Get db driver
        if (!$dbinfo = $this->app->config('database')) {
            $cli->error("No database credentials are defined within the /config/config.yml file.");
            return;
        }
        $db_driver = strtolower($dbinfo['driver']);

        // Check for SQLite
        if ($db_driver == 'sqlite') {
            $cli->error("You are running SQLite for the database, hence there is no dump available.");
            return;
        }

        // Get cmd
        if ($db_driver == 'PostgreSQL') {
            if ($file == 'dump.sql') {
                $file = 'database.dump';
            }
            $dsn = 'postgresql://' . $dbinfo['user'] . ':' . $dbinfo['password'] . '@' . $dbinfo['host'] . ':' . $dbinfo['port'] . '/' . $dbinfo['dbname'];
            $cmd = "pg_dump -Fc $dsn -f " . SITE_PATH . '/' . $file; 
        } else { 
            $cmd = "mysqldump -u$dbinfo[user] -p$dbinfo[password] -h$dbinfo[host] -P$dbinfo[port] $dbinfo[dbname] > " . SITE_PATH . '/' . $file;
        }

        // Dump database
        shell_exec($cmd);

        // Check for success
        if (!file_exists(SITE_PATH . '/' . $file)) {
            $cli->error("There was an error in dumping the database.  Please ensure the database information is correct, or manually dump it.");
            return;
        }

        // Success
        $cli->success("The $db_driver database has been successfully dumped, and the SQL dump file can be found at:", [$file]);
    }

    /**
     * Help
     */
    public function help(Cli $cli):CliHelpScreen
    {

        $help = new CliHelpScreen(
            title: 'Dump SQL Database',
            usage: './levis dump-db [<FILE>]',
            description: 'Dumps the SQL database into a SQL dump file.'
        );

        $help->addParam('file', 'Optional filename to dump the SQL database to.  Defaults to dump.sql');
        $help->addExample('./levis dump-db mydb.sql');

        // Return
        return $help;
    }

}


