<?php

$connection = \Bitrix\Main\Application::getConnection();

$this->queryExecute("SET NAMES 'utf8mb4'");
$this->queryExecute("SET collation_connection = 'utf8mb4_0900_ai_ci'");
