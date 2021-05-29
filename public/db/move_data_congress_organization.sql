UPDATE `Organization`
INNER JOIN `Congress_Organization` ON `Congress_Organization`.`organization_id` = `Organization`.`organization_id`
SET `Organization`.`congress_id`  = `Congress_Organization`.`congress_id` ,
    `Organization`.`montant` = `Congress_Organization`.`montant`