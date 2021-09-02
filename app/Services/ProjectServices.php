<?php

namespace App\Services;

use App\Models\Project;

class ProjectServices
{
    public function getAll()
    {
        return Project::with(['admin', 'category'])->get();

    }

    public function getProjectById($project_id)
    {
        return Project::find($project_id);
    }

    public function getProjectByIdCategory($category_id)
    {
        return Project::with(['admin'])
            ->where('category_id', '=', $category_id);
    }

    public function deleteProject($Project)
    {
        return $Project->delete();
    }

    public function addProject($project, $request)
    {
        if (!$project) {
            $project = new Project();
        }
        $project->nom = $request['nom'];
        $project->date = $request['date'];
        $project->project_img = $request['project_img'];
        $project->lien = $request['lien'];
        $project->admin_id = $request['admin_id'];
        $project->category_id = $request['category_id'];
        $project->save();
        return $project;
    }

}
