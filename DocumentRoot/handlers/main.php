<?

require_once($general -> application_root.'class_banners.php');
$banners = new banners();

// ���� ���� ������� �� ������, ������������ ������� �� URL'� ����� ������
if ($bannerclick!=0)
 {
  $banners->banner_click($bannerclick);
 }

// ���� �� ��������� ������, ������ ���� �� ������ �� ������, � ���� ������ �������� �������� (� ��������) 
$template->assign('banners', $banners->get_banners($general->get_db_config('banners_on_main')));


?>