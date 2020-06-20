#Fast File Database (Beta)

ffDB is a database based on files. All operations (CRUD) are executed directly in PHP. Numerous adapters offer high flexibility for storage formats.

## Storage Formats

- PHP (with OPCache)
- BSON
- Serialized
- Serialized-Gzip
- JSON
- JSON-Gzip
- JSON-Brotli

Storage formats can be changed flexibly at any time. The saving format only affects the speed of loading and writing the file. All other functions are independent of it. Each storage format has its pros and cons. 

An unlimited number of database instances can be created. You can only specify one storage path per database.

`__DIR__.'/path/db/'` results in `__DIR__.'/path/db/tabe_name/storage_files`

## Scheme

The database works only with arrays. There are no rules how a dataset must be structured. A table can store a wide variety of structures. However, it is recommended to use a uniform structure per table. For example, if a table is sorted by a value that is not contained in all records, it will be replaced by null. This can lead to unexpected results.

## Limitations

The limit of the database is only in your hardware. Depending on the performance and size of your hardware, the differences can be very extreme.
According to the adapter, a 50 MB table may need 500 MB RAM.
For larger amounts of data an alternative database (SQL, NoSQL) is recommended. 
A 50 MB table (PHP adapter) corresponds to about 200,000 records of a user profile

```JSON
{
     "md5": "2af5fc8db846a178fb2c974b746d1e16",
     "first_name": "Sasha",
     "last_name": "Tivolier",
     "email": "stivolier1@cam.ac.uk",
     "gender": "Female",
     "ip_address": "160.197.252.53",
     "status": false,
     "currency_code": "EUR"
   }
```
(Optimization follows)

## Functions
### Database & Table
##### Create DB
```PHP
$db = new FFDB(__DIR__.'/db/');
```
***
##### Create Table
```PHP
$db = new FFDB(__DIR__.'/db/');
$db->create('adaptor')->table('name');

//Sample
$db->create('php')->table('foo');
//or 
$foo = $db->create('php')->table('foo');
```

If you are not sure if the table already exists, you can always use create. If the table exists, it will be loaded and returned.
***
##### Get Table
This load the table from file to PHP
```PHP
$db = new FFDB(__DIR__.'/db/');
$db->table('name');

//Sample 
$foo = $db->table('foo');
```
***

##### Copy/Move/Delete/Merge Table
These functions are planned for the next version.

***
### Data

#### __id
ffDB creates a unique __id for each record. This __id is unique per database. Delete and Update always refer to the given __id.
At Insert this __id is created automatically. If a __id is already specified, it will be overwritten.
```PHP
$db = new FFDB(__DIR__.'/db/');
$foo = $db->table('foo');
$__id = $foo->insert(['foo' => 'bar']);
```
***
#### Get Data from Table

##### Get all data 
If you want all data without filters or sorting, you can directly retrieve the array. 
```PHP
$foo_data = $foo->data();
```
The result is an array with all data.

With `data(false)` you get the data object. This allows more features, but requires more understanding of how the database works. 
```PHP
//Instance of \Data
$foo_data = $foo->data(false);
```
The result is an object with all data.
***
#### Filter data 
Filter is one of the most important functions. Filter can be applied to any data object / table. 
```PHP
$foo_filter = $foo->filter();
```
The result is a \Filter with all data.
***
##### WHERE
To select data, `where()` can be used. `where()` always needs an operator. To execute the filter, use `get()`.
```PHP
$foo_filtered_data = $foo->filter()->where('first_name')->equal('Max')->get();
```
The list of all supported operators can be found below.
***
##### AND | OR
A \Rule is a collection from where, operator and optional logic [AND / OR]. A logic is just required if you wana use more than on where.
```PHP
$foo_filtered_data = 
$foo->filter()->
where('first_name')->equal('Max')->and()->
where('last_name')->equal('Lastname')
->get();
```
When using OR, it must be ensured that it is a real OR. The \Rule after OR is absolutely independent of all previous where()


```PHP
$foo_filtered_data = 
$foo->filter()->
where('first_name')->equal('Max')->and()->
where('last_name')->equal('Smith')->or()->
where('last_name')->equal('Foo')
->get();
```
All records are found that have Max Smith OR * Foo. If you want to have Max Smith OR Max Foo, it must be specified like this.
```PHP
$foo_filtered_data = 
$foo->filter()->
where('first_name')->equal('Max')->and()->
where('last_name')->equal('Smith')->or()->
where('first_name')->equal('Max')->and()->
where('last_name')->equal('Foo')
->get();
```
A Soft-OR is planned for the next versions. Similar `or(soft=false)`. Then the OR also refers to all previous hits.
***
##### GET
`get()` must be the last call.\
To get the raw result array, use `get(true)`. Otherwise a \Result object is returned.
```PHP
$result = $foo->filter()->where('first_name')->equal('Max')->get(true);
```
`get()` returns a \Result. \
`$result->num_rows` contains the number of records in \Result. \
`$result->stats` contains information that can help to optimize the queries. \
`$result->data` is a \Data object and contains the found records.
```PHP
$result = $foo->filter()->where('first_name')->equal('Max')->get();
```
With \Data it is possible to start a new filter based on the records from $result
```PHP
$result = $foo->filter()->where('first_name')->equal('Max')->get();
$foo_data = $result->filter()->where('age')->greaterOrEqual(18)->get(true);
```
***
#### Performance & Filter
The order of where() is very important for good performance.\
The statistics in \Result give you important information about how long the filtering took and how many rules had to be applied (and missed).
```PHP
$result = 
$foo->filter()-> //12.000 rows
where('account_status')->equal('active')->and()-> //10.000 rows
where('user_name')->equal('MadMax')->//result 1 row
get();
```
In this example we have 12,000 records. 10,000 of them have `account_status => active`. 
With the first \Rule we found 10.000 rows and put them in the "hits".\
With the second \Rule, we have to search again in 10.000 rows for user_name equal MadMax.

If MadMax is in the last row (latest entry) 12,000(total) + 10,000(status=active) rows must be compared. If you first search for user_name and then check the status, it is 12,000 + 1. So just under half.
```PHP
$result = 
$foo->filter()-> //12.000 rows
where('user_name')->equal('MadMax')->and()-> //found 1 row
where('account_status')->equal('active')-> //check if 1 row has account_status = active
get();
```
***
##### Limit
With `limit()` you can limit the number of records. `limit()` should always be used if you do not need all the data. This can lead to a much better performance.
```PHP
$result = 
$foo->filter()-> 
where('user_name')->equal('MadMax')->
limit(1)->get();
```
***
##### Skip
With `skip()` the first X found records are ignored.
```PHP
$result = 
$foo->filter()-> 
where('account_status')->equal('active')->
skip(5)->get();
```
***
##### Delete the \Result
`delete()` deletes all records found through the filter. `delete()` replaces `get()`.
```PHP
$result = 
$foo->filter()-> //12.000 rows
where('account_status')->equal('active')-> //10.000 rows
delete(); // delete 10.000 rows
```

```PHP
$result = 
$foo->filter()-> //12.000 rows
where('account_status')->equal('active')-> //10.000 rows
skip(10)->
delete(); // delete 9.990 rows and ignore the first 10 
```
```PHP
$result = 
$foo->filter()-> //12.000 rows
where('account_status')->equal('active')-> //10.000 rows
skip(10)->sort('__id')->order('DESC')->
delete(); // delete 9.990 rows and ignore the last 10 
```
***
##### Child for nested keys
`child()` allows to select a deeper key in multidimensional arrays. `child()` works ATM just with `where()`.
```PHP
$document = [
    'user' => 'Max',
    'tags' => [
        'private' => ['dev','php','mysql','js'],
        'public' => ['foo','bar'],
    'status' => 'active'
    ]  
];
$result = 
$foo->filter()-> 
where('tags')->child('private')->contains('php')->get(); 
```
The `child()` function has to be extended much better.\
It is not possible to search for unknown keys at the moment.
```PHP
$document = [
    'user' => 'Max',
    'orders' => [
        '0' => [
            'order_id' => 12345,
            'order_status' => 'payed',
            'products' => [
                '0' => [
                    ...
                ]
            ]
        ]
    ],
    'status' => 'active' 
];
//This is not working!
$result = 
$foo->filter()-> 
where('orders')->child('order_id')->equal('12345')->get(); 
```
***
## Operator
| Function | Operator | Working |
| --- | --- | --- |
| equal($key) | == | yes | 
| notEqual($key) | != | yes | 
| identical($key) | === | yes | 
| notIdentical($key) | !== | yes | 
| greater($key) | \> | yes | 
| greaterOrEqual($key) | \>= | yes | 
| less($key) | \< | yes | 
| lessOrEqual($key) | \<= | yes | 
| contains($key) | in_array() | yes | 
| notContains($key) | !in_array() | yes | 
| regex($regEx) | preg_match() | yes | 
| exists($key) | isset() | no | 
| notExists($key) | !isset() | no | 
| IsNull($key) | ($document[$key] === null) | no | 
| IsNotNull($key) | ($document[$key] !== null) | no | 
| IsEmpty($key) | empty($document[$key]) | no | 
| IsNotEmpty($key) | !empty($document[$key]) | no | 
| existsAndEmpty($key) | (isset($document[$key] && empty($document[$key])) | no | 
| existsAndNotEmpty($key) | (isset($document[$key] && !empty($document[$key])) | no |
***
## Storage Format Adaptor
It is really very easy to write your own adapter for your own storage format. The adapter can simply be put into the folder and is automatically loaded when needed.
#####Tested and working adapters 
- php
- serialize
- json


## ToDo
- [ ] Max entries per file
- [ ] Max size per file
- [ ] Reduce size of stored php adaptor files 
- [ ] `child()` for arrays with unknown keys  
- [ ] `child()` for `sort()` 
- [ ] More `unset()` for lower memory peak usage 
- [ ] Try php multithread for big data
- [ ] Delete tables
- [ ] Copy tables
- [ ] Move tables
- [ ] Merge tables
- [ ] Simplify change storage adaptor
- [ ] `update()` for \Result (update data in all found datasets)
- [ ] Creating "black hole" (a adaptor for inserts without loading the full file to php) for logs
- [ ] Creat cache controller
- [ ] Add APCu
- [ ] Add memcached
- [ ] Add file cache
- [ ] Add OPCache file cache
- [ ] Update `or()` to soft and hard break
- [ ] PHP Tests :/
