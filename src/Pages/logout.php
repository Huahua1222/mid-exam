<?php

namespace Exam\Pages;

if (!session_id()) session_start();
class Logout
{
    public function __construct()
    {
        unset($_SESSION);
        session_destroy();
        header("Location: /login");
        exit;
    }
}
