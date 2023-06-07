<?php

namespace modules\statik\console\controllers;

use craft\console\Controller;
use craft\records\Widget;
use craft\web\User;
use modules\statik\widgets\ManualWidget;

class SaveWidgetManualController extends Controller
{
    // ./craft statik/save-widget-manual/add-widget-to-dashboard
//    public function actionAddWidgetToDashboard(): bool
//    {
//        $users = \craft\elements\User::find()->all();
//
//        if($users !== []){
//            foreach($users as $user){
//                $widget = new Widget();
//                $widget->userId = $user->id;
//                $widget->type = ManualWidget::class;
//                $widget->sortOrder = 0;
//                $widget->colspan = 3;
//                $widget->save();
//            }
//            return true;
//        } else {
//            return false;
//        }
//    }

    /**
     * @throws \yii\db\StaleObjectException
     * @throws \Throwable
     */
    // ./craft statik/save-widget-manual/remove-widgets
//    public function actionRemoveWidgets(){
//        $cm = \craft\elements\User::find()->fullName('Matthieu Leroy')->one()->id;
//        $widgets = \craft\records\Widget::find()->all();
//        if ($cm != '' and $widgets !== []){
//            foreach($widgets as $widget){
//                if($widget->userId == $cm){
//                    if($widget->type == 'craft\widgets\QuickPost'){
//                        $widget->delete();
//                    }
//                }
//            }
//            return true;
//        } else{
//            return false;
//        }
////        dd($widgets);
//    }
}