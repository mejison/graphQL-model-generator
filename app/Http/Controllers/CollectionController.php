<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Artisan;

class CollectionController extends Controller
{
    public function createCollection(Request $request) {
        
        $data = $request->only(['name']);

        $this->createMigration($data);
        $this->createModel($data);
        $this->createQuery($data);
        $this->createType($data);
    }

    public function createQuery($data) {
        $query = file_get_contents(app_path() . '/Stubs/query.stub');
        
        $vars = [
            'class' => ucfirst($data['name']) . 'Query',
            'fields' => ''
        ];

        $query = preg_replace_callback('/\{\{(\w+)\}\}/', function($matches) use ($vars) {
            return $vars[end($matches)];
        }, $query);

        file_put_contents(app_path() . '/GraphQL/Query/' . $vars['class']. '.php' , $query);

        GraphQL::addSchema($vars['class'], [
            'query' => [
                'users' => 'App\GraphQL\Query\\' . $vars['class']. '.php'
            ]
        ]);
    }

    public function createType($data) {

    }

    public function createModel($data) {
        $model = file_get_contents(app_path() . '/Stubs/model.stub');
        
        $vars = [
            'namespace' => 'App',
            'class' => ucfirst($data['name']),
            'table' => $data['name'],
            'fillable' => ''
        ];

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
        
        $migration = preg_replace_callback('/\{\{(\w+)\}\}/', function($matches) use ($vars) {
            return $vars[end($matches)];
        }, $migration);

        file_put_contents(database_path() . '/migrations/' . date('Y') . '_' . date('m') . '_' . date('d') . '_' . time() . '_create_' . $data['name'] . '_table.php', $migration);

        Artisan::call('migrate');
    }
}
