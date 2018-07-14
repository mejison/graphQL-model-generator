<?php

namespace App\GraphQL\Query;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;
use App\News;

class NewsQuery extends Query
{
    protected $attributes = [
        'name' => 'NewsQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('News'));
    }

    public function args()
    {
        return [
            'id' => [
                            'name' => 'id',
                            'type' => Type::string()
                        ],
                    'title' => [
                        'name' => 'title',
                        'type' => Type::string(),
                    ],
                
                    'description' => [
                        'name' => 'description',
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
       $query = News::query();
        foreach($args as $key => $value) {
            $query->where($key, $value);
        }
        
        if ( ! empty($args)) {
            return $query->get();
        }

        return News::all();
    }
}