<?php
   $host        = "host=127.0.0.1";
   $port        = "port=1432";
   $dbname      = "dbname=jamesmilner";
   $credentials = "user=user13 password=user13password";
   $db = pg_connect( "$host $port $dbname $credentials"  );
?>