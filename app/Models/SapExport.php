<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SapExport extends Model
{
    protected $table = "sap_export";

    protected $fillable = [
        "d_pernr",
        "d_anrlt",
        "d_titel",
        "d_name",
        "d_vname",
        "d_rufnm",
        "d_gbdat",
        "d_empct",
        "d_bort",
        "d_natio",
        "d_arbortx",
        "d_0032_batchbez",
        "d_einri",
        "d_ptext",
        "d_email",
        "d_pers_txt",
        "d_abt_nr",
        "d_abt_txt",
        "d_0032_batchid",
        "d_tel01",
        "d_zzbereit",
        "d_personid_ext",
        "d_zzkader",
        "d_adr1_name2",
        "d_adr1_stras",
        "d_adr1_pstlz",
        "d_adr1_ort01",
        "d_adr1_land1",
        "d_adr1_telnr",
        "d_adr5_name2",
        "d_adr5_stras",
        "d_adr5_pstlz",
        "d_adr5_ort01",
        "d_adr5_land1",
        "d_email_privat",
        "d_nebenamt",
        "d_nebenbesch",
        "d_einda",
        "d_endda",
        "d_fmht1",
        "d_fmht1zus",
        "d_fmht2",
        "d_fmht2zus",
        "d_fmht3",
        "d_fmht3zus",
        "d_fmht4",
        "d_fmht4zus",
        "d_kbcod",
        "d_leader",
        'ad_user_id',
        'alarm_enabled',
    ];

    protected $casts = [
        'alarm_enabled' => 'boolean',
    ];

	public function hasAdUser(): bool
	{
		return $this->ad_user_id !== null;
	}

    public function adUser()
    {
        return $this->belongsTo(AdUser::class);
    }
}