<?php
declare(strict_types = 1);

namespace Levis\App\Cli;

use Levis\Svc\{App, Db};
use Apex\Cli\{Cli, CliHelpScreen};
use Symfony\Component\Process\Process;
use Apex\Cli\Interfaces\CliCommandInterface;

/**
 * Execute SQL
 */
class Sql Implements CliCommandInterface
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

        // Get options
        $opt = $cli->getArgs(['file']);
        $file = $opt['file'] ?? '';

        // Check
        if ($file == '' && !isset($args[0])) { 
            $this->connect($cli);
            return;
        } elseif ($file != '' && !file_exists($file)) { 
            $cli->error("No file exists at the location, $file");
            return;
        }

        // Execute file, if needed
        if ($file != '') { 
            $this->db->executeSqlFile($file);
            $cli->send("Successfully executed all SQL statements within the file, $file\r\n\r\n");
            return;
        }

        // Execute
        $sql = $args[0];
        $result = $this->db->query($sql);

        // Give results of select statement
        $res = [];
        if (preg_match("/^select/i", $sql)) { 

            // Get column names
            $column_names = [];
            for ($x=0; $x < $result->columnCount(); $x++) { 
                $info = $result->getColumnMeta($x);
                $column_names[] = $info['name'];
            }
            $res[] = $column_names;

            // Go through rows
            while ($row = $this->db->fetchArray($result)) { 
                $res[] = $row;
            }

            // Display table
            $cli->sendTable($res);
        } else { 
            $cli->send("Successfully executed SQL statement against database.\r\n\r\n");
        }

    }

    /**
     * Connect
     */
    public function connect(Cli $cli):void
    {

        // Get db driver
        if (!$dbinfo = $this->app->config('database')) {
            $cli->error("No database credentials are defiend within the /config/config.yml file.");
            return;
        }
        $driver = strtolower($dbinfo['driver']);

        // Get database command
        $cmd = match($driver) {
            'postgresql' => 'psql',
            'sqlite' => 'sqlite3',
            default => 'mysql'
        };

        // Set args
        if ($driver == 'sqlite') {
            $args = [$cmd, preg_replace("/^~/", SITE_PATH, $dbinfo['dbname'])];
        } elseif ($driver == 'postgresql') {
            $dsn = 'postgresql://' . $dbinfo['user'] . ':' . $dbinfo['password'] . '@' . $dbinfo['host'] . ':' . $dbinfo['port'] . '/' . $dbinfo['dbname'];
            $args = [$cmd, $dsn];
        } else {
            $args = [$cmd, '-u' . $dbinfo['user'], '-p' . $dbinfo['password'], '-h' . $dbinfo['host'], '-P' . $dbinfo['port'], $dbinfo['dbname']];
        }

        // Run process
        $process = new Process($args);
        $process->setTty(true);
        $process->run();
    }

    /**
     * Help
     */
    public function help(Cli $cli):CliHelpScreen
    {

        $help = new CliHelpScreen(
            title: 'Execute SQL Statement',
            usage: './levis sql [<SQL>] [--file=<FILENAME>]',
            description: 'Execute a single SQL statement against the database, or a SQL file.  If run with no argument, you will be connected to the SQL database and given its prompt.'
        );

        $help->addParam('sql', 'The SQL statement to execute against the database.');
        $help->addFlag('--file', 'Optional location of the file containing SQL statements to execute.');
        $help->addExample('./levis sql "SELECT * FROM admin"');
        $help->addExample('./levis sys sql --file dev.sql');

        // Return
        return $help;
    }

}


