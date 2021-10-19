<?php
/** @noinspection UnknownInspectionInspection */
/** @noinspection SyntaxError */
/** @noinspection SqlResolve */
declare(strict_types=1);
/**
 * User: Fabien Sanchez
 * Date: 20/07/2020
 * Time: 14:33
 */

namespace Test;

use Helper\DbQuickUse;
use Helper\PDOFactory;
use PDO;
use PHPUnit\Framework\TestCase;

class DbQuickUseTest extends TestCase
{
    /** @var \PDO instance de connexion a la DB */
    private $pdo;

    public function testGetLastPk(): void
    {
        $db = new DbQuickUse($this->pdo);
        $this->pdo->exec("DELETE from test WHERE id >= 0");
        $this->pdo->exec("INSERT INTO test (nom) VALUES ('a')");
        $lstKey = $db->getLastPk('test', 'id');
        self::assertIsInt($lstKey);
        $this->pdo->exec("INSERT INTO test (nom) VALUES ('b')");
        $lstKey2 = $db->getLastPk('test', 'id');
        self::assertIsInt($lstKey2);
        self::assertGreaterThan($lstKey, $lstKey2);
    }

    public function testCountElement(): void
    {

        $db = new DbQuickUse($this->pdo);
        $this->pdo->exec("DELETE from test WHERE id >= 0");
        $this->pdo->exec("INSERT INTO test (nom) VALUES ('a')");
        $this->pdo->exec("INSERT INTO test (nom) VALUES ('b')");
        $this->pdo->exec("INSERT INTO test (nom) VALUES ('c')");
        $nb = $db->countElement('test');
        self::assertEquals(3, $nb);
        $this->pdo->exec("INSERT INTO test (nom) VALUES ('d')");
        $nb = $db->countElement('test');
        self::assertEquals(4, $nb);
    }

    public function testUpdate(): void
    {
        $db = new DbQuickUse($this->pdo);
        $this->pdo->exec("DELETE from test WHERE id >= 0");
        $this->pdo->exec("INSERT INTO test (nom) VALUES ('a')");
        $id = $db->getLastPk('test', 'id');
        $nom = $db->selectOne(['nom'], 'test', ['id' => $id])['nom'];
        self::assertEquals('a', $nom);
        $db->update('test', ['nom' => 'z'], ['id' => $id]);
        $nom = $db->selectOne(['nom'], 'test', ['id' => $id])['nom'];
        self::assertEquals('z', $nom);
        $this->pdo->exec("INSERT INTO test (nom) VALUES ('b')");
        $db->update('test', ['nom' => 'x'], ['nom' => 'b']);
        $ents = $db->select(['nom'], 'test');
        self::assertCount(2, $ents);
        $noms = array_map(static function ($n) {
            return $n['nom'];
        }, $ents);
        self::assertContainsEquals('z', $noms);
        self::assertContainsEquals('x', $noms);
    }

    public function testSelectOne(): void
    {
        $this->pdo->exec("DELETE from test WHERE id >= 0");
        $this->pdo->exec("INSERT INTO test (nom) VALUES ('c')");
        $this->pdo->exec("INSERT INTO test (nom) VALUES ('a')");
        $this->pdo->exec("INSERT INTO test (nom) VALUES ('b')");
        $db = new DbQuickUse($this->pdo);
        $res = $db->selectOne(['nom'], 'test');
        self::assertIsArray($res);
        self::assertCount(1, $res);
        self::assertArrayHasKey('nom', $res);
    }

    public function testDelete(): void
    {
        $this->pdo->exec("DELETE from test WHERE id >= 0");
        $this->pdo->exec("INSERT INTO test (nom) VALUES ('c')");
        $this->pdo->exec("INSERT INTO test (nom) VALUES ('a')");
        $this->pdo->exec("INSERT INTO test (nom) VALUES ('b')");
        $db = new DbQuickUse($this->pdo);
        $nb = $db->countElement('test');
        self::assertEquals(3, $nb);
        $db->delete('test', ['nom' => 'c']);
        $nb = $db->countElement('test');
        self::assertEquals(2, $nb);
        $db->delete('test', []);
        $nb = $db->countElement('test');
        self::assertEquals(0, $nb);
    }

    public function testSelect(): void
    {
        $this->pdo->exec("DELETE from test WHERE id >= 0");
        $this->pdo->exec("INSERT INTO test (nom) VALUES ('a')");
        $this->pdo->exec("INSERT INTO test (nom) VALUES (null)");
        $this->pdo->exec("INSERT INTO test (nom) VALUES ('b')");
        $db = new DbQuickUse($this->pdo);
        $res = $db->select(['*'], 'test', ['nom' => null]);

        self::assertIsArray($res);
        self::assertCount(1, $res);
        self::assertIsArray($res[0]);
        self::assertArrayHasKey('id', $res[0]);
    }


    public function testSelectWithWhereIsString(): void
    {
        $this->pdo->exec("DELETE from test WHERE id >= 0");
        $this->pdo->exec("INSERT INTO test (nom) VALUES ('c')");
        $this->pdo->exec("INSERT INTO test (nom) VALUES ('a')");
        $this->pdo->exec("INSERT INTO test (nom) VALUES ('b')");
        $db = new DbQuickUse($this->pdo);
        $res = $db->select(['nom'], 'test', ['nom' => 'a']);
        self::assertCount(1, $res);
    }


    public function testSelectWithWhereIsNull(): void
    {
        $this->pdo->exec("DELETE from test WHERE id >= 0");
        $this->pdo->exec("INSERT INTO test (nom) VALUES ('c')");
        $this->pdo->exec("INSERT INTO test (nom) VALUES ('a')");
        $this->pdo->exec("INSERT INTO test (nom) VALUES ('b')");
        $db = new DbQuickUse($this->pdo);
        $res = $db->select(['nom'], 'test', ['nom' => 'a']);
        self::assertCount(1, $res);
    }

    public function testInsertInto(): void
    {
        $this->pdo->exec("DELETE from test WHERE id >= 0");
        $db = new DbQuickUse($this->pdo);
        $db->insertInto('test', ['nom' => 'a']);
        $nb = $db->countElement('test');
        self::assertEquals(1, $nb);
    }

    protected function setUp(): void
    {
        parent::setUp();
        PDOFactory::$case = PDO::CASE_LOWER;
        $pdo = PDOFactory::sqlite();
        $pdo->exec("CREATE TABLE test (id INTEGER PRIMARY KEY AUTOINCREMENT, nom text)");
        $this->pdo = $pdo;
    }
}
