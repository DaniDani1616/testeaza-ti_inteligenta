<?php
$host="sql206.infinityfree.com";
$dbname="if0_39518451_XXX";
$Nume="if0_39518451";
$Parola="Dani2008Fotbal"; 
$mysqli=new mysqli(hostname:$host,
                    username:$Nume,
                    password:$Parola,
                    database:$dbname);
if($mysqli->connect_errno){
    die("Connection error: " . $mysqli->connect_error);
}
return $mysqli;
