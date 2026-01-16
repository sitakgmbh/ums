<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\AdUserEmployeeType;
use App\Models\EmployeeLifecycle;

class AdUser extends Model
{
    protected $table = "ad_users";
	protected $appends = ['employee_type_value'];

    protected $fillable = [
        "employee_type",
		"sid",
        "guid",
        "username",
        "firstname",
        "lastname",
        "display_name",
        "email",

        "is_enabled",
        "is_existing",
        "password_never_expires",

        "account_expiration_date",
        "created",
        "modified",
        "last_bad_password_attempt",
        "last_logon_date",
        "password_last_set",

        "logon_count",

        "city",
        "company",
        "country",
        "department",
        "description",
        "division",
        "fax",
        "home_directory",
        "home_page",
        "home_phone",
        "initials",
        "office",
        "office_phone",
        "postal_code",
        "profile_path",
        "state",
        "street_address",
        "title",
        "manager_dn",
		"profile_photo_base64",

        "distinguished_name",
        "user_principal_name",

        "proxy_addresses",
        "member_of",

        "extensionattribute1",
        "extensionattribute2",
        "extensionattribute3",
        "extensionattribute4",
        "extensionattribute5",
        "extensionattribute6",
        "extensionattribute7",
        "extensionattribute8",
        "extensionattribute9",
        "extensionattribute10",
        "extensionattribute11",
        "extensionattribute12",
        "extensionattribute13",
        "extensionattribute14",
        "extensionattribute15",

        "funktion_id",
        "abteilung_id",
        "arbeitsort_id",
        "unternehmenseinheit_id",
        "anrede_id",
        "titel_id",

        "last_synced_at",
    ];

    protected $casts = [
	    'employee_type' => AdUserEmployeeType::class,
        "is_enabled" => "boolean",
        "is_existing" => "boolean",
        "password_never_expires" => "boolean",

        "account_expiration_date" => "datetime",
        "created" => "datetime",
        "modified" => "datetime",
        "last_bad_password_attempt" => "datetime",
        "last_logon_date" => "datetime",
        "password_last_set" => "datetime",

        "proxy_addresses" => "array",
        "member_of" => "array",

        "last_synced_at" => "datetime",
    ];

    public function funktion()
    {
        return $this->belongsTo(Funktion::class);
    }

    public function abteilung()
    {
        return $this->belongsTo(Abteilung::class);
    }

    public function arbeitsort()
    {
        return $this->belongsTo(Arbeitsort::class);
    }

    public function unternehmenseinheit()
    {
        return $this->belongsTo(Unternehmenseinheit::class);
    }

    public function anrede()
    {
        return $this->belongsTo(Anrede::class);
    }

    public function titel()
    {
        return $this->belongsTo(Titel::class);
    }

	public function sapExport()
	{
		return $this->hasOne(SapExport::class);
	}
	
	/*
	Auskommentiert am 16.01.25, sollte überflüssig sein.
	
	public function adUser()
	{
		return $this->hasOne(AdUser::class, 'sid', 'ad_sid');
	}
	*/

	public function employeeType(): AdUserEmployeeType
	{
		return $this->employee_type ?? AdUserEmployeeType::Unknown;
	}

	public function getEmployeeTypeValueAttribute(): string
	{
		return $this->employee_type?->value ?? 'unknown';
	}
}
