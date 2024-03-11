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
    
    function getWeekDates($inputDate) {
        $translate = array(
            "Monday" => "Пн.",
            "Tuesday" => "Вт.",
            "Wednesday" => "Ср.",
            "Thursday" => "Чт.",
            "Friday" => "Пт.",
            "Saturday" => "Сб.",
            "Sunday" => "Вс.");
            
        
        
        $keyboard = [
            'inline_keyboard' => [
                [],
                [],
                [],
            ],
        ];
            
        $today = DateTime::createFromFormat('Y-m-d', date("Y-m-d"));
        
        
        for ($i = 0; $i < 3; $i++) {
            $day = date('l', strtotime($today->format('Y-m-d')));
            if ($today->format('Y-m-d') == $inputDate) {
                array_push($keyboard['inline_keyboard'][0], ["text" => "🟢 ".$translate[$day]." (".$today->format('d').")", "callback_data" => "sc.".$today->format('Y-m-d')."."]);
            }
            else {
                array_push($keyboard['inline_keyboard'][0], ["text" => $translate[$day]." (".$today->format('d').")", "callback_data" => "sc.".$today->format('Y-m-d')."."]);
            }
            $today->modify('+1 day');
        }
        
        for ($i = 0; $i < 3; $i++) {
            $day = date('l', strtotime($today->format('Y-m-d')));
            if ($today->format('Y-m-d') == $inputDate) {
                array_push($keyboard['inline_keyboard'][1], ["text" => "🟢 ".$translate[$day]." (".$today->format('d').")", "callback_data" => "sc.".$today->format('Y-m-d')."."]);
            }
            else {
                array_push($keyboard['inline_keyboard'][1], ["text" => $translate[$day]." (".$today->format('d').")", "callback_data" => "sc.".$today->format('Y-m-d')."."]);
            }
            $today->modify('+1 day');
        }
        $day = date('l', strtotime($today->format('Y-m-d')));
        if ($today->format('Y-m-d') == $inputDate) {
            array_push($keyboard['inline_keyboard'][2], ["text" => "🟢 ".$translate[$day]." (".$today->format('d').")", "callback_data" => "sc.".$today->format('Y-m-d')."."]);
        }
        else {
            array_push($keyboard['inline_keyboard'][2], ["text" => $translate[$day]." (".$today->format('d').")", "callback_data" => "sc.".$today->format('Y-m-d')."."]);
        }
        
        return $keyboard;
    }
    
    // Получите обновление от Telegram
    R::setup( 'mysql:host=localhost;dbname=u1706092_accountpp',
        'u1706092_default', '03CAL1bI8ybTqhA7' );
        
    $userId = $_GET['chid'];
        
    if (R::findOne('meshbotusers', 'tg_id = ?', array($userId))) {
        $user = R::findOne('meshbotusers', 'tg_id = ?', array($userId));
        $date = date("Y-m-d");
        if (isset($_GET['date'])) {
            $date = $_GET['date'];
        }
        if (isset(json_decode($user -> schedules, true)[$date])) {
            $result2 = json_decode(json_decode($user -> schedules, true)[$date], true);
        }
        else {
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
        }
        if (str_contains($result2['message'], "Предыдущая сессия работы в ЭЖД завершена. Войдите в ЭЖД заново")) {
            if (isset($_GET['mid'])) {
                deleteMessage($userId, $_GET['mid']);
            }
            file_get_contents("https://vorg.site/meshdnevnik_bot/relog.php?chid=".$userId);
            return;
        }
        $keyboard = getWeekDates($date);
        $message = "📆 Расписание на сегодня (".$result2['summary']."):\n\n";
        $translate = array(
            "Monday" => "понедельник",
            "Tuesday" => "вторник",
            "Wednesday" => "среду",
            "Thursday" => "четверг",
            "Friday" => "пятницу",
            "Saturday" => "субботу",
            "Sunday" => "воскресенье");
        if (isset($_GET['date']) && $_GET['date'] != date("Y-m-d")) {
            $message = "📆 Расписание на ".$translate[date('l', strtotime($_GET['date']))]." (".$result2['summary']."):\n\n";
        }
        $j = 0;
        foreach ($result2['activities'] as $i) {
            if ($i['type'] == "LESSON") {
                $j++;
                $i['subject_name'] = $i['lesson']['subject_name'];
                $message .= file_get_contents("https://vorg.site/meshdnevnik_bot/getlessonemoji.php?n=".urlencode($i['subject_name']));
                $message .= $j.". ***".$i['lesson']['subject_name']."*** (".$i['begin_time']." - ".$i['end_time'].", кб.".$i['room_number'].")\n";
            }
        }
        if (str_contains($message, "(0 уроков):")) {
            $message = "🧐 Я понимаю твою тягу к знаниям, но в ".$translate[date('l', strtotime($date))]." у тебя нет уроков в школе. Надеюсь ты не грустишь из-за этого!";
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