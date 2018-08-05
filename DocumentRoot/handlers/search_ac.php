<?

$db = new db();
$search = new search();

$sr = (isset($_GET['searchtext'])) ? $_GET['searchtext'] : '';
$sr = $search->get_search_request($sr);

if ($sr!='')
 {
  $r = $db->query("SELECT * from `search_ac` where `search_text` like '$sr%' order by `search_results` desc limit 10");
  if ($r['num']==0)
   {
    die();
   }
  
  $template->assign('item_size', $r['num']);
  $items = '';
  while ($row = $r['res']->fetch_assoc()) 
   {
    $items.='<option value="'.$row['search_text'].'">'.$row['search_text'].' ('.$row['search_results'].')</option>';
   }
  $template->assign('item_items', $items); 
 }
  else
 {
  die();
 } 
 
?>