<?php
declare(strict_types=1);

namespace Tests;

use Levis\App\Utils\Tests\LevisTestCase;

/**
 * CLI Test
 */
class CliTest extends LevisTestCase
{

    /**
     * Create API endpoint
     */
    public function testCreateApi():void
    {
        $res = $this->levis('create api again');
        $this->assertStringContainsString("Successfully created", $res);
        $this->assertFileExists(SITE_PATH . '/src/Api/Again.php');
        @unlink(SITE_PATH . '/src/Api/Again.php');
        rmdir(SITE_PATH . '/src/Api');
    }

    /**
     * Create CLI command
     */
    public function testCreateCli():void
    {
        $res = $this->levis('create cli again');
        $this->assertStringContainsString("Successfully created", $res);
        $this->assertFileExists(SITE_PATH . '/src/Console/Again.php');
        @unlink(SITE_PATH . '/src/Console/Again.php');
        rmdir(SITE_PATH . '/src/Console');
    }

    /**
     * Create HTTP controller
     */
    public function testCreateHttpController():void
    {
        $res = $this->levis('create http-controller again');
        $this->assertStringContainsString("Successfully created", $res);
        $this->assertFileExists(SITE_PATH . '/src/HttpControllers/Again.php');
        @unlink(SITE_PATH . '/src/HttpControllers/Again.php');
        rmdir(SITE_PATH . '/src/HttpControllers');
    }

    /**
     * Create unit test
     */
    public function testCreateTest():void
    {
        $res = $this->levis('create test again');
        $this->assertStringContainsString("Successfully created", $res);
        $this->assertFileExists(SITE_PATH . '/tests/AgainTest.php');
        @unlink(SITE_PATH . '/tests/AgainTest.php');
    }

    /**
     * Create View
     */
    public function testCreateView():void
    {
        $res = $this->levis('create view again');
        $this->assertStringContainsString("Successfully created", $res);
        $this->assertFileExists(SITE_PATH . '/views/php/again.php');
        $this->assertFileExists(SITE_PATH . '/views/html/again.html');

        @unlink(SITE_PATH . '/views/php/again.php');
        @unlink(SITE_PATH . '/views/html/again.html');

        rmdir(SITE_PATH . '/views/html');
        rmdir(SITE_PATH . '/views/php');
        rmdir(SITE_PATH . '/views');
    }


}



