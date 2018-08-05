<?

require_once($general -> application_root.'class_sitemap.php');
$sitemap = new sitemap();
$template->assign('sitemap', $sitemap->get_sitemap($lng));


?>