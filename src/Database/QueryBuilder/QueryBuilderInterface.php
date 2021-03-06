<?php

namespace Phoenix\Database\QueryBuilder;

use Phoenix\Database\Element\MigrationTable;

interface QueryBuilderInterface
{
    /**
     * @param MigrationTable $table
     * @return array list of queries
     */
    public function createTable(MigrationTable $table);

    /**
     * @param MigrationTable $table
     * @return array list of queries
     */
    public function dropTable(MigrationTable $table);

    /**
     * @param MigrationTable $table
     * @return array list of queries
     */
    public function renameTable(MigrationTable $table);

    /**
     * @param MigrationTable $table
     * @return array list of queries
     */
    public function alterTable(MigrationTable $table);

    /**
     * @param MigrationTable $table
     * @return array list of queries
     */
    public function copyTable(MigrationTable $table);

    /**
     * @param string $string
     * @return string escaped string
     */
    public function escapeString($string);
}
