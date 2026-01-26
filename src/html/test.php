<?php
    session_start();
    require_once '../db_connect.php';
    

    if (!isset($_SESSION['student_id'])) {
        // 学生としてログインしていない場合、ログインページへリダイレクト
        $_SESSION['error'] = "ログインしてください。";
        header("Location: student_login.php");
        exit();
    }

    $error_message = $_SESSION['message'] ?? '';
    if (isset($_SESSION['message'])) {
        echo "<script>alert('" . htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') . "');</script>";
        unset($_SESSION['message']);
    }

    /* いったん、予約確認画面と予約処理が完成するまでコメントアウト 2026/01/25
    // --- データベース接続設定 ---
    $host = 'localhost';
    $dbname = 'library_db';
    $user = 'root';
    $pass = ''; 

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    } catch (PDOException $e) {
        die("接続エラー: " . $e->getMessage());
    }

    // --- APIモード：JavaScriptからのデータリクエストを処理する ---
    if (isset($_GET['action'])) {
        header('Content-Type: application/json');
        
        // 生徒検索
        if ($_GET['action'] === 'search_student') {
            $stmt = $pdo->prepare("SELECT student_name FROM student_info WHERE school_name = ? AND grade = ? AND class_num = ? AND attendance_num = ?");
            $stmt->execute([$_GET['school'], $_GET['grade'], $_GET['class'], $_GET['num']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
            exit;
        }
        
        // 書籍検索
        if ($_GET['action'] === 'search_book') {
            $stmt = $pdo->prepare("SELECT title, author, genre_main, genre_sub, publisher FROM book_info WHERE book_id_code = ?");
            $stmt->execute([$_GET['book_id']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
            exit;
        }
    }
        */
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>予約画面</title>
    <style>
        body { font-family: "Meiryo", sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; padding: 20px; }
        .container { background-color: #fff; border: 2px solid #000; padding: 40px; width: 650px; }
        .form-row { display: flex; align-items: center; margin-bottom: 15px; }
        .form-row label { width: 180px; font-weight: bold; }
        .input-group { flex-grow: 1; display: flex; align-items: center; }
        input[type="text"], input[type="number"], select { padding: 5px; border: 2px solid #000; font-size: 14px; }
        .full-width { width: 100%; }
        .short-input { width: 50px; margin-right: 5px; }
        .unit-label { margin-right: 15px; }
        input[readonly] { background-color: #e9e9e9; border: 2px solid #888; }
        .note { margin-left: 10px; font-size: 0.8em; color: #333; min-width: 60px; }
        .button-area { display: flex; justify-content: space-around; margin-top: 40px; }
        button { padding: 10px 40px; font-size: 16px; background-color: #fff; border: 2px solid #000; cursor: pointer; font-weight: bold; }
        
        /* 数値入力欄の矢印消去 */
        input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }
    </style>
</head>
<body>

<div class="container">
    <form id="reservationForm" method = "POST" action = "reservation_confirm.php">
        
        <div class="form-row">
            <label>学校名</label>
            <div class="input-group">
                <select name="school_name" id="school_name" class="full-width">
                    <option value="">選択してください</option>
                    <option value="第一小学校">第一小学校</option>
                    <option value="第二小学校">第二小学校</option>
                    <option value="第三小学校">第三小学校</option>
                    <option value="第四小学校">第四小学校</option>
                    <option value="第五小学校">第五小学校</option>
                    <option value="第六小学校">第六小学校</option>
                    <option value="第七小学校">第七小学校</option>
                    <option value="第八小学校">第八小学校</option>
                    <option value="第九中学校">第九中学校</option>
                    <option value="第十中学校">第十中学校</option>
                </select>
            </div>
            <span class="note">①</span>
        </div>

        <div class="form-row">
            <label>学年・クラス・番号</label>
            <div class="input-group">
                <select id="grade" class="short-input" style="width: 65px;">
                    <option value="">-</option>
                    <?php for($i=1; $i<=6; $i++){ echo "<option value='$i'>$i</option>"; } ?>
                </select>
                <span class="unit-label">年</span>

                <select id="class" class="short-input" style="width: 65px;">
                    <option value="">-</option>
                    <?php for($i=1; $i<=8; $i++){ echo "<option value='$i'>$i</option>"; } ?>
                </select>
                <span class="unit-label">組</span>

                <select id="number" class="short-input" style="width: 65px;">
                    <option value="">-</option>
                    <?php for($i=1; $i<=50; $i++){ echo "<option value='$i'>$i</option>"; } ?>
                </select>
                <span class="unit-label">番</span>
            </div>
            <span class="note">②</span>
        </div>

        <div class="form-row">
            <label>氏名</label>
            <div class="input-group">
                <input type="text" id="student_name" class="full-width" readonly placeholder="自動表示されます">
            </div>
            <span class="note">※編集不可</span>
        </div>

        <hr>

        <div class="form-row">
            <label>書籍番号</label>
            <div class="input-group">
                <input type="text" id="book_id" name="book_id" class="full-width" placeholder="例: B001">
            </div>
            <span class="note">③</span>
        </div>

        <div class="form-row"><label>書籍名</label><div class="input-group"><input type="text" id="book_title" class="full-width" readonly></div><span class="note">※編集不可</span></div>
        <div class="form-row"><label>著者名</label><div class="input-group"><input type="text" id="author" class="full-width" readonly></div><span class="note">※編集不可</span></div>
        <div class="form-row"><label>ジャンル(類)</label><div class="input-group"><input type="text" id="genre_main" class="full-width" readonly></div><span class="note">※編集不可</span></div>
        <div class="form-row"><label>ジャンル(綱)</label><div class="input-group"><input type="text" id="genre_sub" class="full-width" readonly></div><span class="note">※編集不可</span></div>
        <div class="form-row"><label>出版社</label><div class="input-group"><input type="text" id="publisher" class="full-width" readonly></div><span class="note">※編集不可</span></div>

        <div class="button-area">
            <button type="button" onclick="location.href='stu_myPage.php'">やめる</button>
            <button type="submit">予約確認画面へ</button>
        </div>
    </form>
</div>


<script>
/* いったん、予約確認画面と予約処理が完成するまでコメントアウト 2026/01/25
// 入力監視
const inputs = ['school_name', 'grade', 'class', 'number'];
inputs.forEach(id => {
    // selectタグの場合は 'change' イベントの方が確実です
    document.getElementById(id).addEventListener('change', searchStudent);
});

document.getElementById('book_id').addEventListener('input', searchBook);

// 生徒検索
async function searchStudent() {
    const school = document.getElementById('school_name').value;
    const grade = document.getElementById('grade').value;
    const cls = document.getElementById('class').value;
    const num = document.getElementById('number').value;

    if (school && grade && cls && num) {
        try {
            const res = await fetch(`?action=search_student&school=${school}&grade=${grade}&class=${cls}&num=${num}`);
            const data = await res.json();
            
            const nameInput = document.getElementById('student_name');
            if (data) {
                nameInput.value = data.student_name;
                nameInput.style.color = "black";
            } else {
                nameInput.value = "その学校/ユーザーは存在しません";
                nameInput.style.color = "red";
            }
        } catch (e) {
            console.error(e);
        }
    }
}

// 書籍検索
async function searchBook() {
    const bookId = document.getElementById('book_id').value;
    if (bookId.length > 0) {
        try {
            const res = await fetch(`?action=search_book&book_id=${bookId}`);
            const data = await res.json();
            
            if (data) {
                document.getElementById('book_title').value = data.title;
                document.getElementById('author').value = data.author;
                document.getElementById('genre_main').value = data.genre_main;
                document.getElementById('genre_sub').value = data.genre_sub;
                document.getElementById('publisher').value = data.publisher;
            } else {
                document.getElementById('book_title').value = "その書籍は存在しません";
                ['author', 'genre_main', 'genre_sub', 'publisher'].forEach(id => {
                    document.getElementById(id).value = "";
                });
            }
        } catch (e) {
            console.error(e);
        }
    }
}

// 予約完了メッセージ
function submitReservation() {
    const student = document.getElementById('student_name').value;
    const book = document.getElementById('book_title').value;

    if(student && student !== "その学校/ユーザーは存在しません" && book && book !== "その書籍は存在しません") {
        alert("予約を完了しました");
        // 必要ならここで location.href='test2.php'; を追加して完了後に画面遷移させることも可能です
    } else {
        alert("情報を正しく入力してください");
    }
}
*/
</script>

</body>
</html>