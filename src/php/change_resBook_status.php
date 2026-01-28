<?php
    require_once '../db_connect.php';
    session_start();

    if (!isset($_SESSION['librarian_id'])) {
        // 司書としてログインしていない場合、ログインページへリダイレクト
        $_SESSION['message'] = "司書としてログインしてください。";
        header("Location: librarian_login.php");
        exit();
    }

    // CSRFトークンチェック(合致しなかった場合、logout.phpにCSRFトークンを送る方法を思いつかなかったため、ここでログアウト処理)
    if(!isset($_POST['csrf_token']) || $_POST['csrf_token'] != $_SESSION['csrf_token']){

        $redirect_path = '../html/librarian_login.php';
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

    // 更新ステータスの取得とチェック
    $allowed_status = [4,5];
    $next_status = $_POST['next_status'];

    if (!in_array($next_status, $allowed_status)) {
        $_SESSION['book_manageConfirm_message'] = "不正なステータスです。";
        header("Location: librarian_bookManage.php");
        exit();
    }

    $selected_bookIds = $_POST['book_ids'] ?? [];
    // もし↑で空だった場合はしょっぴく
    if (empty($selected_bookIds)) {
        $_SESSION['book_manageConfirm_message'] = "不正なリクエストです。";
        header("Location: librarian_bookManage.php");
        exit();
    }
    // 更新する書籍数を取得
    $book_id_count = count($selected_bookIds);

    $inClause = substr(str_repeat(',?', count($selected_bookIds)), 1);

    try {
        $db = new db_connect();
        $db->connect();

        // トランザクション開始
        $db->pdo->beginTransaction();

        // 書籍状態を取得
        $select_sql = "SELECT * FROM book_stack AS bs";
        $select_sql .= " WHERE bs.book_id IN ($inClause)";
        $select_sql .= " FOR UPDATE";
        $stmt_select = $db->pdo->prepare($select_sql);
        $stmt_select->execute($selected_bookIds);
        $update_book_ids = $stmt_select->fetchAll(PDO::FETCH_ASSOC);

        // 書籍が取得できていた場合
        if (!empty($update_book_ids)) {

            foreach ($update_book_ids as $rows) {

                // 各レコードで書籍状態を取得
                $book_status_id = intval($rows['status_id']);

                // 書籍状態が「3:予約済み」でなければしょっ引く
                if ($book_status_id != 3) {
                    $db->pdo->rollback();
                    $_SESSION['bookStatus_changeResult_message'] = "選択された本は、すでにステータスが変更されています。再度リストを確認してください";
                    header("Location: ../html/librarian_bookManage.php");
                    exit();
                }
            }
            // 書籍状態が適切だった場合のみ更新

            // ?に合わせるため、status_idとbook_idたちを一つの配列に統合
            $params = array_merge([$next_status], $selected_bookIds);

            $update_sql = "UPDATE book_stack SET status_id = ?";
            $update_sql .= " WHERE book_id IN ($inClause)";
            $stmt_update = $db->pdo->prepare($update_sql);
            $stmt_update->execute($params);

            // 更新レコード数を取得して確認
            $count = $stmt_update->rowCount();

            if ($count == $book_id_count) {
                $db->pdo->commit();

                switch ($next_status) {
                    case 4:
                        $_SESSION['bookStatus_changeResult_message'] = "本校から予約された" . $count . "冊の本の状態を「4:予約受取待ち」へ変更しました。";
                        break;
                    case 5:
                        $_SESSION['bookStatus_changeResult_message'] = "他校から予約された" . $count . "冊の本の状態を「5:配送待ち（予約配送）」へ変更しました。";
                        break;
                }
                header("Location: ../html/librarian_bookManagement.php");
                exit();

            } else if ($count > 0) {    // 選択された書籍数より少ないが、更新はされた場合
                $db->pdo->rollback();
                $_SESSION['bookStatus_changeResult_message'] = "一部の書籍が更新されませんでした。";
                header("Location: ../html/librarian_bookManagement.php");
                exit();
            } else {    // 1件も登録されなかった場合
                $db->pdo->rollback();
                $_SESSION['bookStatus_changeResult_message'] = "書籍状態の更新に失敗しました。";
                header("Location: ../html/librarian_bookManagement.php");
                exit();
            }

        // 書籍が取得できなかった場合
        } else {
            $db->pdo->rollback();
            $_SESSION['bookStatus_changeResult_message'] = "選択された書籍が存在しません";
            header("Location: ../html/librarian_bookManagement.php");
            exit();
        }
    } catch (PDOException $e) {
        $db->pdo->rollback();
        echo "データベースエラー：" . $e->getMessage(); //デバッグ用。あとで消す！
        exit;
    } catch (Exception $e) {
        $db->pdo->rollback();
        echo "エラー：" . $e->getMessage(); //デバッグ用。あとで消す！
        exit;
    } finally {
        $db->close();
    }
?>