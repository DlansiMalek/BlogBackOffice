<?php

namespace App\Services;

use App\Models\Access;
use App\Models\AdminCongress;
use App\Models\City;
use App\Models\Location;
use App\Models\ConfigCongress;
use App\Models\Congress;
use App\Models\Mail;
use App\Models\MailType;
use App\Models\Organization;
use App\Models\Pack;
use App\Models\Resource;
use App\Models\User;
use App\Models\UserCongress;
use Chumper\Zipper\Facades\Zipper;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use JWTAuth;
use PDF;


/**
 * @property OrganizationServices $organizationServices
 */
class CongressServices
{


    public function __construct(OrganizationServices $organizationServices, GeoServices $geoServices)
    {
        $this->organizationServices = $organizationServices;
        $this->geoServices = $geoServices;
    }

    public function getById($congressId)
    {
        return Congress::find($congressId);
    }

    public function getCongressById($id_Congress)
    {
        $congress = Congress::with(['config', "badges", "attestation", "packs.accesses", "form_inputs.type", "form_inputs.values", "mails.type", 'accesss.attestation', 'accesss.participants'])
            ->where("congress_id", "=", $id_Congress)
            ->first();
        return $congress;
    }

    public function getCongressConfigById($id_Congress)
    {
        $congress = ConfigCongress::where("congress_id", "=", $id_Congress)
            ->first();
        return $congress;
    }

    function retrieveCongressFromToken()
    {
        Config::set('jwt.user', 'App\Models\Congress');
        Config::set('jwt.identifier', 'id_Congress');
        try {
            return JWTAuth::parseToken()->toUser();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return null;
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return null;
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return null;
        }
    }

    public function addCongress($name, $start_date, $end_date, $price, $has_payment, $free, $prise_charge_option, $description, $admin_id)
    {
        $congress = new Congress();
        $congress->name = $name;
        $congress->start_date = $start_date;
        $congress->end_date = $end_date;
        $congress->price = $price ? $price : 0;
        $congress->description = $description;
        $congress->save();

        $config = new ConfigCongress();
        $config->congress_id = $congress->congress_id;
        $config->free = $free ? $free : 0;
        $config->has_payment = $has_payment ? 1 : 0;
        $config->prise_charge_option = $prise_charge_option ? 1 : 0;
        $config->save();

        $admin_congress = new AdminCongress();
        $admin_congress->admin_id = $admin_id;
        $admin_congress->congress_id = $congress->congress_id;
        $admin_congress->privilege_id = 1;
        $admin_congress->save();
        return $congress;
    }

    public function editConfigCongress($congress, $eventLocation, $congressId)
    {

        $config_congress = ConfigCongress::where("congress_id", '=', $congressId)->first();
        $config_congress->logo = $congress['logo'];
        $config_congress->banner = $congress['banner'];
        $config_congress->free = $congress['free'];
        $config_congress->has_payment = $congress['has_payment'];
        $config_congress->program_link = $congress['program_link'];
        $config_congress->voting_token = $congress['voting_token'];
        $config_congress->prise_charge_option = $congress['prise_charge_option'];
        $config_congress->feedback_start = $congress['feedback_start'];
        $this->editCongressLocation($eventLocation, $congressId);
        $config_congress->update();
        return $config_congress;
    }

    public function editCongressLocation($eventLocation, $congressId)
    {
        // update congress Location
        // add city in DB
        $congress = $this->getCongressById($congressId);
        $country = $this->geoServices->getCountryByCode($eventLocation['countryCode']);
        $city = $this->geoServices->getCityByNameAndCountryCode(
            $eventLocation['cityName'],
            $eventLocation['countryCode']
        );
        if (!$city) {
            $city = new City();
            $city->name = $eventLocation['cityName'];
            $city->country_code = $eventLocation['countryCode'];
            $city->save();
            // add city to db
        }
        if (!$congress->location_id) {
            // create -- insert
            $location = new Location();
            $location->lng = $eventLocation['lng'];
            $location->lat = $eventLocation['lat'];
            $location->address = $eventLocation['address'];
            $location->city_id = $city->city_id;
            $location->save();
            $congress->location_id = $location->location_id;
            $congress->save();
        } else {
            // update
            Location::where('location_id', '=', $congress->location_id)
                ->update(['lng' => $eventLocation['lng'],
                    'lat' => $eventLocation['lat'],
                    'address' => $eventLocation['address']]);
        }
        //the end :D
    }

    public function getCongressAllAccess($adminId)
    {
        $congress = Congress::with(["accesss.participants", "packs.accesses", "form_inputs.type", "users.responses.values", "users.responses.form_input"])
            ->where("admin_id", "=", $adminId)
            ->get();
        //$congress->accesss = $congress->accesses; // ?????
        return $congress;
    }

    public function getBadgesByUsers($badgeName, $users)
    {

        $users = $users->toArray();
        $file = new Filesystem();
        $path = public_path() . "/" . $badgeName;

        if (!$file->exists($path)) {
            $file->makeDirectory($path);
        }
        $qrCodePath = "/QrCode";
        if (!$file->exists(public_path() . $qrCodePath)) {
            $file->makeDirectory(public_path() . $qrCodePath);
        }

        File::cleanDirectory($path);
        for ($i = 0; $i < sizeof($users) / 4; $i++) {
            $tempUsers = array_slice($users, $i * 4, 4);
            $j = 1;
            $pdfFileName = '';
            foreach ($tempUsers as $tempUser) {
                Utils::generateQRcode($tempUser['qr_code'], $qrCodePath . '/qr_code_' . $j . '.png');
                $pdfFileName .= '_' . $tempUser['user_id'];
                $j++;
            }
            $data = [
                'users' => json_decode(json_encode($tempUsers), false)];
            $pdf = PDF::loadView('pdf.' . $badgeName, $data);
            $pdf->save($path . '/badges' . $pdfFileName . '.pdf');
        }
        $files = glob($path . '/*');
        $file->deleteDirectory(public_path() . $qrCodePath);
        Zipper::make($path . '/badges.zip')->add($files)->close();
        return response()->download($path . '/badges.zip')->deleteFileAfterSend(true);

    }

    public function editCongress($congress, $config, $request)
    {
        $congress->name = $request->input('name');
        $congress->start_date = $request->input('start_date');
        $congress->end_date = $request->input('end_date');
        $congress->price = $request->input('price') ? $request->input('price') : 0;
        $congress->description = $request->input('description');
        $congress->update();

        $config->free = $request->input('config')['free'] ? $request->input('config')['free'] : 0;
        $config->has_payment = $request->input('config')['has_payment'] ? 1 : 0;
        $config->prise_charge_option = $request->input('config')['prise_charge_option'] ? 1 : 0;
        $config->update();

        return $this->getCongressById($congress->congress_id);
    }

    public function getUsersByStatus($congressId, int $status)
    {
        return User::where('isPresent', '=', $status)
            ->where('congress_id', '=', $congressId)
            ->get();
    }

    public function getLabsByCongress($congressId)
    {
        return Organization::with(['users' => function ($q) use ($congressId) {
            $q->where('User.congress_id', '=', $congressId);
        }])->whereHas('users', function ($q) use ($congressId) {
            $q->where('User.congress_id', '=', $congressId);
        })->get();
    }

    public function getOrganizationInvoiceByCongress($labId, $congress)
    {
        $lab = $this->organizationServices->getOrganizationById($labId);
        $totalPrice = 0;
        $packs = Pack::whereCongressId($congress->congress_id)->with(['participants' => function ($q) use ($labId) {
            $q->where('User.organization_id', '=', $labId);
        }])->get();
        foreach ($packs as $pack) {
            $packPrice = $pack->price;
            $pack->price = 0;
            foreach ($pack->participants as $participant) {
                $pack->price += $packPrice;
            }
            $totalPrice += $pack->price;
        }
        $today = date('d-m-Y');
        $data = [
            'packs' => $packs,
            'congress' => $congress,
            'today' => $today,
            'lab' => $lab,
            'totalPrice' => $totalPrice,
            'displayTaxes' => false
        ];
        $pdf = PDF::loadView('pdf.invoice.invoice', $data);
        return $pdf->download($lab->name . '_facture_' . $today . '.pdf');
    }

    public function getBadgeByPrivilegeId($congress, $privilege_id)
    {
        for ($i = 0; $i < sizeof($congress->badges); $i++) {
            if ($congress->badges[$i]->privilege_id == $privilege_id) {
                return $congress->badges[$i]->badge_id_generator;
            }
        }
        return null;
    }

    public function uploadLogo($file, $congressConfig)
    {
        $timestamp = microtime(true) * 10000;
        $path = $file->storeAs('/logo/' . $timestamp, $file->getClientOriginalName());

        $congressConfig->logo = $path;
        $congressConfig->save();

        return $congressConfig;

    }

    public function uploadBanner($file, $congressConfig)
    {
        $timestamp = microtime(true) * 10000;
        $path = $file->storeAs('/banner/' . $timestamp, $file->getClientOriginalName());

        $congressConfig->banner = $path;
        $congressConfig->save();


        return $congressConfig;
    }

    public function getEmailById($id)
    {
        return Mail::find($id);
    }

    function renderMail($template, $congress, $participant, $link, $organization)
    {
        $accesses = "";
        if ($participant && sizeof($participant->accesses) > 0) {
            $accesses = "";
            foreach ($participant->accesses as $access) {
                $accesses = $accesses
                    . "<li>" . $access->name
                    . "<span class=\"bold\"> qui se déroulera le "
                    . \App\Services\Utils::convertDateFrench($access->start_date)
                    . " de "
                    . \App\Services\Utils::getTimeFromDateTime($access->start_date)
                    . " à "
                    . \App\Services\Utils::getTimeFromDateTime($access->end_date)
                    . " </span></li>";
            }
            $accesses = $accesses . "</ul>";
        }
        $template = str_replace('{{$congress-&gt;name}}', '{{$congress->name}}', $template);
        $template = str_replace('{{$congress-&gt;date}}', '{{$congress->date}}', $template);
        $template = str_replace('{{$congress-&gt;price}}', '{{$congress->price}}', $template);
        $template = str_replace('{{$participant-&gt;first_name}}', '{{$participant->first_name}}', $template);
        $template = str_replace('{{$participant-&gt;last_name}}', '{{$participant->last_name}}', $template);
        $template = str_replace('{{$participant-&gt;gender}}', '{{$participant->gender}}', $template);
        $template = str_replace('{{$participant-&gt;price}}', '{{$participant->price}}', $template);
        $template = str_replace('{{$participant-&gt;pack-&gt;label}}', '{{$participant->pack->label}}', $template);
        $template = str_replace('{{$participant-&gt;accesses}}', $accesses, $template);
        $template = str_replace('{{%24link}}', '{{$link}}', $template);
        $template = str_replace('{{$organization-&gt;name}}', '{{$organization->name}}', $template);
        $template = str_replace('{{$organization-&gt;description}}', '{{$organization->description}}', $template);
        $template = str_replace('{{$organization-&gt;email}}', '{{$organization->email}}', $template);
        $template = str_replace('{{$organization-&gt;mobile}}', '{{$organization->mobile}}', $template);

        if ($participant != null)
            $participant->gender = $participant->gender == 2 ? 'Mme.' : 'Mr.';
        return view(['template' => '<html>' . $template . '</html>'], ['congress' => $congress, 'participant' => $participant, 'link' => $link, 'organization' => $organization]);
    }

    public function getMailType($name)
    {
        return MailType::where("name", "=", $name)->first();
    }

    public function getMail($congressId, $mail_type_id)
    {
        return Mail::where("congress_id", '=', $congressId)->where('mail_type_id', '=', $mail_type_id)->first();
    }

    public function getMailById($id)
    {
        return Mail::find($id);
    }

    public function getAccesssByCongressId($congress_id)
    {
        return Access::with(['participants', 'attestation'])
            ->where('congress_id', '=', $congress_id)
            ->get();
    }

    public function getAllCongresses()
    {
        return Congress::with([
            'config',
            'accesss.speakers',
            'accesss.chairs',
            'accesss.sub_accesses',
            'accesss.topic',
            'accesss.type'])
            ->get();

    }

    public function getCongressConfig($congress_id)
    {
        return ConfigCongress::where('congress_id', '=', $congress_id)->first();
    }

    public function getParticipantsCount($congress_id)
    {
        //participant (privilege= 3)
        return UserCongress::where('congress_id', '=', $congress_id)
            ->where('privilege_id', '=', 3)
            ->count();
    }

}
