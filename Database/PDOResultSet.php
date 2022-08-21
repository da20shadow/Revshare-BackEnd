<?php

namespace Database;

use Database\Interfaces\PDOResultSetInterface;
use Generator;
use PDOStatement;

class PDOResultSet implements PDOResultSetInterface
{
    private PDOStatement  $PDOStatement;

    /**
     * @param PDOStatement $PDOStatement
     */
    public function __construct(PDOStatement $PDOStatement)
    {
        $this->PDOStatement = $PDOStatement;
    }

    public function fetch($className): Generator
    {
        while ($row = $this->PDOStatement->fetchObject($className))
        {
            yield $row;
        }
    }
}