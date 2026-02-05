<?php
    require_once '../db_connect.php';
    session_start();

    if (!isset($_SESSION['deliverer_id'])) {
        // 配送員としてログインしていない場合、ログインページへリダイレクト
        $_SESSION['message'] = "配送員としてログインしてください。";
        header("Location: ../html/deliverer_login.php");
        exit();
    }

    $deliverer_id = $_SESSION['deliverer_id'];

    // CSRFトークンチェック(合致しなかった場合、logout.phpにCSRFトークンを送る方法を思いつかなかったため、ここでログアウト処理)
    if(!isset($_POST['csrf_token']) || $_POST['csrf_token'] != $_SESSION['csrf_token']){

        $redirect_path = '../html/deliverer_book_management.php';
        $error_param = "?error=nomal_alert";

        $_SESSION = [];    // セッション変数を全て解除
        
        // セッションクッキーの削除
        if(ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();  // セッションを破壊

        header("Location: " .$redirect_path .$error_param);
        exit();
    }

    $items = $_POST['update_items'] ?? [];

    if (!empty($items)) {
        try {
            $db = new db_connect();
            $db->connect();
            $pdo = $db->pdo;

            $pdo->beginTransaction(); // トランザクション開始

            // 1. 書籍状態の更新用SQL
            $sql_update_stack = "UPDATE book_stack SET status_id = :next_status WHERE book_id = :book_id";
            $stmt_stack_status = $pdo->prepare($sql_update_stack);

            // 2. ★集荷用（搬出）：新しい配送レコードを作るSQL
            // delivery_status = 2 (配送中) と仮定
            $sql_insert_delivery = "INSERT INTO delivery (book_id, deliverer_id, from_school_id, to_school_id, delivery_status, delivery_type, delivery_date) 
                                    VALUES (:book_id, :deliverer_id, :from_id, :to_id, 2, :delivery_type, NOW())";
            $stmt_insert = $pdo->prepare($sql_insert_delivery);

            // 3. ★入荷用（搬入）：既存の配送レコードを完了にするSQL
            // delivery_status = 3 (配送完了)
            // 条件：その本の「配送中(2)」のレコードを探して完了にする
            $sql_finish_delivery = "UPDATE delivery SET delivery_status = 3, arrival_date = NOW() 
                                    WHERE book_id = :book_id AND delivery_status = 2";
            $stmt_finish = $pdo->prepare($sql_finish_delivery);

            // 4.書籍位置の変更用SQL
            $sql_update_position = "UPDATE book_stack SET position = :position WHERE book_id = :book_id";
            $stmt_stack_position = $pdo->prepare($sql_update_position);


            foreach ($items as $item) {
                $book_id = $item['book_id'];
                $next_status = intval($item['next_status']);
                $from_id = $item['from_id'] ?? null;
                $to_id   = $item['to_id'] ?? null;

                // --- A. 書籍ステータスの更新（共通） ---
                $stmt_stack_status->bindValue(':next_status', $next_status, PDO::PARAM_INT);
                $stmt_stack_status->bindValue(':book_id', $book_id, PDO::PARAM_STR);
                $stmt_stack_status->execute();

                
                
                
                // --- B. 配送テーブルの操作 ---
                
                // パターン1：搬出（集荷）なら INSERT
                if ($next_status === 6 || $next_status === 9) {
                    $stmt_insert->bindValue(':book_id', $book_id, PDO::PARAM_STR);
                    $stmt_insert->bindValue(':deliverer_id', $deliverer_id, PDO::PARAM_INT);
                    $stmt_insert->bindValue(':from_id', $from_id, PDO::PARAM_INT);
                    $stmt_insert->bindValue(':to_id', $to_id, PDO::PARAM_INT);
                    
                    if ($next_status == 6) {
                        $stmt_insert->bindValue(':delivery_type', 1, PDO::PARAM_INT);   // 予約配送
                    } else {
                        $stmt_insert->bindValue(':delivery_type', 2, PDO::PARAM_INT);   // 返却配送
                    }
                    
                    $stmt_insert->execute();

                    // 書籍現在位置の変更（0は学校テーブルでは例外的に、学校コードではなく「配送中」という現在位置としている)
                    $stmt_stack_position->bindValue(':position', 0, PDO::PARAM_INT);
                    $stmt_stack_position->bindValue(':book_id', $book_id, PDO::PARAM_STR);
                    $stmt_stack_position->execute();
                }

                // パターン2：搬入（荷下ろし）なら UPDATE（完了処理）
                // ※これをやらないと、deliveryテーブル上で永遠に「配送中」になってしまいます
                if ($next_status === 10) {
                    $stmt_finish->bindValue(':book_id', $book_id, PDO::PARAM_STR);
                    $stmt_finish->execute();

                    $stmt_stack_position->bindValue(':position', $to_id, PDO::PARAM_INT);
                    $stmt_stack_position->bindValue(':book_id', $book_id, PDO::PARAM_STR);
                    $stmt_stack_position->execute();
                }
            }

            $pdo->commit(); // コミット
            $_SESSION['book_manageConfirm_message'] = "配送処理が完了しました。";

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['book_manageConfirm_message'] = "エラーが発生しました：" . $e->getMessage();
        }
    }

    // 管理画面へ戻る
    header("Location: ../html/deliverer_book_management.php");
    exit();
?>