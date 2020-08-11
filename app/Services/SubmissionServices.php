<?php

namespace App\Services;

use App\Models\AttestationParams;
use App\Models\AttestationSubmission;
use App\Models\Author;
use App\Models\CommunicationType;
use App\Models\ResourceSubmission;
use App\Models\Submission;
use App\Models\SubmissionEvaluation;
use Illuminate\Support\Facades\Storage;

class SubmissionServices
{

    public function addSubmission($title, $type, $prez_type, $description, $congress_id, $theme_id, $user_id)
    {
        $submission = new Submission();
        $submission->title = $title;
        $submission->type = $type;
        $submission->prez_type = $prez_type;
        $submission->description = $description;
        $submission->congress_id = $congress_id;
        $submission->theme_id = $theme_id;
        $submission->user_id = $user_id;
        $submission->save();
        return $submission;
    }

    public function editSubmission($submission, $title, $type, $prez_type, $description, $theme_id)
    {
        $submission->title = $title;
        $submission->type = $type;
        $submission->prez_type = $prez_type;
        $submission->description = $description;
        $submission->theme_id = $theme_id;
        $submission->update();
        return $submission;
    }

    public function getSubmission($submission_id)
    {
        return Submission::with([
            'authors' => function ($query) {
                $query->orderBy('rank');
            },
            'resources',
            'congress.configSubmission'
        ])
            ->where('submission_id', '=', $submission_id)
            ->first();
    }

    public function getSubmissionById($submission_id)
    {
        return Submission::where('submission_id', '=', $submission_id)->first();
    }

    public function saveResourceSubmission($resourceIds, $submission_id)
    {
        $oldResources = ResourceSubmission::where('submission_id', '=', $submission_id)->get();
        if (sizeof($oldResources) > 0) {
            foreach ($resourceIds as $resourceId) {
                foreach ($oldResources as $oldResource) {
                    if ($oldResource['resource_id'] != $resourceId) {
                        $this->addResourceSubmission($resourceId, $submission_id);
                    }
                }
            }
        } else {
            foreach ($resourceIds as $resourceId) {

                $this->addResourceSubmission($resourceId, $submission_id);
            }
        }
    }

    function addResourceSubmission($resourceId, $submissionId)
    {

        $resourceSubmission = new ResourceSubmission();
        $resourceSubmission->resource_id = $resourceId;
        $resourceSubmission->Submission_id = $submissionId;
        $resourceSubmission->save();

        return $resourceSubmission;
    }

    public function affectSubmissionToEvaluators($configSubmission, $submission_id, $admins)
    {

        $loopLength = sizeof($admins) > $configSubmission['num_evaluators'] ? $configSubmission['num_evaluators'] : sizeof($admins);

        for ($i = 0; $i < $loopLength; $i++) {
            $this->addSubmissionEvaluation($admins[$i]->admin_id, $submission_id);
        }
    }

    public function addSubmissionEvaluation($admin_id, $submission_id)
    {
        $submissionEvaluation = new SubmissionEvaluation();
        $submissionEvaluation->submission_id = $submission_id;
        $submissionEvaluation->admin_id = $admin_id;
        $submissionEvaluation->save();
        return $submissionEvaluation;
    }


    public function renderSubmissionForAdmin()
    {
        return Submission::with([
            'user:user_id,first_name,last_name,email',
            'authors:submission_id,author_id,first_name,last_name',
            'theme:theme_id,label',
            'submissions_evaluations' => function ($query) {
                $query->select('submission_id', 'submission_evaluation_id', 'admin_id', 'note')
                    ->with(['evaluator:admin_id,name,email']);
            }
        ]);
    }

    public function getCongressSubmissionForAdmin($admin, $congress_id, $privilege_id)
    {
        if ($privilege_id == 1) {
            $allSubmission = $this->renderSubmissionForAdmin()
                ->where('congress_id', '=', $congress_id)->get();
            $allSubmissionToRender = $allSubmission->map(function ($submission) {
                return collect($submission->toArray())
                    ->only(['submission_id', 'title', 'type',
                        'prez_type', 'description', 'global_note',
                        'status', 'theme', 'user', 'authors', 'submissions_evaluations',
                        'congress_id', 'communication_type_id', 'created_at'])
                    ->all();
            });
            return $allSubmissionToRender;

        } elseif ($privilege_id == 11) {
            $allSubmission = Submission::whereHas('submissions_evaluations', function ($query) use ($admin) {
                $query->where('admin_id', '=', $admin->admin_id);
            })
                ->with([
                    'theme:theme_id,label',
                    'submissions_evaluations' => function ($query) use ($admin) {
                        $query->select('submission_id', 'submission_evaluation_id', 'admin_id', 'note')
                            ->with(['evaluator:admin_id,name,email'])->where('admin_id', '=', $admin->admin_id);
                    }
                ])->where('congress_id', '=', $congress_id)->get();
            $allSubmissionToRender = $allSubmission->map(function ($submission) {
                return collect($submission->toArray())
                    ->only(['submission_id', 'title', 'type',
                        'prez_type', 'description', 'global_note',
                        'status', 'theme', 'submissions_evaluations',
                        'congress_id', 'communication_type_id', 'created_at'])
                    ->all();
            });
            return $allSubmissionToRender;
        }
    }


    public function getSubmissionDetailById($admin, $submission_id, $privilege_id)
    {
        if ($privilege_id == 1) {
            $submissionById = $this->renderSubmissionForAdmin()
                ->where('submission_id', '=', $submission_id)->first();
            if ($submissionById) {
                $submissionToRender = $submissionById
                    ->only(['submission_id', 'title', 'type',
                        'prez_type', 'description', 'global_note',
                        'status', 'theme', 'user', 'authors', 'submissions_evaluations',
                        'congress_id', 'created_at','congress','resources']);
                return $submissionToRender;
            }

        } elseif ($privilege_id == 11) {
            $submissionById = Submission::whereHas('submissions_evaluations', function ($query) use ($admin) {
                $query->where('admin_id', '=', $admin->admin_id);
            })
                ->with([
                    'resources',
                    'theme:theme_id,label',
                    'submissions_evaluations' => function ($query) use ($admin) {
                        $query->select('submission_id', 'submission_evaluation_id', 'admin_id', 'note')
                            ->with(['evaluator:admin_id,name,email'])->where('admin_id', '=', $admin->admin_id);
                    }
                ])->where('submission_id', '=', $submission_id)->first();
            if ($submissionById) {
                $submissionToRender = $submissionById
                    ->only(['submission_id', 'title', 'type',
                        'prez_type', 'description', 'global_note',
                        'status', 'theme', 'submissions_evaluations',
                        'congress_id', 'created_at', 'resources']);

                return $submissionToRender;
            }
        }
        return null;
    }


    public function putEvaluationToSubmission($admin, $submissionId, $note)
    {
        $evaluation = $this->getSubmissionEvaluationByAdminId($admin, $submissionId);
        $evaluation->note = $note;
        $evaluation->save();
        $global_note = SubmissionEvaluation::where('submission_id', '=', $submissionId)
            ->where('note', '>=', 0)->average('note');
        $submissionUpdated = Submission::where('submission_id', '=', $submissionId)->first();
        $submissionUpdated->global_note = $global_note;
        $submissionUpdated->save();
        return $submissionUpdated;
    }

    public function getSubmissionEvaluationByAdminId($admin, $submissionId)
    {
        return SubmissionEvaluation::where('admin_id', '=', $admin->admin_id)
            ->where('submission_id', '=', $submissionId)->first();
    }

    public function getSubmissionsByUserId($user)
    {
        return Submission::where('user_id', '=' , $user->user_id)->with('authors','congress')->get();
    }







    public function  getAttestationSubmissionById($attestationSubmissionId) {
        return AttestationSubmission::where('attestation_submission_id','=',$attestationSubmissionId)->first();
    }

    public function getAttestationsSubmissionsByCongressAndType($congressId, $communication_type_id) {
        return AttestationSubmission::where('congress_id','=',$congressId)
            ->where('communication_type_id','=',$communication_type_id)->get();
    }

    public function activateAttestationSubmission($attestationsSubmissions,$attestationSubmissionId) {
        foreach($attestationsSubmissions as $a) {
            if ($a->attestation_submission_id == $attestationSubmissionId) {
                $a->enable = 1;
                $a->update();
            }
            else {
                $a->enable = 0;
                $a->update();
            }
        }
        return "activated successfully";
    }

    public function makeSubmissionEligible($submission) {
        $submission->eligible = 1;
        $submission->update();
        return 'submission is eligible';
    }
    public function deleteAttestationSubmission($attestationSubmission) {

        AttestationParams::where('generator_id', '=', $attestationSubmission->attestation_generator_id)->delete();
        AttestationParams::where('generator_id', '=', $attestationSubmission->attestation_generator_id_blank)->delete();
        $attestationSubmission->delete();
        return "deleted successfully";
    }
    public function getAttestationSubmissionByCongress($congressId) {
        return AttestationSubmission::with([
            'communicationType',
            'attestation_param',
            'attestation_blanc_param'
        ])->where('congress_id','=',$congressId)
            ->orderBy('communication_type_id')
            ->get();
    }

    public function  getCommunicationTypeById($communication_type_id) {
        return CommunicationType::where('communication_type_id','=',$communication_type_id)->first();
    }

    public function getAttestationByGeneratorId($generatorId) {
        return AttestationSubmission::where('attestation_generator_id','=',$generatorId)
            ->first();
    }
    public function getAttestationByGeneratorBlankId($generatorId) {
        return AttestationSubmission::where('attestation_generator_id_blank','=',$generatorId)
            ->first();
    }


    public function updateOrCreateAttestationParams($generatorId,$keys,$update)
    {
        if ($update) {
            AttestationParams::where('generator_id', '=', $generatorId)->delete();
        }
        foreach ($keys as $key) {
            $attestationParam = new AttestationParams();
            $attestationParam->generator_id = $generatorId;
            $attestationParam->key = $key;
            $attestationParam->save();
        }
    }

    public function validerAttestation($congressId, $idGenerator, $communicationTypeId, $blank=false)
    {
        $attestationSubmission = new AttestationSubmission();
        $attestationSubmission->congress_id = $congressId;

        if ($blank) {
            $attestationSubmission->attestation_generator_id_blank = $idGenerator;
            $attestationSubmission->attestation_generator_id = null;

        } else {
            $attestationSubmission->attestation_generator_id = $idGenerator;
            $attestationSubmission->attestation_generator_id_blank = null;

        }
        $attestationSubmission->communication_type_id = $communicationTypeId;
        $attestationSubmission->enable = 1;
        $attestationSubmission->save();

        return $attestationSubmission;
    }
    public function getSubmissionType() {
        return CommunicationType::get();
    }
    public function getSubmissionByStatus($congressId, $status) {
        $submission = Submission::with([
            'user:user_id,first_name,last_name,email',
            'authors:submission_id,author_id,first_name,last_name'])
            ->where('congress_id','=',$congressId)
            ->where('status','=',$status)
            ->get();
        return $submission;
    }
    public function getAttestationSubmissionEnabled($congressId) {
        return AttestationSubmission::with([
            'attestation_param',
            'attestation_blanc_param'
        ])
            ->where('congress_id','=',$congressId)
            ->where('enable','=',1)->get();
    }

    public function getSubmissionByIdWithRelation($relations,$submissionId) {
        return Submission::with($relations)
            ->where('submission_id','=',$submissionId)->first();

}


}
