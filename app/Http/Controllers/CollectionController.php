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
        $this->registerConfig($data['name']);
    }

    public function registerConfig($name) {
        $ini = parse_ini_file(base_path() . "/graphql.ini", true);

        $ini['querys'][$name] = "App\GraphQL\Query\\" . ucfirst($name) . "Query";
        $ini['types'][ucfirst($name)] = "App\GraphQL\Type\\" . ucfirst($name) . "Type";

        
        $this->write_ini_file(base_path() . "/graphql.ini", $ini);
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
    
    public function write_ini_file($file, $array = []) {
        // check first argument is string
        if (!is_string($file)) {
            throw new \InvalidArgumentException('Function argument 1 must be a string.');
        }

        // check second argument is array
        if (!is_array($array)) {
            throw new \InvalidArgumentException('Function argument 2 must be an array.');
        }

        // process array
        $data = array();
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $data[] = "[$key]";
                foreach ($val as $skey => $sval) {
                    if (is_array($sval)) {
                        foreach ($sval as $_skey => $_sval) {
                            if (is_numeric($_skey)) {
                                $data[] = $skey.'[] = '.(is_numeric($_sval) ? $_sval : (ctype_upper($_sval) ? $_sval : '"'.$_sval.'"'));
                            } else {
                                $data[] = $skey.'['.$_skey.'] = '.(is_numeric($_sval) ? $_sval : (ctype_upper($_sval) ? $_sval : '"'.$_sval.'"'));
                            }
                        }
                    } else {
                        $data[] = $skey.' = '.(is_numeric($sval) ? $sval : (ctype_upper($sval) ? $sval : '"'.$sval.'"'));
                    }
                }
            } else {
                $data[] = $key.' = '.(is_numeric($val) ? $val : (ctype_upper($val) ? $val : '"'.$val.'"'));
            }
            // empty line
            $data[] = null;
        }

        // open file pointer, init flock options
        $fp = fopen($file, 'w');
        $retries = 0;
        $max_retries = 100;

        if (!$fp) {
            return false;
        }

        // loop until get lock, or reach max retries
        do {
            if ($retries > 0) {
                usleep(rand(1, 5000));
            }
            $retries += 1;
        } while (!flock($fp, LOCK_EX) && $retries <= $max_retries);

        // couldn't get the lock
        if ($retries == $max_retries) {
            return false;
        }

        // got lock, write data
        fwrite($fp, implode(PHP_EOL, $data).PHP_EOL);

        // release lock
        flock($fp, LOCK_UN);
        fclose($fp);

        return true;
    }
}
