## Adapters

You can setup connection to database in environments block of config file. There are several ways to do so:

#### MySQL
You can use full config array:
```
'environments' => [
    'env1' => [
        'adapter' => 'mysql',
        'db_name' => 'phoenix',
        'host' => 'localhost',
        'username' => 'root',
        'password' => '123',
        'charset' => 'utf8',
    ],
],
```
or dsn:
```
'environments' => [
    'env2' => [
        'adapter' => 'mysql',
        'dsn' => 'mysql:dbname=phoenix;host=localhost;charset=utf8',
        'username' => 'root',
        'password' => '123',
    ],
],
```

#### PostgreSQL
This is similar to MySQL. You can use full config array:
```
'environments' => [
    'env3' => [
        'adapter' => 'pgsql',
        'db_name' => 'phoenix',
        'host' => 'localhost',
        'username' => 'postgres',
        'password' => '123',
        'charset' => 'utf8',
    ],
],
```
or dsn:
```
'environments' => [
    'env4' => [
        'adapter' => 'pgsql',
        'dsn' => 'pgsql:dbname=phoenix;host=localhost;options=\'--client_encoding=utf8\'',
        'username' => 'postgres',
        'password' => '123',
    ],
],
```
#### SQLite

Define file path:
```
'environments' => [
    'env5' => [
        'adapter' => 'sqlite',
        'dsn' => 'sqlite:' . __DIR__ . '/phoenix.sqlite',
    ],
],
```

or use memory:
```
'environments' => [
    'env6' => [
        'adapter' => 'sqlite',
        'dsn' => 'sqlite::memory:',
    ],
],
```
