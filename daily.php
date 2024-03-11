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
        
    $users = R::find('meshbotusers', "dailynotifications = ?", array(1));
        
    $date_r = date("Y-m-d");
    
    foreach ($users as $user) {
        if ($user -> dailynotifications) {
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
                continue;
            }
            
        
            $url = "https://dnevnik.mos.ru/core/api/student_profiles/".$user -> client_id."?with_subjects=true";
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Auth-Token:".$user -> token,
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = json_decode(curl_exec($ch), true);
            
            $message = "🗃 Итоги дня:\n\n";
            foreach ($result2 as $i) {
                foreach ($result['subjects'] as $j) {
                    if (strval($j['id']) == strval($i['subject_id'])) {
                        $message .= file_get_contents("https://vorg.site/meshdnevnik_bot/getlessonemoji.php?n=".urlencode($j['name']));
                        $message .= $j['name'].": ***".$i['name'].$littles_numbers[$i['weight']]."*** (".date('H:i', strtotime($i['created_at'])).")\n";
                    }
                }
            }
            
            if ($message == "🗃 Итоги дня:\n\n") {
                $message = "";
            }
            
            
            
           
            
            $today = new DateTime();
            $today->modify('+1 day');
            $date = $today->format('Y-m-d');
                
            $url = "https://school.mos.ru/api/family/web/v1/schedule?student_id=".$user -> client_id."&date=".$date;
        
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Auth-Token:".$user -> token,
                "X-Mes-Subsystem:familyweb"
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result2 = json_decode(curl_exec($ch), true);
            $j = 0;
            $message_add = "\n🗓 Расписание на завтра (".$result2['summary']."):\n\n";
            if (!str_contains($message_add, "(0 уроков):")) {
                foreach ($result2['activities'] as $i) {
                    if ($i['type'] == "LESSON") {
                        $j++;
                        $i['subject_name'] = $i['lesson']['subject_name'];
                        $message_add .= file_get_contents("https://vorg.site/meshdnevnik_bot/getlessonemoji.php?n=".urlencode($i['subject_name']));
                        $message_add .= $j.". ***".$i['lesson']['subject_name']."*** (".$i['begin_time']." - ".$i['end_time'].", кб.".$i['room_number'].")\n";
                    }
                }
                $message .= $message_add;
            }
            
            
            
            if ($message != "") {
                $message .= "\n***🌛 Спокойной ночи!***";
                sendMessage($user -> tg_id, $message, null);
            }
        }
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