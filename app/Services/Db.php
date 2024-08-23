<?php

namespace App\Services;
use mysqli;

class Db {
    private $hostname = '127.0.0.1';
    private $username = 'root';
    private $password = '';
    private $dbname = 'assignment-5';
    public $db;

    public function __construct() {
        $this->db = new mysqli($this->hostname, $this->username, $this->password, $this->dbname);
    
        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error);
        }

        $this->createTables();
    }

    private function createTables(): void {
        $this->userTable();
        $this->transactionTable();
    }

    private function userTable(): void {
        $query = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            name VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(50) DEFAULT 'customer',
            balance INT DEFAULT 0
        )";

        if ($this->db->query($query) === false) {
            die("Error creating user table: " . $this->db->error);
        }
    }

    private function transactionTable(): void {
        $query = "CREATE TABLE IF NOT EXISTS transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            email VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL,
            amount INT NOT NULL,
            type ENUM('deposit', 'withdraw', 'transfer') NOT NULL,
            FOREIGN KEY (email) REFERENCES users(email)
        )";

        if ($this->db->query($query) === false) {
            die("Error creating transaction table: " . $this->db->error);
        }
    }
}
