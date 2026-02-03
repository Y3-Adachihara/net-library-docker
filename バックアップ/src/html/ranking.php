<?php
require_once '../db_connect.php'; // 既存の接続クラスを読み込み
session_start();

try {
    $db = new db_connect();
    $db->connect();

    // SQLの解説：
    // 1. rentalテーブルのbook_idでグループ化(GROUP BY)
    // 2. それぞれの貸出回数をカウント(COUNT)
    // 3. bookテーブルを結合(LEFT JOIN)してタイトルなどを取得
    // 4. カウントが多い順に並び替え(ORDER BY DESC)
    $sql = "SELECT 
                bi.title, 
                bi.author_name AS author, 
                COUNT(l.lending_id) AS rental_count
            FROM lending l
            INNER JOIN book b ON l.book_id = b.book_id
            INNER JOIN book_info bi ON b.isbn = bi.isbn
            GROUP BY bi.isbn   -- ← ここが重要！ISBN（同じ本）でまとめる
            ORDER BY rental_count DESC
            LIMIT 10";

    $stmt = $db->pdo->prepare($sql);
    $stmt->execute();
    $ranking_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("データベースエラー: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
} finally {
    $db->close();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>貸出数ランキング</title>
    <link rel="stylesheet" href="../css/ランキング.css"> <style>
        /* 簡単な装飾 */
        .ranking-table { width: 80%; margin: 20px auto; border-collapse: collapse; }
        .ranking-table th, .ranking-table td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        .rank-1 { background-color: #ffd700; font-weight: bold; } /* 金 */
        .rank-2 { background-color: #c0c0c0; } /* 銀 */
        .rank-3 { background-color: #cd7f32; } /* 銅 */
        .count-badge { background: #333; color: #fff; padding: 2px 8px; border-radius: 10px; }
    </style>
</head>
<body>

<div class="container">
    <h1>📚 貸出人気ランキング (TOP 10)</h1>

    <table class="ranking-table">
        <thead>
            <tr>
                <th>順位</th>
                <th>タイトル</th>
                <th>著者</th>
                <th>貸出回数</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $rank = 1;
            foreach ($ranking_results as $row): 
                // 3位までクラスを変える
                $rank_class = '';
                if ($rank === 1) $rank_class = 'rank-1';
                elseif ($rank === 2) $rank_class = 'rank-2';
                elseif ($rank === 3) $rank_class = 'rank-3';
            ?>
                <tr class="<?php echo $rank_class; ?>">
                    <td><?php echo $rank; ?>位</td>
                    <td><?php echo htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['author'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><span class="count-badge"><?php echo $row['rental_count']; ?>回</span></td>
                </tr>
            <?php 
                $rank++;
            endforeach; 
            ?>
        </tbody>
    </table>

    <div style="text-align: center;">
        <button type="button" onclick="location.href='librarian_myPage.php'">マイページへ戻る</button>
    </div>
</div>

</body>
</html>