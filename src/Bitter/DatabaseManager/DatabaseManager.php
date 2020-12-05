<?php

/**
 * @project:   Database Manager
 *
 * @author     Fabian Bitter (fabian@bitter.de)
 * @copyright  (C) 2020 Fabian Bitter (www.bitter.de)
 * @version    X.X.X
 */

namespace Bitter\DatabaseManager;

use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\Permission\Key\Key;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Statement;

class DatabaseManager implements ApplicationAwareInterface
{

    use ApplicationAwareTrait;

    const COLUMN_TYPE_STRING = 0;
    const COLUMN_TYPE_NUMBER = 1;
    const COLUMN_TYPE_DATE = 2;
    const COLUMN_TYPE_BOOL = 3;

    /** @var $connection */
    protected $connection;

    public function __construct(
        Connection $connection
    )
    {
        $this->connection = $connection;
    }

    /**
     * @return array
     */
    public function getTables()
    {
        $tables = [];

        if (Key::getByHandle("access_database_manager")->validate()) {
            $rows = $this->connection->fetchAll("SHOW TABLES");

            foreach ($rows as $row) {
                $tableName = $row[key($row)];

                $tables[$tableName] = $tableName;
            }
        }

        return $tables;
    }

    /**
     * @return string
     */
    public function getDefaultTable()
    {
        return array_pop(array_reverse($this->getTables()));
    }

    /**
     * @param string $table
     * @return bool
     */
    public function isValidTable($table)
    {
        return in_array($table, $this->getTables());
    }

    /**
     * @param string $table
     * @return array
     */
    public function getTableStructure($table)
    {
        if (Key::getByHandle("access_database_manager")->validate()) {
            /*
             * Need to include the database name too because otherwise all
             * columns from all databases will be fetched if the table
             * exists multiple times in different databases.
             * 
             * Thanks to Nour Akalay (http://kalmoya.com) for this fix/solution.
             */

            /** @noinspection SqlDialectInspection */
            /** @noinspection SqlNoDataSourceInspection */
            return $this->connection->fetchAll("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION ASC", [$this->connection->getDatabase(), $table]);
        } else {
            return [];
        }
    }

    /**
     * @param string $table
     * @return array
     */
    public function getTableColums($table)
    {
        $tableColumns = [];

        if (!$this->isValidTable($table)) {
            return $tableColumns;
        }

        foreach ($this->getTableStructure($table) as $tableStructureEntry) {
            if ($tableStructureEntry["TABLE_SCHEMA"] != "performance_schema" &&
                !in_array($tableStructureEntry["COLUMN_NAME"], $tableColumns)) {
                $tableColumns[] = $tableStructureEntry["COLUMN_NAME"];
            }
        }

        return $tableColumns;
    }

    /**
     * @param string $table
     * @return array
     */
    public function getTableColumsWithDetails($table)
    {
        $tableColumns = [];

        foreach ($this->getTableColums($table) as $column) {
            $tableColumns[] = [
                "name" => $column,
                "isNullable" => $this->isColumnNullable($table, $column),
                "type" => $this->getColumnType($table, $column)
            ];
        }

        return $tableColumns;
    }

    /**
     * @param string $table
     * @return string
     */
    public function getFirstColumn($table)
    {

        if (!$this->isValidTable($table)) {
            return '';
        }

        return array_pop(array_reverse($this->getTableColums($table)));
    }

    /**
     * @param string $table
     * @param string $column
     * @return bool
     */
    public function isValidColumn($table, $column)
    {
        return in_array($column, $this->getTableColums($table));
    }

    /**
     * @param string $table
     * @param string $orderBy
     * @param string $orderDirection
     * @param int $pageIndex
     * @param int $pageSize
     * @return array
     */
    public function getTableData($table, $orderBy = null, $orderDirection = 'ASC', $pageIndex = 1, $pageSize = 50)
    {
        $tableData = [];

        if (Key::getByHandle("access_database_manager")->validate()) {
            if (!$this->isValidTable($table)) {
                return $tableData;
            }

            if (!$this->isValidColumn($table, $orderBy)) {
                $orderBy = $this->getFirstColumn($table);
            }

            if (!in_array(strtoupper($orderDirection), ["ASC", "DESC"])) {
                $orderDirection = "ASC";
            }

            if (!is_numeric($pageIndex)) {
                $pageIndex = 1;
            }

            if (!is_numeric($pageSize)) {
                $pageIndex = 50;
            }

            $position = ($pageIndex - 1) * $pageSize;

            /** @noinspection SqlDialectInspection */
            /** @noinspection SqlNoDataSourceInspection */
            return $this->connection->fetchAll(
                sprintf(
                    "SELECT * FROM `%s` ORDER BY %s %s LIMIT %s, %s",
                    $table,
                    $orderBy,
                    $orderDirection,
                    (int)$position,
                    (int)$pageSize
                )
            );
        } else {
            return $tableData;
        }
    }

    /**
     * @param string $table
     * @return array
     */
    public function getRowIdentifierColumns($table)
    {
        $rowIdentifiers = [];

        if (!$this->isValidTable($table)) {
            return $rowIdentifiers;
        }

        $structure = $this->getTableStructure($table);

        foreach ($structure as $structureEntry) {
            if (
                isset($structureEntry["COLUMN_NAME"]) &&
                isset($structureEntry["COLUMN_KEY"]) &&
                $structureEntry["COLUMN_KEY"] === "PRI"
            ) {
                $rowIdentifiers[] = $structureEntry["COLUMN_NAME"];
            }
        }

        if (count($rowIdentifiers) === 0) {

            /*
             * This table has no primary key
             * 
             * Use all columns instead as key replacement.
             * 
             * This is the most common workaround used by phpMyadmin + adminer.
             */

            foreach ($structure as $structureEntry) {
                if (isset($structureEntry["COLUMN_NAME"])) {
                    $rowIdentifiers[] = $structureEntry["COLUMN_NAME"];
                }
            }
        }

        return $rowIdentifiers;
    }

    /**
     *
     * @param string $table
     * @param string $column
     * @return bool
     */
    public function isColumnNullable($table, $column)
    {

        if (!$this->isValidTable($table)) {
            return false;
        }

        $structure = $this->getTableStructure($table);

        foreach ($structure as $structureEntry) {
            if ($structureEntry["COLUMN_NAME"] == $column) {
                return isset($structureEntry["IS_NULLABLE"]) && strtoupper($structureEntry["IS_NULLABLE"]) === "YES";
            }
        }

        return false;
    }

    /**
     *
     * @param string $table
     * @param string $column
     * @return int|null
     */
    public function getColumnType($table, $column)
    {
        if (!$this->isValidTable($table)) {
            return self::COLUMN_TYPE_STRING;
        }

        $structure = $this->getTableStructure($table);

        foreach ($structure as $structureEntry) {
            if ($structureEntry["COLUMN_NAME"] == $column) {
                if ($structureEntry["COLUMN_TYPE"] == "tinyint(1)") {
                    return self::COLUMN_TYPE_BOOL;
                } else {
                    switch (strtolower($structureEntry["DATA_TYPE"])) {
                        case "char":
                        case "varchar":
                        case "tinytext":
                        case "blob":
                        case "mediumtext":
                        case "mediumblob":
                        case "longtext":
                        case "longblob":
                            return self::COLUMN_TYPE_STRING;

                        case "tinyint":
                        case "smallint":
                        case "mediumint":
                        case "int":
                        case "bigint":
                        case "float":
                        case "double":
                        case "decimal":
                            return self::COLUMN_TYPE_NUMBER;

                        case "date":
                        case "datetime":
                        case "timestamp":
                        case "time":
                        case "set":
                            return self::COLUMN_TYPE_DATE;

                        case "boolean":
                            return self::COLUMN_TYPE_BOOL;
                    }
                }
            }
        }

        return null;
    }

    /**
     *
     * @param string $table
     * @param array $row
     * @param string $prefix
     * @param string $suffix
     * @return array
     */
    public function getRowIdentifiers($table, $row, $prefix = "", $suffix = "")
    {
        $rowIdentifiers = [];

        foreach ($this->getRowIdentifierColumns($table) as $column) {
            if (isset($row[$column])) {
                $rowIdentifiers[$prefix . $column . $suffix] = $row[$column];
            }
        }

        return $rowIdentifiers;
    }

    /**
     * @param string $table
     * @return int
     */
    public function getTableCount($table)
    {
        if (Key::getByHandle("access_database_manager")->validate()) {
            if (!$this->isValidTable($table)) {
                return 0;
            }

            /** @noinspection SqlNoDataSourceInspection */
            /** @noinspection SqlDialectInspection */
            return $this->connection->fetchColumn("SELECT COUNT(*) FROM `" . $table . "`");
        } else {
            return 0;
        }
    }

    /**
     *
     * @param string $table
     * @param array $rowIdentifiers
     * @param string $delimiter
     * @param string $pattern
     * @return string
     */
    private function buildCondition($table, $rowIdentifiers, $delimiter = " AND ", $pattern = "`%column%` = %value%")
    {
        $condition = '';

        foreach ($rowIdentifiers as $column => $value) {
            if ($this->isValidColumn($table, $column)) {
                if ($value === null) {
                    $value = "NULL";
                } else if ($this->getColumnType($table, $column) === self::COLUMN_TYPE_DATE) {
                    if (strlen($value) == 0) {
                        $value = "NULL";
                    } else {
                        $value = $this->connection->quote($value); // we need to quote
                    }
                } elseif ($this->getColumnType($table, $column) === self::COLUMN_TYPE_STRING) {
                    $value = $this->connection->quote($value); // we need to quote
                } else if ($this->getColumnType($table, $column) === self::COLUMN_TYPE_BOOL) {
                    $value = $value === true ? "TRUE" : "FALSE";
                } else if (strlen($value) == 0) {
                    $value = "''";
                }

                $token = $pattern;

                $token = str_replace("%column%", $column, $token);
                $token = str_replace("%value%", $value, $token);

                if (strlen($condition) > 0) {
                    $token = $delimiter . $token;
                }

                $condition .= $token;
            }
        }

        return $condition;
    }

    /**
     *
     * @param string $table
     * @param array $rowIdentifiers
     * @return array
     */
    public function getTableRow($table, $rowIdentifiers)
    {
        if (Key::getByHandle("access_database_manager")->validate()) {
            if (!$this->isValidTable($table)) {
                return [];
            }

            /** @noinspection SqlNoDataSourceInspection */
            return $this->connection->fetchAssoc(
                sprintf(
                    "SELECT * FROM `%s` WHERE %s LIMIT 1",
                    $table,
                    $this->buildCondition($table, $rowIdentifiers)
                )
            );
        } else {
            return [];
        }
    }

    /**
     *
     * @param string $table
     * @param array $rowIdentifiers
     * @param array $fields
     * @return array|bool|Statement
     * @throws DBALException
     */
    public function updateRow($table, $rowIdentifiers, $fields)
    {
        if (Key::getByHandle("edit_rows")->validate()) {
            if (!$this->isValidTable($table)) {
                return [];
            }

            /** @noinspection SqlNoDataSourceInspection */
            return $this->connection->executeQuery(
                sprintf(
                    "UPDATE `%s` SET %s WHERE %s LIMIT 1",
                    $table,
                    $this->buildCondition($table, $fields, ", "),
                    $this->buildCondition($table, $rowIdentifiers)
                )
            );
        } else {
            return false;
        }
    }

    /**
     *
     * @param string $table
     * @param array $fields
     * @return bool|Statement
     * @throws DBALException
     */
    public function insertRow($table, $fields)
    {
        if (Key::getByHandle("insert_rows")->validate()) {
            if (!$this->isValidTable($table)) {
                return false;
            }

            /** @noinspection SqlNoDataSourceInspection */
            return $this->connection->executeQuery(
                sprintf(
                    "INSERT INTO `%s` (%s) VALUES(%s)",
                    $table,
                    $this->buildCondition($table, $fields, ", ", "`%column%`"),
                    $this->buildCondition($table, $fields, ", ", "%value%")
                )
            );
        } else {
            return false;
        }
    }

    /**
     *
     * @param string $table
     * @param array $rowIdentifiers
     * @return bool|Statement
     * @throws DBALException
     */
    public function deleteRow($table, $rowIdentifiers)
    {

        if (Key::getByHandle("delete_rows")->validate()) {
            if (!$this->isValidTable($table)) {
                return false;
            }

            $this->connection->executeQuery("SET FOREIGN_KEY_CHECKS = 0;");

            /** @noinspection SqlNoDataSourceInspection */
            $retVal = $this->connection->executeQuery(
                sprintf(
                    "DELETE FROM `%s` WHERE %s LIMIT 1",
                    $table,
                    $this->buildCondition($table, $rowIdentifiers)
                )
            );

            $this->connection->executeQuery("SET FOREIGN_KEY_CHECKS = 1;");

            return $retVal;
        } else {
            return false;
        }
    }

}
