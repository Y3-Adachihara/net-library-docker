<?php    



    // 2. 学生マスタ
    $student_master = [
        //1
        ['school_id' => 1, 'grade' => 6, 'class' => 'A', 'number' => 1, 'family_name' => '山田', 'first_name' => '太郎', 'password' => '1234'],
        //2
        ['school_id' => 1, 'grade' => 5, 'class' => 'B', 'number' => 3, 'family_name' => '佐藤', 'first_name' => '花子', 'password' => 'abcd'],
        //3
        ['school_id' => 2, 'grade' => 6, 'class' => '1', 'number' => 10, 'family_name' => '鈴木', 'first_name' => '一郎', 'password' => 'pass123'],
        //4
        ['school_id' => 3, 'grade' => 4, 'class' => '2', 'number' => 15, 'family_name' => '田中', 'first_name' => '次郎', 'password' => 'xyz789'],
        //5
        ['school_id' => 4, 'grade' => 5, 'class' => '1', 'number' => 8, 'family_name' => '高橋', 'first_name' => '愛', 'password' => 'qwerty'],
        //6
        ['school_id' => 5, 'grade' => 3, 'class' => "1", 'number' => 20, 'family_name' => "伊藤", 'first_name' => "健太", 'password' => 'zxcvbn'],
        //7
        ['school_id' => 6, 'grade' => 6, 'class' => "2", 'number' => 5, 'family_name' => "渡辺", 'first_name' => "美咲", 'password' => 'asdfgh'],
        //8
        ['school_id' => 7, 'grade' => 6, 'class' => "1", 'number' => 12, 'family_name' => "山本", 'first_name' => "図書無", 'password' => '0000'],
        //9
        ['school_id' => 8, 'grade' => 5, 'class' => "1", 'number' => 7, 'family_name' => "中村", 'first_name' => "遠方", 'password' => '1111'],
        //10
        ['school_id' => 9, 'grade' => 2, 'class' => "A", 'number' => 1, 'family_name' => "小林", 'first_name' => "中学", 'password' => 'password'],
        //11
        ['school_id' => 10, 'grade' => 1, 'class' => "1", 'number' => 30, 'family_name' => "吉田", 'first_name' => "新一", 'password' => '45gds'],
        //12
        ['school_id' => 9, 'grade' => 3, 'class' => "B", 'number' => 22, 'family_name' => "加藤", 'first_name' => "学", 'password' => 'lmno56'],
        //13
        ['school_id' => 10, 'grade' => 3, 'class' => "2", 'number' => 14, 'family_name' => "佐々木", 'first_name' => "受験", 'password' => 'abc123'],
        //14
        ['school_id' => 1, 'grade' => 6, 'class' => "A", 'number' => 2, 'family_name' => "松本", 'first_name' => "潤", 'password' => 'def456'],
        //15
        ['school_id' => 9, 'grade' => 2, 'class' => "A", 'number' => 2, 'family_name' => "井上", 'first_name' => "真央", 'password' => 'ghi789']
    ];




    /* 
    --|-①自校にある本を借りる場合--|-その本が予約されていない①→二件登録済
  |			      　|
　|			　　　　|-その本がすでに別の生徒によって予約されている--|---最後に自校の生徒によって予約②→予約データ1件、貸出データ2件登録済み
　|									      　|
  |									　　　　|---最後に他校の生徒によって予約③→
  |
　|
  |
  |
  |-②他校にある本を借りる場合--|---その本が予約されていない④
				|
				|---その本がすでに別の生徒によって予約されている--|---最後に自校の生徒によって予約⑤
										  |
  										  |---最後に他校の生徒によって予約⑥
    */





    $lending_list = [
    /* 自校の本を借りる場合 */

        //1.予約されていない場合（ストレート貸出）
        ['student_id' => 1, 'book_id' => '913000501', 'lending_date' => '2025-09-10 12:30:00', 'return_date' => '2025-09-15 12:25:00'],
        ['student_id' => 7, 'book_id' => '913001802', 'lending_date' => '2025-09-12 12:20:00', 'return_date' => '2025-09-17 12:30:00'],

        /* 予約されている */
            /* ↳その本がすでに別の生徒によって予約されている */
                /* 2.自校の生徒によって予約されている */
                ['student_id' => 14, 'book_id' => '913001701', 'lending_date' => '2025-09-18 12:30:00', 'return_date' => '2025-09-25 12:25:00'],
                ['student_id' => 2, 'book_id' => '913001701', 'lending_date' => '2025-09-26 12:30:00', 'return_date' => '2025-10-02 12:25:00'],

                /* 3.   他校の生徒によって予約されている */
                ['student_id' => 6, 'book_id' => '913000402', 'lending_date' => '2025-09-29 12:30:00', 'return_date' => '2025-10-06 12:25:00'],
                ['student_id' => 13, 'book_id' => '913000402', 'lending_date' => '2025-10-07 12:30:00', 'return_date' => '2025-10-13 12:25:00'],
                

    /* 他校の本を借りる場合 */




    ];

    //予約リスト
    $reservation_list = [
        //2.で付随する予約処理(予約が受け取られた＝貸出された際に予約状態が1=>3へ更新され、updated_atも貸出日時となる)
        /* ・予約された際に生成されたレコードのイメージ↓
        ['student_id' => 2, 'book_id' => '913001701', 'status_id' => 1, 'reservation_date' => '2025-09-19 10:00:00']
        　　
            ・予約した学生が受け取った後(貸し出し処理がなされた後)はこうなる↓　
        */
        ['student_id' => 2, 'book_id' => '913001701', 'status_id' => 3, 'reservation_date' => '2025-09-19 10:00:00', 'updated_at' => '2025-09-26 12:30:01'],


        // 3.で付随する予約処理
        //10中から5小へ取り寄せ
        ['student_id' => 6, 'book_id' => '913000402', 'status_id' => 3, 'reservation_date' => '2025-09-28 10:00:00', 'updated_at' => '2025-09-29 12:30:00'],
        //5小から10中へ取り寄せ
        ['student_id' => 13, 'book_id' => '913000402', 'status_id' => 3, 'reservation_date' => '2025-09-29 10:00:00'],

    ];

    //配送リスト
    $delivery_list = [

        //3.で付随する配送処理（10中→5小）
        // 3-1.学校側で運搬する本をまとめた後、配送待ちの書籍に対応した、このレコードが生成される→ ['from_school_id' => 10, 'to_school_id' => 5, 'delivery_type' => 1, 'delivery_status' => 1, 'book_id' => '913000402'],
        // 3-2.配送員がまとめられた本を受け取り、運送用画面で本が配送中にされたとき、delivery_statusが2に変更され、delivery_dateとupdated_atに配送日時がセットされる → ['from_school_id' => 10, 'to_school_id' => 5, 'delivery_type' => 1, 'delivery_status' => 2, 'book_id' => '913000402', 'delivery_date' => '2025-09-29 10:00:00', 'updated_at' => '2025-09-29 10:00:00'],

        // 3-3.配送が完了して、もう変わらないレコード↓
        ['from_school_id' => 10, 'to_school_id' => 5, 'delivery_type' => 1, 'delivery_status' => 3, 'book_id' => '913000402', 'delivery_date' => '2025-09-29 10:00:00', 'arrival_date' => '2025-09-29 10:40:00', 'updated_at' => '2025-09-29 10:40:00'],

        // 3-4. 3-1~3と同じような要領で、復路のレコードも最終的にこのようになる↓
        ['from_school_id' => 5, 'to_school_id' => 10, 'delivery_type' => 2, 'delivery_status' => 3, 'book_id' => '913000402', 'delivery_date' => '2025-10-07 10:00:00', 'arrival_date' => '2025-10-07 11:00:00', 'updated_at' => '2025-10-07 10:00:00'],
    ];


    // 5. 貸出リスト (日付を2025年9月以降にシフト)
    // 貸出期間は7日間に設定
    $lending_list = [
        // --- 過去の貸出（返却済み） ---
        ['student_id' => 1, 'book_id' => '913000501', 'lending_date' => '2025-09-10 10:00:00', 'return_date' => '2025-09-17 12:30:00'],
        ['student_id' => 2, 'book_id' => '913000702', 'lending_date' => '2025-09-11 15:00:00', 'return_date' => '2025-09-18 09:00:00'],
        ['student_id' => 3, 'book_id' => '913000201', 'lending_date' => '2025-09-15 11:00:00', 'return_date' => '2025-09-22 11:35:56'],
        ['student_id' => 4, 'book_id' => '913000101', 'lending_date' => '2025-09-20 13:00:00', 'return_date' => '2025-09-27 10:00:00'],
        ['student_id' => 8, 'book_id' => '933000201', 'lending_date' => '2025-10-01 10:00:00', 'return_date' => '2025-10-08 16:00:00'],
        ['student_id' => 10, 'book_id' => '913000401', 'lending_date' => '2025-10-05 12:00:00', 'return_date' => '2025-10-12 12:00:00'],
        ['student_id' => 11, 'book_id' => '913001502', 'lending_date' => '2025-10-10 14:00:00', 'return_date' => '2025-10-17 09:30:00'],
        ['student_id' => 7, 'book_id' => '913001202', 'lending_date' => '2025-10-15 11:30:00', 'return_date' => '2025-10-22 15:00:00'],
        ['student_id' => 14, 'book_id' => '913000801', 'lending_date' => '2025-10-20 10:00:00', 'return_date' => '2025-10-27 10:00:00'],
        ['student_id' => 5, 'book_id' => '913000601', 'lending_date' => '2025-10-25 15:30:00', 'return_date' => '2025-11-01 09:00:00'],

        // --- 現在貸出中 ---
        // 返却期限(return_date)はNULL
        ['student_id' => 1, 'book_id' => '913001902', 'lending_date' => '2026-01-10 10:00:00', 'return_date' => null], //学問のすゝめ
        ['student_id' => 2, 'book_id' => '913001602', 'lending_date' => '2026-01-12 12:00:00', 'return_date' => null],
        ['student_id' => 3, 'book_id' => '953000102', 'lending_date' => '2026-01-13 14:00:00', 'return_date' => null],
        ['student_id' => 9, 'book_id' => '913000101', 'lending_date' => '2026-01-14 11:00:00', 'return_date' => null],
        ['student_id' => 12, 'book_id' => '913000402', 'lending_date' => '2026-01-15 09:00:00', 'return_date' => null],
        ['student_id' => 13, 'book_id' => '913003002', 'lending_date' => '2026-01-16 16:00:00', 'return_date' => null],
        ['student_id' => 6, 'book_id' => '913000301', 'lending_date' => '2026-01-16 16:30:00', 'return_date' => null],
        ['student_id' => 14, 'book_id' => '913001802', 'lending_date' => '2026-01-17 10:00:00', 'return_date' => null],
        ['student_id' => 15, 'book_id' => '913000802', 'lending_date' => '2026-01-17 12:30:00', 'return_date' => null],
        
        // 延滞中 (2025年12月貸出)
        ['student_id' => 10, 'book_id' => '943000102', 'lending_date' => '2025-12-20 10:00:00', 'return_date' => null]
    ];

    // 6. 予約リスト
    // status_id は新しいテーブル定義で rv_status_id になります
    $reservation_list = [
        // 過去分
        ['student_id' => 8, 'book_id' => '933000201', 'status_id' => 3, 'reservation_date' => '2025-09-28 10:00:00'],
        ['student_id' => 9, 'book_id' => '913000101', 'status_id' => 3, 'reservation_date' => '2026-01-05 11:00:00'],
        ['student_id' => 14, 'book_id' => '913001802', 'status_id' => 3, 'reservation_date' => '2026-01-10 10:00:00'],
        ['student_id' => 3, 'book_id' => '913000501', 'status_id' => 2, 'reservation_date' => '2025-09-12 09:00:00'], // キャンセル

        // 現在進行中 (2026年1月)
        ['student_id' => 4, 'book_id' => '913000101', 'status_id' => 1, 'reservation_date' => '2026-01-15 10:00:00'],
        ['student_id' => 6, 'book_id' => '913000301', 'status_id' => 1, 'reservation_date' => '2026-01-16 12:00:00'],
        ['student_id' => 3, 'book_id' => '913000301', 'status_id' => 1, 'reservation_date' => '2026-01-17 09:00:00'],
        
        // 配送中・受取待ち系
        ['student_id' => 8, 'book_id' => '913001101', 'status_id' => 1, 'reservation_date' => '2026-01-19 08:30:00'], // 配送中
        ['student_id' => 9, 'book_id' => '913000701', 'status_id' => 1, 'reservation_date' => '2026-01-18 14:00:00'], // 配送中
        ['student_id' => 1, 'book_id' => '913002601', 'status_id' => 1, 'reservation_date' => '2026-01-10 10:00:00'], // 受取待ち
        ['student_id' => 13, 'book_id' => '933000102', 'status_id' => 1, 'reservation_date' => '2026-01-13 11:00:00'], // 受取待ち
        
        // 貸出待ち
        ['student_id' => 14, 'book_id' => '913002002', 'status_id' => 1, 'reservation_date' => '2026-01-19 10:00:00'],
        ['student_id' => 15, 'book_id' => '933000202', 'status_id' => 1, 'reservation_date' => '2026-01-19 11:00:00']
    ];

    // 7. 配送リスト
    $delivery_list = [
        // 過去
        ['from_school_id' => 1, 'to_school_id' => 7, 'delivery_type' => 1, 'delivery_status' => 3, 'book_id' => '933000201', 'delivery_date' => '2025-09-28 10:00:00', 'arrival_date' => '2025-09-28 15:00:00'],
        ['from_school_id' => 7, 'to_school_id' => 1, 'delivery_type' => 2, 'delivery_status' => 3, 'book_id' => '933000201', 'delivery_date' => '2025-10-08 16:00:00', 'arrival_date' => '2025-10-09 09:00:00'],
        
        // 又貸しリレー
        ['from_school_id' => 2, 'to_school_id' => 4, 'delivery_type' => 1, 'delivery_status' => 3, 'book_id' => '913001202', 'delivery_date' => '2025-10-01 10:00:00', 'arrival_date' => '2025-10-01 14:00:00'],
        ['from_school_id' => 4, 'to_school_id' => 6, 'delivery_type' => 1, 'delivery_status' => 3, 'book_id' => '913001202', 'delivery_date' => '2025-10-14 11:00:00', 'arrival_date' => '2025-10-14 15:00:00'],
        ['from_school_id' => 6, 'to_school_id' => 2, 'delivery_type' => 2, 'delivery_status' => 3, 'book_id' => '913001202', 'delivery_date' => '2025-10-22 15:00:00', 'arrival_date' => '2025-10-23 10:00:00'],

        // 現在貸出中の配送済み
        ['from_school_id' => 3, 'to_school_id' => 8, 'delivery_type' => 1, 'delivery_status' => 3, 'book_id' => '913000101', 'delivery_date' => '2026-01-05 09:00:00', 'arrival_date' => '2026-01-05 13:00:00'],
        ['from_school_id' => 10, 'to_school_id' => 9, 'delivery_type' => 1, 'delivery_status' => 3, 'book_id' => '913000402', 'delivery_date' => '2026-01-15 09:00:00', 'arrival_date' => '2026-01-15 11:00:00'],
        ['from_school_id' => 4, 'to_school_id' => 1, 'delivery_type' => 1, 'delivery_status' => 3, 'book_id' => '913001802', 'delivery_date' => '2026-01-16 14:00:00', 'arrival_date' => '2026-01-17 09:00:00'],
        ['from_school_id' => 2, 'to_school_id' => 9, 'delivery_type' => 1, 'delivery_status' => 3, 'book_id' => '943000102', 'delivery_date' => '2025-12-19 10:00:00', 'arrival_date' => '2025-12-19 14:00:00'], // 延滞中の本

        // 進行中
        ['from_school_id' => 1, 'to_school_id' => 7, 'delivery_type' => 1, 'delivery_status' => 2, 'book_id' => '913001101', 'delivery_date' => '2026-01-19 08:30:00', 'arrival_date' => null], // 配送中
        ['from_school_id' => 2, 'to_school_id' => 8, 'delivery_type' => 1, 'delivery_status' => 2, 'book_id' => '913000701', 'delivery_date' => '2026-01-18 14:00:00', 'arrival_date' => null], // 配送中
        ['from_school_id' => 9, 'to_school_id' => 10, 'delivery_type' => 1, 'delivery_status' => 3, 'book_id' => '933000102', 'delivery_date' => '2026-01-13 11:00:00', 'arrival_date' => '2026-01-13 15:00:00'], // 到着済み受取待ち

        // 配送待ち
        ['from_school_id' => 1, 'to_school_id' => 2, 'delivery_type' => 1, 'delivery_status' => 1, 'book_id' => '913002002', 'delivery_date' => '2026-01-19 10:00:00', 'arrival_date' => null]
    ];

    // 8. 書籍マスタ (updated_atは後で計算)
    // registered_at は全データ共通で 2025-04-01 に設定 (活動開始前)
    $book_master = [
        ['book_id' => '913000101', 'school_id' => 3, 'title' => 'こころ', 'author_name' => '夏目漱石', 'author_kana' => 'ナツメソウセキ', 'publisher' => '岩波書店', 'publication_year' => '1990-01-01', 'status_id' => 2, 'position' => 8],
        ['book_id' => '913000102', 'school_id' => 9, 'title' => 'こころ', 'author_name' => '夏目漱石', 'author_kana' => 'ナツメソウセキ', 'publisher' => '新潮社', 'publication_year' => '1995-05-01', 'status_id' => 1, 'position' => 9],
        ['book_id' => '913000201', 'school_id' => 2, 'title' => '人間失格', 'author_name' => '太宰治', 'author_kana' => 'ダザイオサム', 'publisher' => '新潮社', 'publication_year' => '1992-04-01', 'status_id' => 1, 'position' => 2],
        ['book_id' => '913000202', 'school_id' => 4, 'title' => '人間失格', 'author_name' => '太宰治', 'author_kana' => 'ダザイオサム', 'publisher' => '角川書店', 'publication_year' => '2000-12-01', 'status_id' => 1, 'position' => 4],
        ['book_id' => '913000301', 'school_id' => 5, 'title' => '羅生門', 'author_name' => '芥川龍之介', 'author_kana' => 'アクタガワリュウノスケ', 'publisher' => '岩波書店', 'publication_year' => '1998-03-01', 'status_id' => 2, 'position' => 5],
        ['book_id' => '913000302', 'school_id' => 6, 'title' => '羅生門', 'author_name' => '芥川龍之介', 'author_kana' => 'アクタガワリュウノスケ', 'publisher' => '新潮社', 'publication_year' => '2005-08-01', 'status_id' => 1, 'position' => 6],
        ['book_id' => '913000401', 'school_id' => 9, 'title' => '銀河鉄道の夜', 'author_name' => '宮沢賢治', 'author_kana' => 'ミヤザワケンジ', 'publisher' => '角川書店', 'publication_year' => '1996-07-07', 'status_id' => 1, 'position' => 9],
        ['book_id' => '913000402', 'school_id' => 10, 'title' => '銀河鉄道の夜', 'author_name' => '宮沢賢治', 'author_kana' => 'ミヤザワケンジ', 'publisher' => '新潮社', 'publication_year' => '2001-11-01', 'status_id' => 1, 'position' => 10],
        ['book_id' => '913000501', 'school_id' => 1, 'title' => '坊っちゃん', 'author_name' => '夏目漱石', 'author_kana' => 'ナツメソウセキ', 'publisher' => '新潮社', 'publication_year' => '1989-02-01', 'status_id' => 1, 'position' => 1],
        ['book_id' => '913000502', 'school_id' => 6, 'title' => '坊っちゃん', 'author_name' => '夏目漱石', 'author_kana' => 'ナツメソウセキ', 'publisher' => '岩波書店', 'publication_year' => '2010-04-01', 'status_id' => 1, 'position' => 6],
        ['book_id' => '913000601', 'school_id' => 4, 'title' => '走れメロス', 'author_name' => '太宰治', 'author_kana' => 'ダザイオサム', 'publisher' => '新潮社', 'publication_year' => '1993-05-15', 'status_id' => 1, 'position' => 4],
        ['book_id' => '913000701', 'school_id' => 2, 'title' => '雪国', 'author_name' => '川端康成', 'author_kana' => 'カワバタヤスナリ', 'publisher' => '新潮社', 'publication_year' => '1960-01-01', 'status_id' => 6, 'position' => 0],
        ['book_id' => '913000702', 'school_id' => 3, 'title' => '雪国', 'author_name' => '川端康成', 'author_kana' => 'カワバタヤスナリ', 'publisher' => '岩波書店', 'publication_year' => '1980-01-01', 'status_id' => 1, 'position' => 3],
        ['book_id' => '913000801', 'school_id' => 1, 'title' => '伊豆の踊子', 'author_name' => '川端康成', 'author_kana' => 'カワバタヤスナリ', 'publisher' => '新潮社', 'publication_year' => '1991-03-01', 'status_id' => 1, 'position' => 1],
        ['book_id' => '913000802', 'school_id' => 9, 'title' => '伊豆の踊子', 'author_name' => '川端康成', 'author_kana' => 'カワバタヤスナリ', 'publisher' => '角川書店', 'publication_year' => '2005-06-01', 'status_id' => 2, 'position' => 9],
        ['book_id' => '913000901', 'school_id' => 10, 'title' => '金閣寺', 'author_name' => '三島由紀夫', 'author_kana' => 'ミシマユキオ', 'publisher' => '新潮社', 'publication_year' => '1970-01-01', 'status_id' => 1, 'position' => 10],
        ['book_id' => '913001001', 'school_id' => 3, 'title' => '吾輩は猫である', 'author_name' => '夏目漱石', 'author_kana' => 'ナツメソウセキ', 'publisher' => '岩波書店', 'publication_year' => '1988-10-01', 'status_id' => 1, 'position' => 3],
        ['book_id' => '913001002', 'school_id' => 5, 'title' => '吾輩は猫である', 'author_name' => '夏目漱石', 'author_kana' => 'ナツメソウセキ', 'publisher' => '講談社', 'publication_year' => '2000-01-01', 'status_id' => 1, 'position' => 5],
        ['book_id' => '913001101', 'school_id' => 1, 'title' => '風の又三郎', 'author_name' => '宮沢賢治', 'author_kana' => 'ミヤザワケンジ', 'publisher' => '岩波書店', 'publication_year' => '1995-11-01', 'status_id' => 6, 'position' => 0],
        ['book_id' => '913001201', 'school_id' => 6, 'title' => '蜘蛛の糸', 'author_name' => '芥川龍之介', 'author_kana' => 'アクタガワリュウノスケ', 'publisher' => '新潮社', 'publication_year' => '1999-01-01', 'status_id' => 1, 'position' => 6],
        ['book_id' => '913001202', 'school_id' => 4, 'title' => '蜘蛛の糸', 'author_name' => '芥川龍之介', 'author_kana' => 'アクタガワリュウノスケ', 'publisher' => '角川書店', 'publication_year' => '2008-01-01', 'status_id' => 1, 'position' => 4],
        ['book_id' => '913001301', 'school_id' => 2, 'title' => '斜陽', 'author_name' => '太宰治', 'author_kana' => 'ダザイオサム', 'publisher' => '新潮社', 'publication_year' => '1980-05-01', 'status_id' => 1, 'position' => 2],
        ['book_id' => '913001302', 'school_id' => 4, 'title' => '斜陽', 'author_name' => '太宰治', 'author_kana' => 'ダザイオサム', 'publisher' => '岩波書店', 'publication_year' => '1990-06-01', 'status_id' => 1, 'position' => 4],
        ['book_id' => '913001401', 'school_id' => 9, 'title' => '細雪', 'author_name' => '谷崎潤一郎', 'author_kana' => 'タニザキジュンイチロウ', 'publisher' => '中公文庫', 'publication_year' => '1985-01-01', 'status_id' => 1, 'position' => 9],
        ['book_id' => '913001501', 'school_id' => 5, 'title' => '山月記', 'author_name' => '中島敦', 'author_kana' => 'ナカジマアツシ', 'publisher' => '岩波書店', 'publication_year' => '1994-02-01', 'status_id' => 1, 'position' => 5],
        ['book_id' => '913001502', 'school_id' => 10, 'title' => '山月記', 'author_name' => '中島敦', 'author_kana' => 'ナカジマアツシ', 'publisher' => '新潮社', 'publication_year' => '2003-07-01', 'status_id' => 1, 'position' => 10],
        ['book_id' => '913001601', 'school_id' => 3, 'title' => '高瀬舟', 'author_name' => '森鴎外', 'author_kana' => 'モリオウガイ', 'publisher' => '岩波書店', 'publication_year' => '1982-01-01', 'status_id' => 1, 'position' => 3],
        ['book_id' => '913001602', 'school_id' => 1, 'title' => '高瀬舟', 'author_name' => '森鴎外', 'author_kana' => 'モリオウガイ', 'publisher' => '新潮社', 'publication_year' => '1995-10-01', 'status_id' => 2, 'position' => 1],
        ['book_id' => '913001701', 'school_id' => 1, 'title' => '舞姫', 'author_name' => '森鴎外', 'author_kana' => 'モリオウガイ', 'publisher' => '岩波書店', 'publication_year' => '1988-04-01', 'status_id' => 1, 'position' => 1],
        ['book_id' => '913001702', 'school_id' => 5, 'title' => '舞姫', 'author_name' => '森鴎外', 'author_kana' => 'モリオウガイ', 'publisher' => '新潮社', 'publication_year' => '2001-01-01', 'status_id' => 1, 'position' => 5],
        ['book_id' => '913001801', 'school_id' => 4, 'title' => '夜明け前', 'author_name' => '島崎藤村', 'author_kana' => 'シマザキトウソン', 'publisher' => '岩波書店', 'publication_year' => '1975-01-01', 'status_id' => 1, 'position' => 4],
        ['book_id' => '913001802', 'school_id' => 6, 'title' => '夜明け前', 'author_name' => '島崎藤村', 'author_kana' => 'シマザキトウソン', 'publisher' => '新潮社', 'publication_year' => '1990-01-01', 'status_id' => 2, 'position' => 1],
        ['book_id' => '913001901', 'school_id' => 3, 'title' => '学問のすゝめ', 'author_name' => '福沢諭吉', 'author_kana' => 'フクザワユキチ', 'publisher' => '岩波書店', 'publication_year' => '1980-01-01', 'status_id' => 1, 'position' => 3],
        ['book_id' => '913001902', 'school_id' => 1, 'title' => '学問のすゝめ', 'author_name' => '福沢諭吉', 'author_kana' => 'フクザワユキチ', 'publisher' => '講談社', 'publication_year' => '2005-01-01', 'status_id' => 2, 'position' => 1],
        ['book_id' => '913002001', 'school_id' => 1, 'title' => '武士道', 'author_name' => '新渡戸稲造', 'author_kana' => 'ニトベイナゾウ', 'publisher' => '岩波書店', 'publication_year' => '1990-01-01', 'status_id' => 1, 'position' => 1],
        ['book_id' => '913002002', 'school_id' => 2, 'title' => '武士道', 'author_name' => '新渡戸稲造', 'author_kana' => 'ニトベイナゾウ', 'publisher' => '講談社', 'publication_year' => '2000-01-01', 'status_id' => 1, 'position' => 2],
        ['book_id' => '913002301', 'school_id' => 1, 'title' => '東海道中膝栗毛', 'author_name' => '十返舎一九', 'author_kana' => 'ジッペンシャイック', 'publisher' => '岩波書店', 'publication_year' => '1985-01-01', 'status_id' => 1, 'position' => 1],
        ['book_id' => '913002302', 'school_id' => 6, 'title' => '東海道中膝栗毛', 'author_name' => '十返舎一九', 'author_kana' => 'ジッペンシャイック', 'publisher' => '新潮社', 'publication_year' => '1995-01-01', 'status_id' => 1, 'position' => 6],
        ['book_id' => '913003001', 'school_id' => 10, 'title' => 'コンビニ人間', 'author_name' => '村田沙耶香', 'author_kana' => 'ムラタサヤカ', 'publisher' => '文藝春秋', 'publication_year' => '2016-07-01', 'status_id' => 1, 'position' => 10],
        ['book_id' => '913003002', 'school_id' => 10, 'title' => 'コンビニ人間', 'author_name' => '村田沙耶香', 'author_kana' => 'ムラタサヤカ', 'publisher' => '文藝春秋', 'publication_year' => '2016-07-01', 'status_id' => 2, 'position' => 10],
        ['book_id' => '913002601', 'school_id' => 1, 'title' => '1Q84', 'author_name' => '村上春樹', 'author_kana' => 'ムラカミハルキ', 'publisher' => '新潮社', 'publication_year' => '2009-05-01', 'status_id' => 4, 'position' => 1],
        ['book_id' => '933000201', 'school_id' => 1, 'title' => 'ハリー・ポッターと賢者の石', 'author_name' => 'J.K.ローリング', 'author_kana' => 'ローリング', 'publisher' => '静山社', 'publication_year' => '1999-12-01', 'status_id' => 1, 'position' => 1],
        ['book_id' => '933000202', 'school_id' => 6, 'title' => 'ハリー・ポッターと賢者の石', 'author_name' => 'J.K.ローリング', 'author_kana' => 'ローリング', 'publisher' => '静山社', 'publication_year' => '1999-12-01', 'status_id' => 1, 'position' => 6],
        ['book_id' => '953000101', 'school_id' => 2, 'title' => '星の王子さま', 'author_name' => 'サン=テグジュペリ', 'author_kana' => 'サンテグジュペリ', 'publisher' => '岩波書店', 'publication_year' => '2000-01-01', 'status_id' => 1, 'position' => 2],
        ['book_id' => '953000102', 'school_id' => 2, 'title' => '星の王子さま', 'author_name' => 'サン=テグジュペリ', 'author_kana' => 'サンテグジュペリ', 'publisher' => '新潮社', 'publication_year' => '2006-01-01', 'status_id' => 2, 'position' => 2],
        ['book_id' => '943000101', 'school_id' => 9, 'title' => '変身', 'author_name' => 'カフカ', 'author_kana' => 'カフカ', 'publisher' => '新潮社', 'publication_year' => '1952-01-01', 'status_id' => 1, 'position' => 9],
        ['book_id' => '943000102', 'school_id' => 2, 'title' => '変身', 'author_name' => 'カフカ', 'author_kana' => 'カフカ', 'publisher' => '岩波書店', 'publication_year' => '1960-01-01', 'status_id' => 2, 'position' => 9],
        ['book_id' => '933000102', 'school_id' => 9, 'title' => '老人と海', 'author_name' => 'ヘミングウェイ', 'author_kana' => 'ヘミングウェイ', 'publisher' => '新潮社', 'publication_year' => '1966-01-01', 'status_id' => 4, 'position' => 10],
        ['book_id' => '933000301', 'school_id' => 3, 'title' => '赤毛のアン', 'author_name' => 'モンゴメリ', 'author_kana' => 'モンゴメリ', 'publisher' => '新潮社', 'publication_year' => '1955-01-01', 'status_id' => 1, 'position' => 3],
    ];
    
    
    
    
    
    
    
    
    
    
    
    
    /*
    $book_master = [
        ['book_id' => '913000101', 'school_id' => 3, 'title' => 'こころ', 'author_name' => '夏目 漱石', 'author_kana' => 'ナツメ ソウセキ', 'publisher' => '岩波文庫', 'publication_year' => '2000-01-01', 'status_id' => 1],
        ['book_id' => '913000102', 'school_id' => 9, 'title' => 'こころ', 'author_name' => '夏目 漱石', 'author_kana' => 'ナツメ ソウセキ', 'publisher' => '岩波文庫', 'publication_year' => '2005-04-10', 'status_id' => 1],
        ['book_id' => '913000201', 'school_id' => 2, 'title' => '人間失格', 'author_name' => '太宰 治', 'author_kana' => 'ダザイ オサム', 'publisher' => '新潮文庫', 'publication_year' => '1998-05-20', 'status_id' => 1],
        ['book_id' => '913000301', 'school_id' => 5, 'title' => '羅生門', 'author_name' => '芥川 龍之介', 'author_kana' => 'アクタガワ リュウノスケ', 'publisher' => '角川文庫', 'publication_year' => '2010-11-15', 'status_id' => 1],
        ['book_id' => '913000302', 'school_id' => 2, 'title' => '羅生門', 'author_name' => '芥川 龍之介', 'author_kana' => 'アクタガワ リュウノスケ', 'publisher' => '角川文庫', 'publication_year' => '2012-03-01', 'status_id' => 1],
        ['book_id' => '913000401', 'school_id' => 9, 'title' => '銀河鉄道の夜', 'author_name' => '宮沢 賢治', 'author_kana' => 'ミヤザワ ケンジ', 'publisher' => '新潮文庫', 'publication_year' => '2008-08-08', 'status_id' => 1],
        ['book_id' => '913000402', 'school_id' => 10, 'title' => '銀河鉄道の夜', 'author_name' => '宮沢 賢治', 'author_kana' => 'ミヤザワ ケンジ', 'publisher' => '新潮文庫', 'publication_year' => '2001-12-25', 'status_id' => 1],
        ['book_id' => '913000501', 'school_id' => 1, 'title' => '坊っちゃん', 'author_name' => '夏目 漱石', 'author_kana' => 'ナツメ ソウセキ', 'publisher' => '岩波文庫', 'publication_year' => '1995-02-14', 'status_id' => 1],
        ['book_id' => '913000502', 'school_id' => 6, 'title' => '坊っちゃん', 'author_name' => '夏目 漱石', 'author_kana' => 'ナツメ ソウセキ', 'publisher' => '岩波文庫', 'publication_year' => '2015-06-30', 'status_id' => 1],
        ['book_id' => '913000601', 'school_id' => 4, 'title' => '走れメロス', 'author_name' => '太宰 治', 'author_kana' => 'ダザイ オサム', 'publisher' => '新潮文庫', 'publication_year' => '2018-09-10', 'status_id' => 1],
        ['book_id' => '913000701', 'school_id' => 2, 'title' => '雪国', 'author_name' => '川端 康成', 'author_kana' => 'カワバタ ヤスナリ', 'publisher' => '新潮文庫', 'publication_year' => '1980-01-01', 'status_id' => 1],
        ['book_id' => '913000702', 'school_id' => 1, 'title' => '雪国', 'author_name' => '川端 康成', 'author_kana' => 'カワバタ ヤスナリ', 'publisher' => '新潮文庫', 'publication_year' => '2020-07-07', 'status_id' => 1],
        ['book_id' => '913000801', 'school_id' => 1, 'title' => '伊豆の踊子', 'author_name' => '川端 康成', 'author_kana' => 'カワバタ ヤスナリ', 'publisher' => '新潮文庫', 'publication_year' => '2003-05-05', 'status_id' => 1],
        ['book_id' => '913000802', 'school_id' => 9, 'title' => '伊豆の踊子', 'author_name' => '川端 康成', 'author_kana' => 'カワバタ ヤスナリ', 'publisher' => '新潮文庫', 'publication_year' => '1999-09-09', 'status_id' => 1],
        ['book_id' => '913000901', 'school_id' => 10, 'title' => '金閣寺', 'author_name' => '三島 由紀夫', 'author_kana' => 'ミシマ ユキオ', 'publisher' => '新潮文庫', 'publication_year' => '2005-10-10', 'status_id' => 1],
        ['book_id' => '913001001', 'school_id' => 3, 'title' => '吾輩は猫である', 'author_name' => '夏目 漱石', 'author_kana' => 'ナツメ ソウセキ', 'publisher' => '岩波文庫', 'publication_year' => '2001-01-01', 'status_id' => 1],
        ['book_id' => '913001002', 'school_id' => 5, 'title' => '吾輩は猫である', 'author_name' => '夏目 漱石', 'author_kana' => 'ナツメ ソウセキ', 'publisher' => '岩波文庫', 'publication_year' => '2016-04-20', 'status_id' => 1],
        ['book_id' => '913001101', 'school_id' => 1, 'title' => '風の又三郎', 'author_name' => '宮沢 賢治', 'author_kana' => 'ミヤザワ ケンジ', 'publisher' => '角川文庫', 'publication_year' => '2011-11-11', 'status_id' => 1],
        ['book_id' => '913001201', 'school_id' => 6, 'title' => '蜘蛛の糸', 'author_name' => '芥川 龍之介', 'author_kana' => 'アクタガワ リュウノスケ', 'publisher' => '新潮文庫', 'publication_year' => '2014-02-28', 'status_id' => 1],
        ['book_id' => '913001202', 'school_id' => 2, 'title' => '蜘蛛の糸', 'author_name' => '芥川 龍之介', 'author_kana' => 'アクタガワ リュウノスケ', 'publisher' => '新潮文庫', 'publication_year' => '2019-12-01', 'status_id' => 1],
        ['book_id' => '913001301', 'school_id' => 2, 'title' => '斜陽', 'author_name' => '太宰 治', 'author_kana' => 'ダザイ オサム', 'publisher' => '新潮文庫', 'publication_year' => '2000-06-15', 'status_id' => 1],
        ['book_id' => '913001302', 'school_id' => 4, 'title' => '斜陽', 'author_name' => '太宰 治', 'author_kana' => 'ダザイ オサム', 'publisher' => '新潮文庫', 'publication_year' => '2018-01-20', 'status_id' => 1],
        ['book_id' => '913001401', 'school_id' => 9, 'title' => '細雪', 'author_name' => '谷崎 潤一郎', 'author_kana' => 'タニザキ ジュンイチロウ', 'publisher' => '中公文庫', 'publication_year' => '1990-10-10', 'status_id' => 1],
        ['book_id' => '913001501', 'school_id' => 5, 'title' => '山月記', 'author_name' => '中島 敦', 'author_kana' => 'ナカジマ アツシ', 'publisher' => '岩波文庫', 'publication_year' => '2003-12-01', 'status_id' => 1],
        ['book_id' => '913001502', 'school_id' => 10, 'title' => '山月記', 'author_name' => '中島 敦', 'author_kana' => 'ナカジマ アツシ', 'publisher' => '岩波文庫', 'publication_year' => '2017-05-05', 'status_id' => 1],
        ['book_id' => '913001601', 'school_id' => 3, 'title' => '高瀬舟', 'author_name' => '森 鴎外', 'author_kana' => 'モリ オウガイ', 'publisher' => '新潮文庫', 'publication_year' => '1995-08-20', 'status_id' => 1],
        ['book_id' => '913001602', 'school_id' => 1, 'title' => '高瀬舟', 'author_name' => '森 鴎外', 'author_kana' => 'モリ オウガイ', 'publisher' => '新潮文庫', 'publication_year' => '2010-09-09', 'status_id' => 1],
        ['book_id' => '913001701', 'school_id' => 1, 'title' => '舞姫', 'author_name' => '森 鴎外', 'author_kana' => 'モリ オウガイ', 'publisher' => '岩波文庫', 'publication_year' => '1988-03-30', 'status_id' => 1],
        ['book_id' => '913001702', 'school_id' => 2, 'title' => '舞姫', 'author_name' => '森 鴎外', 'author_kana' => 'モリ オウガイ', 'publisher' => '岩波文庫', 'publication_year' => '2015-02-14', 'status_id' => 1],
        ['book_id' => '913001801', 'school_id' => 4, 'title' => '夜明け前', 'author_name' => '島崎 藤村', 'author_kana' => 'シマザキ トウソン', 'publisher' => '岩波文庫', 'publication_year' => '1992-11-11', 'status_id' => 1],
        ['book_id' => '913001802', 'school_id' => 6, 'title' => '夜明け前', 'author_name' => '島崎 藤村', 'author_kana' => 'シマザキ トウソン', 'publisher' => '岩波文庫', 'publication_year' => '2008-07-25', 'status_id' => 1],
        ['book_id' => '913001901', 'school_id' => 2, 'title' => '暗夜行路', 'author_name' => '志賀 直哉', 'author_kana' => 'シガ ナオヤ', 'publisher' => '新潮文庫', 'publication_year' => '2004-04-01', 'status_id' => 1],
        ['book_id' => '913002001', 'school_id' => 9, 'title' => '浮雲', 'author_name' => '二葉亭 四迷', 'author_kana' => 'フタバテイ シメイ', 'publisher' => '岩波文庫', 'publication_year' => '1997-02-25', 'status_id' => 1],
        ['book_id' => '913002002', 'school_id' => 10, 'title' => '浮雲', 'author_name' => '二葉亭 四迷', 'author_kana' => 'フタバテイ シメイ', 'publisher' => '岩波文庫', 'publication_year' => '2012-08-30', 'status_id' => 1],
        ['book_id' => '913002101', 'school_id' => 5, 'title' => '金色夜叉', 'author_name' => '尾崎 紅葉', 'author_kana' => 'オザキ コウヨウ', 'publisher' => '新潮文庫', 'publication_year' => '2001-12-01', 'status_id' => 1],
        ['book_id' => '159000101', 'school_id' => 3, 'title' => '学問のすゝめ', 'author_name' => '福沢 諭吉', 'author_kana' => 'フクザワ ユキチ', 'publisher' => '岩波文庫', 'publication_year' => '1999-01-01', 'status_id' => 1],
        ['book_id' => '159000102', 'school_id' => 1, 'title' => '学問のすゝめ', 'author_name' => '福沢 諭吉', 'author_kana' => 'フクザワ ユキチ', 'publisher' => '岩波文庫', 'publication_year' => '2015-04-01', 'status_id' => 1],
        ['book_id' => '156000101', 'school_id' => 1, 'title' => '武士道', 'author_name' => '新渡戸 稲造', 'author_kana' => 'ニトベ イナゾウ', 'publisher' => '岩波文庫', 'publication_year' => '2000-05-05', 'status_id' => 1],
        ['book_id' => '156000102', 'school_id' => 2, 'title' => '武士道', 'author_name' => '新渡戸 稲造', 'author_kana' => 'ニトベ イナゾウ', 'publisher' => '岩波文庫', 'publication_year' => '2018-11-20', 'status_id' => 1],
        ['book_id' => '914000101', 'school_id' => 6, 'title' => '代表的日本人', 'author_name' => '内村 鑑三', 'author_kana' => 'ウチムラ カンゾウ', 'publisher' => '岩波文庫', 'publication_year' => '1995-07-15', 'status_id' => 1],
        ['book_id' => '914000102', 'school_id' => 9, 'title' => '代表的日本人', 'author_name' => '内村 鑑三', 'author_kana' => 'ウチムラ カンゾウ', 'publisher' => '岩波文庫', 'publication_year' => '2011-03-11', 'status_id' => 1],
        ['book_id' => '913002201', 'school_id' => 2, 'title' => '源氏物語', 'author_name' => '紫式部', 'author_kana' => 'ムラサキシキブ', 'publisher' => '角川ソフィア文庫', 'publication_year' => '2000-10-01', 'status_id' => 1],
        ['book_id' => '913002202', 'school_id' => 4, 'title' => '源氏物語', 'author_name' => '紫式部', 'author_kana' => 'ムラサキシキブ', 'publisher' => '角川ソフィア文庫', 'publication_year' => '2019-01-01', 'status_id' => 1],
        ['book_id' => '914000201', 'school_id' => 10, 'title' => '枕草子', 'author_name' => '清少納言', 'author_kana' => 'セイショウナゴン', 'publisher' => '角川ソフィア文庫', 'publication_year' => '2002-06-20', 'status_id' => 1],
        ['book_id' => '914000301', 'school_id' => 5, 'title' => '徒然草', 'author_name' => '兼好法師', 'author_kana' => 'ケンコウホウシ', 'publisher' => '角川ソフィア文庫', 'publication_year' => '2005-09-15', 'status_id' => 1],
        ['book_id' => '914000302', 'school_id' => 1, 'title' => '徒然草', 'author_name' => '兼好法師', 'author_kana' => 'ケンコウホウシ', 'publisher' => '角川ソフィア文庫', 'publication_year' => '2016-02-11', 'status_id' => 1],
        ['book_id' => '915000101', 'school_id' => 3, 'title' => '奥の細道', 'author_name' => '松尾 芭蕉', 'author_kana' => 'マツオ バショウ', 'publisher' => '岩波文庫', 'publication_year' => '1998-08-08', 'status_id' => 1],
        ['book_id' => '915000102', 'school_id' => 2, 'title' => '奥の細道', 'author_name' => '松尾 芭蕉', 'author_kana' => 'マツオ バショウ', 'publisher' => '岩波文庫', 'publication_year' => '2014-04-20', 'status_id' => 1],
        ['book_id' => '913002301', 'school_id' => 1, 'title' => '東海道中膝栗毛', 'author_name' => '十返舎 一九', 'author_kana' => 'ジッペンシャ イック', 'publisher' => '岩波文庫', 'publication_year' => '1999-05-25', 'status_id' => 1],
        ['book_id' => '913002302', 'school_id' => 6, 'title' => '東海道中膝栗毛', 'author_name' => '十返舎 一九', 'author_kana' => 'ジッペンシャ イック', 'publisher' => '岩波文庫', 'publication_year' => '2010-10-10', 'status_id' => 1],
        ['book_id' => '933000101', 'school_id' => 4, 'title' => '老人と海', 'author_name' => 'ヘミングウェイ', 'author_kana' => 'ヘミングウェイ', 'publisher' => '新潮文庫', 'publication_year' => '2003-07-01', 'status_id' => 1],
        ['book_id' => '933000102', 'school_id' => 9, 'title' => '老人と海', 'author_name' => 'ヘミングウェイ', 'author_kana' => 'ヘミングウェイ', 'publisher' => '新潮文庫', 'publication_year' => '2015-12-01', 'status_id' => 1],
        ['book_id' => '953000101', 'school_id' => 3, 'title' => '星の王子さま', 'author_name' => 'サン＝テグジュペリ', 'author_kana' => 'サン テグジュペリ', 'publisher' => '岩波書店', 'publication_year' => '2000-11-15', 'status_id' => 1],
        ['book_id' => '953000102', 'school_id' => 2, 'title' => '星の王子さま', 'author_name' => 'サン＝テグジュペリ', 'author_kana' => 'サン テグジュペリ', 'publisher' => '岩波書店', 'publication_year' => '2017-03-25', 'status_id' => 1],
        ['book_id' => '933000201', 'school_id' => 1, 'title' => 'ハリー・ポッターと賢者の石', 'author_name' => 'J.K.ローリング', 'author_kana' => 'ジェイケイ ローリング', 'publisher' => '静山社', 'publication_year' => '1999-12-01', 'status_id' => 1],
        ['book_id' => '933000202', 'school_id' => 6, 'title' => 'ハリー・ポッターと賢者の石', 'author_name' => 'J.K.ローリング', 'author_kana' => 'ジェイケイ ローリング', 'publisher' => '静山社', 'publication_year' => '1999-12-01', 'status_id' => 1],
        ['book_id' => '933000301', 'school_id' => 2, 'title' => 'シャーロック・ホームズの冒険', 'author_name' => 'コナン・ドイル', 'author_kana' => 'コナン ドイル', 'publisher' => '新潮文庫', 'publication_year' => '2005-01-20', 'status_id' => 1],
        ['book_id' => '933000302', 'school_id' => 1, 'title' => 'シャーロック・ホームズの冒険', 'author_name' => 'コナン・ドイル', 'author_kana' => 'コナン ドイル', 'publisher' => '新潮文庫', 'publication_year' => '2018-06-15', 'status_id' => 1],
        ['book_id' => '933000401', 'school_id' => 9, 'title' => 'アリス・イン・ワンダーランド', 'author_name' => 'ルイス・キャロル', 'author_kana' => 'ルイス キャロル', 'publisher' => '角川文庫', 'publication_year' => '2010-04-10', 'status_id' => 1],
        ['book_id' => '933000501', 'school_id' => 5, 'title' => 'トム・ソーヤーの冒険', 'author_name' => 'マーク・トウェイン', 'author_kana' => 'マーク トウェイン', 'publisher' => '岩波少年文庫', 'publication_year' => '2002-08-25', 'status_id' => 1],
        ['book_id' => '933000502', 'school_id' => 10, 'title' => 'トム・ソーヤーの冒険', 'author_name' => 'マーク・トウェイン', 'author_kana' => 'マーク トウェイン', 'publisher' => '岩波少年文庫', 'publication_year' => '2014-05-05', 'status_id' => 1],
        ['book_id' => '943000101', 'school_id' => 3, 'title' => '変身', 'author_name' => 'カフカ', 'author_kana' => 'カフカ', 'publisher' => '新潮文庫', 'publication_year' => '2006-02-28', 'status_id' => 1],
        ['book_id' => '943000102', 'school_id' => 2, 'title' => '変身', 'author_name' => 'カフカ', 'author_kana' => 'カフカ', 'publisher' => '新潮文庫', 'publication_year' => '2019-09-01', 'status_id' => 1],
        ['book_id' => '933000601', 'school_id' => 1, 'title' => 'グレート・ギャツビー', 'author_name' => 'フィッツジェラルド', 'author_kana' => 'フィッツジェラルド', 'publisher' => '村上春樹訳', 'publication_year' => '2009-10-10', 'status_id' => 1],
        ['book_id' => '913002401', 'school_id' => 6, 'title' => 'ノルウェイの森', 'author_name' => '村上 春樹', 'author_kana' => 'ムラカミ ハルキ', 'publisher' => '講談社文庫', 'publication_year' => '2004-09-15', 'status_id' => 1],
        ['book_id' => '913002402', 'school_id' => 2, 'title' => 'ノルウェイの森', 'author_name' => '村上 春樹', 'author_kana' => 'ムラカミ ハルキ', 'publisher' => '講談社文庫', 'publication_year' => '2004-09-15', 'status_id' => 1],
        ['book_id' => '913002501', 'school_id' => 4, 'title' => '海辺のカフカ', 'author_name' => '村上 春樹', 'author_kana' => 'ムラカミ ハルキ', 'publisher' => '新潮文庫', 'publication_year' => '2005-01-01', 'status_id' => 1],
        ['book_id' => '913002502', 'school_id' => 10, 'title' => '海辺のカフカ', 'author_name' => '村上 春樹', 'author_kana' => 'ムラカミ ハルキ', 'publisher' => '新潮文庫', 'publication_year' => '2005-01-01', 'status_id' => 1],
        ['book_id' => '913002601', 'school_id' => 1, 'title' => '1Q84', 'author_name' => '村上 春樹', 'author_kana' => 'ムラカミ ハルキ', 'publisher' => '新潮文庫', 'publication_year' => '2012-05-25', 'status_id' => 1],
        ['book_id' => '913002602', 'school_id' => 9, 'title' => '1Q84', 'author_name' => '村上 春樹', 'author_kana' => 'ムラカミ ハルキ', 'publisher' => '新潮文庫', 'publication_year' => '2012-05-25', 'status_id' => 1],
        ['book_id' => '913002701', 'school_id' => 2, 'title' => '博士の愛した数式', 'author_name' => '小川 洋子', 'author_kana' => 'オガワ ヨウコ', 'publisher' => '新潮文庫', 'publication_year' => '2005-12-01', 'status_id' => 1],
        ['book_id' => '913002702', 'school_id' => 1, 'title' => '博士の愛した数式', 'author_name' => '小川 洋子', 'author_kana' => 'オガワ ヨウコ', 'publisher' => '新潮文庫', 'publication_year' => '2015-08-10', 'status_id' => 1],
        ['book_id' => '913002801', 'school_id' => 5, 'title' => '容疑者Xの献身', 'author_name' => '東野 圭吾', 'author_kana' => 'ヒガシノ ケイゴ', 'publisher' => '文春文庫', 'publication_year' => '2008-08-05', 'status_id' => 1],
        ['book_id' => '913002802', 'school_id' => 6, 'title' => '容疑者Xの献身', 'author_name' => '東野 圭吾', 'author_kana' => 'ヒガシノ ケイゴ', 'publisher' => '文春文庫', 'publication_year' => '2013-10-10', 'status_id' => 1],
        ['book_id' => '913002901', 'school_id' => 3, 'title' => '火花', 'author_name' => '又吉 直樹', 'author_kana' => 'マタヨシ ナオキ', 'publisher' => '文藝春秋', 'publication_year' => '2015-03-11', 'status_id' => 1],
        ['book_id' => '913002902', 'school_id' => 2, 'title' => '火花', 'author_name' => '又吉 直樹', 'author_kana' => 'マタヨシ ナオキ', 'publisher' => '文藝春秋', 'publication_year' => '2017-02-10', 'status_id' => 1],
        ['book_id' => '913003001', 'school_id' => 1, 'title' => 'コンビニ人間', 'author_name' => '村田 沙耶香', 'author_kana' => 'ムラタ サヤカ', 'publisher' => '文春文庫', 'publication_year' => '2018-09-01', 'status_id' => 1],
        ['book_id' => '913003002', 'school_id' => 10, 'title' => 'コンビニ人間', 'author_name' => '村田 沙耶香', 'author_kana' => 'ムラタ サヤカ', 'publisher' => '文春文庫', 'publication_year' => '2018-09-01', 'status_id' => 1],
        ['book_id' => '007000101', 'school_id' => 10, 'title' => '10才からはじめるプログラミング図鑑', 'author_name' => 'キャロル・ヴォーダマン', 'author_kana' => 'キャロル ヴォーダマン', 'publisher' => '創元社', 'publication_year' => '2019-06-20', 'status_id' => 1],
        ['book_id' => '007000201', 'school_id' => 9, 'title' => 'アルゴリズム図鑑', 'author_name' => '石田 保輝', 'author_kana' => 'イシダ ヤステキ', 'publisher' => '翔泳社', 'publication_year' => '2017-06-06', 'status_id' => 1],
        ['book_id' => '159000201', 'school_id' => 5, 'title' => '君たちはどう生きるか', 'author_name' => '吉野 源三郎', 'author_kana' => 'ヨシノ ゲンザブロウ', 'publisher' => '岩波文庫', 'publication_year' => '1982-11-16', 'status_id' => 1],
        ['book_id' => '210000101', 'school_id' => 3, 'title' => '学習まんが 日本の歴史 1', 'author_name' => '講談社編集部', 'author_kana' => 'コウダンシャヘンシュウブ', 'publisher' => '講談社', 'publication_year' => '2020-07-01', 'status_id' => 1],
        ['book_id' => '289000101', 'school_id' => 4, 'title' => 'スティーブ・ジョブズ I', 'author_name' => 'ウォルター・アイザックソン', 'author_kana' => 'ウォルター アイザックソン', 'publisher' => '講談社', 'publication_year' => '2011-10-24', 'status_id' => 1],
        ['book_id' => '289000201', 'school_id' => 1, 'title' => 'アンネの日記', 'author_name' => 'アンネ・フランク', 'author_kana' => 'アンネ フランク', 'publisher' => '文春文庫', 'publication_year' => '2003-04-10', 'status_id' => 1],
        ['book_id' => '290000101', 'school_id' => 2, 'title' => '深夜特急', 'author_name' => '沢木 耕太郎', 'author_kana' => 'サワキ コウタロウ', 'publisher' => '新潮文庫', 'publication_year' => '1994-05-01', 'status_id' => 1],
        ['book_id' => '320000101', 'school_id' => 2, 'title' => 'こども六法', 'author_name' => '山崎 聡一郎', 'author_kana' => 'ヤマサキ ソウイチロウ', 'publisher' => '弘文堂', 'publication_year' => '2019-08-20', 'status_id' => 1],
        ['book_id' => '331000101', 'school_id' => 10, 'title' => '13歳からの金融入門', 'author_name' => 'デヴィッド・ビアンキ', 'author_kana' => 'デヴィッド ビアンキ', 'publisher' => 'ダイヤモンド社', 'publication_year' => '2016-04-15', 'status_id' => 1],
        ['book_id' => '376000101', 'school_id' => 4, 'title' => '窓ぎわのトットちゃん', 'author_name' => '黒柳 徹子', 'author_kana' => 'クロヤナギ テツコ', 'publisher' => '講談社文庫', 'publication_year' => '1984-01-15', 'status_id' => 1],
        ['book_id' => '410000101', 'school_id' => 5, 'title' => '数の悪魔', 'author_name' => 'エンツェンスベルガー', 'author_kana' => 'エンツェンスベルガー', 'publisher' => '晶文社', 'publication_year' => '2000-09-01', 'status_id' => 1],
        ['book_id' => '440000101', 'school_id' => 9, 'title' => '宇宙の図鑑', 'author_name' => '国立天文台', 'author_kana' => 'コクリツテンモンダイ', 'publisher' => '小学館', 'publication_year' => '2018-03-20', 'status_id' => 1],
        ['book_id' => '451000101', 'school_id' => 1, 'title' => 'すごすぎる天気の図鑑', 'author_name' => '荒木 健太郎', 'author_kana' => 'アラキ ケンタロウ', 'publisher' => 'KADOKAWA', 'publication_year' => '2021-04-30', 'status_id' => 1],
        ['book_id' => '460000101', 'school_id' => 3, 'title' => '小学館の図鑑NEO 昆虫', 'author_name' => '小池 啓一', 'author_kana' => 'コイケ ケイイチ', 'publisher' => '小学館', 'publication_year' => '2014-06-18', 'status_id' => 1],
        ['book_id' => '460000102', 'school_id' => 6, 'title' => '小学館の図鑑NEO 昆虫', 'author_name' => '小池 啓一', 'author_kana' => 'コイケ ケイイチ', 'publisher' => '小学館', 'publication_year' => '2014-06-18', 'status_id' => 1],
        ['book_id' => '486000101', 'school_id' => 4, 'title' => 'ファーブル昆虫記', 'author_name' => 'アンリ・ファーブル', 'author_kana' => 'アンリ ファーブル', 'publisher' => '岩波文庫', 'publication_year' => '1993-02-16', 'status_id' => 1],
        ['book_id' => '596000101', 'school_id' => 2, 'title' => '魔法のケーキ', 'author_name' => '荻田 尚子', 'author_kana' => 'オギタ ヒサコ', 'publisher' => '主婦と生活社', 'publication_year' => '2015-09-11', 'status_id' => 1],
        ['book_id' => '596000201', 'school_id' => 10, 'title' => 'カレーのひみつ', 'author_name' => '水野 仁輔', 'author_kana' => 'ミズノ ジンスケ', 'publisher' => 'ほるぷ出版', 'publication_year' => '2018-07-01', 'status_id' => 1],
        ['book_id' => '620000101', 'school_id' => 5, 'title' => 'はじめての家庭菜園', 'author_name' => '藤田 智', 'author_kana' => 'フジタ サトシ', 'publisher' => '新星出版社', 'publication_year' => '2012-03-15', 'status_id' => 1],
        ['book_id' => '720000101', 'school_id' => 1, 'title' => '13歳からのアート思考', 'author_name' => '末永 幸歩', 'author_kana' => 'スエナガ ユキホ', 'publisher' => 'ダイヤモンド社', 'publication_year' => '2020-02-19', 'status_id' => 1],
        ['book_id' => '750000101', 'school_id' => 4, 'title' => '小学生の工作', 'author_name' => '日本工作協会', 'author_kana' => 'ニホンコウサクキョウカイ', 'publisher' => '成美堂出版', 'publication_year' => '2015-07-10', 'status_id' => 1],
        ['book_id' => '783000101', 'school_id' => 9, 'title' => 'スラムダンク勝利学', 'author_name' => '辻 秀一', 'author_kana' => 'ツジ シュウイチ', 'publisher' => '集英社', 'publication_year' => '2000-10-05', 'status_id' => 1],
        ['book_id' => '780000101', 'school_id' => 6, 'title' => 'オシムの言葉', 'author_name' => '木村 元彦', 'author_kana' => 'キムラ ユキヒコ', 'publisher' => '集英社文庫', 'publication_year' => '2008-04-18', 'status_id' => 1],
        ['book_id' => '813000101', 'school_id' => 3, 'title' => '新明解国語辞典', 'author_name' => '山田 忠雄', 'author_kana' => 'ヤマダ タダオ', 'publisher' => '三省堂', 'publication_year' => '2020-11-19', 'status_id' => 1],
        ['book_id' => '830000101', 'school_id' => 10, 'title' => '一億人の英文法', 'author_name' => '大西 泰斗', 'author_kana' => 'オオニシ ヒロト', 'publisher' => 'ナガセ', 'publication_year' => '2011-09-01', 'status_id' => 1],
        ['book_id' => '911000101', 'school_id' => 5, 'title' => 'サラダ記念日', 'author_name' => '俵 万智', 'author_kana' => 'タワラ マチ', 'publisher' => '河出文庫', 'publication_year' => '1989-10-04', 'status_id' => 1],
        ['book_id' => '914000401', 'school_id' => 1, 'title' => '思考の整理学', 'author_name' => '外山 滋比古', 'author_kana' => 'トヤマ シゲヒコ', 'publisher' => '筑摩書房', 'publication_year' => '1986-04-24', 'status_id' => 1],
        ['book_id' => '936000101', 'school_id' => 9, 'title' => '赤毛のアン', 'author_name' => 'モンゴメリ', 'author_kana' => 'モンゴメリ', 'publisher' => '新潮文庫', 'publication_year' => '2008-02-01', 'status_id' => 1]
    ];

    */
    
    //〇貸出履歴の仮データ（主キーはAUTO_INCREMENTなので指定しない）
    $lending_master = [
        // --- 過去の貸出（返却済み） ---
        // 1. 山田太郎(第一小)が、自校の「坊っちゃん」を借りて返した
        ['school_id' => 1, 'grade' => 6, 'class' => 'A', 'number' => 1, 'book_id' => '913000501', 'lending_date' => '2024-04-10 10:00:00', 'return_date' => '2024-04-17 12:30:00'],
        // 2. 佐藤花子(第一小)が、自校の「雪国」を借りて返した
        ['school_id' => 1, 'grade' => 5, 'class' => 'B', 'number' => 3, 'book_id' => '913000702', 'lending_date' => '2024-04-11 15:00:00', 'return_date' => '2024-04-18 09:00:00'],
        // 3. 鈴木一郎(第二小)が、自校の「人間失格」を借りて返した
        ['school_id' => 2, 'grade' => 6, 'class' => '1', 'number' => 10, 'book_id' => '913000201', 'lending_date' => '2024-04-15 11:00:00', 'return_date' => '2024-04-22 11:00:00'],
        // 4. 田中次郎(第三小)が、自校の「こころ」を借りて返した
        ['school_id' => 3, 'grade' => 4, 'class' => '2', 'number' => 15, 'book_id' => '913000101', 'lending_date' => '2024-04-20 13:00:00', 'return_date' => '2024-04-25 10:00:00'],
        // 5. 【他校貸出】図書無(第七小)が、第一小の「ハリーポッター」を取り寄せて借りて返した
        ['school_id' => 7, 'grade' => 6, 'class' => '1', 'number' => 12, 'book_id' => '933000201', 'lending_date' => '2024-05-01 10:00:00', 'return_date' => '2024-05-08 16:00:00'],
        // 6. 中学学(第九中)が、自校の「銀河鉄道の夜」を借りて返した
        ['school_id' => 9, 'grade' => 2, 'class' => 'A', 'number' => 1, 'book_id' => '913000401', 'lending_date' => '2024-05-05 12:00:00', 'return_date' => '2024-05-12 12:00:00'],
        // 7. 吉田新一(第十中)が、自校の「山月記」を借りて返した
        ['school_id' => 10, 'grade' => 1, 'class' => '1', 'number' => 30, 'book_id' => '913001502', 'lending_date' => '2024-05-10 14:00:00', 'return_date' => '2024-05-17 09:30:00'],
        // 8. 【他校貸出】渡辺美咲(第六小)が、第二小の「蜘蛛の糸」を取り寄せて借りて返した
        ['school_id' => 6, 'grade' => 6, 'class' => '2', 'number' => 5, 'book_id' => '913001202', 'lending_date' => '2024-05-15 11:30:00', 'return_date' => '2024-05-22 15:00:00'],
        // 9. 松本潤(第一小)が、自校の「伊豆の踊子」を借りて返した
        ['school_id' => 1, 'grade' => 6, 'class' => 'A', 'number' => 2, 'book_id' => '913000801', 'lending_date' => '2024-05-20 10:00:00', 'return_date' => '2024-05-21 10:00:00'],
        // 10. 高橋愛(第四小)が、自校の「走れメロス」を借りて返した
        ['school_id' => 4, 'grade' => 5, 'class' => '1', 'number' => 8, 'book_id' => '913000601', 'lending_date' => '2024-05-25 15:30:00', 'return_date' => '2024-06-01 09:00:00'],

        // --- 現在貸出中（返却日がNULL） ---
        // 11. 山田太郎(第一小)が、現在「学問のすゝめ」(自校)を借りている
        ['school_id' => 1, 'grade' => 6, 'class' => 'A', 'number' => 1, 'book_id' => '159000102', 'lending_date' => '2024-06-01 10:00:00', 'return_date' => null],
        // 12. 佐藤花子(第一小)が、現在「高瀬舟」(自校)を借りている
        ['school_id' => 1, 'grade' => 5, 'class' => 'B', 'number' => 3, 'book_id' => '913001602', 'lending_date' => '2024-06-02 12:00:00', 'return_date' => null],
        // 13. 鈴木一郎(第二小)が、現在「星の王子さま」(自校)を借りている
        ['school_id' => 2, 'grade' => 6, 'class' => '1', 'number' => 10, 'book_id' => '953000102', 'lending_date' => '2024-06-03 14:00:00', 'return_date' => null],
        // 14. 【他校貸出】中村遠方(第八小)が、第三小の「こころ」を取り寄せて借りている
        ['school_id' => 8, 'grade' => 5, 'class' => '1', 'number' => 7, 'book_id' => '913000101', 'lending_date' => '2024-06-04 11:00:00', 'return_date' => null],
        // 15. 【他校貸出】加藤学(第九中)が、第十中の「銀河鉄道の夜」を借りている（自校のが貸出中だったため）
        ['school_id' => 9, 'grade' => 3, 'class' => 'B', 'number' => 22, 'book_id' => '913000402', 'lending_date' => '2024-06-05 09:00:00', 'return_date' => null],
        // 16. 佐々木受験(第十中)が、現在「コンビニ人間」(自校)を借りている
        ['school_id' => 10, 'grade' => 3, 'class' => '2', 'number' => 14, 'book_id' => '913003002', 'lending_date' => '2024-06-06 16:00:00', 'return_date' => null],
        // 17. 伊藤健太(第五小)が、現在「羅生門」(自校)を借りている
        ['school_id' => 5, 'grade' => 3, 'class' => '1', 'number' => 20, 'book_id' => '913000301', 'lending_date' => '2024-06-06 16:30:00', 'return_date' => null],
        // 18. 【他校貸出】松本潤(第一小)が、第六小の「夜明け前」を借りている
        ['school_id' => 1, 'grade' => 6, 'class' => 'A', 'number' => 2, 'book_id' => '913001802', 'lending_date' => '2024-06-07 10:00:00', 'return_date' => null],
        // 19. 井上真央(第九中)が、現在「伊豆の踊子」(自校)を借りている
        ['school_id' => 9, 'grade' => 2, 'class' => 'A', 'number' => 2, 'book_id' => '913000802', 'lending_date' => '2024-06-07 12:30:00', 'return_date' => null],
        // 20. 【延滞中】小林中学(第九中)が、第二小の「変身」を借りて、期限を過ぎている
        ['school_id' => 9, 'grade' => 2, 'class' => 'A', 'number' => 1, 'book_id' => '943000102', 'lending_date' => '2024-05-20 10:00:00', 'return_date' => null]
    ];

    //〇予約履歴の仮データ（主キーはAUTO_INCREMENTなので指定しない）
    $reservation_master = [
        // --- パターンA: 自校の本が貸出中のため、返却を待っている ---
        // 1. 田中次郎(第三小)が、自校で貸出中の「こころ」(ID:913000101)を予約。待ち順位1番。
        ['school_id' => 3, 'grade' => 4, 'class' => '2', 'number' => 15, 'book_id' => '913000101', 'status_id' => 2, 'position' => 1, 'reservation_date' => '2024-06-05 10:00:00'],
        
        // 2. 伊藤健太(第五小)が、自校で貸出中の「吾輩は猫である」(ID:913001002)を予約。待ち順位1番。
        ['school_id' => 5, 'grade' => 3, 'class' => '1', 'number' => 20, 'book_id' => '913001002', 'status_id' => 2, 'position' => 1, 'reservation_date' => '2024-06-06 12:00:00'],
        
        // 3. 【予約の予約】鈴木一郎(第二小)が、自校の「羅生門」(ID:913000302)を予約。
        //    既に誰かが借りていて、さらに別の人が予約しているため、待ち順位2番。
        ['school_id' => 2, 'grade' => 6, 'class' => '1', 'number' => 10, 'book_id' => '913000302', 'status_id' => 2, 'position' => 2, 'reservation_date' => '2024-06-07 09:00:00'],

        // --- パターンB: 他校の本を予約（ストレート貸出の予約処理） ---
        //    ※他校の本を借りる際は必ず予約レコードが作られる仕様
        
        // 4. 【配送待ち(往路)】図書無(第七小・図書室なし)が、第一小にある「風の又三郎」(ID:913001101)を予約。
        //    まだ第一小を出発していない状態。
        ['school_id' => 7, 'grade' => 6, 'class' => '1', 'number' => 12, 'book_id' => '913001101', 'status_id' => 5, 'position' => 1, 'reservation_date' => '2024-06-08 08:30:00'],

        // 5. 【配送中(往路)】中村遠方(第八小・図書室なし)が、第二小の「雪国」(ID:913000701)を予約。
        //    運搬車に乗せられて移動中。
        ['school_id' => 8, 'grade' => 5, 'class' => '1', 'number' => 7, 'book_id' => '913000701', 'status_id' => 6, 'position' => 1, 'reservation_date' => '2024-06-07 14:00:00'],

        // 6. 【配送中(往路)】高橋愛(第四小)が、第十中の「金閣寺」(ID:913000901)を予約。移動中。
        ['school_id' => 4, 'grade' => 5, 'class' => '1', 'number' => 8, 'book_id' => '913000901', 'status_id' => 6, 'position' => 1, 'reservation_date' => '2024-06-07 15:00:00'],

        // --- パターンC: 予約した本が確保され、受取待ち ---
        
        // 7. 【予約受取待ち(自校)】山田太郎(第一小)が予約していた自校の「1Q84」(ID:913002601)が返却され、カウンターで取り置き中。
        ['school_id' => 1, 'grade' => 6, 'class' => 'A', 'number' => 1, 'book_id' => '913002601', 'status_id' => 4, 'position' => 1, 'reservation_date' => '2024-06-01 10:00:00'],

        // 8. 【配送予約受取待ち(他校)】佐々木受験(第十中)が予約した第九中の「老人と海」(ID:933000102)が、
        //    第十中に到着し、カウンターで取り置き中。
        ['school_id' => 10, 'grade' => 3, 'class' => '2', 'number' => 14, 'book_id' => '933000102', 'status_id' => 7, 'position' => 1, 'reservation_date' => '2024-06-03 11:00:00'],

        // --- パターンD: 貸出中の他校の本を予約（待ち行列＋配送） ---

        // 9. 松本潤(第一小)が、第二小にある「武士道」(ID:156000102)を予約。
        //    現在第二小の生徒が借りているため、それが返却され次第、配送処理に入る。
        ['school_id' => 1, 'grade' => 6, 'class' => 'A', 'number' => 2, 'book_id' => '156000102', 'status_id' => 2, 'position' => 1, 'reservation_date' => '2024-06-08 10:00:00'],

        // 10. 井上真央(第九中)が、第六小にある「ハリー・ポッター」(ID:933000202)を予約。
        //     現在貸出中のため待ち。
        ['school_id' => 9, 'grade' => 2, 'class' => 'A', 'number' => 2, 'book_id' => '933000202', 'status_id' => 2, 'position' => 1, 'reservation_date' => '2024-06-08 11:00:00'],

        // --- パターンE: その他（キャンセルや特殊状態の想定用） ---
        
        // 11. 【予約受取待ち】渡辺美咲(第六小)が、自校の「東海道中膝栗毛」(ID:913002302)を受け取り待ち。
        ['school_id' => 6, 'grade' => 6, 'class' => '2', 'number' => 5, 'book_id' => '913002302', 'status_id' => 4, 'position' => 1, 'reservation_date' => '2024-06-04 13:00:00'],

        // 12. 【配送待ち(復路)】加藤学(第九中)が借りていた他校(第十中)の本「銀河鉄道の夜」(ID:913000402)を予約。
        //     ※これは少し特殊なケース。本来なら貸出レコード側で処理するが、
        //     「次に予約が入っている」ことを表現するため、復路配送待ちのステータス確認用として作成。
        ['school_id' => 9, 'grade' => 3, 'class' => 'B', 'number' => 22, 'book_id' => '913000402', 'status_id' => 9, 'position' => 1, 'reservation_date' => '2024-06-08 16:00:00'],

        // 13. 【貸出中】佐藤花子(第一小)が予約を入れたが、まだ前の人が返していない。
        ['school_id' => 1, 'grade' => 5, 'class' => 'B', 'number' => 3, 'book_id' => '913001701', 'status_id' => 2, 'position' => 1, 'reservation_date' => '2024-06-08 17:00:00']
    ];
?>