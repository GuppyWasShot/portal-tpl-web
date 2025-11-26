<?php
/**
 * Database Class
 * Mengelola koneksi database menggunakan Singleton Pattern
 * 
 * Usage:
 * $db = Database::getInstance()->getConnection();
 */
class Database {
    private static $instance = null;
    private $conn;
    
    // Konfigurasi database
    private $host = 'localhost';
    private $user = 'root';
    private $pass = '';
    private $db_name = 'db_portal_tpl';

    // private $host = 'sql109.infinityfree.com';
    // private $user = 'if0_40385611';
    // private $pass = 'portaltpl123';
    // private $db_name = 'if0_40385611_portal_tpl';
    
    /**
     * Constructor private untuk mencegah instantiasi langsung
     */
    private function __construct() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->db_name);
        
        if ($this->conn->connect_error) {
            die("Koneksi database gagal: " . $this->conn->connect_error);
        }
        
        // Set charset
        $this->conn->set_charset("utf8mb4");
        
        // Set timezone MySQL ke Asia/Jakarta (GMT+7)
        $this->conn->query("SET time_zone = '+07:00'");
        
        // Enable error reporting untuk development
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    }
    
    /**
     * Mendapatkan instance Database (Singleton Pattern)
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Mendapatkan koneksi mysqli
     * 
     * @return mysqli
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Menutup koneksi database
     */
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
    
    /**
     * Mencegah cloning
     */
    private function __clone() {}
    
    /**
     * Mencegah unserialize
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

