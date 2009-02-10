<?php
require('php-saas.php');
$examplePHPSaas = file_get_contents('styles/example.psaas');
$phpSaas = new PHPSaas($examplePHPSaas);
echo $phpSaas->body;
?>