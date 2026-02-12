<?php


class Config {
    public static function get_env($name, $default) {
        return isset($_ENV[$name]) && trim($_ENV[$name]) !== "" ? $_ENV[$name] : $default;
    }

    public static function DB_HOST() {
        return Config::get_env("DB_HOST", "127.0.0.1");
    }

    public static function DB_PORT() {
        return Config::get_env("DB_PORT", 3306);
    }

    public static function DB_NAME() {
        return Config::get_env("DB_NAME", "MovieStore");
    }

    public static function DB_USER() {
        return Config::get_env("DB_USER", "root");
    }

    public static function DB_PASSWORD() {
        return Config::get_env("DB_PASSWORD", "haris");
    }

    public static function JWT_SECRET() {
        return Config::get_env("JWT_SECRET", "your_key_string");
    }
    public static function STRIPE_SECRET_KEY() { return 'SECRET'; }
    public static function STRIPE_WEBHOOK_SECRET() { return 'SECRET'; }
    public static function MAIL_HOST() {
    return 'sandbox.smtp.mailtrap.io'; 
}

public static function MAIL_PORT() {
    return 2525;
}

public static function MAIL_USER() {
    return 'e50bf164d530e9';
}

public static function MAIL_PASS() {
    return '1b076f015a405c';
}


}

