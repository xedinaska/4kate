<?



session_start();

//print_r($_POST);

require_once('../config.php');

// +++ ==================================================================================== 
// ���������� ������
require_once('../class_adm_general.php');
require_once('../class_adm_navigation.php');
require_once('../class_db.php');
require_once('../class_template.php');

// --- ====================================================================================

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
// ������ �������� ���������� ������
// � ����� ������� � ��� ����� �������� ����, � �� ����� ���������� ��������������� ���������������� ���� � ���������.
require_once('../config_data_'.$lng.'.php'); 

$action='';
if (isset($_GET['action'])) $action=$_GET['action'];
if (isset($_POST['action'])) $action=$_POST['action'];

$general = new general(); 

$page = new navigation($url, $lng);
$page_info = $page->get_page_info();

if ($page_info == FALSE)
 {
  // ���� ����� �������� ��� -- ���������� ����� �� �������� 404
  $general->log_error('Page not found!');
  die();
 }
 
$template = new template($page_info['template'], $lng);

$template->assign('lng', $lng); 
 
$template->assign('APPL_WEBDIR', $config['appl_webdir']);
$template->assign('appl_webdir', $config['appl_webdir']);
$template->assign('tpls_dir', $config['tpls_dir']);

$template->assign('url', $url);

$template->assign('page_name', $page_info['name']);
$template->assign('page_title', implode(' - ', $page_info['ttls']));

$handler_filename = $general->handlers_dir.$page_info['file'];

if (is_file($handler_filename))
 {
  include($handler_filename);
 }

$template->assign('menu', $page->get_menu($page_info['uid'], $page_info['uids']));

$template->process();

echo $template->get_final_result(TRUE, FALSE);
// --- ==================================================================================== 


?>