<?php

namespace Database;

use Database\Interfaces\PDOPreparedStatementInterface;
use Database\Interfaces\PDOResultSetInterface;
use PDOStatement;

class PDOPreparedStatement implements PDOPreparedStatementInterface
{
    private PDOStatement $PDOStatement;

    /**
     * @param PDOStatement $PDOStatement
     */
    public function __construct(PDOStatement $PDOStatement)
    {
        $this->PDOStatement = $PDOStatement;
    }

    public function execute(array $params = []): PDOResultSetInterface
    {
        $this->PDOStatement->execute($params);
        return new PDOResultSet($this->PDOStatement);
    }
}