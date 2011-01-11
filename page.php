<?php
function css_add(){
	drupal_add_css(drupal_get_path('module', 'okved') .'/css/okved.css');

}

function get_version() {
	$version = $_COOKIE["okved_version"];
	if ($version == null) $version = 1;
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

function form_checkedlist_submit($form, &$form_state) {
  if ($form_state['values']['selector'] == 0) {
	$form_state['redirect'] = 'okved/userlist/'; 
  } else {
	$form_state['redirect'] = 'okved/defaultlist/' . $form_state['values']['selector'];
  }
}

function form_checkedlist($form_state, $def_value = 0) {
  $checklist = def_list_array();
  $form['selector'] = array(
    '#type' => 'select',
    '#title' => t('Список'),
    '#options' => $checklist,
    '#default_value' => $def_value,
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Показать список',
  );

  return $form;
}

function form_filter_submit($form, &$form_state) {
  
  $form_state['redirect'] = 'okved/search/' . $form_state['values']['filter'];
}

function form_filter($form_state, $search_value = "") {
  $form['filter'] = array(
    '#type' => 'textfield',
    '#title' => t('Поиск'),
    '#default_value' => $search_value,
    '#required' => TRUE,
    '#size' => 40,
    '#maxlength' => 40,
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Найти',
  );

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

$ret[0] = 'Пользовательский';

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
		document.cookie = "okved_version=" + $(this).val();
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
    'title' => 'Глобальный поиск',
    'content' => t('Content of поиск'),
  );
  $tabs[] = array(
    'title' => 'поиск по разделу',
    'content' => t('Content of поиск'),
  );

  $tabs[] = array(
    'title' => 'Пользовательский список',
    'content' => okveds_userlist_print(),
  );
  $tabs[] = array(
    'title' => t('Раздел'),
    'content' => t('Content of third magic tab'),
  );
  return $tabs;
}

function head_print($search = '', $checklist_id = -1) {
  if (module_exists('magic_tabs')){
	return magic_tabs_get('magic_tabs_okved_callback', 'first');
/*    $tabs['search'] = array(
      'title' => t('Поиск по номеру/наименованию'),
      'type' => 'node',
      'nid' => 'fullsearch',
      'text' => drupal_get_form('form_filter', $search)//,
   //   $settings = array(
   //   'views' => array(
 //       'ajax_path' => url('views/ajax/fullsearch')))
    );
    $tabs['checkedlist'] = array(
      'title' => t('Предопределенный/пользовательский список'),
      'type' => 'freetext',
      'text' => drupal_get_form('form_checkedlist',$checklist_id),
    );

    if ($search != '') {
      $quicktabs['default_tab'] = 'search';
    } else if ($checklist_id == -1 ) {
      $checklist_id == 0;
      $quicktabs['default_tab'] = 'checkedlist';
    }
    
    $quicktabs['qtid'] = 'okvedhead';
    $quicktabs['tabs'] = $tabs;
    $quicktabs['style'] = 'Zen';
    $quicktabs['ajax'] = TRUE;
    return version_combobox() . theme('quicktabs', $quicktabs);
  */
    
    } else 
    return version_combobox() . drupal_get_form('form_filter', $search) . drupal_get_form('form_checkedlist',$checklist_id);
}

function razdels_print()
{
$db = get_db();
$version = get_version();

$q = $db->query('SELECT * FROM razdelz_'.$version);
  	
	$table_attributes = array('id' => 'rasdels_list');
	$headers = array('Раздел');
	while ( ($row = $q->fetchArray()))
	{
		$razdel_link = url('okved/rasdel/'.$row['rid'], array('absolute' => TRUE));
		$attributes = array();
		$search="Подраздел";
		if ( strncasecmp($row['name'], $search, strlen($search)) == 0 )
		  $attributes = array('attributes' => array('style' => "margin-left: 20px;"));
		$rows[] = array( l($row['name'], $razdel_link, $attributes));
	}
//+ theme('table', $headers, $rows, $table_attributes)
return head_print() . '<div id=mainpage_text><h3>' . variable_get('okved_statictext_mainpage', '') . '</h3></div>' . theme('table', $headers, $rows, $table_attributes) ;	

}

function okveds_from_query($q, $search = "")
{
	drupal_add_js(drupal_get_path('module', 'okved') . '/okved.js');
	
	$table_attributes = array('id' => 'okveds_list');
	$headers = array('<input type="checkbox" class="okved_allcheck" name="okved_allcheck">', 'Номер', 'Наименование / Дополнительное описание при наведении');
	while ( ($row = $q->fetchArray()))
	{
		if ($search != "" && stripos($row['name'], $search) === false && stripos($row['name'], $search)  != 0 ){

			 continue;
		 }
		
		if ($row['addition'] != "") 
		{
			$rows[] = array('class' => 'block', 'data' => array(array('data' => sprintf( '<input type="checkbox" class="okved_check" name="okved_check" value="%s">', $row['oid']), 'valign' => 'top') ,array('data' => $row['number'], 'valign' => 'top'), 
array('class' => 'name', 'valign' => 'top', 'data' => $row['name'].'<img src="'.drupal_get_path('module', 'okved').'/images/up_arrow.jpg" align="right">'.sprintf('<p class="addition" style="display: none;">%s</p>', nl2br($row['addition'])))));
		} else {
			
			$rows[] = array(array('data' => sprintf( '<input type="checkbox" class="okved_check" name="okved_check" value="%s">', $row['oid']), 'valign' => 'top'),
							array('data' => $row['number'], 'valign' => 'top'), 
							$row['name']);
		}
	}

return(theme('table', $headers, $rows, $table_attributes));
}

function okveds_search_print($search, $checked_only = false)
{
$db = get_db();
$version = get_version();

$filter="";
if($checked_only == true)
{
	$checked_list = split ( ",", $_COOKIE["checked_okveds_" . $version] );
}	

$q = $db->query('SELECT * FROM okveds_'.$version);
  
return head_print($search) . print_page_link() . okveds_from_query($q, $search);
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
  
return head_print('', $listid) . okveds_from_query($q);
}

function print_page_link()
{

//return '<a href="#" rel="nofollow" id=printlink">Страница для печати</a>';
return '<a href="#" rel="nofollow" id=printlink onClick="printpage(); return false;">Страница для печати</a>';
}

function okveds_userlist_print()
{
$db = get_db();
$version = get_version();

$filter="";
$checked_list = split ( ",", $_COOKIE["checked_okveds_" . $version] );
//printf($_COOKIE["checked_okveds"]);

//if ( $_COOKIE["checked_okveds"]) == "") drupal_set_message('Ни одна позиция не была выбрана', 'error');

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
  return head_print('', 0) . print_page_link() . okveds_from_query($q);
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
  
return head_print() . print_page_link() . okveds_from_query($q);
}

function rasdel_name($rasdel)
{
$db = get_db();
$version = get_version();

$q = $db->query('SELECT * FROM razdelz_'.$version . ' WHERE rid='.$rasdel . ' LIMIT 1');
$row = $q->fetchArray();

return $row['name'];
}

?>
