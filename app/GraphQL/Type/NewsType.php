<?php

namespace App\GraphQL\Type;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Type as BaseType;
use GraphQL;

class NewsType extends BaseType
{
    protected $attributes = [
        'name' => 'NewsType',
        'description' => 'A type'
    ];

    public function fields()
    {
        return [
           
                    'title' => [
                        'type' => Type::string(),
                        'description' => ''
                    ],
                
                    'description' => [
                        'type' => Type::string(),
                        'description' => ''
                    ],
                
        ];
    }
}
