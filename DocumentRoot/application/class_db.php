<?

// +++ ================================================================================================================ 
// �����, ���������� �� �������� � ������ ������
class db extends general
 {
  private $lnk; // ������������� ���������� � ����
  
  private $r_num_rows; // ���������� �����, ������� ������ ������
  private $r_num_fileds; // ���������� �����, ������� ������ ������
  private $r_num_aff_rows; // ���������� �����, ������� �������� ������
  private $r_err_text; // ����� ��������� �� ������ MySQL
  private $r_err_num; // ����� ������ MySQL
  private $r_result; // ��������� ���������� ������� (������)
  private $r_time; // ����� ���������� �������
 
  private $cache; // ��� ��������
 
  // +++ ===============================================================================================
  function __construct($db_login=db_login, $db_password=db_password, $db_host=db_host, $db_database=db_database)
   {
    // ������������� ���������� � ����
    $this->lnk = new mysqli(db_host, db_login, db_password, db_database);
	
    // ���������, ������� �� ���������� ���������� � ����
    if ($this->lnk->connect_error)
    {
     $this->log_error($this->lnk->connect_error, $this->lnk->connect_errno, 5);
    }
	
    // ������� MySQL, ��� � ���� �������� ���� � UTF8
    if (!$this->lnk->set_charset("utf8"))
    {
     $this->log_error($this->lnk->error, $err, 5);
    }

    // �������������� ��� ������ ��������
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
  // ��������� ������������� �����
  public function real_escape_string($str)
   {
    return $this->lnk->real_escape_string($str);
   }
  // --- ===============================================================================================

  
  // +++ ===============================================================================================
  // ��������� ������ � ��
  // $query -- ������
  // $recache -- �������� �� ��, ��� ���� �������� ��������� ������ �������� � �� � �������� ��� ������ � ����
  public function query($query, $recache=FALSE)
   {
    if (parent::USE_DB_CACHE)
	 {
	  // ���� ������ �������� ���� �� �������� ���� ������ SQL, ���������� � ����������� ������...
	  if ((stripos($query, 'insert')!==FALSE)||
	      (stripos($query, 'update')!==FALSE)||
	      (stripos($query, 'delete')!==FALSE)||
	      (stripos($query, 'replace')!==FALSE)||
	      (stripos($query, 'truncate')!==FALSE))
		   {
		    // ���������� ���
		    $this->cache = array();
		   }
	
	  // ���� � ���� ��� ���� ����� ������...
	  if ((isset($this->cache[$query]))&&($recache===FALSE))
	   {
        // ... ����� ���������� ���������.
 	    return $this->cache[$query];
	   }
	
	  // ���� �� ��������� ������, ������� � ���� �� ����
	 }
	
	// ��������� ����� ������ ���������� �������, ��������� ������, ��������� ����� ���������� ���������� �������
	$t1 = microtime(TRUE);
	$r = $this->lnk->query($query);
	$t2 = microtime(TRUE);
	
	// ���� � �������� ���������� ������� �������� ������, ������������� �
	if (0!==($err=$this->lnk->errno))
	 {
	  $this->log_error($this->lnk->error, $err, 5);
	 }
	
	// ��������� ����� �������������� ������ (��. ����������� � ������ ����������� ����������)
	$this->r_num_rows=@$r->num_rows;
	$this->r_num_fields=@$r->field_count;
	$this->r_num_aff_rows=@$this->lnk->affected_rows;
	$this->r_err_text=@$this->lnk->error;
	$this->r_err_num=@$this->lnk->errno;
	$this->r_result=$r;
	$this->r_time=$t2-$t1;
    
	// ��������� ������ �������������� ������
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
	  // ���������� ������ �������������� ������ � ���
	  $this->cache[$query] = $res;
	 } 
	
	// ���������� ������ �������������� ������
	return $res;
   }
  // --- ===============================================================================================
 }
// --- ================================================================================================================



?>