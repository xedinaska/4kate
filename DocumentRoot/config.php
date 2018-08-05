<?

$config['tpls_dir'] = 'tpls/';
$config['adm_tpls_dir'] = 'admin/tpls/';
$config['appl_dir'] = '[DOCROOT]/';
$config['appl_webdir'] = '/';
$config['handlers_dir'] = 'handlers/';
$config['adm_handlers_dir'] = 'admin/handlers/';

$config['languages']['default'] = 'ru';
$config['languages']['ru'] = array ('short' => 'РУ', 'long' => 'Русский');
$config['languages']['en'] = array ('short' => 'EN', 'long' => 'English');

$config['allowed_extension'] = array('css', 'jpg', 'jpeg', 'gif', 'png', 'bmp', 'zip', 'rar', 'doc', 'xls', 'ppt', 'avi', 'mp3', 'swf', 'flv', 'htm', 'html', 'txt', 'js');

define('db_login', 'testuser');
define('db_password', 'testpassword');
define('db_host', 'mariadb');
define('db_database', 'testdb');



?>