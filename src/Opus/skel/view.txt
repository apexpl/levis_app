<?php
declare(strict_types = 1);

namespace Views~parent_namespace~;

use Apex\Svc\{View, App};

/**
 * Render the template.  All methods below are optional, may be 
 * removed as desired, and all methods support method based dependency injection.
 */
class ~alias.lower~
{

    /**
     * Render - This method will be called everytime the view is rendered, regardless of HTTP method.
     * You may also use post(), get(), delete(), et al. methods which only execute for that specific HTTP verb.
     * All methods support dependency injection, so you can put any desired dependencies within the parameter list.
     */
    public function render(View $view, App $app):void
    {

    }

}

