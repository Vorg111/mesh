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
        
        $url = "https://school.mos.ru/portfolio/app/persons/".$user -> profile_id."/events/list";
        
        if (isset(json_decode($user -> olimpiads, true)["res1"]) && !isset(json_decode($user -> olimpiads, true)["res1"]["message"])) {
            $result1 = json_decode($user -> olimpiads, true)["res1"];
        }
        else {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer ".$user -> token,
                "X-Mes-Subsystem: studentportfolioweb",
                "Cookie: aupd_current_role=4:2;"
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result1 = json_decode(curl_exec($ch), true);
        }
        
        if (isset(json_decode($user -> olimpiads, true)["res2"]) && !isset(json_decode($user -> olimpiads, true)["res2"]["message"])) {
            $result2 = json_decode($user -> olimpiads, true)["res2"];
        }
        else {
            $url = "https://school.mos.ru/portfolio/app/persons/".$user -> profile_id."/rewards/list";
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer ".$user -> token,
                "X-Mes-Subsystem: studentportfolioweb",
                "Cookie: aupd_current_role=4:2;"
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result2 = json_decode(curl_exec($ch), true);
        }
        
        $message = "***🧩 Олимпиады***\n\n";
        
        $j = 0;
        $o = 1;
        if (isset($_GET['start'])) $o = $_GET['start'];
        if ($_GET['start'] == 1) unset($_GET['start']);
        $end = 15;
        if (isset($_GET['start'])) $end = min($_GET['start'] + 14, sizeof($result1['data']));
        $j = $o - 1;
        while (true) {
            if ($o == $end) {
                $o++;
                if (!isset($_GET['start'])) $message .= "***‼️ Были показаны только последние 15 олимпиад!***";
                else $message .= "***‼️ Были показаны только олимпиады ".$_GET['start']." - ".$end." ***___(Индексация начинается с последней написанной олимпиады)___***!***";
                break;
            }
            $i = $result1['data'][$o - 1];
            $message .= "🟢 ".$i['name'];
            if (isset($i['subjects'][0]['value'])) {
                $message .= " ___(".$i['subjects'][0]['value'];
                if (isset($i['stageEvent'])) $message .= ", ".$i['stageEvent'];
                $message .= ")___";
            }
            $message .= " - ";
            if (isset($i['result'])) {
                $message .= "***".$i['result'];
                if (isset($i['maxScore'])) {
                    $message .= "/".$i['maxScore'];
                }
                $message .= "***";
            }
            if ($result2['data'][$j]['entityId'] == $i['id']) {
                $message .= " ___(".$result2['data'][$j]['name'].")___";
                $j++;
            }
            $message .= "\n\n";
            $o++;
        }
        
        $keyboard = [
            "inline_keyboard" => [
            ],  
        ];
        
        if (isset($_GET['start'])) {
            array_push($keyboard['inline_keyboard'], [["text" => "← (".($_GET['start'] - 15)." - ".($_GET['start'] - 1).")", "callback_data" => "pt.ol".($_GET['start'] - 15)."."]]);
        }
        if ($end < sizeof($result1['data'])) {
            if (isset($_GET['start'])) array_push($keyboard['inline_keyboard'][0], ["text" => "(".($_GET['start'] + 15)." - ".(min(sizeof($result1['data']), $_GET['start'] + 15 + 14)).") →", "callback_data" => "pt.ol".($_GET['start'] + 15)."."]);
            else array_push($keyboard['inline_keyboard'], [["text" => "(".(16)." - ".(min(sizeof($result1['data']), 16 + 14)).") →", "callback_data" => "pt.ol".(16)."."]]);
        }
        array_push($keyboard['inline_keyboard'], [["text" => "📊 Диагностики", "callback_data" => "pt.diag."],]);
        array_push($keyboard['inline_keyboard'], [["text" => "⛷ Спорт", "callback_data" => "pt.sport."],]);
        
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
            'disable_web_page_preview' => 'True',
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
            'disable_web_page_preview' => 'True',
        ];
        if ($keyboard) {
            $data['reply_markup'] = $keyboard;
        }
        file_get_contents('https://api.telegram.org/bot' . $GLOBALS['token'] . '/editMessageText?' . http_build_query($data));
    }
    // Отправляем приветственное сообщение и клавиатуру
    // Обрабатываем сообщения
?>    