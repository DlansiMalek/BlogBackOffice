<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(MenuDatabaseSeeder::class);
        $this->call(CongressTypeTableSeeder::class);
        $this->call(CongressSeedTable::class);
        $this->call(ConfigCongressSeedTable::class);
        $this->call(PrivilegeTableSeeder::class);
        $this->call(AdminSeedTable::class);
        $this->call(OrganizationSeedTable::class);
        $this->call(CongressOrganizationSeedTable::class);
        $this->call(AdminCongressSeedTable::class);
        $this->call(BadgeSeedTable::class);
        $this->call(BadgeParamsSeeder::class);
        $this->call(CommunicationTypeSeeder::class);
        $this->call(AttestationSeedTable::class);
        $this->call(AttestationTypeSeedTable::class);
        $this->call(AttestationDiversSeedTable::class);
        $this->call(TopicSeedTable::class);
        $this->call(AccessTypeSeedTable::class);
        $this->call(AccessSeedTable::class);
        $this->call(AttestationAccessSeedTable::class);
        $this->call(PackSeedTable::class);
        $this->call(CountriesSeedTable::class);
        $this->call(PackAdminSeeder::class);
        $this->call(HistoryTableSeeder::class);
        $this->call(UserSeedTable::class);
        $this->call(UserPackSeeder::class);
        $this->call(AccessPackSeedTable::class);
        $this->call(Custom_SMSTableSeeder::class);
        $this->call(User_SMSTableSeeder::class);

        $this->call(FormInputTypeSeedTable::class);
        $this->call(FormInputSeedTable::class);
        $this->call(FormInputValueSeedTable::class);
        $this->call(FormInputResponseSeedTable::class);
        $this->call(ResponseValueSeedTable::class);

        $this->call(ConfigSelectionSeeder::class);
        $this->call(UserCongressSeedTable::class);

        $this->call(ConfigMailSeeder::class);
        $this->call(MailTypeSeedTable::class);
        $this->call(MailSeedTable::class);
        $this->call(UserMailSeeder::class);
        $this->call(MailTypeAdminSeedTable::class);
        $this->call(MailAdminSeedTable::class);
        $this->call(UserMailAdminSeeder::class);

        $this->call(LikeSeedTable::class);
        $this->call(ResourceSeeder::class);
        $this->call(UserNotifCongressSeeder::class);

        $this->call(ThemeTableSeeder::class);
        $this->call(ConfigSubmissionSeeder::class);
        $this->call(SubmissionTableSeeder::class);
        $this->call(SubmissionEvaluationTableSeeder::class);
        $this->call(CongressThemeTableSeeder::class);
        $this->call(ResourceSubmissionSeeder::class);
        $this->call(EtablissementSeeder::class);
        $this->call(ServiceSeeder::class);
        $this->call(AuthorSeeder::class);

        $this->call(ThemeAdminSeeder::class);
        $this->call(PaymentTypeSeeder::class);
        $this->call(PaymentSeedTable::class);
        $this->call(UserAccessSeedTable::class);
        $this->call(AccessPresenceSeeder::class);

        $this->call(FeedbackQuestionSeedTable::class);
        $this->call(FeedbackValueSeedTable::class);
        $this->call(FeedbackResponseSeedTable::class);

        $this->call(AttestationRequestSeedTable::class);
        $this->call(AccessVoteSeedTable::class);
        $this->call(AccessChairSeedTable::class);
        $this->call(AccessSpeakerSeedTable::class);
        $this->call(VoteScoreSeeder::class);
        $this->call(ModuleSeedTable::class);
        $this->call(PackAdminModuleSeeder::class);

        $this->call(OffreTypeSeedTable::class);
        $this->call(OffreSeeder::class);
        $this->call(PaymentAdminSeedTable::class);
        $this->call(RoomSeedTable::class);
        $this->call(EvaluationInscriptionSeeder::class);

        $this->call(CitySeedTable::class);
        $this->call(LocationSeeder::class);
    }
}
