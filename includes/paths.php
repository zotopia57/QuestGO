<?php
$requestPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
$scriptDirectory = str_replace('\\', '/', dirname($requestPath));
$scriptDirectory = rtrim($scriptDirectory, '/');
$baseUrl = preg_replace('~/(pages(?:/(?:user|admin))?)$~', '', $scriptDirectory);
$baseUrl = rtrim($baseUrl, '/');
