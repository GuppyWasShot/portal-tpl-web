<?php
/**
 * Rating Class
 * Menangani semua operasi terkait Rating
 * 
 * Usage:
 * $rating = new Rating();
 * $rating->submitRating(1, 5, $user_uuid, $ip);
 */
require_once __DIR__ . '/Database.php';

class Rating {
    private $db;
    
    /**
     * Constructor
     */
    public function __construct($database = null) {
        if ($database === null) {
            $this->db = Database::getInstance()->getConnection();
        } else {
            $this->db = $database;
        }
    }
    
    /**
     * Submit rating untuk suatu karya (insert atau update jika sudah ada)
     * 
     * @param int $project_id ID project
     * @param int $score Skor rating (1-5)
     * @param string $user_uuid UUID user dari session
     * @param string $ip_address IP address user
     * @return bool True jika berhasil, false jika gagal
     */
    public function submitRating($project_id, $score, $user_uuid, $ip_address) {
        try {
        // Validasi input
        if ($project_id <= 0 || $score < 1 || $score > 5) {
                error_log("Rating validation failed: project_id=$project_id, score=$score");
            return false;
        }
        
        // Cek apakah project exists dan published
        $stmt = $this->db->prepare("
            SELECT id_project FROM tbl_project 
            WHERE id_project = ? AND status = 'Published'
        ");
            
            if (!$stmt) {
                error_log("Failed to prepare statement (check project): " . $this->db->error);
                return false;
            }
            
        $stmt->bind_param("i", $project_id);
            if (!$stmt->execute()) {
                error_log("Failed to execute statement (check project): " . $stmt->error);
                $stmt->close();
                return false;
            }
            
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
                error_log("Project not found or not published: project_id=$project_id");
            $stmt->close();
            return false;
        }
        $stmt->close();
        
        // Cek apakah user sudah pernah rating
        $existingRating = $this->getUserRatingFull($project_id, $user_uuid, $ip_address);
        
        if ($existingRating) {
            // Update rating yang sudah ada
            $stmt = $this->db->prepare("
                UPDATE tbl_rating 
                SET skor = ? 
                WHERE id_rating = ?
            ");
                
                if (!$stmt) {
                    error_log("Failed to prepare statement (update rating): " . $this->db->error);
                    return false;
                }
                
            $stmt->bind_param("ii", $score, $existingRating['id_rating']);
            $success = $stmt->execute();
                
                if (!$success) {
                    error_log("Failed to execute update rating: " . $stmt->error);
                }
                
            $stmt->close();
            return $success;
        } else {
            // Insert rating baru
                // Cek apakah kolom uuid_user ada di tabel
                $check_column = $this->db->query("SHOW COLUMNS FROM tbl_rating LIKE 'uuid_user'");
                $has_uuid_column = $check_column && $check_column->num_rows > 0;
                
                if ($has_uuid_column) {
                    // Gunakan uuid_user jika kolom ada
            $stmt = $this->db->prepare("
                INSERT INTO tbl_rating (id_project, uuid_user, ip_address, skor) 
                VALUES (?, ?, ?, ?)
            ");
                    
                    if (!$stmt) {
                        error_log("Failed to prepare statement (insert rating with uuid): " . $this->db->error);
                        return false;
                    }
                    
            $stmt->bind_param("issi", $project_id, $user_uuid, $ip_address, $score);
                } else {
                    // Fallback: tidak gunakan uuid_user jika kolom tidak ada
                    error_log("Warning: Kolom uuid_user tidak ada di tabel tbl_rating, menggunakan ip_address saja");
                    $stmt = $this->db->prepare("
                        INSERT INTO tbl_rating (id_project, ip_address, skor) 
                        VALUES (?, ?, ?)
                    ");
                    
                    if (!$stmt) {
                        error_log("Failed to prepare statement (insert rating without uuid): " . $this->db->error);
                        return false;
                    }
                    
                    $stmt->bind_param("isi", $project_id, $ip_address, $score);
                }
                
            $success = $stmt->execute();
                
                if (!$success) {
                    error_log("Failed to execute insert rating: " . $stmt->error);
                    error_log("Params: project_id=$project_id, user_uuid=$user_uuid, ip=$ip_address, score=$score");
                }
                
            $stmt->close();
            return $success;
            }
        } catch (Exception $e) {
            error_log("Exception in submitRating: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Hapus rating user (cancel rating)
     * 
     * @param int $project_id ID project
     * @param string $user_uuid UUID user
     * @param string $ip_address IP address
     * @return bool True jika berhasil
     */
    public function deleteRating($project_id, $user_uuid, $ip_address) {
        // Cek apakah kolom uuid_user ada
        $check_column = $this->db->query("SHOW COLUMNS FROM tbl_rating LIKE 'uuid_user'");
        $has_uuid_column = $check_column && $check_column->num_rows > 0;
        
        if ($has_uuid_column) {
        $stmt = $this->db->prepare("
            DELETE FROM tbl_rating 
            WHERE id_project = ? AND (uuid_user = ? OR ip_address = ?)
        ");
        $stmt->bind_param("iss", $project_id, $user_uuid, $ip_address);
        } else {
            // Fallback: hanya gunakan ip_address
            $stmt = $this->db->prepare("
                DELETE FROM tbl_rating 
                WHERE id_project = ? AND ip_address = ?
            ");
            $stmt->bind_param("is", $project_id, $ip_address);
        }
        
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
    
    /**
     * Mendapatkan rating lengkap user (termasuk id_rating)
     * 
     * @param int $project_id ID project
     * @param string $user_uuid UUID user
     * @param string $ip_address IP address
     * @return array|null Data rating atau null
     */
    public function getUserRatingFull($project_id, $user_uuid, $ip_address) {
        // Cek apakah kolom uuid_user ada
        $check_column = $this->db->query("SHOW COLUMNS FROM tbl_rating LIKE 'uuid_user'");
        $has_uuid_column = $check_column && $check_column->num_rows > 0;
        
        if ($has_uuid_column) {
        $stmt = $this->db->prepare("
            SELECT id_rating, skor FROM tbl_rating 
            WHERE id_project = ? AND (uuid_user = ? OR ip_address = ?)
        ");
        $stmt->bind_param("iss", $project_id, $user_uuid, $ip_address);
        } else {
            // Fallback: hanya gunakan ip_address
            $stmt = $this->db->prepare("
                SELECT id_rating, skor FROM tbl_rating 
                WHERE id_project = ? AND ip_address = ?
            ");
            $stmt->bind_param("is", $project_id, $ip_address);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $rating = $result->fetch_assoc();
        $stmt->close();
        
        return $rating;
    }
    
    /**
     * Cek apakah user sudah pernah memberikan rating
     * 
     * @param int $project_id ID project
     * @param string $user_uuid UUID user
     * @param string $ip_address IP address
     * @return bool True jika sudah pernah rating
     */
    public function hasUserRated($project_id, $user_uuid, $ip_address) {
        // Cek apakah kolom uuid_user ada
        $check_column = $this->db->query("SHOW COLUMNS FROM tbl_rating LIKE 'uuid_user'");
        $has_uuid_column = $check_column && $check_column->num_rows > 0;
        
        if ($has_uuid_column) {
        $stmt = $this->db->prepare("
            SELECT id_rating FROM tbl_rating 
            WHERE id_project = ? AND (uuid_user = ? OR ip_address = ?)
        ");
        $stmt->bind_param("iss", $project_id, $user_uuid, $ip_address);
        } else {
            // Fallback: hanya gunakan ip_address
            $stmt = $this->db->prepare("
                SELECT id_rating FROM tbl_rating 
                WHERE id_project = ? AND ip_address = ?
            ");
            $stmt->bind_param("is", $project_id, $ip_address);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $hasRated = $result->num_rows > 0;
        $stmt->close();
        
        return $hasRated;
    }
    
    /**
     * Mendapatkan rating yang sudah diberikan user
     * 
     * @param int $project_id ID project
     * @param string $user_uuid UUID user
     * @param string $ip_address IP address
     * @return array|null Data rating atau null
     */
    public function getUserRating($project_id, $user_uuid, $ip_address) {
        // Cek apakah kolom uuid_user ada
        $check_column = $this->db->query("SHOW COLUMNS FROM tbl_rating LIKE 'uuid_user'");
        $has_uuid_column = $check_column && $check_column->num_rows > 0;
        
        if ($has_uuid_column) {
        $stmt = $this->db->prepare("
            SELECT skor FROM tbl_rating 
            WHERE id_project = ? AND (uuid_user = ? OR ip_address = ?)
        ");
        $stmt->bind_param("iss", $project_id, $user_uuid, $ip_address);
        } else {
            // Fallback: hanya gunakan ip_address
            $stmt = $this->db->prepare("
                SELECT skor FROM tbl_rating 
                WHERE id_project = ? AND ip_address = ?
            ");
            $stmt->bind_param("is", $project_id, $ip_address);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $rating = $result->fetch_assoc();
        $stmt->close();
        
        return $rating;
    }
    
    /**
     * Mendapatkan rata-rata rating suatu karya
     * 
     * @param int $project_id ID project
     * @return float|null Rata-rata rating atau null
     */
    public function getAverageRating($project_id) {
        $stmt = $this->db->prepare("
            SELECT AVG(skor) as avg_rating, COUNT(*) as total_rating
            FROM tbl_rating 
            WHERE id_project = ?
        ");
        $stmt->bind_param("i", $project_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        
        return $data;
    }
}

