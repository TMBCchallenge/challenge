<?php

class ConnectionFactory
{
    public static function connect()
    {
        $connections = include_once("./config/database.php");

        try {
            // pull the data and store it
            $driver = $connections['driver'];
            $host = $connections['host'];
            $user = $connections['user'];
            $password = $connections['password'];
            $db = $connections['database'];

            // build the string for pdo
            $dsn = $driver. ":host=" . $host . ";dbname=" . $db;

            $connection = new PDO($dsn, $user, $password);

            return $connection;
        } catch (Exception $e) {
            print_r($e->getMessage());
            exit();
        }
    }
}