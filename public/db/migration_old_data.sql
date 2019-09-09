
/* Migration Congress Part */

DELETE FROM `Congress` ;

INSERT IGNORE INTO `congress_v3`.Congress (congress_id,name,start_date,end_date,price,description,created_at,updated_at)
  SELECT C.congress_id , C.name , C.date , C.date, C.price , "-", C.created_at , C.updated_at
  FROM `congress_v2`.Congress as C;


DELETE FROM `Config_Congress` ;


INSERT IGNORE INTO `congress_v3`.Config_Congress (logo,banner,free,has_payment,feedback_start,program_link,voting_token,congress_id)
  SELECT C.logo, C.banner,C.free,C.has_paiement,C.feedback_start,C.program_link, A.voting_token, C.congress_id
  FROM `congress_v2`.Congress as C
  LEFT OUTER JOIN `congress_v2`.Admin as A
  ON A.admin_id = C.admin_id;



DELETE FROM `Location` ;

INSERT IGNORE INTO `congress_v3`.Location (lng,lat,address,congress_id,city_id)
  SELECT 10.26136550000001, 36.8501833,"-",C.congress_id,3350
  FROM `congress_v2`.Congress as C;


DELETE FROM `User` ;

INSERT IGNORE INTO `congress_v3`.User (user_id,first_name,last_name,gender,mobile,email,email_verified,verification_code,qr_code,rfid,country_id,created_at,updated_at)
  SELECT U.user_id , U.first_name, U.last_name, U.gender, U.mobile , U.email , U.email_verified,U.verification_code, U.qr_code, U.rfid , C.code , U.created_at , U.updated_at
  FROM `congress_v2`.User as U
  LEFT OUTER JOIN `congress_v2`.Country as C on C.country_id = U.country_id;




DELETE FROM `User_Congress` ;

INSERT IGNORE INTO `congress_v3`.User_Congress (isPresent,organization_accepted,user_id,privilege_id,congress_id,organization_id)
  SELECT U.isPresent , U.organization_accepted, U.user_id , IF(U.privilege_id  = 4, 8,U.privilege_id) , U.congress_id , U.organization_id
  FROM `congress_v2`.User as U;




DELETE FROM `Payment` ;


INSERT IGNORE INTO `congress_v3`.Payment (isPaid,path,reference,authorization,free,price,payment_type_id,user_id,congress_id,created_at,updated_at)
  SELECT U.isPaied , U.path_payement, U.ref_payment , U.autorisation_num , U.free , U.price,U.payement_type_id,U.user_id,U.congress_id , U.created_at , U.updated_at
  FROM `congress_v2`.User as U;



DELETE FROM `Mail` ;


INSERT IGNORE INTO `congress_v3`.Mail (mail_id,object,template,congress_id , mail_type_id ,  created_at,updated_at)
  SELECT M.mail_id , M.object , M.template , M.congress_id , M.mail_type_id , M.created_at , M.updated_at
  FROM `congress_v2`.Mail as M;


DELETE FROM `User_Mail` ;



INSERT IGNORE INTO `congress_v3`.User_Mail (status,user_id,mail_id,created_at,updated_at)
  SELECT U.email_sended , U.user_id, M.mail_id , U.created_at , U.updated_at
  FROM `congress_v2`.User as U
  LEFT OUTER JOIN `congress_v2`.Mail as M ON M.congress_id = U.congress_id
  WHERE M.mail_type_id = 1;


INSERT IGNORE INTO `congress_v3`.User_Mail (status,user_id,mail_id,created_at,updated_at)
  SELECT U.email_attestation_sended , U.user_id, M.mail_id , U.created_at , U.updated_at
  FROM `congress_v2`.User as U
  LEFT OUTER JOIN `congress_v2`.Mail as M ON M.congress_id = U.congress_id
  WHERE M.mail_type_id = 3;


DELETE FROM `Admin` ;


INSERT IGNORE INTO `congress_v3`.Admin (admin_id,name,email,mobile , password,passwordDecrypt,rfid,privilege_id,created_at,updated_at)
  SELECT A.admin_id , A.name, A.email , A.mobile , A.password , A.passwordDecrypt, A.rfid ,IF(A.responsible is NULL, 1,NULL) ,  A.created_at, A.updated_at
  FROM `congress_v2`.Admin as A;


DELETE FROM `Admin_Congress` ;


INSERT IGNORE INTO `congress_v3`.Admin_Congress (admin_id,congress_id,privilege_id,created_at,updated_at)
  SELECT  C.admin_id , C.congress_id , 1,   C.created_at, C.updated_at
  FROM `congress_v2`.Congress as C;


DELETE FROM `Badge` ;


INSERT IGNORE INTO `congress_v3`.Badge (badge_id,badge_id_generator,privilege_id,congress_id,created_at,updated_at)
  SELECT  *
  FROM `congress_v2`.Badge;


DELETE FROM `Attestation_Access` ;


INSERT IGNORE INTO `congress_v3`.Attestation_Access (attestation_access_id,attestation_generator_id,access_id,created_at,updated_at)
  SELECT  *
  FROM `congress_v2`.Attestation_Access;

DELETE FROM `Attestation` ;

INSERT IGNORE INTO `congress_v3`.Attestation (attestation_id,attestation_generator_id_blank,attestation_generator_id,congress_id,created_at,updated_at)
  SELECT  *
  FROM `congress_v2`.Attestation;

DELETE FROM `Form_Input_Type` ;


INSERT IGNORE INTO `congress_v3`.Form_Input_Type (form_input_type_id,name,display_name,created_at,updated_at)
  SELECT  *
  FROM `congress_v2`.Form_Input_Type;


DELETE FROM `Form_Input` ;


INSERT IGNORE INTO `congress_v3`.Form_Input (form_input_id,label,congress_id,form_input_type_id
                                           ,created_at,updated_at)
  SELECT  *
  FROM `congress_v2`.Form_Input;

DELETE FROM `Form_Input_Value` ;


INSERT IGNORE INTO `congress_v3`.Form_Input_Value (form_input_value_id,value,form_input_id
                                           ,created_at,updated_at)
  SELECT  *
  FROM `congress_v2`.Form_Input_Value;


DELETE FROM `Form_Input_Response` ;


INSERT IGNORE INTO `congress_v3`.Form_Input_Response (form_input_response_id,response,user_id,form_input_id
                                           ,created_at,updated_at)
  SELECT  F.form_input_reponse_id , F.reponse , F.user_id, F.form_input_id , F.created_at , F.updated_at
  FROM `congress_v2`.Form_Input_Reponse as F;


DELETE FROM `Response_Value` ;


INSERT IGNORE INTO `congress_v3`.Response_Value (response_value_id , form_input_response_id , form_input_value_id , created_at , updated_at)
  SELECT RV.reponse_value_id , RV.form_input_reponse_id , RV.form_input_value_id , RV.created_at , RV.updated_at
  FROM `congress_v2`.Reponse_Value as RV;

DELETE FROM `Access` ;


INSERT IGNORE INTO `congress_v3`.Access (access_id,name,price,duration,max_places,total_present_in_congress,seuil,room,
                                         description,real_start_date,start_date,end_date,show_in_program,show_in_register,with_attestation,congress_id,
                                         access_type_id
                                           ,created_at,updated_at)
  SELECT  A.access_id , A.name , A.price, A.duration , A.max_places , A.total_present_in_congress , A.seuil , "-" , "-" , A.start_date , A.theoric_start_data , A.theoric_end_data , 1 , 1 , 1 , A.congress_id , IF(A.intuitive = 0 , 2 , 1 ) , A.created_at , A.updated_at
  FROM `congress_v2`.Access as A;


DELETE FROM `User_Access` ;


INSERT IGNORE INTO `congress_v3`.User_Access (user_access_id,isPresent,user_id,access_id,
                                           created_at,updated_at)
  SELECT *
  FROM `congress_v2`.User_Access;

DELETE FROM `Access_Presence` ;


INSERT IGNORE INTO `congress_v3`.Access_Presence (access_presence_id,entered_at,left_at,user_id,access_id,
                                           created_at,updated_at)
  SELECT AP.access_presence_id , AP.enter_time , AP.leave_time , AP.user_id , AP.access_id , AP.created_at , AP.updated_at
  FROM `congress_v2`.Access_Presence as AP;

DELETE FROM `Access_Vote` ;


INSERT IGNORE INTO `congress_v3`.Access_Vote (access_vote_id , vote_id , access_id , congress_id , created_at, updated_at)
  SELECT AV.access_vote_id , AV.vote_id , AV.access_id , AV.congress_id , AV.created_at , AV.updated_at
  FROM `congress_v2`.Access_Vote as AV;



DELETE FROM `Vote_Score` ;


INSERT IGNORE INTO `congress_v3`.Vote_Score (vote_score_id , score , num_user_vote , access_vote_id , user_id , created_at , updated_at)
  SELECT VS.vote_score_id , VS.score , VS.num_user_vote , VS.access_vote_id , VS.user_id , VS.created_at , VS.updated_at
  FROM `congress_v2`.Vote_Score as VS;