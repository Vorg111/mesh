<?php
    require "libs/rb.php";
    $token = '6488989522:AAGsTDXZka5WbueA5Re-gdqtHPNi36OorA8';
    
    
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
    
    
    
    R::setup( 'mysql:host=localhost;dbname=u1706092_accountpp',
        'u1706092_default', '03CAL1bI8ybTqhA7' );
        
    
    
    
    
    $users = R::find('meshbotparents');
    foreach ($users as $user) {
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
    }
?>