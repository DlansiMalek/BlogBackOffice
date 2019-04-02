<?php

namespace App\Http\Controllers;


use App\Services\FeedbackService;
use Illuminate\Http\Request;


class FeedbackController extends Controller
{

    protected $feedbackService;

    function __construct(FeedbackService $feedbackService)
    {
        $this->feedbackService = $feedbackService;
    }


    public function getFeedbackForm($congress_id)
    {
        return $this->feedbackService->getFeedbackForm($congress_id);
    }

    public function setFeedbackForm(Request $request, $congress_id)
    {
        if (!$oldQuestions = $this->feedbackService->getFeedbackForm($congress_id)) $oldQuestions = [];
        foreach ($oldQuestions as $oldQuestion) {
            $found = false;
            $newQuestion = null;
            foreach ($request->all() as $q) {
                if (array_key_exists('feedback_question_id', $q) && $q['feedback_question_id'] == $oldQuestion->feedback_question_id) {
                    $found = true;
                    $newQuestion = $q;
                    break;
                }
            }
            if (!$found) $oldQuestion->delete();
            else {
                if ($oldQuestion->max_responses != $newQuestion['max_responses'] || $oldQuestion->label != $newQuestion['label'] || $newQuestion['feedback_question_type_id'] != $oldQuestion->feedback_question_type_id || $newQuestion['order'] != $oldQuestion->order) {
                    $oldQuestion->max_responses = $newQuestion['max_responses'];
                    $oldQuestion->label = $newQuestion['label'];
                    $oldQuestion->feedback_question_type_id = $newQuestion['feedback_question_type_id'];
                    $oldQuestion->order = $newQuestion['order'];
                    $oldQuestion->update();
                }
                $type = $this->feedbackService->getFeedbackQuestionTypeById($oldQuestion->feedback_question_type_id);
                if ($type->name == 'text') $this->feedbackService->deleteFeedbackQuestionValues($oldQuestion->feedback_question_id);
                else if ($type->name == 'choice') {
                    $this->feedbackService->updateFeedbackQuestionValues($newQuestion['values'], $oldQuestion->values, $oldQuestion->feedback_question_id);
                }
            }
        }

        foreach ($request->all() as $newQuestion) {
            $found = false;
            foreach ($oldQuestions as $oldQuestion) {
                if (array_key_exists('feedback_question_id', $newQuestion) && $oldQuestion->feedback_question_id == $newQuestion['feedback_question_id']) {
                    $found = true;
                    break;
                }
            }
            if (!$found) $this->feedbackService->saveFeedbackQuestion($newQuestion, $congress_id);
        }
        return $this->feedbackService->getFeedbackForm($congress_id);

    }

    public function resetFeedbackForm($congress_id)
    {
        $this->feedbackService->resetFeedbackForm($congress_id);
        return [];
    }

    public function saveResponses(){

    }

}
