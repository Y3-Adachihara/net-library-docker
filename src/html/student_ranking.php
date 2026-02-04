<?php
    require_once '../db_connect.php';
    session_start();

    if (!isset($_SESSION['student_id'])) {
        // 生徒としてログインしていない場合、ログインページへリダイレクト
        $_SESSION['message'] = "ログインしてください。";
        header("Location: student_login.php");
        exit();
    }

    //　ここからは、生徒としてログインしていないと実行されない
    $student_id = $_SESSION['student_id'];
    $school_name = null;
    $student_belong = $_SESSION['student_grade'] . "年" . $_SESSION['student_class'] . "組" . $_SESSION['student_number'] . "番" ?? '';
    $student_fullname = $_SESSION['student_family_name'] . " " . $_SESSION['student_first_name'] ?? '';

    $school_popular_data = [];

    $ranking_results = [];

    // HTMLエスケープ関数
    function h($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    // レベル判定関数（デモ用）
    function getReadLevel($count) {
        if ($count >= 50) return ['Lv' => 'MAX', 'title' => 'マスター', 'color' => '#e67e22'];
        if ($count >= 30) return ['Lv' => '5',   'title' => 'エキスパート', 'color' => '#9b59b6'];
        if ($count >= 20) return ['Lv' => '4',   'title' => 'スペシャリスト', 'color' => '#3498db'];
        if ($count >= 10) return ['Lv' => '3',   'title' => 'アドバンスド', 'color' => '#2ecc71'];
        if ($count >= 5)  return ['Lv' => '2',   'title' => 'スタンダード', 'color' => '#f1c40f'];
        return ['Lv' => '1', 'title' => 'ゲスト', 'color' => '#95a5a6'];
    }

    try {
        $db = new db_connect();
        $db->connect();

        $school_sql = "SELECT school_id, school_name FROM school";
        $school_stmt = $db->pdo->query($school_sql);
        $schools = $school_stmt->fetchAll(PDO::FETCH_ASSOC);
        $school_stmt->closeCursor();

        // ネット図書館全体でのｔレンド本取得SQL
        $trending_sql = "SELECT 
                            bi.isbn, 
                            bi.title, 
                            bi.author_name,
                            COUNT(l.lending_id) AS trend_count
                        FROM lending AS l
                        JOIN book_stack AS bs ON l.book_id = bs.book_id
                        JOIN book_info AS bi ON bs.isbn = bi.isbn
                        WHERE l.lending_date >= DATE_SUB(NOW(), INTERVAL 14 DAY) /* 直近14日間に限定 */
                        GROUP BY bi.isbn, bi.title, bi.author_name
                        ORDER BY trend_count DESC
                        LIMIT 10;
                        ";
        $trending_stmt = $db->pdo->prepare($trending_sql);
        $trending_stmt->execute();
        $trending_books = $trending_stmt->fetchAll(PDO::FETCH_ASSOC);
        $trending_stmt->closeCursor();

        // 学校別ランキングSQL（学校名も結合）
        $in_school_sql = "SELECT 
                            bi.isbn, bi.title, COUNT(l.lending_id) AS school_popular_count
                        FROM lending AS l
                        JOIN student AS s ON l.student_id = s.student_id
                        JOIN book_stack AS bs ON l.book_id = bs.book_id
                        JOIN book_info AS bi ON bs.isbn = bi.isbn
                        WHERE s.school_id = :school_id
                        GROUP BY bi.isbn, bi.title
                        ORDER BY school_popular_count DESC
                        LIMIT 5; -- 10校分出すなら、1校あたり5件くらいがスッキリします";

        $in_school_stmt = $db->pdo->prepare($in_school_sql);

        foreach ($schools as $sch) {
            if ($sch['school_id'] == 0) continue;
            $in_school_stmt->bindValue(':school_id', $sch['school_id'], PDO::PARAM_INT);
            $in_school_stmt->execute();
            $school_popular_data[$sch['school_name']] = $in_school_stmt->fetchAll(PDO::FETCH_ASSOC);
            $in_school_stmt->closeCursor();
        }

        // 読破王ランキングSQL
        $ranking_sql = "SELECT 
                        s.family_name, 
                        s.first_name, 
                        sc.school_name,
                        COUNT(l.lending_id) AS read_count
                    FROM lending AS l
                    JOIN student AS s ON l.student_id = s.student_id
                    JOIN school AS sc ON s.school_id = sc.school_id
                    WHERE l.return_date IS NOT NULL 
                    GROUP BY s.student_id
                    ORDER BY read_count DESC
                    LIMIT 10";

        $ranking_stmt = $db->pdo->prepare($ranking_sql);
        $ranking_stmt->execute();
        $ranking_results = $ranking_stmt->fetchAll(PDO::FETCH_ASSOC);
        $ranking_stmt->closeCursor();


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
    <title>読書ランキング | ネット図書館</title>
    <link rel="stylesheet" href="../css/student_ranking.css">
</head>
<body>

<header>
    <div class="header-container">
        <a href="stu_myPage.php">マイページへ戻る</a>
        <h1><a href="student_ranking.php">📖 読書ランキング・ステーション</a></h1>
        <div class="user-info">
            <?php echo h($student_fullname); ?> さん（<?php echo h($student_belong); ?>）
        </div>
    </div>
</header>

<main class="ranking-container">

    <div class="top-row">
        <section class="panel trending-section">
            <h2 class="section-title">🔥 ネット図書館トレンド (14日間)</h2>
            <ul class="ranking-list">
                <?php foreach ($trending_books as $index => $book): ?>
                    <li class="ranking-item">
                        <span class="rank-num"><?php echo $index + 1; ?></span>
                        <div class="book-detail">
                            <p class="title"><?php echo h($book['title']); ?></p>
                            <p class="author"><?php echo h($book['author_name']); ?></p>
                        </div>
                        <span class="count"><?php echo h($book['trend_count']); ?>冊</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="panel king-section">
            <h2 class="section-title">👑 歴代・読破王（読了数）</h2>
            <ul class="ranking-list">
                <?php foreach ($ranking_results as $index => $user): 
                    $level = getReadLevel($user['read_count']); 
                ?>
                    <li class="ranking-item">
                        <span class="rank-num rank-<?php echo $index + 1; ?>"><?php echo $index + 1; ?></span>
                        <div class="user-detail">
                            <p class="name"><?php echo h($user['family_name'] . ' ' . $user['first_name']); ?></p>
                            <p class="school"><?php echo h($user['school_name']); ?></p>
                        </div>
                        <div class="badge" style="background-color: <?php echo $level['color']; ?>">
                            <?php echo $level['title']; ?> (<?php echo $level['Lv']; ?>)
                        </div>
                        <span class="count"><?php echo h($user['read_count']); ?>冊</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </div>

    <section class="school-grid-section">
        <h2 class="section-title">🏫 学校別・人気本ベスト5</h2>
        <div class="school-grid">
            <?php foreach ($school_popular_data as $s_name => $books): ?>
                <div class="school-card">
                    <h3><?php echo h($s_name); ?></h3>
                    <ul class="mini-ranking">
                        <?php if (empty($books)): ?>
                            <li class="empty">まだデータがありません</li>
                        <?php else: ?>
                            <?php foreach ($books as $m_index => $m_book): ?>
                                <li>
                                    <span class="m-rank"><?php echo $m_index + 1; ?></span>
                                    <span class="m-title"><?php echo h($m_book['title']); ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

</main>

</body>
</html>