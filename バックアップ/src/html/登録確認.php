<?php
session_start();

// POSTデータがなければ入力画面へ戻す（URL直打ち対策）
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: ../html/書籍登録.html");
    exit;
}

// データの受け取り
// htmlspecialcharsを使って、悪意のあるスクリプトを無効化（XSS対策）します
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

$title = $_POST["title"];
$author_name = $_POST["author-name"];
$author_kana = $_POST["author-kana"];
$isbn = $_POST["isbn"];

// 日付の結合
$year = $_POST["publication_year"];
$month = $_POST["publication_month"];
$day = $_POST["publication_day"];
$publication_year_display = $year . '年' . $month . '月' . $day . '日'; 

$rui = $_POST["genre-rui"];
$mou = $_POST["genre-mou"];
$me = $_POST["genre-me"];
$book_id = $rui . $mou . $me;
$publisher = $_POST["publisher"];
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登録内容確認</title>
    <link rel="stylesheet" href="../css/書籍登録.css"> <style>
        /* 確認画面用の追加スタイル */
        .confirm-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .confirm-table th, .confirm-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .confirm-table th { background-color: #f2f2f2; width: 30%; }
        .alert-msg { color: red; font-weight: bold; text-align: center; }
    </style>
</head>
<body>

<div class="container">
    <h1>登録内容の確認</h1>
    <p class="alert-msg">以下の内容で登録しますか？</p>

    <form method="POST" action="../php/書籍登録.php">
        
        <table class="confirm-table">
            <tr>
                <th>書籍名</th>
                <td><?php echo h($title); ?></td>
            </tr>
            <tr>
                <th>著者名</th>
                <td><?php echo h($author_name); ?></td>
            </tr>
            <tr>
                <th>著者名(カナ)</th>
                <td><?php echo h($author_kana); ?></td>
            </tr>
            <tr>
                <th>ISBN</th>
                <td><?php echo h($isbn); ?></td>
            </tr>
            <tr>
                <th>ジャンル(類)</th>
                <td><?php echo h($rui); ?></td>
            </tr>
            <tr>
                <th>ジャンル(網)</th>
                <td><?php echo h($mou); ?></td>
            </tr>
            <tr>
                <th>ジャンル(目)</th>
                <td><?php echo h($me); ?></td>
            </tr>
            <tr>
                <th>出版日</th>
                <td><?php echo h($publication_year_display); ?></td>
            </tr>
            <tr>
                <th>出版社</th>
                <td><?php echo h($publisher); ?></td>
            </tr>
        </table>

        <input type="hidden" name="title" value="<?php echo h($title); ?>">
        <input type="hidden" name="author-name" value="<?php echo h($author_name); ?>">
        <input type="hidden" name="author-kana" value="<?php echo h($author_kana); ?>">
        <input type="hidden" name="isbn" value="<?php echo h($isbn); ?>">
        
        <input type="hidden" name="publication_year" value="<?php echo h($year); ?>">
        <input type="hidden" name="publication_month" value="<?php echo h($month); ?>">
        <input type="hidden" name="publication_day" value="<?php echo h($day); ?>">

        <input type="hidden" name="genre-rui" value="<?php echo h($rui); ?>">
        <input type="hidden" name="genre-mou" value="<?php echo h($mou); ?>">
        <input type="hidden" name="genre-me" value="<?php echo h($me); ?>">
        <input type="hidden" name="publisher" value="<?php echo h($publisher); ?>">

        <div class="button-container">
            <button type="button" class="btn" onclick="history.back()">修正する</button>
            <button type="submit" class="btn btn-blue">登録する</button>
        </div>
    </form>
</div>

</body>
</html>