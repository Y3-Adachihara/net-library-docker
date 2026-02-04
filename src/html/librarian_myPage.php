<?php
    // セッションの開始
    session_start();
    require_once '../db_connect.php';

    header("Cache-Control:no-cache,no-store,must-revalidate,max-age=0,post-check=0,pre-check=0");
    header("Pragma:no-cache");

    if (!isset($_SESSION['librarian_id'])) {
        // 司書としてログインしていない場合、ログインページへリダイレクト
        $_SESSION['message'] = "司書としてログインしてください。";
        header("Location: librarian_login.php");
        exit();
    }

    //　ここからは、司書としてログインしていないと実行されない
    $_librarian_id = $_SESSION['librarian_id'];

    // 予約参照画面のメッセージ
    $error_message = $_SESSION['res_refer_error'] ?? null;
    if (isset($_SESSION['res_refer_error'])) {
        echo "<script>alert('" . htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') . "');</script>";
        unset($_SESSION['res_refer_error']);
    }

    if (isset($_SESSION['login_success_message'])) {
        $error_message = $_SESSION['login_success_message'] ?? null;
        echo "<script>alert('" . h($error_message) . "');</script>";
        unset($_SESSION['login_success_message']);
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
        //ここでトークンを隠し属性として送るためのhtmlコードを記述
        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') . '">';
    }

    // HTMLエスケープ関数
    function h($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    // テーブルデータ表示関数
    function table_data_display(array $records): void {

        if (empty($records)) {
            echo "<tr><td colspan='6'>該当する貸出情報はありません。</td></tr>";
            return;
        }

        foreach($records as $rows) {
            $book_id = $rows['book_id'] ?? 'Error';
            $title = $rows['title'] ?? 'Error';
            $publisher = $rows['publisher'] ?? 'Error';
            $author_name = $rows['author_name'] ?? 'Error';
            $belong_id = $rows['grade'] . "年" . $rows['class'] . "組" . $rows['number'] . "番" ?? 'Error';
            $family_name = $rows['family_name'] ?? 'Error';
            $first_name = $rows['first_name'] ?? 'Error';

            $lending_date = $rows['lending_date'];
            $return_date = $rows['return_date'];

            // 貸出日と返却日を日付オブジェクトとして取得(return_dateが空の時に非推奨のエラーが出てきたから、三段演算子で対策)
            $lending_dt_obj = !empty($lending_date) ? new DateTime($lending_date) :null;
            $return_dt_obj = !empty($return_date) ? new DateTime($return_date) :null;

            // 今日の日付を取得
            $today = new DateTime('today');
            // 貸出日の1週間後の日付オブジェクトを取得
            $limit_date = new DateTime($lending_date);
            $limit_date->modify('+1 week');
            

            // 「貸出日と返却日が両方あるとき」
            if ((!empty($lending_date) && !empty($return_date))) {

                // 貸出日と返却日の差分を取得
                $interval = $lending_dt_obj->diff($return_dt_obj);

                // 延滞貸出だった時（1週間）
                if ($interval->invert == 0 && $interval->days > 7) {
                    $status_name = '延滞返却（'. $interval->days .'日延滞)';
                } else {
                    $status_name = '返却済';
                }

            // 「貸出日はあるが、返却日がないとき」
            } else if (!$return_date) {

                if ($today > $limit_date) {
                    $status_name = '延滞中';
                } else {
                    $status_name = '貸出中';
                }

            // 貸出日がないのに、返却日があるとき。またはどちらもないのに表示されているとき
            } else {
                $status_name = '処理エラー発生中';
            }

            //苗字と名前は別れているため、フルネームを作成
            $full_name = $family_name . " " . $first_name;
                
            echo "<tr>";
            echo "<td>" . h($book_id) . "</td>";
            echo "<td>" . h($title) . "</td>";
            echo "<td>" . h($publisher) . "</td>";
            echo "<td>" . h($belong_id) . "</td>";
            echo "<td>" . h($full_name) . "</td>";
            echo "<td>" . h($lending_date) . "</td>";
            if (is_null($return_date)) {
                echo "<td>未返却</td>";
            } else {
                echo "<td>" . h($return_date) . "</td>";
            }
            echo "<td>" . h($status_name) . "</td>";
            echo "</tr>";
        }
    }

    //現在の日付を取得
    $current_date = new DateTimeImmutable();
    $current_date_str = $current_date->format('Y-m-d H:i:s');
        
    //1年前の日付を取得
    $lastYear_date = $current_date->modify('-2 year');
    $lastYear_date_str = $lastYear_date->format('Y-m-d H:i:s');

    $fetchAll_record = [];  //結果が無かった場合に備えて初期化
        
    try {
        $db = new db_connect();
        $db->connect();

        //司書の学校IDと学校名を取得
        $get_librarian_school_sql = "SELECT lib.school_id, sch.school_name FROM librarian AS lib";
        $get_librarian_school_sql .= " LEFT OUTER JOIN school AS sch ON lib.school_id = sch.school_id";
        $get_librarian_school_sql .= " WHERE lib.librarian_id = :librarian_id;";

        $stmt = $db->pdo->prepare($get_librarian_school_sql);
        $stmt->bindValue(':librarian_id', $_librarian_id, PDO::PARAM_INT);
        $stmt->execute();
        $librarian_school_id = $stmt->fetch(PDO::FETCH_ASSOC);
        $school_id = intval($librarian_school_id['school_id']);
        $school_name = $librarian_school_id['school_name'];


        //結合するテーブル（書籍テーブル、学生テーブル, 書籍テーブル、書籍状態テーブル、貸出テーブル、学校テーブル）から、過去1年間の貸出情報を取得
        $sql = "SELECT b_sc.book_id, b_if.title, b_if.publisher, stu.grade, stu.class, stu.number, stu.family_name, stu.first_name, l.lending_date, l.return_date, b_st.status_id, b_st.status_name";
        $sql .= " FROM lending AS l";
        $sql .= " LEFT OUTER JOIN book_stack AS b_sc";
        $sql .= " ON l.book_id = b_sc.book_id";
        $sql .= " LEFT OUTER JOIN book_info AS b_if";
        $sql .= " ON b_sc.isbn = b_if.isbn";
        $sql .= " LEFT OUTER JOIN student AS stu";
        $sql .= " ON l.student_id = stu.student_id";
        $sql .= " LEFT OUTER JOIN book_status AS b_st";
        $sql .= " ON b_sc.status_id = b_st.status_id";
        $sql .= " WHERE l.lending_date BETWEEN :lastYearDate AND :currentDate";
        $sql .= " AND stu.school_id = :school_id";
        $sql .= " ORDER BY l.lending_date DESC;";

        $stmt = $db->pdo->prepare($sql);
        $stmt->bindValue(':lastYearDate', $lastYear_date_str, PDO::PARAM_STR);
        $stmt->bindValue(':currentDate', $current_date_str, PDO::PARAM_STR);
        $stmt->bindValue(':school_id', $school_id, PDO::PARAM_INT);
        $stmt->execute();
        $fetchAll_record= $stmt->fetchAll(PDO::FETCH_ASSOC);
            
    } catch (PDOException $e) {
        $error_message = "データの取得に失敗しました。" . $e->getMessage();
        echo "<script>alert('" . htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') . "');</script>";
    } catch (Exception $e) {
        $error_message = "予期せぬエラーが発生しました。" . $e->getMessage();
        echo "<script>alert('" . htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') . "');</script>";
    }

    $news_list = []; // 初期化
    try {
      // お知らせを取得（公開中のもの、最新5件）
      $sql_news = "SELECT * FROM announcements WHERE is_active = 1 ORDER BY announcements_date DESC LIMIT 5";
      $stmt_news = $db->pdo->prepare($sql_news);
      $stmt_news->execute();
      $news_list = $stmt_news->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
    // エラーが出ても画面全体が止まらないように静かに処理、またはログに残す
    // echo "お知らせ取得エラー: " . h($e->getMessage()); 
    }

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>司書用マイページ(<?php echo h($school_name); ?>)</title>
    <link rel="stylesheet" href="../css/librarian_myPage.css">
</head>
<script>
    function confirmLogout() {
        if(window.confirm('本当にログアウトしますか？')) {
            document.link_logoutFORM.submit();
        }
    }
</script>
    <body>
        <header class="main-header">
        <div class="header-logo">
            <a href="librarian_myPage.php">司書用Myページ(<?php echo h($school_name); ?>)</a>
        </div>
        <nav class="header-nav">
            <ul>
                <li><a href="#" onclick="alert('余裕があったら、マイページの使い方やヘルプ等を説明するページを作ってもいいかも？'); return false;">はじめての方へ</a></li>
                <li><a href="#" onclick = "confirmLogout(); return false;">ログアウト</a></li>
            </ul>
        </nav>
        </header>

        <!-- ログアウトボタンを押したときのCSFSトークン発行 -->
        <form method="POST" action = "../php/logout.php" name = "link_logoutFORM">
            <?php
                set_csrf_token($csrf_token);
            ?>
            <input type="hidden" name = "page_id" value= "1">
        </form>

        <form method="GET" class="librarian-menu-form">
<style>
    /* メニュー全体のコンテナ */
    .menu-container {
        width: 100%;             /* 横幅を100%使う */
        margin: 20px 0;          
        display: flex;
        flex-direction: column;  /* グループを縦（上・下）に積む */
        gap: 30px;               /* グループ間の余白 */
    }

    /* 各セクション（白い背景の箱） */
    .menu-section {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        width: 100%;             /* 箱自体の幅も100% */
        box-sizing: border-box;  /* パディングを含めて幅計算 */
    }

    .section-title {
        font-size: 18px;
        font-weight: bold;
        color: #555;
        margin-bottom: 15px;
        border-left: 5px solid #1a73e8;
        padding-left: 15px;
    }

    /* ★ここがポイント：ボタンを横に4つ並べる設定 */
    .menu-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr); /* 1行に4つ均等配置 */
        gap: 20px;                             /* ボタン同士の間隔 */
    }

    /* ボタンのデザイン調整 */
    .action-btn {
        border: none;
        border-radius: 8px;
        padding: 10px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        text-align: center;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 8px;
        height: 100px; /* ボタンの高さを統一して見やすく */
        width: 100%;
    }
    
    .action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        opacity: 0.95;
    }

    /* ボタンの色（グラデーションでリッチに） */
    .btn-primary { background: linear-gradient(135deg, #1a73e8, #1557b0); }
    .btn-success { background: linear-gradient(135deg, #2e8b57, #246b43); }
    .btn-warning { background: linear-gradient(135deg, #f39c12, #d68910); }
    .btn-info    { background: linear-gradient(135deg, #17a2b8, #138496); }
    
    .btn-icon { font-size: 24px; margin-bottom: 5px; }

    /* 画面が狭くなった時の対応（スマホ・タブレット用） */
    @media (max-width: 900px) {
        .menu-grid { grid-template-columns: repeat(2, 1fr); } /* 2列にする */
    }
    @media (max-width: 600px) {
        .menu-grid { grid-template-columns: 1fr; } /* 1列にする */
    }
</style>

<form method="GET" class="menu-container">
    
    <div class="menu-section">
        <div class="section-title">カウンター業務（メイン）</div>
        <div class="menu-grid">
            <button type="submit" formaction="../html/貸出返却.php" class="action-btn btn-primary">
                <span class="btn-icon">📖</span> 貸出・返却
            </button>
            <button type="submit" formaction="../html/検索画面.php" class="action-btn btn-primary">
                <span class="btn-icon">🔍</span> 書籍検索
            </button>
            <button type="submit" formaction="../html/librarian_bookManagement.php" class="action-btn btn-primary">
                <span class="btn-icon">📋</span> 予約本取り置き
            </button>
             <button type="submit" formaction="../html/librarian_incoming_books.php" class="action-btn btn-primary">
                <span class="btn-icon">📦</span> 検品・仕分け
            </button>
        </div>
    </div>

    <div class="menu-section">
        <div class="section-title">管理・データ</div>
        <div class="menu-grid">
            <button type="submit" formaction="../html/librarian_reservation_reference.php" class="action-btn btn-warning">
                <span class="btn-icon">📅</span> 予約状況参照
            </button>
            <button type="submit" formaction="../html/書籍登録.html" class="action-btn btn-success">
                <span class="btn-icon">➕</span> 新規書籍登録
            </button>
            <button type="submit" formaction="../html/announcements_register.php" class="action-btn btn-info">
                <span class="btn-icon">📢</span> お知らせ登録
            </button>
            <button type="submit" formaction="../html/ranking.php" class="action-btn btn-info">
                <span class="btn-icon">👑</span> ランキング
            </button>
        </div>
    </div>

</form>
</form>

        <div class ="info-table-container">
            <h2 ><?php echo h($school_name); ?>の貸出履歴（過去一年間）</h2>

            <div class = "scroll-wrapper">
                <table class="info-table">
                    <thead>
                        <tr>
                            <th>書籍ID</th> <!-- 貸出IDから変更 -->
                            <th>タイトル</th>
                            <th>出版社</th>
                            <th>所属</th>
                            <th>貸出者名</th>
                            <th>貸出日</th>
                            <th>返却日</th>
                            <th>貸出状態</th>   <!-- 貸出した学生の状態を記述するように変更 -->
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                            table_data_display($fetchAll_record);
                        ?>
                    </tbody>

                </table>
            </div>
        </div>
	
	<style>
            .news-list-wrapper { padding: 10px 20px; }
            .news-item {
                border-bottom: 1px solid #eee;
                padding: 12px 0;
                display: flex;
                align-items: flex-start;
                flex-wrap: wrap;
            }
            .news-item:last-child { border-bottom: none; }
            .news-meta {
                min-width: 150px;
                flex-shrink: 0;
            }
            .news-date {
                color: #888;
                font-size: 0.9em;
                margin-right: 8px;
            }
            .news-title-wrap { flex-grow: 1; }
            .news-title {
                font-weight: bold;
                color: #333;
                margin: 0 0 5px 0;
                font-size: 1em;
            }
            .news-content {
                color: #666;
                font-size: 0.9em;
                white-space: pre-wrap;
                margin: 0;
            }
            /* バッジの色設定 */
            .category-badge {
                display: inline-block;
                padding: 2px 8px;
                border-radius: 4px;
                font-size: 0.75em;
                color: white;
                font-weight: bold;
                text-align: center;
                min-width: 60px;
            }
            .cat-important { background-color: #ff5252; }
            .cat-event { background-color: #4caf50; }
            .cat-new { background-color: #2196f3; }
            .cat-other { background-color: #9e9e9e; }
        </style>

        <div class="info-table-container" style="margin-top: 30px; margin-bottom: 50px;">
            <h2>📢 図書館からのお知らせ</h2>

            <div class="news-list-wrapper">
                <?php if (!empty($news_list)): ?>
                    <?php foreach ($news_list as $news): ?>
                        <?php 
                            // バッジの色判定
                            $badge_class = 'cat-other';
                            if ($news['announcements_category'] === '重要') $badge_class = 'cat-important';
                            elseif ($news['announcements_category'] === 'イベント') $badge_class = 'cat-event';
                            elseif ($news['announcements_category'] === '新着') $badge_class = 'cat-new';
                            
                            // 日付フォーマット
                            $date = date('Y/m/d', strtotime($news['announcements_date']));
                        ?>

                        <div class="news-item">
                            <div class="news-meta">
                                <span class="news-date"><?php echo h($date); ?></span>
                                <span class="category-badge <?php echo $badge_class; ?>">
                                    <?php echo h($news['announcements_category']); ?>
                                </span>
                            </div>
                            <div class="news-title-wrap">
                                <p class="news-title">
                                    <?php echo h($news['announcements_title']); ?>
                                </p>
                                <p class="news-content"><?php echo h($news['announcements_content']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="padding: 20px; text-align: center; color: #666;">現在、お知らせはありません。</p>
                <?php endif; ?>
            </div>
        </div>
	
    </body>
</html>