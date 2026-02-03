<?php
session_start();
require_once '../db_connect.php'; 

// POST以外は弾く
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: ../html/書籍登録.html");
    exit;
}

// --- データの受け取り ---
$title = $_POST["title"];
$author_name = $_POST["author-name"];
$author_kana = $_POST["author-kana"];
$isbn = $_POST["isbn"];
$publisher = $_POST["publisher"];

// 日付結合
$year = $_POST["publication_year"];
$month = $_POST["publication_month"];
$day = $_POST["publication_day"];
$publication_year = sprintf('%04d-%02d-%02d', $year, $month, $day);

// ジャンル（3桁）
$rui = $_POST["genre-rui"];
$mou = $_POST["genre-mou"];
$me = $_POST["genre-me"];
$genre_code = $rui . $mou . $me; // 例: 913

// セッション情報
$school_id = $_SESSION["librarian_school_id"] ?? 1;
$statu_id = 1;
$position = $school_id; 

$message = "";
$is_success = false;
$book_id = ""; // 初期化

try {
    $db = new db_connect();
    $db->connect();
    $pdo = $db->pdo;

    // トランザクション開始
    $pdo->beginTransaction();

    // ====================================================
    // 1. book_info (書籍基本情報) の登録
    // ====================================================
    // ISBNが主キーなので、既に存在する場合は無視(IGNORE)して進みます
    $sql_info = "INSERT IGNORE INTO book_info (
                    isbn, title, author_name, author_kana, publisher, publication_year
                ) VALUES (
                    :isbn, :title, :author_name, :author_kana, :publisher, :publication_year
                )";
    
    $stmt_info = $pdo->prepare($sql_info);
    $stmt_info->execute([
        ':isbn' => $isbn,
        ':title' => $title,
        ':author_name' => $author_name,
        ':author_kana' => $author_kana,
        ':publisher' => $publisher,
        ':publication_year' => $publication_year
    ]);

    // ====================================================
    // 2. book_id の自動生成ロジック (3桁 + 4桁 + 2桁)
    // ====================================================
    
    // 【ステップA】まず、このISBNの本が過去に登録されているかチェック
    // 登録されていれば、その「本の種類ID（真ん中4桁）」を再利用します。
    $sql_check = "SELECT book_id FROM book_stack WHERE isbn = :isbn LIMIT 1";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([':isbn' => $isbn]);
    $existing_data = $stmt_check->fetch(PDO::FETCH_ASSOC);

    $type_id_part = ""; // 真ん中の4桁

    if ($existing_data) {
        // ★パターン1：すでに同じ本（羅生門）がある場合
        // 既存のID（例: 913000101）から、真ん中の4桁（0001）を取り出します
        // substr(文字列, 開始位置0から数えて3番目, 4文字分)
        $type_id_part = substr($existing_data['book_id'], 3, 4);
    } else {
        // ★パターン2：全く新しい本（こころ）の場合
        // このジャンル（913）の中で、一番大きい「本の種類ID」を探して +1 します
        
        // SQL: IDの4文字目から4文字分を取得して最大値を探す
        $sql_max_type = "SELECT MAX(SUBSTRING(book_id, 4, 4)) FROM book_stack WHERE book_id LIKE :genre_prefix";
        $stmt_max = $pdo->prepare($sql_max_type);
        $stmt_max->execute([':genre_prefix' => $genre_code . '%']);
        $max_type_val = $stmt_max->fetchColumn();

        if ($max_type_val) {
            $next_type_val = intval($max_type_val) + 1;
        } else {
            $next_type_val = 1; // そのジャンルで最初の1冊目
        }
        
        // 4桁になるように0埋めする (例: 1 -> 0001)
        $type_id_part = sprintf('%04d', $next_type_val);
    }

    // ここまでのIDのベース（例: 9130001）
    $base_id = $genre_code . $type_id_part;

    // 【ステップB】冊数番号（下2桁）を決める
    // ベースID（9130001）で始まるIDの最大値を探す（現在何冊あるか）
    $sql_copy = "SELECT MAX(book_id) FROM book_stack WHERE book_id LIKE :base_id";
    $stmt_copy = $pdo->prepare($sql_copy);
    $stmt_copy->execute([':base_id' => $base_id . '%']);
    $max_full_id = $stmt_copy->fetchColumn();

    $copy_num = 1; // デフォルトは1冊目
    if ($max_full_id) {
        // 既に913000101があるなら、下2桁（01）を取り出して +1 する
        $current_copy = intval(substr($max_full_id, -2));
        $copy_num = $current_copy + 1;
    }

    // 最終的なIDを結合 (例: 913 + 0001 + 02)
    $book_id = $base_id . sprintf('%02d', $copy_num);


    // ====================================================
    // 3. book_stack (所蔵情報) の登録
    // ====================================================
    $sql_stack = "INSERT INTO book_stack(
                    book_id, isbn, school_id, status_id, position
                ) VALUES(
                    :book_id, :isbn, :school_id, :statu_id, :position
                )";

    $stmt_stack = $pdo->prepare($sql_stack);
    $stmt_stack->execute([
        ':book_id' => $book_id,
        ':isbn' => $isbn,
        ':school_id' => $school_id,
        ':statu_id' => $statu_id,
        ':position' => $position, 
    ]);

    // 全て成功したら確定
    $pdo->commit();
    
    $is_success = true;
    $message = "書籍の登録が完了しました。";

} catch (PDOException $e) {
    if (isset($pdo)) { $pdo->rollBack(); }
    $message = "データベースエラー: " . $e->getMessage();
} catch (Exception $e) {
    $message = "エラーが発生しました: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>登録結果</title>
    <link rel="stylesheet" href="../css/書籍登録.css">
    <style>
        .result-container { text-align: center; margin-top: 50px; }
        .message { font-size: 20px; font-weight: bold; margin-bottom: 30px; 
                   color: <?php echo $is_success ? '#28a745' : '#dc3545'; ?>; }
        .book-id-display { font-size: 24px; color: #333; margin-bottom: 20px; }
        .btn { 
            text-decoration: none; display: inline-block; padding: 10px 20px;
            background: #4a90e2; color: white; border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="result-container">
            <p class="message"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
            
            <?php if ($is_success): ?>
                <div class="book-id-display">
                    登録ID: <strong><?php echo $book_id; ?></strong>
                </div>
            <?php endif; ?>
            
            <a href="../html/librarian_myPage.php" class="btn">ホーム画面へ戻る</a>
        </div>
    </div>
</body>
</html>