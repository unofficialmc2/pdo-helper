<?php
declare(strict_types=1);
/**
 * User: Fabien Sanchez
 * Date: 17/07/2020
 * Time: 10:56
 */

namespace Helper;

use PDO;
use PDOStatement;
use RuntimeException;
use UnderflowException;

/**
 * Class DbQuickUse
 * @package Test
 */
class DbQuickUse
{
    /** @var \PDO instance de connexion à la DB */
    protected $pdo;

    /**
     * DbQuickUse constructor.
     * @param \PDO $pdo instance de connexion à la DB
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * compte le nombre d'element dans une table
     * @param string $table
     * @param mixed[] $where
     * @return int
     */
    public function countElement(string $table, array $where = []): int
    {
        $whereStr = $this->genWhere($where);
        $wherePar = $this->getWhereParam($where);
        $sql = "SELECT count(*) FROM $table WHERE $whereStr";
        $req = $this->prepareAndExecute($sql, [], $wherePar);
        $nb = $req->fetchColumn();
        return (int)$nb;
    }

    /**
     * genere une clause where
     * @param mixed[] $where
     * @return string
     */
    protected function genWhere(array $where): string
    {
        $outWhere = [];
        foreach ($where as $k => $v) {
            if (is_int($k)) {
                $outWhere[] = $v;
            } elseif (is_null($v)) {
                $outWhere[] = $k . ' is null';
            } else {
                $outWhere[] = $k . ' = ?';
            }
        }
        return !empty($outWhere) ? implode(' AND ', $outWhere) : '1 = 1';
    }

    /**
     * retourne les parametr des clauses where
     * @param mixed[] $where
     * @return array<mixed>
     */
    protected function getWhereParam(array $where): array
    {
        $outWhereParams = [];
        foreach ($where as $k => $v) {
            if (!is_int($k) && !is_null($v)) {
                $outWhereParams[] = $v;
            }
        }
        return $outWhereParams;
    }

    /**
     * @param string $sql
     * @param mixed[] $param
     * @param mixed[] $whereParam
     * @return \PDOStatement
     */
    protected function prepareAndExecute(string $sql, array $param, array $whereParam): PDOStatement
    {
        $req = $this->pdo->prepare($sql);
        $params = [];
        foreach ($param as $p) {
            $params[] = $p;
        }
        foreach ($whereParam as $p) {
            $params[] = $p;
        }
        if (empty($params)) {
            $req->execute();
        } else {
            $req->execute($params);
        }
        return $req;
    }

    /**
     * insertion de donnée dans une table
     * @param string $table
     * @param array<string, mixed> $data
     */
    public function insertInto(string $table, array $data): void
    {
        $pdo = $this->pdo;
        $field = [];
        $insert = [];
        $value = [];
        foreach ($data as $k => $v) {
            $field[] = $k;
            $insert[] = '?';
            $value[] = $v;
        }
        $fields = implode(', ', $field);
        $insert = implode(', ', $insert);
        $sql = "INSERT INTO $table ($fields) VALUES ($insert)";
        $req = $pdo->prepare($sql);
        $req->execute($value);
    }

    /**
     * supprime les données d'une table
     * @param string $table
     * @param mixed[] $where
     * @return void
     */
    public function delete(string $table, array $where): void
    {
        $whereStr = $this->genWhere($where);
        $whereParam = $this->getWhereParam($where);
        $sql = "DELETE FROM $table WHERE $whereStr";
        $this->prepareAndExecute($sql, [], $whereParam);
    }

    /**
     * mise a jour de donnée dans une table
     * @param string $table
     * @param array<string, mixed> $data
     * @param mixed[] $where
     */
    public function update(string $table, array $data, array $where = []): void
    {
        $pdo = $this->pdo;
        $set = [];
        $value = [];
        foreach ($data as $k => $v) {
            $set[] = $k . ' = ?';
            $value[] = $v;
        }
        $sets = implode(', ', $set);
        $whereStr = $this->genWhere($where);
        $whereParam = $this->getWhereParam($where);
        $sql = "UPDATE $table SET $sets WHERE $whereStr";
        $req = $this->prepareAndExecute($sql, $value, $whereParam);
    }

    /**
     * lit 1 enregistrement
     * @param mixed[] $fields
     * @param string $table
     * @param mixed[] $where
     * @return null|array<string, mixed>
     */
    public function selectOne(array $fields, string $table, array $where = []): ?array
    {
        $whereStr = $this->genWhere($where);
        $whereParam = $this->getWhereParam($where);
        $selectStr = $this->genSelect($fields);
        $sql = "SELECT $selectStr FROM $table WHERE $whereStr";
        $req = $this->prepareAndExecute($sql, [], $whereParam);
        $sel = $req->fetch(PDO::FETCH_ASSOC);
        return $sel === false ? null : $sel;
    }

    /**
     * genere un select
     * @param mixed[] $select au format ['field', 'alias'=>'field']
     * @return string
     */
    protected function genSelect(array $select): string
    {
        $outSelect = [];
        foreach ($select as $k => $v) {
            if (is_int($k)) {
                $outSelect[] = $v;
            } else {
                $outSelect[] = $v . ' as ' . $k;
            }
        }
        return implode(', ', $outSelect);
    }

    /**
     * lit plusieurs enregistrements
     * @param mixed[] $fields
     * @param string $table
     * @param mixed[] $where
     * @param int $limit
     * @return array<string, mixed>[]
     */
    public function select(array $fields, string $table, array $where = [], int $limit = -1): array
    {
        $whereStr = $this->genWhere($where);
        $whereParam = $this->getWhereParam($where);
        $selectStr = $this->genSelect($fields);
        $sql = "SELECT $selectStr FROM $table WHERE $whereStr";
        $req = $this->prepareAndExecute($sql, [], $whereParam);
        $data = [];
        while (($entity = $req->fetch(PDO::FETCH_ASSOC)) !== false && $limit !== 0) {
            $data[] = $entity;
            $limit--;
        }
        return $data;
    }

    /**
     * retourne le dernier ID généré
     * @param string $table
     * @param string $pk
     * @return int
     */
    public function getLastPk(string $table, string $pk): int
    {
        $sql = "SELECT max($pk) FROM $table";
        $req = $this->pdo->query($sql);
        if ($req === false) {
            throw new RuntimeException('counting request could not be completed');
        }
        $lastPk = (int)$req->fetchColumn();
        if ($lastPk === 0) {
            throw new UnderflowException('there is no primary key');
        }
        return $lastPk;
    }
}
