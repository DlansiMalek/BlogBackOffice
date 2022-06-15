<?php

namespace App\Services;

use App\Models\FormInput;
use App\Models\FormInputType;
use App\Models\FormInputValue;
use App\Models\QuestionReference;
use App\Models\ResponseReference;
use Illuminate\Support\Facades\Log;

class RegistrationFormServices
{

    public function getForm($congressId)
    {
        return FormInput::with(['type', 'values',
         'question_reference' => function ($query) {
            $query->with(['reference', 
            'response_reference'  => function ($q) {
                $q->with(['value']);
            } ]);
        },])
            ->where('congress_id', '=', $congressId)
            ->get();
    }

    public function getInputTypes()
    {
        return FormInputType::all();
    }

    public function setForm($newInputs, $congressId)
    {
        $oldInputs = $this->getForm($congressId);
        foreach ($oldInputs as $old) {
            $exists = false;
            foreach ($newInputs->all() as $new) {
                if ($old->form_input_id == $new['form_input_id']) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) $old->delete();
        }
        foreach ($newInputs->all() as $key => $new) {
            $input = null;
            foreach ($oldInputs as $old) {
                if ($old->form_input_id == $new['form_input_id']) {
                    $input = $old;
                    break;
                }
            }
            if (!$input) $input = new FormInput();
            $input->form_input_type_id = $new["form_input_type_id"];
            $input->congress_id = $congressId;
            $input->label = $new["label"];
            $input->label_en = $new["label_en"];
            $input->required = $new["required"];
            $input->public_label = $new["public_label"];
            $input->public_label_en = $new["public_label_en"];
            $input->key = $new["key"] ? $new["key"] : substr($new["label"], 180);
            $input->save();
            $val = $newInputs[$key];
            $val['form_input_id'] = $input->form_input_id;
            $newInputs[$key] = $val;
            if ($new["type"]["name"] == "checklist" || $new["type"]["name"] == "multiselect" || $new["type"]["name"] == "select" || $new["type"]["name"] == "radio") {
                $oldValues = FormInputValue::where('form_input_id', '=', $input->form_input_id)->get();
                foreach ($oldValues as $oldVal) {
                    $exists = false;
                    foreach ($new["values"] as $newVal) {
                        if ($newVal['form_input_value_id'] == $oldVal->form_input_value_id) {
                            $exists = true;
                            break;
                        }
                    }
                    if (!$exists) $oldVal->delete();
                }


                foreach ($new["values"] as $valueRequest) {
                    $value = null;
                    foreach ($oldValues as $oldVal) {
                        if ($oldVal->form_input_value_id == $valueRequest['form_input_value_id']) {
                            $value = $oldVal;
                            break;
                        }
                    }
                    if (!$value) $value = new FormInputValue();
                    $value->form_input_value_id = $valueRequest['form_input_value_id'];
                    $value->value = $valueRequest['value'];
                    $value->value_en = $valueRequest['value_en'];
                    $value->value_ar = $valueRequest['value_ar'];
                    $value->form_input_id = $input->form_input_id;
                    $value->price = $valueRequest['price'];
                    if ($value->form_input_value_id) $value->update();
                    else $value->save();
                }
            }
        }
        foreach($newInputs->all() as &$new) {
            $oldQuestionReferences = $this->getQuestionReference($new['form_input_id']);
            if (count($oldQuestionReferences) > 0) 
            {
                foreach ($oldQuestionReferences as $old) {
                    $exists = false;
                    foreach ($new['question_reference'] as $reference) {
                        if ($old->question_reference_id == $reference['question_reference_id']) {
                            $exists = true;
                            break;
                        }
                    }
                    if (!$exists) $old->delete();
                }
            }

            if (count($new['question_reference']) > 0) {
                foreach ($new['question_reference'] as $reference) {
                    $questionRef = null;
                    $input = $this->getFormInputByKey($congressId, $reference['key']);
                    foreach ($oldQuestionReferences as $old) {
                        if ($old->question_reference_id == $reference['question_reference_id'] ||($old->form_input_id == $new['form_input_id'] && $old->reference_id == $input->form_input_id)) {
                            $questionRef = $old;
                            break;
                        }
                    }
                    $newQuestionReference = $this->addQuestionReference($new['form_input_id'], $input->form_input_id, $questionRef);
                    $oldResponseReferences = $this->getResponseReference($newQuestionReference->question_reference_id);
                    if (count($oldResponseReferences) > 0) {
                        foreach ($oldResponseReferences as $old) {
                            $exists = false;
                            foreach($reference['responseValues'] as $value) {
                                $oldVal = $this->getFormInputValueByValue($input->form_input_id, $value);
                                if ($old->response_reference_id == $oldVal->response_reference_id) {
                                    $exists = true;
                                    break;
                                }
                            }
                            if (!$exists) $old->delete();
                        }
                    }

                    foreach($reference['responseValues'] as $value) {
                        $oldVal = $this->getFormInputValueByValue($input->form_input_id, $value);
                        $responseRef = null;
                        foreach ($oldResponseReferences as $old) {
                            if ($old->response_reference_id == $oldVal->response_reference_id) {
                                $responseRef = $old;
                                break;
                            }
                        }
                    $this->addResponseReference($oldVal->form_input_value_id, $newQuestionReference->question_reference_id, $responseRef);
                    }
                }
              }
        }
    }

    public function getFormInputByKey($congress_id, $key) 
    {
        return FormInput::where('congress_id', '=', $congress_id)
        ->where('key', '=', $key)->first();
    }

    public function addQuestionReference($form_input_id, $reference_id, $questionRef = null)
    {
        $questtion_reference = $questionRef ? $questionRef : new QuestionReference();
        $questtion_reference->reference_id = $reference_id;
        $questtion_reference->form_input_id = $form_input_id;
        $questionRef ? $questtion_reference->update() : $questtion_reference->save();
        return $questtion_reference;
    }

    public function getFormInputValueByValue($formInputId, $value) 
    {
        return FormInputValue::where('form_input_id', '=', $formInputId)
        ->where('value', '=', $value)->first();
    }

    public function addResponseReference($form_input_value_id, $question_reference_id, $responseRef)
    {
        $response_reference = $responseRef ? $responseRef : new ResponseReference();
        $response_reference->form_input_value_id = $form_input_value_id;
        $response_reference->question_reference_id = $question_reference_id;
        $responseRef ? $response_reference->update() : $response_reference->save();
    }

    public function getQuestionReference($form_input_id)
    {
        return QuestionReference::where('form_input_id', '=', $form_input_id)
        ->get();
    }

    public function getResponseReference($question_reference_id)
    {
        return ResponseReference::where('question_reference_id', '=', $question_reference_id)
        ->get();
    }
}