<?php

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
		$rows[] = array(l($row['name'], $razdel_link));
	}
//+ theme('table', $headers, $rows, $table_attributes)
return version_combobox() . drupal_get_form('form_filter') . drupal_get_form('form_checkedlist') . theme('table', $headers, $rows, $table_attributes) ;	

}

function okveds_from_query($q, $search = "")
{
	drupal_add_js(drupal_get_path('module', 'okved') . '/okved.js');
	
	$table_attributes = array('id' => 'okveds_list');
	$headers = array('Галко', 'Номер', 'Наименование / Дополнительное описание при наведении');
	while ( ($row = $q->fetchArray()))
	{
		if ($search != "" && !stripos($row['name'], $search)){

			 continue;
		 }
		
		if ($row['addition'] != "") 
		{
			$rows[] = array('class' => 'block', 'data' => array(array('data' => sprintf( '<input type="checkbox" class="okved_check" name="okved_check" value="%s">', $row['oid']), 'valign' => 'top') ,array('data' => $row['number'], 'valign' => 'top'), 
							$row['name'].sprintf('<p style="display: none;">%s</p>', nl2br($row['addition']))));
		} else {
			
			$rows[] = array(array('data' =>  sprintf( '<input type="checkbox" class="okved_check" name="okved_check" value="%s">', $row['oid']), 'valign' => 'top'),
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
	$checked_list = split ( ",", $_COOKIE["ckecked_okveds"] );
}	

$q = $db->query('SELECT * FROM okveds_'.$version);
  
return version_combobox() . drupal_get_form('form_filter', $search) . drupal_get_form('form_checkedlist') . okveds_from_query($q, $search);
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
  
return version_combobox() . drupal_get_form('form_filter') . drupal_get_form('form_checkedlist', $listid) . okveds_from_query($q);
}

function print_page_link()
{
drupal_add_js( "function printpage(){

$.fn.removeCol = function(col){
    // Make sure col has value
    if(!col){ col = 1; }
    $('tr td:nth-child('+col+'), tr th:nth-child('+col+')', this).remove();
    return this;
};

document.getElementById('printlink').onclick=function(){

table = content+=$('#okveds_list').html();

content='<table border=\"1\" cellspacing=\"1\">';

content+='<thead class=\"tableHeader-processed\"><tr><th>Номер</th><th>Наименование</tr></thead>';
content+='<tbody>';

oldtable=$('#okveds_list').html();


$(oldtable).find('tr').each(function(n, elem) {
	if ($(elem).find('td').eq(1).html() != null){
		content+='<tr>';
		content+='<td>' + $(elem).find('td').eq(1).html() + '</td>';
		info=$(elem).find('td').eq(2).html();
		$(info).find(\"p\").remove();
		content+='<td>' + info + '</td>';
	};
});

content+='</table>';
w=window.open('about:blank');
w.document.open();
w.document.write( content );
w.document.close();


return false;
		}}", 'inline');

return '<a href="#" rel="nofollow" id=printlink onClick="printpage()">Страница для печати</a>';
}

function okveds_userlist_print()
{
$db = get_db();
$version = get_version();

$filter="";
$checked_list = split ( ",", $_COOKIE["ckecked_okveds"] );
if (count ($checked_list) < 1 ) drupal_set_message('Ни одна позиция не была выбрана', 'error');

foreach ($checked_list as $checkid) {
	if ($filter == '') {
		$filter .= " WHERE ";
	} else $filter .= " OR ";

	$filter .= 'oid=' . $checkid;
}	

$q = $db->query('SELECT * FROM okveds_'.$version . $filter);
  
return version_combobox() . drupal_get_form('form_filter') . drupal_get_form('form_checkedlist') . okveds_from_query($q);
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
  
return version_combobox() . drupal_get_form('form_filter') . drupal_get_form('form_checkedlist') . print_page_link() . okveds_from_query($q);
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
