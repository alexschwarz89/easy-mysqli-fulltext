# EasyMysqliFulltext
An easy-to-use Library to perform ranked fulltext searches with MYSQLi.

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/615491a8-2afb-423e-ba36-8d7ed820b8ee/mini.png)](https://insight.sensiolabs.com/projects/615491a8-2afb-423e-ba36-8d7ed820b8ee)
[![Latest Stable Version](https://poser.pugx.org/alexschwarz89/easy-mysqli-fulltext/v/stable)](https://packagist.org/packages/alexschwarz89/easy-mysqli-fulltext)
[![License](https://poser.pugx.org/alexschwarz89/easy-mysqli-fulltext/license)](https://packagist.org/packages/alexschwarz89/easy-mysqli-fulltext)
[![composer.lock](https://poser.pugx.org/alexschwarz89/easy-mysqli-fulltext/composerlock)](https://packagist.org/packages/alexschwarz89/easy-mysqli-fulltext)
[![GetBadges Game](https://alexschwarz89-easy-mysqli-fulltext.getbadges.io/shield/company/alexschwarz89-easy-mysqli-fulltext)](https://alexschwarz89-easy-mysqli-fulltext.getbadges.io/?ref=shield-game)

## Install

Install via [composer](https://getcomposer.org):

```javascript
{
    "require": {
        "alexschwarz89/EasyMysqliFulltext": "1.2.0"
    }
}
```

Run `composer install`.

## Getting Started

You will find a example file in examples/index.php to use with the included testdata.sql.

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
try {
    $search->execute();
} catch (EmptySearchTermException $e) {
    // Handle invalid search terms
}
```

## You can also

#### Use Search without an existing MYSQLi connection
```php
$search = Search::createWithMYSQLi('localhost', 'username', 'password', 'dbname');
```

#### You can also pass the connection variables via Environment Variable (.env) and just Use
```php
$search = Search::createWithMYSQLi();
```
#### .env file saved in your root directory
``` 
DATABASE_HOST=localhost
DATABASE_USERNAME=username
DATABASE_PASSWORD=password
DATABASE_NAME=database_name
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
