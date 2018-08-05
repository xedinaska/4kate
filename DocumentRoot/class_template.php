<?

// +++ ================================================================================================================
// Класс обработки шаблонов
class template extends general
 {
  private $tpl; // Тело обрабатываемого шаблона
  private $tpls_tree = array(); // Переменная для хранения информации о подключённых шаблонах (для избежания кольцевого подключения)
  private $level = 1; // Уровень вложенности шаблона
  private $dn_config = array(); // Массив для хранения динамических переменных
  
  // +++ =========================================================================================================
  function __construct($tpl_name, $lng)
   {
	parent::__construct();
	$this->read_main_tpl($tpl_name);
   }
  // --- =========================================================================================================
  
  // +++ =========================================================================================================
  function read_main_tpl($tpl_name)
   {
	$tpl_full_name = $this->templates_dir.$tpl_name;
	if (!is_file($tpl_full_name))
	 {
	  parent::log_error('Template ['.$tpl_name.'] not found!', FALSE, 5);
	 }
	  else
	 {
	  $this->tpl = file_get_contents($tpl_full_name);
	  $this->tpls_tree[$tpl_name]=0;
	 }
   }
  // --- =========================================================================================================
  
  // +++ =========================================================================================================
  private function include_files($x)
   {
    // Имя найденного файла подшаблона хранится в первом элементе массива
	$tpl_name = $x[1];
	$tpl_full_name = $this->templates_dir.$tpl_name;
	
	// Если в массиве "дерева подключения шаблонов" уже есть такой шаблон, причём
	// он был подключён на меньшем уровне. чем тот, на котором мы находимся сейчас,
	// запрещаем "кольцевое подключение" шаблонов.
	if ((isset($this->tpls_tree[$tpl_name]))&&($this->tpls_tree[$tpl_name]<$this->level))
	 {
	  parent::log_error('Circle template inclusion ['.$tpl_name.']!', FALSE, 5);
	  return '';
	 }
	
	if (!is_file($tpl_full_name))
	 {
	  parent::log_error('Template ['.$tpl_name.'] not found!', FALSE, 5);
	 }
	  else
	 {
	  $this->tpls_tree[$tpl_name]=$this->level;
	  return file_get_contents($tpl_full_name);
	 } 
   }
  // --- =========================================================================================================
  
  // +++ =========================================================================================================
  private function include_file_config($x)
   {
    // Имя подключаемой переменной
	$var_name = $x[1];
	
	if (!isset($GLOBALS['config_data'][$var_name]))
	 {
	  parent::log_error('File config param ['.$var_name.'] not found!', FALSE, 5);
	 }
	  else
	 {
	  return $GLOBALS['config_data'][$var_name];
	 } 
   }
  // --- =========================================================================================================  
  
  // +++ =========================================================================================================
  private function include_db_config($x)
   {
    // Имя подключаемой переменной
	$var_name = $x[1];
	
	if (!isset($this->db_config[$var_name]))
	 {
	  parent::log_error('DB config param ['.$var_name.'] not found!', FALSE, 5);
	  return (str_replace(array('{', '}'), array('&#x7B;','&#x7D;'), $x[0]));
	 }
	  else
	 {
	  return $this->db_config[$var_name];
	 } 
   }
  // --- =========================================================================================================  
  
  // +++ =========================================================================================================
  private function include_dn_config($x)
   {
    // Имя подключаемой переменной
	$var_name = $x[1];
	
	if (!isset($this->dn_config[$var_name]))
	 {
	  parent::log_error('Dynamic var ['.$var_name.'] not found!', FALSE, 5);
	  return (str_replace(array('{', '}'), array('&#x7B;','&#x7D;'), $x[0]));
	  //return '&#x7B;DN='.$var_name.'&#x7D;';
	 }
	  else
	 {
	  return $this->dn_config[$var_name];
	 } 
   }
  // --- =========================================================================================================  
  
  // +++ =========================================================================================================
  public function assign($var_name, $var_value='')
   {
    if (is_string($var_name))
	 {
	  $this->dn_config[$var_name] = $var_value;
	  return TRUE;
	 }
	  elseif (is_array($var_name))
	   {
	    foreach ($var_name as $k => $v)
		 {
		  $this->dn_config[$k] = $v;
		 }
	    return TRUE;
	   }
	
	return FALSE;   
   }
  // --- =========================================================================================================
  
  // +++ =========================================================================================================
  public function process()
   {
    
	// Подставляем вложенные подшаблоны
	while (preg_match(parent::TEMPLATE_FILE_PH, $this->tpl))
	 {
	  $this->tpl = preg_replace_callback(parent::TEMPLATE_FILE_PH, 'self::include_files', $this->tpl);
	  $this->level++;
	 }
	
	$this->tpl = preg_replace_callback(parent::TEMPLATE_FILE_FLC, 'self::include_file_config', $this->tpl);
	
	while ((preg_match(parent::TEMPLATE_FILE_DBC, $this->tpl))||(preg_match(parent::TEMPLATE_FILE_DNC, $this->tpl)))
	 {
	  $this->tpl = preg_replace_callback(parent::TEMPLATE_FILE_DNC, 'self::include_dn_config', $this->tpl);
	  $this->tpl = preg_replace_callback(parent::TEMPLATE_FILE_DBC, 'self::include_db_config', $this->tpl);
     } 
   }
  // --- =========================================================================================================
  
  // +++ =========================================================================================================
  public function get_final_result($remove_comments=FALSE, $compress=FALSE)
   { 
    if ($remove_comments)
	 {
	  $this->tpl=preg_replace("/<!--[^\[].*-->/Uims",'',$this->tpl);
	 }
	 
    if ($compress)
	 {
	  $this->tpl=str_replace(array('  ', "\n", "\r", "\t"), array(' ', '', '', ' '), $this->tpl);
	 }	 
	
	return $this->tpl;
   }
  // --- =========================================================================================================
  
  // +++ =========================================================================================================
  public function subst_tpl($tpl)
   { 
	$this->read_main_tpl($tpl);
   }
  // --- =========================================================================================================
  
 }


?>