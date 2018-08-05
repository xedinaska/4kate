<?

// +++ ================================================================================================================
// ������� ������� �������� �������.
// � ���� ������ ������������ ������, ���������� � ���������, ������� ����������� �� ���� ��������� ������� (���� �� ��������).
class general
 {
  // ��������� ���������������� ������
  const DEBUG_LEVEL = 5; // 0 -- ��������� ��������� ����� ������ �� �����; ��� ���� � ���� ������ ����� ���������� ��������, ����
                         // ��������� ERROR_LOGGING_MODE ������������ ����� ��� �����
  const ERROR_LOGGING_MODE = FALSE; // FALSE -- �������� ��������� �� ������� �� �����, string -- ��� ����� ��� ������ ������
  
  const USE_DB_CACHE = TRUE; // ������������ �� ����������� �������� � ��
  
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
  // ������������� ������
  // $error_text -- ����� ��������� �� ������
  // $error_number -- ����� ������
  // $need_debug_level -- ���������� ����������� ������� ������� ��� ������ ������ �� ������
  // $file -- ���� �������� ������� �� ������ (����� ��� ����)
  public function log_error($error_text, $error_number=FALSE, $need_debug_level=1, $file=general::ERROR_LOGGING_MODE)
   {
    // ���� ����� ������ �� �����, ����� � ��� ����� 'no number specified'
	if ($error_number===FALSE) $error_number='no number specified';
	
	// �������� FALSE ���������� $file ������� � ���, ��� ����� ��������� �� ������ ����� ����������� �� �����
	if ($file===FALSE)
	 {
	  // ����������� ��� ����������� ���� ������ "��������� ������� �������" ������ ���� >= ��������� ���������� DEGUG_LEVEL
	  if (general::DEBUG_LEVEL>=$need_debug_level)
	   {
	    echo 'Error: ['.$error_text.'], ['.$error_number.']';
	   }	
	 }
	  else // ���� ���������� $file �� ����� FALSE, � �������� ���������� ��� ��� �����, � ������� ������������ �������� ��������� ������
	 {
	  @file_put_contents($file, date('Y.m.d H:i:s').' ['.$error_text.'], ['.$error_number."]\n", FILE_APPEND);
	 }
   }
  // --- =============================================================================================== 
  
  // +++ =============================================================================================== 
  // ���������� �������� ��������� �� ����������������� ����� (config.php)
  // $param_name -- ��� ���������
  // $default_value -- �������� �� ��������� (�� ������, ���� �������� �� ����� � ���������������� �����)
  // ������� ���������� FALSE � ������ ������ ������ � ���������������� ������, ������� ������� ��������
  // ����������������� ����� �� ����� ����� �������� FALSE, �.�. �� �� ������ �������� ������ ������ �
  // ������ �������� "���������� ��������" FALSE
  public function get_config_value($param_name, $default_value=FALSE)
   {
    // ���� ����� �������� ���� � ���������������� �����...
	if (isset($GLOBALS['config'][$param_name]))
	 {
	  // ... ���������� ��� ��������.
	  return $GLOBALS['config'][$param_name];
	 }
	  // ���� ��������� ���...
	  else
	 {
	  // ���� ��� �������� �� ���������...
	  if ($default_value===FALSE)
	   {
	    // ... �������� ������
		log_error('No config value ['.$param_name.'] defined!', FALSE, 5);
	    return FALSE; 		
	   }
	    // ���� ���� �������� �� ���������.
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
    // ��������� �� ������� ���� ��������
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