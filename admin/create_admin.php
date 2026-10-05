<?php

$password = "admin";

$hashedPassword = password_hash(
    $password,
    PASSWORD_DEFAULT
);

echo "Hashed Password:<br><br>";
echo htmlspecialchars($hashedPassword);

?>