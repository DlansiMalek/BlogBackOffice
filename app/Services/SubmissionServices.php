<?php

namespace App\Services;

use App\Models\AttestationParams;
use App\Models\AttestationSubmission;
use App\Models\CommunicationType;
use App\Models\Resource;
use App\Models\ResourceSubmission;
use App\Models\Submission;
use App\Models\SubmissionEvaluation;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SubmissionServices
{

    public function addSubmission($title, $type, $communication_type_id, $description, $congress_id, $theme_id, $user_id, $extId = null, $status = null, $eligible = null)
    {
        $submission = new Submission();
        $submission->title = $title;
        $submission->type = $type;
        $submission->communication_type_id = $communication_type_id;
        $submission->description = $description;
        $submission->congress_id = $congress_id;
        $submission->theme_id = $theme_id;
        $submission->user_id = $user_id;
        if ($status !== null) {
            $submission->status = $status;
        }
        if ($eligible !== null) {
            $submission->eligible = $eligible;
        }
        $submission->extId = $extId;

        $submission->save();
        return $submission;
    }

    public function editSubmission($submission, $title, $type, $status, $communication_type_id, $description, $theme_id, $code)
    {
        $submission->title = $title;
        $submission->type = $type;
        $submission->status = $status;
        $submission->communication_type_id = $communication_type_id;
        $submission->description = $description;
        $submission->theme_id = $theme_id;
        $submission->upload_file_code = $code;
        $submission->update();
        return $submission;
    }

    public function getSubmission($submission_id, $upload_file_code)
    {
        return Submission::with([
            'authors' => function ($query) {
                $query->orderBy('rank');
            },
            'resources',
            'congress.configSubmission'
        ])
            ->where('submission_id', '=', $submission_id)
            ->when($upload_file_code !== 'null', function ($query) use ($upload_file_code) {
                $query->where('upload_file_code', '=', $upload_file_code);
            })
            ->first();
    }

    public function getSubmissionsByCongressId($congress_id)
    {
        return Submission::with(['submissions_evaluations', 'congress.configSubmission'])
            ->where('congress_id', '=', $congress_id)
            ->get();
    }

    public function getSubmissionById($submission_id)
    {
        return Submission::where('submission_id', '=', $submission_id)
            ->with(['congress', 'user'])
            ->first();
    }


    public function saveResourceSubmission($resourceIds, $submission_id)
    {
        $oldResources = ResourceSubmission::where('submission_id', '=', $submission_id)->get();
        if (sizeof($oldResources) > 0) {
            foreach ($resourceIds as $resourceId) {
                $isExist = false;
                foreach ($oldResources as $oldResource) {
                    if ($oldResource['resource_id'] == $resourceId) {
                        $isExist = true;
                        break;
                    }
                }
                if (!$isExist) {
                    $this->addResourceSubmission($resourceId, $submission_id);
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
            'user:user_id,first_name,last_name,email,mobile',
            'communicationType:communication_type_id,label',
            'authors' => function ($query) {
                $query->select('submission_id', 'author_id', 'first_name', 'last_name', 'service_id',
                    'etablissement_id')
                    ->with(['service', 'etablissment']);
            },
            'theme:theme_id,label',
            'submissions_evaluations' => function ($query) {
                $query->select('submission_id', 'submission_evaluation_id', 'admin_id', 'note', 'communication_type_id')
                    ->with(['evaluator:admin_id,name,email']);
            }
        ]);
    }

    public function getCongressSubmissionForAdmin($admin, $congress_id, $privilege_id, $status, $perPage = null, $search = null, $tri = null, $order = null)
    {
        if ($privilege_id == 1 || $privilege_id == 12) {
            $allSubmission = $this->renderSubmissionForAdmin()
                ->when($status !== 'null', function ($query) use ($status) {
                    $query->where('status', '=', $status);
                })
                ->where('congress_id', '=', $congress_id)
                ->where(function ($query) use ($search) {
                    if ($search != "") {
                        $query->whereRaw('lower(title) like (?)', ["%{$search}%"]);
                        $query->orWhereRaw('lower(description) like (?)', ["%{$search}%"]);
                    }
                });
            if ($order && ($tri == 'submission_id' || $tri == 'title' || $tri == 'type' || $tri == 'prez_type'
                    || $tri == 'description' || $tri == 'global_note' || $tri == 'status' || $tri == 'user_id'
                    || $tri == 'theme_id' || $tri == 'congress_id')) {
                $allSubmission = $allSubmission->orderBy($tri, $order);
            }

            $allSubmission = $perPage ? $allSubmission->paginate($perPage) : $allSubmission->get();

            return $allSubmission;
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
                ])->where('congress_id', '=', $congress_id)
                ->when($status !== 'null', function ($query) use ($status) {
                    $query->where('status', '=', $status);
                })
                ->where(function ($query) use ($search) {
                    if ($search != "") {
                        $query->whereRaw('lower(title) like (?)', ["%{$search}%"]);
                        $query->orWhereRaw('lower(description) like (?)', ["%{$search}%"]);
                    }
                });
            if ($order && ($tri == 'submission_id' || $tri == 'title' || $tri == 'type' || $tri == 'prez_type'
                    || $tri == 'description' || $tri == 'global_note' || $tri == 'status' || $tri == 'user_id'
                    || $tri == 'theme_id' || $tri == 'congress_id')) {
                $allSubmission = $allSubmission->orderBy($tri, $order);
            }

            $allSubmission = $perPage ? $allSubmission->paginate($perPage) : $allSubmission->get();

            return $allSubmission;
        }
        return [];
    }


    public function getSubmissionDetailById($admin, $submission_id, $privilege_id)
    {
        if ($privilege_id == 1 || $privilege_id == 12) {
            $submissionById = $this->renderSubmissionForAdmin()
                ->where('submission_id', '=', $submission_id)->first();
            if ($submissionById) {
                $submissionToRender = $submissionById
                    ->only(['submission_id', 'title', 'type', 'communication_type_id', 'limit_date',
                        'prez_type', 'description', 'global_note', 'communicationType',
                        'status', 'theme', 'user', 'authors', 'submissions_evaluations',
                        'congress_id', 'created_at', 'congress', 'resources']);
                return $submissionToRender;
            }

        } elseif ($privilege_id == 11) {
            $submissionById = Submission::whereHas('submissions_evaluations', function ($query) use ($admin) {
                $query->where('admin_id', '=', $admin->admin_id);
            })
                ->with([
                    'resources',
                    'user:user_id,first_name,last_name,email',
                    'theme:theme_id,label',
                    'communicationType:communication_type_id,label',
                    'submissions_evaluations' => function ($query) use ($admin) {
                        $query->select('submission_id', 'submission_evaluation_id', 'admin_id', 'note', 'communication_type_id')
                            ->with(['evaluator:admin_id,name,email'])->where('admin_id', '=', $admin->admin_id);
                    }
                ])->where('submission_id', '=', $submission_id)->first();
            if ($submissionById) {
                if ($submissionById->status === 0) {
                    $submissionById->status = 2;
                    $submissionById->update();
                }
                $submissionToRender = $submissionById
                    ->only(['submission_id', 'title', 'type',
                        'prez_type', 'user', 'description', 'global_note', 'communicationType',
                        'status', 'theme', 'submissions_evaluations',
                        'congress_id', 'created_at', 'resources']);

                return $submissionToRender;
            }
        }
        return null;
    }


    public function putEvaluationToSubmission($admin, $submissionId, $note, $evaluation)
    {
        $evaluation->note = $note;
        $evaluation->save();
        // supposons seulement un seul utilisateur a fait la correction 
        // dans ce cas on doit pas faire la moyenne
        if (!$global_note = SubmissionEvaluation::where('submission_id', '=', $submissionId)
            ->where('note', '>', 0)->average('note')) {
            $global_note = 0;
        }
        // si !$global_note cela veut dire qu'un aucun correcteur a mis une note > 0 
        // don cla note_globable sera 0 

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

    public function getSubmissionsByUserId($user, $offset, $perPage, $search, $perCongressId, $status)
    {
        return Submission::where('user_id', '=', $user->user_id)
            ->with('authors', 'congress', 'resources')
            ->offset($offset)->limit($perPage)
            ->when($perCongressId !== "null", function ($query) use ($perCongressId) {
                $query->where('congress_id', '=', $perCongressId);
            })
            ->when($status !== "null", function ($query) use ($status) {
                $query->where('status', '=', $status);
            })
            ->where(function ($query) use ($search) {
                if ($search != "") {
                    $query->whereRaw('lower(title) like (?)', ["%{$search}%"]);
                    $query->orWhereRaw('lower(code) like (?)', ["%{$search}%"]);
                }
            })
            ->get();
    }

    public function getAllSubmissionsByCongress($congressId, $search, $status, $offset, $perPage, $communication_type_id)
    {
        $allSubmission = Submission::with([
            'resources', 'authors'
        ])->when($search !== "null" && $search !== "" && $search !== null,
            function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%');
                $query->orWhere('code', 'like', '%' . $search . '%');
            })->where('status', '=', 1)
            ->where('congress_id', '=', $congressId)
            ->where('communication_type_id', '=', $communication_type_id);
        $otherSubmissions = Submission::with([
            'resources', 'authors'
        ])->where('communication_type_id', '=', $communication_type_id)
            ->where('congress_id', '=', $congressId)
            ->where('status', '=', 1)
            ->whereHas("authors", function ($query) use ($search) {
                $query->where(DB::raw('CONCAT(first_name," ",last_name)'), 'like', '%' . $search . '%');

            })
            ->union($allSubmission)
            ->paginate($perPage);
        return $otherSubmissions;
    }

    public function getAttestationSubmissionById($attestationSubmissionId)
    {
        return AttestationSubmission::where('attestation_submission_id', '=', $attestationSubmissionId)->first();
    }

    public function getAttestationsSubmissionsByCongressAndType($congressId, $communication_type_id)
    {
        return AttestationSubmission::where('congress_id', '=', $congressId)
            ->where('communication_type_id', '=', $communication_type_id)->get();
    }

    public function activateAttestationSubmission($attestationsSubmissions, $attestationSubmissionId)
    {
        foreach ($attestationsSubmissions as $a) {
            if ($a->attestation_submission_id == $attestationSubmissionId) {
                $a->enable = 1;
                $a->update();
            } else {
                $a->enable = 0;
                $a->update();
            }
        }
        return "activated successfully";
    }

    public function makeSubmissionEligible($submission)
    {
        $submission->eligible = 1;
        $submission->update();
        return 'submission is eligible';
    }

    public function deleteAttestationSubmission($attestationSubmission)
    {

        AttestationParams::where('generator_id', '=', $attestationSubmission->attestation_generator_id)->delete();
        AttestationParams::where('generator_id', '=', $attestationSubmission->attestation_generator_id_blank)->delete();
        $attestationSubmission->delete();
        return "deleted successfully";
    }

    public function getAttestationSubmissionByCongress($congressId)
    {
        return AttestationSubmission::with([
            'communicationType',
            'attestation_param',
            'attestation_blanc_param'
        ])->where('congress_id', '=', $congressId)
            ->orderBy('communication_type_id')
            ->get();
    }

    public function getCommunicationTypeById($communication_type_id)
    {
        return CommunicationType::where('communication_type_id', '=', $communication_type_id)->first();
    }

    public function getAttestationByGeneratorId($generatorId)
    {
        return AttestationSubmission::where('attestation_generator_id', '=', $generatorId)
            ->first();
    }

    public function getAttestationByGeneratorBlankId($generatorId)
    {
        return AttestationSubmission::where('attestation_generator_id_blank', '=', $generatorId)
            ->first();
    }


    public function updateOrCreateAttestationParams($generatorId, $keys, $update)
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

    public function validerAttestation($congressId, $idGenerator, $communicationTypeId, $blank = false)
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

    public function getSubmissionType()
    {
        return CommunicationType::get();
    }

    public function getSubmissionByStatus($congressId, $status, $eligible)
    {
        if ($eligible === '1' or $eligible === '0') {
            $submission = Submission::with([
                'user:user_id,first_name,last_name,email',
                'authors:submission_id,author_id,first_name,last_name'])
                ->where('congress_id', '=', $congressId)
                ->where('status', '=', $status)
                ->where('eligible', '=', $eligible)
                ->get();
        } else {
            $submission = Submission::with([
                'user:user_id,first_name,last_name,email',
                'authors:submission_id,author_id,first_name,last_name'])
                ->where('congress_id', '=', $congressId)
                ->where('status', '=', $status)
                ->get();
        }
        return $submission;
    }

    public function getAttestationSubmissionEnabled($congressId)
    {
        return AttestationSubmission::with([
            'attestation_param',
            'attestation_blanc_param'
        ])
            ->where('congress_id', '=', $congressId)
            ->where('enable', '=', 1)->get();
    }

    public function getSubmissionByIdWithRelation($relations, $submissionId)
    {
        return Submission::with($relations)
            ->where('submission_id', '=', $submissionId)->first();
    }

    public function getCommunicationTypeByKey($key)
    {
        return CommunicationType::where('abrv', '=', $key)
            ->first();
    }

    public function getSubmissionExternal($extId)
    {
        return Submission::where('extId', '=', $extId)
            ->first();
    }

    public function addSubmissionExternal($congressId, $data, $user)
    {

        $communicationType = $this->getCommunicationTypeById($data['communication_type']);
        $communicationTypeId = $communicationType ? $communicationType : 1;
        $submissionTitle = isset($data['submission_title']) ? $data['submission_title'] : '-';
        $submissionType = isset($data['submission_type']) ? $data['submission_type'] : '-';
        $submissionDescription = isset($data['description']) ? $data['description'] : '-';
        $themeId = 1;
        $userId = $user->user_id;

        $submission = $this->getSubmissionExternal($data['submission_extId']);

        if ($submission) {
            return $this->editSubmission($submission, $submissionTitle, $submissionType, 1, $communicationTypeId, $submissionDescription, $themeId, null);
        } else {
            return $this->addSubmission($submissionTitle, $submissionType, $communicationTypeId, $submissionDescription, $congressId, $themeId, $userId, $data['submission_extId'], 1, 1);
        }
    }

    public function getAllResourcesBySubmission($submission_id)
    {
        return ResourceSubmission::with('resource')
            ->where('submission_id', '=', $submission_id)
            ->get();
    }

    public function deleteAllResourcesBySubmission($submission_id)
    {
        $resources = $this->getAllResourcesBySubmission($submission_id);
        foreach ($resources as $item) {
            $resource = $item->resource;
            Storage::delete('/resource/' . $resource->path);
            $item->delete();
            $resource->delete();
        }
    }


}
