<?php
  if (!($cn = pg_connect("host=localhost dbname=ipdp user=postgres password=postgres123"))) {
    echo "connect error\n";
  } else {
    echo "connect ok\n";
  }
?>
