<?php

namespace Alexschwarz89\EasyMysqliFulltext;

use Alexschwarz89\EasyMysqliFulltext\Exception\EmptySearchTermException;
use Alexschwarz89\EasyMysqliFulltext\Exception\QueryValidationException;
use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\QueryFactory;

/**
 * Class SearchQuery
 *
 * Builds a MYSQL-compatible query to be used with Search Class
 *
 * @author Alex Schwarz <alexschwarz@live.de>
 * @package Alexschwarz89\MysqliFulltext
 */
class SearchQuery
{
    /**
     * The final composed query after all work is done
     *
     * @var
     */
    public $composedQuery;
    /**
     * The table name, where to search at
     *
     * @var String
     */
    private $table = null;
    /**
     * The columns where to search at (e.g. 'title, description')
     *
     * @var null
     */
    private $searchFields = null;
    /**
     * The columns to include in the results
     *
     * @var array
     */
    private $selectFields = ['*'];
    /**
     * The conditions built with addCondition, just stored here
     *
     * @var null
     */
    private $searchConditions = null;
    /**
     * Additional where conditions as an array
     *
     * @var array
     */
    private $whereConditions = [];
    /**
     * If set to 'relevance' will automatically select the relevance
     * and does everything for you.
     *
     * @var string
     */
    private $orderBy = 'relevance';
    /**
     * Ascending or Descending Sort
     *
     * @var string
     */
    private $ascDesc = 'DESC';
    /**
     * Maximum number of results returned
     *
     * @var int|null
     */
    private $limit = null;
    /**
     * Offset for results (e.g. for pagination)
     *
     * @var int|null
     */
    private $offset = null;
    /**
     * Instance of Alexschwarz89\MysqliFulltext\Search
     *
     * @var Search
     */
    private $searchInstance = null;

    /**
     * Pass the instance of Search
     * This is needed for escaping with mysqli_escape_string
     *
     * @param Search $search
     */
    public function __construct(Search $search)
    {
        $this->searchInstance = $search;
    }

    /**
     * Sets the table name (as String) to search at
     *
     * @param $table
     * @return $this
     */
    public function setTable($table): SearchQuery
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Sets the fields to search at (e.g. 'title, description')
     *
     * @param String $fields
     * @return $this
     */
    public function setSearchFields($fields): SearchQuery
    {
        $this->searchFields = $fields;
        return $this;
    }

    /**
     * Sets the fields to include in the search results
     * Must be an Array (['*'] to select all fields)
     *
     * @param array $fields
     * @return $this
     */
    public function setSelectFields($fields): SearchQuery
    {
        $this->selectFields = $fields;
        return $this;
    }

    /**
     * A Term that must not be present in any of the rows that are returned
     *
     * @param $term
     * @return $this
     */
    public function exclude($term): SearchQuery
    {
        $this->addCondition('-', $term);
        return $this;
    }

    /**
     * Adds a condition which will be composed later
     *
     * @param String $prefix
     * @param String $value
     * @param String $suffix optional
     */
    protected function addCondition($prefix, $value, $suffix = null): void
    {
        $this->searchConditions[] = [
            'value'  => $this->sanitizeIncludeTerm($value),
            'prefix' => $prefix,
            'suffix' => $suffix
        ];
    }

    /**
     * Sanitizes include terms and removes invalid characters
     * that lead to syntax errors when using INNODB
     *
     * leading a plus and minus sign combination
     * leading / trailing plus or minus signs
     * search terms that only consist of special characters (-*+)
     *
     * @see http://dev.mysql.com/doc/refman/5.6/en/fulltext-boolean.html
     * @param $term
     * @return string
     */
    public function sanitizeIncludeTerm($term): string
    {
        return preg_replace('/[+\-><\(\)~*\"@]+/', ' ', trim($term));
    }

    /**
     * This word must be present in each row that is returned
     *
     * @param $term
     * @return $this
     */
    public function mustInclude($term): SearchQuery
    {
        $this->addCondition('+', $term);
        return $this;
    }

    /**
     * This part must be present in each row that is returned
     * Adds * to the end of the word, e.g. to make
     * "Exa*" match "Example".
     *
     * @param $term
     * @return $this
     */
    public function mustIncludeWildcard($term): SearchQuery
    {
        $this->addCondition('+', $term, '*');
        return $this;
    }

    /**
     * This word is optional, but rows that contain it are rated higher.
     * This mimics the behavior of MATCH() ... AGAINST() without the IN BOOLEAN MODE modifier.
     *
     * @param $term
     * @return $this
     */
    public function canInclude($term): SearchQuery
    {
        $this->addCondition('', $term);
        return $this;
    }

    /**
     * A row containing a word marked with tilde is rated lower than others, but is NOT excluded,
     * as it would be with exclude function
     *
     * @param $term
     * @return $this
     */
    public function preferWithout($term): SearchQuery
    {
        $this->addCondition('~', $term);
        return $this;
    }

    /**
     * Increases the word's contribution to the relevance value that is assigned to a row
     *
     * @param $term
     * @return $this
     */
    public function rankHigher($term): SearchQuery
    {
        $this->addCondition('>', $term);
        return $this;
    }

    /**
     * Decreases the word's contribution to the relevance value that is assigned to a row
     *
     * @param $term
     * @return $this
     */
    public function rankLower($term): SearchQuery
    {
        $this->addCondition('<', $term);
        return $this;
    }

    /**
     * Does only match rows that contain the phrase literally, as it was typed.
     * (e.g. "some words" would match "some words of wisdom" but not "some noise words")
     *
     * @param $term
     * @return $this
     */
    public function mustContainPhrase($term): SearchQuery
    {
        $this->addCondition('"', $term, '"');
        return $this;
    }

    /**
     * Add a additional where condition as string
     * can be called multiple times
     *
     * @param String $cond
     * @return $this
     */
    public function addWhere($cond): SearchQuery
    {
        $this->whereConditions[] = $cond;
        return $this;
    }

    /**
     * If you want to change the default behaviour to rank by relevance
     * you can specify an order by string here (e.g. 'title')
     *
     * @param $fields
     * @param string $ascDesc
     * @return $this
     */
    public function orderBy($fields, $ascDesc = 'DESC'): SearchQuery
    {
        $this->orderBy = $fields;
        $this->ascDesc = $ascDesc;
        return $this;
    }

    /**
     * Set the maximum number of matches that should be returned
     *
     * @param $limit
     * @return $this
     */
    public function limit($limit): SearchQuery
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Set an offset of which result should be returned
     * e.g. use with limit (LIMIT 10,10)
     *
     * @param $offset
     * @return $this
     */
    public function offset($offset): SearchQuery
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Checks if all requirements met to actually compose the query
     *
     * @return bool
     * @throws QueryValidationException
     * @throws EmptySearchTermException
     */
    public function validate(): bool
    {
        if ($this->searchConditions === null) {
            throw new QueryValidationException('Must specify at least one search condition.');
        }

        foreach ($this->searchConditions as $condition) {
            if (strlen(trim($condition['value'])) == 0) {
                throw new EmptySearchTermException();
            }
        }

        return true;
    }

    /**
     * Composes count query and returns it as a string
     *
     * @return string
     */
    public function composeCountQuery(): string
    {
        $queryFactory = new QueryFactory('mysql');
        $select = $queryFactory->newSelect();
        $matchString = $this->getMatchString();
        $select->from($this->table)
            ->cols(['COUNT(*)'])
            ->where($matchString);

        $select = $this->addWhereConditions($select);

        return (string)$select;
    }

    /**
     * Returns the MATCH … AGAINST string
     *
     * @return string
     */
    protected function getMatchString(): string
    {
        $terms = $this->getSearchConditionsString();
        $matchString = "MATCH (" . $this->searchInstance->db->real_escape_string($this->searchFields) . ")";
        $matchString .= " AGAINST ('" . $this->searchInstance->db->real_escape_string($terms) . "' IN BOOLEAN MODE)";

        return $matchString;
    }

    /**
     * Returns all previously defined search Conditions as a String
     *
     * @return string
     */
    protected function getSearchConditionsString(): string
    {
        $terms = '';

        foreach ($this->searchConditions as $condition) {
            $terms .= $condition['prefix'] . $condition['value'] . $condition['suffix'] . ' ';
        }

        return $terms;
    }

    /**
     * Adds where conditions to SelectInterface
     *
     * @param SelectInterface $select
     * @return SelectInterface
     */
    protected function addWhereConditions(SelectInterface $select): SelectInterface
    {
        $addWhereConditions = function ($value) use ($select) {
            /* @var SelectInterface $select */
            $select->where($value);
        };

        array_map($addWhereConditions, $this->whereConditions);

        return $select;
    }

    /**
     * Returns the actual composed query as a string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->compose();
    }

    /**
     * Composes the actual query that later will be sent to the database
     * and returns it.
     *
     * @return string
     */
    public function compose(): string
    {
        $queryFactory = new QueryFactory('mysql');
        $select = $queryFactory->newSelect();

        $matchString = $this->getMatchString();

        if ($this->orderBy == 'relevance') {
            $this->selectFields[] = $matchString . ' AS relevance';
        }

        $select->from($this->table)
            ->cols($this->selectFields)
            ->orderBy([$this->orderBy . ' ' . $this->ascDesc]);
        $select->where($matchString);

        $select = $this->addWhereConditions($select);

        if ($this->limit !== null) {
            $select->limit($this->limit);
        }

        if ($this->offset !== null) {
            $select->offset($this->offset);
        }

        return (string)$select;
    }
}
