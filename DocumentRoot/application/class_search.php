<?

// +++ ==================================================================================================================
class search extends general
 {
  private $db;
  private $sr;
  
  // +++ ======================================================================================
  function __construct()
   {
    parent::__construct();
	$this->db = new db();
   }
  // --- ======================================================================================
  
  // +++ ======================================================================================
  function __destruct()
   {
    unset($db);
   }
  // --- ======================================================================================
  
  // +++ ======================================================================================
  public function get_search_request($sr = FALSE)
   {
    //print_r($_POST);
	if ($sr == FALSE)
	 {
	  $sr = (isset($_POST['searchtext'])) ? $_POST['searchtext'] : '';
	 }
	  
	$sr = preg_replace("/[^а-яa-z\d\-_ \.,\"\']/imsu", "", $sr);
	
	$this->sr=$sr;
	
    return $sr;
   }
  // --- ======================================================================================
  
  // +++ ======================================================================================
  public function get_sr()
   {
    if (isset($this->sr))
	 {
	  return $this->sr;
	 }
	  else
	 {
	  return FALSE;
	 } 
   }
  // --- ======================================================================================
  
  // +++ ======================================================================================
  public function get_search_form($tpl = 'search/search_form.tpl')
   {
    if (!is_file($this->templates_dir.$tpl))
	 {
	  parent::log_error('Template ['.$tpl.'] not found!', FALSE, 5);
	  die();
	 }
	 
	$tpl = file_get_contents($this->templates_dir.$tpl);
	
	$sr = $this->get_search_request();
	if (($sr=='')&&(isset($_SESSION['sr'])))
	 {
	  $sr = $_SESSION['sr'];
	 }
	
	$tpl = str_replace('{SEARCH_VALUE}', htmlspecialchars($sr), $tpl);
	
	
	return $tpl;
   }
  // --- ======================================================================================
  
  // +++ ======================================================================================
  public function get_search_results($sr, $page=1, $tpl = 'search/search_result_li.tpl')
   {
    if (!is_file($this->templates_dir.$tpl))
	 {
	  parent::log_error('Template ['.$tpl.'] not found!', FALSE, 5);
	  die();
	 }
	 
	$tpl = file_get_contents($this->templates_dir.$tpl);
	
    $all_arr = array();
	
	$r = $this->db->query("SELECT `p_uid`, `p_url`, `p_title`, `p_text` from `pages` where (`p_title` like '%$sr%' OR `p_text` like '%$sr%') AND (`p_in_menu`='Y')");
	$i=0;
	while ($row = $r['res']->fetch_assoc()) 
	 {
	  $item['title'] = $row['p_title'];
	  $item['annotation'] = mb_substr(strip_tags($row['p_text']),0,500,'UTF-8').' ...';
	  $item['uid'] = $row['p_uid'];
	  $item['url'] = $row['p_url'];
	  $item['type'] = 'page';
	  $rel = $item['rel'] = mb_substr_count($row['p_title'].$row['p_text'], $sr, 'UTF-8');
	  $all_arr[$rel.'_'.$i++]=$item;
	 }
	
	$r = $this->db->query("SELECT `n_uid`, `n_title`, `n_author`, `n_text` from `news` where (`n_title` like '%$sr%' OR `n_text` like '%$sr%' OR `n_author` like '%$sr%') AND (`n_show`='Y')");
	while ($row = $r['res']->fetch_assoc()) 
	 {
	  $item['title'] = $row['n_title'];
	  $item['annotation'] = mb_substr(strip_tags($row['n_text']),0,500,'UTF-8').' ...';
	  $item['uid'] = $row['n_uid'];
	  $item['url'] = $row['n_uid'];
	  $item['type'] = 'news';
	  $rel = $item['rel'] = mb_substr_count($row['n_title'].$row['n_text'].$row['n_author'], $sr, 'UTF-8');
	  $all_arr[$rel.'_'.$i++]=$item;
	 }
	
	if (count($all_arr)==0)
	 {
	  return FALSE;
	 }
	
	krsort($all_arr, SORT_NUMERIC);
	$all_arr = array_values($all_arr);
	
	$all='';
	$start_item = ($page-1)*10;
	$end_item = ($page)*10-1;
	for($i=$start_item;$i<=$end_item;$i++)
	 {
	  if (!isset($all_arr[$i])) continue;
	  $one = $tpl;
	  
	  $one = str_replace('{ITEM_TITLE}', $all_arr[$i]['title'], $one);
	  $one = str_replace('{ITEM_ANNOTATION}', $all_arr[$i]['annotation'], $one);
	  $one = str_replace('{ITEM_MATCH}', $all_arr[$i]['rel'], $one);
	  
	  $item_url = $this->get_full_url($all_arr[$i]['uid'], $all_arr[$i]['type']);
	  //if ($item_url == FALSE) continue;
	  
	  $one = str_replace('{ITEM_URL}', $item_url, $one);
	  
	  $all.=$one;
	 }
	
	//print_r($all_arr);

	//echo $all;
	
	$total_pages = ceil(count($all_arr)/10);
	return array('text' => $all, 'count' => $total_pages);
   }
  // --- ======================================================================================
  
  // +++ ======================================================================================
  private function get_full_url($uid, $type)
   {
    // Новости
	if ($type=='news')
	 {
	  $r = $this->db->query("SELECT `nr_url` FROM `news_rubrics`, `news` WHERE `news_rubrics`.`nr_uid`=`news`.`n_parent` AND `n_uid`='$uid' limit 1");
	  if ($r['num']==1)
	   {
	    return '/'.$type.'/'.$r['res']->fetch_array(MYSQLI_NUM)[0].'/'.$uid.'/';
	   }
	    else
	   {
	    return FALSE;
	   }	
	 }
	
	// Страницы
	if ($type=='page')
	 {
	  $r = $this->db->query("SELECT `p_url`, `p_parent` FROM `pages` WHERE `p_uid`='$uid' limit 1");
	  if ($r['num']!=1) return FALSE;
	  
	  $row_tmp = $r['res']->fetch_assoc();
          $parent = $row_tmp['p_parent'];
	  $url = $row_tmp['p_url'];
	  while ($parent!=0)
	   {
		$r = $this->db->query("SELECT `p_url`, `p_parent`  FROM `pages` WHERE `p_uid`='$parent' limit 1"); 
		if ($r['num']!=1) return FALSE;
	        $row_tmp = $r['res']->fetch_assoc();
                $parent = $row_tmp['p_parent'];
                $url = $row_tmp['p_url'].'/'.$url;
	   }
	   return '/'.$url.'/';
     }
   }
  // --- ======================================================================================
  
 }
// --- ==================================================================================================================
?>