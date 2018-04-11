<?php
/**
 * Created by IntelliJ IDEA.
 * User: Abbes
 * Date: 06/04/2018
 * Time: 16:27
 */

namespace App\Http\Controllers;


use App\Models\Session_Stage;
use App\Services\SessionStageRepository;
use Illuminate\Http\Request;

class SessionStageController extends Controller
{
    protected $sessionStageRepository;

    function __construct(SessionStageRepository $sessionStageRepository)
    {
        $this->sessionStageRepository = $sessionStageRepository;
    }


    function addSessionStage(Request $request)
    {

        $session_stage = $this->sessionStageRepository->addSessionStage($request);

        $periodes = $this->sessionStageRepository->addingPeriodesToSession($request, $session_stage->session_stage_id);
        $groupes = $this->sessionStageRepository->addingGroupeToSession($request, $session_stage->session_stage_id,
            $periodes, $request->input('capacity') / sizeof($request->input('groupes')));


        return response()->json(['message' => 'adding session stage success', 'data' => $this->sessionStageRepository->getById($session_stage->session_stage_id)]);

    }

    function getAllSessionStage()
    {
        $session_stages = $this->sessionStageRepository->getAll();

        return response()->json($session_stages);
    }

    public function getSessionStageById($sessionStageId)
    {
        return response()->json($this->sessionStageRepository->getById($sessionStageId));
    }

    public function delete($sessionStageId)
    {
        if (!$sessionStage = $this->sessionStageRepository->getById($sessionStageId)) {
            return response()->json(['error' => 'session de stage not found'], 404);
        }

        $this->sessionStageRepository->delete($sessionStage);

        return response()->json(['message' => 'delete success']);
    }

    public function edit($sessionStageId, Request $request)
    {
        if (!$sessionStage = $this->sessionStageRepository->getById($sessionStageId)) {
            return response()->json(['error' => 'session de stage not found'], 404);
        }

        if (!$sessionStage = $this->sessionStageRepository->edit($sessionStage, $request)) {
            return response()->json(["can't edit"], 400);
        }

        $periodes = $this->sessionStageRepository->addingPeriodesToSession($request, $sessionStage->session_stage_id);
        $groupes = $this->sessionStageRepository->addingGroupeToSession($request, $sessionStage->session_stage_id,
            $periodes, $request->input('capacity') / sizeof($request->input('groupes')));

        return response()->json($this->sessionStageRepository->getById($sessionStage->session_stage_id));

    }
}