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
        $db = new PDO("mysql:host=127.0.0.1; dbname=rtc", "abdullah", "Asd@123#Abdo");
        return $db;
    }
}
