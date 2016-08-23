<?php

namespace Sassnowski\CsvSchema;

require __DIR__.'/../vendor/autoload.php';

$config = [
    'schema' => [
        'book' => 'int',
        'series' => 'string',
        'id_1' => 'string',
        'id_1000' => 'string',
        'id_a10' => 'string',
        'id_a100' => 'string',
        'id_a1000' => 'string',
        'id_50000' => 'string',
        'manufacturer_price' => 'int',
        'model_by' => 'string',
        'decoration_by' => 'string',
        'model_year' => 'string',
        'model_number' => 'string',
        'title' => 'string',
        'height' => 'string',
        'width' => 'string',
        'artist' => 'int',
        'literature' => 'string',
        'bild_64' => 'int',
        'bild_641' => 'int',
        'bild_642' => 'int',
        'bild_643' => 'int',
    ],
];

$parser = new Parser($config);
$rows = $parser->fromFile('/Users/KaiS/Downloads/BUCH641.csv');

var_dump($rows[1]->model_by);
