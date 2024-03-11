<?php session_start(); ?>
<?php if (isset($_SESSION['tg_id'])) : ?>
    <script>
        window.location.href ="https://dnevnik.mos.ru/aupd/auth?back_url=https://vorg.site/meshdnevnik_bot/login.php";
    </script>
<?php endif ?>

<?php if (isset($_GET['id'])) : ?>
    <?php $_SESSION['tg_id_d'] = $_GET['id']; ?>
    <script>
        window.location.href ="https://dnevnik.mos.ru/aupd/auth?back_url=https://vorg.site/meshdnevnik_bot/reconnect.php";
    </script>
<?php endif ?>