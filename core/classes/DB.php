<?php

namespace MyApp;

use PDO;

class DB
{
    public function __construct()
    {
    }
    public function connect()
    {
        $db = new PDO("mysql:host=192.168.1.67; dbname=rtc", "abdullah", "Asd@123#Abdo");
        return $db;
    }
}
