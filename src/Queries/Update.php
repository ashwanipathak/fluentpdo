<?php
namespace Envms\FluentPDO\Queries;

use Envms\FluentPDO\{Query,Literal};

/**
 * UPDATE query builder
 *
 * @method Update  leftJoin(string $statement) add LEFT JOIN to query
 *                        ($statement can be 'table' name only or 'table:' means back reference)
 * @method Update  innerJoin(string $statement) add INNER JOIN to query
 *                        ($statement can be 'table' name only or 'table:' means back reference)
 * @method Update  orderBy(string $column) add ORDER BY to query
 * @method Update  limit(int $limit) add LIMIT to query
 */
class Update extends Common
{

    /**
     * UpdateQuery constructor
     *
     * @param Query     $fluent
     */
    public function __construct(Query $fluent) {
        $clauses = array(
            'UPDATE'   => array($this, 'getClauseUpdate'),
            'JOIN'     => array($this, 'getClauseJoin'),
            'SET'      => array($this, 'getClauseSet'),
            'WHERE'    => ' AND ',
            'ORDER BY' => ', ',
            'LIMIT'    => null,
        );
        parent::__construct($fluent, $clauses);

        $this->statements['UPDATE'] = $fluent->getTableName();

        $tableParts    = explode(' ', $fluent->getTableName());
        $this->joins[] = end($tableParts);
    }

    /**
     * @param string|array $fieldOrArray
     * @param bool|string  $value
     *
     * @return $this
     * @throws \Exception
     */
    public function set($fieldOrArray, $value = false) {
        if (!$fieldOrArray) {
            return $this;
        }
        if (is_string($fieldOrArray) && $value !== false) {
            $this->statements['SET'][$fieldOrArray] = $value;
        } else {
            if (!is_array($fieldOrArray)) {
                throw new \Exception('You must pass a value, or provide the SET list as an associative array. column => value');
            } else {
                foreach ($fieldOrArray as $field => $value) {
                    $this->statements['SET'][$field] = $value;
                }
            }
        }

        return $this;
    }

    /**
     * Execute update query
     *
     * @param boolean $getResultAsPdoStatement true to return the pdo statement instead of row count
     *
     * @return int|boolean|\PDOStatement
     */
    public function execute($getResultAsPdoStatement = false) {
        $result = parent::execute();
        if ($getResultAsPdoStatement) {
            return $result;
        }
        if ($result) {
            return $result->rowCount();
        }

        return false;
    }

    /**
     * @return string
     */
    protected function getClauseUpdate() {
        return 'UPDATE ' . $this->statements['UPDATE'];
    }

    /**
     * @return string
     */
    protected function getClauseSet() {
        $setArray = array();
        foreach ($this->statements['SET'] as $field => $value) {
            if ($value instanceof Literal) {
                $setArray[] = $field . ' = ' . $value;
            } else {
                $setArray[]                      = $field . ' = ?';
                $this->parameters['SET'][$field] = $value;
            }
        }

        return ' SET ' . implode(', ', $setArray);
    }

}
