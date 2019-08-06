<?php

namespace App\Services;

use App\Models\FormInput;
use App\Models\FormInputType;
use App\Models\FormInputValue;

class RegistrationFormServices
{

    public function getForm($congressId)
    {
        return FormInput::with(['type', 'values'])
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
        foreach ($newInputs->all() as $new) {
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
            $input->save();
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
                    $value->form_input_id = $input->form_input_id;
                    if ($value->form_input_value_id) $value->update();
                    else $value->save();
                }
            }
        }
    }


}