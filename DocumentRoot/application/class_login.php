<?

// +++ ================================================================================================================
// Класс логина / разлогинивания
class login extends general
 {
  private $user_info;
  private $db;
  
  // +++ =================================================================================================
  function __construct()
   {
    parent::__construct();
	$this->db = new db();	
	if ((isset($_GET['logout']))&&($_GET['logout']=='go'))
     {
      $this->logout();
     }
	  else
	 { 
	  if ((isset($_SESSION['ul_logged']))&&($_SESSION['ul_logged']==TRUE))
	   {
	    $this->fill_user_info();
	   }
	    else
	   {
	    $this->user_info = FALSE;
	   }
     }
   }
  // --- =================================================================================================
 
  // +++ =================================================================================================
  function fill_user_info()
   {
    $this->user_info = array('ul_fio' => $_SESSION['ul_fio'], 'ul_email' => $_SESSION['ul_email'], 'ul_uid' => $_SESSION['ul_uid']);
   }
  // --- =================================================================================================
  
  // +++ =================================================================================================
  public function get_user_info()
   {
    return $this->user_info;
   }
  // --- =================================================================================================
  
  // +++ =================================================================================================
  public function get_login_form($login='', $tpl = 'login/login_form.tpl')
   {
    if (!is_file($this->templates_dir.$tpl))
	 {
	  parent::log_error('Template ['.$tpl.'] not found!', FALSE, 5);
	  die();
	 }
	
    $tpl = file_get_contents($this->templates_dir.$tpl);
	$tpl =  str_replace('{USER_LOGIN}', $login, $tpl);
	
	return $tpl;
   }
  // --- =================================================================================================
 
  // +++ =================================================================================================
  public function do_login()
   {
    $ul = (isset($_POST['ul_login'])) ? trim($_POST['ul_login']) : '';
	$up = (isset($_POST['ul_passw'])) ? trim($_POST['ul_passw']) : '';
	$ur = (isset($_POST['ul_remem'])) ? trim($_POST['ul_remem']) : '';
	
	$ur_c = (isset($_COOKIE['ul_remem'])) ? trim($_COOKIE['ul_remem']) : '----------------------------';

	$ul = preg_replace("/[^a-z\d\-_ \.]/ims", "", $ul);
	$up = sha1($up);
	$ur = ($ur=='on') ? 'on' : '';
	
	$r = $this->db->query("SELECT * from `site_users` where `su_auto`='$ur_c' limit 1");

	if ($r['num']==1)
	 {
	  //echo "X";
          $row_tmp = $r['res']->fetch_assoc();
	  $_SESSION['ul_logged'] = TRUE;
	  $_SESSION['ul_fio'] = $row_tmp['su_fio'];
	  $_SESSION['ul_email'] = $row_tmp['su_email'];
	  $_SESSION['ul_uid'] = $row_tmp['su_uid'];
	  $this->fill_user_info();
	  return TRUE;
	 }
	  else
	 {
	  if (($ul=='')&&($up==''))
	   {
	    return FALSE;
	   }
	  
	  $r = $this->db->query("SELECT * from `site_users` where `su_login`='$ul' AND `su_password`='$up' limit 1");
	  //echo "SELECT * from `site_users` where `su_login`='$ul' AND `su_password`='$up' limit 1";
	  //echo $r['num'];
	  if ($r['num']==1)
	   {
            $row_tmp = $r['res']->fetch_assoc();
	    $_SESSION['ul_logged'] = TRUE;
	    $_SESSION['ul_fio'] = $row_tmp['su_fio'];
	    $_SESSION['ul_email'] = $row_tmp['su_email'];
		$_SESSION['ul_uid'] = $row_tmp['su_uid'];
		
		if ($ur=='on')
		 {
		  $uid = $_SESSION['ul_uid'];
		  $rem = sha1($_SESSION['ul_email'].time().rand(100000,999999));
		  setcookie('ul_remem', $rem, time()+1209600);
		  $this->db->query("UPDATE `site_users` set `su_auto`='$rem' where `su_uid`='$uid' limit 1");
		 }
		$this->fill_user_info();
		return TRUE; 
	   }
	    else
	   {
	    return FALSE;
	   }	
	 } 
	
   }
  // --- =================================================================================================
 
  // +++ =================================================================================================
  public function logout()
   {
	setcookie('ul_remem', '-----------', time()-1209600);
	if (isset($_SESSION['ul_uid'])) $uid = $_SESSION['ul_uid']; else $ul_uid=0;
	$this->db->query("UPDATE `site_users` set `su_auto`='' where `su_uid`='$uid' limit 1");
	unset($_SESSION);
	session_destroy();
	$this->user_info = FALSE;
	
   }
  // --- =================================================================================================
 }
// --- ================================================================================================================


?>