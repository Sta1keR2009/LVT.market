<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$application = \Bitrix\Main\Application::getInstance();
$request = $application->getContext()->getRequest();

include('functions.php');
createField("BLOG_COMMENT", 'UF_ASPRO_COM_LIKE', 'integer');
createField("BLOG_COMMENT", 'UF_ASPRO_COM_RATING', 'integer');
createField("BLOG_COMMENT", 'UF_ASPRO_COM_DISLIKE', 'integer');
createField("BLOG_COMMENT", 'UF_ASPRO_COM_APPROVE', 'boolean');
