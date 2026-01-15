<?php
    session_start();
    require_once '../db_connect.php';
    
    $db = new db_connect();
    $db->connect();
    $error_message = $_SESSION['error'] ?? '';
    if (isset($_SESSION['error'])) {
        echo "<script>alert('" . htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') . "');</script>";
        unset($_SESSION['error']);
    }

    $sql = "SELECT * FROM school";
    $stmt = $db->pdo->prepare($sql);
    $stmt->execute();

    $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>司書用ログインページ</title>
    <link rel="stylesheet" href="../css/login.css">
</head>

<body>
    <header class="main-header">
        <div class="header-logo">
            <a href="login_copy.php">インターネット図書館</a>
        </div>
        <nav class="header-nav">
            <ul>
                <li><a href="#" onclick="alert('余裕があったら、ログインの方法やヘルプ等を説明するページを作ってもいいかも？'); return false;">はじめての方へ</a></li>
                <li><a href="#" onclick="alert('このシステムを利用している学校一覧を表示してもいいかも'); return false;">学校一覧</a></li>
                <li><a href="#" onclick="alert('（難しいので）言語切り替え機能は準備中です'); return false;">言語切り替え</a></li>
            </ul>
        </nav>
    </header>

    <div class="login-container">
        <h1>司書ログイン</h1>
        <form action="../php/librarian_auth.php" method="POST">
            <div class="form-group">

                <?php 
                    $toke_byte = random_bytes(16);
                    $csrf_token = bin2hex($toke_byte);
                    // CSRF対策用のトークンをセッションに保存
                    $_SESSION['csrf_token'] = $csrf_token;
                ?>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <!-- 学校を選択させる -->
                <label for="school">学校:</label>
                <select name = "school" required>
                    <option value = "">選択してください</option>
                    <?php foreach ($schools as $school): ?>
                        <?php if ($school['school_id'] == 0) continue; ?>
                        <option value = "<?php echo htmlspecialchars($school['school_id']); ?>">
                            <?php echo htmlspecialchars($school['school_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!--ログインIDは入力させる-->
                <label for ="login_id">ログインID:</label>
                <input type="text" id="login_id" name="login_id" required>
            </div>

            <div class="form-group">
                <label for="password">パスワード:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">ログイン</button>
        </form>

        <!-- 新規登録と司書用のボタン hrefの値は仮。遷移先の画面が完成したときに設定する -->
        <div class="sub-actions">
            <button onclick="location.href='../html/student_login.php'">戻る</button>
            <button onclick="location.href='../html/librarian_register.php'">新規登録がお済でない方はこちら</button>
        </div>
    </div>
</body>
</html>