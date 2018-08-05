<?

// +++ ================================================================================================================
// ����� ���������� ����� �����
class sitemap extends general
 {
  // ������� ��� �������� ������, ��������� ������, �������� ������
  private $before;
  private $after;
  private $listitem;
  
  // ��������� ��������� ������ ��� ������ � ��
  private $db;
  
  // +++ =========================================================================================================
  // ������ �������
  function __construct($before='sitemap/before.tpl', $after='sitemap/after.tpl', $listitem='sitemap/listitem.tpl')
   {
    parent::__construct();
	
	if (!is_file($this->templates_dir.$before))
	 {
	  parent::log_error('Template ['.$before.'] not found!', FALSE, 5);
	  die();
	 }
	
	if (!is_file($this->templates_dir.$after))
	 {
	  parent::log_error('Template ['.$after.'] not found!', FALSE, 5);
	  die();
	 }
	
	if (!is_file($this->templates_dir.$listitem))
	 {
	  parent::log_error('Template ['.$listitem.'] not found!', FALSE, 5);
	  die();
	 } 
	
	$this->before = file_get_contents($this->templates_dir.$before);
	$this->after = file_get_contents($this->templates_dir.$after);
	$this->listitem = file_get_contents($this->templates_dir.$listitem);
	
	$this->db = new db();
   }
  // --- =========================================================================================================
   
  // +++ =========================================================================================================
  // ������ ����� �����
  // $parent -- ������������� ��������, ����������� ������� �� ����������� �� ������ ������ �������
  // $preurl -- ���������� ��� ���������� �������� URL'�, ���������� �� URL'�� ���� ��������� ��������
  public function get_sitemap($lng, $parent=0, $preurl='')
   {
    $all = '';
	$r = $this->db->query("SELECT `p_uid`, `p_menu_name`, `p_url` from `pages` where `p_in_sitemap`='Y' AND `p_parent`='$parent' AND `p_lang`='$lng' order by `p_ord` asc");
	while ($row = $r['res']->fetch_assoc()) 
	 {
	  $one = $this->listitem;
	  $one = str_replace('{ITEM_NAME}', $row['p_menu_name'], $one);
	  $one = str_replace('{ITEM_URL}', $preurl.$row['p_url'], $one);
	  
	  $subpages = $this->get_sitemap($lng, $row['p_uid'], $preurl.$row['p_url'].'/');
	  if ($subpages!='')
	   {
	    $one = str_replace('{SUBPAGES}', $this->before.$subpages.$this->after, $one);
	   }
	    else
	   {
	    $one = str_replace('{SUBPAGES}', '', $one);
	   }	
	  
	  $all .= $one;
	 }
	
	return $all;
   }
  // --- =========================================================================================================
   
 }
// --- =========================================================================================================

?>