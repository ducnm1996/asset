<?php
$host = 'db';
$dbname = 'manager_asset';
$user = 'postgres';
$pass = 'secret';

$conn = pg_connect("host=$host dbname=$dbname user=$user password=$pass");

if ($conn) {
    echo "✅ Kết nối database thành công!";
} else {
    echo "❌ Không thể kết nối database.";
}
?>
