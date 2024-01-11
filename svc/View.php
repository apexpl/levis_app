<?php
declare(strict_types = 1);

namespace Levis\Svc;

use Levis\Svc\App;
use Apex\Syrus\Render\Templates;

/**
 * Syrus  view
 */
final class View extends \Apex\Syrus\Syrus
{

    #[Inject(App::class)]
    private App $app;

    // Properties
    private string $javascript = '';

    /**
     * Render
     */
    public function render(string $file = ''):string
    {

        // Check auto-routing
        if ($file == '' && $this->template_file == '') { 
            $file = $this->doAutoRouting($this->app->getPath());
        }
        if ($file != '') {
            $this->setTemplateFile($file);
        }

        // Render the template
        $tparser = $this->cntr->make(Templates::class, ['syrus' => $this]);
        $html = $tparser->render();

        // Render again, if no-cache items
        if (preg_match("/<s:(.+?)>/", $html)) { 
            $html = $this->renderBlock($html);
        }

        // Apply Javascript
        if ($this->app->config('enable_javascript') == 1) {
            $html = $this->applyJavascript($html);
        }

        // Return
        return $html;
    }

    /**
     * Add Javascript
     */
    public function addJavascript(string $js):void
    {
        $this->javascript .= $js;
    }

    /**
     * Parse Javascript args
     */
    private function parseJavascriptARgs(string $js_string):array
    {

        // GO through js string
        $vars = [];
        preg_match_all("/\'(.+?)\'/", $js_string, $js_match, PREG_SET_ORDER);
        foreach ($js_match as $match) {
            $vars[] = trim($match[1]);
        }

        // Return
        return $vars;
    }

    /**
     * Apply Javascript
     */
    private function applyJavascript(string $html):string
    {

        // Check if Javascript enabled
        if ($this->app->config('enable_javascript') != 1) {
            return $html;
        }

        // Add any defined Javascript
        if ($this->javascript != '') { 
            $html = str_replace("</body>", "\t<script type=\"text/javascript\">\n" . $this->javascript . "\n\t</script>\n\n</body>", $html);
        }

        // Return
        return $html;
    }

}

