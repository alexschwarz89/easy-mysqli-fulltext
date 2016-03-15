<?php

namespace Alexschwarz89\EasyMysqliFulltext;
use Alexschwarz89\EasyMysqliFulltext\Exception\ConnectionFailedException;
use Alexschwarz89\EasyMysqliFulltext\Exception\QueryFailedException;
use Alexschwarz89\EasyMysqliFulltext\Exception\QueryValidationException;

/**
 * Class Search
 *
 * An easy-to-use library to perform ranked fulltext searches with MYSQLi
 *
 * @author Alex Schwarz <alexschwarz@live.de>
 * @package Alexschwarz89\MysqliFulltext
 */
class Search
{
    /**
     * Holding the mysqli instance
     * @var \mysqli
     */
    public $db             = null;
    /**
     * @var SearchQuery
     */
    private $searchQuery    = null;
    /**
     * The results as an associative array
     *
     * @var Array
     */
    public $searchResult   = null;

    /**
     * After execute is called, contains the number of matched rows
     *
     * @var null
     */
    public $numRows         = null;

    /**
     * Needs an instance of MYSQLi.
     * Also have a look at self::createWithMYSQLi to create a new instance
     *
     * @param \mysqli $connection
     */
    public function __construct(\mysqli $connection)
    {
        if ($connection !== null) {
            $this->db = $connection;
        }
    }

    /**
     * Returns a new Instance of itself with setting up a mysqli instance
     * Throws a Exception with mysqli connect_errno as Code if there is a problem
     *
     * @param $host
     * @param $user
     * @param $password
     * @param $database
     * @return Search
     * @throws \Exception
     */
    public static function createWithMYSQLi($host, $user, $password, $database)
    {
        $db = new \mysqli($host, $user, $password, $database);
        if ($db->connect_errno) {
            throw new ConnectionFailedException('Failed to connect to MySQL', $db->connect_errno);
        }
        return new self($db);
    }

    /**
     * Sets the Subject to search for
     *
     * @param SearchQuery $query
     */
    public function setSearchQuery(SearchQuery $query)
    {
        $this->searchQuery = $query;
    }

    /**
     * Checks if a search query is set and if this query validates
     *
     * @throws QueryValidationException
     */
    public function validate()
    {
        if (!$this->searchQuery instanceof SearchQuery) {
            throw new QueryValidationException('Must set a search query.');
        }

        $this->searchQuery->validate();
    }

    /**
     * Performs the actual query on the database and returns the results
     * as an associative array. If there a no results, returns an empty array.
     *
     * @return Array
     * @throws \Exception
     */
    public function execute()
    {
        $this->validate();

        $query = $this->searchQuery;
        $result = $this->db->query($query);

        if (!$result) {
            throw new QueryFailedException('No valid result from Database, Error: ' . $this->db->error, $this->db->errno);
        }

        $this->searchResult = $result->fetch_all(MYSQLI_ASSOC);
        $this->numRows = count($this->searchResult);
        return $this->searchResult;
    }
}
