<?php
    require_once '../db_connect.php';
    session_start();

    if (!isset($_SESSION['librarian_id'])) {
        // 司書としてログインしていない場合、ログインページへリダイレクト
        $_SESSION['message'] = "司書としてログインしてください。";
        header("Location: librarian_login.php");
        exit();
    }

    // タイトルから部分一致で検索掛ける時に使う学校ID
    $librarian_school_id = $_SESSION['librarian_school_id'];
    // 検索結果画面（司書用）から貸出・返却画面に遷移した場合の書籍ID受け取り
    $received_book_id = isset($_GET['book_id']) ? $_GET['book_id'] : '';

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

    // この時点で、セッションにトークンがあっても無くても、$csrf_tokenにトークンが格納されている
    $csrf_token = $_SESSION['csrf_token'];

    function display_message(String $message, String $title): void {
        $safe_message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $js_message = str_replace(array("\r\n", "\r", "\n"), '\\n', $safe_message);
        echo "<script>alert('" . $js_message . "');</script>";
        unset($_SESSION['']);
    }

    
    // 貸出処理のメッセージ
    if (isset($_SESSION['lend_result_message'])) {
        display_message($_SESSION['lend_result_message'], 'lend_result_message');
        unset($_SESSION['lend_result_message']);
    // 返却処理のメッセージ
    } else if (isset($_SESSION['return_result_message'])) {
        display_message($_SESSION['return_result_message'], 'return_result_message');
        unset($_SESSION['return_result_message']);
    }
    $librarian_school_id = isset($_SESSION['librarian_school_id']) ? $_SESSION['librarian_school_id'] : null;

    $librarian_school_name = '';
    $librarian_fullname = '';

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
    } catch (PDOException $e) {
        echo "データベースエラー: " . h($e->getMessage());
        exit;
    } catch (Exception $e) {
        echo "エラー: " . h($e->getMessage());
        exit;
    } finally {
        $db->close(); // DB接続解除   
    }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>貸出・返却</title>
    <link rel = "stylesheet" href="../css/貸出返却.css">
</head>
<body>
<header>
    <a href="#"><?php echo htmlspecialchars($librarian_fullname); ?> さん(<?php echo htmlspecialchars($librarian_school_name); ?>)の検索画面</a>
    <button class="logout-btn" onclick="confirmLogout()">ログアウト</button>
</header>
<div class="container">

    <h1>貸出・返却</h1>
    <form method = "POST">
        <div class="form-group">
            <label>学年：</label>
            <select name="school-year" required>
                <option value="">選択してください</option>
                <option value="1">1年</option>
                <option value="2">2年</option>
                <option value="3">3年</option>
                <option value="4">4年</option>
                <option value="5">5年</option>
                <option value="6">6年</option>
                <option value="7">7年</option>
                <option value="8">8年</option>
                <option value="9">9年</option>

            </select>
        </div>

        <div class="form-group">
            <label>クラス:</label>
            <input type="text" name="class" placeholder="1">
        </div>

        <div class="form-group">
    <label>番号：</label>
    <select name="number" id="number-select" required>
        <option value="">選択してください</option>
    </select>
</div>

<script>
    const selectElement = document.getElementById('number-select');

    for (let i = 1; i <= 50; i++) {

        const option = document.createElement('option');
    
        option.value = i;
        option.textContent = i;

        selectElement.appendChild(option);
    }
</script>

    <?php set_csrf_token($csrf_token); ?>

        <div class="form-group">
            <label>書籍番号：</label>
            <input type="text" name="id-number" placeholder="901000101" value="<?php echo htmlspecialchars($received_book_id); ?>">
        </div>
        <!--
        <div>
            <label>タイトル：</label>
            <input type="text" name="title" placeholder="例:こころ">
            <button type="submit" formaction = "貸出返却.php" class="btn-blue">このタイトルで部分検索</button>
        </div>
-->

        <div class="action-buttons">
            <?php if (empty($received_book_id)): ?>
            <button type="button" class="btn-blue" onclick="location.href='librarian_myPage.php'">戻る</button>
            <?php else: ?>
            <button type="button" class="btn-blue" onclick="location.href='検索画面.php'">戻る</button>
            <?php endif; ?>

            <button type="submit" formaction = "../php/lend.php" class="btn-blue">貸出</button>
            <button type="submit" formaction = "../php/return.php" class="btn-blue">返却</button>
        </div>
    </form>
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