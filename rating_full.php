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
    
    // Получите обновление от Telegram
    R::setup( 'mysql:host=localhost;dbname=u1706092_accountpp',
        'u1706092_default', '03CAL1bI8ybTqhA7' );
        
    $userId = $_GET['chid'];
        
    if (R::findOne('meshbotusers', 'tg_id = ?', array($userId))) {
        $user = R::findOne('meshbotusers', 'tg_id = ?', array($userId));
        
        if (isset($user -> short_rait)) {
            $result2 = json_decode($user -> short_rait, true);
        }
        else {
            $result2 = array();
            $date = date("Y-m-d");
            for ($i = 1; $i < 4; $i++) {
                $url = "https://school.mos.ru/api/ej/rating/v1/rank/rankShort?personId=".$user -> profile_id."&beginDate=".$date."&endDate=".$date;
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, false);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Auth-Token:".$user -> token,
                    "X-Mes-Subsystem:familyweb"
                ));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result2 = json_decode(curl_exec($ch), true);
                
            if (str_contains($result2['message'], "Предыдущая сессия работы в ЭЖД завершена. Войдите в ЭЖД заново")) {
                if (isset($_GET['mid'])) {
                    deleteMessage($userId, $_GET['mid']);
                }
                file_get_contents("https://vorg.site/meshdnevnik_bot/relog.php?chid=".$userId);
                return;
            }
                if (isset($result2[0]['rankPlace'])) break;
                
                $date = date("Y-m-d", strtotime("-".$i." days"));
            }
        }
        $message = "📊 Общий рейтинг в вашем классе: \n\n";
        
        if (isset($user -> rait)) {
            $result3 = json_decode($user -> rait, true);
        }
        else {
            $url = "https://school.mos.ru/api/ej/rating/v1/rank/class?date=".$date."&personId=".$user -> profile_id;
                
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Auth-Token:".$user -> token,
                "X-Mes-Subsystem:familyweb"
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result3 = json_decode(curl_exec($ch), true);
            
            if (str_contains($result3['message'], "Предыдущая сессия работы в ЭЖД завершена. Войдите в ЭЖД заново")) {
                if (isset($_GET['mid'])) {
                    deleteMessage($userId, $_GET['mid']);
                }
                file_get_contents("https://vorg.site/meshdnevnik_bot/relog.php?chid=".$userId);
                return;
            }
        }
        
        $str = "🐶 🐱 🐻 🐨 🐯 🦁 🐮 🐣 🐠 🐡 🐘 🦃 🦜 🦧 🦈 🫎 🍎 🍐 🍇 🍒 🍑 🥭 🍍 🥝 🍅";
        $smiles = explode(" ", $str);
        
        for ($i = 0; $i < sizeof($result3); $i++) {
            if ($user -> profile_id == $result3[$i]['personId']) {
                $message .= $user -> rating_smile."️ ";
                $message .= "***".$result3[$i]['rank']['rankPlace'].". ".$result3[$i]['rank']['averageMarkFive']." - Я***\n";
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
                [["text" => "🏆 По предметам", "callback_data" => "rt.predmets."]],
                [["text" => "📉 Краткий рейтинг", "callback_data" => "rt.short."]],
                [["text" => "🌍 Глобальный рейтинг", "callback_data" => "glrt.base."]],
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