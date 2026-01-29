<?php
    require_once '../db_connect.php';
    session_start();

    //CSRF対策
    //トークンが一致しないか（右）、そもそもトークンがlogin.php(ログイン画面)から送られていない（左）時
    // CSRFトークンチェック(合致しなかった場合、logout.phpにCSRFトークンを送る方法を思いつかなかったため、ここでログアウト処理)
    if(!isset($_POST['csrf_token']) || $_POST['csrf_token'] != $_SESSION['csrf_token']){

        $redirect_path = '../html/student_login.php';
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

    $grade = $_POST['school-year'];
    $class = $_POST['class'];
    $number = $_POST['number'];
    $book_id = $_POST['id-number'];

    // 司書としてログインしていなければ、ログイン画面へリダイレクト
    if (!isset($_SESSION['librarian_id'])) {
        header("Location: ../html/librarian_login.php");
        exit();
    }

    $librarian_school_id = intval($_SESSION['librarian_school_id']);

    try {
        $db = new db_connect();
        $db->connect(); //データベースへ接続

        //トランザクション開始
        $db->pdo->beginTransaction();

        // 入力された学生情報を取得
        $student_select = "SELECT * FROM student WHERE school_id = :school_id AND grade = :grade AND class = :class AND number = :number";
        $stmt = $db->pdo->prepare($student_select);
        $stmt->bindValue(':school_id', $librarian_school_id);
        $stmt->bindValue(':grade', $grade, PDO::PARAM_INT);
        $stmt->bindValue(':class', $class, PDO::PARAM_STR);
        $stmt->bindValue(':number', $number, PDO::PARAM_INT);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        // 検索条件に指定された書籍が存在するか確認
        $book_isExists = "SELECT status_id, position, school_id FROM book_stack WHERE book_id = :book_id";
        $stmt = $db->pdo->prepare($book_isExists);
        $stmt->bindValue(':book_id', $book_id, PDO::PARAM_STR);
        $stmt->execute();
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        
        $message = "";

        //入力された学生と書籍が存在した場合
        if ($student && $book) {
            
            //入力された学生の…
            $student_id = intval($student['student_id'],10);    //学生管理ID
            $student_school = intval($student['school_id'],10); //所属学校ID

            //入力された書籍の…
            $book_status = intval($book['status_id'],10);   //現在の状態（貸出可能か、それ以外か）
            $book_position = intval($book['position'],10);  //現在位置（学校ID）
            $book_owner = intval($book['school_id'],10);    //その本の所有学校


            // 貸出禁止期間に入っているか確認
            $deny_select = "SELECT start_date, end_date FROM lending_deny AS ld
                             WHERE ld.book_id = :book_id
                             AND (
                                DATE(NOW()) >= DATE(ld.start_date) 
                                AND 
                                DATE(NOW()) <= DATE(ld.end_date)
                            )";
            $stmt = $db->pdo->prepare($deny_select);
            $stmt->bindValue(':book_id', $book_id, PDO::PARAM_STR);
            $stmt->execute();
            $is_denied = $stmt->fetch(PDO::FETCH_ASSOC);

            $deny_start_now = null;
            $deny_end_now = null;
            $interval = null;

            // 表示する用↓
            $start_date = null;
            $end_date = null;
            $remain_date = null;


            // 貸出予約期間に入っている場合はしょっ引く
            if ($is_denied) {
                $db->pdo->rollback();
                $deny_start_now = date('Y年m月d日', strtotime($is_denied['start_date']));
                $deny_end_now = date('Y年m月d日', strtotime($is_denied['end_date']));

                // 貸し出せたときのメッセージで使う日付の差分を作成
                $start = new Datetime($deny_start_now);
                $end = new Datetime($deny_end_now);

                $_SESSION['lend_result_message'] = "申し訳ございません。\nこの本は現在、貸出禁止期間となります。\n期間は" . $deny_start . "から" . $deny_end . "までです。";
                header("Location:../html/貸出返却.php");
                exit();
            }

            // 貸出禁止期間に入っていなかった場合、期間に入るまでの日数を取得するための期間開始日を取得
            $interval_sql = "SELECT start_date FROM lending_deny AS ld 
                            WHERE book_id = :book_id 
                            AND DATE(NOW) < ld.start_date 
                            AND ORDER BY ld.start_date ASC 
                            LIMIT 1
                            ";
            $stmt_interval = $db->pdo->prepare($interval_sql);
            $stmt_interval->bindValue(':book_id', $book_id, PDO::PARAM_STR);
            $stmt_interval->execute();
            $denyStartAfter_row = $stmt_interval->fetch(PDO::FETCH_ASSOC);

            if ($denyStartAfter_row) {
                $deny_start_now = date('Y年m月d日', strtotime($denyStartAfter_row['start_date']));
                $start_date = new Datetime($deny_start_now);
                $today = new Datetime();

                $interval = $today->diff($start_date);
                $remain_date = $interval->days;
            }



            // 学生が現在何冊借りているかどうか確認
            $stu_lend_counts = "SELECT COUNT(*) AS count FROM lending WHERE student_id = :student_id AND return_date IS NULL";
            $stmt = $db->pdo->prepare($stu_lend_counts);
            $stmt->bindValue(":student_id", $student_id, PDO::PARAM_INT);
            $stmt->execute();
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            $lend_counts = intval($count['count']);

            // 現在借りている冊数が2冊以上なら
            if ($lend_counts >= 2) {
                $db->pdo->rollback();
                $_SESSION['lend_result_message'] = "貸出冊数が制限に達しています！";
                header("Location:../html/貸出返却.php");
                exit();
            }

            switch ($book_status) { //書籍状態からどの処理に回すか判断

                case 1: //貸出可能だった場合

                    // 借りようとしている書籍が自校所有
                    if ($student_school == $book_position && $book_owner == $librarian_school_id) {
                         
                        // もし、この段階に来ても書籍状態が1(貸出可能)であれば、書籍状態を更新
                        $update_bookStatus = "UPDATE book_stack SET status_id = :neo_status WHERE book_id = :book_id AND status_id = :old_status";
                        $stmt = $db->pdo->prepare($update_bookStatus);
                        $stmt->bindValue(':neo_status', 2, PDO::PARAM_INT);   //2:貸し出し中
                        $stmt->bindValue(':book_id', $book_id, PDO::PARAM_STR);
                        $stmt->bindValue(':old_status', 1, PDO::PARAM_INT);   //1:貸し出し可能
                        $stmt->execute();

                        // 更新処理が成功した場合だけ、貸出レコードを作成
                        if ($stmt->rowCount() == 1) {
                        
                            $inset_LendRec = "INSERT INTO lending(student_id, book_id) VALUES(:student_id, :book_id)";
                            $stmt = $db->pdo->prepare($inset_LendRec);
                            $stmt->bindValue(':student_id', $student_id, PDO::PARAM_INT);
                            $stmt->bindValue(':book_id', $book_id, PDO::PARAM_STR);
                            $stmt->execute();
                            
                            $db->pdo->commit();

                            $message = "貸し出し処理が完了しました。";
                            if (!empty($remain_date) && $remain_date <= 14) {
                                $message .= "この本は、あと" . $remain_date . "で貸出禁止期間に入ります。";
                                $message .= "\nそれまでに、必ず返却してください。";
                            }

                        } else {
                            $db->pdo->rollback();
                            $message = "ほかの人が先に借りてしまいました……";
                        }
                        break;


                    // 借りようとしている書籍が他校保有
                    } else {
                        $db->pdo->rollback();
                        $message = "他校にある本、もしくは他校所蔵の本になります。予約してください";
                        break;
                    }

                case 4:
                case 7:
                    $_SESSION['grade'] = $grade;
                    $_SESSION['class'] = $class;
                    $_SESSION['number'] = $number;
                    $_SESSION['book_id'] = $book_id;

                    $db->pdo->rollback();
                    $_SESSION['reserved_lend_message'] = "予約番号を入力してください";
                    header("Location: ../html/verify_resCode.php");
                    exit();

                case 2:
                case 3:
                case 5:
                case 6: 
                case 8:
                case 9:
                    $db->pdo->rollback();
                    // 再入力が面倒なので、セッションで引き渡し
                    $message = "この本は貸出済みです。先に返却するか、予約してください。";
                    break;
                
                default:
                    $db->pdo->rollback();
                    $message = "この本は現在、貸出できません";
            }

        } else {
            $db->pdo->rollback();
            $message = "指定された本または学生が存在しません";
        }

        // セッションにメッセージを格納
        // 予約に回すやつ以外
        $_SESSION['lend_result_message'] = $message;
        header("Location: ../html/貸出返却.php");
        exit();

    } catch (PDOException $e) {
        $db->pdo->rollback();
        echo "データベースエラー：" . $e->getMessage(); //デバッグ用。あとで消す！
        exit;
    } catch (Exception $e) {
        $db->pdo->rollback();
        echo "エラー：" . $e->getMessage(); //デバッグ用。あとで消す！
        exit;
    }
?>