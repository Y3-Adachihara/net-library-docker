<?php
    session_start();
    require_once '../db_connect.php';

    header("Cache-Control:no-cache,no-store,must-revalidate,max-age=0,post-check=0,pre-check=0");
    header("Pragma:no-cache");

    if (!isset($_SESSION['student_id'])) {
        $_SESSION['message'] = "ログインしてください。";
        header("Location: student_login.php");
        exit();
    }

    $student_id = $_SESSION['student_id'];
    $family_name = $_SESSION['student_family_name'];
    $first_name = $_SESSION['student_first_name'];
    $student_name = $family_name . " " . $first_name;

    $message = $_SESSION['to_stu_myPage_message'] ?? null;
    if (isset($_SESSION['to_stu_myPage_message'])) {
        echo "<script>alert('" . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . "');</script>";
        unset($_SESSION['to_stu_myPage_message']);
    }

    function csrf_token_generate(): string {
        $toke_byte = random_bytes(16);
        $csrf_token = bin2hex($toke_byte);
        return $csrf_token;
    }
    $csrf_token = csrf_token_generate();

    function set_csrf_token(String $csrf_token): void {
        $_SESSION['csrf_token'] = $csrf_token;
        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') . '">';
    }
    
    // HTMLエスケープ関数
    function h($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    // --- レベル判定ロジック ---
    function getStudentLevelStatus($count) {
        $levels = [
            50 => ['MAX', 'マスター'],
            30 => ['5',   'エキスパート'],
            20 => ['4',   'スペシャリスト'],
            10 => ['3',   'アドバンスド'],
            5  => ['2',   'スタンダード'],
            0  => ['1',   'ゲスト']
        ];

        $current_lv = '1';
        $current_name = 'ゲスト';
        $next_threshold = 5;
        $next_name = 'スタンダード';
        $is_max = false;

        foreach ($levels as $threshold => $info) {
            if ($count >= $threshold) {
                $current_lv = $info[0];
                $current_name = $info[1];
                
                // 次のレベルを探す
                $prev_threshold = null;
                $prev_name = null;
                foreach (array_reverse($levels, true) as $th => $val) {
                    if ($th > $threshold) {
                        $next_threshold = $th;
                        $next_name = $val[1];
                        break;
                    }
                    if ($th == 50) {
                         $is_max = true;
                    }
                }
                break;
            }
        }

        $remaining = $is_max ? 0 : ($next_threshold - $count);

        return [
            'level_text' => "Lv{$current_lv}：{$current_name}",
            'is_max' => $is_max,
            'next_name' => $next_name,
            'remaining' => $remaining
        ];
    }
    
    $news_list = []; 
    $ranking_list = []; 
    $level_info = [];

    try {
        $db = new db_connect();
        $db->connect();

        // 1. お知らせ
        $sql_news = "SELECT * FROM announcements WHERE is_active = 1 ORDER BY announcements_date DESC LIMIT 5";
        $stmt_news = $db->pdo->prepare($sql_news);
        $stmt_news->execute();
        $news_list = $stmt_news->fetchAll(PDO::FETCH_ASSOC);

        // 2. ランキング
        $sql_rank = "SELECT 
                        bi.title, 
                        COUNT(l.lending_id) AS rental_count
                     FROM lending l
                     INNER JOIN book_stack bs ON l.book_id = bs.book_id
                     LEFT OUTER JOIN book_info bi ON bs.isbn = bi.isbn
                     GROUP BY bi.title
                     ORDER BY rental_count DESC
                     LIMIT 10"; 
        $stmt_rank = $db->pdo->prepare($sql_rank);
        $stmt_rank->execute();
        $ranking_list = $stmt_rank->fetchAll(PDO::FETCH_ASSOC);

        // 3. 自分の読了数
        $sql_count = "SELECT COUNT(*) FROM lending WHERE student_id = :sid AND return_date IS NOT NULL";
        $stmt_count = $db->pdo->prepare($sql_count);
        $stmt_count->bindValue(':sid', $student_id, PDO::PARAM_INT);
        $stmt_count->execute();
        $my_read_count = $stmt_count->fetchColumn();

        $level_info = getStudentLevelStatus($my_read_count);

    } catch (Exception $e) {
        // エラー時は何もしない
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
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            padding-top: 80px;
        }

        .header {
            width: 100%;
            height: 60px;
            background-color: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 5%;
            box-sizing: border-box;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-left {
            display: flex;
            align-items: baseline;
            gap: 15px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 15px; /* ボタン間の隙間 */
        }

        .welcome-text {
            text-decoration: none;
            color: #333;
            font-size: 15px;
            font-weight: 500;
        }
        .user-name {
            font-weight: bold;
            color: #1a73e8;
            font-size: 16px;
        }

        .level-display {
            font-size: 14px;
            color: #555;
            background-color: #f8f9fa;
            padding: 4px 10px;
            border-radius: 15px;
            border: 1px solid #e9ecef;
        }
        .level-up-alert {
            color: #e67e22;
            font-weight: bold;
            font-size: 13px;
            animation: flash 2s infinite;
        }
        @keyframes flash {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        /* ランク一覧ボタン */
        .rank-list-btn {
            background-color: #fff;
            color: #1a73e8;
            border: 1px solid #1a73e8;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .rank-list-btn:hover {
            background-color: #f0f8ff;
        }

        .logout-btn {
            background-color: #ff4d4f;
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .logout-btn:hover {
            background-color: #ff7875;
            box-shadow: 0 2px 5px rgba(255, 77, 79, 0.3);
        }

        /* --- モーダルウィンドウのスタイル --- */
        .modal {
            display: none; /* 初期状態は非表示 */
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5); /* 半透明の黒背景 */
            animation: fadeIn 0.3s;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto; /* 画面中央少し上 */
            padding: 25px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            position: relative;
        }

        .close-btn {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 20px;
        }
        .close-btn:hover { color: #000; }

        .modal-title {
            margin-top: 0;
            color: #333;
            text-align: center;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        /* ランク表 */
        .rank-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .rank-table th, .rank-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .rank-table th { background-color: #f9f9f9; color: #555; }
        
        /* ランクバッジ色 */
        .badge-lv-max { color: #e67e22; font-weight: bold; } /* マスター */
        .badge-lv-5 { color: #9b59b6; font-weight: bold; }   /* エキスパート */
        .badge-lv-4 { color: #3498db; font-weight: bold; }   /* スペシャリスト */
        .badge-lv-3 { color: #2ecc71; font-weight: bold; }   /* アドバンスド */
        .badge-lv-2 { color: #f1c40f; font-weight: bold; }   /* スタンダード */
        .badge-lv-1 { color: #95a5a6; font-weight: bold; }   /* ゲスト */

        /* --- レイアウト用ラッパー --- */
        .content-wrapper {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 30px;
            width: 90%;
            max-width: 1200px;
            margin-bottom: 40px;
        }

        .main-panel {
            flex: 1;
            background-color: #fff;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            min-height: 500px;
        }

        .side-panel {
            width: 320px;
            background-color: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }

        @media (max-width: 900px) {
            .content-wrapper {
                flex-direction: column;
                align-items: center;
            }
            .main-panel, .side-panel {
                width: 100%;
                box-sizing: border-box;
            }
            .header-left {
                flex-direction: column;
                gap: 2px;
            }
            /* スマホでヘッダーボタンが窮屈にならないように調整 */
            .header-right { gap: 10px; }
            .rank-list-btn, .logout-btn { padding: 6px 12px; font-size: 11px; }
        }

        /* 既存スタイル省略部 */
        .page-title {
            font-size: 22px;
            font-weight: bold;
            margin: 0 0 30px 0;
            text-align: left;
            color: #2c3e50;
            border-left: 5px solid #1a73e8;
            padding-left: 15px;
        }
        .button-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            gap: 20px;
        }
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
        .news-section {
            margin-top: 10px;
            text-align: left;
            border-top: 2px solid #f0f0f0;
            padding-top: 20px;
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
        .news-meta { min-width: 120px; flex-shrink: 0; }
        .news-date { color: #999; font-size: 0.85em; margin-bottom: 5px; display: block; }
        .news-title-wrap { flex-grow: 1; }
        .news-title { font-weight: bold; color: #333; margin: 0 0 5px 0; font-size: 1em; }
        .news-content { color: #666; font-size: 0.9em; line-height: 1.6; white-space: pre-wrap; margin: 0; }
        .category-badge {
            display: inline-block; padding: 2px 10px; border-radius: 4px; 
            font-size: 0.7em; color: white; font-weight: bold; text-align: center;
        }
        .cat-important { background-color: #ff5252; }
        .cat-event { background-color: #4caf50; }
        .cat-new { background-color: #2196f3; }
        .cat-other { background-color: #9e9e9e; }
        .ranking-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .ranking-more-link {
            font-size: 13px;
            color: #1a73e8;
            text-decoration: none;
            border: 1px solid #1a73e8;
            padding: 4px 10px;
            border-radius: 15px;
            transition: all 0.2s;
            font-weight: normal;
        }
        .ranking-more-link:hover {
            background-color: #1a73e8;
            color: #fff;
        }
        .ranking-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .ranking-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px dashed #eee;
            font-size: 14px;
        }
        .ranking-item:last-child { border-bottom: none; }
        .rank-num {
            width: 24px;
            height: 24px;
            line-height: 24px;
            text-align: center;
            background: #eee;
            border-radius: 4px;
            font-weight: bold;
            color: #555;
            margin-right: 12px;
            flex-shrink: 0;
            font-size: 12px;
        }
        .rank-1 .rank-num { background: #ffd700; color: #fff; }
        .rank-2 .rank-num { background: #c0c0c0; color: #fff; }
        .rank-3 .rank-num { background: #cd7f32; color: #fff; }
        .rank-book-title {
            flex-grow: 1;
            line-height: 1.4;
            color: #333;
            font-weight: 500;
        }
        .rank-count {
            font-size: 12px;
            color: #888;
            margin-left: 8px;
            white-space: nowrap;
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 10px;
        }
    </style>
</head>
<body>

    <form method="POST" action = "../php/logout.php" id = "logout_form">
        <?php set_csrf_token($csrf_token); ?>
        <input type="hidden" name = "page_id" value= "0">
    </form>

    <div class="header">
        <div class="header-left">
            <span class="welcome-text">こんにちは、<span class="user-name"><?php echo h($student_name); ?></span> さん</span>
            
            <?php if (!empty($level_info)): ?>
                <span class="level-display">
                    あなたは現在 <?php echo h($level_info['level_text']); ?> です
                    <?php if (!$level_info['is_max'] && $level_info['remaining'] <= 2): ?>
                         <span class="level-up-alert">あと<?php echo $level_info['remaining']; ?>冊で<?php echo h($level_info['next_name']); ?>！！</span>
                    <?php endif; ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="header-right">
            <button class="rank-list-btn" onclick="openRankModal()">ランク一覧</button>
            <button class="logout-btn" onclick="confirmLogout()">ログアウト</button>
        </div>
    </div>

    <div class="content-wrapper">

        <div class="main-panel">
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
                <button class="menu-btn" onclick="location.href='student_lending_history.php'">
                    貸出履歴
                </button>
                 <button class="menu-btn" onclick="location.href='student_lending_deny_list.php'">
                    貸出禁止本一覧
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
                                <span class="news-date"><?php echo h($date); ?></span>
                                <span class="category-badge <?php echo $badge_class; ?>">
                                    <?php echo h($news['announcements_category']); ?>
                                </span>
                            </div>
                            <div class="news-title-wrap">
                                <p class="news-title"><?php echo h($news['announcements_title']); ?></p>
                                <p class="news-content"><?php echo h($news['announcements_content']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #888; text-align: center;">現在、お知らせはありません。</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="side-panel">
            <div class="ranking-title">
                <span>👑 人気ランキング</span>
                <a href="student_ranking.php" class="ranking-more-link">詳細へ</a>
            </div>
            
            <?php if (!empty($ranking_list)): ?>
                <ul class="ranking-list">
                <?php 
                $r = 1;
                foreach ($ranking_list as $book): 
                    $rank_css = '';
                    if ($r === 1) $rank_css = 'rank-1';
                    elseif ($r === 2) $rank_css = 'rank-2';
                    elseif ($r === 3) $rank_css = 'rank-3';
                ?>
                    <li class="ranking-item <?php echo $rank_css; ?>">
                        <span class="rank-num"><?php echo $r; ?></span>
                        <span class="rank-book-title"><?php echo h($book['title']); ?></span>
                        <span class="rank-count"><?php echo $book['rental_count']; ?>回</span>
                    </li>
                <?php $r++; endforeach; ?>
                </ul>
            <?php else: ?>
                <p style="font-size:0.9em; color:#888; text-align:center;">まだデータがありません。</p>
            <?php endif; ?>
        </div>

    </div>

    <div id="rankModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeRankModal()">&times;</span>
            <h3 class="modal-title">🏆 読破王ランク一覧</h3>
            <table class="rank-table">
                <tr>
                    <th>レベル</th>
                    <th>ランク名</th>
                    <th>必要な読了数</th>
                </tr>
                <tr>
                    <td>MAX</td>
                    <td class="badge-lv-max">マスター</td>
                    <td>50冊以上</td>
                </tr>
                <tr>
                    <td>Lv.5</td>
                    <td class="badge-lv-5">エキスパート</td>
                    <td>30冊以上</td>
                </tr>
                <tr>
                    <td>Lv.4</td>
                    <td class="badge-lv-4">スペシャリスト</td>
                    <td>20冊以上</td>
                </tr>
                <tr>
                    <td>Lv.3</td>
                    <td class="badge-lv-3">アドバンスド</td>
                    <td>10冊以上</td>
                </tr>
                <tr>
                    <td>Lv.2</td>
                    <td class="badge-lv-2">スタンダード</td>
                    <td>5冊以上</td>
                </tr>
                <tr>
                    <td>Lv.1</td>
                    <td class="badge-lv-1">ゲスト</td>
                    <td>0冊〜</td>
                </tr>
            </table>
            <p style="text-align:center; font-size:12px; color:#666; margin-top:15px;">
                ※返却済みの本がカウントされます。
            </p>
        </div>
    </div>
    
    <script>
        function confirmLogout() {
            if (confirm("ログアウトしますか？")) {
                document.getElementById('logout_form').submit();
            }
        }

        // モーダル操作用スクリプト
        var modal = document.getElementById("rankModal");

        function openRankModal() {
            modal.style.display = "block";
        }

        function closeRankModal() {
            modal.style.display = "none";
        }

        // モーダルの外側をクリックしたら閉じる
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>