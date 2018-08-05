<?

// +++ ================================================================================================================
// ������� ������� �������� �������.
// � ���� ������ ������������ ������, ���������� � ���������, ������� ����������� �� ���� ��������� ������� (���� �� ��������).
class navigation extends general
 {
 
  private $url; // URL, �� ������� ��������� ����
  private $page_info; // ������ ���������� � ��������
  
  // ������� ��� ������� ����  
  private $before;
  private $after;
  private $listitem_out;
  private $listitem_in;
  private $listitem_on;
  
  private $db;

  // +++ ================================================================================================================  
  function __construct($url, $lng, $templates=array('before' => 'menu/before.tpl', 'after' => 'menu/after.tpl', 'listitem_out' => 'menu/out.tpl', 'listitem_in' => 'menu/in.tpl', 'listitem_on' => 'menu/on.tpl'))
   {
    parent::__construct();
	$this->url = $url;
	$this->lng = $lng;
	$this->db = new db();
	
	
	// ��������� �� ������� ���� ���� �������� ������� ����
	if (!is_file($this->templates_dir.$templates['before']))
	 {
	  parent::log_error('Template ['.$templates['before'].'] not found!', FALSE, 5);
	  die();
	 }
	
	if (!is_file($this->templates_dir.$templates['after']))
	 {
	  parent::log_error('Template ['.$templates['after'].'] not found!', FALSE, 5);
	  die();
	 }
	
	if (!is_file($this->templates_dir.$templates['listitem_out']))
	 {
	  parent::log_error('Template ['.$templates['listitem_out'].'] not found!', FALSE, 5);
	  die();
	 } 
	 
	if (!is_file($this->templates_dir.$templates['listitem_in']))
	 {
	  parent::log_error('Template ['.$templates['listitem_in'].'] not found!', FALSE, 5);
	  die();
	 } 

    if (!is_file($this->templates_dir.$templates['listitem_on']))
	 {
	  parent::log_error('Template ['.$templates['listitem_on'].'] not found!', FALSE, 5);
	  die();
	 } 	 
	
	// ������ ��� ���� �������� ������� ����
	$this->before = file_get_contents($this->templates_dir.$templates['before']);
	$this->after = file_get_contents($this->templates_dir.$templates['after']);
	$this->listitem_out = file_get_contents($this->templates_dir.$templates['listitem_out']);
	$this->listitem_in = file_get_contents($this->templates_dir.$templates['listitem_in']);
	$this->listitem_on = file_get_contents($this->templates_dir.$templates['listitem_on']);
   }
  // --- ================================================================================================================  
  
  // +++ ================================================================================================================  
  function __destruct()
   {
    unset($this->db);
   }
  // --- ================================================================================================================  
  
  // +++ ================================================================================================================  
  // �������� ������ ���������� � ��������
  public function get_page_info()
   {
    $uids = array();
	$urls = array();
	$ttls = array();
	
	// ��������� URL, �� ������� ��������� ����, �� ������
	$url_parts = explode('/', $this->url);
	
	$parent=0;
	// �������� ���������� ���������� ������ � ��� ������� ��� ���������� ���� ��������������� �������� � ��
	for ($i=0;$i<count($url_parts);$i++)
	 {
	  $part = $url_parts[$i];
	  $r = $this->db->query("SELECT * from `pages` where `p_parent`='$parent' AND `p_lang`='$this->lng' AND `p_url`='$part'");
	  if ($r['num']!==1)
	   {
		return FALSE;
	   }
          $row = $r['res']->fetch_assoc();
	  $parent = $row['p_uid'];
	  
	  // �������� ������� �� uid'��, url'�� � title'�� �� ���� ��������� � URL'�, �� ������� ��������� ����
	  $uids[] = $row['p_uid'];
	  $urls[] = $row['p_url'];
	  $ttls[] = $row['p_title'];
	 } 
	
	// �������� ���������� � ��� ��������, �� ������� ������ ��������� ����
	$this->page_info['uid'] = $row['p_uid'];
	$this->page_info['title'] = $row['p_title'];
	$this->page_info['keywords'] = $row['p_keywords'];
	$this->page_info['description'] = $row['p_description'];
	$this->page_info['name'] = $row['p_name'];
	$this->page_info['menu_name'] = $row['p_menu_name'];
	$this->page_info['in_menu'] = $row['p_in_menu'];
	$this->page_info['in_sitemap'] = $row['p_in_sitemap'];
	$this->page_info['compress'] = $row['p_compress'];
	$this->page_info['cache'] = $row['p_cache'];
	$this->page_info['text'] = $row['p_text'];
	$this->page_info['file'] = $row['p_file'];
	$this->page_info['template'] = $row['p_template'];
	$this->page_info['template_print'] = $row['p_template_print'];
	$this->page_info['uids'] = $uids;
	$this->page_info['urls'] = $urls;
	$this->page_info['ttls'] = $ttls;
	
	return $this->page_info;
   }
  // --- ================================================================================================================  
  
  // +++ ================================================================================================================  
  // ������ ����
  public function get_menu($page_id, $ids, $parent=0, $preurl='', $force_in = FALSE)
   {
	
	$all = '';
	$r = $this->db->query("SELECT `p_uid`, `p_menu_name`, `p_url` from `pages` where `p_lang`='$this->lng' AND `p_parent`='$parent' AND `p_in_menu`='Y' order by `p_ord` asc");
	while ($row = $r['res']->fetch_assoc()) 
	 {
	  $need_subpages = FALSE;
	  // 1 (on). �� �� ���� ��������
	  if (($page_id == $row['p_uid'])&&($force_in == FALSE))
	   {
	    $one = $this->listitem_on;
		$need_subpages = TRUE;
	   }
	  // 2 (in). �� �� ����� �� ���������� ���� ��������
		elseif(in_array($row['p_uid'], $ids, TRUE))
		 {
		  $one = $this->listitem_in;
		  $need_subpages = TRUE;
		 }
	  // 3 (out). �� �� �� �������� � �� �� � �����������.
	      else
		   {
		    $one = $this->listitem_out;
		   }
	  
	  $one = str_replace('{ITEM_NAME}', $row['p_menu_name'], $one);
	  $one = str_replace('{ITEM_URL}', $preurl.$row['p_url'], $one);
	  
	  if ($need_subpages == TRUE)
	   {
	    if (isset($GLOBALS['special_submenu_method']))
	     {
	      $function_name = $GLOBALS['special_submenu_method'];
		  $subpages = $this->$function_name($row['p_url']);
	     }
	      else
	     {
	      $subpages = $this->get_menu($page_id, $ids, $row['p_uid'], $preurl.$row['p_url'].'/', $force_in);
	     }	
	    if ($subpages!='')
	     {
	      $one = str_replace('{SUBPAGES}', $this->before.$subpages.$this->after, $one);
	     }
	      else
	     {
	      $one = str_replace('{SUBPAGES}', '', $one);
	     }	
	   } 
	  $all .= $one;
	 }
	
	return $all;
   }
  // --- ================================================================================================================   
  
  // +++ ================================================================================================================   
  // ���������� ������ ���������� � ���� ��� �������� "�������"
  public function news_subpages($url)
   {
    $r = $this->db->query("SELECT `nr_uid`, `nr_name`, `nr_url` FROM `news_rubrics`, `news` WHERE `news_rubrics`.`nr_uid`=`news`.`n_parent` AND `news_rubrics`.`nr_lng`='$this->lng' GROUP BY `news_rubrics`.`nr_uid` ORDER BY `news_rubrics`.`nr_ord` asc");
	$all='';
	while ($row = $r['res']->fetch_assoc()) 
	 {
	  // 1 (on). �� �� ���� ������� ��������
	  if (($row['nr_uid'] == $GLOBALS['news_rubric_uid'])&&($GLOBALS['news_uid']==0))
	   {
	    $one = $this->listitem_on;
	   }
	  // 2 (in). �� ������������� ������� �� ���� �������
		elseif (($row['nr_uid'] == $GLOBALS['news_rubric_uid'])&&($GLOBALS['news_uid']!=0))
		 {
		  $one = $this->listitem_in;
		 }
	  // 3 (out). �� �� �� ���� ������� � �� �� � �������
	      else
		   {
		    $one = $this->listitem_out;
		   }
	  
	  $one = str_replace('{SUBPAGES}', '', $one);
	  $one = str_replace('{ITEM_NAME}', $row['nr_name'], $one);
	  $one = str_replace('{ITEM_URL}', $url.'/'.$row['nr_url'], $one);
	  $all.=$one;   
	 }
	
    return $all;
   }
  // --- ================================================================================================================   
  
  // +++ ================================================================================================================   
  // ������ ���� ������� ������
  public function bread_crumbs($stop=1, $postfix='', $templates=array('bc' => 'bread_crumbs/bread_crumbs.tpl', 'bc_li' => 'bread_crumbs/bread_crumbs_listitem.tpl', 'lng_li_on' => 'bread_crumbs/lng_listitem_on.tpl', 'lng_li_out' => 'bread_crumbs/lng_listitem_out.tpl', 'other_langs' => 'bread_crumbs/other_languages.tpl', 'other_langs_li' => 'bread_crumbs/lng_listitem_other.tpl'))
   {
    // ��������� �� ������� ���� ��������
	foreach ($templates as $v)
	 {
	  if (!is_file($this->templates_dir.$v))
	   {
	    parent::log_error('Template ['.$v.'] not found!', FALSE, 5);
	    die();
	   }	
	 }  
	
    // ������ ��� �������	
    $bc = file_get_contents($this->templates_dir.$templates['bc']);
	$bc_li = file_get_contents($this->templates_dir.$templates['bc_li']);
	$lng_li_on = file_get_contents($this->templates_dir.$templates['lng_li_on']);
	$lng_li_out = file_get_contents($this->templates_dir.$templates['lng_li_out']);
	$other_langs = file_get_contents($this->templates_dir.$templates['other_langs']);
	$other_langs_li = file_get_contents($this->templates_dir.$templates['other_langs_li']);	
	
	// ������ ���� ������� ������
	
	// ��������� � ������ �� ������ �� ������� ��������
	$all = $bc_li;
	$all = str_replace('{ITEM_URL}', '/main', $all);
	$all = str_replace('{ITEM_NAME}', parent::get_db_config('bc_main'), $all);
	
	// ������ ���������� ��
	$preurl = '';
	// �������� �� �������� � URL'��� � TITLE'���
	for ($i=0; $i<count($this->page_info['urls'])-$stop; $i++)
	 {
	  $one = $bc_li;
	  $one = str_replace('{ITEM_NAME}', $this->page_info['ttls'][$i], $one);
	  $one = str_replace('{ITEM_URL}', $preurl.'/'.$this->page_info['urls'][$i], $one);
	  $all.=$one;
	  
	  // ����������� URL, ��������� ������ ��������� �� � ����� ������ ������ ��������� �������� ������ ������������ �������
	  $preurl .= '/'.$this->page_info['urls'][$i];
	 }
	
	// ���������� � ����� �� �������� (��������, ������� ��������) � ��������� �� � �������� �����, ���������� �� ���� ���� ��
	$all.=$postfix;
	$bc = str_replace('{BREAD_CRUMBS}', $all, $bc);
	
	// ������ �����, ���������� �� "�������� ����� �������� �� ������"
	// ����� �� ������ �����, ���������� �� ����������� ������
	$all = ''; // ������������� ���������� ��� "�������� ��"
	$all_l = ''; // ������������� ���������� ��� ������ �������� ������ �����
	
	// ��������� ���������� � ���, �� ����� ����� ���� ������� �������� + ���������� � ����� ��������������� ��������
	$save_lng = $this->lng;
	$save_pi = $this->page_info;
	
	// ������� �� ���������, ��� ������������ �������� ������ ��� ���� �������� ���
	$have_another_languages = FALSE;
	
	// �������� �� ������� �������� ������ �����
	foreach ($GLOBALS['config']['languages'] as $k => $v)
	 {
	  // ���������� ������� � ��������� ����� �� ���������
	  if ($k == 'default') continue;

	  // "������ ���", ��� ���� ������������� ���� �� �����, ������� �� ������ ��������, � ���� �������� �� ���� �����
	  $this->lng = $k;
	  $test = $this->get_page_info();
	  
	  // ���� ����� �������� ������� � ��������������� ���� �� ��������� � ���, �� ������� ���� �� ����� ���� ������������� ����...
	  if (($test!==FALSE)&&($save_lng!=$k))
	   {
	    
		// ������������� ������� ����, ��� �� ����� �������������� �������� ������
 	    $have_another_languages = TRUE;
		
		// ��������� ��� �������� ������ � ������
		$one = $other_langs_li;
		$one = str_replace('{ITEM_LNG}', $k, $one);
		$one = str_replace('{ITEM_NAME}', $v['long'], $one);
		$one = str_replace('{ITEM_URL}', $this->url, $one);
		$all.=$one;
	   }
	  
	  // ������ ������ ������ �������� ������ �����
	  if ($save_lng == $k)
	   {
	    // ���� ���� ������������� ���� �� ��������������� �����, ������ ������� ��� ����� �����
		$one_l = $lng_li_on;
	   }
        else 
	   {	
	    // ��� ���� ��������� ������ ������� ������ �� ���
		$one_l = $lng_li_out;
	   }	
	  
	  $one_l = str_replace('{ITEM_LNG}', $k, $one_l);
	  $one_l = str_replace('{ITEM_NAME}', $v['short'], $one_l);
	  
	  // ���� ��� ��������, �� ������� ������ ��������� ����, ���� �������������� �������� ������ �� ��������������� �����, ��� ������ �� ��� �������������� ������
	  if ($test!==FALSE)
	   {
	    $one_l = str_replace('{ITEM_URL}', $this->url, $one_l);
	   }
	    else // ����� ������ ��� ������ �� ������� �������� �������� ������ ���������������� �����
	   {
	    $one_l = str_replace('{ITEM_URL}', 'main', $one_l);
	   }	
	  
	  $all_l.=$one_l;
	 }
	
	// ��������������� ���������� � ���, �� ����� ����� ���� ������� �������� + ���������� � ����� ��������������� ��������
	$this->lng = $save_lng;
	$this->page_info = $save_pi;
	
	// ���� � ��� ���� �������������� �������� ������ ��� ��������������� ������ ��������, ������� ���������� �� ���� � ������� ������ ��
	if ($have_another_languages == TRUE)
	 {
	  $other_langs = str_replace('{LANGUAGES}', $all, $other_langs);
	  $bc = str_replace('{AVAILABLE_IN}', $other_langs, $bc);
	 }
      else // ����� ������ �������� ���� � ��������������� ��������� �������� ��������
	 {
	  $bc = str_replace('{AVAILABLE_IN}', '', $bc);
	 } 
	
	// �������� � ������� ������ �� ���������� � ���� ��������� �������� ������� �����
	$bc = str_replace('{LANGUAGES}', $all_l, $bc);
	
	return $bc;
   }
  // --- ================================================================================================================   
 
  
 }
// --- ================================================================================================================
?>