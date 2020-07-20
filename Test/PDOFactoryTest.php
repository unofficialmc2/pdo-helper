<?php
/** @noinspection SqlResolve */
/** @noinspection SqlNoDataSourceInspection */
declare(strict_types=1);
/**
 * User: Fabien Sanchez
 * Date: 23/06/2020
 * Time: 10:16
 */

namespace Test;

use Helper\PDOFactory;
use PDO;
use PHPUnit\Framework\TestCase;

class PDOFactoryTest extends TestCase
{

    public function testSqlite(): void
    {
        $pdo = PDOFactory::sqlite();
        $this->assertInstanceOf(PDO::class, $pdo);
        $pdo = null;
        touch('./test.db');
        $pdo = PDOFactory::sqlite('./test.db');
        $this->assertInstanceOf(PDO::class, $pdo);
        $pdo = null;
        unlink('./test.db');
    }


    public function testPropertyCase(): void
    {
        PDOFactory::$case = PDO::CASE_UPPER;
        $pdo = PDOFactory::sqlite();
        $pdo->exec("CREATE TABLE test (nom text)");
        $pdo->exec("INSERT INTO test (nom) VALUES ('a')");
        $entity = $pdo->query("SELECT nom FROM test")->fetch(PDO::FETCH_ASSOC);
        self::assertNotFalse($entity);
        $field = array_keys($entity)[0];
        self::assertEquals('NOM', $field);
        $pdo = null;

        PDOFactory::$case = PDO::CASE_LOWER;
        $pdo = PDOFactory::sqlite();
        $pdo->exec("CREATE TABLE test (nom text)");
        $pdo->exec("INSERT INTO test (nom) VALUES ('a')");
        $entity = $pdo->query("SELECT nom FROM test")->fetch(PDO::FETCH_ASSOC);
        self::assertNotFalse($entity);
        $field = array_keys($entity)[0];
        self::assertEquals('nom', $field);
        $pdo = null;
    }

    public function testPropertyFetchMode(): void
    {
        PDOFactory::$fetchMode = PDO::FETCH_ASSOC;
        $pdo = PDOFactory::sqlite();
        $pdo->exec("CREATE TABLE test (nom text)");
        $pdo->exec("INSERT INTO test (nom) VALUES ('a')");
        $entity = $pdo->query("SELECT nom FROM test")->fetch();
        self::assertNotFalse($entity);
        self::assertIsArray($entity);
        $pdo = null;

        PDOFactory::$fetchMode = PDO::FETCH_OBJ;
        $pdo = PDOFactory::sqlite();
        $pdo->exec("CREATE TABLE test (nom text)");
        $pdo->exec("INSERT INTO test (nom) VALUES ('a')");
        $entity = $pdo->query("SELECT nom FROM test")->fetch();
        self::assertNotFalse($entity);
        self::assertIsObject($entity);
        $pdo = null;
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
