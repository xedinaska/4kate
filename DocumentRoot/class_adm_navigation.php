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
	  $r = $this->db->query("SELECT * from `adm_pages` where `p_parent`='$parent' AND `p_lang`='$this->lng' AND `p_url`='$part'");
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
	$this->page_info['name'] = $row['p_name'];
	$this->page_info['menu_name'] = $row['p_menu_name'];
	$this->page_info['in_menu'] = $row['p_in_menu'];
	$this->page_info['text'] = $row['p_text'];
	$this->page_info['file'] = $row['p_file'];
	$this->page_info['template'] = $row['p_template'];
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
	$r = $this->db->query("SELECT `p_uid`, `p_menu_name`, `p_url` from `adm_pages` where `p_lang`='$this->lng' AND `p_parent`='$parent' AND `p_in_menu`='Y' order by `p_ord` asc");
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
  
      
  
 }
// --- ================================================================================================================
?>