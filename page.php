<?php
$rid=0;

function css_add(){
	drupal_add_css(drupal_get_path('module', 'okved') .'/css/okved.css');
}

function get_version() {
	$version = $_COOKIE["okved_version"];
	if ($version == null || !is_numeric($version)) $version = 1;	//если кукиз не определен, или в кукиз написали что-то кроме чисел, возвращает 1 раздел
	
	return $version;
}
function get_db() {
    static $dbobj;

    if (!isset($dbobj)) {
        // Assign a reference to the static variable
        $dbobj = new SQLite3(drupal_get_path('module', 'okved') . '/qokved.db', 0666, $error);
    }
    return $dbobj;
}

function form_checkedlist($form_state) {
  $checklist = def_list_array();
  $form['container'] = array(
     '#prefix' => '<div class="container-inline">',
     '#suffix' => '</div>',
  );

  $form['container']['selector'] = array(
    '#type' => 'select',
    '#title' => t('Список'),
    '#options' => $checklist,
  );
  
$form['container']['checklistbtn'] = array(
    '#type' => 'button',
    '#value' => 'Показать',
    '#attributes' => array('onclick' => "ajax_checklists(this.form.getAttribute('action'), this.form.selector.value); return false;"),
);


$form['#action'] = url("documents/okved/ajaxchecklist");

  return $form;
}

function form_search($form_state, $globalsearch = false) {
// контейнер для полей
$form['container'] = array(
     '#prefix' => '<div class="container-inline">',
     '#suffix' => '</div>',
);

$form['container']['search_string'] = array(
    '#type' => 'textfield',
    '#title' => t('Строка поиска'),
    '#default_value' => "",
    '#size' => 40,
    '#maxlength' => 40,
);
$form['container']['clear'] = array(
    '#name' => 'clear',
    '#type' => 'button',
    '#value' => t('Reset'),
    '#attributes' => array('onclick' => 'this.form.reset(); return false;'),
);    
    
$qarr= explode('/', $_GET['q']);
  if ($qarr[2] == 'rasdel' && !$globalsearch){
    $attrib_btn = array('onclick' => "ajax_search(this.form.getAttribute('action'), this.form.search_string.value, " . $qarr[3] . "); return false;");
  }
  else {
    $attrib_btn = array('onclick' => "ajax_search(this.form.getAttribute('action'), this.form.search_string.value); return false;");    
  }
  
$form['container']['searchbtn'] = array(
    '#name' => 'searchbtn',
    '#type' => 'button',
    '#value' => 'Найти',
    '#attributes' => $attrib_btn,
);


$form['#action'] = url("documents/okved/ajaxsearch");
//$form['#attributes'] = array( 'onsubmit' => "ajax_search(this.form.getAttribute('action'), this.form.search_string.value); return false; ");

  return $form;
}

function okved_settings_print(){
  $form['okved_statictext_mainpage'] = array(
    '#type' => 'textarea',
    '#title' => t('Текст на главной странице модуля'),
    '#default_value' => variable_get('okved_statictext_mainpage', ''),
    '#rows' => 5
  );
  
  return system_settings_form($form);
}

function def_list_array()
{
$db = get_db();
$version = get_version();

$ret = Array();
$q = $db->query('SELECT * FROM global_lists WHERE vid = '.$version);

while ( ($row = $q->fetchArray()))
{

	$ret[$row['sid']] = $row['name'];
}

return $ret;
}

function version_combobox() {
$db = get_db();
$version = get_version();

$vers_list = Array();

$q = $db->query('SELECT * FROM versions');
 
while ( ($row = $q->fetchArray()))
{
	$vers_list[$row['id']] = $row['name'];
}
drupal_add_js('
$(document).ready(function(){

	$("#version_combo").change(function() {
		document.cookie = "okved_version=" + $(this).val() + "; path=/";
		window.location.reload();
	});

});', 'inline');


  $form['version_combo'] = array(
    '#type' => 'select',
    '#title' => t('Версия закона'),
    '#options' => $vers_list,
    '#value' => $version,
    '#id' => 'version_combo',
  );

return drupal_render($form);
}

function magic_tabs_okved_callback($active = 0) {

  $tabs[] = array(
    'title' => t('Разделы'),
    'content' => razdels_print().'<script type="text/javascript">register_events(); </script>',
  );
  $tabs[] = array(
    'title' => 'Глобальный поиск',
    'content' => drupal_get_form('form_search', true) . '<div id=search_container>Введите поисковой запрос</div>',
  );
  
$qarr= explode('/', $_GET['q']);
if ($qarr [2] == 'rasdel')
  $tabs[] = array(
    'title' => 'Поиск по разделу',
    'content' => drupal_get_form('form_search', false) . '<div id=search_container>Введите поисковой запрос</div>',
  );
/*  $tabs[] = array(
    'title' => 'Содержимое раздела',
    'content' => okveds_print(2).'<script type="text/javascript">register_events();</script>',
  );
*/
  $tabs[] = array(
    'title' => 'Пользовательский список',
    'content' => okveds_userlist_print().'<script type="text/javascript">register_events(); </script>',
  );
  $tabs[] = array(
    'title' => 'Предопределенный список',
    'content' => drupal_get_form('form_checkedlist').'<div id=checklist_container>Выбирите список</div>',
  );
  return $tabs;
  
}

function head_print($search = '', $checklist_id = -1) {

drupal_add_js(drupal_get_path('module', 'okved') . '/okved.js');
  if (module_exists('magic_tabs')){
 
    unset($_SESSION['magic_tabs']);		//Иначе откроет последнюю открытую вкладку
    return version_combobox() . magic_tabs_get('magic_tabs_okved_callback', 0);
  }
}

function razdels_print()
{
$db = get_db();
$version = get_version();

$qarr= explode('/', $_GET['q']);
if ($qarr [2] == rasdel)
  return okveds_print($qarr [3]);


$q = $db->query('SELECT * FROM razdelz_'.$version);
  	
	$table_attributes = array('id' => 'rasdels_list');
	$headers = array('Раздел');
	while ( ($row = $q->fetchArray()))
	{
		$razdel_link = url('documents/okved/rasdel/'.$row['rid'], array('absolute' => TRUE));
		$attributes = array();
		$search="Подраздел";
		if ( strncasecmp($row['name'], $search, strlen($search)) == 0 )
		  $attributes = array('attributes' => array('style' => "margin-left: 20px;"));
		$rows[] = array( l($row['name'], $razdel_link, $attributes));
	}
//+ theme('table', $headers, $rows, $table_attributes)
//return '<div id=mainpage_text><h3>' . variable_get('okved_statictext_mainpage', '') . '</h3></div>' . theme('table', $headers, $rows, $table_attributes) ;	
return theme('table', $headers, $rows, $table_attributes) ;	

}

function okveds_from_query($q, $search = "")
{
	
	
	$table_attributes = array('id' => 'okveds_list');
	$headers = array(array('data' => '<input type="checkbox" class="okved_allcheck" name="okved_allcheck">', 'width' => "3%"), array('data' => 'Номер', 'width' => "7%"), array('data' => 'Наименование / Дополнительное описание при наведении', 'width' => "90%"));
	while ( ($row = $q->fetchArray()))
	{
		if ($search != "" &&  gettype (stripos($row['name'], $search)) == "boolean" ){

			 continue;
		 }
		
		if ($row['addition'] != "") 
		{
			$rows[] = array('class' => 'block', 'data' => array(array('data' => sprintf( '<input type="checkbox" class="okved_check" name="okved_check" value="%s">', $row['oid']), 'valign' => 'top') ,array('data' => $row['number'], 'valign' => 'top'), 
array('class' => 'name', 'valign' => 'top', 'data' => $row['name'].'<img src="'.url(drupal_get_path('module', 'okved')).'/images/up_arrow.jpg" align="right">'.sprintf('<p class="addition" style="display: none;">%s</p>', nl2br($row['addition'])))));
		} else {
			
			$rows[] = array(array('data' => sprintf( '<input type="checkbox" class="okved_check" name="okved_check" value="%s">', $row['oid']), 'valign' => 'top'),
							array('data' => $row['number'], 'valign' => 'top'), 
							$row['name']);
		}
	}

return(theme('table', $headers, $rows, $table_attributes));
}

function okveds_search_print($search, $checked_only = false, $rasdel = 1)
{
$db = get_db();
$version = get_version();

$filter="";
if($checked_only == true)
{
	$checked_list = split ( ",", $_COOKIE["checked_okveds_" . $version] );
}	

$query = 'SELECT * FROM okveds_'.$version;

if ($rasdel==1) {		//Если это "Все разделы"
	;
} else { 				//Если нет, ищем по разделу + подразделам
	$query .=' WHERE razdel_id='.$rasdel;
	
	$q = $db->query('SELECT rid FROM razdelz_'.$version.' WHERE father='.$rasdel);
	while ($row = $q->fetchArray())
	{
		$query .= ' OR razdel_id='.$row['rid'];
	}
}


$q = $db->query($query);
  
return print_page_link() . okveds_from_query($q, $search);
}

function okveds_list_print($listid)
{
$db = get_db();
$version = get_version();

$q = $db->query('SELECT * FROM global_lists WHERE sid=' . $listid . ' LIMIT 1');
$row = $q->fetchArray();

$filter="";
$checked_list = split ( ",", $row['checks'] );

foreach ($checked_list as $checkid) {
	if ($filter == '') {
		$filter .= " WHERE ";
	} else $filter .= " OR ";

	$filter .= 'oid=' . $checkid;
}
$q = $db->query('SELECT * FROM okveds_'.$version . $filter);
  
return okveds_from_query($q);
}

function print_page_link()
{
return '&nbsp;<a href="'.url('documents/okved') . '" onClick="' . $activate1 . '" >Выбор раздела</a>' .'&nbsp;|&nbsp;'.  '<a href="#" rel="nofollow" id=printlink onClick="printpage(); return false;">Страница для печати</a>';
}

function okveds_userlist_print()
{
$db = get_db();
$version = get_version();

$filter="";
$checked_list = split ( ",", $_COOKIE["checked_okveds_" . $version] );

//if ( $_COOKIE["checked_okveds"]) == "") drupal_set_message('Ни одна позиция не была выбрана', 'error');
$have_check = false;

foreach ($checked_list as $checkid) {
	if($checkid!='' ||  $checkid !=null){
		$have_check = true;
		if ($filter == '') {
			$filter .= "WHERE ";
		} else $filter .= " OR ";
	
		$filter .= 'oid=' . $checkid;
	}
  }
	
if ($have_check) {
  $q = $db->query('SELECT * FROM okveds_' . $version . ' ' . $filter);  
//  return head_print('', 0) . print_page_link() . okveds_from_query($q);
  return  print_page_link() . okveds_from_query($q);

} else {
  return "Ни одна позиция не была выбрана";
}
}

function okveds_print($rasdel)
{
$db = get_db();
$version = get_version();

$return="";	
$filter="";
if ($rasdel==1) {		//Если это "Все разделы"
	$filter="";
} else { 				//Если нет, ищем по разделу + подразделам
	$filter=' WHERE razdel_id='.$rasdel;
	
	$q = $db->query('SELECT rid FROM razdelz_'.$version.' WHERE father='.$rasdel);
	while ($row = $q->fetchArray())
	{
		$filter .= ' OR razdel_id='.$row['rid'];
	}
}
$q = $db->query('SELECT * FROM okveds_'.$version . $filter);
  
return print_page_link() . okveds_from_query($q);
}

function rasdel_name($rasdel)
{
$db = get_db();
$version = get_version();

//print $rasdel;
$q = $db->query('SELECT name FROM razdelz_'.$version . ' WHERE rid='.$rasdel . ' LIMIT 1');
$row = $q->fetchArray();
//return '123';
return $row['name'];
}

?>
