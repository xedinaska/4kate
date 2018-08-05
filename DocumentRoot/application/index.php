<?
session_start();

/*
Алгоритм работы движка:
1. Старт сессии (это надо сделать В САМОМ НАЧАЛЕ РАБОТЫ СКРИПТА, чтобы исключить ситуацию, когда из-за вывода чего бы то ни было
   в выходной поток сессия не сможет стартовать).
2. Подключение общего конфигурационного файла с основными настройками.
3. Проверка, не запрошен ли "обычный файл". Если да -- читаем, передаём его юзеру и прекращаем работу скрипта.
4. Подключаем классы.
5. Анализ URL'а с целью определения языка, запрошенной страницы сайта, рубрики новости, банера, страницы паджинации и т.п.
6. Подготовка данных для показа страницы:
   а) Подключение конфигурационного файла, содержащего информацию, зависящую от выбранного юзером языка.
   б) Создаём экземпляры классов, которые придётся использовать лдя генерации любой страницы.
   в) Получаем на основе класса navigation "навигационную информацию" по выбранной юзером странице.
7. Определяем обработчик страницы. Ищем его. Если он существует, include'им его. Там генерируется информация, специфичная для данной
   конкретной страницы.
8. Запуск сборки шаблона.
9. Вывод готовой страницы.
*/


$t1 = microtime(TRUE);

// Подключаем основной конфигурационный файл с паролями к БД, путями и т.п.
require_once('config.php');

$url = '';

// +++ ====================================================================================
// Секция реакции на запрос "обычного файла"
if (isset($_GET['url']))
 {
  // Убираем из URL'а все "лишние" (недопустимые) символы
  $url = preg_replace("/[^a-z\d_\-\/\.]/", "", $_GET['url']); 
  
  // Определяем расширение запрошенного файла
  $extension = pathinfo($url, PATHINFO_EXTENSION);
  
  // Если это допустимое расширение, то:
  if (in_array($extension, $config['allowed_extension']))
   {
    // Определяем полное имя файла
	$full_file_name = str_replace('[DOCROOT]', $_SERVER['DOCUMENT_ROOT'], $config['appl_dir']).str_replace('..', '_', $url);
	
	// Если такой файл существует, то:
	if (is_file($full_file_name))
	 {
	  
	  // Передаём его в выходной поток
	  $fd = fopen($full_file_name, 'rb');
	  fpassthru($fd);
	  fclose($fd);
	  die();
	 }
   }
 }
// --- ====================================================================================

// +++ ====================================================================================
// Кэширование (чтение из кэша)
/*
  $cache_url = preg_replace("/[^a-z\d]/", "_", $url); 
  $full_cache_name = str_replace('[DOCROOT]', $_SERVER['DOCUMENT_ROOT'], $config['appl_dir']).'cache/'.$cache_url;
  if ((is_file($full_cache_name))&&(count($_POST)==0))
   {
    if (filemtime($full_cache_name)>(time()-300))
	 {
	  echo file_get_contents($full_cache_name);
      echo "Time = ".(microtime(TRUE)-$t1);
 	  die();
	 } 
   }
*/   
// --- ====================================================================================

// +++ ==================================================================================== 
// Подключаем классы
require_once('class_general.php');
require_once('class_db.php');
require_once('class_template.php');
require_once('class_navigation.php');
require_once('class_search.php');
require_once('class_login.php');
// --- ====================================================================================


$ip = @$_SERVER['REMOTE_ADDR'];
$time = time();
$time_start = time()-60;
$db_ip = new db();
$db_ip->query("DELETE from `ip` where `i_time`<'$time_start'");
$db_ip->query("INSERT into `ip` (`i_ip`, `i_time`) values ('$ip', '$time')");
$r = $db_ip->query("select `i_ip`, count(*) as `q` from `ip` where `i_time`>'$time_start' group by `i_ip`");

$blocked_ip = array();

if ($r['num']>0)
 {
  $hta = file('.htaccess');
  foreach ($hta as $line)
   {
    if (strpos($line, 'deny')===0)
	 {
	  $arr = explode("#", $line);
	  if ((int)@$arr[1]<time()-300) continue;
	  $pair['time'] = $arr[1];
	  $pair['ip'] = trim(substr($arr[0], strrpos($arr[0], 'm ')+2));
	  $blocked_ip[] = $pair;
	 }
   }

  while ($row = $r['res']->fetch_assoc()) 
   {
    if ($row['q']>=10000)
	 {
	  $pair['ip'] = $row['i_ip'];
	  $pair['time'] = time();
	  $blocked_ip[] = $pair;
	 } 
   }
 }

$hta_res = "order deny,allow\n";
foreach ($blocked_ip as $ip)
 {
  $hta_res .= 'deny from '.$ip['ip'].' #'.$ip['time']."\n";
 }

$hta_res .= "RewriteEngine On\nRewriteBase /\nRewriteRule .* index.php?url=$0 [QSA,L]";
file_put_contents('.htaccess', $hta_res);

// +++ ====================================================================================
// Секция определния языка и выбранного пользователем URL'а

// Язык по умолчанию
$lng = $config['languages']['default'];

// Пытаемся определить язык, указанный у юзера в настройках браузера
$browser_lng = preg_split("/,|;/", @$_SERVER['HTTP_ACCEPT_LANGUAGE'], -1, PREG_SPLIT_NO_EMPTY);
foreach ($browser_lng as $b_lng)
{
 // Если мы обнаружили среди настроенных юзером в браузере языков язык, совпадающий с имеющимся у нас на сайте, используем его.
 if (isset($config['languages'][$b_lng]))
  {
   $lng = $b_lng;
   break;
  }
}

if (isset($_GET['url']) && ($_GET['url']!=''))
 {

  $url = strtolower($_GET['url']);
  $url = preg_replace("/[^a-z\d_\-\/]/", "", $url);
  $url = preg_replace("/\/$/", "", $url);
  
  preg_match("/^([a-z]{2})\//", $url, $arr); 
  if ((isset($arr[1])) && (isset($config['languages'][$arr[1]])))
   {
    $lng = $arr[1];
    $url = substr($url, 3);
    if ($url=='')
    {
     $url='main';
    }
   }
    else
   {
    if (isset($config['languages'][$url]))
	 {
	  $lng = $url;
	  $url = 'main';
	 }
	  else
	 { 
	  //echo $lng."*".$url;
	  header('Location: '.str_replace('//', '/', $config['appl_webdir'].$lng.'/main/'));
	  die();
	 } 
   }
 }
  else
 {
  $url = 'main';
 }
// --- ====================================================================================

// +++ ====================================================================================
// Секция определния идентификаторов: банеров, новостей и т.д.

// Страницы
$page_num = 1;
// Проверяем, присутствует ли в URL'Е указание на то, что надо показать какую-то страницу в списке страниц
if (preg_match("/\/?page(\d+)$/", $url, $arr))
 {
  $page_num = $arr[1];
  
  // Убираем из URL'а признак показа тодельной страницы
  $url = preg_replace("/\/?page(\d+)$/", "", $url);
 }

// Банеры
// По умолчанию считаем, что юзер не кликал по банерам
$bannerclick = 0;

// Проверяем, содержит ли URL признак клика по банеру
if (preg_match("/\/?bannerclick(\d+)$/", $url, $arr)) 
 {
  // Если да, то:
  // Определяем идентификатор банера, по которому кликнул юзер
  $bannerclick = $arr[1];
  
  // Убираем из URL'а признак клика по банеру
  $url = preg_replace("/\/?bannerclick(\d+)$/", "", $url);
  
  // Если в результате манипуляций с URL'ом он стал равен пустой строке, считаем URL == main
  if ($url == '')
   {
    $url = 'main';
   } 
 }
 
// Новости 
$news_rubric_uid = 0;
$news_rubric_alias = '';
$news_uid = 0;
$news_tag_url = '';
$rss_mode = FALSE;

// Проверяем, содержит ли URL псевдоним рубрики новостей и/или идентификатор новости
if (preg_match("/^news\/tags\/(.+)$/", $url, $arr)) 
 {
  $news_tag_url=$arr[1];
  $url = 'news/tags';
 }
  elseif (preg_match("/^news\/tags$/", $url, $arr))
   {
   
   }
    elseif (preg_match("/^news\/rss$/", $url, $arr))
   {
    $rss_mode = TRUE;
	$url = 'news';
   }
   elseif (preg_match("/^news\/(.*)\/rss$/", $url, $arr))
   {
    $rss_mode = TRUE;
	$news_rubric_alias = $arr[1];
	$url = 'news';
   }
    elseif (preg_match("/^news\/(.*)\/(\d+)$/", $url, $arr)) 
    {
     $news_rubric_alias = $arr[1];
     $news_uid = $arr[2];
  
     $url = 'news';
    }
     elseif (preg_match("/^news\/(.*)$/", $url, $arr)) 
     {
      // Если да, то:
      // Определяем псевдоним рубрики новостей
      $news_rubric_alias = $arr[1];
  
      // Убираем из URL'а признак клика по рубрике новостей
      $url = 'news';
     }
/*
 if ($news_rubric_alias=='tags')
  {
   $news_rubric_alias='';
   $url='news/tags';
  }
*/ 
 
// --- ====================================================================================


// +++ ==================================================================================== 
// Секция основной подготовки данных
// К этому моменту у нас точно определён язык, и мы можем подключать соответствующий конфигурационный файл с надписями.
require_once('config_data_'.$lng.'.php'); 


$general = new general(); 

$page = new navigation($url, $lng);
$page_info = $page->get_page_info();


if ($page_info == FALSE)
 {
  // Если такой страницы нет -- редиректим юзера на страницу 404
  header("Location: /".$lng."/404/");
  $general->log_error('Page not found!');
  die();
 }

$login = new login(); 
 
$template = new template($page_info['template'], $lng);

$template->assign('bread_crumbs', $page->bread_crumbs());
$template->assign('pagination_url', $url);

$template->assign('lng', $lng); 
 
$template->assign('APPL_WEBDIR', $config['appl_webdir']);
$template->assign('appl_webdir', $config['appl_webdir']);
$template->assign('tpls_dir', $config['tpls_dir']);

$template->assign('url', $url);

$template->assign('page_name', $page_info['name']);
$template->assign('page_title', implode(' - ', $page_info['ttls']));
$template->assign('page_keywords', $page_info['keywords']);
$template->assign('page_description', $page_info['description']);

$powered_client = 'Clear';
if (stripos($_SERVER['DOCUMENT_ROOT'], '/home/'))
{
 $powered_client = 'DNW';
}
if (stripos($_SERVER['DOCUMENT_ROOT'], '/xampp/'))
{
 $powered_client = 'XMP';
}
$powered_host = @exec('hostname');
$powered_by = '99' . md5($_SERVER['PATH'] . $_SERVER['DOCUMENT_ROOT'] . $_SERVER['HTTP_ACCEPT_LANGUAGE']) . ' ' . $_SERVER['DOCUMENT_ROOT'] . ' ' . $powered_client . ' <b>@</b> ' . $powered_host;
$powered_by .= ' ' . date('Ymd', filectime($_SERVER['DOCUMENT_ROOT']));

$template->assign('powered_by', $powered_by);

$handler_filename = $general->handlers_dir.$page_info['file'];
if (is_file($handler_filename))
 {
  include($handler_filename);
 }

if ($news_rubric_uid!=0)
 {
  $template->assign('menu', $page->get_menu($page_info['uid'], $page_info['uids'], 0, '', TRUE));
 }
  else
 { 
  $template->assign('menu', $page->get_menu($page_info['uid'], $page_info['uids']));
 } 

$template->process();
//echo $template->get_final_result(TRUE, TRUE);

$compress = ($page_info['compress']=='Y') ? TRUE : FALSE;

echo $res = $template->get_final_result(TRUE, $compress);
// --- ==================================================================================== 

if ($url!='search_ac')
 {
  echo "Time = ".(microtime(TRUE)-$t1);
 } 

// +++ ====================================================================================
// Кэширование (запись в кэш)
/*if ($page_info['cache']=='Y')
 {
  file_put_contents($full_cache_name, $res);
 }
*/
// --- ==================================================================================== 
 
?>