<?php
    $host = 'testserver';
    $user = 'testuser';
    $pass = 'testpassword';
    $dbase = 'testchallenge'; 

    $connexion_db = mysqli_connect($host,$user,$pass) or die ('Error connection parameters to the DB');
    mysqli_select_db($dbase,$connexion_db)or die ('Could not connect to the DB');

?>