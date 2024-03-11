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
    
        if (isset($result['class_unit']['id'])) {
            
            if (isset($_GET['class'])) {
                echo $result['class_unit']['name'];
                return;
            }
        
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
            
            if (isset($_GET['id'])) {
                echo $result2['id'];
                return;
            }
            if (isset($_GET['data'])) {
                $data = array();
                $data['address'] = $result2['address'];
                $data['phone'] = $result2['phone'];
                $data['email'] = $result2['email'];
                $data['website_link'] = $result2['website_link'];
                echo json_encode($data);
                return;
            }
            
            echo $school_name;
        }
    }
?>