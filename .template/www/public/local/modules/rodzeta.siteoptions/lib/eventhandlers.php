<?php

namespace Rodzeta\Siteoptions;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class EventHandlers
{
    public static function register()
    {
        //NOTE ����� ����������� ������������ https://dev.1c-bitrix.ru/api_help/main/events/index.php
        // ����������� ����� ������� � ��������� ������ �� ������ - ��������
        // \Rodzeta\Siteoptions\EmailToBitrix24::register(); // ��������� �������� ������� - �������� ����� � Bitrix24
        // \Rodzeta\Siteoptions\IblockToBitrix24::register(); // ��������� ��������� ��������� - ������������� � �������� Bitrix24
    }
}
