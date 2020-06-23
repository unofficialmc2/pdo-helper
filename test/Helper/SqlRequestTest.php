<?php
/** @noinspection SqlResolve */
declare(strict_types=1);

/**
 * User: Fabien Sanchez
 * Date: 18/06/2020
 * Time: 23:28
 */

namespace Test\Helper;

use Helper\SqlRequest;
use PHPUnit\Framework\TestCase;

/**
 * Test de SqlRequest
 */
class SqlRequestTest extends TestCase
{

    /**
     * test des select
     */
    public function testSelect(): void
    {
        $req = new SqlRequest();
        $req->from('test');
        $req->select(["f1"]);
        $this->assertEquals('select f1 from test', $req->sql());
    }

    /**
     * test des addSelect
     */

    public function testAddSelect(): void
    {
        $req = new SqlRequest();
        $req->from('test');
        $req->select(["f1"]);
        $req->addSelect('f2');
        $this->assertEquals('select f1, f2 from test', $req->sql());
    }

    /**
     * test des addSelect
     */
    public function testAddSelectWithAlias(): void
    {
        $req = new SqlRequest();
        $req->from('test');
        $req->select(["f1"]);
        $req->addSelect('f2', 'w');
        $this->assertEquals('select f1, f2 as w from test', $req->sql());
    }

    /**
     * test des select avec plusieur champ
     */
    public function testSelectWithMultiSelect(): void
    {
        $req = new SqlRequest();
        $req->from('test');
        $req->select(["f1", "f2"]);
        $this->assertEquals('select f1, f2 from test', $req->sql());
    }

    /**
     * test des select avec un alias
     */
    public function testSelectWithAlias(): void
    {
        $req = new SqlRequest();
        $req->from('test');
        $req->select(["f" => "f3"]);
        $this->assertEquals('select f3 as f from test', $req->sql());
    }

    /**
     * test des select avec un alias et sans, control de l'ordre
     */
    public function testSelectWithAliasAndOrder(): void
    {
        $req = new SqlRequest();
        $req->from('test');
        $req->select(["f" => "f3", "f4"]);
        $this->assertEquals('select f3 as f, f4 from test', $req->sql());
        $req2 = new SqlRequest();
        $req2->from('test');
        $req2->select(["f4", "f" => "f5"]);
        $this->assertEquals('select f4, f5 as f from test', $req2->sql());
    }

    /**
     * test de from
     */
    public function testFrom(): void
    {
        $req = new SqlRequest();
        $req->select(["f1"])->from(['xxx']);
        $this->assertEquals('select f1 from xxx', $req->sql());
    }

    /**
     * test de from
     */
    public function testFromMultiTable(): void
    {
        $req = new SqlRequest();
        $req->select(["f1"])->from(['xxx', ', yyy']);
        $this->assertEquals('select f1 from xxx , yyy', $req->sql());
    }

    /**
     * test de from
     */
    public function testFromWithInnerJoin(): void
    {
        $req = new SqlRequest();
        $req->select(["f1", "f2"])->from(['xxx', 'inner join yyy on (xxx.id = yyy.id)']);
        $this->assertEquals('select f1, f2 from xxx inner join yyy on (xxx.id = yyy.id)', $req->sql());
    }

    /**
     * test de where
     */
    public function testWhere(): void
    {
        $req = new SqlRequest();
        $req->select(["f1"])->from(['xxx'])->where('a = 1');
        $this->assertEquals('select f1 from xxx where (a = 1)', $req->sql());
    }

    /**
     * test de where
     */
    public function testMultiWhere(): void
    {
        $req = new SqlRequest();
        $req->select(["f1"])->from(['xxx'])->where(['a = 1', 'id > 0']);
        $this->assertEquals('select f1 from xxx where a = 1 and id > 0', $req->sql());
    }

    /**
     * test de where
     */
    public function testAddWhere(): void
    {
        $req = new SqlRequest();
        $req->select(["f1"])->from(['xxx'])->where(['a = 1', 'id > 0']);
        $this->assertEquals('select f1 from xxx where a = 1 and id > 0', $req->sql());
        $req->addWhere('id < 2');
        $this->assertEquals('select f1 from xxx where a = 1 and id > 0 and id < 2', $req->sql());
    }
}
