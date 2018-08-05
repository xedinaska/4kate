<?

// +++ ================================================================================================================
// Реализует сепцифичные для отображения новостей методы
class news extends general
 {
  
  // +++ ===============================================================================================
  function __construct()
   {
    parent::__construct();
	$this->db = new db();
   }
  // --- =============================================================================================== 
  
  // +++ ===============================================================================================  
  function __destruct()
   {
    unset($this->db);
   } 
  // --- ===============================================================================================
 
  // +++ ===============================================================================================
  public function get_adm_news_rubrics()
   {
    $r = $this->db->query("SELECT `nr_uid`, `nr_name`, `nr_ord` from `news_rubrics` where `nr_lng`='$this->lng' order by `nr_ord` asc");
	
	
	$all='<table>';
	
	$all.='<form action="/admin/'.$this->lng.'/news/" method="post">';
	$all.='<input type="hidden" name="action" value="order_go" />';
	
	$all.='<tr>';
	$all.=' <td colspan="4"><a href="/admin/'.$this->lng.'/news/?action=add">Добавить</a></td>';
	$all.='</tr>';
	
	$all.='<tr>';
	$all.=' <td>Порядок</td>';
	$all.=' <td>Имя</td>';
	$all.=' <td>Редактировать</td>';
	$all.=' <td>Удалить</td>';
	$all.='</tr>';
	
	if ($r['num']>0)
	 {
	  $all.='<tr>';
	  $all.=' <td colspan="4"><input type="submit" name="order_go" value="Пересортировать" /></td>';
	  $all.='</tr>';
	 } 
	
	while ($row = $r['res']->fetch_assoc()) 
	 {
	  $all.='<tr>';
	  $all.=' <td>';
	  $all.='  <input type="text" name="ord['.$row['nr_uid'].']" value="'.$row['nr_ord'].'" />';
	  $all.=' </td>';
	  $all.=' <td>';
	  $all.='  <a href="/admin/'.$this->lng.'/news/?rubric='.$row['nr_uid'].'">'.$row['nr_name'].'</a>';
	  $all.=' </td>';
	  $all.=' <td>';
	  $all.='  <a href="/admin/'.$this->lng.'/news/?rubric='.$row['nr_uid'].'&action=edit">Редактировать</a>';
	  $all.=' </td>';
	  $all.=' <td>';
	  $all.='  <a href="/admin/'.$this->lng.'/news/?rubric='.$row['nr_uid'].'&action=del_go" onclick="return confirm(\'Удалить?\')">Удалить</a>';
	  $all.=' </td>';
	  $all.='</tr>';
	 }
	
	
	$all.='</form>';
	$all.='</table>';
	return $all;
   }
  // --- ===============================================================================================
  
  
  // +++ ===============================================================================================
  // Строит форму добавления или редактирования рубрики новостей
  public function get_news_rubric_form($action, $message='', $nr_uid=0, $nr_name='', $nr_lng='', $nr_url='', $nr_ord=0)
   {
    if ($nr_lng=='')
	 {
	  $nr_lng = $this->lng;
	 }
	 
	if ($nr_ord==0) 
	 {
	  $r = $this->db->query("SELECT `nr_ord` from `news_rubrics` where `nr_lng`='$this->lng' order by `nr_ord` desc limit 1");
	  if ($r['num'])
	   {
		$row_tmp = $r['res']->fetch_assoc();
		$nr_ord = $row_tmp['nr_ord'] + 10;
	   }
	 }
	
	$all='<table>';
	
	$all.='<form action="/admin/'.$this->lng.'/news/" method="post">';
	$all.='<input type="hidden" name="action" value="'.$action.'_go" />';
	$all.='<input type="hidden" name="nr_uid" value="'.$nr_uid.'" />';
	
	$all.='<tr>';
	$all.=' <td align="right">&nbsp;</td>';
	$all.=' <td align="left">'.$message.'</td>';
	$all.='</tr>';
	
	$all.='<tr>';
	$all.=' <td align="right">Имя</td>';
	$all.=' <td align="left"><input type="text" name="nr_name" value="'.$nr_name.'" /></td>';
	$all.='</tr>';
	
	$all.='<tr>';
	$all.=' <td align="right">URL</td>';
	$all.=' <td align="left"><input type="text" name="nr_url" value="'.$nr_url.'" /></td>';
	$all.='</tr>';
	
	$all.='<tr>';
	$all.=' <td align="right">Порядок</td>';
	$all.=' <td align="left"><input type="text" name="nr_ord" value="'.$nr_ord.'" /></td>';
	$all.='</tr>';	

	$all.='<tr>';
	$all.=' <td align="right">Язык</td>';
	$all.=' <td align="left">';
	$all.=' <select size="1" name="nr_lng">';
	
	$langs = $this->get_config_value('languages');
	
	foreach ($langs as $lang => $larr)
	 {
	  if ($lang=='default') continue;
	  if ($lang==$nr_lng)
	   {
	    $all.='<option value="'.$lang.'" selected="">'.$larr['long'].'</option>';
	   }
	    else
		 {
	      $all.='<option value="'.$lang.'">'.$larr['long'].'</option>';
	     }
	 }
	
	$all.=' </select>';
	$all.=' </td>';
	$all.='</tr>';	
	
	$all.='<tr>';
	$all.=' <td align="right">&nbsp;</td>';
	
	if ($action=='add')
	 {
	  $all.=' <td align="left"><input type="submit" name="go" value="Добавить" /></td>';
	 }
	  else
	 { 
	  $all.=' <td align="left"><input type="submit" name="go" value="Сохранить" /></td>';
	 } 
	$all.='</tr>';
	
    $all.='</form>';
	$all.='</table>';
	return $all;	
   }
  // --- =============================================================================================== 
  
  // +++ ===============================================================================================
  // Строит форму добавления или редактирования рубрики новостей
  public function get_news_form($action, $rubric, $message='', $n_uid=0, $n_parent=0, $n_dt=0, $n_show='Y', $n_title='', $n_author='', $n_annotation='', $n_text='')
   {
 
	if ($n_dt==0)
	 {
	  $n_dt = time();
	 } 

    if ($n_parent==0)
	 {
	  $n_parent = $rubric;
	 }
	 
	$all='<table>';
	
	$all.='<form action="/admin/'.$this->lng.'/news/" method="post">';
	$all.='<input type="hidden" name="action" value="'.$action.'_go" />';
	$all.='<input type="hidden" name="n_uid" value="'.$n_uid.'" />';
	$all.='<input type="hidden" name="rubric" value="'.$rubric.'" />';
	
	$all.='<tr>';
	$all.=' <td align="right">&nbsp;</td>';
	$all.=' <td align="left">'.$message.'</td>';
	$all.='</tr>';
	
	$all.='<tr>';
	$all.=' <td align="right">Заголовок</td>';
	$all.=' <td align="left"><input type="text" name="n_title" value="'.$n_title.'" /></td>';
	$all.='</tr>';
	
	$all.='<tr>';
	$all.=' <td align="right">Автор</td>';
	$all.=' <td align="left"><input type="text" name="n_author" value="'.$n_author.'" /></td>';
	$all.='</tr>';
	
	$all.='<tr>';
	$all.=' <td align="right">Дата</td>';
	$all.=' <td align="left">Г.м.д ч:м:с ';
	$all.='  <input type="text" name="n_dt_year" value="'.date('Y',$n_dt).'" size="4" /> ';
	$all.='  <input type="text" name="n_dt_month" value="'.date('m',$n_dt).'" size="2" /> ';
	$all.='  <input type="text" name="n_dt_day" value="'.date('d',$n_dt).'" size="2" /> ';
	$all.='  <input type="text" name="n_dt_hour" value="'.date('H',$n_dt).'" size="2" /> ';
	$all.='  <input type="text" name="n_dt_min" value="'.date('i',$n_dt).'" size="2" /> ';
	$all.='  <input type="text" name="n_dt_sec" value="'.date('s',$n_dt).'" size="2" /> ';
	$all.=' </td>';
	$all.='</tr>';
	
	$all.='<tr>';
	$all.=' <td align="right">Аннотация</td>';
	$all.=' <td align="left"><textarea name="n_annotation" cols="70" rows="5">'.$n_annotation.'</textarea></td>';
	$all.='</tr>';
	
	$all.='<tr>';
	$all.=' <td align="right">Рубрика</td>';
	$all.=' <td align="left">';
	$all.=' <select size="1" name="n_parent">';
	
	$r = $this->db->query("SELECT `nr_uid`, `nr_name` from `news_rubrics` where `nr_lng`='$this->lng' order by `nr_ord` asc");
	
	//echo $n_parent;
	while ($row = $r['res']->fetch_assoc()) 
	 {
	  if ($row['nr_uid']==$n_parent)
	   {
	    //echo $nr_
		$all.='<option value="'.$row['nr_uid'].'" selected="">'.$row['nr_name'].'</option>';
	   }
	    else
		 {
	      $all.='<option value="'.$row['nr_uid'].'">'.$row['nr_name'].'</option>';
	     }
	 }
	
	$all.=' </select>';
	$all.=' </td>';
	$all.='</tr>';	
	
	$all.='<tr>';
	$all.=' <td align="right">Показывать</td>';
	$checked = ($n_show=='Y') ? 'checked=""' : '';
	$all.=' <td align="left"><input type="checkbox" name="n_show" '.$checked.'/></td>';
	$all.='</tr>';
	
	$all.='<tr>';
	$all.=' <td align="right">Текст</td>';
	$all.=' <td align="left"><textarea class="ckeditor" name="n_text">'.$n_text.'</textarea></td>';
	$all.='</tr>';	

	$all.='<tr>';
	$all.=' <td align="right">&nbsp;</td>';
	
	if ($action=='news_add')
	 {
	  $all.=' <td align="left"><input type="submit" name="go" value="Добавить" /></td>';
	 }
	  else
	 { 
	  $all.=' <td align="left"><input type="submit" name="go" value="Сохранить" /></td>';
	 } 
	$all.='</tr>';
	
    $all.='</form>';
	$all.='</table>';
	return $all;	
   }
  // --- =============================================================================================== 
  
  // +++ ===============================================================================================
  // Строит список новостей
  public function get_adm_news_list($rubric)
   {
    $r = $this->db->query("SELECT `n_uid`, `n_title`, `n_dt` from `news` where `n_parent`='$rubric' order by `n_dt` desc");
	
	$all='<table>';
	
	$all.='<tr>';
	$all.=' <td colspan="4"><a href="/admin/'.$this->lng.'/news/?action=news_add&rubric='.$rubric.'">Добавить</a></td>';
	$all.='</tr>';
	
	$all.='<tr>';
	$all.=' <td>Дата</td>';
	$all.=' <td>Заголовок</td>';
	$all.=' <td>Редактировать</td>';
	$all.=' <td>Удалить</td>';
	$all.='</tr>';
	
	while ($row = $r['res']->fetch_assoc()) 
	 {
	  $all.='<tr>';
	  $all.=' <td>';
	  $all.=date('Y.m.d H:i:s', $row['n_dt']);
	  $all.=' </td>';
	  $all.=' <td>';
	  $all.=$row['n_title'];
	  $all.=' </td>';
	  $all.=' <td>';
	  $all.='  <a href="/admin/'.$this->lng.'/news/?rubric='.$rubric.'&news_id='.$row['n_uid'].'&action=news_edit">Редактировать</a>';
	  $all.=' </td>';
	  $all.=' <td>';
	  $all.='  <a href="/admin/'.$this->lng.'/news/?rubric='.$rubric.'&news_id='.$row['n_uid'].'&action=news_del_go" onclick="return confirm(\'Удалить?\')">Удалить</a>';
	  $all.=' </td>';
	  $all.='</tr>';
	 }

	$all.='</table>';
	return $all;    
   }
  // --- =============================================================================================== 
  
  
  // +++ =============================================================================================== 
  public function get_keywords($uids, $kw_tpl = 'news/keyword_li.tpl')
   {
    if (!is_file($this->templates_dir.$kw_tpl)) 
	 {
	  parent::log_error('Template ['.$kw_tpl.'] not found!', FALSE, 5);
	  die(); 
	 }
	$kw_tpl = file_get_contents($this->templates_dir.$kw_tpl); 
	
    $r = $this->db->query("SELECT `news_keywords`.`nk_url`, `news_keywords`.`nk_name`, `n_m2m_nk`.`n_uid` from `news_keywords` LEFT JOIN `n_m2m_nk` ON `news_keywords`.`nk_uid`=`n_m2m_nk`.`nk_uid` where `n_m2m_nk`.`n_uid` IN ".$uids." ORDER BY `n_m2m_nk`.`n_uid`, `news_keywords`.`nk_name` asc");
	while ($row = $r['res']->fetch_assoc()) 
	 {
	  $news_id = $row['n_uid'];
	  $pair = array('url' => $row['nk_url'], 'name' => $row['nk_name']);
	  $final[$news_id][]=$pair;
	 }
	 
	foreach ($final as $k => $v) 
	 {
	  $all='';
	  foreach ($v as $pair)
	   {
	    $one = $kw_tpl;
		$one = str_replace('{ITEM_URL}', $pair['url'], $one);
		$one = str_replace('{ITEM_NAME}', $pair['name'], $one);
		$all .=$one;
	   }
	  $final[$k]['text'] = $all;
	 }
	//print_r($final);
	return $final;
   }
  // --- =============================================================================================== 
  
  // +++ ================================================================================================================   
  public function tags_cloud($limit = 100, $min = 10, $max=30, $kw_tpl = 'news/tag_li.tpl')
   {
    if (!is_file($this->templates_dir.$kw_tpl)) 
	 {
	  parent::log_error('Template ['.$kw_tpl.'] not found!', FALSE, 5);
	  die(); 
	 }
	$kw_tpl = file_get_contents($this->templates_dir.$kw_tpl); 
	
	$r = $this->db->query("SELECT `news_keywords`.`nk_url`, `news_keywords`.`nk_name`, count(*) as 'q' from `news_keywords` RIGHT JOIN `n_m2m_nk` ON `news_keywords`.`nk_uid`=`n_m2m_nk`.`nk_uid` group by `n_m2m_nk`.`nk_uid` order by `q` desc, `news_keywords`.`nk_name` asc limit ".$limit);
	
	while ($row = $r['res']->fetch_assoc()) 
	 {
//	  echo $row['nk_name'].' ';
	  $words[$row['nk_name']]=array('url' => $row['nk_url'], 'count' => $row['q']);
	 }
    
	//print_r($words);
	
	mysqli_data_seek($r['res'], 0);
	$max_occ = $r['res']->fetch_assoc()['q'];
	mysqli_data_seek($r['res'], $r['num']-1);
        $min_occ = $r['res']->fetch_assoc()['q'];
	
	$fonts_diff = $max-$min;
	$size_diff = $max_occ-$min_occ;
	
	ksort($words);

	$all = '';
	foreach ($words as $k => $v)
	 {
	  $one = $kw_tpl;
	  $one = str_replace('{ITEM_URL}', $v['url'], $one);
	  $one = str_replace('{ITEM_NAME}', $k, $one);
	    
	  $size_perc = round((($v['count']-$min_occ)/$size_diff), 2);
	  $font_size = round($fonts_diff*$size_perc)+$min;
	  
	  //echo $v['count'].' ----> '.$size_perc.' ---> '.$font_size.'<br />';
	  
	  $one = str_replace('{ITEM_SIZE}', $font_size, $one);
	  $all .= $one;
	 }
	
	//print_r($words);
	
	//echo $all;
	
	return $all;
   }
  // --- ================================================================================================================     
  
 }
// --- ================================================================================================================

 
 
?>