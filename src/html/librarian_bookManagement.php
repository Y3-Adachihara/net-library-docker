<?php
    require_once '../db_connect.php';
    session_start();

    if (!isset($_SESSION['librarian_id'])) {
        // 司書としてログインしていない場合、ログインページへリダイレクト
        $_SESSION['message'] = "司書としてログインしてください。";
        header("Location: librarian_login.php");
        exit();
    }
    //　ここからは、司書としてログインしていないと実行されない
    if (isset($_SESSION['book_manageConfirm_message'])) {
        $message = $_SESSION['book_manageConfirm_message'];
        echo "<script>alert('" . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . "');</script>";
        unset($_SESSION['book_manageConfirm_message']);

    } else if (isset($_SESSION['bookStatus_changeResult_message'])) {
        $message = $_SESSION['bookStatus_changeResult_message'];
        echo "<script>alert('" . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . "');</script>";
        unset($_SESSION['bookStatus_changeResult_message']);
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
        // トークンを隠し属性として送るためのhtmlコードを記述
        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') . '">';
    }

    //CSRFトークンがセットされていなかったらセッションにセットする
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = csrf_token_generate();
    }


    $librarian_id = $_SESSION['librarian_id'] ?? null;
    $librarian_school_id = $_SESSION['librarian_school_id'] ?? null;
    $librarian_fullname = '';
    $librarian_school_name = '';

    $local_nonCarry_reservations = [];  // 予約者は自校、本も自校が所蔵
    $local_carryIn_reservations = [];   // 予約者は自校、本は他校が所蔵
    $deliver_reservations = [];         // 予約者は他校、本は自校が所蔵

    $all_reservations = [];

    // HTMLエスケープ関数
    function h($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    function table_data_display(array $records, int $next_status): void { // $from_where 1:自校からの予約 | 2:他校からの予約
        if (empty($records)) {
            echo "<tr><td colspan='6'>現在、予約取り置きリストはありません。</td></tr>";
            return;
        }

        echo "<tr>";
        echo "<th>チェック</th>";
        echo "<th>書籍ID</th>";
        echo "<th>ISBN</th>";
        echo "<th>タイトル</th>";
        echo "<th>予約元</th>";
        echo "<th>予約学生</th>";
        echo "<th>予約日</th>";
        echo "</tr>";

        foreach($records as $row) {
            $book_id = $row['book_id'];
            $book_isbn = $row['isbn'];
            $book_title = $row['title'];
            $student_school = $row['school_name'];
            $family_name = $row['family_name'];
            $first_name = $row['first_name'];
            $full_name = $family_name . " " . $first_name;
            $reservation_date = $row['reservation_date'];

            echo "<tr>";

            // 自校の生徒が自校の本を予約した場合
            if ($next_status == 4) {
                echo "<td><input type=\"checkbox\" name=\"local_noncarry_res[]\" value= \"" . h($book_id) . "\"></td>";    // これがチェックボックス

            // 自校の生徒が他校の本を予約した場合
            } else if ($next_status == 7) {
                echo "<td><input type=\"checkbox\" name=\"local_carry_res[]\" value= \"" . h($book_id) . "\"></td>";    // これがチェックボックス

            // 他校の生徒が自校の本を予約した場合
            } else if ($next_status == 5) {
                echo "<td><input type=\"checkbox\" name=\"deliver_res[]\" value= \"" . h($book_id) . "\"></td>";    // これがチェックボックス
            }
            echo "<td>" . h($book_id) . "</td>";
            echo "<td>" . h($book_isbn) . "</td>";
            echo "<td>" . h($book_title) . "</td>";
            echo "<td>" . h($student_school) . "</td>";
            echo "<td>" . h($full_name) . "</td>";
            echo "<td>" . h($reservation_date) . "</td>";
            echo "</tr>";
        
        }
    }

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

        $sql_school_name = "SELECT * FROM school WHERE school_id = :school_id";
        $stmt_scName = $db->pdo->prepare($sql_school_name);
        $stmt_scName->bindValue(':school_id', $librarian_school_id, PDO::PARAM_INT);
        $stmt_scName->execute();
        $school_list = $stmt_scName->fetch(PDO::FETCH_ASSOC);

        if(!empty($row)) {
            $school_name = $school_list['school_name'];
        }

        // 自校・他校からの予約
        $sql_all = "SELECT r.book_id, bk_sc.school_id AS belong_id, r.reservation_date, bi.title, bi.isbn, s.school_id AS school_id, sc.school_name, s.family_name, s.first_name";
        $sql_all .= " FROM reservation AS r";
        $sql_all .= " LEFT OUTER JOIN book_stack AS bs";
        $sql_all .= " ON r.book_id = bs.book_id";
        $sql_all .= " LEFT OUTER JOIN book_info AS bi";
        $sql_all .= " ON bs.isbn = bi.isbn";
        $sql_all .= " LEFT OUTER JOIN student AS s";
        $sql_all .= " ON r.student_id = s.student_id";
        $sql_all .= " LEFT OUTER JOIN school AS sc";
        $sql_all .= " ON s.school_id = sc.school_id";
        $sql_all .= " LEFT OUTER JOIN school AS bk_sc";
        $sql_all .= " ON bs.school_id = bk_sc.school_id";
        $sql_all .= " WHERE bs.position = :school_id";
        $sql_all .= " AND r.status_id = :res_status_id";
        $sql_all .= " AND bs.status_id = :bk_status_id";
        $sql_all .= " AND NOT EXISTS (
                            SELECT 1 
                            FROM lending_deny AS ld
                            WHERE (
                                ld.book_id = bs.book_id
                                AND (
                                    DATE(NOW()) >= DATE_SUB(DATE(ld.start_date), INTERVAL 3 DAY) 
                                    AND 
                                    DATE(NOW()) <= DATE(ld.end_date)
                                )
                            )
                        )";
        $sql_all .= " ORDER BY r.reservation_date ASC, r.reservation_id ASC";

        $stmt = $db->pdo->prepare($sql_all);
        $stmt->bindValue(':school_id', $librarian_school_id, PDO::PARAM_INT);
        $stmt->bindValue(':res_status_id', 1, PDO::PARAM_INT);
        $stmt->bindValue(':bk_status_id', 3, PDO::PARAM_INT);
        $stmt->execute();
        $toMySchoolReservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $processed_book_ids = [];

        foreach($toMySchoolReservations as $row) {
            if(in_array($row['book_id'], $processed_book_ids)) {
                continue;
            }

            $processed_book_ids[] = $row['book_id'];


            // $all_reservations [] = $row;   // とりま自校・他校からの予約リストは入れる

            // 自校からの予約リストを格納
            if (intval($row['school_id']) == $librarian_school_id) {
                $reserver_school_id = intval($row['school_id']) ; // こいつは予約者の学校ID
                $book_belong = intval($row['belong_id']);   // こいつは書籍を所蔵する学校のID

                // 自校の生徒が自校の本を予約
                if ($reserver_school_id == $book_belong) {
                    $local_nonCarry_reservations [] = $row;

                // 自校の生徒が他校の本を予約
                } else {
                    $local_carryIn_reservations [] = $row;
                }

            // 他校からの予約リストを格納
            } else {
                $deliver_reservations [] = $row;
            }
        }

    } catch (PDOException $e) {
        $error_message = "データの取得に失敗しました。" . $e->getMessage();
        echo "<script>alert('" . htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') . "');</script>";
    } catch (Exception $e) {
        $error_message = "予期せぬエラーが発生しました。" . $e->getMessage();
        echo "<script>alert('" . htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') . "');</script>";
    }
        

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>予約取り置き画面(<?php echo h($librarian_school_name); ?>)</title>
    <link rel="stylesheet" href="../css/librarian_book_management.css">
</head>
<body>
    <!-- ログアウトボタンを押したときのCSFSトークン発行 -->
        <form method="POST" action = "../php/logout.php" name = "link_logoutFORM">
            <?php
                set_csrf_token($csrf_token);
            ?>
            <input type="hidden" name = "page_id" value= "1">
        </form>
    <header>
        <a href="#"><?php echo htmlspecialchars($librarian_fullname); ?> さん(<?php echo htmlspecialchars($librarian_school_name); ?>)の検索画面</a>
        <button class="logout-btn" onclick="confirmLogout()">ログアウト</button>
    </header>
    <div class="tabs">
        <button onclick="showTab('all')">① 自校予約（自校所蔵本）- 予約取り置きBOXへ</button>
        <button onclick="showTab('local')">② 自校予約 (他校所蔵本) - 配送待ちBOXへ</button>
        <button onclick="showTab('delivery')">③ 他校からの予約（自校所蔵本） - 配送待ちBOXへ</button>
    </div>

    <div id="area-all" class="tab-content">
        <form action="librarian_book_confirm.php" method="post">
            <p>自校の生徒から、自校所蔵の本に対する予約です。チェックして確認画面へ進んでください。</p>
            <table>
                <?php table_data_display($local_nonCarry_reservations, 4) ?>
            </table>
            <input type="hidden" name="next_status" value="4">
            <button type="submit">確認画面へ</button>
        </form>
    </div>

    <div id="area-local" class="tab-content" style="display:none;">
        <form action="librarian_book_confirm.php" method="post">
            <p>自校の生徒から、他校所蔵の本に対する予約です。チェックして確認画面へ進んでください。</p>
            <table>
                <?php table_data_display($local_carryIn_reservations, 7) ?>
            </table>
            <input type="hidden" name="next_status" value="7">
            <button type="submit">確認画面へ</button>
        </form>
    </div>

    <div id="area-delivery" class="tab-content" style="display:none;">
        <form action="librarian_book_confirm.php" method="post">
            <p>他校からの予約です。チェックして確認画面へ進んでください。</p>
            <table>
                <?php table_data_display($deliver_reservations, 5) ?>
            </table>
            <input type="hidden" name="next_status" value="5">
            <button type="submit">確認画面へ</button>
        </form>
    </div>

    <script>
    function showTab(tabName) {
        // 1. 一旦全部隠す
        document.getElementById('area-all').style.display = 'none';
        document.getElementById('area-local').style.display = 'none';
        document.getElementById('area-delivery').style.display = 'none';
        
        // 2. 選ばれたやつだけ表示
        document.getElementById('area-' + tabName).style.display = 'block';
    }
    </script>

    <div class="button-row">
        <button type="button" class="btn" onclick="location.href='../html/librarian_myPage.php'">戻る</button>
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