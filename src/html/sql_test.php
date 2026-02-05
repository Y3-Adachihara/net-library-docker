<?php
require_once '../db_connect.php';   //ディレクトリパス修正

$db = new db_connect();
$db->connect();

try {
    // book_infoを主として、book_stackを外部結合
    $sql = "SELECT 
                bi.isbn, 
                bi.title, 
                bi.author_name, 
                bi.publisher, 
                bs.book_id, 
                bs.school_id, 
                bs.status_id
            FROM book_info AS bi
            LEFT OUTER JOIN book_stack AS bs ON bi.isbn = bs.isbn
            ORDER BY bi.isbn ASC";
    //クエリの準備と実行
    $stmt = $db->pdo->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage();
} finally {
    $db->close();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>書籍所蔵一覧</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .no-stack { color: #999; font-style: italic; }
    </style>
</head>
<body>
    <h1>書籍・所蔵 結合一覧</h1>
    <table>
        <thead>
            <tr>
                <th>ISBN</th>
                <th>タイトル</th>
                <th>著者</th>
                <th>出版社</th>
                <th>所蔵ID (Book ID)</th>
                <th>学校ID</th>
                <th>状態</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['isbn'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['author_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['publisher'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                
                <?php if ($row['book_id']): ?>
                    <td><?= htmlspecialchars($row['book_id'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['school_id'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['status_id'], ENT_QUOTES, 'UTF-8') ?></td>
                <?php else: ?>
                    <td colspan="3" class="no-stack">所蔵なし（本棚に在庫がありません）</td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <br>
    <a href="index.php">戻る</a>
</body>
</html>