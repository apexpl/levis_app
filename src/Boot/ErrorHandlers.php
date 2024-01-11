<?php
declare(strict_types = 1);

namespace Levis\App\Boot;

use Levis\Svc\{App, Logger, View, Di};

/**
 * Exception / error handlers
 */
class ErrorHandlers
{

    // Properties
    private App $app;

    /**
     * Exception handler
     */
    public function handleException($e):void
    {
        $this->error(0, $e->getMessage(), $e->getFile(), $e->getLine());
    }

    /**
     * Error
     */
    public function error(int $errno, string $message, string $file, int $line):void
    {

        // Initialize
        $file = str_replace(SITE_PATH, '', $file);
        $this->app = Di::get(App::class);
        $logger = Di::get(Logger::class);

        // Get level of log message
        $level = match($errno) {
            2, 32, 512 => 'warning',
            8, 1024 => 'notice',
            64, 128, 256, 4096 => 'error',
            2048, 8192, 16384 => 'info',
            1, 4, 16 => 'critical',
            default => 'error'
        };

        // Add log
        $log_line = '(' . $file . ':' . $line . ') ' . $message;
        $logger?->$level($log_line);

        // Render
        if (php_sapi_name() == "cli") { 
            $this->renderCli($message, $file, $line);
        } elseif (str_starts_with(ltrim($this->app->getPath(), '/'), 'api/')) {
            $this->renderJson($message, $file, $line);
        } else { 
            $this->renderHtml($message, $file, $line);
        }

        // Exit
        exit(1);
    }

    /**
     * Render CLI
     */
    private function renderCli(string $message, string $file, int $line):void
    {

        // Send message.
        fputs(STDOUT, 'ERROR: ' . $message . "\r\n\r\n");
        fputs(STDOUT, "    File: $file\r\n");
        fputs(STDOUT, "    Line: $line\r\n\r\n");

        // Exit
        exit(1);
    }

    /**
     * Render JSON
     */
    private function renderJson(string $message, string $file, int $line):void
    {

        // Set vars
        $vars = [
            'status' => 'error',
            'message' => $message,
            'data' => [
                'file' => $file,
                'line' => $line
            ]
        ];

        // Echo output
        header("Content-type: application/json");
        http_response_code(500);
        echo json_encode($vars);
        exit(1);
    }

    /**
     * Render HTML
     */
    private function renderHtml(string $message, string $file, int $line):void
    {

        // Get template file
        $parts = explode('/', $this->app->getPath());
        $template_file = $this->app->config('debug_mode') == 1 ? '500.html' : '500_generic.html';

        // Get template dir
        if (isset($parts[0]) && file_exists(SITE_PATH . '/views/html/' . $parts[0] . '/' . $template_file)) { 
            $template_file = $parts[0] . '/' . $template_file;
        }

        // Get view, assign variables
        $view = Di::get(View::class);
        $view->assign('error_message', $message);
        $view->assign('error_file', $file);
        $view->assign('error_line', (string) $line);

        // Parse view
        $view->setRpcEnabled(false);
        http_response_code(500);
        echo $view->render($template_file);
        exit(1);
    }

}


