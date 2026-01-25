<?php
    require_once '../db_connect.php';
    session_start();
    $db = new db_connect();
    $db->connect();

    $error_message = $_SESSION['message'] ?? '';
    if (isset($_SESSION['message'])) {
        echo "<script>alert('" . htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') . "');</script>";
        unset($_SESSION['message']);
    }

    $message_csrf = "不正にログアウトが行なわたか、タイムアウトしました";    //大体、タイムアウト=セッション有効期限切れらしい
    $message_nomal = "不正なリクエストです。ログアウトしました・";

    if (isset($_GET['error']) && $_GET['error'] == 'csrf_alert') {
        echo "<script>alert('" . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . "');</script>";
    } else if (isset($_GET['error']) && $_GET['error'] == 'nomal_alert') {
        echo "<script>alert('" . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . "');</script>";
    }

    try {
        $sql = "SELECT * FROM school";
        $stmt = $db->pdo->prepare($sql);
        $stmt->execute();

        $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        echo "データベースエラー：" . $e->getMessage(); //デバッグ用。あとで消す！
        exit;
    } catch (Exception $e) {
        echo "エラー：" . $e->getMessage(); //デバッグ用。あとで消す！
        exit;
    } finally {
        $db->close();
    }
    
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログインページ</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>

    <header class="main-header">
        <div class="header-logo">
            <a href="student_login.php">インターネット図書館</a>
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
        <h1>ログイン</h1>
        <form action="../php/student_auth.php" method="POST">
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

                <!--学年を選択させる-->
                <label for ="grade">学年:</label>
                <select name="grade" required>
                    <option value= "">選択してください</option>
                    <option value="1">1年</option>
                    <option value="2">2年</option>
                    <option value="3">3年</option>
                    <option value="4">4年</option>
                    <option value="5">5年</option>
                    <option value="6">6年</option>
                    <!-- 少子化による統合で義務教育学校となっている場合を考慮し、9年生まで用意する -->
                    <option value="7">7年</option>
                    <option value="8">8年</option>
                    <option value="9">9年</option>
                </select>

                <!--クラスは入力させる-->
                <label for ="class">クラス:</label>
                <input type="text" id="class" name="class" required>

                <!-- 番号を選択させる -->
                <label for ="number">番号:</label>
                <select name="number" required>
                    <option value="">選択してください</option>
                    <?php for ($i = 1; $i <= 50; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>

            </div>
            <div class="form-group">
                <label for="password">パスワード:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">ログイン</button>
        </form>

        <div class="sub-actions">
            <button onclick="location.href='../html/register.php'">新規登録</button>
            <button onclick="location.href='../html/librarian_login.php'">司書の方はこちら</button>
        </div>
    </div>
</body>
</html>