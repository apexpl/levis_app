<?php

use Levis\App\Utils\Tests\LevisTestCase;

/**
 * Http tests
 */
class RestApiTest extends LevisTestCase
{

    /**
     * Setup
     */
    public function testSetup():void
    {
        $site_path = SITE_PATH;
        system("cp -R $site_path/config/skel/HttpControllers $site_path/src/");
        $this->assertTrue(true);
    }

    /**
     * Test home page
     */
    public function testLevis():void
    {

        // Create API endpoing
        $res = $this->levis('create api levtest');
        $this->assertStringContainsString("Successfully created", $res);
        $this->assertFileExists(SITE_PATH . '/src/Api/Levtest.php');

        $code = file_get_contents(SITE_PATH . '/src/Api/Levtest.php');
        $code = str_replace("namespace App", "namespace Levis\\App", $code);
        file_put_contents(SITE_PATH . '/src/Api/Levtest.php', $code);

        // Test API endpoint
        $res = $this->http('/api/levtest');
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertStringContainsString("This is an example response", $res->getBody());
    }

    /**
     * Test 404 page
     */
    public function testPageNotFound():void
    {
        $res = $this->http('/api/some_junk_page');
        $this->assertEquals(404, $res->getStatusCode());
        $this->assertStringContainsString("No endpoint exists", $res->getBody());
    }

    /**
     * tearDown
     */
    public function testTearDown():void
    {
        $site_path = SITE_PATH;
        system("rm -rf $site_path/src/Api");
        system("rm -rf $site_path/src/HttpControllers");
        $this->assertTrue(true);
    }

}


