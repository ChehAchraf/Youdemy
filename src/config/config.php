<?php
namespace App\Config;

class Config {
    const DB_HOST = 'localhost';
    const DB_NAME = 'youdemy';
    const DB_USER = 'root';
    const DB_PASS = '';

    const UPLOAD_PATH = __DIR__ . '/../../uploads/';
    const MAX_FILE_SIZE = 500 * 1024 * 1024; // 500MB
} 