<?php
session_start();
require_once '../db_connect.php';

// CSRFチェックやログインチェックは省略

$items = $_POST['items'] ?? [];

if (!empty($items)) {
    try {
        $db = new db_connect();
        $db->connect();
        $pdo = $db->pdo;

        $pdo->beginTransaction();

        $sql = "UPDATE book_stack SET status_id = :next_status WHERE book_id = :book_id";
        $stmt = $pdo->prepare($sql);

        foreach ($items as $item) {
            $stmt->bindValue(':next_status', $item['next_status'], PDO::PARAM_INT);
            $stmt->bindValue(':book_id', $item['book_id'], PDO::PARAM_INT);
            $stmt->execute();
            
            // ★もし必要なら：予約完了通知などをここで送る処理を追加したりできます
        }

        $pdo->commit();
        $_SESSION['message'] = "書籍の状態を更新しました。";

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['message'] = "エラーが発生しました: " . $e->getMessage();
    }
}

header("Location: ../html/librarian_incoming_books.php");
exit();
?>