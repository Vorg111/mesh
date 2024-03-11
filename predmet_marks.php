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
        if (!isset($_GET['pid'])) {
            $message = "❓ Выбери предмет по которому хочешь получить список оценок:";
            $keyboard = [
                "inline_keyboard" => [
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
            $j = 0;
            foreach ($result['subjects'] as $i) {
                if (sizeof($keyboard['inline_keyboard'][$j]) > 1) {
                    if ($_GET['animation']) editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
                    array_push($keyboard['inline_keyboard'], []);
                    $j++;
                }
                array_push($keyboard['inline_keyboard'][$j], ["text" => file_get_contents("https://vorg.site/meshdnevnik_bot/getlessonemoji.php?n=".urlencode($i['name'])).$i['name'], "callback_data" => "mr.predmet.".$i['id']."."]);
            }
            if (sizeof($keyboard['inline_keyboard'][$j]) > 1) {
                if ($_GET['animation']) editMessage($userId, $_GET['mid'], $message, json_encode($keyboard));
                array_push($keyboard['inline_keyboard'], []);
                $j++;
            }
            array_push($keyboard['inline_keyboard'][$j], ["text" => "🕢 По дням", "callback_data" => "mr.days."]);
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
        
        if (isset($user -> avg_marks)) {
            $result2 = json_decode($user -> avg_marks, true);
        }
        else {
            $url = "https://dnevnik.mos.ru/core/api/academic_years?only_current_year=true";
                
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $year = json_decode(curl_exec($ch), true)[0]['id'];
        
        
        
            $url = "https://dnevnik.mos.ru/reports/api/progress/json?academic_year_id=$year&student_profile_id=".$user -> client_id;
                
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Auth-Token:".$user -> token,
                "X-Mes-Subsystem:familyweb"
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result2 = json_decode(curl_exec($ch), true);
        }
        
        $message = file_get_contents("https://vorg.site/meshdnevnik_bot/getlessonemoji.php?n=".urlencode($name))." Ваши оценки по $name:\n\n";
        
        $count = 0;
        foreach ($result2 as $i) {
            if ($i['subject_name'] == $name) {
                foreach ($i['periods'] as $j) {
                    $message .= "🟢 ".$j['name']." ___(".$j['avg_five'].")___:\n***";
                    $start = false;
                    foreach ($j['marks'] as $m) {
                        if ($start) $message .= ", ";
                        $message .= $m['values'][0]['five'].$littles_numbers[$m['weight']];
                        $start = true;
                    }
                    $message .= "***\n";
                }
                break;
            }
        }
        $keyboard = [
            'inline_keyboard' => [
                [["text" => "🔘 Другой предмет", "callback_data" => "mr.predmetss."], ],
                [["text" => "🕑 По дням", "callback_data" => "mr.days."], ["text" => "🔘 Средние баллы", "callback_data" => "mr.avg."]],
            ],
        ];
        
        editMessage($userId,$_GET['mid'], $message, json_encode($keyboard));
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