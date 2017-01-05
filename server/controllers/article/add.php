<?php
use Respect\Validation\Validator as DataValidator;
DataValidator::with('CustomValidations', true);

class AddArticleController extends Controller {
    const PATH = '/add';

    public function validations() {
        return [
            'permission' => 'staff_2',
            'requestData' => [
                'title' => [
                    'validation' => DataValidator::length(3, 40),
                    'error' => ERRORS::INVALID_NAME
                ],
                'content' => [
                    'validation' => DataValidator::length(10),
                    'error' => ERRORS::INVALID_CONTENT
                ],
                'topicId' => [
                    'validation' => DataValidator::dataStoreId('topic'),
                    'error' => ERRORS::INVALID_TOPIC
                ]
            ]
        ];
    }

    public function handler() {
        $article = new Article();
        $article->setProperties([
            'title' => Controller::request('title'),
            'content' => Controller::request('content'),
            'lastEdited' => Date::getCurrentDate(),
            'position' => Controller::request('position') || 1
        ]);

        $topic = Topic::getDataStore(Controller::request('topicId'));
        $topic->ownArticleList->add($article);
        $topic->store();

        $staff = Controller::getLoggedUser();

        Log::createLog('ADD_ARTICLE', $article->title);

        Response::respondSuccess([
            'articleId' => $article->store()
        ]);
    }
}