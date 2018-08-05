<?

// +++ ================================================================================================================
// Верхний уровень иерархии классов.
// В этом классе определяются методы, переменные и константы, которые понадобятся во всех остальных классах (ниже по иерархии).
class general
 {
  // Настройки протоколирования ошибок
  const DEBUG_LEVEL = 5; // 0 -- полностью отключить вывод ошибок на экран; при этом в файл ошибки будут продолжать писаться, если
                         // константа ERROR_LOGGING_MODE представляет собой имя файла
  const ERROR_LOGGING_MODE = FALSE; // FALSE -- выводить сообщения об ошибках на экран, string -- имя файла для вывода ошибок
  
  const USE_DB_CACHE = TRUE; // Использовать ли кэширование запросов к БД
  
  const TEMPLATE_FILE_PH = '/{FILE\s*=\s*[\'\"](.*)[\'\"]}/imsU';
  const TEMPLATE_FILE_FLC = '/{FL_CONFIG\s*=\s*[\'\"](.*)[\'\"]}/imsU';
  const TEMPLATE_FILE_DBC = '/{DB_CONFIG\s*=\s*[\'\"](.*)[\'\"]}/imsU';
  const TEMPLATE_FILE_DNC = '/{DN\s*=\s*[\'\"](.*)[\'\"]}/imsU';
  
  public $application_root;
  public $templates_dir;
  public $handlers_dir;
  public $application_web;
  
  public $db_config=array();
  
  public $lng;
  
  private $db;
  
  // +++ ===============================================================================================
  function __construct()
   {
    $this->lng = $GLOBALS['lng'];
	
	$this->db = new db();
	$r = $this->db->query("SELECT `config`.`c_name`, `config_values`.`cv_value` FROM `config` join `config_values` WHERE `config`.`c_uid` = `config_values`.`cv_parent` and `config_values`.`cv_lang`='$this->lng'");
	if ($r['err_n']!==0)
	 {
	  $this->log_error('Error in query while extracting database based config!', $r['err_n'], 5);
	 }
	  else
	 {
          while ($row = $r['res']->fetch_assoc()) 
	   {
	    $this->db_config[$row['c_name']] = $row['cv_value'];
	   }
	 }
		
	$stylename = $this->get_db_config('stylename');
	
    $this->application_root = str_replace('[DOCROOT]', $_SERVER['DOCUMENT_ROOT'], $this->get_config_value('appl_dir'));
	$this->templates_dir = $this->application_root.$this->get_config_value('tpls_dir').$stylename.'/';
	$this->handlers_dir = $this->application_root.$this->get_config_value('handlers_dir');
	$this->application_web = $this->get_config_value('appl_webdir');
	$this->application_web_templates = $this->get_config_value('appl_webdir').$this->get_config_value('tpls_dir').$stylename.'/';;
   }
  // --- ===============================================================================================
  
  function __destruct()
   {
    unset($this->db);
   }
  
  // +++ ===============================================================================================
  // Протоколирует ошибки
  // $error_text -- текст сообщения об ошибке
  // $error_number -- номер ошибки
  // $need_debug_level -- минимально необходимый уровень толадки для показа ошибки на экране
  // $file -- куда выводить сообщен об ошибке (экран или файл)
  public function log_error($error_text, $error_number=FALSE, $need_debug_level=1, $file=general::ERROR_LOGGING_MODE)
   {
    // Если номер ошибки не задан, пишем в лог фарзу 'no number specified'
	if ($error_number===FALSE) $error_number='no number specified';
	
	// Значение FALSE переменной $file говорит о том, что вывод сообщения об ошибке нужно осуществить на экран
	if ($file===FALSE)
	 {
	  // Пепреданный для отображения этой ошибки "необхомый уровень отладки" должен быть >= заданному константой DEGUG_LEVEL
	  if (general::DEBUG_LEVEL>=$need_debug_level)
	   {
	    echo 'Error: ['.$error_text.'], ['.$error_number.']';
	   }	
	 }
	  else // Если переменная $file не равна FALSE, её значение трактуется как имя файла, в который записывается протокол возникших ошибок
	 {
	  @file_put_contents($file, date('Y.m.d H:i:s').' ['.$error_text.'], ['.$error_number."]\n", FILE_APPEND);
	 }
   }
  // --- =============================================================================================== 
  
  // +++ =============================================================================================== 
  // Возвращает значение параметра из конфигурационного файла (config.php)
  // $param_name -- имя параметра
  // $default_value -- значение по умолчанию (на случай, если параметр не задан в конфигурационном файле)
  // Функция возвращает FALSE в случае ОШИБКИ работы с конфигурационным файлом, поэтому никакой параметр
  // конфигурационного файла НЕ МОЖЕТ иметь значение FALSE, т.к. мы не сможем отличить случай ошибки и
  // случай возврата "легального значения" FALSE
  public function get_config_value($param_name, $default_value=FALSE)
   {
    // Если такой параметр есть в конфигурационном файле...
	if (isset($GLOBALS['config'][$param_name]))
	 {
	  // ... возвращаем его значение.
	  return $GLOBALS['config'][$param_name];
	 }
	  // Если параметра нет...
	  else
	 {
	  // Если нет значения по умолчанию...
	  if ($default_value===FALSE)
	   {
	    // ... логируем ошибку
		log_error('No config value ['.$param_name.'] defined!', FALSE, 5);
	    return FALSE; 		
	   }
	    // Если ЕСТЬ значение по умолчанию.
		else
	   {
		return $default_value;		
	   } 	
	 } 
   }
  // --- =============================================================================================== 
  
  // +++ =============================================================================================== 
  public function get_db_config($name)
   {
    if (isset($this->db_config[$name]))
	 {
	  return $this->db_config[$name];
	 }
	  else
	 {
	  $this->log_error('DB config param ['.$name.'] not found!', FALSE, 5);
	 } 
   }
  // --- ===============================================================================================  
  
  // +++ ===============================================================================================
  public function pagination($cur_page, $pages_count, $templates = array('pgn' => 'pagination/pagination.tpl', 'li' => 'pagination/pagination_li.tpl', 'li_nl' => 'pagination/pagination_li_nl.tpl', 'prev' => 'pagination/pagination_prev.tpl', 'next' => 'pagination/pagination_next.tpl', 'prev_nl' => 'pagination/pagination_prev_nl.tpl', 'next_nl' => 'pagination/pagination_next_nl.tpl', 'separator' => 'pagination/pagination_separator.tpl'))
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
	 
	$pgn = file_get_contents($this->templates_dir.$templates['pgn']); 
	$li = file_get_contents($this->templates_dir.$templates['li']); 
	$li_nl = file_get_contents($this->templates_dir.$templates['li_nl']); 
	$prev = file_get_contents($this->templates_dir.$templates['prev']); 
	$next = file_get_contents($this->templates_dir.$templates['next']); 
	$prev_nl = file_get_contents($this->templates_dir.$templates['prev_nl']); 
	$next_nl = file_get_contents($this->templates_dir.$templates['next_nl']); 
	$separator = file_get_contents($this->templates_dir.$templates['separator']); 
	
	$all = '';
	for ($i=1; $i<=$pages_count; $i++)
	 {
	  if (($i>=$cur_page-2)&&($i<=$cur_page+2))
	   {
	    if ($i!=$cur_page)
		 {
		  $all.=str_replace('{PAGE_NUM}', $i, $li);
		 }
          else
		 {
		  $all.=str_replace('{PAGE_NUM}', $i, $li_nl);
		 } 
	   }	
	 }
	
	if ($cur_page>3)
	 {
	  $all = str_replace('{PAGE_NUM}', 1, $li).$separator.$all;
	 } 
	if ($cur_page<$pages_count-3)
	 {
	  $all = $all.$separator.str_replace('{PAGE_NUM}', $pages_count, $li);
	 } 
	
	if ($cur_page>1)
	 {
	  $pgn = str_replace('{PREV_LINK}', str_replace('{PAGE_PREV}', $cur_page-1, $prev), $pgn); 
	 }
	  else
	 {
	  $pgn = str_replace('{PREV_LINK}', $prev_nl, $pgn); 
	 } 
	
	if ($cur_page<$pages_count)
	 {
	  $pgn = str_replace('{NEXT_LINK}', str_replace('{PAGE_NEXT}', $cur_page+1, $next), $pgn); 
	 }
	  else
	 {
	  $pgn = str_replace('{NEXT_LINK}', $next_nl, $pgn); 
	 } 
	
	$pgn = str_replace('{PAGES_LINKS}', $all, $pgn);
	return $pgn;
   }
  // --- ===============================================================================================  
  
 }
// --- ================================================================================================================


?>