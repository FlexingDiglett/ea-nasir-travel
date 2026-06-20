<?php

class Database {
    private $mongoManager = null;
    private $pdo = null;

    private $sqlHost = 'db-sql';
    private $sqlDb = 'travel_app';
    private $sqlUser = 'root';
    private $sqlPass = 'password';

    private $mongoUri = "mongodb://admin:password@mongodb:27017";

    public function getMongo() {
        if ($this->mongoManager === null) {
            try {
                $this->mongoManager = new MongoDB\Driver\Manager($this->mongoUri);    # URI: Uniform Resource Identifier (diff. from URL -> Locator), points to a raw resource instead of a webpage
            } catch (Exception $e) {
                die(json_encode(['success' => false, 'message' => 'MongoDB connection failed: ' . $e->getMessage()]));
            }
        }
        return $this->mongoManager;
    }

    public function getMariaDB() {
        if ($this->pdo === null) { # PDO: PHP Data Objects
            try {
                $dsn = "mysql:host={$this->sqlHost};dbname={$this->sqlDb};charset=utf8mb4";  # DataSourceName: formatted string locating the db; charset->sp. chars allowed
                $this->pdo = new PDO($dsn, $this->sqlUser, $this->sqlPass);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);         # tells the db to report any spelling errors in the queries
                $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);    # tells the db to package the file into PHP arrays
            } catch (PDOException $e) {
                die(json_encode(['success' => false, 'message' => 'MariaDB connection failed: ' . $e->getMessage()]));
            }
        }
        return $this->pdo;
    }
}
