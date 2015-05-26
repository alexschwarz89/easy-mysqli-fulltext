# EasyMysqliFulltext
An easy-to-use Library to perform ranked fulltext searches with MYSQLi.

## Install

Install via [composer](https://getcomposer.org):

```javascript
{
    "require": {
        "alexschwarz89/EasyMysqliFulltext": "1.0.0"
    }
}
```

Run `composer install`.

## Getting Started

#### Set up search on a existing MYSQLi connection
```php
use \Alexschwarz89\EasyMysqliFulltext\Search;
$search = new Search( $mysqliInstance );
```

#### Simply searching for "example" in our testdata

```php
$query = new SearchQuery($search);
$query->setTable('testdata')
    ->setSearchFields('description')
    ->mustInclude('example');
    
$search->setSearchQuery( $query );
$search->execute();
```

## You can also

#### Use Search without an existing MYSQLi connection
```php
$search = Search::createWithMYSQLi('localhost', 'username', 'password', 'dbname');
```

#### Build more complex search queries
```php
$query->setTable('testdata') 
    ->setSearchFields('description,title,isbn,author')
    ->mustInclude('example')
    ->canInclude('another')
    ->exclude('again')
    ->preferWithout('this')
    ->orderBy('some_field', 'ASC');
```

Contributing is surely allowed! :-)

