<?php
$password = 'Lenhros_231';
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
echo "Hash: $hash\n";
?>