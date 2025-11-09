<?php
$server= "localhost";
$username="root";
$password= "";
$database="LosCuatro";

$connection = new mysqli($server, $username, $password, $database); //standard syntax to create connection

if ($connection->connect_error) {
  die("Connection failed" . $connection->connect_error);
} 

//add this for the first time to make sure your conncted but remove 
//it after or else it shows up everytime top of the page if you include this script
//else{ 
//echo"Connected sucessfully";}

