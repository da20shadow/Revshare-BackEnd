<?php

namespace Database\Interfaces;

use Generator;

interface PDOResultSetInterface
{
    public function fetch($className): Generator;
}