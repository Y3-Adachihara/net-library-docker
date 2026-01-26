<?php
require_once '../db_connect.php'; // DB接続ファイル
session_start();

// セキュリティ対策
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// 学校IDと学校名の対応リスト（データに合わせて作成）
$school_list = [
    1 => '第一中学校',
    2 => '第二中学校',
    3 => '第三中学校',
    4 => '第四中学校',
    5 => '第五中学校',
    6 => '第六中学校',
    7 => '第七中学校',
    8 => '第八中学校',
    9 => '第九中学校',
    10 => '第十中学校'
];

// 1. 検索画面からの入力を受け取る
$title     = isset($_GET["search-title"]) ? $_GET["search-title"] : '';
$id        = isset($_GET["search-id"]) ? $_GET["search-id"] : '';
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
    // ※注意：ここに書かれているカラム名（title, book_idなど）がDBに存在する必要があります
    $sql = "SELECT * FROM book_stack LEFT OUTER JOIN book_info ON book_stack.isbn = book_info.isbn WHERE 1 = 1";
    $params = [];

    if (!empty($title)) {
        $sql .= " AND title LIKE ?";
        $params[] = "%" . $title . "%";
    }
    if (!empty($id)) {
        $sql .= " AND book_id = ?"; // データに合わせてカラム名を book_id にしました
        $params[] = $id;
    }
    if (!empty($publisher)) {
        $sql .= " AND publisher LIKE ?";
        $params[] = "%" . $publisher . "%";
    }
    if (!empty($author)) {
        $sql .= " AND author_name LIKE ?"; // データに合わせて author_name にしました
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
    <link rel="stylesheet" href="検索結果.css">
</head>
<body>

<div class="container">
    <h1>検索結果一覧</h1>

    <div class="selected-criteria">
        <div class="criteria-label">選んだ項目</div>
        <div class="criteria-content">
            <ul>
                <li>・タイトル：<?php echo h($title); ?></li>
                <li>・識別番号：<?php echo h($id); ?></li>
                <li>・選択したジャンル：<?php echo h($rui . ' ' . $mou . ' ' . $me); ?></li>
                <li>・出版社：<?php echo h($publisher); ?></li>
                <li>・著者名：<?php echo h($author); ?></li>
            </ul>
        </div>
    </div>

    <table class="result-table">
        <thead>
            <tr>
                <th>タイトル</th>
                <th>出版社</th>
                <th>場所</th>
                <th>状況</th>
                <th>予約</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($results) > 0): ?>
                <?php foreach ($results as $row): ?>
                <tr>
                    <td class="book-title">
                        <?php echo h($row['title']); ?>
                    </td>

                    <td class="book-publisher">
                        <?php echo h($row['publisher']); ?>
                    </td>

                    <td class="book-location">
                        <?php 
                            // $school_listの中に該当するIDがあれば名前を表示、なければ「不明」
                            $pos = $row['position']; // DBのカラム名が position の場合
                            echo h(isset($school_list[$pos]) ? $school_list[$pos] : '不明'); 
                        ?>
                    </td>

                    <td class="status">
                        <?php 
                        // status_id が 1 なら貸出可（〇）とする
                        if (isset($row['status_id']) && $row['status_id'] == 1) {
                            echo '<span style="color:blue;">〇</span>';
                        } else {
                            echo '<span style="color:red;">×</span>';
                        }
                        ?>
                    </td>

                    <td class="action">
                        <?php if (isset($row['status_id']) && $row['status_id'] == 1): ?>
                            <button type="button" class="reserve-btn" onclick="location.href='マイページ(仮).html'">
                                予約
                            </button>
                        <?php else: ?>
                            <button type="button" disabled style="background:#ccc;">不可</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align:center;">該当する本が見つかりませんでした。</td>
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