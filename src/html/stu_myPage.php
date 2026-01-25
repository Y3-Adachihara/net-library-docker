<?php
session_start();
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
            font-family: "Meiryo", "Hiragino Kaku Gothic ProN", sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100vh;
        }

        /* ヘッダーエリア（ログアウトボタン用） */
        .header {
            width: 100%;
            max-width: 800px;
            display: flex;
            justify-content: flex-end; /* 右寄せ */
            padding: 20px 0;
            margin-top: 20px;
        }

        /* ログアウトボタンのスタイル */
        .logout-btn {
            background-color: #fff;
            border: 1px solid #333;
            padding: 5px 15px;
            cursor: pointer;
            font-size: 14px;
        }
        .logout-btn:hover {
            background-color: #eee;
        }

        /* メインメニューのコンテナ */
        .menu-container {
            background-color: #fff;
            border: 2px solid #000;
            width: 800px;
            height: 500px;
            padding: 40px; /* パディング */
            box-sizing: border-box;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }

        /* 画面左上のタイトル */
        .page-title {
            font-size: 24px;
            font-weight: bold;
            margin: 0 0 40px 0; /* 下に余白を開ける */
            text-align: left;   /* 左寄せ */
        }

        /* ボタンが並ぶエリア */
        .button-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 50px;
        }

        /* メインの3つのボタン共通スタイル */
        .menu-btn {
            width: 30%;
            height: 100px;
            font-size: 18px;
            font-weight: bold;
            background-color: #fff;
            border: 2px solid #333;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
        }

        .menu-btn:hover {
            background-color: #f0f0f0;
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

    </style>
</head>
<body>

    <div class="header">
        <button class="logout-btn" onclick="confirmLogout()">ログアウト</button>
    </div>

    <div class="menu-container">
        
        <div class="page-title">生徒用マイページ</div>

        <div class="button-row">
            <button class="menu-btn" onclick="location.href='search.php'">
                検索する
            </button>

            <button class="menu-btn" onclick="location.href='test.php'">
                予約する
            </button>

            <button class="menu-btn" onclick="location.href='view_reservations.php'">
                予約情報参照
            </button>
        </div>

    </div>

    <script>
        /**
         * ④ ログアウト確認ポップアップ
         */
        function confirmLogout() {
            if (confirm("ログアウトしますか？")) {
                location.href = 'login.php'; 
            }
        }
    </script>
</body>
</html>