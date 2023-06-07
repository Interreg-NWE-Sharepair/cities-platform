<?php

namespace modules\statik\variables;

use craft\db\Query;
use craft\elements\Entry;
use craft\elements\GlobalSet;
use craft\web\twig\variables\Cp;
use craft\web\twig\variables\Paginate;
use craft\web\View;
use modules\statik\services\SlugifyService;
use modules\statik\Statik;

use Craft;

/**
 * @author    Statik
 * @package   Statik
 * @since     1.0.0
 */
class StatikVariable
{
    public function revision(): string
    {
        return Statik::getInstance()->revision->getVersion();
    }

    /**
     * Render pagination template with options
     * @param Paginate $pageInfo
     * @param array $options
     * @return null
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     */
    public function paginate(Paginate $pageInfo, array $options = []): ?bool
    {
        if (!$pageInfo->total) {
            return false;
        }

        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
        echo Craft::$app->view->renderTemplate('statik/_paginate/_render', [
            'pageInfo' => $pageInfo,
            'options' => $options,
        ]);

        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_SITE);
        return null;
    }

    public function isBot($userAgent = '/bot|crawl|facebook|google|slurp|spider|mediapartners/i'): bool
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            if ($_SERVER['HTTP_USER_AGENT'] &&
                preg_match($userAgent, $_SERVER['HTTP_USER_AGENT'])) {
                return true;
            }
            return false;
        }
        return false;
    }

    // create slugs from titles in contentbuilder for the anchor link
    public function slugify($string): array|string
    {
        return SlugifyService::instance()->createSlug($string);
    }

    public function getStories($entryId, $authorId): array
    {
        $storySectionId = Craft::$app->getSections()->getSectionByHandle('repairStories')->id;
        $siteId = Craft::$app->getRequest()->sites->getCurrentSite()->id;

        $storiesAuthor = Entry::find()
                                ->sectionId($storySectionId)
                                ->siteId($siteId)
                                ->authorId($authorId)
                                ->id(['not', $entryId])
                                ->ids();
        
        if (count($storiesAuthor) >= 3) {
            return array_slice($storiesAuthor, 0, 3);
        }
        
        $otherStories = Entry::find()
                                ->sectionId($storySectionId)
                                ->siteId($siteId)
                                ->id(['not', $entryId])
                                ->ids();
        shuffle($otherStories);
        $stories = array_merge($storiesAuthor, $otherStories);
        $stories = array_unique($stories);

        return array_slice($stories, 0, 3);
    }

    public function sortByDistance(): \craft\elements\db\EntryQuery
    {
        $siteId = Craft::$app->sites->getCurrentSite()->id;
        $global =GlobalSet::findOne(['handle'=>'siteInfo', 'siteId'=>$siteId]);
        $long = $global->longitude;
        $lat = $global->latitude;

        $latitude = Craft::$app->fields->getFieldByHandle("latitudeOrganisation");
        $columnLatitude = "field_{$latitude->handle}_{$latitude->columnSuffix}";
        $longitude = Craft::$app->fields->getFieldByHandle("longitudeOrganisation");
        $columnLongitude = "field_{$longitude->handle}_{$longitude->columnSuffix}";

        $sectionId = Craft::$app->sections->getSectionByHandle('organisations')->id;

        $query = new Query();
        $query->select(["elementId", $columnLatitude, $columnLongitude, "geoDistance($columnLatitude,$columnLongitude,$lat,$long) AS distance"]);
        $query->from('content');
        $query->innerJoin('{{%entries}} entries', 'entries.id = content.elementId');
        $query->innerJoin('{{%elements}} elements', 'elements.id = content.elementId');
        $query->where('entries.sectionId = ' . $sectionId);
        $query->andWhere("siteId = $siteId");
        $query->andWhere("elements.revisionId IS NULL");
        $query->andWhere("elements.draftId IS NULL");
        $query->andWhere("elements.dateDeleted IS NULL");
        $query->orderBy('distance ASC');

        $professionalsWithDistance = [];
        $professionalsWithoutDistance = [];
        foreach($query->all() as $p){
            if($p['distance']){
                $professionalsWithDistance[] = $p['elementId'];
            }
            $professionalsWithoutDistance[] = $p['elementId'];
        }
        $professionals = array_merge($professionalsWithDistance,$professionalsWithoutDistance);
        $entryQuery = Entry::find();
        $entryQuery->id($professionals);
        $entryQuery->fixedOrder(true);

        return $entryQuery;
    }
}
