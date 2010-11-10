<?php

function printArray( $a ){
  
   static $count; 
  $count = (isset($count)) ? ++$count : 0;
  $colors = array('#FFCB72', '#FFB072', '#FFE972', '#F1FF72', '#92FF69', '#6EF6DA', '#72D9FE', '#77FFFF', '#FF77FF');
  if ($count > count($colors)) {
   $count--;
   return;
  }

  if (!is_array($a)) {
   echo "Passed argument is not an array!<p>";
   return; 
  }

  echo "<table border=1 cellpadding=0 cellspacing=0 bgcolor=$colors[$count]>";

  while(list($k, $v) = each($a)) {
   echo "<tr><td style='padding:1em'>$k</td><td style='padding:1em'>$v</td></tr>\n";
   if (is_array($v)) {
    echo "<tr><td> </td><td>";
    self::printArray($v);
    echo "</td></tr>\n";
   }
  }
  echo "</table>";
  $count--;
 }


$error="";
    if ($db = new SQLite3('qokved.db', 0666, $error)) {
		
        $q = $db->query('SELECT * FROM versions');
  
        echo $q->numColumns();

		while ( ($row = $q->fetchArray()))
		{
				printf("%b %-20s\n", $row['id'], $row['name']);
		}

        print_r ($row);
		//printArray($row);

     /*   if ($q) {
            $db->queryExec('CREATE TABLE tablename (id int, requests int, PRIMARY KEY (id)); INSERT INTO tablename VALUES (1,1)');
            $hits = 1;
        } else {
            $result = $q->fetchSingle();
            $hits = $result+1;
        }
        $db->queryExec("UPDATE tablename SET requests = '$hits' WHERE id = 1");
       */ 
    } else {
		//output=$error;
        die($error);
      
    }

?>
