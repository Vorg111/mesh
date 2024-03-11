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
    
    function getWeekDates($inputDate) {
        $translate = array(
            "Monday" => "Пн.",
            "Tuesday" => "Вт.",
            "Wednesday" => "Ср.",
            "Thursday" => "Чт.",
            "Friday" => "Пт.",
            "Saturday" => "Сб.",
            "Sunday" => "Вс.");
            
        
        
        $dates = array();
            
        $today = DateTime::createFromFormat('Y-m-d', date("Y-m-d"));
        
        
        for ($i = 0; $i < 7; $i++) {
            $day = date('l', strtotime($today->format('Y-m-d')));
            array_push($dates, $today->format('Y-m-d'));
            $today->modify('+1 day');
        }
        
        return $dates;
    }
    
    
    // Получите обновление от Telegram
    R::setup( 'mysql:host=localhost;dbname=u1706092_accountpp',
        'u1706092_default', '03CAL1bI8ybTqhA7' );
        
    $userId = $_GET['chid'];
    
    if ($_GET['id']) {
        echo $_GET['id'];
        $userId = R::findOne('meshbotusers', 'id = ?', array($_GET['id'])) -> tg_id;
    }
    progress();
    function progress() {
        if (R::findOne('meshbotusers', 'tg_id = ?', array($userId))) {
            $user = R::findOne('meshbotusers', 'tg_id = ?', array($userId));
            
            
            $date_r = date("Y-m-d");
            if ($user -> marksnotifications) {
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
                    return;
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
                
                if ($user -> upd_notifications > date("H") * 60 * 60 + date("i") * 60 + date("s")) {
                    $user -> upd_notifications = 0;
                }
                
                
                $message = "🔔 Вам выставлены новые оценки:\n\n";
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
                
                $user -> upd_notifications = date("H") * 60 * 60 + date("i") * 60 + date("s");
                R::store($user);
                $userId = $user -> tg_id;
                //if ($message != "🔔 Вам выставлены новые оценки:\n\n") sendMessage("652001276", $message." ".$user -> upd_notifications, null);
                if ($message != "🔔 Вам выставлены новые оценки:\n\n") sendMessage($userId, $message, null);
            }
            $user -> upd_time = date("H") * 60 * 60 + date("i") * 60 + date("s");
            
            $url = "https://school.mos.ru/v2/token/refresh";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer ".$user -> token,
                "X-Mes-Subsystem:familyweb"
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result3 = curl_exec($ch);
            
            if (!str_contains($result3, "\"brief\":\"token_expired\"") && !str_contains($result3, "\"brief\":")) {
                $user -> token = $result3;
                R::store($user);
            }
            else return;
            
            $ball = file_get_contents("https://vorg.site/meshdnevnik_bot/get-global_ball.php?chid=".$user -> tg_id);
            if ((double)$ball > 0) {
                $user -> global_ball = ((double)$ball);
                R::store($user);
            }
            
            $dates = getWeekDates(date("Y-m-d"));
            foreach ($dates as $date) {
                $url = "https://school.mos.ru/api/family/web/v1/schedule?student_id=".$user -> client_id."&date=".$date;
                
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, false);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Auth-Token:".$user -> token,
                    "X-Mes-Subsystem:familyweb"
                ));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $schedules[$date] = curl_exec($ch);
            }
            $user -> schedules = json_encode($schedules);
            R::store($user);
            
            
            
                    
            $today = DateTime::createFromFormat('Y-m-d', date("Y-m-d"));
            $rtoday = DateTime::createFromFormat('Y-m-d', date("Y-m-d"));
            $hometasks = null; $mmmarks = null;
            for ($i = 0; $i < 7; $i++) {
                $url = "https://school.mos.ru/api/family/web/v1/homeworks?from=".$today->format('Y-m-d')."&to=".$today->format('Y-m-d')."&student_id=".$user -> client_id;
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, false);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Auth-Token:".$user -> token,
                    "X-Mes-Subsystem:familyweb"
                ));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result2 = json_decode(curl_exec($ch), true);
                if (isset($result2['message']) && str_contains($result2['message'], "Предыдущая сессия работы в ЭЖД завершена. Войдите в ЭЖД заново")) {
                    $hometasks = null;
                    $mmmarks = null;
                    break;
                }
                $hometasks[$today->format('Y-m-d')] = $result2['payload'];
                $today->modify('+1 day');
                
    
                $url = "https://dnevnik.mos.ru/core/api/marks?created_at_from=".$rtoday->format('Y-m-d')."&created_at_to=".$rtoday->format('Y-m-d')."&student_profile_id=".$user -> client_id;
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, false);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Auth-Token:".$user -> token,
                    "X-Mes-Subsystem:familyweb"
                ));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result2 = json_decode(curl_exec($ch), true);
                if (isset($result2['message']) && str_contains($result2['message'], "Предыдущая сессия работы в ЭЖД завершена. Войдите в ЭЖД заново")) {
                    $hometasks = null;
                    $mmmarks = null;
                    break;
                }
                
                $mmmarks[$rtoday->format('Y-m-d')] = $result2;
                $rtoday->modify('-1 day');
            }
            $url = "https://dnevnik.mos.ru/core/api/student_profiles/".$user -> client_id."?with_subjects=true";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Auth-Token:".$user -> token,
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $mmmarks['subjs'] = curl_exec($ch);
            
            $url = "https://school.mos.ru/api/family/mobile/v1/menu/buffet/?date=".date("Y-m-d")."&contract_id=1";
                    
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Auth-Token:".$user -> token,
                "X-Mes-Subsystem:familymp"
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $user -> menu = curl_exec($ch);
            
            
            
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
            if (!(isset($result2['message']) && str_contains($result2['message'], "Предыдущая сессия работы в ЭЖД завершена. Войдите в ЭЖД заново"))) {
                $user -> avg_marks = json_encode($result2);
            }
            else {
                $user -> avg_marks = null;
            }
            
            
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
                if (isset($result2[0]['rankPlace'])) break;
                
                $date = date("Y-m-d", strtotime("-".$i." days"));
            }
            
            if (!(str_contains($result2['message'], "Предыдущая сессия работы в ЭЖД завершена. Войдите в ЭЖД заново"))) {
                $user -> short_rait = json_encode($result2);
            }
            
            $url = "https://school.mos.ru/api/ej/rating/v1/rank/class?date=".date("Y-m-d")."&personId=".$user -> profile_id;
                
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Auth-Token:".$user -> token,
                "X-Mes-Subsystem:familyweb"
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result3 = json_decode(curl_exec($ch), true);
            if (!(str_contains($result3['message'], "Предыдущая сессия работы в ЭЖД завершена. Войдите в ЭЖД заново"))) {
                $user -> rait = json_encode($result3);
            }
            
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
                
                if (str_contains($result['message'], "Предыдущая сессия работы в ЭЖД завершена. Войдите в ЭЖД заново")) {
                    break;
                }
                if (isset($result[0]['rank'])) break;
                
                $date = date("Y-m-d", strtotime("-".$i." days"));
            }
            if (!(str_contains($result['message'], "Предыдущая сессия работы в ЭЖД завершена. Войдите в ЭЖД заново"))) {
                $user -> rait_subjlist = json_encode($result);
            }
            $rait_subj = array();
            if (isset($mmmarks['subjs'])) {
                foreach (json_decode($mmmarks['subjs'], true)['subjects'] as $i) {
                    $url = "https://school.mos.ru/api/ej/rating/v1/rank/class?subjectId=".$i['id']."&date=".date("Y-m-d")."&personId=".$user -> profile_id;
                        
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_POST, false);
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        "Auth-Token:".$user -> token,
                        "X-Mes-Subsystem:familyweb"
                    ));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $rait_subj[$i['id']] = json_decode(curl_exec($ch), true);
                }
                $user -> rait_subj = json_encode($rait_subj);
            }
            
            
            $olimp = array();
            $url = "https://school.mos.ru/portfolio/app/persons/".$user -> profile_id."/events/list";
        
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer ".$user -> token,
                "X-Mes-Subsystem: studentportfolioweb",
                "Cookie: aupd_current_role=4:2;"
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $olimp['res1'] = json_decode(curl_exec($ch), true);
            
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
            $olimp['res2'] = json_decode(curl_exec($ch), true);
            
            
            $url = "https://school.mos.ru/portfolio/app/persons/".$user -> profile_id."/sport-rewards/list";
        
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer ".$user -> token,
                "X-Mes-Subsystem: studentportfolioweb",
                "Cookie: aupd_current_role=4:2;"
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $user -> spotr_rewards = curl_exec($ch);
            
            $url = "https://school.mos.ru/portfolio/app/persons/".$user -> profile_id."/independent-diagnostic-grouped?count=1";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer ".$user -> token,
                "X-Mes-Subsystem: studentportfolioweb",
                "Cookie: aupd_current_role=4:2;"
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $user -> diags = curl_exec($ch);
            
            $user -> olimpiads = json_encode($olimp);
            $user -> hometasks = json_encode($hometasks);
            $user -> marks = json_encode($mmmarks);
            R::store($user);
            if (true) {
                $user -> school_ids = file_get_contents("https://vorg.site/meshdnevnik_bot/get_school.php?id=1&chid=".$user -> tg_id);
                $user -> school_data = file_get_contents("https://vorg.site/meshdnevnik_bot/get_school.php?data=1&chid=".$user -> tg_id);
                $user -> st_class = file_get_contents("https://vorg.site/meshdnevnik_bot/get_school.php?class=1&chid=".$user -> tg_id);
                $user -> school = "".file_get_contents("https://vorg.site/meshdnevnik_bot/get_school.php?chid=".$user -> tg_id);
                R::store($user);
            }
            
        }
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