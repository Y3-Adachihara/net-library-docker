<?php
    session_start();
    require_once '../db_connect.php';

    
    if (!isset($_SESSION['student_id'])) {
        // 学生としてログインしていない場合、ログインページへリダイレクト
        $_SESSION['error'] = "ログインしてください。";
        header("Location: student_login.php");
        exit();
    }

    $book_id = $_POST['book_id'];

    $family_name = $_SESSION['student_family_name'];
    $first_name = $_SESSION['student_first_name'];
    // 苗字と名前を結合
    $student_name = $family_name . " " . $first_name;
    $class = $_SESSION['student_class'];
    $school_id = $_SESSION['student_school_id'];
    $grade = $_SESSION['student_grade'];

    $school_name = '';
    $book_isbn = '';
    $book_title = '';
    $book_author = '';
    $book_author_kana = '';
    $book_publisher = '';


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

    try {
        $db = new db_connect();
        $db->connect();

        $sql = "SELECT bi.isbn AS bi_isbn, bi.title, bi.author_name, bi.author_kana, bi.publisher FROM book_stack AS bs LEFT OUTER JOIN book_info AS bi ON bs.isbn = bi.isbn WHERE bs.book_id = :book_id";
        $stmt = $db->pdo->prepare($sql);
        $stmt->bindValue(':book_id', $book_id, PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $book_isbn = $row['bi_isbn'];
            $book_title = $row['title'];
            $book_author = $row['author_name'];
            $book_author_kana = $row['author_kana'];
            $book_publisher = $row['publisher'];
        }

        $sql = "SELECT school_name FROM school WHERE school_id = :school_id";
        $stmt = $db->pdo->prepare($sql);
        $stmt->bindValue(':school_id', $school_id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $school_name = $row['school_name'];
        }


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
    <title>予約内容の確認</title>
    <link rel="stylesheet" href="../css/reservation_confirm.css"> </head>
<body>

    <header class="site-header">
        <div class="header-logo">図書管理システム</div>
        <div class="header-user-area">
            <span class="welcome-text">こんにちは、<span class="user-name"><?php echo htmlspecialchars($student_name); ?></span> さん</span>
            <li><a href="javascript:link_logoutFORM.submit()">ログアウト</a></li>
        </div>
    </header>

    <!-- ログアウトボタンを押したときのCSFSトークン発行 -->
    <form method="POST" action = "../php/logout.php" name = "link_logoutFORM">
        <?php 
            set_csrf_token($csrf_token);
        ?>
        <input type="hidden" name = "page_id" value= "1">
    </form>

    <main class="main-container">
        
        <div class="confirm-card">
            <h1 class="page-title">予約内容の確認</h1>
            <p class="guide-text">以下の内容で予約を行います。<br>間違いがなければ「確定する」ボタンを押してください。</p>

            <section class="info-section student-section">
                <h2 class="section-title">予約する人</h2>
                <div class="info-grid">
                    <div class="info-label">学校名</div>
                    <div class="info-value"><?php echo htmlspecialchars($school_name); ?></div>
                    
                    <div class="info-label">学年・組</div>
                    <div class="info-value"><?php echo htmlspecialchars($grade); ?>年 <?php echo htmlspecialchars($class); ?>組</div>
                    
                    <div class="info-label">氏名</div>
                    <div class="info-value highlight-name"><?php echo htmlspecialchars($student_name); ?></div>
                </div>
            </section>

            <section class="info-section book-section">
                <h2 class="section-title">予約する本</h2>
                <div class="book-display">
                    <div class="book-image">
                        <img src="../images/book_placeholder.png" alt="書籍画像">
                    </div>
                    <div class="book-details">
                        <div class="detail-row">
                            <span class="d-label">書籍番号</span>
                            <span class="d-value"><?php echo htmlspecialchars($book_id); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="d-label">タイトル</span>
                            <span class="d-value title-text"><?php echo htmlspecialchars($book_title); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="d-label">ISBN</span>
                            <span class="d-value title-text"><?php echo htmlspecialchars($book_isbn); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="d-label">著者</span>
                            <span class="d-value"><?php echo htmlspecialchars($book_author); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="d-label">著者(カナ)</span>
                            <span class="d-value"><?php echo htmlspecialchars($book_author_kana); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="d-label">出版社</span>
                            <span class="d-value"><?php echo htmlspecialchars($book_publisher); ?></span>
                        </div>
                    </div>
                </div>
            </section>

            <div class="action-area">
                <button type="button" class="btn btn-cancel" onclick="history.back()">修正する</button>

                <form action="../php/reservation.php" method="POST" class="confirm-form">
                    <input type="hidden" name="book_id" value="<?php echo htmlspecialchars($book_id); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <button type="submit" class="btn btn-confirm">この内容で予約する</button>
                </form>
            </div>

        </div>
    </main>

</body>
</html>