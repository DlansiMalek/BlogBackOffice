<?php

namespace App\Services;

 
use App\Models\Project;
use Illuminate\Http\Request;


class ProjectServices{
    public function getAll()
    {
        return Project::all();
    }

    public function getProjectById($project_id)
    {
        return Project::find($project_id);
    }

    public function deleteFAQ($Project)
    {
        return $Project->delete();
    }

    public function addProject($project, $request, $category_id,$admin_id)
    {
        if (!$project) {
            $project = new Project();
        }
        $project ->nom  =  $request['nom'];
        $project ->date  =  $request['date'];
        $project ->lien  =  $request['lien'];
        $project ->admin_id  =  $admin_id;
        $project ->category_id  =  $category_id;

        $project >save();
        return $project ;
    }


}