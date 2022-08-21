<?php

namespace Database;

use Database\Interfaces\PDODatabaseInterface;
use Database\Interfaces\PDOPreparedStatementInterface;
use PDO;

class PDODatabase implements PDODatabaseInterface
{
    private PDO $PDO;

    /**
     * @param PDO $db
     */
    public function __construct(PDO $db)
    {
        $this->PDO = $db;
    }

    public function query(string $query): PDOPreparedStatementInterface
    {
        $stmt = $this->PDO->prepare($query);
        return new PDOPreparedStatement($stmt);
    }
}