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
    // 返却処理のメッセージ
    } else if (isset($_SESSION['return_result_message'])) {
        display_message($_SESSION['return_result_message'], 'return_result_message');
    // 
    } 
    /*
    else if (isset($_SESSION['title_select_message'])) {
        display_message(($_SESSION['title_select_message']), 'title_select_message');
    }
    
    
    $this_grade = $_POST['school-year'] ?? null;
    $this_class = $_POST['class'] ?? null;
    $this_number = $_POST['number'] ?? null;
    $this_book_id = $_POST['id_number'] ?? null;
    $this_book_title = $_POST['title'] ?? null;

    // テーブル型式で表示する際に使う書籍リストを格納する配列
    $title_selected_books = null;

    // 何も選ばれていなかったらこのページに戻す
    if (empty($title_selected_books)) {
        $_SESSION['title_select_message'] = "タイトルを入れてから検索してください。";
        header("Location: 貸出返却.php");
        exit();
    }

    
    function table_data_display (array $records) {

        if (empty($records)) {
            echo "<tr><td colspan='6'>指定されたタイトルに部分一致する本はありません。</td></tr>";
            return;
        }


    }
    
    try {
        $db = new db_connect();
        $db->connect();
        
        $sql = "SELECT * FROM book_stack AS bs";
        $sql .= " LEFT OUTRR JOIN book_info AS bi";
        $sql .= " ON bs.isbn = bi.isbn";
        $sql .= " WHERE bi.title LIKE ':title'";
        $sql .= " AND bs.school_id = :school_id";
        $stmt = $db->pdo->prepare($sql);
        $stmt->execute([
            'title' => '%' . $this_book_title . '%',
            'school_od' => $librarian_school_id
        ]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($results)) {
            foreach($results AS $row) {
                $title_selected_books [] = $row;
            }
        }

    } catch (PDOException $e) {
        $db->pdo->rollback();
        echo "データベースエラー：" . $e->getMessage(); //デバッグ用。あとで消す！
        exit;
    } catch (Exception $e) {
        $db->pdo->rollback();
        echo "エラー：" . $e->getMessage(); //デバッグ用。あとで消す！
        exit;
    } finally {
        $db->close();
    }
        */
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
            <label>識別番号：</label>
            <input type="text" name="id-number" placeholder="901000101">
        </div>
        
        <div>
            <label>タイトル：</label>
            <input type="text" name="title" placeholder="例:こころ">
            <button type="submit" formaction = "貸出返却.php" class="btn-blue">このタイトルで部分検索</button>
        </div>

        <div class="action-buttons">
            <button type="button" class="btn-blue" onclick="location.href='librarian_myPage.php'">戻る</button>

            <button type="submit" formaction = "../php/lend.php" class="btn-blue">貸出</button>
            <button type="submit" formaction = "../php/return.php" class="btn-blue">返却</button>
        </div>
    </form>
</div>
</body>
</html>