<?php

namespace App\Services;

use App\Models\Form_Input;
use App\Models\Form_Input_Type;
use App\Models\Form_Input_Value;

class RegistrationFormServices
{

    public function getForm($congressId)
    {
        return Form_Input::with(['type', 'values'])
            ->where('congress_id', '=', $congressId)
            ->get();
    }

    public function getInputTypes()
    {
        return Form_Input_Type::get();
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
        foreach ($newInputs->all() as $new) {
            $input = new Form_Input();
            $input->form_input_id = $new['form_input_id'];
            $input->form_input_type_id = $new["type"]["form_input_type_id"];
            $input->congress_id = $congressId;
            $input->label = $new["label"];
            if (!$new['form_input_id']) $input->save();
            else $input->update();
            if ($new["type"]["name"] == "checklist" || $new["type"]["name"] == "multiselect" || $new["type"]["name"] == "select" || $new["type"]["name"] == "radio") {
                $oldValues = Form_Input_Value::where('form_input_id', '=', $input->form_input_id)->get();
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
                    $value = new Form_Input_Value();
                    $value->form_input_value_id = $valueRequest['form_input_value_id'];
                    $value->value = $valueRequest['value'];
                    $value->form_input_id = $input->form_input_id;
                    if ($value->form_input_value_id) $value->update();
                    else $value->save();
                }
            }
        }
    }


}