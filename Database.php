
<?php

namespace App\Services\Comment; 

use \PDO;

// Singleton to connect commentdb
class Database {
    
    private static $instance = null;
    private $conn;
    
    private $host = 'testserver';
    private $user = 'testuser';
    private $pass = 'testpassword';
    private $name = 'commentdb';
    
    private function __construct()
    {
        try {
            $this->conn = new PDO("mysql:host={$this->host};
              dbname={$this->name}", $this->user,$this->pass,
              array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }
    }
    
    public static function getInstance()
    {
        if(!self::$instance)
        {
          self::$instance = new Database();
        }
      
        return self::$instance;
    }
    
    public function getConnection()
    {
        return $this->conn;
    }
}