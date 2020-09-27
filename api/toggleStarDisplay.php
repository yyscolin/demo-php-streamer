<?php

include('../public/mysql_connections.php');
mysqli_query($con, "update stars set display = ".$_POST['value']." where id = ".$_POST['id']);

?>