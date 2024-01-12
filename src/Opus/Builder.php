<?php
declare(strict_types = 1);

namespace Levis\App\Opus;

/**
 * Component builder
 */
class Builder extends AbstractBuilder
{

    /**
     * Build
     */
    public function build(string $comp_type, string $filename, array $vars):array
    {

        // GEt dest file
        $dest = match($comp_type) {
            'view' => '/views/php/' . strtolower($filename) . '.php',
            'test' => '/tests/' . $this->applyFilter($filename, 'title') . 'Test.php',
            default => 'src/' . ltrim(rtrim($filename, '.php'), '.php') . '.php'
        };
        $dest = ltrim($dest, '/');

        // Get class name
        list($namespace, $class_name) = $this->pathToNamespace($dest);
        $vars['namespace'] = $namespace;
        $vars['class_name'] = $class_name;

        // Get code
        $skel_file = __DIR__ . '/skel/' . $comp_type . '.txt';
        if (!file_exists($skel_file)) {
            throw new \Exception("Unsupported component type, $comp_type");
        }
        $code = $this->convertString(file_get_contents($skel_file), $vars);

        // Create directory, if needed
        if (!is_dir(dirname(SITE_PATH . "/$dest"))) { 
            mkdir(dirname(SITE_PATH . "/$dest"), 0755, true);
        }

        // Save file
        file_put_contents(SITE_PATH . "/$dest", $code);
        $files = [$dest];

        // Add .html for view
        if ($comp_type == 'view') {
            $html_file = SITE_PATH . '/views/html/' . strtolower($filename) . '.html';
            if (!is_dir(dirname($html_file))) {
                mkdir(dirname($html_file), 0755, true);
            }
            file_put_contents($html_file, "\n<h1>Page Title</h1>\n\n");
            $files[] = '/views/html/' . strtolower($filename) . '.html';
        }

        // Return
        return $files;
    }

}


