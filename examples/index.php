<?php

require_once __DIR__ . '/../vendor/autoload.php';

use \Alexschwarz89\EasyMysqliFulltext\SearchQuery;
use \Alexschwarz89\EasyMysqliFulltext\Search;

$search = Search::createWithMYSQLi('localhost', 'username', 'password', 'dbname');

// You can access the newly created db instance, for example to set the charset
$search->db->set_charset('utf8');

// You can also create a new Search with an existing MYSQLi connection
// $search  = new Search($mysqliInstance)

$query = new SearchQuery($search);
$query->setTable('testdata')
    ->setSearchFields('description')
    ->mustInclude('example')
    ->canInclude('another')
    ->exclude('again');

$search->setSearchQuery( $query );

$results = $search->execute();

if ($search->numRows > 0) {
    print "There are " . $search->numRows . "rows that match your search.\n";
    //var_dump($results)
} else {
    print "No results. \n";
}
