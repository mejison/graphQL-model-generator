<?php

namespace App\GraphQL\Type;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Type as BaseType;
use GraphQL;

class TestType extends BaseType
{
    protected $attributes = [
        'name' => 'TestType',
        'description' => 'A type'
    ];

    public function fields()
    {
        return [
           
                    'name' => [
                        'type' => Type::string(),
                        'description' => ''
                    ],
                
        ];
    }
}
