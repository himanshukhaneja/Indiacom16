<?php
/**
 * Created by PhpStorm.
 * User: Saurav
 * Date: 4/22/15
 * Time: 7:54 PM
 */

/*if ( ! defined('BASEPATH')) exit('No direct script access allowed');*/
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|				 NOTE: For MySQL and MySQLi databases, this setting is only used
| 				 as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7
|				 (and in table creation queries made with DB Forge).
| 				 There is an incompatibility in PHP with mysql_real_escape_string() which
| 				 can make your site vulnerable to SQL injection if you are using a
| 				 multi-byte character set and are running versions lower than these.
| 				 Sites using Latin-1 or UTF-8 database character set and collation are unaffected.
|	['swap_pre'] A default table prefix that should be swapped with the dbprefix
|	['autoinit'] Whether or not to automatically initialize the database.
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the 'default' group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class
*/

$active_group = DBGROUP;
$active_record = TRUE;

$db['default']['hostname'] = 'localhost';
$db['default']['username'] = 'root';
$db['default']['password'] = '';
$db['default']['database'] = 'indiacom';
$db['default']['dbdriver'] = 'mysql';
$db['default']['dbprefix'] = '';
$db['default']['pconnect'] = TRUE;
$db['default']['db_debug'] = FALSE;
$db['default']['cache_on'] = FALSE;
$db['default']['cachedir'] = '';
$db['default']['char_set'] = 'utf8';
$db['default']['dbcollat'] = 'utf8_general_ci';
$db['default']['swap_pre'] = '';
$db['default']['autoinit'] = TRUE;
$db['default']['stricton'] = FALSE;

$db['OnlineTest']['hostname'] = '160.153.55.227';
$db['OnlineTest']['username'] = 'rootWebsite';
$db['OnlineTest']['password'] = 'CPAcc#4012';
$db['OnlineTest']['database'] = 'indiacom';
$db['OnlineTest']['dbdriver'] = 'mysql';
$db['OnlineTest']['dbprefix'] = '';
$db['OnlineTest']['pconnect'] = FALSE;
$db['OnlineTest']['db_debug'] = FALSE;
$db['OnlineTest']['cache_on'] = FALSE;
$db['OnlineTest']['cachedir'] = '';
$db['OnlineTest']['char_set'] = 'utf8';
$db['OnlineTest']['dbcollat'] = 'utf8_general_ci';
$db['OnlineTest']['swap_pre'] = '';
$db['OnlineTest']['autoinit'] = TRUE;
$db['OnlineTest']['stricton'] = FALSE;

$dbconfig['hostname'] = $db[$active_group]['hostname'];
$dbconfig['username'] = $db[$active_group]['username'];
$dbconfig['password'] = $db[$active_group]['password'];
$dbconfig['database'] = $db[$active_group]['database'];
$dbconfig['dbdriver'] = $db[$active_group]['dbdriver'];
$dbconfig['dbprefix'] = $db[$active_group]['dbprefix'];
$dbconfig['pconnect'] = $db[$active_group]['pconnect'];
$dbconfig['db_debug'] = $db[$active_group]['db_debug'];
$dbconfig['cache_on'] = $db[$active_group]['cache_on'];
$dbconfig['cachedir'] = $db[$active_group]['cachedir'];
$dbconfig['char_set'] = $db[$active_group]['char_set'];
$dbconfig['dbcollat'] = $db[$active_group]['dbcollat'];
$dbconfig['swap_pre'] = $db[$active_group]['swap_pre'];
$dbconfig['autoinit'] = $db[$active_group]['autoinit'];
$dbconfig['stricton'] = $db[$active_group]['stricton'];

/* End of file database.php */
/* Location: ./application/config/database.php */