<?

$check = $login->do_login();
$user_info = $login->get_user_info();

if (count($_POST)==0)
 {
  if ($user_info!=FALSE)
   {
    $template->assign('login_message', 'Вы уже авторизованы');
    $template->assign('login_form', '');
    $template->assign('logout_url', '<a href="{DN="appl_webdir"}{DN="lng"}/{DN="url"}/?logout=go">Выйти</a>');
   }
    else
   {	
    $template->assign('login_form', $login->get_login_form());
    $template->assign('ul_login', '');
    $template->assign('login_message', '');
    $template->assign('logout_url', '');
   }	
 }
  else
   {
    if ($check == TRUE)
     {
      $template->assign('login_form', '');
	  $template->assign('login_message', 'Спасибо, вы авторизованы!');
	  $template->assign('logout_url', '<a href="{DN="appl_webdir"}{DN="lng"}/{DN="url"}/?logout=go">Выйти</a>');
     }
	  else
	 {
	  $template->assign('login_message', 'Неверные логин и/или пароль!');
	  $ul = (isset($_POST['ul_login'])) ? preg_replace("/[^а-яa-z\d\-_ \.]/imsu", "", trim($_POST['ul_login'])) : '';
	  $template->assign('login_form', $login->get_login_form($ul));
	  $template->assign('logout_url', '');
	 } 
   } 
  


?>