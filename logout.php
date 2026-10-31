<?php
require_once __DIR__ . '/config/config.php';
unset($_SESSION['user']); session_regenerate_id(true); flash('You have been signed out.'); redirect('/index.php');
