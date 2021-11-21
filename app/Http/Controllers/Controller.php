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


/* Delete duplicate submission with same title:

DELETE `a`
FROM
    Submission AS `a`,
    Submission AS `b`
WHERE
    -- IMPORTANT: Ensures one version remains
    -- Change "ID" to your unique column's name
    `a`.`submission_id` < `b`.`submission_id`

    -- Any duplicates you want to check for
    AND `a`.`title` = `b`.`title`
    AND `a`.`congress_id` = `b`.`congress_id`
    AND `a`.`user_id` = `b`.`user_id`;

*/

/*UPDATE Submission SET title = REPLACE(title,'Š','S');
UPDATE Submission SET title = REPLACE(title,'š','s');
UPDATE Submission SET title = REPLACE(title,'Ð','Dj');
UPDATE Submission SET title = REPLACE(title,'Ž','Z');
UPDATE Submission SET title = REPLACE(title,'ž','z');
UPDATE Submission SET title = REPLACE(title,'À','A');
UPDATE Submission SET title = REPLACE(title,'Á','A');
UPDATE Submission SET title = REPLACE(title,'Â','A');
UPDATE Submission SET title = REPLACE(title,'Ã','A');
UPDATE Submission SET title = REPLACE(title,'Ä','A');
UPDATE Submission SET title = REPLACE(title,'Å','A');
UPDATE Submission SET title = REPLACE(title,'Æ','A');
UPDATE Submission SET title = REPLACE(title,'Ç','C');
UPDATE Submission SET title = REPLACE(title,'È','E');
UPDATE Submission SET title = REPLACE(title,'É','E');
UPDATE Submission SET title = REPLACE(title,'Ê','E');
UPDATE Submission SET title = REPLACE(title,'Ë','E');
UPDATE Submission SET title = REPLACE(title,'Ì','I');
UPDATE Submission SET title = REPLACE(title,'Í','I');
UPDATE Submission SET title = REPLACE(title,'Î','I');
UPDATE Submission SET title = REPLACE(title,'Ï','I');
UPDATE Submission SET title = REPLACE(title,'Ñ','N');
UPDATE Submission SET title = REPLACE(title,'Ò','O');
UPDATE Submission SET title = REPLACE(title,'Ó','O');
UPDATE Submission SET title = REPLACE(title,'Ô','O');
UPDATE Submission SET title = REPLACE(title,'Õ','O');
UPDATE Submission SET title = REPLACE(title,'Ö','O');
UPDATE Submission SET title = REPLACE(title,'Ø','O');
UPDATE Submission SET title = REPLACE(title,'Ù','U');
UPDATE Submission SET title = REPLACE(title,'Ú','U');
UPDATE Submission SET title = REPLACE(title,'Û','U');
UPDATE Submission SET title = REPLACE(title,'Ü','U');
UPDATE Submission SET title = REPLACE(title,'Ý','Y');
UPDATE Submission SET title = REPLACE(title,'Þ','B');
UPDATE Submission SET title = REPLACE(title,'ß','Ss');
UPDATE Submission SET title = REPLACE(title,'à','a');
UPDATE Submission SET title = REPLACE(title,'á','a');
UPDATE Submission SET title = REPLACE(title,'â','a');
UPDATE Submission SET title = REPLACE(title,'ã','a');
UPDATE Submission SET title = REPLACE(title,'ä','a');
UPDATE Submission SET title = REPLACE(title,'å','a');
UPDATE Submission SET title = REPLACE(title,'æ','a');
UPDATE Submission SET title = REPLACE(title,'ç','c');
UPDATE Submission SET title = REPLACE(title,'è','e');
UPDATE Submission SET title = REPLACE(title,'é','e');
UPDATE Submission SET title = REPLACE(title,'ê','e');
UPDATE Submission SET title = REPLACE(title,'ë','e');
UPDATE Submission SET title = REPLACE(title,'ì','i');
UPDATE Submission SET title = REPLACE(title,'í','i');
UPDATE Submission SET title = REPLACE(title,'î','i');
UPDATE Submission SET title = REPLACE(title,'ï','i');
UPDATE Submission SET title = REPLACE(title,'ð','o');
UPDATE Submission SET title = REPLACE(title,'ñ','n');
UPDATE Submission SET title = REPLACE(title,'ò','o');
UPDATE Submission SET title = REPLACE(title,'ó','o');
UPDATE Submission SET title = REPLACE(title,'ô','o');
UPDATE Submission SET title = REPLACE(title,'õ','o');
UPDATE Submission SET title = REPLACE(title,'ö','o');
UPDATE Submission SET title = REPLACE(title,'ø','o');
UPDATE Submission SET title = REPLACE(title,'ù','u');
UPDATE Submission SET title = REPLACE(title,'ú','u');
UPDATE Submission SET title = REPLACE(title,'û','u');
UPDATE Submission SET title = REPLACE(title,'ý','y');
UPDATE Submission SET title = REPLACE(title,'ý','y');
UPDATE Submission SET title = REPLACE(title,'þ','b');
UPDATE Submission SET title = REPLACE(title,'ÿ','y');
UPDATE Submission SET title = REPLACE(title,'ƒ','f');
UPDATE Submission SET title = REPLACE(title,'.',' ');
UPDATE Submission SET title = REPLACE(title,' ','-');
UPDATE Submission SET title = REPLACE(title,'--','-');

UPDATE Submission SET title = REPLACE(title,'ě','e');
UPDATE Submission SET title = REPLACE(title,'ž','z');
UPDATE Submission SET title = REPLACE(title,'š','s');
UPDATE Submission SET title = REPLACE(title,'č','c');
UPDATE Submission SET title = REPLACE(title,'ř','r');
UPDATE Submission SET title = REPLACE(title,'ď','d');
UPDATE Submission SET title = REPLACE(title,'ť','t');
UPDATE Submission SET title = REPLACE(title,'ň','n');
UPDATE Submission SET title = REPLACE(title,'ů','u');

UPDATE Submission SET title = REPLACE(title,'Ě','E');
UPDATE Submission SET title = REPLACE(title,'Ž','Z');
UPDATE Submission SET title = REPLACE(title,'Š','S');
UPDATE Submission SET title = REPLACE(title,'Č','C');
UPDATE Submission SET title = REPLACE(title,'Ř','R');
UPDATE Submission SET title = REPLACE(title,'Ď','D');
UPDATE Submission SET title = REPLACE(title,'Ť','T');
UPDATE Submission SET title = REPLACE(title,'Ň','N');
UPDATE Submission SET title = REPLACE(title,'Ů','U');
*/


/*
Affectation de tous les evaluations inscriptions 

INSERT IGNORE INTO Evaluation_Inscription (`admin_id`, `congress_id`, `user_id`)
SELECT Admin_Congress.admin_id,  352,  User.user_id
FROM User
INNER JOIN `User_Congress` ON `User_Congress`.`user_id` = `User`.`user_id` AND `User_Congress`.`congress_id` = 352
INNER JOIN `Admin_Congress` ON `Admin_Congress`.`congress_id` = 352 AND `Admin_Congress`.`privilege_id` = 13
LEFT JOIN `Evaluation_Inscription` ON `Evaluation_Inscription`.`user_id` = `User`.`user_id` AND `Admin_Congress`.`admin_id` = `Evaluation_Inscription`.`admin_id`
WHERE Evaluation_Inscription.evaluation_inscription_id IS NULL

*/


/*  Get code submission incremental

SET @code=0;
SELECT 
  submission_id,
  CONCAT('CA',LPAD(@code:=@code+1, 4, '0')) AS code
 FROM submission
 WHERE status =1 AND communication_type_id = 2
 AND congress_id = 354;

*/ 

// Search for submission with special caracter
/* SELECT * FROM `Submission` WHERE description Like '%%'*/
/* SELECT * FROM `Submission` WHERE description Like '%%'*/
/* UPDATE Submission SET description = REPLACE(description,'','');*/

/* Set random QRCode
UPDATE User 
SET `qr_code` = CONCAT(CONV(FLOOR(RAND() * 99999999999999), 10, 36), `user_id`)
WHERE qr_code IS NULL
*/

/* Generate data user for congressId
SELECT User.user_id, first_name, last_name, passwordDecrypt from User
INNER JOIN User_Congress ON User_Congress.user_id = User.user_id
WHERE User_Congress.congress_id = 397
*/

/*

Get credentiels admin for specefic congress

SELECT `email` , `passwordDecrypt` 
FROM `Admin`
INNER JOIN Admin_Congress ON Admin_Congress.admin_id = Admin.admin_id
WHERE congress_id = 384

*/