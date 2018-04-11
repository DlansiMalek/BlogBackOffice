<?php
/**
 * Created by IntelliJ IDEA.
 * User: S4M37
 * Date: 19/12/2017
 * Time: 11:14
 */

namespace App\Services;


use App\Models\Groupe;
use App\Models\Niveau;
use App\Models\Periode;
use App\Models\S_Groupe;
use App\Models\S_Periode;
use App\Models\Service;
use App\Models\Session_Stage;
use App\Models\SGroupe_Service;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;

class SessionStageRepository
{


    public function addSessionStage(Request $request)
    {
        $session_stage = new Session_Stage();

        $session_stage = $this->fillSessionStage($session_stage, $request);

        $session_stage->save();
        return $session_stage;
    }

    public function addingGroupeToSession(Request $request, $session_stage_id, $periodes, $capacity)
    {
        $groupes = $request->input("groupes");
        foreach ($groupes as $groupe) {
            $groupeData = new Groupe();
            $groupeData->label = $groupe['label'];
            $groupeData->capacity = $capacity;
            $groupeData->session_stage_id = $session_stage_id;
            $groupeData->save();

            $sgroupes = $groupe['sgroupes'];
            $this->addingSGroupeToSession($groupeData->groupe_id, $sgroupes, $periodes, $capacity / sizeof($sgroupes));
        }
        return $this->getAllGroupesBySessionStage($session_stage_id);
    }

    public function getAllGroupesBySessionStage($session_stage_id)
    {
        return Groupe::with(['sgroupes.services', 'sgroupes.speriodes'])
            ->where('session_stage_id', '=', $session_stage_id)
            ->get();
    }

    public function addingSGroupeToSession($groupe_id, $sgroupes, $periodes, $capacity)
    {
        $nbsgroupes = sizeof($sgroupes);
        foreach ($sgroupes as $sgroupe) {
            $sgroupeData = new S_Groupe();
            $sgroupeData->label = $sgroupe['label'];
            $sgroupeData->capacity = $capacity;
            $sgroupeData->groupe_id = $groupe_id;
            $sgroupeData->save();
            $this->addingServicesToSgroupes($sgroupe['services'], $sgroupeData->s_groupe_id);
            $this->addingSPeriode($nbsgroupes, $periodes, $sgroupeData->s_groupe_id);
        }
    }

    public function addingServicesToSgroupes($services, $s_groupe_id)
    {
        foreach ($services as $service) {
            $sgroupe_service = new SGroupe_Service();
            $sgroupe_service->service_id = $service['service_id'];
            $sgroupe_service->s_groupe_id = $s_groupe_id;
            $sgroupe_service->save();
        }
    }

    public function addingPeriodesToSession(Request $request, $session_stage_id)
    {
        $periodes = $request->input('periodes');

        foreach ($periodes as $periode) {
            $periodeData = new Periode();
            $periodeData->start_date = Utils::convertDate($periode['start_date'], '/');
            $periodeData->end_date = Utils::convertDate($periode['end_date'], '/');
            if (array_key_exists('end_middle_date', $periode)) {
                $periodeData->end_middle_date = $periode['end_middle_date'];
            }
            if (array_key_exists('start_middle_date', $periode)) {
                $periodeData->start_middle_date = $periode['start_middle_date'];
            }
            $periodeData->session_stage_id = $session_stage_id;
            $periodeData->save();
        }

        return $this->getAllPeriodesBySessionStage($session_stage_id);
    }

    private function getAllPeriodesBySessionStage($session_stage_id)
    {
        return Periode::where('session_stage_id', '=', $session_stage_id)
            ->get();
    }

    private function addingSPeriode($nbsgroupes, $periodes, $s_groupe_id)
    {
        foreach ($periodes as $periode) {

            $weekNumber = Utils::calculateWeek($periode['start_date'], $periode['end_date']);//number of week

            $s = ceil($weekNumber / $nbsgroupes);
            Log::info($weekNumber);

            $pas = 0;
            for ($i = 0; $i < $nbsgroupes; $i++) {
                $speriode = new S_Periode();
                $speriode->start_date = Utils::getDateAddingWeek($periode['start_date'], $pas);
                $speriode->end_date = Utils::getDateAddingWeek($periode['start_date'], $pas + $s);
                $speriode->periode_id = $periode['periode_id'];
                $speriode->s_groupe_id = $s_groupe_id;
                $speriode->save();
                $pas = $s + $pas;
            }
        }
    }

    function getAll()
    {
        return Session_Stage::with(['niveau'])
            ->get();
    }

    public function getById($sessionStageId)
    {
        return Session_Stage::with(['groupes.sgroupes.services', 'niveau', 'periodes.speriodes'])
            ->where("session_stage_id", "=", $sessionStageId)
            ->first();
    }

    public function delete($sessionStage)
    {

        $this->deleteGroupes($sessionStage->groupes);
        $this->deletePeriodes($sessionStage->periodes);

        $sessionStage->delete();
    }

    public function deleteGroupes($groupes)
    {
        foreach ($groupes as $groupe) {
            foreach ($groupe->sgroupes as $sgroupe) {
                $sgroupe->delete();
            }
            $groupe->delete();
        }
    }

    public function deletePeriodes($periodes)
    {
        foreach ($periodes as $periode) {
            foreach ($periode->speriodes as $speriode) {
                $speriode->delete();
            }
            $periode->delete();
        }
    }

    public function edit($sessionStage, Request $request)
    {

        $this->deleteGroupes($sessionStage->groupes);
        $this->deletePeriodes($sessionStage->periodes);
        $sessionStage = $this->fillSessionStage($sessionStage, $request);

        $sessionStage->update();

        return $sessionStage;
    }

    private function fillSessionStage($session_stage, Request $request)
    {
        $session_stage->name = $request->input('name');
        $session_stage->date_choice_open = Utils::convertDate($request->input('date_choice_open'), '/');
        $session_stage->date_choice_close = Utils::convertDate($request->input('date_choice_close'), '/');
        $session_stage->niveau_id = $request->input('niveau_id');
        $session_stage->capacity = $request->input('capacity');

        if ($request->has('date_service_open')) {
            $session_stage->date_service_open = Utils::convertDate($request->input('date_service_open'), '/');
        }
        if ($request->has('date_service_close')) {
            $session_stage->date_service_close = Utils::convertDate($request->input('date_service_close'), '/');
        }
        return $session_stage;
    }
}