<?php
require_once 'auth.php';
require_permission('work_order_create');
header('Location: Southbridge_Rehabilitation_Work_Order_Protected.php');
exit;
?>
