<?php

namespace App\Services;

use App\Models\FeedbackQuestion;
use App\Models\FeedbackResponse;
use App\Models\FeedbackValue;
use JWTAuth;
use PDF;

class FeedbackService
{


    public function getFeedbackForm($congress_id)
    {
        return FeedbackQuestion::with(['type', 'values'])
            ->where('congress_id', '=', $congress_id)
            ->orderBy("order")
            ->get();
    }

    public function getFeedbackQuestionTypeById($feedback_question_type_id)
    {
        return Feedback_Question_Type::find($feedback_question_type_id);
    }

    public function deleteFeedbackQuestionValues($feedback_question_id)
    {
        FeedbackValue::where('feedback_question_id', '=', $feedback_question_id);
    }

    public function saveFeedbackQuestion($request, $congress_id)
    {
        $question = new FeedbackQuestion();
        $question->label = $request['label'];
        $question->congress_id = $congress_id;
        $question->feedback_question_type_id = $request['feedback_question_type_id'];
        $question->max_responses = $request['max_responses'];
        $question->order = $request['order'];
        $question->save();
        $type = $this->getFeedbackQuestionTypeById($question->feedback_question_type_id);
        if (!$type || $type->name != 'choice') return;
        foreach ($request['values'] as $requestValue) {
            $value = new FeedbackValue();
            $value->value = $requestValue['value'];
            $value->order = $requestValue['order'];
            $value->feedback_question_id = $question->feedback_question_id;
            $value->save();
        }

    }

    public function updateFeedbackQuestionValues($newValues, $oldValues, $feedback_question_id)
    {
        foreach ($oldValues as $oldValue) {
            $found = false;
            $newValue = new FeedbackValue();
            foreach ($newValues as $val) {
                if (array_key_exists('feedback_question_value_id', $val) && $oldValue->feedback_question_value_id == $val['feedback_question_value_id']) {
                    $found = true;
                    $newValue = $val;
                    break;
                }
            }
            if (!$found) $oldValue->delete();
            else if ($oldValue->value != $newValue['value'] || $oldValue->order != $newValue['order']) {
                $oldValue->value = $newValue['value'];
                $oldValue->order = $newValue['order'];
                $oldValue->save();
            }
        }
        foreach ($newValues as $newValue) {
            $found = false;
            foreach ($oldValues as $oldValue) {
                if (array_key_exists('feedback_question_value_id', $newValue) && $oldValue->feedback_question_value_id == $newValue['feedback_question_value_id']) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $value = new FeedbackValue();
                $value->value = $newValue['value'];
                $value->order = $newValue['order'];
                $value->feedback_question_id = $feedback_question_id;
                $value->save();
            }
        }
    }

    public function resetFeedbackForm($congress_id)
    {
        FeedbackQuestion::where("congress_id", "=", $congress_id)->delete();
    }

    public function deleteResponses($user_id)
    {
        FeedbackResponse::where('user_id', '=', $user_id)->delete();
    }

    public function saveFeedbackResponse($req, $user_id)
    {
        $resp = new FeedbackResponse();
        $resp->text = array_key_exists("text", $req) ? $req['text'] : "";
        $resp->feedback_question_value_id = array_key_exists("feedback_question_value_id", $req) ? $req['feedback_question_value_id'] : null;
        $resp->user_id = $user_id;
        $resp->feedback_question_id = $req['feedback_question_id'];
        $resp->save();
    }

    public function getFeedbackResponses($user_id)
    {
        return FeedbackResponse::where('user_id', '=', $user_id)->get();
    }

    public function getFeedbackResponsesByCongressId($congress_id)
    {
        return FeedbackQuestion::with(['type', 'values.responses', 'responses'])
            ->where('congress_id', '=', $congress_id)
            ->get();
    }

    public function getFeedbackQuestionTypes()
    {
        //TODO A voir
        return Feedback_Question_Type::get();
    }

}
