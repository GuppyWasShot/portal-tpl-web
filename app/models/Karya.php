<?php
/**
 * Karya Class
 * Menangani semua operasi terkait Karya/Project
 * 
 * Usage:
 * $karya = new Karya();
 * $detail = $karya->getKaryaById(1);
 */
require_once __DIR__ . '/Database.php';

class Karya {
    private $db;
    
    /**
     * Constructor
     * Menggunakan dependency injection untuk database connection
     */
    public function __construct($database = null) {
        if ($database === null) {
            $this->db = Database::getInstance()->getConnection();
        } else {
            $this->db = $database;
        }
    }
    
    /**
     * Mendapatkan detail karya berdasarkan ID
     * 
     * @param int $id ID project
     * @return array|null Data karya atau null jika tidak ditemukan
     */
    public function getKaryaById($id) {
        $stmt = $this->db->prepare("
            SELECT p.*, 
            GROUP_CONCAT(DISTINCT c.nama_kategori ORDER BY c.nama_kategori SEPARATOR ', ') as kategori,
            GROUP_CONCAT(DISTINCT c.warna_hex ORDER BY c.nama_kategori SEPARATOR ',') as warna,
            AVG(r.skor) as avg_rating,
            COUNT(DISTINCT r.id_rating) as total_rating
            FROM tbl_project p
            LEFT JOIN tbl_project_category pc ON p.id_project = pc.id_project
            LEFT JOIN tbl_category c ON pc.id_kategori = c.id_kategori
            LEFT JOIN tbl_rating r ON p.id_project = r.id_project
            WHERE p.id_project = ? AND p.status = 'Published'
            GROUP BY p.id_project
        ");
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $karya = $result->fetch_assoc();
        $stmt->close();
        
        return $karya;
    }
    
    /**
     * Mendapatkan semua link dari suatu karya
     * 
     * @param int $project_id ID project
     * @return array Array of links
     */
    public function getLinks($project_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM tbl_project_links 
            WHERE id_project = ? 
            ORDER BY is_primary DESC
        ");
        
        $stmt->bind_param("i", $project_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $links = [];
        while ($row = $result->fetch_assoc()) {
            $links[] = $row;
        }
        
        $stmt->close();
        return $links;
    }
    
    /**
     * Mendapatkan semua file dari suatu karya
     * 
     * @param int $project_id ID project
     * @return array Array of files
     */
    public function getFiles($project_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM tbl_project_files 
            WHERE id_project = ? 
            ORDER BY id_file ASC
        ");
        
        $stmt->bind_param("i", $project_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $files = [];
        while ($row = $result->fetch_assoc()) {
            $files[] = $row;
        }
        
        $stmt->close();
        return $files;
    }
    
    /**
     * Memisahkan files menjadi snapshots dan documents
     * 
     * @param array $files Array of files
     * @return array Array dengan key 'snapshots' dan 'documents'
     */
    public function separateFiles($files) {
        $snapshots = array_filter($files, function($f) {
            return strpos($f['file_path'], 'snapshots') !== false;
        });
        
        $documents = array_filter($files, function($f) {
            return strpos($f['file_path'], 'files') !== false;
        });
        
        return [
            'snapshots' => array_values($snapshots),
            'documents' => array_values($documents)
        ];
    }
    
    /**
     * Mendapatkan semua karya dengan filter (untuk galeri)
     * 
     * @param array $filters Array berisi: search, sort, kategori
     * @return array Array of karya
     */
    public function getAllKarya($filters = []) {
        $search = isset($filters['search']) ? trim($filters['search']) : '';
        $sort = isset($filters['sort']) ? $filters['sort'] : 'terbaru';
        $kategori_filter = isset($filters['kategori']) ? $filters['kategori'] : [];
        
        // Build WHERE conditions
        $where_conditions = ["p.status = 'Published'"];
        $params = [];
        $types = "";
        
        // Filter pencarian
        if (!empty($search)) {
            $where_conditions[] = "(p.judul LIKE ? OR p.pembuat LIKE ? OR p.deskripsi LIKE ?)";
            $search_param = "%$search%";
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
            $types .= "sss";
        }
        
        // Filter kategori
        if (!empty($kategori_filter) && is_array($kategori_filter)) {
            $placeholders = implode(',', array_fill(0, count($kategori_filter), '?'));
            $where_conditions[] = "pc.id_kategori IN ($placeholders)";
            foreach ($kategori_filter as $kat_id) {
                $params[] = intval($kat_id);
                $types .= "i";
            }
        }
        
        // Order by
        $order_by = "p.id_project DESC"; // default: terbaru
        switch ($sort) {
            case 'judul_asc':
                $order_by = "p.judul ASC";
                break;
            case 'judul_desc':
                $order_by = "p.judul DESC";
                break;
            case 'terlama':
                $order_by = "p.tanggal_selesai ASC";
                break;
            case 'rating':
                $order_by = "avg_rating DESC";
                break;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $query = "SELECT p.*, 
                GROUP_CONCAT(DISTINCT c.nama_kategori ORDER BY c.nama_kategori SEPARATOR ', ') as kategori,
                GROUP_CONCAT(DISTINCT c.warna_hex ORDER BY c.nama_kategori SEPARATOR ',') as warna,
                AVG(r.skor) as avg_rating,
                COUNT(DISTINCT r.id_rating) as total_rating
                FROM tbl_project p
                LEFT JOIN tbl_project_category pc ON p.id_project = pc.id_project
                LEFT JOIN tbl_category c ON pc.id_kategori = c.id_kategori
                LEFT JOIN tbl_rating r ON p.id_project = r.id_project
                WHERE $where_clause
                GROUP BY p.id_project
                ORDER BY $order_by";
        
        if (!empty($params)) {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->db->query($query);
        }
        
        $karya_list = [];
        while ($row = $result->fetch_assoc()) {
            $karya_list[] = $row;
        }
        
        if (isset($stmt)) {
            $stmt->close();
        }
        
        return $karya_list;
    }
    
    /**
     * Mendapatkan semua karya dengan filter dan pagination
     * 
     * @param array $filters Array berisi: search, sort, kategori
     * @param int $page Halaman saat ini (mulai dari 1)
     * @param int $per_page Jumlah item per halaman
     * @return array Array dengan keys: data, total, total_pages, current_page
     */
    public function getAllKaryaPaginated($filters = [], $page = 1, $per_page = 12) {
        $search = isset($filters['search']) ? trim($filters['search']) : '';
        $sort = isset($filters['sort']) ? $filters['sort'] : 'terbaru';
        $kategori_filter = isset($filters['kategori']) ? $filters['kategori'] : [];
        
        // Build WHERE conditions
        $where_conditions = ["p.status = 'Published'"];
        $params = [];
        $types = "";
        
        // Filter pencarian
        if (!empty($search)) {
            $where_conditions[] = "(p.judul LIKE ? OR p.pembuat LIKE ? OR p.deskripsi LIKE ?)";
            $search_param = "%$search%";
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
            $types .= "sss";
        }
        
        // Filter kategori - project must have ALL selected categories (AND logic)
        if (!empty($kategori_filter) && is_array($kategori_filter)) {
            $count_needed = count($kategori_filter);
            $placeholders = implode(',', array_fill(0, $count_needed, '?'));
            // Check that project has ALL selected categories by counting matches
            $where_conditions[] = "(
                SELECT COUNT(DISTINCT pc_filter.id_kategori) 
                FROM tbl_project_category pc_filter 
                WHERE pc_filter.id_project = p.id_project 
                AND pc_filter.id_kategori IN ($placeholders)
            ) = $count_needed";
            foreach ($kategori_filter as $kat_id) {
                $params[] = intval($kat_id);
                $types .= "i";
            }
        }
        
        // Order by
        $order_by = "p.id_project DESC"; // default: terbaru
        switch ($sort) {
            case 'judul_asc':
                $order_by = "p.judul ASC";
                break;
            case 'judul_desc':
                $order_by = "p.judul DESC";
                break;
            case 'terlama':
                $order_by = "p.tanggal_selesai ASC";
                break;
            case 'rating':
                $order_by = "avg_rating DESC";
                break;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        // Query untuk count total
        $count_query = "SELECT COUNT(DISTINCT p.id_project) as total
                FROM tbl_project p
                WHERE $where_clause";
        
        if (!empty($params)) {
            $stmt = $this->db->prepare($count_query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $count_result = $stmt->get_result();
            $total = $count_result->fetch_assoc()['total'];
            $stmt->close();
        } else {
            $count_result = $this->db->query($count_query);
            $total = $count_result->fetch_assoc()['total'];
        }
        
        // Query untuk data dengan pagination
        $offset = ($page - 1) * $per_page;
        $query = "SELECT p.*, 
                GROUP_CONCAT(DISTINCT c.nama_kategori ORDER BY c.nama_kategori SEPARATOR ', ') as kategori,
                GROUP_CONCAT(DISTINCT c.warna_hex ORDER BY c.nama_kategori SEPARATOR ',') as warna,
                AVG(r.skor) as avg_rating,
                COUNT(DISTINCT r.id_rating) as total_rating
                FROM tbl_project p
                LEFT JOIN tbl_project_category pc ON p.id_project = pc.id_project
                LEFT JOIN tbl_category c ON pc.id_kategori = c.id_kategori
                LEFT JOIN tbl_rating r ON p.id_project = r.id_project
                WHERE $where_clause
                GROUP BY p.id_project
                ORDER BY $order_by
                LIMIT ? OFFSET ?";
        
        $params[] = $per_page;
        $params[] = $offset;
        $types .= "ii";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $karya_list = [];
        while ($row = $result->fetch_assoc()) {
            $karya_list[] = $row;
        }
        $stmt->close();
        
        $total_pages = ceil($total / $per_page);
        
        return [
            'data' => $karya_list,
            'total' => $total,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'per_page' => $per_page
        ];
    }
    
    /**
     * Tambah karya baru (untuk admin)
     * 
     * @param array $data Data karya
     * @return int|false ID karya baru atau false jika gagal
     */
    public function tambahKarya($data) {
        // TODO: Implementasi method ini
        // Akan digunakan untuk refaktor proses_tambah_karya.php
        return false;
    }
    
    /**
     * Update karya (untuk admin)
     * 
     * @param int $id ID karya
     * @param array $data Data yang akan diupdate
     * @return bool
     */
    public function updateKarya($id, $data) {
        // TODO: Implementasi method ini
        return false;
    }
    
    /**
     * Hapus karya (untuk admin)
     * 
     * @param int $id ID karya
     * @return bool
     */
    public function hapusKarya($id) {
        // TODO: Implementasi method ini
        return false;
    }
}

