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
	$this->templates_dir = $this->application_root.$this->get_config_value('adm_tpls_dir').'/';
	$this->handlers_dir = $this->application_root.$this->get_config_value('adm_handlers_dir');
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
  
  
 }
// --- ================================================================================================================


?>