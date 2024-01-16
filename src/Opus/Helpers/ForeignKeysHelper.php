<?php
declare(strict_types = 1);

namespace Levis\App\Opus\Helpers;

use Levis\Svc\Db;
use Levis\App\Utils\Io;
use Levis\App\Model\{BaseModel, MagicModel};
use Levis\App\Opus\AbstractBuilder;

/**
 * Foreign Keys
 */
class ForeignKeysHelper extends AbstractBuilder
{

    #[Inject(Db::class)]
    private Db $db;

    #[Inject(Io::class)]
    private Io $io;

    // Properties
    private string $use_declarations = '';
    private array $model_classes = [];
    public static array $queue = [];
    public static array $tbl_created = [];
    public static array $tbl_skipped = [];


    /**
     * Apply foreign keys
     */
    public function apply(string $code, string $dbtable, string $dirname, bool $with_magic, array $props, bool $auto_confirm = false):string
    {

        // Initialize
        $this->use_declarations = '';
        ForeignKeysHelper::$queue = [];
        $this->model_classes = $this->getModelClasses();

        // Get keys
        $keys = $this->getForeignKeys($dbtable, $dirname, $with_magic, $props, $auto_confirm);
        $referenced_keys = $this->getReferencedForeignKeys($dbtable, $dirname, $with_magic, $props, $auto_confirm);

        // Apply relationships
        $code = $this->applyRelationships($code, $keys, $referenced_keys, $auto_confirm);
        $code = str_replace('~use_declarations~', $this->use_declarations, $code);

        // Return
        return $code;
    }

    /**
     * Get foreign keys
     */
    private function getForeignKeys(string $dbtable, string $dirname, bool $with_magic, array $props, bool $auto_confirm = false):array
    {

        // Get foreign keys
        $keys = $this->db->getForeignKeys($dbtable);

        // Go though keys
        foreach ($keys as $column => $vars) { 

            // Check for class name
            $class_name = $this->model_classes[$vars['table']] ?? '';
            if ($class_name == '' && !$class_name = $this->generateClass($vars['table'], $dirname, $with_magic, $auto_confirm)) {
                unset($keys[$column]);
                continue;
            }

            // Add to results
            $keys[$column]['class_name'] = $class_name;
        }

        // Return
        return $keys;
    }

    /**
     * Get referenced foreign keys
     */
    private function getReferencedForeignKeys(string $dbtable, string $dirname, bool $with_magic, array $props, bool $auto_confirm = false):array
    {

        // Get foreign keys
        $keys = $this->db->getReferencedForeignKeys($dbtable);

        // Go though keys
        foreach ($keys as $foreign_key => $vars) { 

            // Check for class name
            $class_name = $this->model_classes[$vars['ref_table']] ?? '';
            if ($class_name == '' && !$class_name = $this->generateClass($vars['ref_table'], $dirname, $with_magic, $auto_confirm)) {
                unset($keys[$foreign_key]);
                continue;
            }

            // Add to results
            $keys[$foreign_key]['class_name'] = $class_name;
        }

        // Return
        return $keys;
    }

    /**
     * Get model classes
     */
    private function getModelClasses():array
    {

        // Go through files
        $classes = [];
        $files = $this->io->parseDir(SITE_PATH . '/src');
        foreach ($files as $file) {

            // Check for php extension
            if (!str_ends_with($file, '.php')) {
                continue;
            }

            // Get classs name
            list($namespace, $class) = $this->pathToNamespace(SITE_PATH . '/src/' . $file);
            $class_name = $namespace . "\\" . $class;

            // Load class
            if (!class_exists($class_name)) {
                continue;
            }
            $obj = new \ReflectionClass($class_name);

            // Check for BaseModel interface
        if (!in_array($obj->getExtensionName(), [BaseModel::class, MagicModel::class])) {
                continue;
            } elseif (!$obj->hasProperty('dbtable')) {
                continue;
            }


            // Add to classes
            $table_name = $obj->getProperty('dbtable')->getDefaultValue();
            $classes[$table_name] = $class_name;
        }

        // Return
        return $classes;
    }

    /**
     * Generate class
     */
    private function generateClass(string $table_name, string $entity_dir, bool $with_magic, bool $auto_confirm = false):?string
    {

        // Check skipped and created
        if ($table_name == 'armor_users' || in_array($table_name, ForeignKeysHelper::$tbl_skipped)) { 
            return null;
        } elseif (isset(ForeignKeysHelper::$tbl_created[$table_name])) { 
            return ForeignKeysHelper::$tbl_created[$table_name];
        }

        // Confirm creation
        if ($auto_confirm === false && !$this->cli->getConfirm("A foreign key relationship with the table '$table_name' was found, but no model class was found.  Would you like to generate one?", 'y')) { 
            ForeignKeysHelper::$tbl_skipped[] = $table_name;
            return null;
        }

        // Get name
        $name = preg_replace("/^(.+?)_/", "", $table_name);
        $filename = $this->applyFilter($name, 'single');
        $filename = $this->applyFilter($filename, 'title') . '.php';
        $filename = str_replace(SITE_PATH . '/src/', '', "$entity_dir/$filename");

        // Confirm filename
        if ($auto_confirm === false) {
            $this->cli->send("Please enter the filepath relative to the /src/ directory where you would like the new model class for '$table_name' saved.  Leave blank and press enter to accept the default value provided.\r\n\r\n");
            $filename = $this->cli->getInput("Filepath of Model [$filename]: ", $filename);
        }
        $filename = $this->parseFilename($filename);

        // Add to queue
        ForeignKeysHelper::$queue[$table_name] = $filename;

        // Get class name, and return
        list($namespace, $class_name) = $this->pathToNamespace($filename);
        $class_name = $namespace . "\\" . $class_name;


        // Return
        ForeignKeysHelper::$tbl_created[$table_name] = $class_name;
        $this->model_classes[$table_name] = $class_name;
        return $class_name;
    }

    /**
     * Apply foreign key relationships
     */
    private function applyRelationships(string $code, array $keys, array $referenced_keys, bool $auto_confirm = false):string
    {

        // Check for code tags
        preg_match("/<relations_one>(.*?)<\/relations_one>/si", $code, $one_match);
        preg_match("/<relations_many>(.*?)<\/relations_many>/si", $code, $many_match);

        // Go through keys
        list($one_code, $many_code) = ['', ''];
        foreach ($keys as $alias => $vars) {

            if (str_ends_with($vars['type'], 'many')) {
                $foreign_key = $vars['table'] . '.' . $vars['column'];
                $many_code .= $this->generateCode($many_match[1], $vars['table'], $vars['class_name'], $vars['type'], $foreign_key);
            } elseif (isset($one_match[1])) {
                $one_code .= $this->generateCode($one_match[1], $alias, $vars['class_name'], $vars['type']);
            }
        }

        // Go through referenced keys
        foreach ($referenced_keys as $foreign_key => $vars) { 

            if (isset($many_match[1])) {
                $many_code .= $this->generateCode($many_match[1], $vars['ref_table'], $vars['class_name'], $vars['type'], $foreign_key);
            } else if (isset($one_match[1])) {
                $one_code .= $this->generateCode($one_match[1], $vars['ref_column'], $vars['class_name'], $vars['type']);
            }
        }

        // Replace code
        if (isset($one_match[0])) {
            $code = str_replace($one_match[0], $one_code, $code);
        }

        if (isset($many_match[0])) {
            $code = str_replace($many_match[0], $many_code, $code);
        }

        // Return
        return $code;
    } 

    /**
     * Generate to one code
     */
    private function generateCode(string $tmp_code, string $alias, string $class_name, string $type, string $foreign_key = ''):string
    {

        // Get short class name
        $parts = explode("\\", $class_name);
        $short_name = array_pop($parts);
        $item_alias = preg_replace("/^(.+)\_/", "", rtrim($alias, '_id'));

        // Initialize replace
        $replace = [
            '~get_phrase~' => $this->applyFilter($item_alias, 'phrase'),
            '~short_name~' => $short_name,
            '~name~' => $alias,
            '~foreign_key~' => $foreign_key
        ];
        $name = rtrim(rtrim($alias, 'id'), '_');
        $name = $short_name;

        // Set variables based on type
        if (str_ends_with($type, 'many')) { 
            $name = $this->applyFilter($name, 'plural');
        } else { 
            $name = $this->applyFilter($name, 'single');
        }
        $replace['~method_name~'] = $this->applyFilter('get_' . $name, 'camel');
        $replace['~method_name~'] = preg_replace("/ss$/", 's', $replace['~method_name~']);

        // Add to use declarations
        $this->use_declarations .= "\nuse " . $class_name . ";";
        if (str_ends_with($type, 'many') && !str_contains($this->use_declarations, 'ModelIterator')) { 
            $this->use_declarations .= "\nuse Apex\\App\\Base\\Model\\ModelIterator;";
        }

        // Return
        return strtr($tmp_code, $replace);
    }

    /**
     * Parse filename
     */
    private function parseFilename(string $filename, string $prefix = 'src'):string
    {

        // Format filename
        if (!preg_match("/^$prefix\//", $filename)) { 
            $filename = $prefix . '/' . ltrim($filename, '/');
        }
        if (!preg_match("/\.php$/", $filename)) { 
            $filename .= '.php';
        }

        // Return
        return $filename;
    }


}


