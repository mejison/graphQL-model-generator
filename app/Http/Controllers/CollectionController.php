<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Artisan;
use GraphQL;

class CollectionController extends Controller
{
    public function createCollection(Request $request) {
        
        $data = $request->only(['name', 'fields']);
        $data['fields'] = json_decode($data['fields']);

        $this->createMigration($data);
        $this->createModel($data);
        $this->createType($data);
        $this->createQuery($data);
    }

    public function createQuery($data) {
        $query = file_get_contents(app_path() . '/Stubs/query.stub');
        
        $vars = [
            'class' => ucfirst($data['name']) . 'Query',
            'type' => ucfirst($data['name']),
            'fields' =>  "'id' => [
                            'name' => 'id',
                            'type' => Type::string()
                        ],"
        ];

        if ( ! empty($data['fields'])) {
            foreach($data['fields'] as $field) {
                $vars['fields'] .= "
                    '{$field->name}' => [
                        'name' => '{$field->name}',
                        'type' => Type::{$field->type}(),
                    ],
                ";
            }
        }

        $vars['fields'] .= "
                    'created_at' => [
                        'name' => 'created_at',
                        'type' => Type::string()
                    ],
                ";

        $query = preg_replace_callback('/\{\{(\w+)\}\}/', function($matches) use ($vars) {
            return $vars[end($matches)];
        }, $query);

        file_put_contents(app_path() . '/GraphQL/Query/' . $vars['class']. '.php' , $query);

        GraphQL::addSchema('default', [
            'query' => [
                $data['name'] => "App/GraphQL/Query/" . $vars['class']
            ]
        ]);
    }

    public function createType($data) {
        $query = file_get_contents(app_path() . '/Stubs/type.stub');
        
        $vars = [
            'class' => ucfirst($data['name']) . 'Type',
            'fields' => ''
        ];

        if ( ! empty($data['fields'])) {
            foreach($data['fields'] as $field) {
                $vars['fields'] .= "
                    '{$field->name}' => [
                        'type' => Type::{$field->type}(),
                        'description' => ''
                    ],
                ";
            }
        }

        $query = preg_replace_callback('/\{\{(\w+)\}\}/', function($matches) use ($vars) {
            return $vars[end($matches)];
        }, $query);

        file_put_contents(app_path() . '/GraphQL/Type/' . $vars['class']. '.php' , $query);

        GraphQL::addType("App/GraphQL/Type/" . $vars['class'], ucfirst($data['name']));
    }

    public function createModel($data) {
        $model = file_get_contents(app_path() . '/Stubs/model.stub');

        $vars = [
            'namespace' => 'App',
            'class' => ucfirst($data['name']),
            'table' => $data['name'],
            'fillable' => ''
        ];

        if ( ! empty($data['fields'])) {
            $data['fields'] = "'" . implode(collect($data['fields'])->pluck('name')->toArray(), "', '") . "'";
        }

        $model = preg_replace_callback('/\{\{(\w+)\}\}/', function($matches) use ($vars) {
            return $vars[end($matches)];
        }, $model);

        file_put_contents(app_path() . '/' . $vars['class']. '.php' , $model);
    }

    public function createMigration($data) {
        $migration = file_get_contents(app_path() . '/Stubs/migration.stub');
        $vars = [
            'migration_name' => 'Create' . ucfirst($data['name']) . 'Table',
            'table_name' => $data['name'],
            'fields' => '',
        ];

        foreach($data['fields'] as $field) {
            $vars['fields'] .= "\$table->" . $field->type . "('" . $field->name . "');";
        }
        
        $migration = preg_replace_callback('/\{\{(\w+)\}\}/', function($matches) use ($vars) {
            return $vars[end($matches)];
        }, $migration);

        file_put_contents(database_path() . '/migrations/' . date('Y') . '_' . date('m') . '_' . date('d') . '_' . time() . '_create_' . $data['name'] . '_table.php', $migration);

        Artisan::call('migrate');
    }
}
