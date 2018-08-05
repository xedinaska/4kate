<?

$search = new search();
$template->assign('search_form', $search->get_search_form());
$template->assign('pagination', '');
$template->assign('search_results', '');

if (!isset($db))
{
 $db = new db();
}

if (count($_POST)!=0)
 {
  $res = $search->get_search_results($db->real_escape_string($search->get_search_request()));
  $template->assign('search_results', $res['text']);
  $template->assign('pagination', $general->pagination($page_num, $res['count']));
  $sr = $search->get_sr();
  if ($sr!==FALSE)
   {
    $_SESSION['sr']=$sr;
   }
  if ($res['count']==0) 
   {
    $template->assign('search_results', $general->get_db_config('no_search_results'));  
	$template->assign('pagination', '');
   }  
 }

if ((count($_POST)==0)&&(isset($_SESSION['sr'])))
 {
  $res = $search->get_search_results($db->real_escape_string($_SESSION['sr']), $page_num);
  $template->assign('search_results', $res['text']);
  $template->assign('pagination', $general->pagination($page_num, $res['count']));
  $sr = $search->get_sr();
  if ($res['count']==0) 
   {
    $template->assign('search_results', $general->get_db_config('no_search_results'));  
	$template->assign('pagination', '');
   }  
 } 



?>