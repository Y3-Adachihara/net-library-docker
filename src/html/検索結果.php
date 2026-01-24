<?php
    require_once 'db_connect.php';// DB接続ファイル
    session_start();

    // セキュリティ対策（文字化け・攻撃防止）
    function h($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    // 1. 検索画面からの入力を受け取る
    $title     = isset($_GET["search-title"]) ? $_GET["search-title"] : '';
    $id        = isset($_GET["search-id"]) ? $_GET["search-id"] : '';
    // ジャンルは3つを繋げて表示するために取得
    $rui       = isset($_GET["genre-rui"]) ? $_GET["genre-rui"] : '';
    $mou       = isset($_GET["genre-mou"]) ? $_GET["genre-mou"] : '';
    $me        = isset($_GET["genre-me"]) ? $_GET["genre-me"] : '';
    $publisher = isset($_GET["search-publisher"]) ? $_GET["search-publisher"] : '';
    $author    = isset($_GET["search-author"]) ? $_GET["search-author"] : '';

    $results = [];

    try {
        $db = new db_connect();
        $db->connect(); 

        // 2. 検索用SQLの作成
        $sql = "SELECT * FROM book WHERE 1 = 1";
        $params = [];

        if (!empty($title)) {
            $sql .= " AND title LIKE ?";
            $params[] = "%" . $title . "%";
        }
        if (!empty($id)) {
            $sql .= " AND id = ?";
            $params[] = $id;
        }
        if (!empty($publisher)) {
            $sql .= " AND publisher LIKE ?";
            $params[] = "%" . $publisher . "%";
        }
        if (!empty($author)) {
            $sql .= " AND author LIKE ?";
            $params[] = "%" . $author . "%";
        }

        $stmt = $db->pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();

    } catch (PDOException $e) {
        echo "データベースエラー: " . h($e->getMessage());
        exit;
    } catch (Exception $e) {
        echo "エラー: " . h($e->getMessage());
        exit;
    }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>検索結果一覧</title>
    <link rel="stylesheet" href="../css/検索結果.css">
</head>
<body>

<div class="container">
    <h1>検索結果一覧</h1>

    <div class="selected-criteria">
        <div class="criteria-label">選んだ項目</div>
        <div class="criteria-content">
            <ul>
                <li>・タイトル：<?php echo h($title); ?></li>

                <li>・選択したジャンル：<?php echo h($rui . ' ' . $mou . ' ' . $me); ?></li>

                <li>・出版社：<?php echo h($publisher); ?></li>

                <li>・著者名：<?php echo h($author); ?></li>
            </ul>
        </div>
    </div>

    <table class="result-table">
        <thead>
            <tr>
                <th colspan="3">検索一覧</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($results) > 0): ?>
                <?php foreach ($results as $row): ?>
                <tr>
                    <td class="book-title">
                        <?php echo h($row['title']); ?>
                    </td>

                    <td class="status">
                        <?php 
                        // DBの status カラムが 1 なら「〇」、それ以外なら「×」と表示する例
                        // あなたのDBに合わせて条件を変えてください
                        if (isset($row['status']) && $row['status'] == 1) {
                            echo '〇';
                        } else {
                            echo '×'; // 貸出中など
                        }
                        ?>
                    </td>

                    <td class="action">
                        <button type="button" class="reserve-btn" onclick="location.href='マイページ(仮).html'">
                            予約
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" style="text-align:center;">該当する本が見つかりませんでした。</td>
                </tr>
            <?php endif; ?>
            
            </tbody>
    </table>

    <div class="footer-actions">
        <button type="button" class="btn-back" onclick="history.back()">
            戻る
        </button>
    </div>
</div>

</body>
</html>