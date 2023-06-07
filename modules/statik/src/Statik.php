<?php

namespace modules\statik;

use Craft;
use craft\base\Element;
use craft\base\Widget;
use craft\console\Application as ConsoleApplication;
use craft\elements\User;
use craft\events\DraftEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterElementSourcesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\SetAssetFilenameEvent;
use craft\events\TemplateEvent;
use craft\helpers\Assets;
use craft\i18n\PhpMessageSource;
use craft\services\Dashboard;
use craft\services\Drafts;
use craft\services\Fields;
use craft\services\Users;
use craft\web\twig\variables\CraftVariable;
use craft\web\View;
use modules\statik\assetbundles\Statik\StatikAsset;
use modules\statik\fields\AnchorLink;
use modules\statik\services\LanguageService;
use modules\statik\services\ProductcategoryService;
use modules\statik\services\RepairstatusService;
use modules\statik\services\Revision;
use modules\statik\services\OrganisationService;
use modules\statik\variables\StatikVariable;
use modules\statik\widgets\ManualWidget;
use yii\base\Event;
use yii\base\Module;

/**
 * Class Statik
 *
 * @author    Statik
 * @package   Statik
 * @since     1.0.0
 *
 * @property LanguageService language
 * @property Revision revision
 * @property OrganisationService organisation
 * @property ProductcategoryService productCategory
 * @property RepairstatusService repairStatus
 *
 */
class Statik extends Module
{
    // Static Properties
    // =========================================================================

    /**
     * @var Statik
     */
    public static Statik $instance;

    const LANGUAGE_COOKIE = '__language';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function __construct($id, $parent = null, array $config = [])
    {
        Craft::setAlias('@modules/statik', $this->getBasePath());

        // Translation category
        $i18n = Craft::$app->getI18n();
        /** @noinspection UnSafeIsSetOverArrayInspection */
        if (!isset($i18n->translations[$id]) && !isset($i18n->translations[$id . '*'])) {
            $i18n->translations[$id] = [
                'class' => PhpMessageSource::class,
                'sourceLanguage' => 'nl-BE',
                'basePath' => '@modules/statik/translations',
                'forceTranslation' => true,
                'allowOverrides' => true,
            ];
        }

        // Base template directory
        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function (RegisterTemplateRootsEvent $e) {
            if (is_dir($baseDir = $this->getBasePath() . DIRECTORY_SEPARATOR . 'templates')) {
                $e->roots[$this->id] = $baseDir;
            }
        });

        // Set this as the global instance of this module class
        static::setInstance($this);

        parent::__construct($id, $parent, $config);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$instance = $this;

        // Add in our console commands
        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'modules\statik\console\controllers';
        } else {
            $this->controllerNamespace = 'modules\statik\controllers';
        }

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function (Event $event) {
            /** @var CraftVariable $variable */
            $variable = $event->sender;
            $variable->set('statik', StatikVariable::class);
        });

        Event::on(Assets::class, Assets::EVENT_SET_FILENAME, function (SetAssetFilenameEvent $event) {
            $event->extension = mb_strtolower($event->extension);
        });

        if (Craft::$app->getRequest()->getIsCpRequest()) {
            Event::on(View::class, View::EVENT_BEFORE_RENDER_TEMPLATE, function (TemplateEvent $event) {
                Craft::$app->getView()->registerAssetBundle(StatikAsset::class);
            });
        }

        // Register our fields
        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function (RegisterComponentTypesEvent $event) {
            $event->types[] = AnchorLink::class;
        });

        // CM city can only see users from city
        Event::on(User::class, Element::EVENT_REGISTER_SOURCES, function (RegisterElementSourcesEvent $event) {
            /** @var User $currentUser */
            $currentUser = Craft::$app->getUser()->getIdentity();
            // Not if user is Admin
            if ($currentUser->admin != 1 && !$currentUser->isInGroup(6)) {
                $sources = $event->sources;
                $result = array_filter($sources, function ($source) {
                    if (isset($source['criteria']) && isset($source['criteria']['groupId'])) {
                        /** @var UserGroup $group */
                        $group = Craft::$app->getUserGroups()->getGroupById($source['criteria']['groupId']);
                        foreach (Craft::$app->getSites()->getEditableSites() as $site) {
                            if ($group->can("editSite:{$site->uid}")) {
                                return true;
                            }
                        }
                    }
                    return false;
                });
                $event->sources = $result;
            }
        });

        $this->setComponents([
            'revision' => Revision::class,
            'language' => LanguageService::class,
            'organisation' => OrganisationService::class,
            'productCategory' => ProductcategoryService::class,
            'repairStatus' => RepairstatusService::class
        ]);

        // register widget
        Event::on(Dashboard::class, Dashboard::EVENT_REGISTER_WIDGET_TYPES, function (RegisterComponentTypesEvent $event) {
            $event->types[] = ManualWidget::class;
        });

        // Activate Widget on user save
        Event::on(
            Users::class,
            Users::EVENT_AFTER_ACTIVATE_USER,
            function (Event $event) {
                $widget = new \craft\records\Widget();
                $widget->userId = $event->user->id;;
                $widget->type = ManualWidget::class;
                $widget->sortOrder = 0;
                $widget->colspan = 3;
                $widget->save();
            }
        );
    }
}
