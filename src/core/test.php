<?php
session_start();
echo '<pre>';
echo 'SESSION ID: ' . session_id() . "\n";
print_r($_SESSION);
echo '</pre>';