<?php

echo @$model;
echo '<br>';
isset($_SESSION['user']) and  print('User name: ' .  $_SESSION['user']['login_name']) and
print('<br>Unsuccessful logins: ' . $_SESSION['user']['failing_login_count']);

?>
