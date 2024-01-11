<?php
declare(strict_types = 1);

namespace Levis\App\Utils;

use Apex\Syrus\Parser\StackElement;
use Apex\Syrus\Interfaces\LoaderInterface;
use Psr\Http\Message\UriInterface;

/**
 * Syrus adapter class
 */
class SyrusAdapter implements LoaderInterface
{

    /**
     * Get breadcrumbs
     *
     * Returns associative array, keys being the name displayed within the web browser, and values being the href to link to.  
     * If value is blank, element will not contain a hyperlink.
     */
    public function getBreadCrumbs(StackElement $e, UriInterface $uri):array
    {

        // Set array, two links, one text-only element
        $crumbs = [
            'Home' => '/index', 
            'Template Tags' => '/tags', 
            'Breadcrumbs' => ''
        ];

        // Return
        return $crumbs;
    }

    /**
     * Get social links
     */
    public function getSocialLinks(StackElement $e, UriInterface $uri):array
    {
        return [];
    }

    /**
     * Get value of placeholder
     */
    public function getPlaceholder(StackElement $e, UriInterface $uri):string
    {
        return '';
    }

    /**
     * Get theme
     */
    public function getTheme():string
    {
        return '';
    }

    /**
     * Get page var
     */
    public function getPageVar(string $var_name):?string
    {
        return '';
    }

    /**
     * Check nocache pages
     */
    public function checkNoCachePage(string $file):bool
    {
        return false;
    }

    /**
     * Check nocache tags
     */
    public function checkNoCacheTag(string $tag_name):bool
    {
        return false;
    }

}


