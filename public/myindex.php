<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Hello, Nginx! Shopware 6 Site</title>
</head>
<body>
    <h1>Hello, Nginx! Shopware 6 Site</h1>
    <p>We have just configured our Nginx web server</p>

<p>Seite abgerufen am 
   <?php 
      echo ( date('d.m.Y \u\m H:i:s') );
   ?> 
Uhr</p>

<?php
echo " <p>Hallo Welt1</p> ";
?>

<?php phpinfo(); ?>

</body>
</html>
