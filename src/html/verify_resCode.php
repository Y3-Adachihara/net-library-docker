<?php
    session_start();

    // CSRFトークン発行関数(発行するだけで、セッション変数への保存は行わないから注意！)
    function csrf_token_generate(): string {
        $toke_byte = random_bytes(16);
        $csrf_token = bin2hex($toke_byte);
        return $csrf_token;
    }

    // CSRFトークンセット関数
    function set_csrf_token(String $csrf_token): void {
        // トークンを隠し属性として送るためのhtmlコードを記述
        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') . '">';
    }

    //CSRFトークンがセットされていなかったらセッションにセットする
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = csrf_token_generate();
    }

    $csrf_token = $_SESSION['csrf_token'];

    // 予約番号の入力を促すメッセージを表示
    if (isset($_SESSION['reserved_lend_message'])) {
        $message = $_SESSION['reserved_lend_message'];
        echo "<script>alert('" . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . "');</script>";
        unset($_SESSION['reserved_lend_message']);
    }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>貸出_予約番号入力</title>
    <link rel = "stylesheet" href="../css/.css">
</head>

<body>
    <h1>予約番号を入力してください</h1>

    <form action = "../php/reserved_book_lend.php" method = "POST">

    <?php set_csrf_token($csrf_token); ?>
        <label>予約番号：</label>
        <input type="text" name="reservation_number" placeholder="0123" required></input>

        <button type="button" onclick = "location.href='貸出返却.php'">戻る</button>
        <button type="submit">OK</button>
    </form>
</body>