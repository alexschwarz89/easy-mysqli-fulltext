<?php

namespace Alexschwarz89\EasyMysqliFulltext;

use Dotenv\Dotenv;
use Alexschwarz89\EasyMysqliFulltext\Exception\QueryFailedException;
use Alexschwarz89\EasyMysqliFulltext\Exception\QueryValidationException;
use Alexschwarz89\EasyMysqliFulltext\Exception\ConnectionFailedException;

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
     * 
     * @var \mysqli
     */
    public $db             = null;
    /**
     * Setting the host connection instance
     * 
     * @var null
     */
    private $host = null;
    /**
     * Setting host user connection instance
     * 
     * @var null
     */
    private $user = null;
    /**
     * Setting host password connection instance
     * 
     * @var null;
     */
    private $password = null;
    /**
     * Setting database name instance
     * 
     * @var null
     */
    private $database = null;

    /**
     * @var SearchQuery
     */
    private $searchQuery    = null;
    /**
     * The results as an associative array
     *
     * @var array
     */
    public $searchResult   = null;
    /**
     * After execute is called, contains the number of matched rows
     *
     * @var null
     */
    public $numRows         = null;
    /**
     * After execute is called, contains the number of all matched rows
     * This ignores LIMIT and OFFSET parameters
     * Use for pagination
     *
     * @var null
     */
    public $totalRows         = null;
    
    /**
     * Needs an instance of MYSQLi.
     * Also have a look at self::createWithMYSQLi to create a new instance
     *
     * @param \mysqli $connection
     */
    public function __construct(\mysqli $connection)
    {
        $this->loadEnv();
        $this->host     = getenv('DATABASE_HOST');
        $this->user     = getenv('DATABASE_USERNAME');
        $this->password = getenv('DATABASE_PASSWORD');
        $this->database = getenv('DATABASE_NAME');

        if ($connection !== null) {
            $this->db = $connection;
        }
    }

    /**
     * Load Dotenv to grant getenv() access to environment variables in .env file
     *
     * @return Values
     */
    protected function loadEnv()
    {
        if(!getenv("APP_ENV")) {
            $dotenv = new Dotenv($_SERVER['DOCUMENT_ROOT']);
            $dotenv->load();
        }
    }

    /**
     * Returns a new Instance of itself with setting up a mysqli instance
     * Throws a Exception with mysqli connect_errno as Code if there is a problem
     *
     * @return Search
     * @throws \Exception
     */
    public static function createWithMYSQLi()
    {
        $db = new \mysqli($this->host, $this->user, $this->password, $this->database);
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

        $query      = $this->searchQuery;
        $countQuery = $this->searchQuery->composeCountQuery();

        $countResult = $this->db->query($countQuery);
        if (!$countResult) {
            throw new QueryFailedException('No valid result from Database, Error: ' . $this->db->error, $this->db->errno);
        }

        $result = $this->db->query($query);

        if (!$result) {
            throw new QueryFailedException('No valid result from Database, Error: ' . $this->db->error, $this->db->errno);
        }

        $this->searchResult = $result->fetch_all(MYSQLI_ASSOC);
        $this->numRows      = count($this->searchResult);
        $this->totalRows    = $countResult->fetch_row()[0];
        return $this->searchResult;
    }
}
