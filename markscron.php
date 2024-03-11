<?php 
ob_start();
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
        
    
    setChannelDesc("@meshdnevnik_channel", "Оффициальный канал бота @meshdnevnik_bot . Здесь вы будете видеть все новости бота, которым пользуются уже ".(R::count("meshbotusers") + R::count("meshbotparents"))." человек!");
    
    
    
    $users = R::find('meshbotusers');
    
    
    $times_new = 0;
    
    foreach ($users as $user) {
        if ($times_new > 50) break;
        if ($user -> upd_time > date("H") * 60 * 60 + date("i") * 60 + date("s")) {
            $user -> upd_time = date("H") * 60 * 60 + date("i") * 60 + date("s");
        }
        if ($user -> upd_time + 60 * 60 < date("H") * 60 * 60 + date("i") * 60 + date("s")) {
            file_get_contents("https://vorg.site/meshdnevnik_bot/userupdate.php?id=".$user -> id);
            $times_new++;
        }
    }
    foreach ($users as $user) {
        if ($times_new > 50) break;
        if ($user -> upd_time > date("H") * 60 * 60 + date("i") * 60 + date("s")) {
            $user -> upd_time = date("H") * 60 * 60 + date("i") * 60 + date("s");
        }
        if ($user -> upd_time + 60 * 60 < date("H") * 60 * 60 + date("i") * 60 + date("s")) {
            file_get_contents("https://vorg.site/meshdnevnik_bot/userupdate.php?id=".$user -> id);
            $times_new++;
        }
    }
    
    $users = R::find('meshbotparents', "marksnotifications = ?", array(1));
        
    $date_r = date("Y-m-d");
    
    
    foreach ($users as $user) {
        if ($user -> marksnotifications) {
            $url = "https://dnevnik.mos.ru/core/api/student_profiles";
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Auth-Token:".$user -> token,
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result222 = json_decode(curl_exec($ch), true);
                
            if (str_contains($result222['message'], "Предыдущая сессия работы в ЭЖД завершена. Войдите в ЭЖД заново")) {
                continue;
            }
            foreach ($result222 as $iii) {
                $url = "https://dnevnik.mos.ru/core/api/marks?student_profile_id=".$iii['id']."&created_at_from=".$date_r."&created_at_to=".$date_r;
                        
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, false);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Auth-Token:".$user -> token,
                    "X-Mes-Subsystem:familyweb"
                ));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result2 = json_decode(curl_exec($ch), true);
                
                
                
                
            
                $url = "https://dnevnik.mos.ru/core/api/student_profiles/".$iii['id']."?with_subjects=true";
                
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, false);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Auth-Token:".$user -> token,
                ));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result = json_decode(curl_exec($ch), true);
                
                if ($user -> upd_notifications > date("H") * 60 * 60 + date("i") * 60 + date("s")) {
                    $user -> upd_notifications = 0;
                }
                
                
                $message = "🔔 ".$iii['user_name']." - новые оценки:\n\n";
                foreach ($result2 as $i) {
                    if (strval(date('H', strtotime($i['created_at'])) * 60 * 60 + (date('i', strtotime($i['created_at'])) * 60)) >= $user -> upd_notifications) {
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
                }
                
                $userId = $user -> tg_id;
                //if ($message != "🔔 Вам выставлены новые оценки:\n\n") sendMessage("652001276", $message." ".$user -> upd_notifications, null);
                if ($message != "🔔 ".$iii['user_name']." - новые оценки:\n\n") sendMessage($userId, $message, null);
            }
            $user -> upd_notifications = date("H") * 60 * 60 + date("i") * 60 + date("s");
            R::store($user);
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
    
    function setChannelDesc($chat, $desc) {
        $data = [
            'chat_id' => $chat,
            'description' => $desc,
        ];
        file_get_contents('https://api.telegram.org/bot' . $GLOBALS['token'] . '/setChatDescription?' . http_build_query($data));
    }
    // Отправляем приветственное сообщение и клавиатуру
    // Обрабатываем сообщения
?>