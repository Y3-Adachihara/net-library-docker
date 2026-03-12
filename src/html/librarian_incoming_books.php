<?php
session_start();
require_once '../db_connect.php';

// 司書ログインチェック
if (!isset($_SESSION['librarian_id'])) {
    $_SESSION['message'] = "司書としてログインしてください。";
    header("Location: librarian_login.php");
    exit();
}

$librarian_school_id = $_SESSION['librarian_school_id'] ?? null;
$librarian_fullname = '';
$librarian_school_name = '';

// メッセージ表示処理
if (isset($_SESSION['bookStatus_changeResult_message'])) {
    $msg = $_SESSION['bookStatus_changeResult_message'];
    echo "<script>alert('" . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . "');</script>";
    unset($_SESSION['bookStatus_changeResult_message']);
}
if (isset($_SESSION['message'])) {
    echo "<script>alert('" . htmlspecialchars($_SESSION['message'], ENT_QUOTES, 'UTF-8') . "');</script>";
    unset($_SESSION['message']);
}

// HTMLエスケープ関数
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

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

// リスト初期化
$reserved_list = []; // 予約あり（取り置き棚へ）
$return_list = [];   // ★追加：他校へ返却（配送箱へ）
$shelf_list = [];    // 予約なし・自校の本（書架へ）

$error_message = null;

try {
    $db = new db_connect();
    $db->connect();
    $pdo = $db->pdo;

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

    // ★SQL修正ポイント
    // 1. bs.school_id (持ち主) を取得
    // 2. schoolテーブルを結合して持ち主の学校名を取得
    $sql = "SELECT 
                bs.book_id,
                bs.school_id AS owner_school_id, -- 本の持ち主の学校ID
                sc.school_name AS owner_school_name, -- 書籍を所蔵する学校名
                stu_sc.school_name AS reserver_school_name, -- 予約者の学校名（必要に応じて）
                bi.title,
                bi.isbn,
                r.reservation_id,
                st.family_name,
                st.first_name
            FROM book_stack AS bs
            LEFT JOIN book_info AS bi 
                ON bs.isbn = bi.isbn 
            LEFT JOIN reservation AS r 
                ON bs.book_id = r.book_id 
                AND r.status_id = 1
                AND r.reservation_id = (
                    SELECT r2.reservation_id
                    FROM reservation AS r2
                    WHERE r2.book_id = bs.book_id   -- 同じ本の中で
                    AND r2.status_id = 1            -- 有効な予約で
                    ORDER BY r2.reservation_date ASC, r2.reservation_id ASC -- 一番早いもの
                    LIMIT 1
                )
            LEFT JOIN student AS st 
                ON r.student_id = st.student_id
            LEFT JOIN school AS sc            -- ★追加：学校名の取得用
                ON bs.school_id = sc.school_id
            LEFT JOIN school AS stu_sc       -- ★追加：予約者の学校名取得用（必要に応じて）
                ON st.school_id = stu_sc.school_id
            WHERE bs.position = :school_id  -- 現在位置が自分の学校
              AND bs.status_id = 10         -- 状態が「検品・仕分け中」
            ORDER BY bs.book_id ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':school_id', $librarian_school_id, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 振り分け処理
    foreach ($results as $row) {
        $reserver_name = "";
        
        if (!empty($row['reservation_id'])) {
            // ■ パターン1：予約あり -> ステータス3 (予約受取待ち)
            $row['next_status'] = 3;
            $row['student_name'] = $row['family_name'] . " " . $row['first_name'] . " (" . $row['reserver_school_name'] . ")";
            $reserved_list[] = $row;
            
        } else {
            // 予約なしの場合、さらに持ち主で分岐
            if ($row['owner_school_id'] != $librarian_school_id) {
                // ■ パターン2：他校の本 -> ステータス8 (配送待ち・返却配送)
                $row['next_status'] = 8;
                $row['student_name'] = "返却先: " . $row['owner_school_name']; // 予約者欄に返却先を表示
                $return_list[] = $row;
            } else {
                // ■ パターン3：自校の本 -> ステータス1 (貸出可能)
                $row['next_status'] = 1;
                $row['student_name'] = "-";
                $shelf_list[] = $row;
            }
        }
    }

} catch (PDOException $e) {
    error_log("データベース接続エラー：" . $e->getMessage());
    $error_message = "データベース通信エラーが発生しました。しばらく経ってからやり直してください。";
} catch (Exception $e) {
    error_log("予期しないエラー: " . $e->getMessage());
    $error_message = "予期せぬエラーが発生しました。システム管理者にお問い合わせください。";
} finally {
    $db->close();
}

// テーブル表示用関数
function renderTable($list, $type) {
    if (empty($list)) {
        echo "<p>該当する図書はありません。</p>";
        return;
    }
    echo "<table>";
    echo "<tr><th>選択</th><th>書籍ID</th><th>タイトル</th><th>所蔵学校</th><th>備考(予約者/返却先)</th><th>次の状態</th></tr>";
    
    foreach ($list as $row) {
        // 表示用テキストと色の設定
        if ($type === 'reserved') {
            $status_text = "予約取り置き画面の手続きへ";
            $status_color = "red";
        } elseif ($type === 'return') {
            $status_text = "配送箱へ (返却配送)";
            $status_color = "#e67e22"; // オレンジ色
        } else {
            $status_text = "書架へ (貸出可能)";
            $status_color = "blue";
        }
        
        $book_id = $row['book_id'];

        echo "<tr>";
        echo "<td style='text-align:center;'><input type='checkbox' name='process_list[]' value='" . $row['book_id'] . "'></td>";
        
        // 隠しデータ送信
        echo "<input type='hidden' name='items[". h($book_id) ."][title]' value='". h($row['title']) . "'>";
        echo "<input type='hidden' name='items[". h($book_id) ."][next_status]' value='". h($row['next_status']) . "'>";
        echo "<input type='hidden' name='items[". h($book_id) ."][student_name]' value='". h($row['student_name']) . "'>";

        echo "<td>" . h($row['book_id']) . "</td>";
        echo "<td>" . h($row['title']) . "</td>";
        echo "<td>" . h($row['owner_school_name']) . "</td>";
        echo "<td>" . h($row['student_name']) . "</td>";
        echo "<td style='color:{$status_color}; font-weight:bold;'>" . h($status_text) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>検品・仕分け処理</title>
    <link rel="stylesheet" href="../css/librarian_incoming_books.css">
    <style>
        .tabs { margin-bottom: 20px; }
        .tabs button {
            padding: 10px 20px;
            margin-right: 5px;
            cursor: pointer;
            border: 1px solid #ccc;
            background-color: #f1f1f1;
            font-size: 16px;
        }
        .tabs button.active {
            background-color: #fff;
            border-bottom: 2px solid #007bff;
            font-weight: bold;
        }
        /* ボタンの色分け */
        .tabs button.tab-res.active { border-bottom-color: red; color: red; }
        .tabs button.tab-ret.active { border-bottom-color: #e67e22; color: #e67e22; }
        .tabs button.tab-she.active { border-bottom-color: blue; color: blue; }

        .tab-content {
            border: 1px solid #ccc;
            padding: 20px;
            background-color: #fff;
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <?php if ($error_message !== null): ?>
        <div style="color: red; padding: 20px; border: 1px solid red; background-color: #fee; text-align: center;">
                <h3>現在システムをご利用いただけません</h3>
                <p><?php echo h($error_message); ?></p>
            </div>
    <?php else: ?>
    <header>
        <a href="librarian_incoming_books.php"><?php echo htmlspecialchars($librarian_fullname); ?> さん(<?php echo htmlspecialchars($librarian_school_name); ?>)の検品・仕分け画面</a>
        <button class="logout-btn" onclick="confirmLogout()">ログアウト</button>
    </header>
    <!-- ログアウトボタンを押したときのCSFSトークン発行 -->
        <form method="POST" action = "../php/logout.php" name = "link_logoutFORM">
            <?php
                set_csrf_token($csrf_token);
            ?>
            <input type="hidden" name = "page_id" value= "1">
        </form>


    <h2>検品・仕分け処理</h2>
    <p>返却や配送により到着した図書（ステータス10）を仕分けます。</p>

    <div class="tabs">
        <button class="tab-link tab-res active" onclick="openTab(event, 'TabReserved')">① 予約あり (<?php echo count($reserved_list); ?>冊)</button>
        <button class="tab-link tab-ret" onclick="openTab(event, 'TabReturn')">② 他校へ返却 (<?php echo count($return_list); ?>冊)</button>
        <button class="tab-link tab-she" onclick="openTab(event, 'TabShelf')">③ 書架へ配架 (<?php echo count($shelf_list); ?>冊)</button>
    </div>

    <div id="TabReserved" class="tab-content">
        <h3>【予約割当】予約棚へ移動</h3>
        <p>予約が入っています。ステータスを変更後、予約取り置き棚へ移動してください。</p>
        <form action="librarian_incoming_confirm.php" method="POST">
            <?php renderTable($reserved_list, 'reserved'); ?>
            <?php if (!empty($reserved_list)): ?>
                <button type="submit">チェックした本のステータスを変更する確認画面へ</button>
            <?php endif; ?>
        </form>
    </div>

    <div id="TabReturn" class="tab-content" style="display:none;">
        <h3>【返却配送】配送箱へ移動</h3>
        <p>他校の所有本です。ステータスを変更後、配送箱（コンテナ）へ入れてください。</p>
        <form action="librarian_incoming_confirm.php" method="POST">
            <?php renderTable($return_list, 'return'); ?>
            <?php if (!empty($return_list)): ?>
                <button type="submit">チェックした本を「返却配送待ち(8)」にする確認画面へ</button>
            <?php endif; ?>
        </form>
    </div>

    <div id="TabShelf" class="tab-content" style="display:none;">
        <h3>【配架】書架へ移動</h3>
        <p>自校の本で、予約もありません。通常の書架へ戻してください。</p>
        <form action="librarian_incoming_confirm.php" method="POST">
            <?php renderTable($shelf_list, 'shelf'); ?>
            <?php if (!empty($shelf_list)): ?>
                <button type="submit">チェックした本を「貸出可能(1)」にする確認画面へ</button>
            <?php endif; ?>
        </form>
    </div>

    <br>
    <div class="bottom-container">
        <button type="button" class="btn-back" onclick="location.href='librarian_myPage.php'">マイページへ戻る</button>
    </div>
    
    <script>
        function confirmLogout() {
            if (confirm("ログアウトしますか？")) {
                document.getElementById('logout_form').submit();
            }
        }

        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;

            // コンテンツを隠す
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }

            // ボタンのactiveを外す
            tablinks = document.getElementsByClassName("tab-link");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }

            // 対象を表示
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }
    </script>
</body>
<?php endif; ?>
</html>