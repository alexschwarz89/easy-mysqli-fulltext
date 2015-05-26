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

## Example usage of the Query Builder

#### Search table "testdata" for all rows that contain "example" in field "description"

```php
$query = new SearchQuery($search);
$query->setTable('testdata')
    ->setSearchFields('description')
    ->mustInclude('example');
    
$search->setSearchQuery( $query );
$search->execute();
```

