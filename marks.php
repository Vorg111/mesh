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
        $keyboard = null;
        
        $date = date('d.m.Y');
        $date_r = date('Y-m-d');
        if (isset($_GET['date'])) {
            $date_r = $_GET['date'];
            $date = date('d.m.Y', strtotime($_GET['date']));
        }
        
        if (isset(json_decode($user -> marks, true)[$date_r])) {
            $result2 = json_decode($user -> marks, true)[$date_r];
        }
        else {
            $url = "https://dnevnik.mos.ru/core/api/marks?student_profile_id=".$user -> client_id."&created_at_from=".$date_r."&created_at_to=".$date_r;
                
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
        }
        
        
        $keyboard = [
            'inline_keyboard' => [
                [["text" => "🔘 Средние баллы", "callback_data" => "mr.avg."], ["text" => "🔘 По предметам", "callback_data" => "mr.predmets."]],
                [["text" => "←", "callback_data" => "mr.prevday.".$date_r."|"],],
            ],
        ];
        
        if ($date != date("d.m.Y")) array_push($keyboard['inline_keyboard'][1], ["text" => "→", "callback_data" => "mr.nextday.".$date_r."|"]);
        
        $message = "🏅 Ваши оценки за сегодня:\n\n";
        if ($date != date("d.m.Y")) $message = "🏅 Ваши оценки за ".$date.":\n\n";
        foreach ($result2 as $i) {
            foreach ($result['subjects'] as $j) {
                if (strval($j['id']) == strval($i['subject_id'])) {
                    $message .= file_get_contents("https://vorg.site/meshdnevnik_bot/getlessonemoji.php?n=".urlencode($j['name']));
                    if ($i['is_point']) {
                        $message .= $j['name'].": ***".$i['name'].$littles_numbers[$i['weight']]." с точкой до ".date('d.m.Y', strtotime($i['point_date']))."*** (".date('H:i', strtotime($i['created_at'])).")\n";
                        continue;
                    }
                    $message .= $j['name'].": ***".$i['name'].$littles_numbers[$i['weight']]."*** (".date('H:i', strtotime($i['created_at'])).")\n";
                }
            }
        }
        if ($message == ("🏅 Ваши оценки за сегодня:\n\n") || $message == ("🏅 Ваши оценки за ".$date.":\n\n")) {
            $message = "🏝 Надеюсь, ты хорошо отдохнул за ".$date.", а то тебе не ставили оценок!";
        }
        if (isset($_GET['mid'])) {
            editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
            return;
        }
        sendMessage($userId, $message, json_encode($keyboard));
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