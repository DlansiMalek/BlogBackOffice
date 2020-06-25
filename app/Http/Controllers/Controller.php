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
 * @SWG\SecurityScheme(
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

/*
 *
 UPDATE User SET last_name = REPLACE(last_name,'è','e') //remove accent
 */

/*
 * php artisan migrate:refresh --seed
 */

/*
 *
 *  INSERT INTO Attestation_Access (attestation_generator_id, privilege_id, access_id)
SELECT ASS.attestation_generator_id , 8 , ASS.access_id  FROM `Attestation_Access` as ASS
INNER JOIN Access as A on A.access_id = ASS.access_id
WHERE A.congress_id = 126 And ASS.privilege_id = 5
 */


 /* Affectation à tous les utilisateurs les accées manquant



 1- Add to all sans exception
 2- Delete doublons

 DELETE UA1

FROM `User_Access` UA1 , `User_Access` UA2

Where UA1.user_access_id > UA2.user_access_id 
And UA1.user_id = UA2.user_id
And UA1.access_id = UA2.access_id

*/