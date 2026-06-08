<?php

namespace Ipol\Catapulto\Bitrix;

IncludeModuleLangFile(__FILE__);

/**
 * Class MailSender
 *
 * @package namespace Ipol\Catapulto\Bitrix
 */
class MailSender
{

    private static function checkTrackingMailEvents() {
        $eventType = 'CATAPULTO_TRACKING';
        $eventName = Tools::getMessage('MAILSENDER_EVENTNAME');
        $eventDescription = Tools::getMessage('MAILSENDER_EVENTDESCRIPTION');

        $messageHeader = Tools::getMessage('MAILSENDER_MESSAGEHEADER');
        $messageBody = Tools::getMessage('MAILSENDER_MESSAGEBODY');

        $templateReady = true;

        $eTypeCurrent = \CEventType::GetList([
            'TYPE_ID'=>$eventType
        ])->Fetch();

        if (!$eTypeCurrent) {
            $templateReady = false;

            $eType = new \CEventType;
            if ($eType->Add([
                "LID" => LANGUAGE_ID,
                "EVENT_NAME" => $eventType,
                "NAME" => $eventName,
                "DESCRIPTION" => $eventDescription
            ])) {
                $messageTemplate = new \CEventMessage;
                if ($messageTemplate->Add([
                    "ACTIVE" => "Y",
                    "LID" => SITE_ID,
                    "EVENT_NAME" => 'CATAPULTO_TRACKING',
                    "EMAIL_FROM" => "#EMAIL_FROM#",
                    "EMAIL_TO" => "#EMAIL_TO#",
                    "SUBJECT" => $messageHeader,
                    "BODY_TYPE" => "html",
                    "BCC" => "#BCC#",
                    "MESSAGE" => $messageBody,
                ])) {
                    $templateReady = true;
                }
            }
        }

        return $templateReady;
    }

    public static function notifyTracking($trackLink, $email) {
        if (empty($email)) return;
        if (!self::checkTrackingMailEvents()) return; //Cant send email!

        \CEvent::Send('CATAPULTO_TRACKING', SITE_ID, [
            'EMAIL_FROM'=> \COption::GetOptionString('main', 'email_from'),
            'EMAIL_TO'=>$email,
            'TRACK_NUMBER'=>'<a target="_blank" href="'.$trackLink.'">'.$trackLink.'</a>'
        ]);
    }


}
