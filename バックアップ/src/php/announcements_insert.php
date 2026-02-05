<?php
header("Content-Type: text/html; charset=UTF-8");

require_once '../db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $db = new db_connect();
        $db->connect();

        $sql = "INSERT INTO announcements (
                    announcements_category, 
                    announcements_title, 
                    announcements_content, 
                    announcements_date, 
                    is_active
                ) VALUES (?, ?, ?, NOW(), 1)";

        $stmt = $db->pdo->prepare($sql);
        $stmt->execute([
            $_POST['announcements_category'],
            $_POST['announcements_title'],
            $_POST['announcements_content']
        ]);

        echo "<script>alert('お知らせを登録しました'); location.href='../html/librarian_myPage.php';</script>";

    } catch (Exception $e) {
        echo "エラー: " . $e->getMessage();
    }
}
?>