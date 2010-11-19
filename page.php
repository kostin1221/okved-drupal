<?php

$db = new SQLite3('/srv/www/htdocs/sites/all/modules/okved/qokved.db', 0666, $error);
$version = 1;

function form_filter_submit($form, &$form_state) {
  $form_state['redirect'] = 'okved/search/' . $form_state['values']['filter'];
}

function form_filter($form_state) {
  $form['filter'] = array(
    '#type' => 'textfield',
    '#title' => t('Поиск'),
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

function razdels_print()
{
$db = new SQLite3('/srv/www/htdocs/sites/all/modules/okved/qokved.db', 0666, $error);
$version = 1;

$q = $db->query('SELECT * FROM razdelz_'.$version);
  	
	$table_attributes = array('id' => 'rasdels_list');
	$headers = array('Раздел');
	while ( ($row = $q->fetchArray()))
	{
		$razdel_link = url('okved/rasdel/'.$row['rid'], array('absolute' => TRUE));
		$rows[] = array(l($row['name'], $razdel_link));
	}
//+ theme('table', $headers, $rows, $table_attributes)
return drupal_get_form('form_filter') . theme('table', $headers, $rows, $table_attributes) ;	

}

function okveds_from_query($q, $search = "")
{
	drupal_add_js('$(document).ready(function(){
 
    $("#okveds_list .block").hover(function(){
		$(this).find("p").slideToggle("fast");
     }, function() {
		$(this).find("p").slideToggle("fast");
	 });});', 'inline');
	
	$table_attributes = array('id' => 'okveds_list');
	$headers = array('Номер', 'Наименование / Дополнительное описание при наведении');
	while ( ($row = $q->fetchArray()))
	{
		if ($search != "" && !stripos($row['name'], $search)){

			 continue;
		 }
		
		if ($row['addition'] != "") 
		{
			$rows[] = array('class' => 'block',
							'data' => array(array('data' => $row['number'], 'valign' => 'top'), 
							$row['name'].sprintf('<p style="display: none;">%s</p>', $row['addition'])));
		} else {
			
			$rows[] = array(array('data' => $row['number'], 'valign' => 'top'), 
							$row['name']);
		}
	}

return(theme('table', $headers, $rows, $table_attributes));
}

function okveds_search_print($search)
{
$db = new SQLite3('/srv/www/htdocs/sites/all/modules/okved/qokved.db', 0666, $error);
$version = 1;

$return="";	

$q = $db->query('SELECT * FROM okveds_'.$version);
  
return drupal_get_form('form_filter') . okveds_from_query($q, $search);
}

function okveds_print($rasdel)
{
$db = new SQLite3('/srv/www/htdocs/sites/all/modules/okved/qokved.db', 0666, $error);
$version = 1;

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
  
return drupal_get_form('form_filter') . okveds_from_query($q);
}

function rasdel_name($rasdel)
{
$db = new SQLite3('/srv/www/htdocs/sites/all/modules/okved/qokved.db', 0666, $error);
$version = 1;

$q = $db->query('SELECT * FROM razdelz_'.$version . ' WHERE rid='.$rasdel . ' LIMIT 1');
$row = $q->fetchArray();

return $row['name'];
}

?>
