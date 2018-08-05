<?

include('../class_adm_news.php');
$news = new news();
$db = new db();

$rubric = 0;
if (isset($_GET['rubric'])) $rubric=$_GET['rubric'];
if (isset($_POST['rubric'])) $rubric=$_POST['rubric'];

$news_id = 0;
if (isset($_GET['news_id'])) $news_id=$_GET['news_id'];
if (isset($_POST['news_id'])) $news_id=$_POST['news_id'];

if ($action == 'order_go')
 {
  if (isset($_POST['ord'])) $ord=$_POST['ord']; else $ord=array();
  foreach($ord as $u => $v)
   {
    $db->query("UPDATE `news_rubrics` SET `nr_ord`='$v' where `nr_uid`='$u' limit 1");
   }
 }

if ($action == 'del_go')
 {
  $db->query("INSERT into `news_backup` SELECT * from `news` where `n_parent`='$rubric'");
  $db->query("DELETE from `news_rubrics` where `nr_uid`='$rubric' limit 1");
  $action='';
 } 

if ($action == 'news_del_go')
 {
  $db->query("INSERT into `news_backup` SELECT * from `news` where `n_uid`='$n_uid'");
  $db->query("DELETE from `news` where `n_uid`='$n_uid' limit 1");
  $action='';
 }  
 
if ($action == 'add')
 {
  $template->assign('content', $news->get_news_rubric_form($action));
 }
 
if ($action == 'news_add')
 {
  $template->assign('content', $news->get_news_form($action, $rubric));
 } 
 
if ($action == 'edit')
 {
  $r = $db->query("SELECT * from `news_rubrics` where `nr_uid`='$rubric'");
  $row = $r['res']->fetch_assoc();
  
  $template->assign('content', $news->get_news_rubric_form($action, '', $row['nr_uid'], $row['nr_name'], $row['nr_lng'], $row['nr_url'], $row['nr_ord']));
 } 
 
if ($action == 'add_go')
 {
  $nr_name=$db->real_escape_string(trim($_POST['nr_name']));
  $nr_url=$db->real_escape_string(trim($_POST['nr_url']));
  $nr_ord=$db->real_escape_string(trim($_POST['nr_ord']));
  $nr_lng=$db->real_escape_string(trim($_POST['nr_lng']));
  
  if ($nr_ord=='') $nr_ord=0;
  $r = $db->query("SELECT `nr_uid` from `news_rubrics` where `nr_lng`='$nr_lng' and `nr_url`='$nr_url'");
  
  if ($nr_name=='')
   {
    $template->assign('content', $news->get_news_rubric_form('add', 'У рубрики ДОЛЖНО быть имя', 0, $nr_name, $nr_lng, $nr_url, $nr_ord));
   }
    elseif (($r['num']>0)||($nr_url==''))
     {
      $template->assign('content', $news->get_news_rubric_form('add', 'URL рубрики не может дублироваться или быть пустым!', 0, $nr_name, $nr_lng, $nr_url, $nr_ord));
     }
      else
	   {
	    $db->query("INSERT into `news_rubrics` (`nr_name`, `nr_lng`, `nr_url`, `nr_ord`) VALUES ('$nr_name', '$nr_lng', '$nr_url', '$nr_ord')");
		$action = '';
	   }
  
 } 
 
if ($action == 'news_add_go')
 {
  $n_parent=$db->real_escape_string(trim($_POST['n_parent']));
  $n_dt=mktime($_POST['n_dt_hour'], $_POST['n_dt_min'], $_POST['n_dt_sec'], $_POST['n_dt_month'], $_POST['n_dt_day'], $_POST['n_dt_year']);
  if ((isset($_POST['n_show']))&&($_POST['n_show']=='on')) $n_show='Y'; else $n_show='N';
  $n_title=$db->real_escape_string(trim($_POST['n_title']));
  $n_author=$db->real_escape_string(trim($_POST['n_author']));
  $n_annotation=$db->real_escape_string(trim($_POST['n_annotation']));
  $n_text=$db->real_escape_string(trim($_POST['n_text']));
  $n_lng=$lng;
  
  if ($n_title=='')
   {
    $template->assign('content', $news->get_news_form('news_add', $rubric, 'У новости ДОЛЖЕН быть заголовок', 0, $n_parent, $n_dt, $n_show, $n_title, $n_author, $n_annotation, $n_text));
   }
    else
	 {
	  $db->query("INSERT into `news` (`n_parent`, `n_lng`, `n_dt`, `n_show`, `n_title`, `n_author`, `n_annotation`, `n_text`) VALUES ('$n_parent', '$n_lng', '$n_dt', '$n_show', '$n_title', '$n_author', '$n_annotation', '$n_text')");
	  $action = '';
	 }
  
 }  
 
if ($action == 'edit_go')
 {
  $nr_uid=$db->real_escape_string(trim($_POST['nr_uid']));
  $nr_name=$db->real_escape_string(trim($_POST['nr_name']));
  $nr_url=$db->real_escape_string(trim($_POST['nr_url']));
  $nr_ord=$db->real_escape_string(trim($_POST['nr_ord']));
  $nr_lng=$db->real_escape_string(trim($_POST['nr_lng']));
  
  if ($nr_ord=='') $nr_ord=0;
  $r = $db->query("SELECT `nr_uid` from `news_rubrics` where `nr_lng`='$nr_lng' and `nr_url`='$nr_url' and `nr_uid`!='$nr_uid'");
  
  if ($nr_name=='')
   {
    $template->assign('content', $news->get_news_rubric_form('edit', 'У рубрики ДОЛЖНО быть имя', $nr_uid, $nr_name, $nr_lng, $nr_url, $nr_ord));
   }
    elseif (($r['num']>0)||($nr_url==''))
     {
      $template->assign('content', $news->get_news_rubric_form('edit', 'URL рубрики не может дублироваться или быть пустым!', $nr_uid, $nr_name, $nr_lng, $nr_url, $nr_ord));
     }
      else
	   {
	    $db->query("UPDATE `news_rubrics` SET `nr_name`='$nr_name', `nr_lng`='$nr_lng', `nr_url`='$nr_url', `nr_ord`='$nr_ord' where `nr_uid`='$nr_uid' limit 1");
		$action = '';
	   }
  
 }  
 
if (($action == '')&&($rubric==0))
 {
  $template->assign('content', $news->get_adm_news_rubrics());
 }
  elseif (($action == '')&&($rubric!=0))
   {
    $template->assign('content', $news->get_adm_news_list($rubric));
   }


?>