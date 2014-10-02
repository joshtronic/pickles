<?php

class DatabaseTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SERVER_NAME']    = '127.0.0.1';

        file_put_contents('/tmp/pickles.php', '<?php
            $config = [
                "environments" => [
                    "local"      => "127.0.0.1",
                    "production" => "123.456.789.0",
                ],
                "pickles" => [
                    "namespace"  => "",
                    "datasource" => "mysql",
                ],
                "datasources" => [
                    "mysql" => [
                        "type"     => "mysql",
                        "driver"   => "pdo_mysql",
                        "database" => "test",
                        "hostname" => "localhost",
                        "username" => "root",
                        "password" => "",
                        "database" => "test",
                    ],
                ],
            ];
        ');

        Pickles\Config::getInstance('/tmp/pickles.php');
    }

    public function testGetInstanceFalse()
    {
        file_put_contents('/tmp/pickles.php', '<?php
            $config = [
                "environments" => [
                    "local"      => "127.0.0.1",
                    "production" => "123.456.789.0",
                ],
                "datasources" => [

                ],
            ];
        ');

        Pickles\Config::getInstance('/tmp/pickles.php');

        $this->assertFalse(Pickles\Database::getInstance());
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage The specified datasource is not defined in the config.
     */
    public function testGetInstanceDatasourceNotDefined()
    {
        file_put_contents('/tmp/pickles.php', '<?php
            $config = [
                "environments" => [
                    "local"      => "127.0.0.1",
                    "production" => "123.456.789.0",
                ],
                "pickles" => [
                    "namespace"  => "",
                    "datasource" => "bad",
                ],
            ];
        ');

        Pickles\Config::getInstance('/tmp/pickles.php');

        Pickles\Database::getInstance();
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage The specified datasource lacks a driver.
     */
    public function testGetInstanceDatasourceLacksDriver()
    {
        file_put_contents('/tmp/pickles.php', '<?php
            $config = [
                "environments" => [
                    "local"      => "127.0.0.1",
                    "production" => "123.456.789.0",
                ],
                "pickles" => [
                    "namespace"  => "",
                    "datasource" => "bad",
                ],
                "datasources" => [
                    "bad" => [
                        "type" => "mysql",
                    ],
                ],
            ];
        ');

        Pickles\Config::getInstance('/tmp/pickles.php');

        $this->assertInstanceOf('Pickles\\Database', Pickles\Database::getInstance());
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage There was an error loading the database configuration.
     */
    public function testOpenConfigError()
    {
        file_put_contents('/tmp/pickles.php', '<?php
            $config = [
                "environments" => [
                    "local"      => "127.0.0.1",
                    "production" => "123.456.789.0",
                ],
                "pickles" => [
                    "namespace"  => "",
                    "datasource" => "bad",
                ],
                "datasources" => [
                    "bad" => [
                        "type"     => "mysql",
                        "driver"   => "pdo_mysql",
                        "database" => "test",
                    ],
                ],
            ];
        ');

        Pickles\Config::getInstance('/tmp/pickles.php');

        $db = Pickles\Database::getInstance();
        $db->open();
    }

    public function testGetInstanceDatasourcesArray()
    {
        file_put_contents('/tmp/pickles.php', '<?php
            $config = [
                "environments" => [
                    "local"      => "127.0.0.1",
                    "production" => "123.456.789.0",
                ],
                "pickles" => [
                    "namespace"  => "",
                    "datasource" => "bad",
                ],
                "datasources" => [
                    "bad" => [
                        "type"     => "mysql",
                        "driver"   => "pdo_mysql",
                        "database" => "test",
                        "hostname" => "localhost",
                        "username" => "root",
                        "password" => "",
                        "database" => "test",
                    ],
                ],
            ];
        ');

        Pickles\Config::getInstance('/tmp/pickles.php');

        $this->assertInstanceOf('Pickles\\Database', Pickles\Database::getInstance());
    }

    // Also tests the datasource being missing and selecting the first one
    public function testGetInstanceMySQL()
    {
        file_put_contents('/tmp/pickles.php', '<?php
            $config = [
                "environments" => [
                    "local"      => "127.0.0.1",
                    "production" => "123.456.789.0",
                ],
                "pickles" => [
                    "namespace"  => "",
                ],
                "datasources" => [
                    "bad" => [
                        "type"     => "mysql",
                        "driver"   => "pdo_mysql",
                        "database" => "test",
                        "hostname" => "localhost",
                        "username" => "root",
                        "password" => "",
                        "database" => "test",
                    ],
                ],
            ];
        ');

        Pickles\Config::getInstance('/tmp/pickles.php');

        $this->assertInstanceOf('Pickles\\Database', Pickles\Database::getInstance());
    }

    public function testOpenMySQL()
    {
        file_put_contents('/tmp/pickles.php', '<?php
            $config = [
                "environments" => [
                    "local"      => "127.0.0.1",
                    "production" => "123.456.789.0",
                ],
                "pickles" => [
                    "namespace"  => "",
                    "datasource" => "mysql",
                ],
                "datasources" => [
                    "mysql" => [
                        "type"     => "mysql",
                        "driver"   => "pdo_mysql",
                        "database" => "test",
                        "hostname" => "localhost",
                        "username" => "root",
                        "password" => "",
                        "database" => "test",
                    ],
                ],
            ];
        ');

        Pickles\Config::getInstance('/tmp/pickles.php');

        $db = Pickles\Database::getInstance();
        $db->open();
    }

    public function testExecute()
    {
        $db = Pickles\Database::getInstance();
        $this->assertEquals('0', $db->execute('SHOW TABLES'));
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage No query to execute.
     */
    public function testExecuteNoQuery()
    {
        $db = Pickles\Database::getInstance();
        $db->execute(' ');
    }

    public function testFetch()
    {
        file_put_contents('/tmp/pickles.php', '<?php
            $config = [
                "environments" => [
                    "local"      => "127.0.0.1",
                    "production" => "123.456.789.0",
                ],
                "pickles" => [
                    "namespace"  => "",
                    "datasource" => "mysql",
                    "profiler" => true,
                ],
                "datasources" => [
                    "mysql" => [
                        "type"     => "mysql",
                        "driver"   => "pdo_mysql",
                        "database" => "test",
                        "hostname" => "localhost",
                        "username" => "root",
                        "password" => "",
                        "database" => "test",
                    ],
                ],
            ];
        ');

        Pickles\Config::getInstance('/tmp/pickles.php');

        $db = Pickles\Database::getInstance();
        $this->assertEquals([], $db->fetch('SELECT * FROM pickles WHERE id != ?', ['0']));
    }

    public function testExplainNoInput()
    {
        $config = Pickles\Config::getInstance();
        $db = Pickles\Database::getInstance();
        $this->assertEquals([], $db->fetch('SELECT * FROM pickles WHERE id != 0'));
    }

    public function testSlowQuery()
    {
        $db = Pickles\Database::getInstance();
        $this->assertEquals('0', $db->execute('SHOW DATABASES', null, true));
    }

    public function testCloseMySQL()
    {
        $db = Pickles\Database::getInstance();
        $db->open();

        $this->assertTrue($db->close());
    }

    public function testGetInstancePostgreSQL()
    {
        file_put_contents('/tmp/pickles.php', '<?php
            $config = [
                "environments" => [
                    "local"      => "127.0.0.1",
                    "production" => "123.456.789.0",
                ],
                "pickles" => [
                    "namespace"  => "",
                    "datasource" => "pgsql",
                ],
                "datasources" => [
                    "pgsql" => [
                        "type"     => "pgsql",
                        "driver"   => "pdo_pgsql",
                        "database" => "test",
                        "hostname" => "localhost",
                        "username" => "root",
                        "password" => "",
                        "database" => "test",
                    ],
                ],
            ];
        ');

        Pickles\Config::getInstance('/tmp/pickles.php');

        $this->assertInstanceOf('Pickles\\Database', Pickles\Database::getInstance());
    }

    /**
     * @expectedException     PDOException
     * @expectedExceptionCode 7
     */
    public function testOpenPostgreSQL()
    {
        file_put_contents('/tmp/pickles.php', '<?php
            $config = [
                "environments" => [
                    "local"      => "127.0.0.1",
                    "production" => "123.456.789.0",
                ],
                "pickles" => [
                    "namespace"  => "",
                    "datasource" => "pgsql",
                ],
                "datasources" => [
                    "pgsql" => [
                        "type"     => "pgsql",
                        "driver"   => "pdo_pgsql",
                        "database" => "test",
                        "hostname" => "localhost",
                        "username" => "root",
                        "password" => "",
                        "database" => "test",
                    ],
                ],
            ];
        ');

        Pickles\Config::getInstance('/tmp/pickles.php');

        // Throws an error because I don't have PostgreSQL installed
        $db = Pickles\Database::getInstance();
        $db->open();
    }

    public function testGetInstanceSQLite()
    {
        file_put_contents('/tmp/pickles.php', '<?php
            $config = [
                "environments" => [
                    "local"      => "127.0.0.1",
                    "production" => "123.456.789.0",
                ],
                "pickles" => [
                    "namespace"  => "",
                    "datasource" => "sqlite",
                ],
                "datasources" => [
                    "sqlite" => [
                        "type"     => "sqlite",
                        "driver"   => "pdo_sqlite",
                        "database" => "test",
                        "hostname" => "localhost",
                        "username" => "root",
                        "password" => "",
                        "database" => "test",
                    ],
                ],
            ];
        ');

        Pickles\Config::getInstance('/tmp/pickles.php');

        $this->assertInstanceOf('Pickles\\Database', Pickles\Database::getInstance());
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Datasource driver "pdo_invalid" is invalid
     */
    public function testGetInstanceInvalidDriver()
    {
        file_put_contents('/tmp/pickles.php', '<?php
            $config = [
                "environments" => [
                    "local"      => "127.0.0.1",
                    "production" => "123.456.789.0",
                ],
                "pickles" => [
                    "namespace"  => "",
                    "datasource" => "invalid",
                ],
                "datasources" => [
                    "invalid" => [
                        "type"     => "invalid",
                        "driver"   => "pdo_invalid",
                        "database" => "test",
                        "hostname" => "localhost",
                        "username" => "root",
                        "password" => "",
                        "database" => "test",
                    ],
                ],
            ];
        ');

        Pickles\Config::getInstance('/tmp/pickles.php');

        Pickles\Database::getInstance();
    }
}

