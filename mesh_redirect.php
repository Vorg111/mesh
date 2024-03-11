<?php session_start(); ?>
<?php if (isset($_GET['id'])) : ?>
    <?php
        $_SESSION['tg_id'] = $_GET['id'];
        if (isset($_GET['ref'])) {
            $_SESSION['ref_id'] = $_GET['ref'];
        }
        if (isset($_GET['promo'])) {
            $_SESSION['promo_id'] = $_GET['promo'];
        }
    ?>
    <script>
        window.location.href ="https://school.mos.ru?backUrl=https://vorg.site/meshdnevnik_bot/redirect.php";
    </script>
<?php endif ?>