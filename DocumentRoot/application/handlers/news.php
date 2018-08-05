<?

require_once('class_news.php');

// ���������, ����� ������� ������ navigation ������� ������ ���������� ��� �������� "�������"
$special_submenu_method = 'news_subpages';

// ������ ��������� ������, ����������� �� �������
$news = new news();

// ������ ��������� ������ ��
if (!isset($db))
 {
  $db = new db();
 }

if ($rss_mode == TRUE)
 {
  $template->subst_tpl('news_rss.tpl');
  $template->assign('rss_date', date('r'));
  if ($news_rubric_alias!='')
   {
    // �������� ���������� � ��������� �������: ������������� � ���
	$r = $db->query("SELECT `nr_uid`, `nr_name` from `news_rubrics` where `nr_lng`='$lng' AND `nr_url`='$news_rubric_alias'");
	if ($r['num']==1)
	 {
          $row_tmp = $r['res']->fetch_assoc();
	  $news_rubric_uid=$row_tmp['nr_uid'];
	  $news_rubric_name=$row_tmp['nr_name'];
	 }
	$template->assign('rss_items', $news->get_news_list_rss($news_rubric_uid, $news_rubric_alias, $news_rubric_name));
	$template->assign('rss_url', 'news/'.$news_rubric_alias.'/rss');
   }
    else
   {
    $template->assign('rss_items', $news->get_news_list_rss());
	$template->assign('rss_url', 'news/rss');
   }	
 }
  else
 {
	  // ���� ���� ������ ������� ��������
	  if ($news_rubric_alias!='')
	   {
		// �������� ���������� � ��������� �������: ������������� � ���
		$r = $db->query("SELECT `nr_uid`, `nr_name` from `news_rubrics` where `nr_lng`='$lng' AND `nr_url`='$news_rubric_alias'");
		if ($r['num']==1)
		 {
                  $row_tmp = $r['res']->fetch_assoc();
		  $news_rubric_uid=$row_tmp['nr_uid'];
		  $news_rubric_name=$row_tmp['nr_name'];
		 }
	 
		// ���� ���� �� ������ ���������� �������
		if ($news_uid==0)
		 {
		  // ������ ������� ������, � ������� �������� ���� �������� "�������"
		  $template->assign('bread_crumbs', $page->bread_crumbs(0));
	  
		  // ������ ������ �������� + �������� ���������� ������� ��� ����������
		  $news_list = $news->get_news_list($page_num, $news_rubric_uid, $news_rubric_alias);
		  $template->assign('news_list', $news_list['text']);
	
		// ������ ����������
		$template->assign('pagination', $general->pagination($page_num, $news_list['count']));
		$template->assign('pagination_url', $url.'/'.$news_rubric_alias);
		
		// ��������� ������� �� �������� ������ (� ������ ����� ������� ��������)
		$template->assign('page_name', $page_info['name'].' - '.$news_rubric_name);
		$template->assign('page_title', implode(' - ', $page_info['ttls']).' - '.$news_rubric_name); 
		$template->assign('rss_title', $news_rubric_name);
		$template->assign('rss_url', 'news/'.$news_rubric_alias.'/rss');
	   }
		else // ���� ���� ������ ���������� �������
	   {
		
		// ��������� ����������� ������ ������������� (� �������������� ��� ������ ������ �������)
		$template->subst_tpl('news_item.tpl');
		
		// ������ ������� ������, � ������� �������� ���� �������� "�������" + ��� �������
		$template->assign('bread_crumbs', $page->bread_crumbs(0, ' <a href="/'.$lng.'/news/'.$news_rubric_alias.'/">'.$news_rubric_name.'</a> / '));
		
		// ��������� ���������� � �������
		$r = $db->query("SELECT `n_title`, `n_dt`, `n_author`, `n_text` from `news` where `n_uid`='$news_uid'");
		
		// ���� ����� ������� ���, ������ �������� �� 404
		if ($r['num']!=1)
		 {
		  header("Location: /".$lng."/404/");
		  $general->log_error('Page not found!');
		  die();
		 }
		
		// �������������� ����������� ��� �������������
		$row = $r['res']->fetch_assoc();
		$template->assign('page_name', $row['n_title']); 
		$template->assign('page_title', implode(' - ', $page_info['ttls']).' - '.$news_rubric_name.' - '.$row['n_title']);
		$template->assign('page_text', $row['n_text']); 
		$template->assign('news_author', $row['n_author']); 
		$template->assign('ndt_Y', date('Y', $row['n_dt'])); 
		$template->assign('ndt_M', date('m', $row['n_dt'])); 
		$template->assign('ndt_D', date('d', $row['n_dt'])); 
		$template->assign('ndt_h', date('H', $row['n_dt'])); 
		$template->assign('ndt_m', date('i', $row['n_dt'])); 
		$template->assign('ndt_s', date('s', $row['n_dt'])); 
		
		$keywords = $news->get_keywords('('.$news_uid.')');
		$template->assign('keywords', $keywords[$news_uid]['text']);
		$template->assign('rss_title', $news_rubric_name);
		$template->assign('rss_url', 'news/'.$news_rubric_alias.'/rss');
	   }
	 }
	  else // ���� �� ������ �� ���� �������, �� �������
	 {
	  $news_list = $news->get_news_list($page_num);
	  $template->assign('news_list', $news_list['text']);
	  $template->assign('pagination', $general->pagination($page_num, $news_list['count']));
	  $template->assign('rss_title', $page_info['name']);
	  $template->assign('rss_url', 'news/rss');
	 }
 }
?>