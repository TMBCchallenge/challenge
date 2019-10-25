<?php

require_once("ConnectionFactory.php");

abstract class Base
{
    /**
     * @var db connection
     */
    private $connection;

    public $sql;

    public $params;

    public function __construct()
    {
        $this->sql = null;
        $this->params = array();
    }

    /**
     * checks if the connection is already ready
     * @return bool
     */
    public function isConnected()
    {
        return is_null($this->connection) ? false : true;
    }

    public function connect()
    {
        $this->connection = ConnectionFactory::connect();
    }

    protected function resetVariables()
    {
        $this->sql = null;
        $this->params = array();
    }

    public function getLastInsertedId()
    {
        return $this->connection->lastInsertId();
    }

    public function prepare()
    {
        // first check we have a connection
        if (!$this->isConnected()) {
            $this->connect();
        }

        // next try to run the query if we have all we need
        if ($this->shouldRun()) {
            try {
                // create stmt and prepare it and return back array
                $stmt = $this->connection->prepare($this->sql);
                $stmt->execute($this->params);
                // reset all variables
                $this->resetVariables();

                return [$stmt, $stmt->rowCount()];
            } catch (Exception $e) {
                print_r($e->getMessage());
                exit();
            }
        } else {
            echo 'We are missing either an SQL query or params to pass into PDO';
            exit();
        }
    }

    public function prepareCount()
    {
        // first check we have a connection
        if (!$this->isConnected()) {
            $this->connect();
        }

        // next try to run the query if we have all we need
        if ($this->shouldRun()) {
            try {
                // create stmt and prepare it and return back array
                $stmt = $this->connection->prepare($this->sql);
                $stmt->execute($this->params);
                // reset all variables
                $this->resetVariables();

                return $stmt->fetchColumn();
            } catch (Exception $e) {
                print_r($e->getMessage());
                exit();
            }
        } else {
            echo 'We are missing either an SQL query or params to pass into PDO';
            exit();
        }
    }

    protected function shouldRun()
    {
        return (is_null($this->sql) || empty($this->params)) ? false : true;
    }
}