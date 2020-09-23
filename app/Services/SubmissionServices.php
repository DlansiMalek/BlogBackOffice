<?php

namespace App\Services;

use App\Models\Author;
use App\Models\ResourceSubmission;
use App\Models\Submission;
use App\Models\SubmissionEvaluation;

class SubmissionServices
{

    public function addSubmission($title, $type, $communication_type_id, $description, $congress_id, $theme_id, $user_id)
    {
        $submission = new Submission();
        $submission->title = $title;
        $submission->type = $type;
        $submission->communication_type_id = $communication_type_id;
        $submission->description = $description;
        $submission->congress_id = $congress_id;
        $submission->theme_id = $theme_id;
        $submission->user_id = $user_id;
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
            ->where('title', 'LIKE', '%' . $search . '%')
            ->get();
    }

    public function getAllSubmissionsByCongress( $congressId, $search, $status)
    {
        $allSubmission = Submission::with([
            'authors' => function ($query) {
                $query->select('submission_id', 'author_id', 'first_name', 'last_name', 'rank');
            },
            'resources' => function ($query) {
                $query->select('submission_id', 'resource_submission_id', 'resource.resource_id', 'resource.path');
            }
        ])
            ->select('submission_id', 'code', 'title', 'communication_type_id', 'status')
            ->where('congress_id', '=', $congressId)
            ->when($search !== "null" && $search !== "" && $search !== null , function ($query) use ($search, $status) {
                $query ->whereHas('authors', function ($q) use ($search) {
                    $q->where('first_name', 'like', $search );
                    $q->orWhere('last_name', 'like', $search);
                });
                $query->orWhere('title', '=', $search);
                $query->orWhere('code', '=', $search);
            })->when($status !== "null" && $status !=="" && $search !== null, function ($query) use ($status) {
                $query->where('status', '=', $status);
            })
            ->get();
        return $allSubmission->values();
    }

}
