<?php

namespace modules\statik\services;

use Craft;
use craft\base\Component;
use craft\elements\User;
use craft\helpers\App;
use craft\mail\Mailer;
use craft\mail\Message;
use craft\web\View;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Error\Error;
use yii\base\Exception;

class MailService extends Component
{
    public Mailer $mailer;
    public ?string $emailCM;
    public array $mailAddresses;

    public function init() : void
    {
        $this->mailer = Craft::$app->getMailer();
        $this->emailCM = Craft::$app->getGlobals()->getSetByHandle('siteInfo')->email;
        $this->mailAddresses = array_map('trim', explode(',', $this->emailCM));
    }

    public function sendEmailStory(array $fields, User $user, $language): void
    {
        $subjectCM = $this->getSubjectCM($language);
        $subjectUser = $this->getSubjectUser($language);

        $this->setUpEmail($this->mailAddresses, $subjectCM, '_mail/_story/_defaultCM_' . $language, ['story' => $fields, 'user' => $user]);
        $this->setUpEmail($user->email, $subjectUser, '_mail/_story/_defaultUser_' . $language, ['story' => $fields, 'user' => $user]);
    }

    public function sendEmailStoryWithoutAuthor(array $fields, $language): void
    {
        $emailWriter = $fields['email'];
        $subjectCM = $this->getSubjectCM($language);
        $subjectUser = $this->getSubjectUser($language);

        $this->setUpEmail($this->mailAddresses, $subjectCM, '_mail/_story/_defaultCM_' . $language, ['story' => $fields]);
        $this->setUpEmail($emailWriter, $subjectUser, '_mail/_story/_defaultUser_' . $language, ['story' => $fields]);
    }

    public function sendEmailEvent(array $fields, User $user, $language): void
    {
        $subjectUser = $this->getSubjectUser($language);
        switch ($language) {
            case 'nl':
                $subjectCM = "Nieuw Repair Event ingezonden";
                break;
            case 'fr':
                $subjectCM = "Nouveau Repair Event soumis";
                break;
            default:
                $subjectCM = "New Repair Event submitted";

        }

        $this->setUpEmail($this->mailAddresses, $subjectCM, '_mail/_event/_defaultCM_' . $language, ['event' => $fields, 'user' => $user]);
        $this->setUpEmail($user->email, $subjectUser, '_mail/_event/_defaultUser_' . $language, ['event' => $fields, 'user' => $user]);
    }

    private function setUpEmail($mailTo, string $title, string $template, array $parameter) : void
    {
        try {
            $email = App::mailSettings()->fromEmail;
            $currentSite = Craft::$app->getGlobals()->getSetByHandle('siteInfo')->cityName;
            $message = new Message();
            $message->setSubject(Craft::t('site', $title));
            $message->setFrom([$email => $currentSite]);
            $message->setTo($mailTo);
            $html = Craft::$app->getView()->renderTemplate($template, $parameter, View::TEMPLATE_MODE_SITE);
            $message->setHtmlBody($html);
            $this->mailer->send($message);
        } catch (Exception | Error $e) {
            Craft::error("Error setting up email: {$e->getMessage()}", "REPCIT_MAIL");
        }
    }

    private function getSubjectCM($language): string
    {
        switch ($language) {
            case 'nl':
                return "Nieuwe Repair Story ingezonden";
            case 'fr':
                return "Nouvelle Repair Story soumis";
            default:
                return "New Repair Story submitted";
        }
    }

    private function getSubjectUser($language): string
    {
        switch ($language) {
            case 'nl':
                return "Bedankt voor uw inzending";
            case 'fr':
                return "Merci pour votre soumission";
            default:
                return "Thank you for your submission";
        }
    }
}
