<?php
$host="localhost";
$dbname="login_db";
$Nume="root";
$Parola=""; 
$mysqli=new mysqli(hostname:$host,
                    username:$Nume,
                    password:$Parola,
                    database:$dbname);
if($mysqli->connect_errno){
    die("Connection error: " . $mysqli->connect_error);
}
return $mysqli;