<?php

namespace App\Http\Controllers;


use App\Models\Congress;
use App\Models\Feedback_Question_Type;
use App\Models\Feedback_Response;
use App\Services\FeedbackService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Shared\Date;


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

    public function saveFeedbackResponses(Request $request, $user_id){
        $this->feedbackService->deleteResponses($user_id);
        foreach($request->all() as $req){
            $this->feedbackService->saveFeedbackResponse($req, $user_id);
        }
        return $this->feedbackService->getFeedbackResponses($user_id);
    }

    public function getFeedbackStart($congress_id){
        $res = Congress::find($congress_id)->feedback_start;
        return response()->json($res!=null?$res:date('Y-m-d h:i:s'),200);
    }

    public function setFeedbackStart(Request $request, $congress_id){
        $congress = Congress::find($congress_id);
        $congress->feedback_start = $request->input('feedback_start');
        $congress->update();
        return $congress;
    }

    public function getFeedbackResponses($congress_id){
        return $this->feedbackService->getFeedbackResponsesByCongressId($congress_id);
    }

    public function getFeedbackQuestionTypes(){
        return $this->feedbackService->getFeedbackQuestionTypes();
    }

}
