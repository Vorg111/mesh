<?php
    require "libs/rb.php";
    $token = '6488989522:AAGsTDXZka5WbueA5Re-gdqtHPNi36OorA8';
    function SubErase($string, $start_in, $end_in) {
        $start = strpos($string, $start_in);
        if ($start !== false) {
            $start += strlen($start_in);
            
            $end = strpos($string, $end_in, $start);
            
            if ($end !== false) {
                $substring = substr($string, $start, $end - $start);
                return $substring;
            } else {
                return "Строка ' и названием:' не найдена.";
            }
        } else {
            return "Строка 'chat id: ' не найдена.";
        }
        return $str;
    }
    
    $littles_numbers = array(
        1 => "",
        2 => "₂",
        3 => "₃",
        4 => "₄",
        5 => "₅",
        6 => "₆",
        7 => "₇",
        8 => "₈",
        9 => "₉",
        10 => "₁₀",
        );
    
    // Получите обновление от Telegram
    R::setup( 'mysql:host=localhost;dbname=u1706092_accountpp',
        'u1706092_default', '03CAL1bI8ybTqhA7' );
        
    $userId = $_GET['chid'];
        
    if (R::findOne('meshbotusers', 'tg_id = ?', array($userId))) {
        $user = R::findOne('meshbotusers', 'tg_id = ?', array($userId));
        if (isset($_GET['only'])) {
            $result = 0;
            $message = "📄 Список ваших мест в рейтингах по предметам:\n\n";
            if (isset($user -> rait_subjlist)) {
                $result = json_decode($user -> rait_subjlist, true);
            }
            else {
                $date = date("Y-m-d");
                for ($i = 1; $i < 4; $i++) {
                    $url = "https://school.mos.ru/api/ej/rating/v1/rank/subjects?date=$date&personId=".$user -> profile_id;
                
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_POST, false);
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        "X-mes-subsystem:familyweb",
                        "Auth-Token:".$user -> token,
                    ));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $result = json_decode(curl_exec($ch), true);
                    
                    if (str_contains($result['message'], "Предыдущая сессия работы в ЭЖД завершена. Войдите в ЭЖД заново")) {
                        if (isset($_GET['mid'])) {
                            deleteMessage($userId, $_GET['mid']);
                        }
                        file_get_contents("https://vorg.site/meshdnevnik_bot/relog.php?chid=".$userId);
                        return;
                    }
                    if (isset($result[0]['rank'])) break;
                    
                    $date = date("Y-m-d", strtotime("-".$i." days"));
                }
            }
            $keyboard = [
                "inline_keyboard" => [
                    [["text" => "🏆 По предметам", "callback_data" => "rt.subjects."]],
                    [["text" => "📈 Полный рейтинг", "callback_data" => "rt.full."]],
                    [["text" => "📉 Краткий рейтинг", "callback_data" => "rt.main."]],
                    [["text" => "🌍 Глобальный рейтинг", "callback_data" => "glrt.base."]],
                ],
            ];
                
            foreach($result as $i) {
                $message .= file_get_contents("https://vorg.site/meshdnevnik_bot/getlessonemoji.php?n=".urlencode($i['subjectName'])).$i['subjectName'].":  ***".$i['rank']['rankPlace']."***  ___(".number_format($i['rank']['averageMarkFive'], 2, '.', '').")___";
                if ($i['rank']['rankStatus'] == "stable") {
                   
                }
                elseif ($i['rank']['rankStatus'] == "up") {
                    $message .= " 📈 ";
                }
                else {
                    $message .= " 📉 ";
                    
                }
                $message .= "\n";
            }
            editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
            return;
        }
        if (!isset($_GET['pid'])) {
            $message = "❓ Выбери предмет по которому хочешь получить рейтинг:";
            $keyboard = [
                "inline_keyboard" => [
                    [['text' => "📄 Список мест", 'callback_data' => 'rt.only_my_subjects.']],
                    [],
                ]
            ];
            
            
            if (isset(json_decode($user -> marks, true)['subjs'])) {
                $result = json_decode(json_decode($user -> marks, true)['subjs'], true);
            }
            else {
                $url = "https://dnevnik.mos.ru/core/api/student_profiles/".$user -> client_id."?with_subjects=true";
            
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, false);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Auth-Token:".$user -> token,
                ));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result = json_decode(curl_exec($ch), true);
                    
                if (str_contains($result['message'], "Предыдущая сессия работы в ЭЖД завершена. Войдите в ЭЖД заново")) {
                    if (isset($_GET['mid'])) {
                        deleteMessage($userId, $_GET['mid']);
                    }
                    file_get_contents("https://vorg.site/meshdnevnik_bot/relog.php?chid=".$userId);
                    return;
                }
            }
            
            $j = 1;
            foreach ($result['subjects'] as $i) {
                if (sizeof($keyboard['inline_keyboard'][$j]) > 1) {
                    if ($_GET['animation']) editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
                    array_push($keyboard['inline_keyboard'], []);
                    $j++;
                }
                array_push($keyboard['inline_keyboard'][$j], ["text" => file_get_contents("https://vorg.site/meshdnevnik_bot/getlessonemoji.php?n=".urlencode($i['name'])).$i['name'], "callback_data" => "rt.subject.".$i['id']."."]);
            }
            if (sizeof($keyboard['inline_keyboard'][$j]) > 1) {
                if ($_GET['animation']) editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
                array_push($keyboard['inline_keyboard'], []);
                $j++;
            }
            array_push($keyboard['inline_keyboard'][$j], ["text" => "📉 Краткий рейтинг", "callback_data" => "rt.short."]);
            array_push($keyboard['inline_keyboard'], [["text" => "📈 Полный рейтинг", "callback_data" => "rt.full."], ["text" => "🌍 Глобальный рейтинг", "callback_data" => "glrt.base."]],);
            editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
            return;
        }
        
        if (isset(json_decode($user -> marks, true)['subjs'])) {
            $result = json_decode(json_decode($user -> marks, true)['subjs'], true);
        }
        else {
            $url = "https://dnevnik.mos.ru/core/api/student_profiles/".$user -> client_id."?with_subjects=true";
        
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Auth-Token:".$user -> token,
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = json_decode(curl_exec($ch), true);
                
            if (str_contains($result['message'], "Предыдущая сессия работы в ЭЖД завершена. Войдите в ЭЖД заново")) {
                if (isset($_GET['mid'])) {
                    deleteMessage($userId, $_GET['mid']);
                }
                file_get_contents("https://vorg.site/meshdnevnik_bot/relog.php?chid=".$userId);
                return;
            }
        }
        
        foreach ($result['subjects'] as $i) {
            if ($_GET['pid'] == $i['id']) {
                $name = $i['name'];
                break;
            }
        }
        
        
        $message = file_get_contents("https://vorg.site/meshdnevnik_bot/getlessonemoji.php?n=".urlencode($name))."Общий рейтинг по предмету ***\"".$name."\"***: \n\n";
        
        $date = date("Y-m-d");
        
        if (isset(json_decode($user -> rait_subj, true)[$_GET['pid']])) {
            $result3 = json_decode($user -> rait_subj, true)[$_GET['pid']];
        }
        else {
            $url = "https://school.mos.ru/api/ej/rating/v1/rank/class?subjectId=".$_GET['pid']."&date=".$date."&personId=".$user -> profile_id;
                
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Auth-Token:".$user -> token,
                "X-Mes-Subsystem:familyweb"
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result3 = json_decode(curl_exec($ch), true);
        }
        
        $str = "🐶 🐱 🐻 🐨 🐯 🦁 🐮 🐣 🐠 🐡 🐘 🦃 🦜 🦧 🦈 🫎 🍎 🍐 🍇 🍒 🍑 🥭 🍍 🥝 🍅";
        $smiles = explode(" ", $str);
        
        for ($i = 0; $i < sizeof($result3); $i++) {
            if ($user -> profile_id == $result3[$i]['personId']) {
                $message .= $user -> rating_smile."️ ";
                $message .= "***".$result3[$i]['rank']['rankPlace'].". ".number_format($result3[$i]['rank']['averageMarkFive'], 2, '.', '')." - Я***\n";
                continue;
            }
            $oneUser = R::findOne("meshbotusers", "profile_id = ?", array($result3[$i]['personId']));
            if ($oneUser -> rating_smile) {
                $message .= $oneUser -> rating_smile." ";
            }
            else {
                $unlogUser = R::findOne("unlogsmiles", "profile_id = ?", array($result3[$i]['personId']));
                if (!($unlogUser -> rating_smile)) {
                    $unlogUser = R::dispense('unlogsmiles');
                    $unlogUser -> rating_smile = $smiles[rand(0, sizeof($smiles) - 1)];
                    $unlogUser -> profile_id = $result3[$i]['personId'];
                    R::store($unlogUser);
                }
                $message .= $unlogUser -> rating_smile."️ ";
            }
            
            
            $message .= $result3[$i]['rank']['rankPlace'].". ".number_format($result3[$i]['rank']['averageMarkFive'], 2, '.', '')." - ";
            if ($oneUser -> rating_visible) {
                $fio = explode(" ", $oneUser -> fio);
                $message .= $fio[0]." ".$fio[1];
            }
            else {
                $message .= "Некоторая личность";
            }
            $message .= "\n";
        }
        $keyboard = [
            "inline_keyboard" => [
                [["text" => "🔘 Другой предмет", "callback_data" => "rt.subjects."]],
                [["text" => "📈 Полный рейтинг", "callback_data" => "rt.full."]],
                [["text" => "📉 Краткий рейтинг", "callback_data" => "rt.short."]],
            ],
        ];
        if (rand(1, 15) < 4) {
            $message .= "\n✨ Кастомизируй свой смайлик рядом с именем в рейтинге! Нажми на кнопку ниже и выбери другой смайл!";
            array_push($keyboard['inline_keyboard'], [["text" => "✨ Кастомизировать эмодзи рейтинга", "callback_data" => "st.emojiratingg."]]);
        }
        editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
    }
    function sendMessage($chatId, $text, $keyboard, $photo = null) {
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ];
        if ($keyboard) {
            $data['reply_markup'] = $keyboard;
        }
        if ($photo) {
            $data = [
                'chat_id' => $chatId,
                'caption' => $text,
                'photo' => $photo,
                'parse_mode' => 'Markdown',
            ];
            if ($keyboard) {
                $data['reply_markup'] = $keyboard;
            }
            file_get_contents('https://api.telegram.org/bot' . $GLOBALS['token'] . '/sendPhoto?' . http_build_query($data));
            return;
        }
        file_get_contents('https://api.telegram.org/bot' . $GLOBALS['token'] . '/sendMessage?' . http_build_query($data));
    }
    
    
    function deleteMessage($chatId, $message_id) {
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
        ];
        file_get_contents('https://api.telegram.org/bot' . $GLOBALS['token'] . '/deleteMessage?' . http_build_query($data));
    }
    
    
    function editMessage($chatId, $messageId, $text, $keyboard) {
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ];
        if ($keyboard) {
            $data['reply_markup'] = $keyboard;
        }
        file_get_contents('https://api.telegram.org/bot' . $GLOBALS['token'] . '/editMessageText?' . http_build_query($data));
    }
    // Отправляем приветственное сообщение и клавиатуру
    // Обрабатываем сообщения 
?>   