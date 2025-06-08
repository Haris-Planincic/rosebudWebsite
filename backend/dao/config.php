<?php


class Config
{
    public static function DB_HOST()
    {
        return '127.0.0.1';
    }
    public static function DB_NAME()
    {
        return 'MovieStore';
    }

    public static function DB_USER()
    {
        return 'root';
    }

    public static function DB_PASSWORD()
    {
        return 'haris';
    }

    

    public static function JWT_SECRET()
    {
        return 'your_key_string'; // 🔒 Replace with a secure random key for production
    }
}
