<?
session_start();

/*
�������� ������ ������:
1. ����� ������ (��� ���� ������� � ����� ������ ������ �������, ����� ��������� ��������, ����� ��-�� ������ ���� �� �� �� ����
   � �������� ����� ������ �� ������ ����������).
2. ����������� ������ ����������������� ����� � ��������� �����������.
3. ��������, �� �������� �� "������� ����". ���� �� -- ������, ������� ��� ����� � ���������� ������ �������.
4. ���������� ������.
5. ������ URL'� � ����� ����������� �����, ����������� �������� �����, ������� �������, ������, �������� ���������� � �.�.
6. ���������� ������ ��� ������ ��������:
   �) ����������� ����������������� �����, ����������� ����������, ��������� �� ���������� ������ �����.
   �) ������ ���������� �������, ������� ������� ������������ ��� ��������� ����� ��������.
   �) �������� �� ������ ������ navigation "������������� ����������" �� ��������� ������ ��������.
7. ���������� ���������� ��������. ���� ���. ���� �� ����������, include'�� ���. ��� ������������ ����������, ����������� ��� ������
   ���������� ��������.
8. ������ ������ �������.
9. ����� ������� ��������.
*/


$t1 = microtime(TRUE);

// ���������� �������� ���������������� ���� � �������� � ��, ������ � �.�.
require_once('config.php');

$url = '';

// +++ ====================================================================================
// ������ ������� �� ������ "�������� �����"
if (isset($_GET['url']))
 {
  // ������� �� URL'� ��� "������" (������������) �������
  $url = preg_replace("/[^a-z\d_\-\/\.]/", "", $_GET['url']); 
  
  // ���������� ���������� ������������ �����
  $extension = pathinfo($url, PATHINFO_EXTENSION);
  
  // ���� ��� ���������� ����������, ��:
  if (in_array($extension, $config['allowed_extension']))
   {
    // ���������� ������ ��� �����
	$full_file_name = str_replace('[DOCROOT]', $_SERVER['DOCUMENT_ROOT'], $config['appl_dir']).str_replace('..', '_', $url);
	
	// ���� ����� ���� ����������, ��:
	if (is_file($full_file_name))
	 {
	  
	  // ������� ��� � �������� �����
	  $fd = fopen($full_file_name, 'rb');
	  fpassthru($fd);
	  fclose($fd);
	  die();
	 }
   }
 }
// --- ====================================================================================

// +++ ====================================================================================
// ����������� (������ �� ����)
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
// ���������� ������
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
// ������ ���������� ����� � ���������� ������������� URL'�

// ���� �� ���������
$lng = $config['languages']['default'];

// �������� ���������� ����, ��������� � ����� � ���������� ��������
$browser_lng = preg_split("/,|;/", @$_SERVER['HTTP_ACCEPT_LANGUAGE'], -1, PREG_SPLIT_NO_EMPTY);
foreach ($browser_lng as $b_lng)
{
 // ���� �� ���������� ����� ����������� ������ � �������� ������ ����, ����������� � ��������� � ��� �� �����, ���������� ���.
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
// ������ ���������� ���������������: �������, �������� � �.�.

// ��������
$page_num = 1;
// ���������, ������������ �� � URL'� �������� �� ��, ��� ���� �������� �����-�� �������� � ������ �������
if (preg_match("/\/?page(\d+)$/", $url, $arr))
 {
  $page_num = $arr[1];
  
  // ������� �� URL'� ������� ������ ��������� ��������
  $url = preg_replace("/\/?page(\d+)$/", "", $url);
 }

// ������
// �� ��������� �������, ��� ���� �� ������ �� �������
$bannerclick = 0;

// ���������, �������� �� URL ������� ����� �� ������
if (preg_match("/\/?bannerclick(\d+)$/", $url, $arr)) 
 {
  // ���� ��, ��:
  // ���������� ������������� ������, �� �������� ������� ����
  $bannerclick = $arr[1];
  
  // ������� �� URL'� ������� ����� �� ������
  $url = preg_replace("/\/?bannerclick(\d+)$/", "", $url);
  
  // ���� � ���������� ����������� � URL'�� �� ���� ����� ������ ������, ������� URL == main
  if ($url == '')
   {
    $url = 'main';
   } 
 }
 
// ������� 
$news_rubric_uid = 0;
$news_rubric_alias = '';
$news_uid = 0;
$news_tag_url = '';
$rss_mode = FALSE;

// ���������, �������� �� URL ��������� ������� �������� �/��� ������������� �������
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
      // ���� ��, ��:
      // ���������� ��������� ������� ��������
      $news_rubric_alias = $arr[1];
  
      // ������� �� URL'� ������� ����� �� ������� ��������
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
// ������ �������� ���������� ������
// � ����� ������� � ��� ����� �������� ����, � �� ����� ���������� ��������������� ���������������� ���� � ���������.
require_once('config_data_'.$lng.'.php'); 


$general = new general(); 

$page = new navigation($url, $lng);
$page_info = $page->get_page_info();


if ($page_info == FALSE)
 {
  // ���� ����� �������� ��� -- ���������� ����� �� �������� 404
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
// ����������� (������ � ���)
/*if ($page_info['cache']=='Y')
 {
  file_put_contents($full_cache_name, $res);
 }
*/
// --- ==================================================================================== 
 
?>