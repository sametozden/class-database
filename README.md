# MySQLi and PDO database class

You can make any sql queries with this class.

## Installation

No needs any installation. Only put this file in your folder on server.


## Configuration

```php
require("mysqli-class.php");
#or
require("pdo-class.php");

define("DB_HOST", 'localhost');
define("DB_NAME", 'example_db');
define("DB_USER", 'db_user');
define("DB_PASSWORD", 'db_password');

$connect = new sql(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, "Database connection failed");

$connect->set_cachefolder("my-cache-folder"); // cache html file will save to in this folder, recommend use unique name
$connect->set_cachestatus(true); // true = cache open, false = cache close
$connect->set_cachetime(60); // cache files will save 60 minutes in cache folder

```






## Insert Record

| Parameters  | Type | Description |
| ------------- | ------------- | ------------- |
| table name  | string  | your table name |
| fieldname and data | array | values add to the table |
| warning message | string | if error occurs you will get the this message |
| debug | boolean | full sql queries print to the screen in the display=none div |


```php

$stack = array("fieldname" => "value", "fieldname2" => "null", "fieldname3" => time());
$insert = $connect->insert("yourtable", $stack, "error occured", false);

// return values
$insert[0] // true or false
$insert[1] // if data can't add to the table (false), then you will show the 'warning message' here

```




## Update Record

| Parameters  | Type | Description |
| ------------- | ------------- | ------------- |
| table name  | string  | your table name |
| fieldname and data | array | values add to the table |
| additional query | string | optional query |
| warning message | string | if error occurs you will get the this message |
| debug | boolean | full sql queries print to the screen in the display=none div |


```php

$stack = array("fieldname" => "value", "fieldname2" => "null", "fieldname3" => time());
$update = $connect->update("yourtable", $stack, " where id='5' and age < 18 ", "error occured", false);

// return values
$update[0] // true or false
$update[1] // if data can't update to the table (false), then you will show the 'warning message' here

```


## Delete Record

| Parameters  | Type | Description |
| ------------- | ------------- | ------------- |
| table name  | string  | your table name |
| additional query | string | optional query |
| warning message | string | if error occurs you will get the this message |
| debug | boolean | full sql queries print to the screen in the display=none div |


```php

$delete = $connect->delete("yourtable", " where id='7' ", "error occured", false);

// return values
$delete[0] // true or false
$delete[1] // if data can't delete from the table (false), then you will show the 'warning message' here

```


## Read Single Row

### Select

| Parameters  | Type | Description |
| ------------- | ------------- | ------------- |
| table name  | string  | your table name |
| field name | string | what your want get to field name in the table |
| query | string | query (like where clause) |
| warning message | string | if error occurs you will get the this message |
| debug | boolean | full sql queries print to the screen in the display=none div |


### Read

| Parameters  | Type | Description |
| ------------- | ------------- | ------------- |
| $select variable  | variable  | you should sending the $select variable |
| table name | boolean | if you want get result with table name set to 'true' |

```php

$select = $connect->select("yourtable", "name,age" , " where id='5' ", "error occured", false);
$readrow = $connect->read($select, false);

// return values
$readrow[0] // true
$readrow[1] // array $readrow[1]['name'] = "John" , $readrow[1]['age'] = "23" 

// if you set the second parameter of read method to true, you will get similar result: $readrow[1]['yourtable']['name'] = "John" , $readrow[1]['yourtable']['age'] = "23"

```



## Read Multiple Rows

Only different from 'single row', you will get the results like array in the $readrow[1] value.

```php

$select = $connect->select("yourtable", "name,age" , " where id='5' ", "error occured", false);
$readrows = $connect->readall($select, false);

foreach($readrows as $values){
	echo $values['age']."---".$values['age']."<br>";
}

// return values
$readrows[0] // true
$readrows[1] 

// array $readrows[1][0]['name'] = "John" , $readrows[1][0]['age'] = "23" 
// array $readrows[1][1]['name'] = "Marco" , $readrows[1][1]['age'] = "18" 
// array $readrows[1][2]['name'] = "Reus" , $readrows[1][2]['age'] = "19" 


```




## Cache System

Cache method improve your system performance. If you should open the cache status open with $connect->set_cachestatus(true) parameter, you can use cache method.


| Parameters  | Type | Description |
| ------------- | ------------- | ------------- |
| table name  | string  | your table name |
| fieldname and data | array | values add to the table |
| additional query | string | optional query |
| warning message | string | if error occurs you will get the this message |
| debug | boolean | full sql queries print to the screen in the display=none div |
| read type | "read" or "readall" | You can choosing read type (single or multiple rows) |
| table name | boolean | if you want get result with table name set to 'true' |
| file name | string | You can define your cache file name. example: db-[filename]-[md5 hash].html |
| time | integer | if you set this parameter, this value override to global $connect->set_cachetime() value | 


### Requests

System check your in cache folder, if doesn't exist this queries cache, first time system will going to sql server and will create a html file in cache folder. When getting next queries won't going to the sql server.

```php

$selectcache = $connect->cache("yourtable", "name,age" , " where id='5' ", "error occured", false, "readall", "product_detail", "90");
$readcache = $connect->readall($selectcache, false);

// we are overriding global value. this query will cache 90 minutes
// this queries name will be like this:  db-product_detail-[md5 hashs of query].html

// return values
$readcache[0] // true
$readcache[1] 

// array $readcache[1][0]['name'] = "John" , $readcache[1][0]['age'] = "23" 
// array $readcache[1][1]['name'] = "Marco" , $readcache[1][1]['age'] = "18" 
// array $readcache[1][2]['name'] = "Reus" , $readcache[1][2]['age'] = "19"

```

## Get Rows Count

```php

$select = $connect->select("yourtable", "name,age" , " where id='5' ", "error occured", false);
$rowscount = $connect->rows_count($select);

echo $rowscount;  // for example: 45 (integer)



```

## License
[MIT](https://choosealicense.com/licenses/mit/)


