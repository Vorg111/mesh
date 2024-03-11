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
            if (str_contains($result2['message'], "Предыдущая сессия работы в ЭЖД завершена. Войдите в ЭЖД заново")) {
                if (isset($_GET['mid'])) {
                    deleteMessage($userId, $_GET['mid']);
                }
                file_get_contents("https://vorg.site/meshdnevnik_bot/relog.php?chid=".$userId);
                return;
            }
        }
        
        $message = "📊 Ваши средние баллы по предметам:\n\n";
        
        $count = 0;
        foreach ($result2 as $i) {
            $message .= file_get_contents("https://vorg.site/meshdnevnik_bot/getlessonemoji.php?n=".urlencode($i['subject_name']));
            $message .= $i['subject_name'].": ";
            if (isset($i['periods'][1]['avg_five'])) {
                if (isset($i['periods'][2]['avg_five'])) {
                    $message .= "___(".$i['periods'][0]['avg_five'].", ".$i['periods'][1]['avg_five'].")___, ***".$i['periods'][2]['avg_five'];
                    if ($i['periods'][2]['avg_five'] >= 4.9) $count++;
                }
                else {
                    $message .= "___(".$i['periods'][0]['avg_five'].")___, ***".$i['periods'][1]['avg_five'];
                    if ($i['periods'][1]['avg_five'] >= 4.9) $count++;
                }
            }
            else {
                $message .= "***".$i['periods'][0]['avg_five'];
                if ($i['periods'][0]['avg_five'] >= 4.9) $count++;
            }
            $message .= "***,\n";
        }
        $keyboard = [
            'inline_keyboard' => [
                [["text" => "🕑 По дням", "callback_data" => "mr.days."], ["text" => "🔘 По предметам", "callback_data" => "mr.predmets."]],
            ],
        ];
        
        if ($count > 1) {
            $message .= "\n🎉 Поздравляю, ". $user -> name ."! У тебя ". $count ." предметов со средним балов не ниже 4.9 в этом периоде!";
        }
        
        //$message .= "\n❔ Если вы хотите получит осписок всех оценок по определенному предмету в нынешний учебный период, то ответьте на это сообщение  названием прдемета с большой буквы. К примеру напишите ответом на это сообщение \"Математика\".";
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