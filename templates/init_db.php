<?php
$dbFile = 'laundry.db';
$db = new PDO('sqlite:' . $dbFile);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Run database.sql content
$schema = file_get_contents('database.sql');
$db->exec($schema);

echo 'Database laundry.db created successfully with all tables/seeds!<br>';
echo '<a href="code/login.php">Go to Login</a>';
?>

