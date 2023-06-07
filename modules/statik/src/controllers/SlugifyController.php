<?php

namespace modules\statik\controllers;

use Craft;
use craft\web\Controller;
use modules\statik\services\SlugifyService;


class SlugifyController extends Controller
{
    protected int|bool|array $allowAnonymous = ['create-slug-from-string'];

    public function actionCreateSlugFromString(): array|string
    {
        $string = Craft::$app->request->getParam('string');
        return SlugifyService::instance()->createSlug($string);
    }
}
