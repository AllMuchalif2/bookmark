<?php
require_once 'config.php';
require_once 'db.php';

// Fungsi CRUD Kategori
function getCategories($db) {
    $stmt = $db->query("SELECT * FROM categories ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addCategory($db, $name) {
    $stmt = $db->prepare("INSERT INTO categories (name) VALUES (?)");
    return $stmt->execute([$name]);
}

function updateCategory($db, $id, $name) {
    $stmt = $db->prepare("UPDATE categories SET name = ? WHERE id = ?");
    return $stmt->execute([$name, $id]);
}

function deleteCategory($db, $id) {
    // Update bookmark yang terkait ke NULL
    $db->prepare("UPDATE bookmarks SET category_id = NULL WHERE category_id = ?")->execute([$id]);
    
    // Hapus kategori
    $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
    return $stmt->execute([$id]);
}

// Fungsi CRUD Bookmark
function getBookmarks($db) {
    $stmt = $db->query("
        SELECT b.*, c.name as category_name 
        FROM bookmarks b
        LEFT JOIN categories c ON b.category_id = c.id
        ORDER BY c.name, b.title
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addBookmark($db, $title, $url, $category_id) {
    $stmt = $db->prepare("INSERT INTO bookmarks (title, url, category_id) VALUES (?, ?, ?)");
    return $stmt->execute([$title, $url, $category_id]);
}

function updateBookmark($db, $id, $title, $url, $category_id) {
    $stmt = $db->prepare("UPDATE bookmarks SET title = ?, url = ?, category_id = ? WHERE id = ?");
    return $stmt->execute([$title, $url, $category_id, $id]);
}

function deleteBookmark($db, $id) {
    $stmt = $db->prepare("DELETE FROM bookmarks WHERE id = ?");
    return $stmt->execute([$id]);
}

// Fungsi untuk mendapatkan daftar projek Laragon
function getLaragonProjects() {
    $projects = [];
    $dir = LARAGON_WWW_PATH;
    
    if (is_dir($dir)) {
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item != '.' && $item != '..' && is_dir($dir . $item)) {
                $path = $dir . $item;
                $projects[] = [
                    'name' => $item,
                    'url' => $item . '.care:8001',
                    'modified' => date("Y-m-d H:i:s", filemtime($path))
                ];
            }
        }
    }
    
    return $projects;
}