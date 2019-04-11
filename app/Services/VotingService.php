<?php

namespace App\Services;

use App\Models\Access;
use App\Models\Congress;
use App\Models\Feedback_Question;
use App\Models\Feedback_Question_Type;
use App\Models\Feedback_Question_Value;
use App\Models\Feedback_Response;
use App\Models\Form_Input;
use App\Models\Form_Input_Value;
use App\Models\Mail;
use App\Models\Mail_Type;
use App\Models\Organization;
use App\Models\Pack;
use App\Models\User;
use Chumper\Zipper\Facades\Zipper;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use JWTAuth;
use PDF;


/**
 * @property VotingService $votingService
 */
class VotingService
{


}
