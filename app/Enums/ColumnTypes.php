<?php

namespace App\Enums;

enum ColumnTypes: string
{
    case VARCHAR = 'VARCHAR';
    case CHAR = 'CHAR';
    case TEXT = 'TEXT';
    case INT = 'INT';
    case BIGINT = 'BIGINT';
    case SMALLINT = 'SMALLINT';
    case MEDIUMINT = 'MEDIUMINT';
    case TINYINT = 'TINYINT';
    case DECIMAL = 'DECIMAL';
    case FLOAT = 'FLOAT';
    case DOUBLE = 'DOUBLE';
    case BOOLEAN = 'BOOLEAN';
    case DATE = 'DATE';
    case DATETIME = 'DATETIME';
    case TIMESTAMP = 'TIMESTAMP';
    case TIME = 'TIME';
    case YEAR = 'YEAR';
    case JSON = 'JSON';
}
