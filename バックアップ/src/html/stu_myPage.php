<?php
    session_start();
    require_once '../db_connect.php';

    header("Cache-Control:no-cache,no-store,must-revalidate,max-age=0,post-check=0,pre-check=0");
    header("Pragma:no-cache");

    if (!isset($_SESSION['student_id'])) {
        // 学生としてログインしていない場合、ログインページへリダイレクト
        $_SESSION['message'] = "ログインしてください。";
        header("Location: student_login.php");
        exit();
    }

    $family_name = $_SESSION['student_family_name'];
    $first_name = $_SESSION['student_first_name'];
    // 苗字と名前を結合
    $student_name = $family_name . " " . $first_name;

    $message = $_SESSION['to_stu_myPage_message'] ?? null;
    if (isset($_SESSION['to_stu_myPage_message'])) {
        echo "<script>alert('" . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . "');</script>";
        unset($_SESSION['to_stu_myPage_message']);
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
    
    $news_list = []; // 初期化
    try {
        $db = new db_connect();
        $db->connect();

        // お知らせを取得（公開中のもの、最新5件）
        $sql_news = "SELECT * FROM announcements WHERE is_active = 1 ORDER BY announcements_date DESC LIMIT 5";
        $stmt_news = $db->pdo->prepare($sql_news);
        $stmt_news->execute();
        $news_list = $stmt_news->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        // エラー時は何もしない（画面が壊れないように）
    }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>生徒用マイページ</title>
    <style>
        /* 共通スタイル */
        body {
            font-family: "Helvetica Neue", Arial, "Hiragino Kaku Gothic ProN", "Hiragino Sans", "Meiryo", sans-serif;
            background-color: #f0f2f5; /* 少し落ち着いたグレー */
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            padding-top: 80px; /* ヘッダーの高さ分、上に余白を空ける */
        }

        /* ヘッダーエリア（デザイン変更のメイン） */
        .header {
            width: 100%;
            height: 60px;
            background-color: #ffffff;
            display: flex;
            justify-content: space-between; /* 左右に振り分け */
            align-items: center;
            padding: 0 5%; /* 左右に少し余裕を持たせる */
            box-sizing: border-box;
            position: fixed; /* 上部に固定 */
            top: 0;
            left: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); /* 下に影をつけて浮かす */
        }

        /* 「こんにちは○○さん」のテキスト */
        .welcome-text {
            text-decoration: none;
            color: #333;
            font-size: 15px;
            font-weight: 500;
        }
        .user-name {
            font-weight: bold;
            color: #1a73e8; /* 名前を少し強調 */
        }

        /* ログアウトボタンのスタイル */
        .logout-btn {
            background-color: #ff4d4f; /* 警告色の赤系に */
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 20px; /* 丸みをつけてモダンに */
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .logout-btn:hover {
            background-color: #ff7875;
            box-shadow: 0 2px 5px rgba(255, 77, 79, 0.3);
        }

        /* メインメニューのコンテナ */
        .menu-container {
            background-color: #fff;
            border-radius: 12px; /* 角を丸く */
            border: none; /* 線を消して影で表現 */
            width: 90%;
            max-width: 900px;
            min-height: 500px;
            padding: 40px;
            box-sizing: border-box;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            margin-bottom: 40px;
        }

        /* 画面左上のタイトル */
        .page-title {
            font-size: 22px;
            font-weight: bold;
            margin: 0 0 30px 0;
            text-align: left;
            color: #2c3e50;
            border-left: 5px solid #1a73e8; /* タイトル横にアクセントの青線 */
            padding-left: 15px;
        }

        /* ボタンが並ぶエリア */
        .button-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            gap: 20px; /* ボタン同士の隙間 */
        }

        /* メインの3つのボタン共通スタイル */
        .menu-btn {
            width: 32%;
            height: 110px;
            font-size: 16px;
            font-weight: bold;
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            color: #444;
        }

        .menu-btn:hover {
            background-color: #f8fbff;
            border-color: #1a73e8;
            color: #1a73e8;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(26, 115, 232, 0.1);
        }

        /* お知らせセクション */
        .news-section {
            margin-top: 10px;
            text-align: left;
            border-top: 2px solid #f0f0f0;
            padding-top: 20px;
            overflow-y: auto;
        }
        .news-header-text {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }
        .news-item {
            border-bottom: 1px solid #f5f5f5;
            padding: 15px 0;
            display: flex;
            align-items: flex-start;
        }
        .news-item:last-child { border-bottom: none; }
        
        .news-meta {
            min-width: 120px;
            flex-shrink: 0;
        }
        .news-date {
            color: #999;
            font-size: 0.85em;
            margin-bottom: 5px;
            display: block;
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
            line-height: 1.6;
            white-space: pre-wrap;
            margin: 0;
        }
        
        /* バッジ */
        .category-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 4px;
            font-size: 0.7em;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        .cat-important { background-color: #ff5252; }
        .cat-event { background-color: #4caf50; }
        .cat-new { background-color: #2196f3; }
        .cat-other { background-color: #9e9e9e; }
    </style>
</head>
<body>

    <form method="POST" action = "../php/logout.php" id = "logout_form">
        <?php 
            set_csrf_token($csrf_token);
        ?>
        <input type="hidden" name = "page_id" value= "0">
    </form>

    <div class="header">
        <a href="#" class="welcome-text">こんにちは、<span class="user-name"><?php echo htmlspecialchars($student_name, ENT_QUOTES, 'UTF-8'); ?></span> さん</a>
        <button class="logout-btn" onclick="confirmLogout()">ログアウト</button>
    </div>

    <div class="menu-container">
        
        <div class="page-title">生徒用マイページ</div>

        <div class="button-row">
            <button class="menu-btn" onclick="location.href='検索画面_生徒.php'">
                検索する
            </button>

            <button class="menu-btn" onclick="location.href='test.php'">
                予約する
            </button>

            <button class="menu-btn" onclick="location.href='student_reservation_reference.php'">
                予約情報参照
            </button>
        </div>
	
	<div class="news-section">
            <div class="news-header-text">📢 図書館からのお知らせ</div>
            
            <?php if (!empty($news_list)): ?>
                <?php foreach ($news_list as $news): ?>
                    <?php 
                        $badge_class = 'cat-other';
                        if ($news['announcements_category'] === '重要') $badge_class = 'cat-important';
                        elseif ($news['announcements_category'] === 'イベント') $badge_class = 'cat-event';
                        elseif ($news['announcements_category'] === '新着') $badge_class = 'cat-new';
                        
                        $date = date('Y/m/d', strtotime($news['announcements_date']));
                    ?>
                    <div class="news-item">
                        <div class="news-meta">
                            <span class="news-date"><?php echo htmlspecialchars($date, ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="category-badge <?php echo $badge_class; ?>">
                                <?php echo htmlspecialchars($news['announcements_category'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>
                        <div class="news-title-wrap">
                            <p class="news-title">
                                <?php echo htmlspecialchars($news['announcements_title'], ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                            <p class="news-content"><?php echo htmlspecialchars($news['announcements_content'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #888; text-align: center;">現在、お知らせはありません。</p>
            <?php endif; ?>
        </div>

    </div>

    <script>
        /**
         * ④ ログアウト確認ポップアップ
         */
        function confirmLogout() {
            if (confirm("ログアウトしますか？")) {
                document.getElementById('logout_form').submit();
            }
        }
    </script>
</body>
</html>