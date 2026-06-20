<?php

class User {
    private $db;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // connects to the db-sql Docker container, and (thanks to @) throws a clean error instead of crashing with a PHP error text

        $this->db = @new mysqli('db-sql', 'root', 'password', 'travel_app');

        if ($this->db->connect_error) {
            die(json_encode(['success' => false, 'message' => 'Database connection failed']));
        }
    }

    public function register($email, $password, $userType, $contactName) {
        $checkSql = "SELECT id FROM users WHERE email = ?";
        $stmt = $this->db->prepare($checkSql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Email is already registered.'];
        }
        $stmt->close();

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $insertSql = "INSERT INTO users (email, password_hash, user_type, contact_name) VALUES (?, ?, ?, ?)";
        $insertStmt = $this->db->prepare($insertSql);
        $insertStmt->bind_param("ssss", $email, $hashedPassword, $userType, $contactName);

        if ($insertStmt->execute()) {
            return ['success' => true, 'message' => 'Registration successful.'];
        }

        return ['success' => false, 'message' => 'Registration failed.'];
    }

    public function getProfileData() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $sql = "SELECT id, email, user_type, contact_name, created_at FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();

        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();
        $stmt->close();

        return $userData;
    }

    public function updateProfile($newContactName) {
        if (!$this->isLoggedIn()) {
            return ['success' => false, 'message' => 'You must be logged in to update your profile.'];
        }

        $sql = "UPDATE users SET contact_name = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        $stmt->bind_param("si", $newContactName, $_SESSION['user_id']);

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Profile updated successfully.'];
        }

        $stmt->close();
        return ['success' => false, 'message' => 'Failed to update profile.'];
    }

    public function login($email, $password) {
        $sql = "SELECT id, password_hash, user_type FROM users WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            return ['success' => true, 'message' => 'Login successful.'];
        }

        return ['success' => false, 'message' => 'Invalid email or password.'];
    }

    public function logout() {
        session_unset();
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully.'];
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function __destruct() {
        if ($this->db && empty($this->db->connect_error)) {
            $this->db->close();
        }
    }
}
