<?php

class Application_Model_Paginator implements Zend_Paginator_Adapter_Interface
{
    protected $_className = null;

    const ROW_COUNT_COLUMN = 'zend_paginator_row_count';

    protected $_countSelect = null;

    protected $_select = null;

    protected $_rowCount = null;

    public function __construct(Zend_Db_Select $select, $className)
    {
        $this->_select = $select;
        $this->_className = $className;
    }


    public function setRowCount($rowCount)
    {
        if ($rowCount instanceof Zend_Db_Select) {
            $columns = $rowCount->getPart(Zend_Db_Select::COLUMNS);

            $countColumnPart = $columns[0][1];

            if ($countColumnPart instanceof Zend_Db_Expr) {
                $countColumnPart = $countColumnPart->__toString();
            }

            $rowCountColumn = $this->_select->getAdapter()->foldCase(self::ROW_COUNT_COLUMN);

            // The select query can contain only one column, which should be the row count column
            if (false === strpos($countColumnPart, $rowCountColumn)) {

                throw new Zend_Paginator_Exception('Row count column not found');
            }

            $result = $rowCount->query(Zend_Db::FETCH_ASSOC)->fetch();

            $this->_rowCount = count($result) > 0 ? $result[$rowCountColumn] : 0;
        } else if (is_integer($rowCount)) {
            $this->_rowCount = $rowCount;
        } else {
            
            throw new Zend_Paginator_Exception('Invalid row count');
        }

        return $this;
    }


    public function getItems($offset, $itemCountPerPage)
    {
        $this->_select->limit($itemCountPerPage, $offset);        
        $stmt = $this->_select->query();
        $stmt->getDriverStatement()->setFetchMode(Zend_Db::FETCH_CLASS,$this->_className);
        return $stmt->getDriverStatement()->fetchAll();
    }


    public function count()
    {
        if ($this->_rowCount === null) {
            $this->setRowCount(
                $this->getCountSelect()
            );
        }

        return $this->_rowCount;
    }

   
    public function getCountSelect()
    {

        if ($this->_countSelect !== null) {
            return $this->_countSelect;
        }

        $rowCount = clone $this->_select;
        $rowCount->__toString(); 

        $db = $rowCount->getAdapter();

        $countColumn = $db->quoteIdentifier($db->foldCase(self::ROW_COUNT_COLUMN));
        $countPart   = 'COUNT(1) AS ';
        $groupPart   = null;
        $unionParts  = $rowCount->getPart(Zend_Db_Select::UNION);


        if (!empty($unionParts)) {
            $expression = new Zend_Db_Expr($countPart . $countColumn);

            $rowCount = $db->select()->from($rowCount, $expression);
        } else {
            $columnParts = $rowCount->getPart(Zend_Db_Select::COLUMNS);
            $groupParts  = $rowCount->getPart(Zend_Db_Select::GROUP);
            $havingParts = $rowCount->getPart(Zend_Db_Select::HAVING);
            $isDistinct  = $rowCount->getPart(Zend_Db_Select::DISTINCT);

          
            if (($isDistinct && count($columnParts) > 1) || count($groupParts) > 1 || !empty($havingParts)) {
                $rowCount = $db->select()->from($this->_select);
            } else if ($isDistinct) {
                $part = $columnParts[0];

                if ($part[1] !== Zend_Db_Select::SQL_WILDCARD && !($part[1] instanceof Zend_Db_Expr)) {
                    $column = $db->quoteIdentifier($part[1], true);

                    if (!empty($part[0])) {
                        $column = $db->quoteIdentifier($part[0], true) . '.' . $column;
                    }

                    $groupPart = $column;
                }
            } else if (!empty($groupParts) && $groupParts[0] !== Zend_Db_Select::SQL_WILDCARD &&
                       !($groupParts[0] instanceof Zend_Db_Expr)) {
                $groupPart = $db->quoteIdentifier($groupParts[0], true);
            }

            
            if (!empty($groupPart)) {
                $countPart = 'COUNT(DISTINCT ' . $groupPart . ') AS ';
            }

            /**
             * Create the COUNT part of the query
             */
            $expression = new Zend_Db_Expr($countPart . $countColumn);

            $rowCount->reset(Zend_Db_Select::COLUMNS)
                     ->reset(Zend_Db_Select::ORDER)
                     ->reset(Zend_Db_Select::LIMIT_OFFSET)
                     ->reset(Zend_Db_Select::GROUP)
                     ->reset(Zend_Db_Select::DISTINCT)
                     ->reset(Zend_Db_Select::HAVING)
                     ->columns($expression);
        }

        $this->_countSelect = $rowCount;

        return $rowCount;
    }
}
