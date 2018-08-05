<?

// +++ ================================================================================================================
// Реализует сепцифичные для отображения новостей методы
class news extends general
 {
  
  // +++ ===============================================================================================
  function __construct()
   {
    parent::__construct();
	$this->db = new db();
   }
  // --- =============================================================================================== 
  
  // +++ ===============================================================================================  
  function __destruct()
   {
    unset($this->db);
   } 
  // --- ===============================================================================================
 
  // +++ ===============================================================================================
  // Строит список новостей и определяет общее количество страниц (для паджинации)
  public function get_news_list($page_num = 1, $rubric_uid=0, $rubric_url= '', $tag_url='', $templates = array('news_li_wr' => 'news/news_li_withrubric.tpl', 'news_li_wnr' => 'news/news_li_withnorubric.tpl'))
   {
    //echo $page_num;
	
	// Проверяем на наличие всех шаблонов
	foreach ($templates as $v)
	 {
	  if (!is_file($this->templates_dir.$v))
	   {
	    parent::log_error('Template ['.$v.'] not found!', FALSE, 5);
	    die();
	   }	
	 }
	
	// Читаем шаболны
	$news_li_wr = file_get_contents($this->templates_dir.$templates['news_li_wr']);
	$news_li_wnr = file_get_contents($this->templates_dir.$templates['news_li_wnr']);
	
	// Определяем, сколько новостей выводить на страницу, и с какой страницы начинать
	$news_per_page = $this->get_db_config('news_per_page');
	$start = ($page_num-1)*$news_per_page;
    
	if ($tag_url!='') // Если юзер выбрал тег
	 {
	  // Подключаем шаблон со ссылкой на рубрику
	  $tpl = $news_li_wr;
	  
	  // Определяем UID'ы новостей, соответствующих тегу
	  $r = $this->db->query("SELECT `n_m2m_nk`.`n_uid` from `n_m2m_nk` JOIN `news_keywords` on `n_m2m_nk`.`nk_uid` = `news_keywords`.`nk_uid` where `news_keywords`.`nk_url`='$tag_url'");
	  
	  // Построили массив полученных UID'ов
	  while ($row = $r['res']->fetch_assoc()) 
	   {
	    $uids_array[] = $row['n_uid'];
	   }
      
	  // Если ни одного UID'а новости нет, т.е. отображать нечего, редиректим юзера на 404
	  if (count($uids_array)==0) 
	   {
	    header("Location: /".$this->lng."/404/");
        parent::log_error('Page not found!');
        die();
	   }
	  
	  // Строим строчку вида (1, 67, 89) для передачи в SQL-запрос, выбирающий конкретные новости для показа
	  $uids = '('.implode(', ', $uids_array).')';
	  
	  // Очищаем массив UID'ов, чтобы при разбиении на страницы эти массивы не "просуммировались"
	  $uids_array = array();
 
	  $r = $this->db->query("SELECT SQL_CALC_FOUND_ROWS `n_uid` from `news` where `n_uid` in ".$uids." AND `n_lng`='$this->lng' AND `n_show`='Y' order by `n_dt` desc limit $start,$news_per_page");
	  
     }
	elseif ($rubric_uid == 0) // Если юзер не выбрал рубрику
	 {
	  // Подключаем шаблон со ссылкой на рубрику
	  $tpl = $news_li_wr;
	  
	  // Выбираем все новости
	  $r = $this->db->query("SELECT SQL_CALC_FOUND_ROWS `n_uid` from `news` where `n_lng`='$this->lng' AND `n_show`='Y' order by `n_dt` desc limit $start,$news_per_page");
	 }
      else // Если юзер выбрал рубрику
	 {
	  // Подключаем шаблон без ссылки на рубрику
	  $tpl = $news_li_wnr;
	  
	  // Выбираем только новости выбранной юзером рубрики
	  $r = $this->db->query("SELECT SQL_CALC_FOUND_ROWS `n_uid` from `news` where `n_lng`='$this->lng' AND `n_show`='Y' AND `n_parent`='$rubric_uid' order by `n_dt` desc limit $start,$news_per_page");
	 } 

	// Определяем, сколько рядов вернул бы MySQL, если бы не LIMIT в запросе 
	$r2 = $this->db->query("SELECT FOUND_ROWS()");	 
	
	// Получаем общее количество новостей и страниц, необходимых для их отображения
        $row_tmp = $r['res']->fetch_array(MYSQLI_NUM);
        $total_news = $row_tmp[0];
	$total_pages = ceil($total_news/$news_per_page);
	
	// Строим массив идентификаторов новостей вида (1, 7, 34) для последующего отображения на выбранной юзером странице
    while ($row = $r['res']->fetch_array(MYSQLI_NUM)) 
     {
      $uids_array[]=$row[0];
     }
    
  
    // Если юзер запросил слишком большую стрницу (напр., 100000000000000), и новостей на такую страницу не хватило, редиректим на 404
    if (count($uids_array)==0)
     {
      header("Location: /".$this->lng."/404/");
      parent::log_error('Page not found!');
      die();
     }
  
    $uids = '('.implode(', ', $uids_array).')';
  
	if ($rubric_uid == 0)
	 {
	  // Если юзер не выбрал рубрику, выполняем "хитрый запрос", сразу возвращающий нам информацию о родительской рубрике каждой новости
	  $r = $this->db->query("SELECT `news`.`n_uid`, `news`.`n_parent`, `news`.`n_dt`, `news`.`n_title`, `news`.`n_author`, `news`.`n_annotation`, `news_rubrics`.`nr_url`, `news_rubrics`.`nr_name` from `news` LEFT JOIN `news_rubrics` ON `news`.`n_parent`=`news_rubrics`.`nr_uid` where `n_uid` in $uids order by `n_dt` desc");
	 }
      else
	 {
	  // Если юзер выбрал рубрику, "хитрый запрос" не нужен, т.к. мы и так уже знаем URL рубрики
	  $r = $this->db->query("SELECT `news`.`n_uid`, `news`.`n_parent`, `news`.`n_dt`, `news`.`n_title`, `news`.`n_author`, `news`.`n_annotation` from `news` where `n_uid` in $uids order by `n_dt` desc");
	 }
	
    $keywords = $this->get_keywords($uids);
	
    // Формируем список новостей
	$all = '';
	while ($row = $r['res']->fetch_assoc()) 
     {
      $one = $tpl;
	  $one = str_replace('{ITEM_UID}', $row['n_uid'], $one);
	  $one = str_replace('{ITEM_TITLE}', $row['n_title'], $one);
	  $one = str_replace('{ITEM_Y}', date('Y', $row['n_dt']), $one);
	  $one = str_replace('{ITEM_M}', date('m', $row['n_dt']), $one);
	  $one = str_replace('{ITEM_D}', date('d', $row['n_dt']), $one);
	  $one = str_replace('{ITEM_h}', date('H', $row['n_dt']), $one);
	  $one = str_replace('{ITEM_m}', date('i', $row['n_dt']), $one);
	  $one = str_replace('{ITEM_s}', date('s', $row['n_dt']), $one);
	  $one = str_replace('{ITEM_AUTHOR}', $row['n_author'], $one);
	  $one = str_replace('{ITEM_ANNOTATION}', $row['n_annotation'], $one);
	  
	  if ($rubric_uid == 0)
	   {
	    $one = str_replace('{ITEM_RUBRIC_URL}', $row['nr_url'], $one);
	    $one = str_replace('{ITEM_RUBRIC_NAME}', $row['nr_name'], $one);
	   }
	    else
	   {
	    $one = str_replace('{ITEM_RUBRIC_URL}', $rubric_url, $one);
	   }	
	  
	  if (isset($keywords[$row['n_uid']]))
	   {
	    $one = str_replace('{KEYWORDS}', $keywords[$row['n_uid']]['text'], $one);
	   }
  	    else
	   {
	    $one = str_replace('{KEYWORDS}', '', $one);
	   }	
	  $all .= $one;
     }
  
  
    // Возвращаем список новостей и количество страниц для паджинации
    return array('text' => $all, 'count' => $total_pages);
   }
  // --- =============================================================================================== 
  
  // +++ ===============================================================================================
  // Строит список новостей для RSS
  public function get_news_list_rss($rubric_uid=0, $rubric_url= '', $rubric_name = '', $news_limit = 100, $templates = array('news_li_rss' => 'news/news_li_rss.tpl'))
   {
	
	// Проверяем на наличие всех шаблонов
	foreach ($templates as $v)
	 {
	  if (!is_file($this->templates_dir.$v))
	   {
	    parent::log_error('Template ['.$v.'] not found!', FALSE, 5);
	    die();
	   }	
	 }
	
	// Читаем шаболны
	$tpl = file_get_contents($this->templates_dir.$templates['news_li_rss']);
	
	if ($rubric_uid == 0)
	 {
	  // Если юзер не выбрал рубрику, выполняем "хитрый запрос", сразу возвращающий нам информацию о родительской рубрике каждой новости
	  $r = $this->db->query("SELECT `news`.`n_uid`, `news`.`n_parent`, `news`.`n_dt`, `news`.`n_title`, `news`.`n_author`, `news`.`n_annotation`, `news_rubrics`.`nr_url`, `news_rubrics`.`nr_name` from `news` LEFT JOIN `news_rubrics` ON `news`.`n_parent`=`news_rubrics`.`nr_uid` where `n_show`='Y' AND `n_lng`='$this->lng' order by `n_dt` desc limit $news_limit");
	 }
      else
	 {
	  // Если юзер выбрал рубрику, "хитрый запрос" не нужен, т.к. мы и так уже знаем URL рубрики
	  $r = $this->db->query("SELECT `news`.`n_uid`, `news`.`n_parent`, `news`.`n_dt`, `news`.`n_title`, `news`.`n_author`, `news`.`n_annotation` from `news` where `n_parent`='$rubric_uid' AND `n_show`='Y' AND `n_lng`='$this->lng' order by `n_dt` desc limit $news_limit");
	 }
	
    // Формируем список новостей
	$all = '';
	while ($row = $r['res']->fetch_assoc()) 
     {
      $one = $tpl;
	  $one = str_replace('{ITEM_UID}', $row['n_uid'], $one);
	  $one = str_replace('{ITEM_TITLE}', htmlentities($row['n_title'], ENT_COMPAT, 'UTF-8'), $one);
	  $one = str_replace('{ITEM_DATE}', date('r', $row['n_dt']), $one);
	  $one = str_replace('{ITEM_AUTHOR}', htmlentities($row['n_author'], ENT_COMPAT, 'UTF-8'), $one);
	  $one = str_replace('{ITEM_ANNOTATION}', htmlentities($row['n_annotation'], ENT_COMPAT, 'UTF-8'), $one);
	  
	  if ($rubric_uid == 0)
	   {
	    $one = str_replace('{ITEM_RUBRIC_URL}', $row['nr_url'], $one);
	    $one = str_replace('{ITEM_RUBRIC_NAME}', htmlentities($row['nr_name'], ENT_COMPAT, 'UTF-8'), $one);
	   }
	    else
	   {
	    $one = str_replace('{ITEM_RUBRIC_URL}', $rubric_url, $one);
		$one = str_replace('{ITEM_RUBRIC_NAME}', htmlentities($rubric_name, ENT_COMPAT, 'UTF-8'), $one);
	   }	
  
	  $all .= $one;
     }
  
  
    // Возвращаем список новостей
    return $all;
   }
  // --- ===============================================================================================   
  
  
  // +++ =============================================================================================== 
  public function get_keywords($uids, $kw_tpl = 'news/keyword_li.tpl')
   {
    if (!is_file($this->templates_dir.$kw_tpl)) 
	 {
	  parent::log_error('Template ['.$kw_tpl.'] not found!', FALSE, 5);
	  die(); 
	 }
	$kw_tpl = file_get_contents($this->templates_dir.$kw_tpl); 
	
    $r = $this->db->query("SELECT `news_keywords`.`nk_url`, `news_keywords`.`nk_name`, `n_m2m_nk`.`n_uid` from `news_keywords` LEFT JOIN `n_m2m_nk` ON `news_keywords`.`nk_uid`=`n_m2m_nk`.`nk_uid` where `n_m2m_nk`.`n_uid` IN ".$uids." ORDER BY `n_m2m_nk`.`n_uid`, `news_keywords`.`nk_name` asc");
	while ($row = $r['res']->fetch_assoc()) 
	 {
	  $news_id = $row['n_uid'];
	  $pair = array('url' => $row['nk_url'], 'name' => $row['nk_name']);
	  $final[$news_id][]=$pair;
	 }
	 
	foreach ($final as $k => $v) 
	 {
	  $all='';
	  foreach ($v as $pair)
	   {
	    $one = $kw_tpl;
		$one = str_replace('{ITEM_URL}', $pair['url'], $one);
		$one = str_replace('{ITEM_NAME}', $pair['name'], $one);
		$all .=$one;
	   }
	  $final[$k]['text'] = $all;
	 }
	//print_r($final);
	return $final;
   }
  // --- =============================================================================================== 
  
  // +++ ================================================================================================================   
  public function tags_cloud($limit = 100, $min = 10, $max=30, $kw_tpl = 'news/tag_li.tpl')
   {
    if (!is_file($this->templates_dir.$kw_tpl)) 
	 {
	  parent::log_error('Template ['.$kw_tpl.'] not found!', FALSE, 5);
	  die(); 
	 }
	$kw_tpl = file_get_contents($this->templates_dir.$kw_tpl); 
	
	$r = $this->db->query("SELECT `news_keywords`.`nk_url`, `news_keywords`.`nk_name`, count(*) as 'q' from `news_keywords` RIGHT JOIN `n_m2m_nk` ON `news_keywords`.`nk_uid`=`n_m2m_nk`.`nk_uid` group by `n_m2m_nk`.`nk_uid` order by `q` desc, `news_keywords`.`nk_name` asc limit ".$limit);
	
	while ($row = $r['res']->fetch_assoc()) 
	 {
//	  echo $row['nk_name'].' ';
	  $words[$row['nk_name']]=array('url' => $row['nk_url'], 'count' => $row['q']);
	 }
    
	//print_r($words);

	mysqli_data_seek($r['res'], 0);
	$max_occ = $r['res']->fetch_assoc()['q'];
	mysqli_data_seek($r['res'], $r['num']-1);
        $min_occ = $r['res']->fetch_assoc()['q'];
	
	$fonts_diff = $max-$min;
	$size_diff = $max_occ-$min_occ;
	
	ksort($words);

	$all = '';
	foreach ($words as $k => $v)
	 {
	  $one = $kw_tpl;
	  $one = str_replace('{ITEM_URL}', $v['url'], $one);
	  $one = str_replace('{ITEM_NAME}', $k, $one);
	    
	  $size_perc = round((($v['count']-$min_occ)/$size_diff), 2);
	  $font_size = round($fonts_diff*$size_perc)+$min;
	  
	  //echo $v['count'].' ----> '.$size_perc.' ---> '.$font_size.'<br />';
	  
	  $one = str_replace('{ITEM_SIZE}', $font_size, $one);
	  $all .= $one;
	 }
	
	//print_r($words);
	
	//echo $all;
	
	return $all;
   }
  // --- ================================================================================================================     
  
 }
// --- ================================================================================================================

 
 
?>