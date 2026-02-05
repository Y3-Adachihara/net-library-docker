<?php
session_start();
require_once '../db_connect.php';

try {
    $db = new db_connect();
    $db->connect();

    // 修正版SQL：bookテーブルにあるタイトルを直接集計します
    // これなら「紐付けエラー」が起きません
    $sql = "SELECT 
                bi.title, 
                bi.author_name AS author, 
                COUNT(l.lending_id) AS rental_count
            FROM lending l
            INNER JOIN book_stack bs ON l.book_id = bs.book_id
            LEFT OUTER JOIN book_info bi ON bs.isbn = bi.isbn
            WHERE l.return_date IS NOT NULL
            GROUP BY bi.title, bi.author_name
            ORDER BY rental_count DESC
            LIMIT 10";

    $stmt = $db->pdo->prepare($sql);
    $stmt->execute();
    $ranking_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("データベースエラー: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
// db_connectクラスにcloseメソッドがない場合は削除可
// finally {
//    $db->close();
// }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>貸出数ランキング</title>
    <link rel="stylesheet" href="../css/ランキング.css"> 
    <style>
        /* CSSが見つからない場合の予備スタイル */
        body { font-family: "Meiryo", sans-serif; background-color: #f4f4f4; text-align: center; }
        .container { background: #fff; width: 90%; max-width: 800px; margin: 30px auto; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .ranking-table { width: 100%; margin: 20px auto; border-collapse: collapse; }
        .ranking-table th, .ranking-table td { border-bottom: 1px solid #ddd; padding: 15px; text-align: left; }
        .ranking-table th { background-color: #f8f9fa; }
        
        .rank-1 { background-color: #fff9c4; } 
        .rank-2 { background-color: #f5f5f5; } 
        .rank-3 { background-color: #fff0e0; } 
        
        .rank-badge { 
            display: inline-block; width: 30px; height: 30px; line-height: 30px; 
            border-radius: 50%; text-align: center; font-weight: bold; color: white; background: #ccc; 
        }
        .rank-1 .rank-badge { background: #ffd700; }
        .rank-2 .rank-badge { background: #c0c0c0; }
        .rank-3 .rank-badge { background: #cd7f32; }

        .count-badge { background: #333; color: #fff; padding: 4px 10px; border-radius: 12px; font-size: 0.9em; }
        
        .btn-back {
            display: inline-block; padding: 10px 20px; background: #333; color: #fff; 
            text-decoration: none; border-radius: 5px; margin-top: 20px;
        }
        .btn-back:hover { background: #555; }
    </style>
</head>
<body>

<div class="container">
    <h1>🏆 貸出人気ランキング (TOP 10)</h1>

    <table class="ranking-table">
        <thead>
            <tr>
                <th style="width: 10%;">順位</th>
                <th>タイトル</th>
                <th>著者</th>
                <th style="width: 15%;">貸出回数</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $rank = 1;
            if (!empty($ranking_results)):
                foreach ($ranking_results as $row): 
                    $rank_class = '';
                    if ($rank === 1) $rank_class = 'rank-1';
                    elseif ($rank === 2) $rank_class = 'rank-2';
                    elseif ($rank === 3) $rank_class = 'rank-3';
            ?>
                <tr class="<?php echo $rank_class; ?>">
                    <td>
                        <span class="rank-badge"><?php echo $rank; ?></span>
                    </td>
                    <td><?php echo htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['author'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><span class="count-badge"><?php echo $row['rental_count']; ?>回</span></td>
                </tr>
            <?php 
                    $rank++;
                endforeach; 
            else:
            ?>
                <tr><td colspan="4">まだ貸出データがありません。</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="librarian_myPage.php" class="btn-back">マイページへ戻る</a>
</div>

</body>
</html>