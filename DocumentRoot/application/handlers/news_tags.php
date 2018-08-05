<?

require_once('class_news.php');

// Указываем, каким методом класса navigation строить список подстраниц для страницы "Новости"
$special_submenu_method = 'news_subpages';

// Создаём экземпляр класса, отвечающего за новости
$news = new news();
if ($news_tag_url=='')
 {
  $template->assign('page_text', $news->tags_cloud());
 }
  else
 {
  $db = new db();
  $r = $db->query("SELECT `nk_name` from `news_keywords` where `nk_url`='$news_tag_url'");
  if ($r['num']==0)
    {
     header("Location: /".$this->lng."/404/");
     parent::log_error('Page not found!');
     die();
    }
  $row_tmp = $r['res']->fetch_assoc();
  $tag_name = $row_tmp['nk_name'];
  
  $template->assign('page_name', $page_info['name'].' - '.$tag_name);
  $template->assign('page_title', implode(' - ', $page_info['ttls']).' - '.$tag_name); 
  $template->subst_tpl('news.tpl');
  $template->assign('pagination_url', 'news/tags/'.$news_tag_url);
  $news_list = $news->get_news_list($page_num, 0, '', $news_tag_url);
  $template->assign('news_list', $news_list['text']);
  $template->assign('pagination', $general->pagination($page_num, $news_list['count']));
 }  
 
?>