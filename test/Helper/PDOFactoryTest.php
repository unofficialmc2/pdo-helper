<?php
declare(strict_types=1);
/**
 * User: Fabien Sanchez
 * Date: 23/06/2020
 * Time: 10:16
 */

namespace Test\Helper;

use Helper\PDOFactory;
use PDO;
use PHPUnit\Framework\TestCase;

class PDOFactoryTest extends TestCase
{

    public function testSqlite(): void
    {
        $pdo = PDOFactory::sqlite();
        $this->assertInstanceOf(PDO::class, $pdo);
        touch('./test.db');
        $pdo = PDOFactory::sqlite('./test.db');
        $this->assertInstanceOf(PDO::class, $pdo);
        $pdo = null;
        unlink('./test.db');
    }

    // TODO : test pour la propriétée $case

    // TODO : test pour la propriétée $fetchMode

    // TODO : test pour PgSql
    //   private function testPgsql(): void {}

    // TODO : test pour Mysql
    //   private function testMysql(): void {}

    // TODO : test pour Oci
    //  private function testOci(): void {}

}
