<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
/**
 * @SWG\Swagger(
 *   schemes={"http","https"},
 *   basePath="/api",
 *   @SWG\Info(
 *     title="API VayeCongress",
 *     version="1.0.0"
 *   )
 * )
 *@SWG\SecurityScheme(
 *         securityDefinition="Bearer",
 *         type="apiKey",
 *         name="Authorization",
 *         in="header"
 *     ),
 */


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}


/* Affecte aux tous les utilisateur un accées spécifique
 *
 *
INSERT INTO `User_Access`( `isPresent`, `user_id`, `access_id`)
SELECT 0 , U.user_id , 14 FROM User as U
     WHERE U.congress_id = 6 AND U.user_id NOT IN
        (SELECT UA.user_id FROM User_Access AS UA
         WHERE UA.access_id = 14)
 *
 *
 */
