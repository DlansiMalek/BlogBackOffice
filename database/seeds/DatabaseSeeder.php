<?php

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
        //TODO Config Mail Seeder
        //TODO Resource Seeder
        //TODO AccessPresence Seeder
        //TODO VoteScore Seeder
        //TODO UserMail Seeder

        // $this->call(CongressSeedTable::class);
        // $this->call(ConfigCongressSeedTable::class);
        $this->call(PrivilegeTableSeeder::class);
        $this->call(AdminSeedTable::class);
        // $this->call(OrganizationSeedTable::class);
        // $this->call(CongressOrganizationSeedTable::class);
        // $this->call(AdminCongressSeedTable::class);
        // $this->call(BadgeSeedTable::class);
        // $this->call(AttestationSeedTable::class);

        $this->call(AttestationTypeSeedTable::class);
        // $this->call(AttestationDiversSeedTable::class);
        $this->call(TopicSeedTable::class);
        $this->call(AccessTypeSeedTable::class);
        // $this->call(AccessSeedTable::class);
        // $this->call(AttestationAccessSeedTable::class);
        // $this->call(PackSeedTable::class);
        $this->call(CountriesSeedTable::class);
        // $this->call(UserSeedTable::class);
        // $this->call(AccessPackSeedTable::class);
        // $this->call(FormInputTypeSeedTable::class);
        // $this->call(FormInputSeedTable::class);
        // $this->call(FormInputValueSeedTable::class);
        // $this->call(FormInputResponseSeedTable::class);
        // $this->call(ResponseValueSeedTable::class);
        $this->call(MailTypeSeedTable::class);
        // $this->call(MailSeedTable::class);
        // $this->call(UserCongressSeedTable::class);
        $this->call(PaymentTypeSeeder::class);
        // $this->call(PaymentSeedTable::class);
        // $this->call(UserAccessSeedTable::class);
        // $this->call(FeedbackQuestionSeedTable::class);
        // $this->call(FeedbackValueSeedTable::class);
        // $this->call(FeedbackResponseSeedTable::class);
        // $this->call(AccessVoteSeedTable::class);
        // $this->call(AccessChairSeedTable::class);
        // $this->call(AccessSpeakerSeedTable::class);
        // $this->call(AttestationRequestSeedTable::class);
        // $this->call(LikeSeedTable::class);
        $this->call(CitySeedTable::class);
        // $this->call(LocationSeedTable::class);
        $this->call(PackAdminSeedTable::class);
        $this->call(ModuleSeedTable::class);
        $this->call(PackAdminModuleSeedTable::class);
        // $this->call(HistoryTableSeeder::class);
        // $this->call(PaymentAdminSeedTable::class);

        $this->call(MigrationOldData::class);


    }
}
