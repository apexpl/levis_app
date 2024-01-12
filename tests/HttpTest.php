<?php

use Levis\App\Utils\Tests\LevisTestCase;

/**
 * Http tests
 */
class HttpTest extends LevisTestCase
{

    /**
     * Setup
     */
    public function testSetup():void
    {
        $site_path = SITE_PATH;
        system("cp -R $site_path/config/skel/views $site_path/");
        system("cp -R $site_path/config/skel/HttpControllers $site_path/src/");
        $this->assertTrue(true);
    }

    /**
     * Test home page
     */
    public function testIndex():void
    {
        $res = $this->http('/index');
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertStringContainsString("Welcome to Levis", $res->getBody());
    }

    /**
     * Test 404 page
     */
    public function testPageNotFound():void
    {
        $res = $this->http('/some_junk_page');
        $this->assertEquals(404, $res->getStatusCode());
        $this->assertStringContainsString("Page Not Found", $res->getBody());
    }

    /**
     * Test dynamic path param
     */
    public function testDynamicPathParam():void
    {
        $res = $this->http('/order/a5gr881');
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertStringContainsString("Order: a5gr881", $res->getBody());
    }

    /**
     * Test hard end
     */
    public function testHardEnd():void
    {
        $res = $this->http('/hardend');
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertStringContainsString("At Hard End", $res->getBody());

        $res = $this->http('/hardend/test');
        $this->assertEquals(404, $res->getStatusCode());
        $this->assertStringContainsString("Page Not Found", $res->getBody());
    }

    /**
     * tearDown
     */
    public function testTearDown():void
    {
        $site_path = SITE_PATH;
        system("rm -rf $site_path/views");
        system("rm -rf $site_path/src/HttpControllers");
        $this->assertTrue(true);
    }

}


