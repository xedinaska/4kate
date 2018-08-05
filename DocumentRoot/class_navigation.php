<?

// +++ ================================================================================================================
// Верхний уровень иерархии классов.
// В этом классе определяются методы, переменные и константы, которые понадобятся во всех остальных классах (ниже по иерархии).
class navigation extends general
 {
 
  private $url; // URL, на котором находится юзер
  private $page_info; // массив информации о странице
  
  // шаблоны для пунктов меню  
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
	
	
	// Проверяем на наличие всех пяти шаблонов пунктов меню
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
	
	// Читаем все пять шаблонов пунктов меню
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
  // Собирает полную информацию о странице
  public function get_page_info()
   {
    $uids = array();
	$urls = array();
	$ttls = array();
	
	// Разбиваем URL, на котором находится юзер, по слешам
	$url_parts = explode('/', $this->url);
	
	$parent=0;
	// Проходим полученный разбиением массив и для каджого его компонента ищем соответствующую страницу в БД
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
	  
	  // Собираем массивы из uid'ов, url'ов и title'ов по всем страницам в URL'е, на котором находится юзер
	  $uids[] = $row['p_uid'];
	  $urls[] = $row['p_url'];
	  $ttls[] = $row['p_title'];
	 } 
	
	// СОбираем информацию о той странице, на которой сейчас находится юзер
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
  // Строит меню
  public function get_menu($page_id, $ids, $parent=0, $preurl='', $force_in = FALSE)
   {
	
	$all = '';
	$r = $this->db->query("SELECT `p_uid`, `p_menu_name`, `p_url` from `pages` where `p_lang`='$this->lng' AND `p_parent`='$parent' AND `p_in_menu`='Y' order by `p_ord` asc");
	while ($row = $r['res']->fetch_assoc()) 
	 {
	  $need_subpages = FALSE;
	  // 1 (on). Мы НА этой странице
	  if (($page_id == $row['p_uid'])&&($force_in == FALSE))
	   {
	    $one = $this->listitem_on;
		$need_subpages = TRUE;
	   }
	  // 2 (in). Мы на одной их подстраниц этой страницы
		elseif(in_array($row['p_uid'], $ids, TRUE))
		 {
		  $one = $this->listitem_in;
		  $need_subpages = TRUE;
		 }
	  // 3 (out). Мы НЕ на странице и НЕ на её подстранице.
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
  // Генерирует список подстраниц в меню для страницы "Новости"
  public function news_subpages($url)
   {
    $r = $this->db->query("SELECT `nr_uid`, `nr_name`, `nr_url` FROM `news_rubrics`, `news` WHERE `news_rubrics`.`nr_uid`=`news`.`n_parent` AND `news_rubrics`.`nr_lng`='$this->lng' GROUP BY `news_rubrics`.`nr_uid` ORDER BY `news_rubrics`.`nr_ord` asc");
	$all='';
	while ($row = $r['res']->fetch_assoc()) 
	 {
	  // 1 (on). Мы НА этой рубрике новостей
	  if (($row['nr_uid'] == $GLOBALS['news_rubric_uid'])&&($GLOBALS['news_uid']==0))
	   {
	    $one = $this->listitem_on;
	   }
	  // 2 (in). Мы просматриваем новость из этой рубрики
		elseif (($row['nr_uid'] == $GLOBALS['news_rubric_uid'])&&($GLOBALS['news_uid']!=0))
		 {
		  $one = $this->listitem_in;
		 }
	  // 3 (out). Мы НЕ на этой рубрике и НЕ на её новости
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
  // Строит блок хлебных крошек
  public function bread_crumbs($stop=1, $postfix='', $templates=array('bc' => 'bread_crumbs/bread_crumbs.tpl', 'bc_li' => 'bread_crumbs/bread_crumbs_listitem.tpl', 'lng_li_on' => 'bread_crumbs/lng_listitem_on.tpl', 'lng_li_out' => 'bread_crumbs/lng_listitem_out.tpl', 'other_langs' => 'bread_crumbs/other_languages.tpl', 'other_langs_li' => 'bread_crumbs/lng_listitem_other.tpl'))
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
	
    // Читаем все шаблоны	
    $bc = file_get_contents($this->templates_dir.$templates['bc']);
	$bc_li = file_get_contents($this->templates_dir.$templates['bc_li']);
	$lng_li_on = file_get_contents($this->templates_dir.$templates['lng_li_on']);
	$lng_li_out = file_get_contents($this->templates_dir.$templates['lng_li_out']);
	$other_langs = file_get_contents($this->templates_dir.$templates['other_langs']);
	$other_langs_li = file_get_contents($this->templates_dir.$templates['other_langs_li']);	
	
	// Строим сами хлебные крошки
	
	// Добавялем в начало ХК ссылку на главную страницу
	$all = $bc_li;
	$all = str_replace('{ITEM_URL}', '/main', $all);
	$all = str_replace('{ITEM_NAME}', parent::get_db_config('bc_main'), $all);
	
	// Строим оставшиеся ХК
	$preurl = '';
	// Проходим по массивам с URL'ами и TITLE'ами
	for ($i=0; $i<count($this->page_info['urls'])-$stop; $i++)
	 {
	  $one = $bc_li;
	  $one = str_replace('{ITEM_NAME}', $this->page_info['ttls'][$i], $one);
	  $one = str_replace('{ITEM_URL}', $preurl.'/'.$this->page_info['urls'][$i], $one);
	  $all.=$one;
	  
	  // Накапливаем URL, поскольку каждая следующая ХК в своей ссылке должна содержать элементы ссылок родительских страниц
	  $preurl .= '/'.$this->page_info['urls'][$i];
	 }
	
	// Доклеиваем в конец ХК постфикс (например, рубрику новостей) и вставляем ХК в основной шблон, отвечающий за весь блок ХК
	$all.=$postfix;
	$bc = str_replace('{BREAD_CRUMBS}', $all, $bc);
	
	// Строим часть, отвечающую за "страница также доступна на языках"
	// Здесь же строим часть, отвечающую за перключение языков
	$all = ''; // Накопительная переменная для "доступно на"
	$all_l = ''; // Накопительная переменная для списка языковых версий сайта
	
	// Сохраняем информацию о том, на каком языке юзер смотрит страницу + информацию о самой просматриваемой странице
	$save_lng = $this->lng;
	$save_pi = $this->page_info;
	
	// Считаем по умолчанию, что альтернывных языковых версий для этой страницы нет
	$have_another_languages = FALSE;
	
	// Проходим по массиву языковых версий сайта
	foreach ($GLOBALS['config']['languages'] as $k => $v)
	 {
	  // Пропускаем элемент с указанием языка по умолчанию
	  if ($k == 'default') continue;

	  // "Делаем вид", что юзер просматривает сайт на языке, который мы сейчас проверям, и ищем страницу на этом языке
	  $this->lng = $k;
	  $test = $this->get_page_info();
	  
	  // Если такая страница найдена и рассматриваемый язык не совпадает с тем, на котором юзер на самом деле просматривает сайт...
	  if (($test!==FALSE)&&($save_lng!=$k))
	   {
	    
		// Устанавливаем признак того, что мы нашли альтернативную языковую версию
 	    $have_another_languages = TRUE;
		
		// Добавляем эту языковую версию в список
		$one = $other_langs_li;
		$one = str_replace('{ITEM_LNG}', $k, $one);
		$one = str_replace('{ITEM_NAME}', $v['long'], $one);
		$one = str_replace('{ITEM_URL}', $this->url, $one);
		$all.=$one;
	   }
	  
	  // Строим просто список языковых версий сайта
	  if ($save_lng == $k)
	   {
	    // Если юзер просматривает сайт на рассматриваемом языке, просто выводим имя этого языка
		$one_l = $lng_li_on;
	   }
        else 
	   {	
	    // Для всех остальных языков выводим ссылку на них
		$one_l = $lng_li_out;
	   }	
	  
	  $one_l = str_replace('{ITEM_LNG}', $k, $one_l);
	  $one_l = str_replace('{ITEM_NAME}', $v['short'], $one_l);
	  
	  // Если для страницы, на которой сейчас находится юзер, есть альтернативная языковая версия на рассматриваемом языке, даём ссылку на эту альтернативную версию
	  if ($test!==FALSE)
	   {
	    $one_l = str_replace('{ITEM_URL}', $this->url, $one_l);
	   }
	    else // Иначе просто даём ссылку на главную страницу языковой версии рассматриваемого языка
	   {
	    $one_l = str_replace('{ITEM_URL}', 'main', $one_l);
	   }	
	  
	  $all_l.=$one_l;
	 }
	
	// Восстанавливаем информацию о том, на каком языке юзер смотрит страницу + информацию о самой просматриваемой странице
	$this->lng = $save_lng;
	$this->page_info = $save_pi;
	
	// Если у нас есть альтернативные языковые версии для просматриваемой юзером страницы, выводим информацию об этом в главный шаблон ХК
	if ($have_another_languages == TRUE)
	 {
	  $other_langs = str_replace('{LANGUAGES}', $all, $other_langs);
	  $bc = str_replace('{AVAILABLE_IN}', $other_langs, $bc);
	 }
      else // Иначе просто скрываем блок с альтернативными языковыми версиями страницы
	 {
	  $bc = str_replace('{AVAILABLE_IN}', '', $bc);
	 } 
	
	// Помещаем в главный шаблон ХК информацию о всех доступных языковых версиях сайта
	$bc = str_replace('{LANGUAGES}', $all_l, $bc);
	
	return $bc;
   }
  // --- ================================================================================================================   
 
  
 }
// --- ================================================================================================================
?>