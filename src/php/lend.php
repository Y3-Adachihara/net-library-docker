<?php
    require_once '../db_connect.php';
    session_start();

    $grade = $_POST['school-year'];
    $class = $_POST['class'];
    $number = $_POST['number'];
    $book_id = $_POST['id-number'];

    // 司書としてログインしていなければ、ログイン画面へリダイレクト
    if (!isset($_SESSION['librarian_id'])) {
        header("Location: librarian_login.php");
        exit();
    }

    //CSRF対策
    //トークンが一致しないか（右）、そもそもトークンがlogin.php(ログイン画面)から送られていない（左）時
    if (!isset($_POST["csrf_token"]) || $_POST["csrf_token"] != $_SESSION["csrf_token"]) {
        $_SESSION['error'] = "不正なリクエストです";
        header("Location: ../html/貸出返却.php");
        exit(); 
    }

    $librarian_school_id = intval($_SESSION['librarian_school_id']);

    /*
    function lending($grade, $class, $number, $book_id) {
        //現在の日付を取得
        $current_date = new DateTimeImmutable();
        $current_date_str = $current_date->format('Y-m-d H:i:s');

        $lending_sql = "INSERT INTO lending student_id, book_id, lending_date)";
    }
    */

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
        $book_isExists = "SELECT status_id, position FROM book_stack WHERE book_id = :book_id";
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

            // 学生が現在何冊借りているかどうか確認
            $stu_lend_counts = "SELECT COUNT(*) AS count FROM lending WHERE student_id = :student_id AND return_date IS NULL";
            $stmt = $db->pdo->prepare($stu_lend_counts);
            $stmt->bindValue(":student_id", $student_id, PDO::PARAM_INT);
            $stmt->execute();
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            $lend_counts = intval($count['count']);

            if ($lend_counts >= 2) {
                $db->pdo->rollback();
                $_SESSION['lend_result_message'] = "貸出冊数が制限に達しています！";
                header("Location:../html/貸出返却.php");
                exit();
            }

            
            switch ($book_status) { //書籍状態からどの処理に回すか判断

                case 1: //貸出可能だった場合

                    // 借りようとしている書籍が自校所有
                    if ($student_school == $book_position) {

                        // ログインしている司書の学校が所蔵する本かどうかチェック
                        if ($book_position != $librarian_school_id) {
                            $db->pdo->rollback();
                            $_SESSION['lend_result_message'] = "他校の本を本校の司書貸出することはできません。";
                            header("Location:../html/貸出返却.php");
                            exit();
                        }
                         
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

                        } else {
                            $db->pdo->rollback();
                            $message = "ほかの人が先に借りてしまいました……";
                        }
                        break;


                    // 借りようとしている書籍が他校保有
                    } else {

                        // 今はとりあえず、何も確認ダイアログを出さずに予約画面へリダイレクト
                        $db->pdo->rollback();

                        /* やろうとしたけど、そもそもこの場合は書籍IDを入力する前提にしない方がいい気がした。検索画面に飛ばして、そこから予約させれば
                        // 再入力が面倒なので、セッションで引き渡し
                        $_SESSION['librarian_school_id'] = $librarian_school_id;
                        $_SESSION['reservation_grade'] = $grade;
                        $_SESSION['reservation_class'] = $class;
                        $_SESSION['reservation_number'] = $number;
                        $_SESSION['reservation_book_id'] = $book_id;
                        */
                        
                        header("Location:../html/reservation.php");
                        exit();
                    
                    }

                case 4:
                case 7:
                    // 貸出を申請してきている学生が、予約を取りに来ているか、貸出しに来ているかを判断（予約機能がまだなので、作ってない）
                    break;


                case 2:
                case 3:
                case 5:
                case 6: 
                case 8:
                case 9:
                    // 今は仮で、何も確認ダイアログを出さずにリダイレクト
                    $db->pdo->rollback();
                    // 再入力が面倒なので、セッションで引き渡し
                    $_SESSION['librarian_school_id'] = $librarian_school_id;
                    $_SESSION['reservation_grade'] = $grade;
                    $_SESSION['reservation_class'] = $class;
                    $_SESSION['reservation_number'] = $number;
                    $_SESSION['reservation_book_id'] = $book_id;
                    header("Location:../html/reservation.php");
                    exit();
                
                default:
                    $db->pdo->rollback();
                    $message = "この本は現在、貸出できません";
            }

        } else {
            $db->pdo->rollback();
            $message = "指定された本または学生が存在しません";
        }

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