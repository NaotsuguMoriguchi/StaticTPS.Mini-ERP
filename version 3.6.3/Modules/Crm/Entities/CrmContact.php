<?php

namespace Modules\Crm\Entities;

use App\Contact;
use DB;
use Illuminate\Database\Eloquent\Model;

class CrmContact extends Contact
{
    /**
      * The table associated with the model.
      *
      * @var string
      */
    protected $table = 'contacts';

    /**
    * The member that assigned to the lead.
    */
    public function leadUsers()
    {
        return $this->belongsToMany('App\User', 'crm_lead_users', 'contact_id', 'user_id');
    }

    /**
     * get source for contact
     */
    public function Source()
    {
        return $this->belongsTo('App\Category', 'crm_source');
    }

    /**
     * get life_stage for contact
     */
    public function lifeStage()
    {
        return $this->belongsTo('App\Category', 'crm_life_stage');
    }

    /**
     * Return list of lead dropdown for a business
     *
     * @param $business_id int
     * @param $prepend_none = true (boolean)
     *
     * @return array users
     */
    public static function leadsDropdown($business_id, $prepend_none = true, $append_id = true)
    {
        $all_contacts = Contact::where('business_id', $business_id)
                        ->where('type', 'lead')
                        ->active();

        if ($append_id) {
            $all_contacts->select(
                DB::raw("IF(contact_id IS NULL OR contact_id='', name, CONCAT(name, ' (', contact_id, ')')) AS leads"),
                'id'
                );
        } else {
            $all_contacts->select('id', DB::raw("name as leads"));
        }

        $leads = $all_contacts->pluck('leads', 'id');

        //Prepend none
        if ($prepend_none) {
            $leads = $leads->prepend(__('lang_v1.none'), '');
        }

        return $leads;
    }

    public static function contactsDropdownForLogin($business_id, $append_contact_id = true)
    {
        $all_contacts = Contact::where('business_id', $business_id)
                        ->active();

        if ($append_contact_id) {
            $all_contacts->select(
                DB::raw("IF(contact_id IS NULL OR contact_id='', name, CONCAT(name, ' (', contact_id, ')')) AS contacts"),
                'id'
                );
        } else {
            $all_contacts->select('id', DB::raw("name as contacts"));
        }

        $contacts = $all_contacts->pluck('contacts', 'id');

        return $contacts;
    }
}
