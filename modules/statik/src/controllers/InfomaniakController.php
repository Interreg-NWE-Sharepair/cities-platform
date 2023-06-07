<?php

namespace modules\statik\controllers;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use yii\base\Exception;


class InfomaniakController extends Controller
{
    protected int|bool|array $allowAnonymous = ['subscribe'];

    public function actionSubscribe()
    {
        try {
            // get post variables
            $request = Craft::$app->getRequest();
            $email = $request->getParam('email', '');
            $contacts = '{"contacts": [{"email": "'.$email.'"}]}';

            $client_api = getenv('LLN_INFOMANIAK_API_KEY');
            $client_secret = getenv('LLN_INFOMANIAK_SECRET_KEY');
            $mailingListId = 113834;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,'https://newsletter.infomaniak.com/api/v1/public/mailinglist/'.$mailingListId.'/importcontact');
            curl_setopt($ch,CURLOPT_HTTPHEADER,['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_USERPWD, $client_api.':'.$client_secret);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch,CURLOPT_POSTFIELDS, $contacts);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
            curl_close($ch);

            if(json_decode($result,1)['result'] == "success") {
                return $this->redirectToPostedUrl();
            }
            return $this->redirect(UrlHelper::siteUrl('?error=1'));
        } catch (\Throwable $e) {
            Craft::error("Something went wrong with subscribing to the newsletter: $e","REPCIT_INFOMANIAK");
        }
    }
}
