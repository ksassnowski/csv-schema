# CSV Schema Parser

[![Build Status](https://travis-ci.org/ksassnowski/csv-schema.svg?branch=master)](https://travis-ci.org/ksassnowski/csv-schema)
[![Code Climate](https://codeclimate.com/github/ksassnowski/csv-schema/badges/gpa.svg)](https://codeclimate.com/github/ksassnowski/csv-schema)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ksassnowski/csv-schema/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ksassnowski/csv-schema/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/efc81c31-f930-4d96-8c90-6104d500788a/mini.png)](https://insight.sensiolabs.com/projects/efc81c31-f930-4d96-8c90-6104d500788a)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/ksassnowski/csv-schema/master/LICENSE.md)
[![Current Release](https://img.shields.io/badge/packagist-0.3.0-blue.svg)](https://img.shields.io/badge/release-0.4.1-blue.svg)

Have you ever wanted to have something like an ORM but for CSV files? No? Well now you can!

Introducing **CSV Schema Parser**. The number one way to turn your boring old CSV files into kick-ass PHP objects.
And as if that wasn't amazing enough, it also casts your data to the correct data type! How cool is that? Not very cool you say? Well I disagree!

## Installation

```bash
composer require sassnowski/csv-schema
```

## Usage

First we have to define the schema of the CSV file we're about to parse. The schema is an associative array where the keys
define what properties will be named on the resulting object. The values specify the data type of that column.
The schema is ordered, meaning the first entry in the schema will correspond to the first column in the CSV and so on.

```php
<?php

$config = [
    'schema' => [
        'first_name' => 'string',
        'last_name' => 'string',
        'age' => 'int',
        'coolness_factor' => 'float',
    ]
];
```

After we defined the schema, we can instantiate a new parser from it.

```php
<?php

// continuing from above..

$parser = new Sassnowski\CsvSchema\Parser($config);
```

### Reading from a string

The parser provides a `fromString` method that will turn any CSV string into an array of objects. The objects will be structured according to our schema.

```php
<?php

public Parser::fromString(string $input): array
```

#### Example

```php
<?php

// Using our parser from above..

$input = "Kai,Sassnowski,26,0.3\nJohn,Doe,38,7.8";

$rows = $parser->fromString($input);

var_dump($rows[0]->firstname);
// string(3) "Kai"

var_dump($rows[0]->age);
// int(26)

var_dump($rows[0]->coolness_factor);
// float(0.3)
```

### Reading from a file

More often than not you will parse your CSV from a file. For this the `Parser` provides the `fromFile` method.

```php
<?php

public Parser::fromFile(string $filename): array
```

#### Example

```php
<?php

// Using our parser from above..

// Assuming our file contains the same data as the string example.
$rows = $parser->fromFile('somefile.csv');

var_dump($rows[1]->firstname);
// string(4) "John"

var_dump($rows[1]->coolness_factor);
// float(7.8)
```

## Configuration

The configuration array provides a way to overwrite the default settings of the parser. You can set the `delimiter`, the `enclosure` character, the `escape` character and the `encoding` of the input file.

```php
<?php

$config = [
    'delimiter' => ',',
    'enclosure' => '"',
    'escape' => '\\',
    'skipTitle' => false,
    'encoding' => 'UTF-8',
    'schema' => [ /* Your schema */ ],
];
```

### Available Column Types

You can parse a column to `string`, `float`, `int` and `array`.

#### Parsing arrays

Assuming you have multiple values in a column (sometimes we cannot choose our data...) you might want to parse that column into an array instead.
You can do this by specifying `array:<delimiter>` as the column type in your schema.

```php
<?php

$config = [
    'schema' => [
        'name' => 'string',
        'friends' => 'array:|'
    ],
];

$input = 'Kai Sassnowski,Lots|and|lots|of|friends';

$parser = new Sassnowski\CsvSchema\Parser($config);

$rows = $parser->fromString($input);

var_dump($rows[0])->friends);

// array(5) {
//    [0]=>
//    string(4) "Lots"
//    [1]=>
//    string(3) "and"
//    [2]=>
//    string(4) "lots"
//    [3]=>
//    string(2) "of"
//    [4]=>
//    string(7) "friends"
// }
```

## Adding custom types

Sometimes you might want a bit more control than the built-in types give you. For instance, you might want to query your database
based on an integer in one of your columns and return a model instead. You can easily register
arbitrarily complex types by using the static `registerType` method on the `Parser` class.

```php
<?php

public static registerType(string $type, callable $callback)
```

The provided callback receives the column's value and should return its parsed representation.

```php
<?php

Parser::registerType('foo', function ($value) {
    return $value.'foo';
});

$config = [
    'schema' => [
        'custom' => 'foo'
    ]
];

$parser = new \Sassnowski\CsvSchema\Parser($config);

$rows = $parser->fromString("hello\nworld");

var_dump($rows[0]->custom);
// string(8) "hellofoo"

var_dump($rows[1]->custom);
// string(8) "worldfoo"
```

### Adding parameters to custom types

You can add additional parameters to your types by specifying them in the following format in your schema `<type>:<parameter>`.
In this case, the callback function gets passed a second parameter containing the parameter specified in the schema.
This allows you to reuse your types instead of defining all sorts of variations of the same type (like querying the database, but using a different table/model).

```php
<?php

Parser::registerType('model', function ($value, $table) {
    // Pseudo code, assuming some library.
    return DB::table($table)->findById($value);
});

$config = [
    'schema' => [
        'author' => 'model:authors',
        'uploaded_by' => 'model:users',
    ]
];

$input = "5,13";

$parser = new \Sassnowski\CsvSchema\Parser($config);

$rows = $parser->fromString($input);

var_dump($rows[0]->author);
// object(Author)#1 (15) {
//    ["id"]=>
//    int(5)
//    ...
// }

var_dump($rows[1]->uploaded_by);
// object(User)#1 (12) {
//    ["id"]=>
//    int(13)
//    ...
// }
```
