<?php

namespace Database\Interfaces;

interface PDOPreparedStatementInterface
{
    public function execute(array $params = []): PDOResultSetInterface;
}