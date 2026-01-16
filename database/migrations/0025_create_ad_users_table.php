<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("ad_users", function (Blueprint $table) 
		{
            $table->id();

			$table->string("employee_type", 50);
            $table->string("sid", 256)->unique();
            $table->uuid("guid")->nullable()->index();
            $table->string("username");
            $table->string("firstname")->nullable();
            $table->string("lastname")->nullable();
            $table->string("display_name")->nullable();
            $table->string("email")->nullable();

            $table->boolean("is_enabled")->default(true);
            $table->boolean("is_existing")->default(true);
            $table->boolean("password_never_expires")->default(false);

            $table->timestamp("account_expiration_date")->nullable();
            $table->timestamp("created")->nullable();
            $table->timestamp("modified")->nullable();
            $table->timestamp("last_bad_password_attempt")->nullable();
            $table->timestamp("last_logon_date")->nullable();
            $table->timestamp("password_last_set")->nullable();

            $table->integer("logon_count")->nullable();

            $table->string("city")->nullable();
            $table->string("company")->nullable();
            $table->string("country")->nullable();
            $table->string("department")->nullable();
            $table->text("description")->nullable();
            $table->string("division")->nullable();
            $table->string("fax")->nullable();
            $table->string("home_directory")->nullable();
            $table->string("home_page")->nullable();
            $table->string("home_phone")->nullable();
            $table->string("initials")->nullable();
            $table->string("office")->nullable();
            $table->string("office_phone")->nullable();
            $table->string("postal_code")->nullable();
            $table->string("profile_path")->nullable();
            $table->string("state")->nullable();
            $table->string("street_address")->nullable();
            $table->string("title")->nullable();
            $table->string("manager_dn")->nullable();
			$table->longText("profile_photo_base64")->nullable();

            $table->string("distinguished_name")->nullable();
            $table->string("user_principal_name")->nullable();

            $table->json("proxy_addresses")->nullable();
            $table->json("member_of")->nullable();

            for ($i = 1; $i <= 15; $i++) 
			{
                $table->string("extensionattribute{$i}")->nullable();
            }

			$table->foreignId("funktion_id")->nullable()->constrained("funktionen")->nullOnDelete();
			$table->foreignId("abteilung_id")->nullable()->constrained("abteilungen")->nullOnDelete();
			$table->foreignId("arbeitsort_id")->nullable()->constrained("arbeitsorte")->nullOnDelete();
			$table->foreignId("unternehmenseinheit_id")->nullable()->constrained("unternehmenseinheiten")->nullOnDelete();
			$table->foreignId("anrede_id")->nullable()->constrained("anreden")->nullOnDelete();
			$table->foreignId("titel_id")->nullable()->constrained("titel")->nullOnDelete();

            $table->timestamp("last_synced_at")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("ad_users");
    }
};
