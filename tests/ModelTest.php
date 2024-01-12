<?php
declare(strict_types=1);

namespace Tests;

use Levis\Svc\{Db, Di};
use Levis\App\TestModel;
use Levis\App\Utils\Tests\LevisTestCase;

/**
 * Test model
 */
class ModelTest extends LevisTestCase
{

    /**
     * Setup
     */
    public function testSetup():void
    {

        // Delete, if needed
        if (file_exists(SITE_PATH . '/db.sqlite')) {
            @unlink(SITE_PATH . '/db.sqlite');
        }

        // Create table
        $db = Di::get(Db::class);
        $db->query("CREATE TABLE test (id INTEGER NOT NULL PRIMARY KEY, is_active BOOLEAN NOT NULL DEFAULT true, balance DECIMAL(12,2) NOT NULL DEFAULT 0, name VARCHAR(100) NOT NULL, email VARCHAR(100) NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP)");

        // Check for table
        $tables = $db->getTableNames();
        $this->assertContains('test', $tables);
    }

    /**
     * Create model
     */
    public function testCreateModel():void
    {
        $res = $this->levis('create model TestModel --dbtable test');
        $this->assertStringContainsString("Successfully created", $res);
        $this->assertFileExists(SITE_PATH . '/src/TestModel.php');

        $code = file_get_contents(SITE_PATH . '/src/TestModel.php');
        $code = str_replace("namespace App", "namespace Levis\\App", $code);
        file_put_contents(SITE_PATH . '/src/TestModel.php', $code);
    }

    /**
     * Test insert
     */
    public function testInsert():void
    {

        $obj = TestModel::insert([
            'balance' => 5.25,
            'name' => 'Matt',
            'email' => 'matt@apexpl.io'
        ]);

        //$this->assertEquals(5.25, $obj->balance);
        $this->assertEquals('Matt', $obj->name);
        //$this->assertEquals('matt@apexpl.io', $obj->email):
    }

    /**
     * Test update
     */
    public function testUpdate():void
    {

        // Get model
        $email = 'matt@apexpl.io';
        $obj = TestModel::whereFirst('email = %s', $email);
        $this->assertNotNull($obj);
        $this->assertEquals(5.25, $obj->balance);
        $this->assertEquals('Matt', $obj->name);

        // Update
        $obj->name = 'Matt Dizak';
        $obj->balance = 11.53;
        $obj->save();

        // Get model
        $obj = TestModel::whereFirst('email = %s', $email);
        $this->assertNotNull($obj);
        $this->assertEquals(11.53, $obj->balance);
        $this->assertEquals('Matt Dizak', $obj->name);
    }

    /**
     * Test delete
     */
    public function testDelete():void
    {

        // Get model
        $email = 'matt@apexpl.io';
        $obj = TestModel::whereFirst('email = %s', $email);
        $this->assertNotNull($obj);

        // Delete
        $obj->delete();

        // Get model
        $obj = TestModel::whereFirst('email = %s', $email);
        $this->assertNull($obj);
    }

    /**
     * Tear down
     */
    public function testTeardown()
    {
        if (file_exists(SITE_PATH . '/db.sqlite')) { @unlink(SITE_PATH . '/db.sqlite'); }
        if (file_exists(SITE_PATH . '/src/TestModel.php')) { @unlink(SITE_PATH . '/src/TestModel.php'); }
        $this->assertTrue(true);
    }

}


