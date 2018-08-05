<?

// +++ ================================================================================================================ 
// Класс, отвечающий за операции с базами данных
class db extends general
 {
  private $lnk; // Идентификатор соединения с СУБД
  
  private $r_num_rows; // Количество рядов, которое вернул запрос
  private $r_num_fileds; // Количество полей, которое вернул запрос
  private $r_num_aff_rows; // Количество рядов, которое ЗАТРОНУЛ запрос
  private $r_err_text; // Текст сообщения об ошибке MySQL
  private $r_err_num; // Номер ошибки MySQL
  private $r_result; // Результат выполнения запроса (ресурс)
  private $r_time; // Время выполнения запроса
 
  private $cache; // Кэш запросов
 
  // +++ ===============================================================================================
  function __construct($db_login=db_login, $db_password=db_password, $db_host=db_host, $db_database=db_database)
   {
    // Устанавливаем соединение с СУБД
    $this->lnk = new mysqli(db_host, db_login, db_password, db_database);
	
    // Проверяем, удалось ли установить соединение с СУБД
    if ($this->lnk->connect_error)
    {
     $this->log_error($this->lnk->connect_error, $this->lnk->connect_errno, 5);
    }
	
    // Говорим MySQL, что с нами общаться надо в UTF8
    if (!$this->lnk->set_charset("utf8"))
    {
     $this->log_error($this->lnk->error, $err, 5);
    }

    // Инициализируем кэш пустым массивом
    $this->cache = array(); 
   }
  // --- ===============================================================================================
  
  // +++ ===============================================================================================
  function __destruct()
   {
    $this->lnk->close();
   }
  // --- ===============================================================================================


  // +++ ===============================================================================================
  // Выполняет экранирование строк
  public function real_escape_string($str)
   {
    return $this->lnk->real_escape_string($str);
   }
  // --- ===============================================================================================

  
  // +++ ===============================================================================================
  // Выполняет запрос к БД
  // $query -- запрос
  // $recache -- указание на то, что надо повторно выполнить запрос напрямую к БД и обновить его данные в кэше
  public function query($query, $recache=FALSE)
   {
    if (parent::USE_DB_CACHE)
	 {
	  // Если запрос содержит одно из ключевых слов языука SQL, приводящих к модификации данных...
	  if ((stripos($query, 'insert')!==FALSE)||
	      (stripos($query, 'update')!==FALSE)||
	      (stripos($query, 'delete')!==FALSE)||
	      (stripos($query, 'replace')!==FALSE)||
	      (stripos($query, 'truncate')!==FALSE))
		   {
		    // Сбрасываем кэш
		    $this->cache = array();
		   }
	
	  // Если в кэше уже есть такой запрос...
	  if ((isset($this->cache[$query]))&&($recache===FALSE))
	   {
        // ... сразу возвращаем результат.
 	    return $this->cache[$query];
	   }
	
	  // Если мы добрались досюда, запроса в кэше не было
	 }
	
	// Фиксируем время начала выполнения запроса, выполняем запрос, фиксируем время завершения выполнения запроса
	$t1 = microtime(TRUE);
	$r = $this->lnk->query($query);
	$t2 = microtime(TRUE);
	
	// Если в процессе выполнения запроса возникла ошибка, протоколируем её
	if (0!==($err=$this->lnk->errno))
	 {
	  $this->log_error($this->lnk->error, $err, 5);
	 }
	
	// Формируем набор результирующих данных (см. комментарии в секции определения переменных)
	$this->r_num_rows=@$r->num_rows;
	$this->r_num_fields=@$r->field_count;
	$this->r_num_aff_rows=@$this->lnk->affected_rows;
	$this->r_err_text=@$this->lnk->error;
	$this->r_err_num=@$this->lnk->errno;
	$this->r_result=$r;
	$this->r_time=$t2-$t1;
    
	// Формируем массив результирующих данных
	$res = array
	       (
		        'num'=>$this->r_num_rows,
			'num_f'=>$this->r_num_fields,
			'aff'=>$this->r_num_aff_rows,
			'err_t'=>$this->r_err_text,
			'err_n'=>$this->r_err_num,
			'res'=>$this->r_result,
			'time'=>$this->r_time
		   );
	
	 if (parent::USE_DB_CACHE)
	 {
	  // Складываем массив результирующих данных в кэш
	  $this->cache[$query] = $res;
	 } 
	
	// Возвращаем массив результирующих данных
	return $res;
   }
  // --- ===============================================================================================
 }
// --- ================================================================================================================



?>