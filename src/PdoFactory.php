<?php

namespace Helper;

use PDO;

/**
 * PDOFactory est une collection de methode statique facilitant la création de connecteur PDO
 * @author Fabien Sanchez
 */
class PDOFactory
{

    // case du nom des champs (\PDO::CASE_UPPER | \PDO::CASE_LOWER)
    public static $case = PDO::CASE_UPPER;
    // mode de sortie des champs (\PDO::FETCH_OBJ | \PDO::FETCH_ASSOC)
    public static $fetchMode = PDO::FETCH_OBJ;

    /**
     * Initialise par defaut les attribus du connecteur
     *
     * @param PDO $pdo
     * @return PDO
     */
    static private function configPdo(PDO $pdo): PDO
    {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, self::$fetchMode);
        $pdo->setAttribute(PDO::ATTR_CASE, self::$case);
        return $pdo;
    }

    /**
     * Connecteur Mysql
     *
     * @param string $host     localhost / adresse IP
     * @param string $dbName   Nom de la base de donnée
     * @param string $username User de la base mysql
     * @param string $password Mot de passe
     * @param integer $port    Numero de port (defaut : 3306)
     * @param string $charset  Charset utilisé (defaut : utf8)
     * @return PDO
     */
    static public function mysql($host, $dbName, $username, $password, $port = 3306, $charset = 'utf8'): PDO
    {
        $dns = "mysql:host=$host;port=$port;dbname=$dbName;charset=$charset";
        // $options = array(
        //     PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES $charset",
        // ); 
        $pdo = new PDO($dns, $username, $password);
        return self::configPdo($pdo);
    }

    /**
     * Connecteur sqlite
     *
     * @param string $filename Chemin/de/la/base/de/donnee
     * @return PDO
     */
    static public function sqlite(string $filename = ':memory:'): PDO
    {
        if ($filename !== ':memory:') {
            if (!file_exists($filename)) {
                throw new \InvalidArgumentException("Le fichier $filename n'a pas été trouvé! ");
            }
            $filename = realpath($filename);
        }
        $pdo = new PDO('sqlite:' . $filename);
        return self::configPdo($pdo);
    }

    /**
     * Connecteur Oracle
     *
     * @param string $sid      SID enregistré dans le TNSNAME
     * @param string $user     User oracle
     * @param string $password Mot de passe du user oracle
     * @return PDO
     */
    static public function oci(string $sid, string $user, string $password, string $charset = ''): PDO
    {
        if(!empty($charset)){
            $charset = ';charset=' . $charset;
        }
        $pdo = new PDO("oci:dbname=$sid$charset", $user, $password);
        $pdo = self::configPdo($pdo);
        try {
            $pdo->exec("ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD'");
            $pdo->exec("ALTER SESSION SET NLS_TIMESTAMP_FORMAT = 'YYYY-MM-DD HH24:MI:SS'");
        } catch (\PDOException $ex) {
            throw new \RuntimeException("Impossible de modifier les formats de date de la base", 0, $ex);
        }
        return $pdo;
    }

    /**
     * Connecteur Postgresql
     *
     * @param string $dbname   Nom de la base de donnée
     * @param string $host     Adresse du serveur
     * @param string $user     User postgres
     * @param string $password Mot de passe du user postgres
     * @return PDO
     */
    static public function pgsql(string $dbname, string $host, string $user, string $password): PDO
    {
        $pdo = new PDO("pgsql:dbname=$dbname;host=$host", $user, $password);
        return self::configPdo($pdo);
    }
}
