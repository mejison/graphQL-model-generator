<?php

namespace App\GraphQL\Query;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;
use App\Test;

class TestQuery extends Query
{
    protected $attributes = [
        'name' => 'TestQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('Test'));
    }

    public function args()
    {
        return [
            'id' => [
                            'name' => 'id',
                            'type' => Type::string()
                        ],
                    'name' => [
                        'name' => 'name',
                        'type' => Type::string(),
                    ],
                
                    'created_at' => [
                        'name' => 'created_at',
                        'type' => Type::string()
                    ],
                
        ];
    }

    public function resolve($root, $args)
    {
       $query = Test::query();
        foreach($args as $key => $value) {
            $query->where($key, $value);
        }
        
        if ( ! empty($args)) {
            return $query->get();
        }

        return Test::all();
    }
}