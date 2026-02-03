<?php
session_start();
require_once '../db_connect.php';

// CSRFトークン生成
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// チェックされた本がなければ戻る
if (empty($_POST['process_list'])) {
    header("Location: librarian_incoming_books.php");
    exit();
}

// POSTデータの受け取り
$process_list = $_POST['process_list']; // チェックされたbook_idの配列
$items = $_POST['items'];               // 全件の詳細データ items[book_id][key]

function h($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>処理内容確認</title>
    <link rel="stylesheet" href="../css/librarian_myPage.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn-submit { background-color: #28a745; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        .btn-back { background-color: #6c757d; color: white; padding: 10px 20px; border: none; cursor: pointer; text-decoration: none; display:inline-block;}
    </style>
</head>
<body>
    <h2>処理内容の確認</h2>
    <p>以下の内容で更新します。間違いがなければ「確定する」を押してください。</p>

    <form action="../php/librarian_incoming_exec.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
        
        <table>
            <tr>
                <th>書籍ID</th>
                <th>タイトル</th>
                <th>処理内容</th>
            </tr>
            <?php 
            // チェックされたIDだけをループ処理
            foreach ($process_list as $book_id): 
                // items配列から、そのIDに対応する情報を取得
                if (!isset($items[$book_id])) continue;
                
                $data = $items[$book_id];
                $title = $data['title'];
                $next_status = $data['next_status'];
                $student_name = $data['student_name'];

                // 表示用テキストの生成
                if ($next_status == 3) {
                    $action_text = "【予約取り置き】 予約取り置き画面でステータスを変更<br><small>予約者: " . h($student_name) . "</small>";
                    $row_style = "background-color: #fff0f0;"; // 薄い赤（注意喚起）
                } else if ($next_status == 8) {
                    $action_text = "【配送取り置き】 この画面の処理が完了後、配送取り置きボックスに本を格納<br><small>次の状態: " . h($next_status) . ":配送待ち（返却配送）</small>";
                    $row_style = "background-color: #fff8e1;";
                } else {
                    $action_text = "【配架】 通常の書架へ移動";
                    $row_style = "";
                }
            ?>
                <tr style="<?php echo $row_style; ?>">
                    <td><?php echo h($book_id); ?></td>
                    <td><?php echo h($title); ?></td>
                    <td><?php echo $action_text; // htmlタグを含むためh()しない箇所あり ?></td>
                </tr>

                <input type="hidden" name="update_items[<?php echo h($book_id); ?>][book_id]" value="<?php echo h($book_id); ?>">
                <input type="hidden" name="update_items[<?php echo h($book_id); ?>][next_status]" value="<?php echo h($next_status); ?>">
            
            <?php endforeach; ?>
        </table>

        <a href="librarian_incoming_books.php" class="btn-back">戻る</a>
        <button type="submit" class="btn-submit">確定する</button>
    </form>
</body>
</html>