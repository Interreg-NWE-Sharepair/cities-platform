# Things to do when adding a new site

- Create new sitegroup and related sites in the Craft backend
- Add a new user group in the Craft backend, to ensure that the Repair Heroes for the new city will be linked to the 
newly created sitegroup.- 
  
- Create a new search engine key on cse.google.com and add it in the template `_snippet/_global/_header.twig` based on the name of the sitegroup.

````
Sitegroup: newCity
User group: Repair heroes newCity
````

# Controllers

## StoriesController

- actionSaveStory

A Repair Hero can save a story as draft to edit it later or submit their story. When a story is saved as draft we do setScenario to validate the fields and then we save the entry as draft.

````
setScenario(Element::SCENARIO_ESSENTIALS);
saveElementAsDraft($entry, Craft::$app->getUser()->getIdentity()->getId());
````

The draft story now has a draftId and draftCreator. When the Hero wants to edit the draft, we send the draftId as param in the URL. The draftId then gets send to the Controller to find the right entry.

````
Entry::find()
        ->draftId($request->getParam('draftId'))
        ->draftCreator(Craft::$app->getUser()->getIdentity())
        ->siteId($request->getParam('siteId'))
        ->anyStatus()
        ->one();
````

- actionSaveStoryWithoutAuthor

Someone who isn't a Repair Hero can only submit their story. We don't save this person as a user, so they can't be assigned as author of the entry. Their personal information will be saved in the Craft backend and used instead of the entry author (which is the Craft admin). 


## EventsController

- actionSaveEvent

This action is very similar to actionSaveStory. The only difference is that there is a category *eventType* that we find and set on the entry.

````
$eventTypeGroup = Craft::$app->getCategories()->getGroupByHandle('eventType');
$typeEventCategory = Category::findOne(['groupId' => $eventTypeGroup->id, 'id' => $fields['category']]);
$entry->setFieldValue('category', [$typeEventCategory->id]);
````

# Services

## OrganisationService

- createOrUpdateOrganisations

After requesting information from te REPMAP API (which happens in the *OrganisationController*), we populate the data in the *Organisation* Model.

````
$o = new Organisation();
$o->populate($organisation);
````

Then we have to look in which language the data is to know on which site we have to save the entry. We do this with a switch case. This language can be found in de data as the *locale*.

````
switch ($o->locale[0]) {
        case 'nl':
                $siteId = 5;
                $this->language = $o->locale[0];
                break;
                
        case 'fr':
                $siteId = 6;
                $this->language = $o->locale[0];
                break;
                
        case 'en':
                $siteId = 8;
                $this->language = $o->locale[0];
                break;
}
````

We set the entry fields according to the language. If there is no data for this language, we look at the default value. We also do this to save categories (*organisationType* and *productCategories*). After all fields are set, the entry is saved.