<?php 
    // config.php
    define('EMAIL_USER', 'email');
    //migocastra@gmail.com
    define('EMAIL_PASS', 'app_password');
    //tcae mhac boge kwel

    // Xendit Configuration
    define('XENDIT_SECRET_KEY', 'xnd_development_VWMBDO5rtGnf7h4daB6YSohRDVeEiFEtjM5d7p04ESuSZFm5UGrTZASia3UWdU');
    define('XENDIT_PUBLIC_KEY', 'xnd_public_development_o2MpedgBq43AyKgf4cBqNnMFXt9XDsQTP68IoAnXg5frjnpo1_50mWqlh3u38iQ');
    define('GATEWAY_BASE_URL', 'https://api.xendit.co');
    
    // Redirect URLs after payment
    define('GATEWAY_RETURN_SUCCESS', 'http://localhost/ComPLETED/OrgMerch_Website/src/callback.php?status=success');
    define('GATEWAY_RETURN_FAILED', 'http://localhost/ComPLETED/OrgMerch_Website/src/callback.php?status=failed');

    $servername = "localhost";
    
    $username = "root";

    $password = "";

    $dbname = "orgmerch";

    $con = new mysqli($servername, $username, $password, $dbname);

    if ($con->connect_error) {
        die("Connection failed: " . $con->connect_error);
    }
?>