<?php

namespace Phoenix\Database\Element;

use Phoenix\Behavior\ParamsCheckerBehavior;

class MigrationTable
{
    use ParamsCheckerBehavior;

    const ACTION_CREATE = 'create';

    const ACTION_ALTER = 'alter';

    const ACTION_RENAME = 'rename';

    const ACTION_DROP = 'drop';

    const ACTION_COPY = 'copy';

    const COPY_ONLY_STRUCTURE = 'only_structure';

    const COPY_ONLY_DATA = 'only_data';

    const COPY_STRUCTURE_AND_DATA = 'structure_and_data';

    private $action = self::ACTION_ALTER;

    private $tmpPrimaryKey;

    private $name;

    private $newName;

    private $charset;

    private $collation;

    private $comment;

    private $columns = [];

    private $primaryColumns = [];

    private $foreignKeys = [];

    private $indexes = [];

    private $columnsToDrop = [];

    private $foreignKeysToDrop = [];

    private $indexesToDrop = [];

    private $columnsToChange = [];

    private $dropPrimaryKey = false;

    private $copyType;

    /**
     * @param string $name
     * @param mixed $primaryKey
     */
    public function __construct($name, $primaryKey = true)
    {
        $this->name = $name;
        $this->tmpPrimaryKey = $primaryKey;
    }

    /**
     * @param string $name
     * @param string $type
     * @param array $settings
     * @return MigrationTable
     */
    public function addColumn($name, $type, array $settings = [])
    {
        $column = new Column($name, $type, $settings);
        $this->columns[$column->getName()] = $column;
        return $this;
    }

    /**
     * @param string $oldName
     * @param string $newName
     * @param string $type
     * @param array $settings
     * @return MigrationTable
     */
    public function changeColumn($oldName, $newName, $type, array $settings = [])
    {
        $newColumn = new Column($newName, $type, $settings);
        if (isset($this->columns[$oldName])) {
            $this->columns[$oldName] = $newColumn;
            return $this;
        }

        $this->columnsToChange[$oldName] = $newColumn;
        return $this;
    }

    /**
     * add primary key(s) to table
     * @param mixed $primaryColumn
     * true - if you want classic autoincrement integer primary column with name id
     * Column - if you want to define your own column (column is added to list of columns)
     * string - name of column in list of columns
     * array of strings - names of columns in list of columns
     * array of Column - list of own columns (all columns are added to list of columns)
     * other (false, null) - if your table doesn't have primary key
     */
    public function addPrimary($primaryColumn)
    {
        if ($primaryColumn === true) {
            $primaryColumn = new Column('id', Column::TYPE_INTEGER, [ColumnSettings::SETTING_AUTOINCREMENT => true]);
            return $this->addPrimary($primaryColumn);
        }

        if ($primaryColumn instanceof Column) {
            $this->columns = array_merge([$primaryColumn->getName() => $primaryColumn], $this->columns);
            $this->primaryColumns = array_merge([$primaryColumn->getName()], $this->primaryColumns);
            return $this;
        }

        if (is_string($primaryColumn)) {
            $this->primaryColumns = array_merge([$primaryColumn], $this->primaryColumns);
            return $this;
        }

        if (is_array($primaryColumn)) {
            foreach (array_reverse($primaryColumn) as $column) {
                $this->addPrimary($column);
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getNewName()
    {
        return $this->newName;
    }

    /**
     * @return Column[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param string $name
     * @return Column|null
     */
    public function getColumn($name)
    {
        return isset($this->columns[$name]) ? $this->columns[$name] : null;
    }

    /**
     * @param string $name
     * @return MigrationTable
     */
    public function dropColumn($name)
    {
        $this->columnsToDrop[] = $name;
        return $this;
    }

    /**
     * @return array
     */
    public function getColumnsToDrop()
    {
        return $this->columnsToDrop;
    }

    /**
     * @return Column[]
     */
    public function getColumnsToChange()
    {
        return $this->columnsToChange;
    }

    /**
     * @return array
     */
    public function getPrimaryColumns()
    {
        return $this->primaryColumns;
    }

    /**
     * @param string|array $columns name(s) of column(s)
     * @param string $type type of index (unique, fulltext) default ''
     * @param string $method method of index (btree, hash) default ''
     * @param string $name name of index
     * @return MigrationTable
     */
    public function addIndex($columns, $type = Index::TYPE_NORMAL, $method = Index::METHOD_DEFAULT, $name = '')
    {
        $index = new Index($columns, $this->createIndexName($columns, $name), $type, $method);
        $this->indexes[] = $index;
        return $this;
    }

    /**
     * @return Index[]
     */
    public function getIndexes()
    {
        return $this->indexes;
    }

    /**
     * @param string|array $columns
     * @return MigrationTable
     */
    public function dropIndex($columns)
    {
        $indexName = $this->createIndexName($columns);
        return $this->dropIndexByName($indexName);
    }

    /**
     * @param string $indexName
     * @return MigrationTable
     */
    public function dropIndexByName($indexName)
    {
        $this->indexesToDrop[] = $indexName;
        return $this;
    }

    /**
     * @return array
     */
    public function getIndexesToDrop()
    {
        return $this->indexesToDrop;
    }

    /**
     * @param string|array $columns
     * @param string $referencedTable
     * @param string|array $referencedColumns
     * @param string $onDelete
     * @param string $onUpdate
     * @return MigrationTable
     */
    public function addForeignKey($columns, $referencedTable, $referencedColumns = ['id'], $onDelete = ForeignKey::DEFAULT_ACTION, $onUpdate = ForeignKey::DEFAULT_ACTION)
    {
        $this->foreignKeys[] = new ForeignKey($columns, $referencedTable, $referencedColumns, $onDelete, $onUpdate);
        return $this;
    }

    /**
     * @return ForeignKey[]
     */
    public function getForeignKeys()
    {
        return $this->foreignKeys;
    }

    /**
     * @param string|array $columns
     * @return MigrationTable
     */
    public function dropForeignKey($columns)
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }
        $this->foreignKeysToDrop[] = implode('_', $columns);
        return $this;
    }

    /**
     * @return array
     */
    public function getForeignKeysToDrop()
    {
        return $this->foreignKeysToDrop;
    }

    /**
     * @return MigrationTable
     */
    public function dropPrimaryKey()
    {
        $this->dropPrimaryKey = true;
        return $this;
    }

    /**
     * @return boolean
     */
    public function hasPrimaryKeyToDrop()
    {
        return $this->dropPrimaryKey;
    }

    /**
     * @param string $charset
     * @return MigrationTable
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @param string $collation
     * @return MigrationTable
     */
    public function setCollation($collation)
    {
        $this->collation = $collation;
        return $this;
    }

    /**
     * @return string
     */
    public function getCollation()
    {
        return $this->collation;
    }

    public function getCopyType()
    {
        return $this->copyType;
    }
    
    /**
     * @param string $comment
     * @return MigrationTable
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @return MigrationTable
     */
    public function unsetComment()
    {
        return $this->setComment('');
    }

    public function create()
    {
        $this->action = self::ACTION_CREATE;
        $this->addPrimary($this->tmpPrimaryKey);
    }

    public function save()
    {
        $this->action = self::ACTION_ALTER;
    }

    public function drop()
    {
        $this->action = self::ACTION_DROP;
    }

    public function rename($newName)
    {
        $this->action = self::ACTION_RENAME;
        $this->newName = $newName;
    }

    public function copy($newName, $copyType = self::COPY_ONLY_STRUCTURE)
    {
        $this->inArray($copyType, [self::COPY_ONLY_STRUCTURE, self::COPY_ONLY_DATA, self::COPY_STRUCTURE_AND_DATA], 'Copy type "' . $copyType . '" is not allowed');

        $this->action = self::ACTION_COPY;
        $this->newName = $newName;
        $this->copyType = $copyType;
    }

    public function getAction()
    {
        return $this->action;
    }

    private function createIndexName($columns, $name = '')
    {
        if ($name) {
            return $name;
        }

        if (!is_array($columns)) {
            $columns = [$columns];
        }
        return 'idx_' . $this->getName() . '_' . implode('_', $columns);
    }

    /**
     * @return Table
     */
    public function toTable()
    {
        $table = new Table($this->getName());
        $table->setCharset($this->getCharset());
        $table->setCollation($this->getCollation());
        $table->setComment($this->getComment());
        if ($this->getPrimaryColumns()) {
            $table->setPrimary($this->getPrimaryColumns());
        }
        foreach ($this->getColumns() as $column) {
            $table->addColumn($column);
        }
        foreach ($this->getIndexes() as $index) {
            $table->addIndex($index);
        }
        foreach ($this->getForeignKeys() as $foreignKey) {
            $table->addForeignKey($foreignKey);
        }
        return $table;
    }
}
