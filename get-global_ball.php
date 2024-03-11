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

        $token = $user -> token;
        
        $url = "https://dnevnik.mos.ru/core/api/student_profiles/".$user -> client_id;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Auth-Token:".$token,
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = json_decode(curl_exec($ch), true);
        
        if (isset($result2['message']) && str_contains($result2['message'], "Предыдущая сессия работы в ЭЖД завершена. Войдите в ЭЖД заново")) {
            echo 0;
            return;
        }
    
        if (true) {
        
            $url = "https://school.mos.ru/api/family/web/v1/school_info?class_unit_id=".$result['class_unit']['id']."&school_id=".$result['school_id'];
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Auth-Token:".$token,
                "X-mes-subsystem:familyweb"
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result2 = json_decode(curl_exec($ch), true);
            $school_name = $result2['name'];
            
            $school_pos = file_get_contents("https://vorg.site/meshdnevnik_bot/getrait_pos_school.php?name=".urlencode($school_name));
        }
        
        
        $result2 = array();
        $date = date("Y-m-d");
        for ($i = 1; $i < 4; $i++) {
            $url = "https://school.mos.ru/api/ej/rating/v1/rank/rankShort?personId=".$result['person_id']."&beginDate=".$date."&endDate=".$date;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Auth-Token:".$token,
                "X-Mes-Subsystem:familyweb"
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result2 = json_decode(curl_exec($ch), true);
            if (isset($result2[0]['rankPlace'])) break;
            
            $date = date("Y-m-d", strtotime("-".$i." days"));
        }
        $url = "https://school.mos.ru/api/ej/rating/v1/rank/class?date=".$date."&personId=".$result['person_id'];
                
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Auth-Token:".$token,
            "X-Mes-Subsystem:familyweb"
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result3 = json_decode(curl_exec($ch), true);
        $avg_m = 0;
        for ($i = max($result2[0]['rankPlace'] - 100, 0); $i < sizeof($result3); $i++) {
            if ($result3[$i]['personId'] == $result['person_id']) {
                $avg_m = $result3[$i]['rank']['averageMarkFive'];
                break;
            }
        }
        echo ((pow($avg_m, 9) * (1 / (pow($school_pos / 2, 2))) * $result['class_unit']['class_level_id']) * 10);
    }
?>