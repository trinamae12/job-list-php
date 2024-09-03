<?php
namespace Framework;

use PDO;

class Database 
{
    public $conn;

    /**
     * Constructor for database class
     * 
     * @param array $config
     */
    public function __construct($config) 
    {
        $host = 'db';
        $port = 3306;
        $dbName = 'myDb';
        $username = 'user';
        $password = 'test';
    
        $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8;";
        //$dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        ];

        try {
            // $this->conn = new PDO($dsn, $config['username'], $config['password'], $options);
            $this->conn = new PDO($dsn, $username, $password, $options);
        } catch(PDOException $e) {
            throw new Exception("Database connection failed: {$e->getMessage()}");
        }
    }

    /**
     * Query the database
     * 
     * @param string $query
     * @return PDOStatement
     * @throws PDOException
     */
    public function query($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            
            // Bind named params
            foreach($params as $param => $value) {
                $stmt->bindValue(':' . $param, $value);
            }

            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Query failed to execute: {$e->getMessage()}");
        }
    }
}