<?php
session_start();
unset($_SESSION['nums'], $_SESSION['sorted']);
header("Location: index.php");
exit;
