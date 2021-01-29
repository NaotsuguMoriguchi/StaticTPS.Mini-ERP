<?php

namespace Modules\Crm\Entities;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'crm_schedules';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
    * The attributes that should be cast to native types.
    *
    * @var array
    */
    protected $casts = [
        'notify_via' => 'array',
    ];
    
    /**
    * The member that belongs to the schedule.
    */
    public function users()
    {
        return $this->belongsToMany('App\User', 'crm_schedule_users', 'schedule_id', 'user_id');
    }

    /**
     * user who created a schedule.
     */
    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    public function customer()
    {
        return $this->belongsTo(\App\Contact::class, 'contact_id');
    }

    public function scheduleLog()
    {
        return $this->hasMany(\Modules\Crm\Entities\ScheduleLog::class);
    }

    /**
     * Return the status for schedule.
     */
    public static function statusDropdown()
    {
        $status = [
                'scheduled' => __('crm::lang.scheduled'),
                'open' => __('crm::lang.open'),
                'canceled' => __('crm::lang.canceled'),
                'completed' =>  __('crm::lang.completed')
            ];

        return $status;
    }
}
