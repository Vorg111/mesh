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
        
    $users = R::find('meshbotusers', "goodmorning_notific = ?", array(1));
        
    $date_r = date("Y-m-d");
    
    foreach ($users as $user) {
        if ($user -> goodmorning_notific) {
            $message = "🌄 С добрым утром!\n";
            if ((date('d.m', strtotime($user -> birthdate)) == date('d.m'))) $message .= "\n***🎁 С днем рождения!*** \n";
            
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
                
                if (isset($result[0]['rank'])) break;
                
                $date = date("Y-m-d", strtotime("-".$i." days"));
            }
            $downrait = [];
            $uprait = [];
                
            foreach($result as $i) {
                if ($i['rank']['rankStatus'] == "stable") {
                   
                }
                elseif ($i['rank']['rankStatus'] == "up") {
                   array_push($uprait, file_get_contents("https://vorg.site/meshdnevnik_bot/getlessonemoji.php?n=".urlencode($i['subjectName']))." По предмету \"".$i['subjectName']."\" до ***".$i['rank']['rankPlace']."*** места, поздравляю! ___(".number_format($i['rank']['averageMarkFive'], 2, '.', '').")___");
                }
                else {
                   array_push($downrait, file_get_contents("https://vorg.site/meshdnevnik_bot/getlessonemoji.php?n=".urlencode($i['subjectName']))." По предмету \"".$i['subjectName']."\" до ***".$i['rank']['rankPlace']."*** места. ___(".number_format($i['rank']['averageMarkFive'], 2, '.', '').")___");
                }
            }
            if (sizeof($uprait) > 0) {
                $rrmessage .= "\n📈 ***За вчера вы поднялись в рейтинге класса:***\n";
                $rrmessage .= implode(",\n", $uprait);
                $rrmessage .= "\n";
            }
            if (sizeof($downrait) > 0) {
                $rrmessage .= "\n📉 ***За вчера вы опустились в рейтинге класса:***\n";
                $rrmessage .= implode(",\n", $downrait);
                $rrmessage .= "\n";
            }
            
            
            $url = "https://school.mos.ru/api/family/web/v1/schedule?student_id=".$user -> client_id."&date=".date('Y-m-d');
            
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
                continue;
            }
            if (isset($result2['activities'][0])) {
                $message .= "\nСегодня твой первый урок — это ***\"".file_get_contents("https://vorg.site/meshdnevnik_bot/getlessonemoji.php?n=".urlencode($result2['activities'][0]['lesson']['subject_name']))
                ." ".$result2['activities'][0]['lesson']['subject_name']."\"*** в ".$result2['activities'][0]['begin_time']."!";
                
                $url = "https://school.mos.ru/api/family/web/v1/homeworks?from=".date("Y-m-d")."&to=".date("Y-m-d")."&student_id=".$user -> client_id;
                $amessage = "\n\n📄 Не забудь сделать все домашние задания на сегодня:\n\n";
                    
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, false);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Auth-Token:".$user -> token,
                    "X-Mes-Subsystem:familyweb"
                ));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result2 = json_decode(curl_exec($ch), true);
                
                $g_k = 0;
                foreach ($result2['payload'] as $i) {
                    $amessage .= "***".file_get_contents("https://vorg.site/meshdnevnik_bot/getlessonemoji.php?n=".urlencode($i['subject_name']));
                    $amessage .= $i['subject_name']."*** - ".$i['description'];
                    $k = 1;
                    for ($ii = 0;; $ii++) {
                        if (isset($i['additional_materials'][$ii])) {
                            if ($i['additional_materials'][$ii]['action_name'] == "Пройти") {
                                foreach ($i['additional_materials'][$ii]['items'] as $j) {
                                    $g_k++;
                                    $add_m = "https://t.me/meshdnevnik_bot?start=uc".date("Y-m-d")."000165$g_k";
                                    $amessage .= " [(цдз ".$k.")](".$add_m.")";
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
                                    $amessage .= " [(файл ".$k.")](".$j['url'].")";
                                    $k++;
                                }
                            }
                        }
                        else {
                            break;
                        }
                    }
                    $amessage .= "\n";
                }
                if ($amessage != "📄 ДЗ на сегодня:\n\n") {
                    $message .= $rrmessage;
                    $message .= $amessage;
                }
            }
            else {
                $message .= $rrmessage;
                $message .= "Успешно отдохни в свой выходной!";
            }
            sendMessage($user -> tg_id, $message, null);
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