# CSV Schema Parser

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
        'age' => 'integer',
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

The configuration array provides a way to overwrite the default settings of the parser. You can set the `delimiter`, the `enclosure` character as well as the `escape` character.

```php
<?php

$config = [
    'delimiter' => ',',
    'enclosure' => '"',
    'escape' => '\\',
    'schema' => [ /* Your schema */ ],
];
```

### Available Column Types

You can parse a column to `string`, `float`, `integer` and `array`.

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

array(5) {
    [0]=>
    string(4) "Lots"
    [1]=>
    string(3) "and"
    [2]=>
    string(4) "lots"
    [3]=>
    string(2) "of"
    [4]=>
    string(7) "friends"
  }
```
