<?php

require_once __DIR__ . '/../vendor/autoload.php';

use \Alexschwarz89\EasyMysqliFulltext\SearchQuery;
use \Alexschwarz89\EasyMysqliFulltext\Search;

$search = Search::createWithMYSQLi('localhost', 'username', 'password', 'dbname');

// You can also create a new Search with an existing MYSQLi connection
// $search  = new Search($mysqliInstance)

$query = new SearchQuery($search);
$query->setTable('testdata')
    ->setSearchFields('description')
    ->mustInclude('example')
    ->canInclude('another')
    ->exclude('again');

$search->setSearchQuery( $query );

$result = $search->execute();

if ($search->numRows > 0) {
    var_dump($result);
} else {
    print "No results.";
}