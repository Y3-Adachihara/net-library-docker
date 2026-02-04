<?php
require_once '../db_connect.php'; // DB接続ファイル
session_start();

if (!isset($_SESSION['librarian_id'])) {
        // 司書としてログインしていない場合、ログインページへリダイレクト
        $_SESSION['message'] = "司書としてログインしてください。";
        header("Location: librarian_login.php");
        exit();
    }

// セキュリティ対策
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// 1. 検索画面からの入力を受け取る
$title     = isset($_GET["search-title"]) ? $_GET["search-title"] : '';
$id        = isset($_GET["search-id"]) ? $_GET["search-id"] : '';
$rui       = isset($_GET["genre-rui"]) ? $_GET["genre-rui"] : '';
$mou       = isset($_GET["genre-mou"]) ? $_GET["genre-mou"] : '';
$me        = isset($_GET["genre-me"]) ? $_GET["genre-me"] : '';
$publisher = isset($_GET["search-publisher"]) ? $_GET["search-publisher"] : '';
$author    = isset($_GET["search-author"]) ? $_GET["search-author"] : '';
$librarian_school_id = isset($_SESSION['librarian_school_id']) ? $_SESSION['librarian_school_id'] : null;

$librarian_school_name = '';
$librarian_fullname = '';

$results = [];

// CSRFトークン発行関数(発行するだけで、セッション変数への保存は行わないから注意！)
    function csrf_token_generate(): string {
        $toke_byte = random_bytes(16);
        $csrf_token = bin2hex($toke_byte);
        return $csrf_token;
    }
    // CSRFトークンの生成
    $csrf_token = csrf_token_generate();

    // CSRFトークンセット関数
    function set_csrf_token(String $csrf_token): void {
        // CSRF対策用のトークンをセッションに保存
        $_SESSION['csrf_token'] = $csrf_token;
        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') . '">';
    }

try {
    $db = new db_connect();
    $db->connect();

    $sql = "SELECT librarian.family_name, librarian.first_name, school.school_name FROM librarian LEFT JOIN school ON librarian.school_id = school.school_id WHERE librarian_id = ?";
    $stmt = $db->pdo->prepare($sql);
    $stmt->execute([$_SESSION['librarian_id']]);
    $librarian = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($librarian) {
        $librarian_fullname = $librarian['family_name'] . ' ' . $librarian['first_name'];
        $librarian_school_name = $librarian['school_name'];
    }

        // 司書の学校名を取得

    $sql = "SELECT * FROM school";
        $stmt = $db->pdo->prepare($sql);
        $stmt->execute();
        $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($schools) {
            foreach ($schools as $school) {
                if ($school['school_id'] == $librarian_school_id) {
                    $librarian_school_name = $school['school_name'];
                    break;
                }
            }
        }

    $sql = "SELECT * FROM book_status";
    $stmt = $db->pdo->prepare($sql);
    $stmt->execute();
    $status_list = $stmt->fetchAll(PDO::FETCH_ASSOC); // status_id と status_name の連想配列を取得

    // 2. 検索用SQLの作成
    $sql = "SELECT * FROM book_stack LEFT OUTER JOIN book_info ON book_stack.isbn = book_info.isbn LEFT OUTER JOIN book_status ON book_stack.status_id = book_status.status_id WHERE 1 = 1";
    $params = [];

    if (!empty($title)) {
        $sql .= " AND book_info.title LIKE ?";
        $params[] = "%" . $title . "%";
    }
    if (!empty($id)) {
        // 入力されたIDで検索する場合
        $sql .= " AND book_stack.book_id LIKE ?"; 
        $params[] = $id . "%";
    }
    if (!empty($publisher)) {
        $sql .= " AND publisher LIKE ?";
        $params[] = "%" . $publisher . "%";
    }
    if (!empty($author)) {
        $sql .= " AND author_name LIKE ?";
        $params[] = "%" . $author . "%";
    }
    // ジャンル検索が必要な場合はここに追加ロジックが入りますが、
    // 今回はbook_id表示の修正に集中します。
    // 類・網・目を連結して検索用文字列を作る（例: 類9, 網1, 目3 → "913"）
    $genre_code = "";
    
    // "0"類の場合、empty()だと空扱いされるため、!== '' でチェックします
    if ($rui !== '') {
        $genre_code .= $rui;
        
        if ($mou !== '') {
            $genre_code .= $mou;
            
            if ($me !== '') {
                $genre_code .= $me;
            }
        }
    }

    // ジャンルコードがある場合、検索条件に追加
    if ($genre_code !== '') {
        $sql .= " AND book_stack.book_id LIKE ?";
        $params[] = $genre_code . "%";
    }

    if (empty($params)) {
        // 生徒の学校IDで絞り込み
        if ($librarian_school_id !== null) {
            $sql .= " AND book_stack.position = ?";
            $params[] = $librarian_school_id;
        }
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
}finally {
        $db->close(); // DB接続解除   
    }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>検索結果一覧</title>
    <link rel="stylesheet" href="../css/検索結果.css">
    <style>
        .result-table th, .result-table td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        .book-id { font-weight: bold; }
    </style>
</head>
<body>
<!-- ログアウトボタンを押したときのCSRFトークン発行 -->
<form method="POST" action = "../php/logout.php" id = "logout_form">
        <?php 
            set_csrf_token($csrf_token);
        ?>
        <input type="hidden" name = "page_id" value= "0">
    </form>


<header>
    <a href="#"><?php echo htmlspecialchars($librarian_fullname); ?> さん(<?php echo htmlspecialchars($librarian_school_name); ?>)の検索画面</a>
    <button class="logout-btn" onclick="confirmLogout()">ログアウト</button>
</header>

<div class="container">
    <h1>検索結果一覧_司書用</h1>

    <div class="selected-criteria">
        <div class="criteria-label">選んだ項目</div>
        <div class="criteria-content">
            <ul>
                <?php if (!empty($title)): ?>
                <li>タイトル：<?php echo h($title); ?></li>
                <?php endif; ?>
                <?php if (!empty($id)): ?>
                <li>識別番号：<?php echo h($id); ?></li>
                <?php endif; ?>
                <?php if (!empty($rui) || !empty($mou) || !empty($me)): ?>
                <li>選択したジャンル：<?php echo h($rui . ' ' . $mou . ' ' . $me); ?></li>
                <?php endif; ?>
                <?php if (!empty($publisher)): ?>
                <li>出版社：<?php echo h($publisher); ?></li>
                <?php endif; ?>
                <?php if (!empty($author)): ?>
                <li>著者名：<?php echo h($author); ?></li>
                <?php endif; ?>
                <?php if (empty($title) && empty($id) && empty($rui) && empty($mou) && empty($me) && empty($publisher) && empty($author)): ?>
                <li>検索条件が指定されなかったため、<?php echo h($librarian_school_name); ?>の全書籍を表示します。</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <table class="result-table">
        <thead>
            <tr>
                <th>識別番号</th>
                <th>タイトル</th>
                <th>出版社</th>
                <th>場所</th>
                <th>予約しない貸出</th>
                <th>書籍状態</th>
                <th>貸出</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($results) > 0): ?>
                <?php foreach ($results as $row): ?>
                <tr>
                    <td class="book-id">
                        <?php echo h($row['book_id']); ?>
                    </td>

                    <td class="book-title">
                        <?php echo h($row['title']); ?>
                    </td>

                    <td class="book-publisher">
                        <?php echo h($row['publisher']); ?>
                    </td>

                    <td class="book-location">
                        <?php 
                            $pos = $row['position'];
                            foreach ($schools as $school) {
                                if ($school['school_id'] == $pos) {
                                    echo h($school['school_name']);
                                    break;
                                }
                            }
                        ?>
                    </td>

                    <td class="status">
                        <?php 
                        if (isset($row['status_id']) && $row['status_id'] == 1 && $row['school_id'] == $librarian_school_id) {
                            echo '<span style="color:blue;">〇</span>';
                        } else {
                            echo '<span style="color:red;">×</span>';
                        }
                        ?>
                    </td>

                    <td class="book-status">
                        <?php 
                            // status_id に基づいて status_name を表示
                            $status_name = '';
                            foreach ($status_list as $status) {
                                if ($status['status_id'] == $row['status_id']) {
                                    $status_name = $status['status_name'];
                                    break;
                                }
                            }
                            echo h($status_name);
                        ?>

                    <td class="action">
                        <?php //if (isset($row['status_id']) && $row['status_id'] == 1): ?>
                            <button type="button" class="borrow-btn" onclick="location.href='貸出返却.php?book_id=<?php echo h($row['book_id']); ?>'">
                                貸出・返却
                            </button>
                        <?php //else: ?>
                            <!--<button type="button" disabled style="background:#ccc;">不可</button>-->
                        <?php //endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center;">該当する本が見つかりませんでした。</td>
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
<script>
    function confirmLogout() {
            if (confirm("ログアウトしますか？")) {
                document.getElementById('logout_form').submit();
            }
        }
</script>