<?php
namespace CriterionRegisterLogin\Database;

class Database
{
    private static $connection;

    public function __construct()
    {
        self::$connection = mysqli_connect('db', 'db_user', 'db_password', 'criterion_db') or die('Could not connect to the database.');
    }

    public function getConnection()
    {
        return self::$connection;
    }
}