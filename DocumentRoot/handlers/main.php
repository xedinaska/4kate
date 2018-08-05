<?

require_once($general -> application_root.'class_banners.php');
$banners = new banners();

// ≈сли юзер килкнул по банеру, осуществл€ем переход по URL'у этого банера
if ($bannerclick!=0)
 {
  $banners->banner_click($bannerclick);
 }

// ≈сли мы добрались досюда, значит юзер не кликал по банеру, и надо просто показать страницу (с банерами) 
$template->assign('banners', $banners->get_banners($general->get_db_config('banners_on_main')));


?>