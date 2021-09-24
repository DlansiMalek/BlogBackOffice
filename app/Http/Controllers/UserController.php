<?php

namespace App\Http\Controllers;

use App\Models\AttestationRequest;
use App\Models\FormInputResponse;
use App\Models\Meeting;
use App\Services\AccessServices;
use App\Services\AdminServices;
use App\Services\BadgeServices;
use App\Services\CongressServices;
use App\Services\ContactServices;
use App\Services\MailServices;
use App\Services\OffreServices;
use App\Services\OrganizationServices;
use App\Services\PackServices;
use App\Services\PaymentServices;
use App\Services\RegistrationFormServices;
use App\Services\ResourcesServices;
use App\Services\RoomServices;
use App\Services\SharedServices;
use App\Services\SmsServices;
use App\Services\StandServices;
use App\Services\TrackingServices;
use App\Services\UrlUtils;
use App\Services\UserServices;
use App\Services\Utils;
use App\Services\PrivilegeServices;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception;
use App\Services\MeetingServices;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    protected $contactServices;
    protected $smsServices;
    protected $userServices;
    protected $congressServices;
    protected $adminServices;
    protected $sharedServices;
    protected $badgeServices;
    protected $accessServices;
    protected $packServices;
    protected $organizationServices;
    protected $paymentServices;
    protected $mailServices;
    protected $roomServices;
    protected $resourcesServices;
    protected $trackingServices;
    protected $offreServices;
    protected $standServices;
    protected $registrationFormServices;
    protected $meetingServices;
    protected $privilegeServices;

    public function __construct(UserServices $userServices, CongressServices $congressServices,
        AdminServices $adminServices,
        SharedServices $sharedServices,
        BadgeServices $badgeServices,
        AccessServices $accessServices,
        PackServices $packServices,
        OrganizationServices $organizationServices,
        PaymentServices $paymentServices,
        SmsServices $smsServices,
        ContactServices $contactServices,
        RoomServices $roomServices,
        MailServices $mailServices,
        ResourcesServices $resourcesServices,
        TrackingServices $trackingServices,
        OffreServices $offreServices,
        StandServices $standServices,
        RegistrationFormServices $registrationFormServices,
        MeetingServices $meetingServices,
        PrivilegeServices $privilegeServices) {
        $this->smsServices = $smsServices;
        $this->userServices = $userServices;
        $this->congressServices = $congressServices;
        $this->adminServices = $adminServices;
        $this->sharedServices = $sharedServices;
        $this->badgeServices = $badgeServices;
        $this->accessServices = $accessServices;
        $this->packServices = $packServices;
        $this->organizationServices = $organizationServices;
        $this->paymentServices = $paymentServices;
        $this->mailServices = $mailServices;
        $this->roomServices = $roomServices;
        $this->contactServices = $contactServices;
        $this->resourcesServices = $resourcesServices;
        $this->trackingServices = $trackingServices;
        $this->offreServices = $offreServices;
        $this->standServices = $standServices;
        $this->registrationFormServices = $registrationFormServices;
        $this->meetingServices = $meetingServices;
        $this->privilegeServices = $privilegeServices;
    }

    public function getLoggedUser()
    {
        if (!$user = $this->userServices->retrieveUserFromToken()) {
            return response()->json(['error' => 'user not found'], 404);
        }
        $user = $this->userServices->getUserById($user->user_id);

        return response()->json($user);
    }

    public function getUserByTypeAndCongressId($congress_id, Request $request)
    {
        $privilegeIds = $request->all();
        return $this->userServices->getUserByTypeAndCongressId($congress_id, $privilegeIds);
    }

    public function index()
    {
        return $this->userServices->getAllUsers();
    }

    public function getUserByEmail(Request $request)
    {
        $email = $request->input('email');

        if (!$user = $this->userServices->getUserByEmail($email)) {
            return response()->json(['error' => 'user not found'], 404);
        }

        return response()->json($user);
    }

    public function addContact(Request $request)
    {
        if (!$request->has('qrCode')) {
            return response()->json(['qrCode is needed'], 400);
        }
        if (!$user = $this->userServices->retrieveUserFromToken()) {
            return response()->json(['no user found'], 404);
        }
        if (!$user_viewed = $this->userServices->getUserByQrCode($request->input('qrCode'))) {
            return response()->json(['contact not found'], 404);
        }
        if ($contact = $this->contactServices->getContactByUserViewedId(
            $user_viewed->user_id,
            $user->user_id
        )) {
            return response()->json(['contact already registred'], 200);
        }

        $this->contactServices->addContact(
            $user->user_id,
            $user_viewed->user_id,
            $request->has('congressId') ? $request->input('congressId') : null
        );

        return response()->json('conctact added', 200);

    }

    public function deleteContact($user_viewed_id, Request $request)
    {

        if (!$user = $this->userServices->retrieveUserFromToken()) {
            return response()->json('no user found', 404);
        }
        if (!$user_viewed = $this->userServices->getUserById($user_viewed_id)) {
            return response()->json('no user found', 404);
        }
        $congress_id = $request->query('congress_id');
        if (!$contact = $this->contactServices->getContactByUserViewedId($user_viewed_id, $user->user_id, $congress_id)) {
            return response()->json('no contact found', 404);
        }

        $contact->delete();
        return response()->json('contact deleted', 201);

    }

    public function listContacts(Request $request)
    {

        if (!$user = $this->userServices->retrieveUserFromToken()) {
            return response()->json('no user found', 404);
        }
        $offset = $request->query('offset', 0);
        $perPage = $request->query('perPage', 6);
        $search = $request->query('search', '');
        $congressId = $request->query('eventId');
        $contacts = $this->contactServices->getAllContacts($offset, $perPage, $search, $congressId, $user->user_id);

        return response()->json($contacts, 200);

    }

    public function confirmInscription(Request $request, $userId)
    {
        $code = $request->query('verification_code', '');

        if (!($user = $this->userServices->getUserByVerificationCodeAndId($code, $userId))) {
            return response()->json(['error' => 'user not found'], 404);
        }

        $user->email_verified = 1;
        $user->update();

        return response()->redirectTo(UrlUtils::getBaseUrlFrontOffice() . '/login' . "?valid_account=true");
    }

    public function getUserByCongressIdAndUserId($userId, $congressId)
    {
        if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json('no admin found', 404);
        }
        if (!$admin_congress = $this->adminServices->checkHasPrivilegeByCongress($admin->admin_id, $congressId)) {
            return response()->json('no admin found', 404);
        }
        $admin_id = $admin_congress->privilege_id == config('privilege.Comite_de_selection') ? $admin->admin_id : null;
        $user = $this->userServices->getUserByIdWithRelations($userId, [
            'accesses' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
                $query->where('show_in_register', '=', 1);
            }, 'payments' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            },
            'inscription_evaluation' => function ($query) use ($congressId, $admin_id) {

                $query->select(['user_id', 'note', 'admin_id', 'commentaire', 'evaluation_inscription_id'])->where('congress_id', '=', $congressId);

            },
            'inscription_evaluation.itemNote',
            'inscription_evaluation.admin' => function ($query) {
                $query->select(['admin_id', 'name']);
            },
            'user_congresses' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }, 'user_congresses.congress.itemEvaluation' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }, 'user_congresses.congress.config' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }, 'responses.form_input' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId)
                ->with([ "question_reference"=> function ($query) {
                    $query->with(['reference', 
                    'response_reference'  => function ($q) {
                        $q->with(['value']);
                    } ]);
                },]);
            }, 'responses.values', 'responses.form_input.values',
            'responses.form_input.type', 'packs' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }, 'profile_img',
        ]);

        return response()->json($user);
    }

    public function getUserByCongressIdAndUserIdForPayement($userId, $congressId, Request $request)
    {
        $verification_code = $request->query('verification_code', '');
        $user = $this->userServices->getUserByIdWithRelations($userId, [
            'accesses' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
                $query->where('show_in_register', '=', 1);
            }, 'payments' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            },
            'user_congresses.congress.config',
            'user_congresses' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }, 'responses.form_input' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }, 'responses.values', 'responses.form_input.values',
            'responses.form_input.type',
        ]);
        if ($user->verification_code !== $verification_code) {
            return response()->json('bad request', 400);
        }
        return response()->json($user, 200);

    }

    public function delete($userId, $congressId = null)
    {
        if ($congressId) {
            $this->userServices->deleteUserAccesses($userId, $congressId);
            $this->userServices->deleteFormInputUser($userId, $congressId);
            $this->userServices->deleteUserPacks($userId, $congressId);
            $userCongress = $this->userServices->getUserCongress($congressId, $userId);
            $payment = $this->userServices->getPaymentInfoByUserAndCongress($userId, $congressId);
            $evaluations = $this->userServices->getAllEvaluationInscriptionByUserId($userId, $congressId);
            if ($userCongress) {
                $userCongress->delete();
            }
            if ($payment) {
                $payment->delete();
            }
            foreach ($evaluations as $evaluation) {
                $evaluation->delete();
            }
            return response()->json(['response' => 'user disaffected to congress'], 202);
        } else {
            $this->userServices->deleteById($userId);
            return response()->json(['response' => 'user deleted'], 202);
        }
    }

    public function validateUser($user_id, $validation_code)
    {
        $user = $this->userServices->getParticipatorById($user_id);
        if (!$user) {
            return response()->json(['response' => 'user not found'], 404);
        }
        if ($validation_code === $user->verification_code) {
            $user->email_verified = 1;
            $user->update();
            return response()->json(['response' => 'user verified'], 202);
        }
        return response()->json(['response' => 'invalid verifiaction code'], 400);
    }

    public function getUsersByCongressPagination($congressId, Request $request)
    {
        if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
            return response()->json('no admin found', 404);
        }
        if (!$admin_congress = $this->adminServices->checkHasPrivilegeByCongress($admin->admin_id, $congressId)) {
            return response()->json('no admin found', 404);
        }
        $perPage = $request->query('perPage', 10);
        $search = Str::lower($request->query('search', ''));
        $tri = $request->query('tri', '');
        $order = $request->query('order', '');
        $admin_id = $admin_congress->privilege_id == config('privilege.Comite_de_selection') ? $admin->admin_id : null;
        $users = $this->userServices->getUsersByCongress($congressId, null, true, $perPage, $search, $tri, $order, $admin_id);

        foreach ($users as $user) {
            foreach ($user->accesses as $access) {
                if ($access->pivot->isPresent == 1) {
                    $infoPresence = $this->badgeServices->getAttestationEnabled($user->user_id, $access);
                    $access->attestation_status = $infoPresence['enabled'];
                    $access->time_in_access = $infoPresence['time'];
                } else {
                    $access->attestation_status = 0;
                }

            }
        }
        return response()->json($users);
    }

    public function getUsersByCongress($congressId, $privilegeId)
    {
        if (!$congress = $this->congressServices->getById($congressId)) {
            return response()->json(["error" => "congress not found"], 404);
        }
        $users = $this->userServices->getUsersMinByCongress($congressId, $privilegeId);

        return response()->json($users);
    }

    public function changeMultipleUsersStatus($congress_id, Request $request)
    {
        if (!$admin = $this->adminServices->retrieveAdminFromToken()) {
            return response('no admin found', 404);
        }
        if (!$congress = $this->congressServices->getCongressById($congress_id)) {
            return response()->json('no congress found');
        }
        if (!$request->has('users') || sizeof($request->input('users')) === 0) {
            return response('fields are missing', 404);
        }
        $usersCongress = $this->userServices->getUsersCongressByCongressId($congress_id);
        $users = $request->input('users');
        for ($i = 0; $i < sizeof($users); $i++) {
            $left = 0;
            $right = sizeof($usersCongress) - 1;
            $index = -1;
            while ($left <= $right) {
                $midpoint = (int) floor(($left + $right) / 2);

                if ($usersCongress[$midpoint]['user_id'] < $users[$i]['user_id']) {
                    $left = $midpoint + 1;
                } elseif ($usersCongress[$midpoint]['user_id'] > $users[$i]['user_id']) {
                    $right = $midpoint - 1;
                } else {
                    $index = $midpoint;
                    $status = json_decode($users[$i]['user_congresses'][0]['isSelected']);
                    $this->userServices->changeUserStatus($usersCongress[$midpoint], $status);
                    $this->acceptOrRefuseUser($status, $congress, json_decode(json_encode($users[$i])), $usersCongress[$midpoint]);
                    break;
                }
            }
            if ($index === -1) {
                return response()->json('no user found', 404);
            }
        }
        return response()->json('success', 200);

    }

    public function changeUserStatus($user_id, $congress_id, Request $request)
    {
        if (!$user_congress = $this->userServices->getUserCongress($congress_id, $user_id)) {
            return response()->json(['messsage' => 'no user congress found'], 404);
        }
        if (!$request->has('status')) {
            return response()->json(['message' => 'status is required'], 400);
        }
        $status = $request->input('status');

        $this->userServices->changeUserStatus($user_congress, $status);
        $user = $this->userServices->getUserByIdWithRelations($user_id, ['accesses' => function ($query) use ($congress_id) {
            $query->where('congress_id', '=', $congress_id);
        }]);
        $congress = $this->congressServices->getCongressById($congress_id);
        $this->acceptOrRefuseUser($status, $congress, $user, $user_congress);
        return response()->json(['message' => 'change status success'], 200);
    }

    public function acceptOrRefuseUser($status, $congress, $user, $user_congress)
    {
        if ($status == 1) {
            // Mail acceptation
            $badge = $this->congressServices->getBadgeByPrivilegeId($congress, $user_congress->privilege_id);
            $badgeIdGenerator = $badge['badge_id_generator'];
            $fileAttached = false;
            $fileName = "badge.png";
            if ($badgeIdGenerator != null) {
                $fileAttached = $this->sharedServices->saveBadgeInPublic($badge, $user, $user->qr_code, $user_congress->privilege_id, $congress->congress_id);
            }
            if ($mailtype = $this->congressServices->getMailType('confirmation')) {
                $linkFrontOffice = UrlUtils::getBaseUrlFrontOffice() . '/login';
                $linkPrincipalRoom = UrlUtils::getBaseUrlFrontOffice() . "/room/".$congress->congress_id.'/event-room';
                if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                    $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, null, null, $linkFrontOffice,null,null,null,null,null,null,null,[],null,null,$linkPrincipalRoom ), $user, $congress, $mail->object, $fileAttached, $userMail, null, $fileName);
                }
            }
        } else if ($status == -1) {
            // Mail refus
            if ($mailtype = $this->congressServices->getMailType('refus')) {
                if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                    $this->mailServices->sendMail(
                        $this->congressServices->renderMail($mail->template, $congress, $user, null, null, null), $user, $congress, $mail->object, null, $userMail);
                }
            }
        }
    }

    public function affectScoreToUser($congress_id, $user_id, Request $request)
    {
        if (!($request->has('note') && $request->has('admin_id'))) {
            return response()->json('some fields are missing', 400);
        }
        if (!$evaluation = $this->userServices->getEvaluationInscriptionByUserIdAndAdminId(
            $user_id,
            $congress_id,
            $request->input('admin_id'))) {
            return response()->json('evaluation not found', 404);
        }

        $evaluation = $this->userServices->affectNoteToUser(
            $evaluation,
            $request->input('note'),
            $request->input('commentaire')
        );
        //affect gloable score ;
        $user_congress = $this->userServices->getUserCongress($congress_id, $user_id);
        $avg_note = $this->userServices->getAverageNote($user_id, $congress_id);
        $user_congress->globale_score = $avg_note;
        $user_congress->update();

        return response()->json('Evaluation has been updated successfully', 200);

    }

    public function validateUserAccount($userId = null, $congressId = null, $token = null)
    {
        $user = $this->userServices->getUserById($userId);
        if (!$user) {
            return response()->json(['response' => 'Votre compte à été supprimé'], 404);
        }
        if ($token == $user->verification_code) {
            $user->email_verified = 1;
            $user->update();

            return response()->redirectTo(UrlUtils::getUrlEventizerWeb() . "/#/auth/user/" . $user->user_id . "/upload-payement?token=" . $token . "&congressId=" . $congressId);
        } else {
            return response()->json(['response' => 'Token not match'], 400);
        }
    }

    public function addUserToCongress(Request $request, $congressId)
    {

        $accessIds = $request->input("accessIds");
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['error' => 'congress not found'], 404);
        }
        $user = $this->userServices->addParticipant($request, $congressId);
        $this->userServices->affectAccess($user->user_id, $accessIds, $user->pack->accesses);

        return response()->json(['add success'], 200);
    }

    public function getAllUsersByCongress($congress_id, Request $request)
    {
        $privilegeId = $request->query('privilege_id', null);

        $users = $this->userServices->getAllUsersByCongress($congress_id, $privilegeId);

        return response()->json($users);
    }

    public function registerUser(Request $request)
    {
        if (!$request->has(['email', 'first_name', 'last_name', 'password'])) {
            return response()->json(['response' => 'bad request', 'required fields' => ['email', 'first_name', 'last_name', 'password']], 400);
        }

        // Get User per mail
        if (!$user = $this->userServices->getUserByEmail($request->input('email'))) {
            $user = $this->userServices->saveUser($request);
            // TODO Sending Confirmation Mail

            if ($mailAdminType = $this->mailServices->getMailTypeAdmin('confirmation')) {
                $activationLink = $activationLink = UrlUtils::getBaseUrl() . '/users/confirmInscription/' . $user->user_id . '?verification_code=' . $user->verification_code;
                if ($mail = $this->mailServices->getMailAdmin($mailAdminType->mail_type_admin_id)) {
                    $userMail = $this->mailServices->addingUserMailAdmin($mail->mail_admin_id, $user->user_id);
                    $this->mailServices->sendMail($this->adminServices->renderMail($mail->template, null, $user, $activationLink), $user, null, $mail->object, null, $userMail);
                }
            }
        } else {
            $user = $this->userServices->editUser($request, $user);
        }

        return response()->json($user);
    }

    public function saveUserInscription(Request $request, $congress_id)
    {
        $packId = $request->input('packIds', []);
        $accessesIds = $request->input('accessesId', []);
        $privilegeId = config('privilege.Participant');
        $user = $this->userServices->retrieveUserFromToken();
        if (!$user) {
            return response()->json(['response' => 'No user found'], 404);
        }
        $congress = $this->congressServices->getCongressById($congress_id);
        if (!$congress) {
            return response()->json(['response' => 'No congress found'], 404);
        }

        // Check if User already registed to congress
        if ($user_congress = $this->userServices->getUserCongress($congress_id, $user->user_id)) {
            return response()->json(['error' => 'user registred congress'], 405);
        }

        // Affect User to Congress
        $user_congress = $this->userServices->saveUserCongress($congress_id, $user->user_id, $privilegeId, null, null);

        $this->handleCongressInscription($request, $privilegeId, $user, $congress, $congress_id, $packId, $accessesIds, $user_congress);

        return response()->json(['response' => 'Inscrit avec succès'], 200);
    }

    public function saveUser(Request $request, $congress_id)
    {

        if (!$request->has(['email', 'privilege_id', 'first_name', 'last_name'])) {
            return response()->json(['response' => 'bad request', 'required fields' => ['email', 'privilege_id', 'first_name', 'last_name']], 400);
        }

        $privilegeId = $request->input('privilege_id');

        if ($request->has('avatar_id') && $privilegeId != config('privilege.Organisme')) {
            $request->merge(['avatar_id' => null]);
        }
        //check if date limit
        // Get User per mail
        $resource = $request->has('resource_id') ? $resource = $this->resourcesServices->getResourceByResourceId($request->input('resource_id')) : null;

        if (!$user = $this->userServices->getUserByEmail($request->input('email'))) {
            $user = $this->userServices->saveUser($request, $resource);
        } else {
            $user = $this->userServices->editUser($request, $user);
        }

        // Check if User already registed to congress
        if ($user_congress = $this->userServices->getUserCongress($congress_id, $user->user_id)) {
            return response()->json(['error' => 'user registred congress'], 405);
        }

        $congress = $this->congressServices->getCongressById($congress_id);

        if (!$congress) {
            return response()->json(['response' => 'No congress found'], 404);
        }

        // Affect User to Congress
        $user_congress = $this->userServices->saveUserCongress($congress_id, $user->user_id, $request->input('privilege_id'), $request->input('organization_id'), $request->input('pack_id'));

        $packId = $request->input('packIds', []);
        $accessesIds = $request->has('accessIds') ? $request->input('accessIds', []) : $request->input('accessesId', []);
        $this->handleCongressInscription($request, $privilegeId, $user, $congress, $congress_id, $packId, $accessesIds, $user_congress);
        return response()->json(['response' => 'Inscrit avec succès'], 200);
    }

    public function editerUserToCongress(Request $request, $congressId, $userId)
    {
        if (!$request->has(['email', 'privilege_id', 'first_name', 'last_name'])) {
            return response()->json(['response' => 'bad request', 'required fields' => ['email', 'privilege_id', 'first_name', 'last_name']], 400);
        }

        $privilegeId = $request->input('privilege_id');
        if ($privilegeId == 3 && !$request->has('price')) {
            return response()->json(['response' => 'bad request', 'required fields' => ['price']], 400);
        }

        if ($request->has('avatar_id') && $privilegeId != config('privilege.Organisme')) {
            $request->merge(['avatar_id' => null]);
        }

        // Get User perId
        $user = $this->userServices->getUserByIdWithRelations($userId, [
            'accesses' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }, 'user_packs'
            , 'payments' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }, 'user_congresses' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }, 'responses.form_input' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId)
                ->with([ "question_reference"=> function ($query) {
                    $query->with(['reference', 
                    'response_reference'  => function ($q) {
                        $q->with(['value']);
                    } ]);
                },]);
            }, 'responses.values', 'responses.form_input.values',
            'responses.form_input.type',
        ]);

        if (!$user) {
            return response()->json(['error' => 'user not found'], 404);
        } else {
            $this->userServices->editUser($request, $user);
        }
        //Delete Data inutile
        //$user->responses->delete();
        $this->userServices->deleteFormInputUser($userId, $congressId);

        if (sizeof($user->payments) > 0 && $request->has("price")) {
            $user->payments[0]->price = $request->input("price");
            $user->payments[0]->update();
        } else {
            if ($privilegeId == config('privilege.Participant') && $request->input("price") != 0) {
                $this->paymentServices->affectPaymentToUser($user->user_id, $congressId, $request->input("price"), false);
            }

        }

        if ($privilegeId != config('privilege.Participant') && sizeof($user->payments) > 0) {
            $user->payments[0]->delete();
        }

        $this->userServices->updateUserCongress($user->user_congresses[0], $request);

        //Adding Responses User To Form (Additional Information)
        if ($request->has('responses')) {
            $this->userServices->saveUserResponses($request->input('responses'), $user->user_id);
        }

        $accessIds = $request->input('accessIds');
        //Save Access Premium
        $userAccessIds = $this->accessServices->getAccessIdsByAccess($user->accesses);

        if ($privilegeId != config('privilege.Participant')) {
            $packs = $this->packServices->getAllPackByCongress($congressId);
            $packIds = $this->packServices->getPackIdsByPacks($packs);
            $this->packServices->editUserPacksWithPackId($userId, $user->user_packs, $packIds);
            $allAccess = $this->accessServices->getMainByCongressId($congressId);
            $accessIds = $this->accessServices->getAccessIdsByAccess($allAccess);
        } else {

            $this->packServices->editUserPacksWithPackId($userId, $user->user_packs, $request->input('packIds'));
            $accessNotInRegister = $this->accessServices->getAllAccessByRegisterParams($congressId, 0, 0);
            $accessNotInRegisterIds = $this->accessServices->getAccessIdsByAccess($accessNotInRegister);
            $accessIds = array_merge($accessIds, $accessNotInRegisterIds);
            $accessInPackNotInRegister = $this->accessServices->getAllAccessByPackIds(
                $user->user_id,
                $congressId,
                $request->input('packIds'),
                1,
                0
            );
            $accessInPackNotInRegisterIds = $this->accessServices->getAccessIdsByAccess($accessInPackNotInRegister);
            $accessIds = array_merge($accessIds, $accessInPackNotInRegisterIds);
        }

        if ($accessIds && array_count_values($accessIds)) {
            //$accessIds = array_merge($accessIds, array_diff($accessIdsIntutive, $accessIds));
            $accessDiffDeleted = array_diff($userAccessIds, $accessIds);
            $accessDiffAdded = array_diff($accessIds, $userAccessIds);
            $this->userServices->affectAccess($user->user_id, $accessDiffAdded, []);
            $this->userServices->deleteAccess($user->user_id, $accessDiffDeleted);
        } else if ($userAccessIds && array_count_values($userAccessIds)) {
            $this->userServices->deleteAccess($user->user_id, $userAccessIds);
        }

        return response()->json($user, 200);
    }

    public function checkUserRights($congressId, $accessId = null)
    {
        $user = $this->userServices->retrieveUserFromToken();
        if (!$user) {
            return response()->json(['response' => 'No user found'], 401);
        }
        $userId = $user->user_id;
        $congress = $this->congressServices->getCongressById($congressId);
        $user = $this->userServices->getUserByIdWithRelations($userId, [
            'user_congresses' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            },
            'payments' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            },
            'accesses' => function ($query) use ($congressId, $accessId) {
                $query->where('Access.access_id', '=', $accessId)->where('congress_id', '=', $congressId);
            },
            'user_access' => function ($query) use ($userId, $accessId) {
                $query->where('user_id', '=', $userId)->where('access_id', '=', $accessId);
            }]);

        $privilege = $this->privilegeServices->getPrivilegeById($user->user_congresses[0]->privilege_id);
        $isModerator = $this->userServices->isUserModerator($privilege);
        if (!$isModerator && !Utils::isValidSendMail($congress, $user) || ($accessId && sizeof($user->accesses) == 0)) {
            return response()->json(['response' => 'not authorized'], 401);
        }
        
        if (!$accessId) {
            $isAllowedJitsi = $congress->config->max_online_participants && $congress->config->url_streaming ? $congress->config->max_online_participants >= $congress->config->nb_current_participants : true;
            $urlStreaming = $congress->config->url_streaming;
        } else {
            $access = $this->accessServices->getAccessById($accessId);
            $isAllowedJitsi = $congress->config->max_online_participants && $access->url_streaming ? $congress->config->max_online_participants >= $access->nb_current_participants : true;
            $urlStreaming = $access->url_streaming;
        }
        $allowedOnlineAccess = $this->congressServices->getAllAllowedOnlineAccess($congressId);
        if (count($allowedOnlineAccess) != 0 && $urlStreaming) {
            $isAllowedJitsi = $this->congressServices->getAllowedOnlineAccessByPrivilegeId($congressId, $user->user_congresses[0]->privilege_id) ? true : false;
        }

        $userToUpdate = $accessId ? $user->user_access[0] : $user->user_congresses[0];
        $roomName = $accessId ? 'eventizer_room_' . $congressId . $accessId : 'eventizer_room_' . $congressId;
        if ($congress->config && $congress->config->is_agora) {
            $token = $this->roomServices->createTokenAgora($user->user_id . '_' .$user->first_name . '_' . $user->last_name , $roomName, $isModerator);
        } else {
            $token = $this->roomServices->createToken($user->email, $roomName, $isModerator, $user->first_name . " " . $user->last_name);
        }
        
        $userToUpdate->token_jitsi = $token;
        $userToUpdate->update();

        return response()->json(
            [
                "type" => $congress->config && $congress->config->is_agora ? "agora" : "jitsi",
                "token" => $token,
                "is_moderator" => $isModerator,
                "privilege_id" => $user->user_congresses[0]->privilege_id,
                "allowed" => $isAllowedJitsi,
                "url_streaming" => $urlStreaming,
            ], 200);

    }

    public function getAllUserAccess($congressId)
    {
        $user = $this->userServices->retrieveUserFromToken();
        if (!$user) {
            return response()->json(['message' => 'no user found'], 400);
        }
        $userId = $user->user_id;
        return $this->userServices->getAllUserAccess($congressId, $userId);
    }

    public function getUsersByAccess($congressId, $accessId)
    {
        $users = $this->userServices->getUsersByAccess($congressId, $accessId);

        return response()->json($users);
    }

    public function getPresencesByAccess($accessId)
    {
        $users = $this->userServices->getPresencesByAccess($accessId);

        return response()->json($users);
    }

    public function getPresencesByCongress($congressId)
    {
        $users = $this->userServices->getAllPresencesByCongress($congressId);

        return response()->json($users);
    }

    public function getQrCodeUser($userId)
    {
        if (!$user = $this->userServices->getUserById($userId)) {
            return response()->json(["error" => "user not found"], 404);
        }
        $file = new Filesystem();

        Utils::generateQRcode($user->qr_code, "qrcode.png");

        if ($file->exists(public_path() . "/qrcode.png")) {
            return response()->download(public_path() . "/qrcode.png")
                ->deleteFileAfterSend(true);
        } else {
            return response()->json(["error" => "dossier vide"]);
        }
    }

    public function getAllPayementTypes()
    {
        return response()->json($this->paymentServices->getAllPaymentTypes());
    }

    public function getUserStatusPresences($congressId, Request $request)
    {
        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['error' => 'congress not found'], 404);
        }
        $autorisation = $request->input('autorisation');
        $accessId = $request->input("accessId");
        if ($accessId) {
            if (!$access = $this->accessServices->getById($accessId)) {
                return response()->json(['error' => 'access not found'], 404);
            }
            $usersAccess = $this->accessServices->getUserAccessByAccessId($accessId);
            $result = array();
            foreach ($usersAccess as $user) {
                /*if ($user->isPresent == $autorisation)
                array_push($result, $user);*/

                //TODO return after congress
                if ($this->badgeServices->getAttestationEnabled($user->user_id, $access)['enabled'] == $autorisation) {
                    array_push($result, $user);
                }
            }
            return response()->json($result);
        } else {
            $userCongress = $this->congressServices->getUsersByStatus($congressId, $autorisation);
            return response()->json($userCongress);
        }
    }

    public function changePaiement($paymentId, Request $request)
    {
        $isPaid = $request->input('status');

        if (!$userPayement = $this->userServices->getPaymentById($paymentId)) {
            return response()->json(['error' => 'payment not found'], 404);
        }

        $congressId = $userPayement->congress_id;
        if (!$user = $this->userServices->getUserByIdWithRelations($userPayement->user_id, ['accesses' => function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId);
        }])) {
            return response()->json(['error' => 'user not found'], 404);
        }

        $congress = $this->congressServices->getCongressById($userPayement->congress_id);

        $userCongress = $this->userServices->getUserCongress($congress->congress_id, $user->user_id);

        if ($userPayement->isPaid != 1 && $isPaid == 1) {
            $badge = $this->congressServices->getBadgeByPrivilegeId($congress, $userCongress->privilege_id);
            $badgeIdGenerator = $badge['badge_id_generator'];
            $fileAttached = false;
            $fileName = "badge.png";
            if ($badgeIdGenerator != null) {
                $fileAttached = $this->sharedServices->saveBadgeInPublic(
                    $badge,
                    $user,
                    $user->qr_code,
                    $userCongress->privilege_id,
                    $congress->congress_id
                );
            }

            // $link = Utils::baseUrlWEB . "/#/auth/user/" . $user->user_id . "/manage-account?token=" . $user->verification_code;
            /*if ($mailtype = $this->congressServices->getMailType('paiement')) {
            if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
            $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
            $this->userServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, $userPayement), $user, $congress, $mail->object, null, $userMail);
            }
            }*/

            if ($mailtype = $this->congressServices->getMailType('confirmation')) {
                $linkFrontOffice = UrlUtils::getBaseUrlFrontOffice() . '/login';
                $linkPrincipalRoom = UrlUtils::getBaseUrlFrontOffice() . "/room/".$congress->congress_id.'/event-room';
                if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                    $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, $userPayement, null, $linkFrontOffice,null,null,null,null,null,null,null,[],null,null,null,null,$linkPrincipalRoom), $user, $congress, $mail->object, $fileAttached, $userMail, null, $fileName);
                }
            }
            $this->smsServices->sendSmsToUsers($congress->congress_id, $user, $congress);
        }
        $userPayement->isPaid = $isPaid;
        $userPayement->update();

        return response()->json(['message' => 'user updated success']);
    }

    public function uploadUsers(Request $request)
    {

        ini_set('max_execution_time', 500);
        $savedUsers = array();
        $users = $request->input("data");
        foreach ($users as $userData) {
            if ($userData['first_name'] && $userData['last_name'] && $userData['mobile'] && $userData['email']) {
                $request->merge([
                    'first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name'],
                    'email' => $userData['email'],
                    'mobile' => $userData['mobile'],
                ]);
                if (!$user = $this->userServices->getUserByEmail($userData['email'])) {
                    $user = $this->userServices->saveUser($request);
                    array_push($savedUsers, $user->user_id);
                } else {
                    array_push($savedUsers, $user->user_id);
                }
            }
        }
        return $savedUsers;
    }

    public function saveUsersFromExcel($congressId, Request $request)
    {
        ini_set('max_execution_time', 500); //3 minutes

        $congress = $this->congressServices->getById($congressId);
        $users = $request->input("data");
        $refused = $request->query('refused');
        if ($refused == "false") {
            $refused = false;
        } else {
            $refused = true;
        }
        //PrivilegeId = 3
        $sum = 0;
        $privilegeId = $request->input("privilegeId");
        $organizationId = $request->input("organisationId");
        $emails = [];
        $accessIdTable = [];
        foreach ($users as $e) {
            if(isset($e['email']))
            {
            $emails[] = $e["email"];
            $accessIdTable[] = $e["accessIdTable"];
            }
        }

        // Affect All Access Free (To All Users)
        $accessNotInRegister = $this->accessServices->getAllAccessByRegisterParams($congressId, 0);
        $accessInRegister = $this->accessServices->getAllAccessByRegisterParams($congressId, 1);
        $accessIds = $this->accessServices->getAccessIdsByAccess($accessNotInRegister);

        foreach ($users as $userData) {
            if (isset($userData['email'])) {

                $request->merge(['privilege_id' => $privilegeId,
                    'email' => $userData['email'],
                ]);
                // Create user if it doesn't exist
                if (!$this->userServices->getUserByEmail($userData['email'])) {
                    $user = $this->userServices->addUserFromExcel($userData);
                }
                // Get User per mail
                if ($user_by_mail = $this->userServices->getUserByEmail($userData['email'])) {
                    $user_id = $user_by_mail->user_id;
                    $user = $this->userServices->getUserByIdWithRelations($user_id, [
                        'accesses' => function ($query) use ($congressId) {
                            $query->where('congress_id', '=', $congressId);
                            $query->where('show_in_register', '=', 1);
                        }, 'payments' => function ($query) use ($congressId) {
                            $query->where('congress_id', '=', $congressId);
                        },
                        'user_congresses' => function ($query) use ($congressId) {
                            $query->where('congress_id', '=', $congressId);
                        },
                    ]);
                    // Check if User already registed to congress
                    $user_congress = $this->userServices->getUserCongress($congressId, $user->user_id);
                    if (!$user_congress) {
                        if ($accessNotInRegister) {
                            $this->userServices->affectAccessIds($user->user_id, $accessNotInRegister);
                        }
                        $user_congress = $this->userServices->saveUserCongress($congressId, $user->user_id, $request->input('privilege_id'), $request->input('organization_id'), $request->input('pack_id'));
                        if ($congress->congress_type_id == 1) { // If event type payed affect payment user
                            $this->paymentServices->affectPaymentToUser($user->user_id, $congressId, 0, false);
                        }
                    } else {
                        $user_congress->privilege_id = $privilegeId;
                        $user_congress->update();
                    }

                    $new_access_array = null;
                    $old_access_array = $user->accesses;
                    for ($i = 0; $i < sizeof($emails); $i++) {
                        //if statement to get the right index i of accessIdTable corresponding to our user
                        if ($emails[$i] == $userData['email']) {
                            //put all new accesses ID in the new access array
                            $new_access_array = $accessIdTable[$i];
                        };
                    }

                    if ($new_access_array) {
                        //add new accesses if not already existant
                        for ($j = 0; $j < sizeof($new_access_array); $j++) {
                            $exists = false;
                            //check if the user already has this access_id
                            foreach ($old_access_array as $old_access) {
                                if ($old_access->access_id == $new_access_array[$j]) {
                                    $exists = true;
                                }
                            }
                            if (!$exists) {
                                // this means we have a new access to add
                                // add the new access
                                $this->userServices->affectAccessById($user->user_id, $new_access_array[$j]);
                            }

                        }
                        //delete the access if no longer exists on the excel sheet
                        // we loop in the old access aray
                        for ($j = 0; $j < sizeof($old_access_array); $j++) {
                            $exists = false; // initialise to doesn't exist
                            for ($k = 0; $k < sizeof($new_access_array); $k++) {
                                //search if access exists in the new access table if it does we won't delete it
                                //otherwise we have to delete it
                                if ($old_access_array[$j]->access_id == $new_access_array[$k]) {
                                    $exists = true; // access exists in both array new and old
                                }
                            }
                            if (!$exists) {
                                //delete where access_id== $j from the old access array
                                $this->userServices->deleteAccessById($user->user_id, $old_access_array[$j]->access_id);
                            }
                        }
                    } else {
                        //new access_array empty
                        //there is no new accesses
                        //delete all current access traitement
                        for ($k = 0; $k < sizeof($old_access_array); $k++) {
                            $this->userServices->deleteAccessById($user->user_id, $old_access_array[$k]->access_id);
                        }
                    }

                    if ($refused) {
                        if ($congress->congress_type_id == 2) {
                            $this->userServices->changeUserStatus($user_congress, 1);
                        }
                        if ($congress->congress_type_id == 1) {
                            $this->paymentServices->changeIsPaidStatus($user->user_id, $congressId, 1);
                        }
                    }
                    
                    if ($congress->config_selection && $congress->config_selection->num_evaluators > 0 && $privilegeId == config('privilege.Participant') && ($congress->congress_type_id == 2 || ($congress->congress_type_id == 1 && $congress->config_selection))) {
                        $evaluations = $this->adminServices->getEvaluationInscription($congressId, $user->user_id);
                        if (count($evaluations) == 0) {
                            $evalutors = $this->adminServices->getEvaluatorsByCongress($congressId, 13, 'evaluations');
                            $this->adminServices->affectEvaluatorsToUser(
                                $evalutors,
                                $congress->config_selection->num_evaluators,
                                $congressId,
                                $user->user_id
                            );
                        }
                    }
                }
            }
        }

        $formInputs = $this->registrationFormServices->getForm($congressId);

        foreach ($users as $user) {
            foreach ($formInputs as $input) {
                $arrayKeys = array_keys($user);
                foreach ($arrayKeys as $key) {
                    if ($input->key == $key) {
                        // delete old response
                        $user_by_mail = $this->userServices->getUserByEmail($user['email']);
                        $this->userServices->deleteFormInputUserByKey($user_by_mail->user_id, $congressId, $key);
                        // add new response
                        $reponse = new FormInputResponse();
                        $reponse->user_id = $user_by_mail->user_id;
                        $reponse->form_input_id = $input->form_input_id;

                        if ($input->form_input_type_id == 6 || $input->form_input_type_id == 8 || $input->form_input_type_id == 7 || $input->form_input_type_id == 9) {
                            $formInputValues = $this->userServices->getFormInputValues($input->form_input_id);
                            if ($input->form_input_type_id == 6 || $input->form_input_type_id == 8) {
                                $reponse->response = '';
                                $reponse->save();
                                $user_responses = explode(";", $user[$key]);
                                foreach ($user_responses as $res) {
                                    foreach ($formInputValues as $value) {
                                        if ($value->value == $res) {
                                            $this->userServices->addResponseValue($reponse->form_input_response_id, $value->form_input_value_id);
                                        }
                                    }
                                }
                            } else {
                                $reponse->response = '';
                                $reponse->save();
                                $user_responses = explode(";", $user[$key]);
                                foreach ($formInputValues as $value) {
                                    if ($value->value == $user_responses[0]) {
                                        $this->userServices->addResponseValue($reponse->form_input_response_id, $value->form_input_value_id);
                                        break;
                                    }
                                }
                            }
                        } else {
                            $reponse->response = $user[$key] == '-' ? null : $user[$key];
                            $reponse->save();
                        }

                    }
                }
            }
        }

        if ($refused) {
            // partie gestion des participants refusés !
            $all_refused_participants = $this->userServices->getRefusedParticipants($congressId, $emails);
            foreach ($all_refused_participants as $refused_participant) {
                //change user payment status
                if ($congress->congress_type_id == 2 && sizeof($refused_participant->user_congresses) > 0 && $refused_participant->user_congresses[0]->isSelected != 1) {
                    $this->userServices->changeUserStatus($refused_participant->user_congresses[0], -1);
                }
                if ($congress->congress_type_id == 1 && sizeof($refused_participant->payments) > 0 && $refused_participant->payments[0]->isPaid != 1) {
                    $this->paymentServices->changeIsPaidStatus($refused_participant->user_id, $congressId, -1);
                }

                //envoi de mail de refus
                if ($mailtype = $this->congressServices->getMailType('refus')) {
                    if ($mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id)) {
                        $userMail = $this->mailServices->addingMailUser($mail->mail_id, $refused_participant->user_id);
                        $this->mailServices->sendMail(
                            $this->congressServices->renderMail($mail->template, $congress, $refused_participant, null, null, null),
                            $refused_participant,
                            $congress,
                            $mail->object,
                            null,
                            $userMail
                        );
                    }
                }
            }
        }

        if ($organizationId != null) {
            $organization = $this->organizationServices->getOrganizationById($organizationId);
            $organization->montant = $organization->montant + $sum;
            $organization->update();
            return response()->json($organization);
        } else {
            return response()->json(['message' => 'import success']);
        }

    }

    public function redirectToLinkFormSondage($userId, $congressId)
    {

        $congress = $this->congressServices->getCongressById($congressId);
        $mailtype = $this->congressServices->getMailType('attestation');
        $mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id);
        $mailId = $mail->mail_id;
        /* Meme Block Of Send Attestation */
        if (!$user = $this->userServices->getUserByIdWithRelations($userId, ['accesses' => function ($query) use ($congressId) {
            $query->where("congress_id", "=", $congressId);
            $query->where('with_attestation', "=", 1);
        }, 'payments' => function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId);
        }, 'user_congresses' => function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId);
        },
            'user_mails' => function ($query) use ($mailId) {
                $query->where('mail_id', '=', $mailId);
            }])) {
            return response()->json(['error' => 'user not found'], 404);
        }

        $request = array();
        if (Utils::isValidSendMail($congress, $user)) {
            if (sizeof($user->user_congresses) > 0 && $user->user_congresses[0]->isPresent == 1 && $congress->attestation) {
                array_push(
                    $request,
                    array(
                        'badgeIdGenerator' => $congress->attestation->attestation_generator_id,
                        'name' => Utils::getFullName($user->first_name, $user->last_name),
                        'qrCode' => false,
                    )
                );
            }
            foreach ($user->accesses as $access) {
                if ($access->pivot->isPresent == 1) {
                    if (sizeof($access->attestations) > 0) {
                        $attestationId = Utils::getAttestationByPrivilegeId($access->attestations, config('privilege.Participant'));
                        if ($attestationId) {
                            array_push(
                                $request,
                                array(
                                    'badgeIdGenerator' => $attestationId,
                                    'name' => Utils::getFullName($user->first_name, $user->last_name),
                                    'qrCode' => false,
                                )
                            );
                        }
                    }
                }
                $chairPerson = $this->accessServices->getChairAccessByAccessAndUser($access->access_id, $userId);
                $privilegeId = null;
                if ($chairPerson) {
                    $privilegeId = config('privilege.Moderateur');
                }
                $speakerPerson = $this->accessServices->getSpeakerAccessByAccessAndUser($access->access_id, $userId);
                if ($speakerPerson) {
                    $privilegeId = config('privilege.Conferencier_Orateur');
                }
                $attestationId = null;
                if ($privilegeId) {
                    $attestationId = Utils::getAttestationByPrivilegeId($access->attestations, $privilegeId);
                }

                if ($attestationId) {
                    array_push(
                        $request,
                        array(
                            'badgeIdGenerator' => $attestationId,
                            'name' => Utils::getFullName($user->first_name, $user->last_name),
                            'qrCode' => false,
                        )
                    );
                }
            }

            if ($mail) {
                $userMail = null;
                if (sizeof($user->user_mails) == 0) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                } else {
                    $userMail = $user->user_mails[0];
                }
                if ($userMail->status != 1) {
                    $fileName = 'attestations.zip';
                    $this->badgeServices->saveAttestationsInPublic($request);
                    $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, null),
                        $user, $congress, $mail->object, true, $userMail, null, $fileName);
                }
            }
        } else {
            return response()->json(['error' => 'user not present or empty email'], 501);
        }

        /* Block Sending Sondage */
        $linkForm = $congress->config->link_sondage;

        return response()->redirectTo($linkForm);
    }

    public function sendSondage($userId, $congressId)
    {

        if (!$user = $this->userServices->getUserByIdWithRelations($userId, [])) {
            return response()->json(['error' => 'user not found'], 404);
        }
        $congress = $this->congressServices->getCongressById($congressId);

        if ($user->email != null && $user->email != "-" && $user->email != "") {

            $mailtype = $this->congressServices->getMailType('sondage');
            $mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id);

            $linkSondage = UrlUtils::getBaseUrl() . "/users/" . $user->user_id . '/congress/' . $congressId . '/sondage';
            if ($mail) {
                $userMail = $this->mailServices->getMailByUserIdAndMailId($mail->mail_id, $user->user_id);
                if (!$userMail) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                }

                $this->mailServices->sendMail(
                    $this->congressServices->renderMail($mail->template, $congress, $user, null, null, null, $linkSondage),
                    $user,
                    $congress,
                    $mail->object,
                    false,
                    $userMail
                );
            }
        } else {
            return response()->json(['error' => 'user not present or empty email'], 501);
        }
        return response()->json(['message' => 'email sended success']);
    }

    public function sendMailAttesation($userId, $congressId, $strict = 1)
    {
        // $strict = 0;
        if (!$user = $this->userServices->getUserByIdWithRelations($userId, ['accesses' => function ($query) use ($congressId) {
            $query->where("congress_id", "=", $congressId);
            $query->where('with_attestation', "=", 1);
        }, 'user_congresses' => function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId);
        }])) {
            return response()->json(['error' => 'user not found'], 404);
        }

        $congress = $this->congressServices->getCongressById($congressId);
        $request = array();
        if ($user->email != null && $user->email != "-" && $user->email != "") {
            if (sizeof($user->user_congresses) > 0 && $user->user_congresses[0]->isPresent == 1 && $congress->attestation) {
                array_push(
                    $request,
                    array(
                        'badgeIdGenerator' => $congress->attestation->attestation_generator_id,
                        'name' => Utils::getFullName($user->first_name, $user->last_name),
                        'qrCode' => false,
                    )
                );
            }
            foreach ($user->accesses as $access) {
                if ($strict == 0 || $access->pivot->isPresent == 1) {
                    if (sizeof($access->attestations) > 0) {
                        $attestationId = Utils::getAttestationByPrivilegeId($access->attestations, config('privilege.Participant'));
                        if ($attestationId) {
                            array_push(
                                $request,
                                array(
                                    'badgeIdGenerator' => $attestationId,
                                    'name' => Utils::getFullName($user->first_name, $user->last_name),
                                    'qrCode' => false,
                                )
                            );
                        }
                    }
                }
                $chairPerson = $this->accessServices->getChairAccessByAccessAndUser($access->access_id, $userId);
                $privilegeId = null;
                if ($chairPerson) {
                    $privilegeId = config('privilege.Moderateur');
                }
                $speakerPerson = $this->accessServices->getSpeakerAccessByAccessAndUser($access->access_id, $userId);
                if ($speakerPerson) {
                    $privilegeId = config('privilege.Conferencier_Orateur');
                }
                $attestationId = null;
                if ($privilegeId) {
                    $attestationId = Utils::getAttestationByPrivilegeId($access->attestations, $privilegeId);
                }

                if ($attestationId) {
                    array_push(
                        $request,
                        array(
                            'badgeIdGenerator' => $attestationId,
                            'name' => Utils::getFullName($user->first_name, $user->last_name),
                            'qrCode' => false,
                        )
                    );
                }
            }

            $mailtype = $this->congressServices->getMailType('attestation');
            $mail = $this->congressServices->getMail($congress->congress_id, $mailtype->mail_type_id);

            if ($mail) {
                $userMail = $this->mailServices->getMailByUserIdAndMailId($mail->mail_id, $user->user_id);
                if (!$userMail) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                }
                $fileName = 'attestations.zip';
                $this->badgeServices->saveAttestationsInPublic($request);
                $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, null),
                    $user, $congress, $mail->object, true, $userMail, null, $fileName);
            }
        } else {
            return response()->json(['error' => 'user not present or empty email'], 501);
        }
        return response()->json(['message' => 'email sended success']);
    }

    public function updateUserPayment($userId, $congressId, Request $request)
    {
        if (!$paymentUser = $this->userServices->getPaymentByUserId($congressId, $userId)) {
            return response()->json(['error' => 'user not found'], 404);
        }

        $paymentUser = $this->userServices->updateUserPayment($paymentUser, $request->input('path'));

        $user = $this->userServices->getUserById($userId);

        if ($mailtype = $this->congressServices->getMailType('upload')) {
            if ($mail = $this->congressServices->getMail($paymentUser->congress_id, $mailtype->mail_type_id)) {
                $congress = $this->congressServices->getCongressById($paymentUser->congress_id);
                $userMail = $this->mailServices->addingMailUser($mail->mail_id, $paymentUser->user_id);
                $this->mailServices->sendMail(
                    $this->congressServices
                        ->renderMail($mail->template, $congress, $user, null, null, null),
                    $user,
                    $congress,
                    $mail->object,
                    false,
                    $userMail
                );
            }
        }

        return response()->json($user);
    }

    public function calculPrice($congress, $packId, $accessIds)
    {
        $price = $congress->price;
        if ($packId) {
            $pack = $this->packServices->getPackById($packId);
            $price += $pack->price;
        }
        $accesss = $this->accessServices->getAllAccessByAccessIds($accessIds);
        if (count($accesss)) {
            $price += array_sum(array_map(function ($access) {
                return $access["price"];
            }, $accesss->toArray()));
        }

        return $price;
    }

    public function sendCustomMail($user_id, $mail_id, $congress_id)
    {
        if (!$user = $this->userServices->getParticipatorById($user_id)) {
            return response()->json(['response' => 'user not found'], 404);
        }

        if (!$mail = $this->congressServices->getEmailById($mail_id)) {
            return response()->json(['response' => 'mail not found'], 404);
        }
        $congress = $this->congressServices->getCongressById($congress_id);
        $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, null), $user, $congress, $mail->object, false);
        return response()->json(['response' => 'success'], 200);
    }

    public function userConnect($qrCode)
    {
        $user = $this->userServices->getUserByQrCode($qrCode);
        return $user ? response()->json($user, 200, []) : response()->json(["error" => "wrong qrcode"], 404);
    }

    public function userConnectPost(Request $request)
    {
        if (!$request->has('qr_code')) {
            return response()->json(['error' => 'bad reques'], 400);
        }

        $user = $this->userServices->getUserByQrCode($request->qr_code);

        $request->merge(['email' => $user->email, 'password' => $user->passwordDecrypt]);

        $credentials = request(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'invalid credentials'], 401);
        }

        return response()->json(['user' => $user, 'token' => $token], 200);
    }

    public function getPresenceStatus($user_id)
    {
        $table = [];
        foreach ($this->userServices->getUserById($user_id)->accesss as $access) {
            array_push($table, $access->pivot);
        }
        return $table;
    }

    public function getAllPresenceStatus(Request $request)
    {
        $table = [];
        foreach ($request->all() as $user_id) {
            $table = array_merge($table, $this->getPresenceStatus($user_id));
        }
        return $table;
    }

    public function requestAttestations(Request $request, $user_id)
    {
        if (!$this->userServices->getUserById($user_id)) {
            return response()->json(['error' => 'user_does_not_exist'], 404);
        }

        $res = [];
        $oldRequests = $this->userServices->getAttestationRequestsByUserId($user_id);
        foreach ($request->all() as $access_id) {
            if (!$this->userServices->isRegisteredToAccess($user_id, $access_id)) {
                continue;
            }

            $already_exists = false;
            foreach ($oldRequests as $oldRequest) {
                if ($oldRequest->access_id == $access_id) {
                    $already_exists = true;
                    array_push($res, $oldRequest);
                }
            }
            if ($already_exists) {
                continue;
            }

            $attestation_request = new AttestationRequest();
            $attestation_request->access_id = $access_id;
            $attestation_request->user_id = (int) $user_id;
            $attestation_request->save();
            array_push($res, $attestation_request);
        }
        return response()->json($res, 200);
    }

    public function requestedAttestations(Request $request)
    {
        $res = [];
        foreach ($request->all() as $user_id) {
            $temp = $this->userServices->getAttestationRequestsByUserId($user_id);
            if ($temp && count($temp)) {
                $res = array_merge($res, $temp);
            }

        }
        return $res;
    }

    public function setAttestationRequestStatus($user_id, $done)
    {
        $requests = $this->userServices->getAttestationRequestsByUserId($user_id);
        foreach ($requests as $req) {
            $req->done = $done ? 1 : 0;
            $req->update();
        }
        return $this->userServices->getAttestationRequestsByUserId($user_id);
    }

    public function mobileEditUser(Request $request, $user_id)
    {
        if (!$request->has(['first_name', 'last_name', 'gender', 'mobile', 'email', 'country_id'])) {
            return response()->json(['error' => 'bad request'], 400);
        }

        $user = $this->userServices->getUserById($user_id);
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->gender = $request->input('gender');
        $user->mobile = $request->input('mobile');
        $user->email = $request->input('email');
        $user->country_id = $request->input('country_id');
        $user->update();
        return $user;
    }

    public function forgetPassword(Request $request)
    {

        if (!$request->has(['email'])) {
            return response()->json(['response' => 'bad request', 'required fields' => ['email']], 400);
        }

        if (!$user = $this->userServices->getUserByEmail($request->input('email'))) {
            return response()->json(['response' => 'email not found'], 404);
        }

        if (!$mailAdminType = $this->mailServices->getMailTypeAdmin('forget_password')) {
            return response()->json(['response' => 'bad request'], 400);
        }

        if (!$mail = $this->mailServices->getMailAdmin($mailAdminType->mail_type_admin_id)) {
            return response()->json(['response' => 'bad request'], 400);
        }

        $user->verification_code = Str::random(40);
        $user->update();
        if ($request->id !== null) {
            $congressid = $request->id;
            $activationLink = UrlUtils::getBaseUrlFrontOffice() . 'password/reset/' . $congressid . '/' . $user->user_id . '?verification_code=' . $user->verification_code . '&user_id=' . $user->user_id;

        } else {
            $activationLink = UrlUtils::getBaseUrlFrontOffice() . 'password/reset/' . $user->user_id . '?verification_code=' . $user->verification_code . '&user_id=' . $user->user_id;

        }
        $userMail = $this->mailServices->addingUserMailAdmin($mail->mail_admin_id, $user->user_id);
        $this->mailServices->sendMail($this->adminServices->renderMail($mail->template, null, null, $activationLink), $user, null, $mail->object, null, $userMail);

        return response()->json(['response' => 'Check your mail to reset password !'], 200);

    }

    public function getUserById($user_id, Request $request)
    {

        $verification_code = $request->query('verification_code', '');
        if (!$user = $this->userServices->getUserById($user_id)) {
            return response()->json(['response' => 'user not found'], 404);
        }
        if ($user->verification_code !== $verification_code) {
            return response()->json('bad request', 400);
        }

        return response()->json($user, 200);
    }

    public function resetUserPassword($userId, Request $request)
    {
        if (!$request->has(['verification_code', 'password'])) {
            return response()->json(['response' => 'bad request'], 400);
        }

        $verification_code = $request->input('verification_code');
        if (!$user = $this->userServices->getUserById($userId)) {
            return response()->json(['response' => 'user not found'], 404);
        }
        if ($user->verification_code !== $verification_code) {
            return response()->json(['response' => 'bad request'], 400);
        }
        if (!$mailAdminType = $this->mailServices->getMailTypeAdmin('reset_password_success')) {
            return response()->json(['response' => 'bad request'], 400);
        }

        if (!$mail = $this->mailServices->getMailAdmin($mailAdminType->mail_type_admin_id)) {
            return response()->json(['response' => 'bad request'], 400);
        }
        $password = $request->input('password');
        $user->passwordDecrypt = $password;
        $user->password = bcrypt($password);
        $user->update();
        $userMail = $this->mailServices->addingUserMailAdmin($mail->mail_admin_id, $user->user_id);
        $this->mailServices->sendMail($this->adminServices->renderMail($mail->template), $user, null, $mail->object, null, $userMail);

        return response()->json(['response' => 'password successfully updated'], 200);
    }

    public function editUserProfile(Request $request)
    {
        if (!$request->has(['email', 'first_name', 'last_name'])) {
            return response()->json(['response' => 'bad request', 'required fields' => ['email', 'first_name', 'last_name']], 400);
        }

        $user = $this->userServices->retrieveUserFromToken();
        if (!$user) {
            return response()->json(['response' => 'No user found'], 401);
        }
        $resource = $request->has('resource_id') ? $resource = $this->resourcesServices->getResourceByResourceId($request->input('resource_id')) : null;
        $user = $this->userServices->editUser($request, $user, $resource);
        if (!$mailAdminType = $this->mailServices->getMailTypeAdmin('update_profile')) {
            return response()->json(['response' => 'mail type admin not found'], 400);
        }

        if ($mail = $this->mailServices->getMailAdmin($mailAdminType->mail_type_admin_id)) {
            $userMail = $this->mailServices->addingUserMailAdmin($mail->mail_admin_id, $user->user_id);
            $this->mailServices->sendMail($this->adminServices->renderMail($mail->template), $user, null, $mail->object, null, $userMail);
        }
        $user = $this->userServices->getUserById($user->user_id);
        return response()->json($user, 200);
    }

    private function handleCongressInscription(Request $request, $privilegeId, $user, $congress, $congress_id, $packId, $accessesIds, $user_congress)
    {
        if ($whiteList = $this->userServices->getWhiteListByEmailAndCongressId($user->email, $congress_id)) {
            $this->userServices->changeUserStatus($user_congress, 1);
        }

        if ($request->has('responses')) {
            $this->userServices->saveUserResponses($request->input('responses'), $user->user_id);
        }
        $accessNotInRegister = $this->accessServices->getAllAccessByRegisterParams($congress_id, 0, 0);
        $this->userServices->affectAccessElement($user->user_id, $accessNotInRegister);

        if ($privilegeId == config('privilege.Participant')) {
            $this->userServices->affectPacksToUser($user->user_id, $packId);
            $accessInPackNotInRegister = $this->accessServices->getAllAccessByPackIds(
                $user->user_id,
                $congress_id,
                $packId,
                1,
                0
            );
            $this->userServices->affectAccessElement($user->user_id, $accessInPackNotInRegister);
            $this->userServices->affectAccess($user->user_id, $accessesIds, []);
        } else {
            $packs = $this->packServices->getAllPackByCongress($congress_id);
            $this->userServices->affectPacksToUser($user->user_id, null, $packs);
            $accessInRegister = $this->accessServices->getAllAccessByRegisterParams($congress_id, 0, 1);
            $this->userServices->affectAccessElement($user->user_id, $accessInRegister);

            $accessInRegister = $this->accessServices->getAllAccessByRegisterParams($congress_id, 1);
            $this->userServices->affectAccessElement($user->user_id, $accessInRegister);
        }

        if ($packId == []) {
            $pack = null;
        } else {
            $pack = $this->packServices->getPackById($packId);
        }
        $accessesId = $accessesIds;
        $accesses = $this->accessServices->getAllAccessByAccessIds($accessesId);
        $totalPrice = $this->userServices->calculateCongressFees($congress, $pack, $accesses);
        $isFree = false;
        if ($privilegeId == config('privilege.Participant')) {
            $nbParticipants = $this->congressServices->getParticipantsCount($congress_id, 3, null);
            $freeNb = $this->paymentServices->getFreeUserByCongressId($congress_id);
            //Free Inscription (By Chance)
            if ($freeNb < $congress->config->free && ($nbParticipants % 10) == 0) {
                $this->paymentServices->affectPaymentToUser($user->user_id, $congress_id, $totalPrice, true);
                $isFree = true;
            }
        }
        // Sending Mail
        $link = $request->root() . "/api/users/" . $user->user_id . '/congress/' . $congress_id . '/validate/' . $user->verification_code;
        $user = $this->userServices->getUserIdAndByCongressId($user->user_id, $congress_id);
        $userPayment = null;

        if ($privilegeId != config('privilege.Participant') || $congress->congress_type_id == 3 || ($congress->congress_type_id == 1 && $totalPrice == 0) || $isFree || $whiteList) {
            //Free Mail
            if ($isFree) {
                if ($mailtype = $this->congressServices->getMailType('free')) {
                    if ($mail = $this->congressServices->getMail($congress_id, $mailtype->mail_type_id)) {
                        $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                        $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, null), $user, $congress, $mail->object, false, $userMail);
                    }
                }
            }
            //Confirm Direct
            $badge = $this->congressServices->getBadgeByPrivilegeId($congress, $privilegeId);
            $badgeIdGenerator = $badge['badge_id_generator'];
            $fileAttached = false;
            if ($badgeIdGenerator != null) {
                $fileAttached = $this->sharedServices->saveBadgeInPublic(
                    $badge,
                    $user,
                    $user->qr_code,
                    $privilegeId,
                    $congress->congress_id
                );
            }
            if ($mailtype = $this->congressServices->getMailType('confirmation')) {
                $linkFrontOffice = UrlUtils::getBaseUrlFrontOffice() . '/login';
                $linkPrincipalRoom = UrlUtils::getBaseUrlFrontOffice() . '/room/'.$congress_id.'/event-room';
                if ($mail = $this->congressServices->getMail($congress_id, $mailtype->mail_type_id)) {
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                    $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user, null, null, null, null, $linkFrontOffice,null,null,null,null,null,null,null,[],null,null,$linkPrincipalRoom), $user, $congress, $mail->object, $fileAttached, $userMail, null, 'badge.png');
                }
            }
            $this->smsServices->sendSmsToUsers($user, null, $congress_id, $congress);
        } else {
            //PreInscription First (Payment Required)
            //Add Payement Ligne
            if (($congress->congress_type_id == 1 && (!$congress->config_selection)) || ($congress->congress_type_id == 1 && $congress->config_selection && ($congress->config_selection->selection_type == 2 || $congress->config_selection->selection_type == 3))) {
                $userPayment = $this->paymentServices->affectPaymentToUser($user->user_id, $congress_id, $totalPrice, false);
            }

            if ($mailtype = $this->congressServices->getMailType('inscription')) {
                if ($mail = $this->congressServices->getMail($congress_id, $mailtype->mail_type_id)) {
                    $linkPrincipalRoom = UrlUtils::getBaseUrlFrontOffice() . '/room/'.$congress_id.'/event-room';
                    $userMail = $this->mailServices->addingMailUser($mail->mail_id, $user->user_id);
                   $this->mailServices->sendMail($this->congressServices->renderMail($mail->template, $congress, $user,  $link, null, $userPayment ,null,null,null,null,null,null,null,null,null,[],null,null,$linkPrincipalRoom), $user, $congress, $mail->object, false, $userMail);
                }
            }
        }
        if ($congress->config_selection && $congress->config_selection->num_evaluators > 0 && $privilegeId == config('privilege.Participant') && ($congress->congress_type_id == 2 || ($congress->congress_type_id == 1 && $congress->config_selection))) {
            $evalutors = $this->adminServices->getEvaluatorsByCongress($congress_id, 13, 'evaluations');
            $this->adminServices->affectEvaluatorsToUser(
                $evalutors,
                $congress->config_selection->num_evaluators,
                $congress_id,
                $user->user_id
            );
        }

        // Notify Organizer Mail Rule (privilege ==3 & configCongress Activated & form user-register not backoffice add)
        if ($privilegeId === config('privilege.Participant') && $congress->config->replyto_mail && $congress->config->is_notif_register_mail && !$user->is_admin_created) {
            $mail = $congress->config->replyto_mail; // Mail To Send with every inscription
            $template = Utils::getDefaultMailNotifNewRegister();
            $objectMail = "Nouvelle Inscription";
            $this->adminServices->sendMail($this->congressServices->renderMail($template, $congress, $user, null, null, $userPayment), $congress, $objectMail, null, false, $mail);
        }

        $privilege = $this->sharedServices->getPrivilegeById($privilegeId);

        $this->trackingServices->sendUserInfo($congress->congress_id, $congress->form_inputs, $user);
    }

    public function trackingUser(Request $request)
    {

        $user = $this->userServices->retrieveUserFromToken();

        if (!$user) {
            return response()->json(['response' => 'user not found'], 404);
        }

        $userId = $user->user_id;
        if (!$request->has(['action', 'congress_id'])) {
            return response()->json(['response' => 'bad request', 'required fields' => ['action', 'congress_id']], 400);
        }

        if (($request->has('channel_name') && !$request->has('type')) || ((!$request->has('channel_name') || $request->has('channel_name') == '') && $request->has('type'))) {
            return response()->json(['response' => 'bad request', 'required fields' => ['type', 'channel_name']], 400);
        }

        $congressId = $request->input("congress_id");

        if (!$congress = $this->congressServices->getCongressById($congressId)) {
            return response()->json(['response' => 'Congress not found', 404]);
        }

        $userCalledId = $request->input("user_call_id");

        if ($request->has("user_call_id") && !$userCalled = $this->userServices->getUserById($userCalledId)) {
            return response()->json(['response' => 'user called not found']);
        }

        if (!$action = $this->sharedServices->getActionByKey($request->input("action"))) {
            return response()->json(['response' => 'action not found'], 404);
        }

        if ($request->input('type') && $request->input('type') != 'STAND' && $request->input('type') != 'ACCESS') {
            return response()->json(['response' => 'Bad request type must be [STAND|ACCESS]'], 400);
        }

        // LOGOUT & LEAVE IF TRACK STILL OPEN
        if ($request->input('action') == 'LOGIN') {
            $this->userServices->closeTracking($congressId, $userId);

            $participator = $this->userServices->getUserByIdWithRelations($userId,
                ['user_congresses' => function ($query) use ($congressId) {
                    $query->where('congress_id', '=', $congressId);
                }]);

            if (sizeof($participator->user_congresses) > 0 && $participator->user_congresses[0]) {
                /* Make it present in congress */
                $userCongress = $participator->user_congresses[0];
                $userCongress->isPresent = 1;
                $userCongress->update();
            }

        }

        $standId = null;
        $accessId = null;
        if ($request->input('type') == 'STAND') {
            $stands = $this->standServices->getStands($congressId, $request->input('channel_name'));
            if (sizeof($stands) == 0) {
                return response()->json(['response' => 'stand not found'], 404);
            }
            $standId = $stands[0]->stand_id;
        }

        if ($request->input('type') == 'ACCESS') {
            $accesses = $this->congressServices->getAccesssByCongressId($congressId, $request->input('channel_name'));
            if (sizeof($accesses) == 0) {
                return response()->json(['response' => 'access not found'], 404);
            }
            $accessId = $accesses[0]->access_id;

            $user_access = $this->userServices->getUserAccessByUser($userId, $accessId);

            if ($user_access) {
                $user_access->isPresent = 1;
                $user_access->update();
            }
        }

        return response()->json($this->userServices->addTracking($congressId, $action->action_id, $userId, $accessId, $standId, $request->input('type'), $request->input('comment'), $userCalledId));
    }

    public function getWhiteList(Request $request, $congress_id)
    {
        $perPage = $request->query('perPage', 10);
        $search = $request->query('search', '');

        $whiteLists = $this->userServices->getWhiteList($congress_id, $perPage, $search);
        return response()->json($whiteLists, 200);
    }

    public function addWhiteList(Request $request, $congress_id)
    {
        if (!$congress = $this->congressServices->getById($congress_id)) {
            return response()->json(["error" => "congress not found"], 404);
        }
        ini_set('max_execution_time', 500);
        $users = $request->input("data");
        foreach ($users as $userData) {
            if ($userData['email'] && !$whiteList = $this->userServices->getWhiteListByEmailAndCongressId($userData['email'], $congress_id)) {
                $firstName = isset($userData['first_name']) ? $userData['first_name'] : null;
                $lastName = isset($userData['last_name']) ? $userData['last_name'] : null;
                $mobile = isset($userData['mobile']) ? $userData['mobile'] : null;

                $this->userServices->addWhiteList($congress_id, $userData['email'], $firstName, $lastName, $mobile);
            }

        }
        return response()->json(['message' => 'added successfully'], 200);
    }

    public function deleteWhiteList($congress_id, $white_list_id)
    {
        if (!$congress = $this->congressServices->getById($congress_id)) {
            return response()->json(["error" => "congress not found"], 404);
        }
        if (!$white_list = $this->userServices->getWhiteListById($white_list_id)) {
            return response()->json(["error" => "white-list not found"], 404);
        }
        $this->userServices->deleteWhiteList($white_list);
        return response()->json(['message' => 'deleted successfully'], 200);
    }

    public function updateUserPathCV($userId, Request $request)
    {
        if (!$user = $this->userServices->getUserById($userId)) {
            return response()->json(['response' => 'User not found'], 404);
        }

        $path = $request->input('path');
        if (!$user = $this->userServices->updateUserPathCV($path, $user)) {
            return response()->json(['response' => 'Path not found'], 404);
        }

        return response()->json(['path' => $path]);
    }

    public function deleteUserCV($userId)
    {
        if (!$user = $this->userServices->getUserById($userId)) {
            return response()->json(['response' => 'user not found'], 404);
        }

        $this->userServices->makeUserPathCvNull($user);
        return response()->json(['response' => 'user cv deleted'], 200);

    }

    public function migrateUsersData($congressId)
    {
        $users = $this->userServices->getUsersWithResources($congressId);
        foreach ($users as $user) {
            $user->img_base64 = Utils::getBase64Img(UrlUtils::getFilesUrl() . $user->profile_img->path);
            $user->update();
        }
        return response()->json(['$users' => $users]);
    }

    public function checkStandRights($congressId, $standId, $organizerId = null)
    {
        $user = $this->userServices->retrieveUserFromToken();
        if (!$user) {
            return response()->json(['response' => 'No user found'], 401);
        }
        $stand = $this->standServices->getStandById($standId);
        if (!$stand) {
            return response()->json(['response' => 'No stand found'], 401);
        }

        $userId = $user->user_id;
        $congress = $this->congressServices->getCongressById($congressId);
        

        $user = $this->userServices->getUserByIdWithRelations($userId, [
            'user_congresses' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            },
            'payments' => function ($query) use ($congressId) {
                $query->where('congress_id', '=', $congressId);
            }]);

        if (!Utils::isValidSendMail($congress, $user)) {
            return response()->json(['response' => 'not authorized'], 401);
        }
        
        $isModerator = $organizerId ? $this->userServices->isUserOrganizer($user->user_congresses[0]) : $this->userServices->isUserModeratorStand($user->user_congresses[0]);
        $urlStreaming = !$isModerator && $stand->url_streaming ? $stand->url_streaming : null;
        $allowed = $isModerator || !$stand->url_streaming;

        $userToUpdate = $user->user_congresses[0];
        $roomName = $organizerId ?  'eventizer_room_' . $congressId . 'support' . $organizerId : 'eventizer_room_' . $congressId . 's' . $standId ;
       
        if ($congress->config && $congress->config->is_agora) {
            $token = $this->roomServices->createTokenAgora($user->user_id . '_' .$user->first_name . '_' . $user->last_name, $roomName, $isModerator);
        } else {
            $token = $this->roomServices->createToken($user->email, $roomName, $isModerator, $user->first_name . " " . $user->last_name);
        }
        
        $userToUpdate->token_jitsi = $token;
        $userToUpdate->update();

        return response()->json(
            [
                "type" => $congress->config && $congress->config->is_agora ? "agora" : "jitsi",
                "token" => $token,
                "is_moderator" => $isModerator,
                "privilege_id" => $user->user_congresses[0]->privilege_id,
                "allowed" => $allowed,
                "url_streaming" => $urlStreaming
            ], 200);
    }

    public function getOrganizers($congressId)
    {
        if (!$congress = $this->congressServices->getById($congressId)) {
            return response()->json(["error" => "congress not found"], 404);
        }
        $users = $this->userServices->getUsersMinByCongress($congressId, 2);

        return response()->json($users);
    }

    public function changeQrCode($user_id, Request $request)
    {
        $congressId = $request->input("congressId");

        if (!$user = $this->userServices->getUserByIdWithRelations($user_id, ['user_congresses' => function ($query) use ($congressId) {
            $query->where('congress_id', '=', $congressId);
        }]))
            return response()->json(['error' => 'user not found'], 400);

        $oldUsers = $this->userServices->getMinUserByQrCode($request->input("qrcode"));

        foreach ($oldUsers as $oldUser
        ) {
            if ($oldUser->user_id != $user->user_id) {
                $oldUser->qr_code = Utils::generateCode($oldUser->user_id);
                $oldUser->update();
            }
        }

        if (sizeof($user->user_congresses) > 0) {
            $user->user_congresses[0]->isPresent = 1;
            $user->user_congresses[0]->update();
        }

        $user->qr_code = $request->get('qrcode');
        $user->update();
        return $user;
    }

    public function getAllUsersByCongressFrontOfficeWithPagination($congress_id,Request $request)
    {
        $perPage = $request->query('perPage', 10);
        $search = Str::lower($request->query('search', ''));
        if (!$user = $this->userServices->retrieveUserFromToken()) {
            return response()->json('no user found', 404);
        }

        $users = $this->userServices->getAllUsersByCongressFrontOfficeWithPagination($congress_id,$perPage,$search,$user->user_id);
        return response()->json($users);
    }
    public function checkMeetingRights($congressId, $meetingId )
    {
        $user = $this->userServices->retrieveUserFromToken();
        if (!$user) {
            return response()->json(['response' => 'No user found'], 401);
        }
        $userId = $user->user_id;
        $congress = $this->congressServices->getCongressById($congressId);
        if (!$congress) {
            return response()->json(['response' => 'No congress found'], 401);
        }
        $meeting = $this->meetingServices->getMeetingById($meetingId);
        if (!$meeting->meeting_id) {
            return response()->json(['response' => 'No meeting found'], 401);
        }
        $allowed = true;
        $userMeeting = $this->meetingServices->getUserMeetingsById($meeting->meeting_id,$userId);
        if (!$userMeeting) {
            $allowed = false;
            return response()->json(['response' => 'No user meetings found'], 401);
        }

        if ($meeting->user_meeting[0]->status == 0 || $meeting->user_meeting[0]->status == -1) {
            $allowed = false;
        }
        
        $userToUpdate = $user->user_congresses[0];
        $roomName = 'eventizer_room_' . $congressId . '_m_' . $meeting->meeting_id;

        if ($congress->config && $congress->config->is_agora) {
            $token = $this->roomServices->createTokenAgora($user->user_id . '_' .$user->first_name . '_' . $user->last_name, $roomName, null);
        } else {
            $token = $this->roomServices->createToken($user->email, $roomName, null, $user->first_name . " " . $user->last_name);
        }

        $userToUpdate->token_jitsi = $token;
        $userToUpdate->update();

        return response()->json(
            [
                "type" => $congress->config && $congress->config->is_agora ? "agora" : "jitsi",
                "token" => $token,
                "privilege_id" => $user->user_congresses[0]->privilege_id,
                "allowed" => $allowed

            ],
            200
        );
    }
}
