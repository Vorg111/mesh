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
        $keyboard = null;
        $date = date("Y-m-d");
        if (!isset($_GET['date'])) {
            $date = date("Y-m-d");
            $url = "https://school.mos.ru/api/family/web/v1/homeworks?from=".date("Y-m-d")."&to=".date("Y-m-d")."&student_id=".$user -> client_id;
            $message = "📄 ДЗ на сегодня:\n\n";
            $keyboard = [
                'inline_keyboard' => [
                    [["text" => "→", "callback_data" => "dz.tommorow."],],
                ],
            ];
        }
        elseif ($_GET['date'] == "next") {
            $today = new DateTime();
            $today->modify('+1 day');
            $tomorrow = $today->format('Y-m-d');
            $date = $tomorrow;
            $url = "https://school.mos.ru/api/family/web/v1/homeworks?from=".$tomorrow."&to=".$tomorrow."&student_id=".$user -> client_id;
            $message = "📄 ДЗ на завтра:\n\n";
            $keyboard = [
                'inline_keyboard' => [
                    [["text" => "←", "callback_data" => "dz.today."], ["text" => "→", "callback_data" => "dz.nextday.".$tomorrow."|"],],
                ],
            ];
        }
        else {
            $date = $_GET['date'];
            $url = "https://school.mos.ru/api/family/web/v1/homeworks?from=".$_GET['date']."&to=".$_GET['date']."&student_id=".$user -> client_id;
            $message = "📄 ДЗ на ".$_GET['date'].":\n\n";
            $keyboard = [
                'inline_keyboard' => [
                    [["text" => "←", "callback_data" => "dz.prevday.".$_GET['date']."|"], ["text" => "→", "callback_data" => "dz.nextday.".$_GET['date']."|"],],
                ],
            ];
        }
        if (isset(json_decode($user -> hometasks, true)[$date])) {
            $result2['payload'] = json_decode($user -> hometasks, true)[$date];
        }
        else {
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
        
        if ($_GET['cdz'] == 1) {
            $g_k = 0;
            foreach ($result2['payload'] as $i) {
                for ($ii = 0;; $ii++) {
                    if (isset($i['additional_materials'][$ii])) {
                        if ($i['additional_materials'][$ii]['action_name'] == "Пройти") {
                            foreach ($i['additional_materials'][$ii]['items'] as $j) {
                                $g_k++;
                                if ($g_k == $_GET['g_k']) {
                                    $object = $j['urls'][2]['url'];
                                    if (str_contains($object, "?")) {
                                        $object .= "&";
                                    }
                                    else {
                                        $object .= "?";
                                    }
                                    $object .= "role=student&utm_campaign=1&utm_medium=lesson&utm_source=familyw";
                                    $message = "📲 Для того чтобы выполнить цдз, перейдите по [ссылке]($object) или просто нажмите на кнопку ниже!";
                                    $keyboard = [
                                        "inline_keyboard" => [
                                            [["text" => "🟠 Выполнить ЦДЗ", "web_app" => ["url" => $object]]],
                                        ],    
                                    ];
                                    sendMessage($userId, $message, json_encode($keyboard));
                                    return;
                                }
                            }
                        }
                    }
                    else {
                        break;
                    }
                }
            }
            return;
        }
        
        $g_k = 0;
        foreach ($result2['payload'] as $i) {
            $message .= "***".file_get_contents("https://vorg.site/meshdnevnik_bot/getlessonemoji.php?n=".urlencode($i['subject_name']));
            $message .= $i['subject_name']."*** - ".$i['description'];
            $k = 1;
            for ($ii = 0;; $ii++) {
                if (isset($i['additional_materials'][$ii])) {
                    if ($i['additional_materials'][$ii]['action_name'] == "Пройти") {
                        foreach ($i['additional_materials'][$ii]['items'] as $j) {
                            $g_k++;
                            if (isset($_GET['date'])) $add_m = "https://t.me/meshdnevnik_bot?start=uc".$_GET['date']."000165$g_k";
                            else $add_m = "https://t.me/meshdnevnik_bot?start=uc".date("Y-m-d")."000165$g_k";
                            $message .= " [(цдз ".$k.")](".$add_m.")";
                            $k++;
                        }
                    }
                }
                else {
                    break;
                }
            }
            $k = 1;
            for ($ii = 0;; $ii++) {
                if (isset($i['materials'][$ii])) {
                    if ($i['materials'][$ii]['type'] == "attachments") {
                        foreach ($i['materials'][$ii]['urls'] as $j) {
                            $message .= " [(файл ".$k.")](".$j['url'].")";
                            $k++;
                        }
                    }
                }
                else {
                    break;
                }
            }
            $message .= "\n";
        }
        if ($message == "📄 ДЗ на сегодня:\n\n" || $message == "📄 ДЗ на завтра:\n\n" || $message == "📄 ДЗ на ".$_GET['date'].":\n\n") {
            $message = "🌚 Не хочу тебя огорчать, но походу на сегодня тебе ничего не задано.\n";
            if ($_GET['date'] == "next") {
                $message = "🏔 Ты можешь взобраться на Эверест в поисках домашнего задания, а то такое ощущение, что твои учителя тебе на завтра ничего не задали...\n";
            }
            elseif (isset($_GET['date'])) {
                $message = "⌚️ Переведи часы на несколько дней назад, а то ты уже смотришь домашнее задание на ".$_GET['date']." и его тут еще нет.\n";
            }
        }
        if (isset($_GET['mid'])) {
            if (rand(0, 10) == 0) $message .= "\n🌐 ***Мы будем очень признательны, если вы подпишетесь на наши соцсети***: [Телеграм канал](https://t.me/meshdnevnik_channel) • [Twitter](https://twitter.com/mesdnevnik_bot)";
            elseif (rand(0, 10) <= 2) $message .= "\n🍉 ***Кликай и ***[стань арбузным королем уже сегодня!](https://t.me/wmclick_bot/click?startapp=ref_RYAvd9MX)";
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