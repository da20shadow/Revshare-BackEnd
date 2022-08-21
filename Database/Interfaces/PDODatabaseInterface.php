<?php

namespace Database\Interfaces;

interface PDODatabaseInterface
{
    public function query(string $query):PDOPreparedStatementInterface;
}