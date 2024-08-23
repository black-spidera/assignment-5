<?php

declare(strict_types=1);

namespace App\Model;

use App\Interface\UserDataHandlerInterface;
use App\Services\Db;

class UserModel implements UserDataHandlerInterface
{
    private $db;

    public function __construct()
    {
        $dbInstance = new Db();
        $this->db = $dbInstance->db;
    }

    public function saveUser(array $userData): bool
    {
        if (!$this->userExists($userData['email'])) {
            $stmt = $this->db->prepare("INSERT INTO users (email, name, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $userData['email'], $userData['name'], $userData['password']);
        } else {
            $stmt = $this->db->prepare("UPDATE users SET balance = ? WHERE email = ?");
            $stmt->bind_param("ss", $userData['balance'], $userData['email']);
        }

        if ($stmt === false) {
            return false;
        }

        $result = $stmt->execute();

        $stmt->close();
        return $result;
    }

    public function recordTransactions(array $data): bool
    {
        $type = trim($data['type']);
        $validTypes = ['deposit', 'withdraw', 'transfer'];
    
        if (!in_array($type, $validTypes)) {
            throw new \InvalidArgumentException("Invalid transaction type: " . $type);
        }
    
        $stmt = $this->db->prepare("INSERT INTO transactions (email, name, amount, type) VALUES (?, ?, ?, ?)");
    
        if ($stmt === false) {
            return false;
        }
    
        $stmt->bind_param("ssss", $data['user']['email'], $data['user']['name'], $data['amount'], $type);
        $result = $stmt->execute();
    
        $stmt->close();
        return $result;
    }
    

    public function getTransactionsByEmail(string $email): array
{
    $stmt = $this->db->prepare("SELECT * FROM transactions WHERE email = ?");
    
    if ($stmt === false) {
        return [];
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $transactions = [];
    
    while ($transaction = $result->fetch_assoc()) {
        $transactions[] = $transaction;
    }
    
    $stmt->close();
    return $transactions;
}



    public function userExists(string $email): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");

        if ($stmt === false) {
            return false;
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $stmt->bind_result($count);
        $stmt->fetch();

        $stmt->close();
        return $count > 0;
    }

    public function getUser(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");

        if ($stmt === false) {
            return null;
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $stmt->close();
        return $user ?: null;
    }

    public function getAllUsers(): array
    {
        $result = $this->db->query("SELECT * FROM users");

        if ($result === false) {
            return [];
        }

        $users = [];
        while ($user = $result->fetch_assoc()) {
            $users[] = $user;
        }

        return $users;
    }

    public function parseEmail(string $email): string
    {
        return str_replace(['@', '.'], '', $email);
    }
}
