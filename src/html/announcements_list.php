<?php
require_once '../db_connect.php'; 
session_start();

$error_message = null;

try {
    $db = new db_connect();
    $db->connect();

    // SQL作成：新しい日付順（DESC）に取得
    // is_active = 1 (公開中) のものだけを表示する設定にしています
    $sql = "SELECT * FROM announcements WHERE is_active = 1 ORDER BY announcements_date DESC";
    
    $stmt = $db->pdo->prepare($sql);
    $stmt->execute();
    $news_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
        error_log("DBエラー：" . $e->getMessage());
        $error_message = "データベース通信エラーが発生しました。しばらく経ってからやり直してください。"
    } catch (Exception $e) {
        error_log("予期せぬエラー：" . $e->getMessage());
        $error_message = "予期せぬエラーが発生しました。システム管理者にお問い合わせください。";
    } finally {
        $db->close();
    }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>図書館からのお知らせ</title>
    <link rel="stylesheet" href="../css/検索画面.css">
    
    <style>
        /* この画面専用のCSS（後でCSSファイルに移してもOK） */
        .news-container {
            max_width: 800px;
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .news-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        .news-item:last-child {
            border-bottom: none;
        }
        .news-header {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        .news-date {
            color: #888;
            font-size: 0.9em;
            margin-right: 15px;
        }
        /* カテゴリごとのバッジ装飾 */
        .category-badge {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            color: white;
            margin-right: 10px;
            font-weight: bold;
        }
        .cat-important { background-color: #ff5252; } /* 重要：赤 */
        .cat-event { background-color: #4caf50; }     /* イベント：緑 */
        .cat-new { background-color: #2196f3; }       /* 新着：青 */
        .cat-other { background-color: #9e9e9e; }     /* その他：グレー */

        .news-title {
            font-size: 1.1em;
            font-weight: bold;
            color: #333;
            margin: 0;
        }
        .news-content {
            margin-top: 5px;
            color: #555;
            font-size: 0.95em;
            line-height: 1.6;
            white-space: pre-wrap; /* 改行を反映させる */
        }
    </style>
</head>
<body>
<? if ($error_message != null): ?>
            <div style="color: red; padding: 20px; border: 1px solid red; background-color: #fee; text-align: center;">
                <h3>現在システムをご利用いただけません</h3>
                <p><?php echo h($error_message); ?></p>
            </div>
        <?php else: ?>
<div class="news-container">
    <h1>📢 図書館からのお知らせ</h1>

    <?php if (count($news_list) > 0): ?>
        
        <?php foreach ($news_list as $news): ?>
            <?php 
                $badge_class = 'cat-other';
                if ($news['announcements_category'] === '重要') $badge_class = 'cat-important';
                elseif ($news['announcements_category'] === 'イベント') $badge_class = 'cat-event';
                elseif ($news['announcements_category'] === '新着') $badge_class = 'cat-new';
                
                // 日付を見やすく整形 (例: 2026-02-03 10:00:00 → 2026/02/03)
                $date = date('Y/m/d', strtotime($news['announcements_date']));
            ?>

            <div class="news-item">
                <div class="news-header">
                    <span class="news-date"><?php echo $date; ?></span>
                    <span class="category-badge <?php echo $badge_class; ?>">
                        <?php echo htmlspecialchars($news['announcements_category'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <h3 class="news-title">
                        <?php echo htmlspecialchars($news['announcements_title'], ENT_QUOTES, 'UTF-8'); ?>
                    </h3>
                </div>
                <div class="news-content">
                    <?php echo htmlspecialchars($news['announcements_content'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
            </div>
        <?php endforeach; ?>

    <?php else: ?>
        <p>現在、お知らせはありません。</p>
    <?php endif; ?>

    <div style="text-align: center; margin-top: 30px;">
        <button type="button" class="btn" onclick="location.href='stu_myPage.php'">マイページへ戻る</button>
    </div>

</div>

</body>
<?php endif; ?>
</html>