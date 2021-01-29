<?php

namespace Modules\Crm\Http\Controllers;

use App\Business;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Illuminate\Routing\Controller;
use Menu;
use Modules\Crm\Entities\Schedule;

class DataController extends Controller
{

    /**
     * Parses notification message from database.
     * @return array
     */
    public function parse_notification($notification)
    {
        $commonUtil = new Util();

        $notification_datas = [];
        if ($notification->type == 'Modules\Crm\Notifications\ScheduleNotification') {
            $data = $notification->data;
            $schedule = Schedule::with('createdBy')
                        ->where('business_id', $data['business_id'])
                        ->find($data['schedule_id']);

            if (!empty($schedule)) {
                $business = Business::find($data['business_id']);
                $startdatetime = $commonUtil->format_date($schedule->start_datetime, true, $business);
                $msg = __(
                    'crm::lang.schedule_notification',
                    [
                    'created_by' => $schedule->createdBy->user_full_name,
                    'title' => $schedule->title,
                    'startdatetime' => $startdatetime
                    ]
                );

                $notification_datas = [
                    'msg' => $msg,
                    'icon_class' => 'fas fa fa-calendar-check bg-green',
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans()
                ];
            }
        }

        return $notification_datas;
    }

    /**
     * Adds Crm menus
     * @return null
     */
    public function modifyAdminMenu()
    {
        $business_id = session()->get('user.business_id');
        $module_util = new ModuleUtil();
        
        $is_crm_enabled = (boolean)$module_util->hasThePermissionInSubscription($business_id, 'crm_module');

        $commonUtil = new Util();
        $is_admin = $commonUtil->is_admin(auth()->user(), $business_id);

        if ($is_crm_enabled) {
            Menu::modify(
                'admin-sidebar-menu',
                function ($menu) use ($is_admin) {
                    $menu->url(action('\Modules\Crm\Http\Controllers\CrmDashboardController@index'), __('crm::lang.crm'), ['icon' => 'fas fa fa-broadcast-tower', 'active' => request()->segment(1) == 'crm' || request()->get('type') == 'life_stage' || request()->get('type') == 'source'])->order(86);
                }
            );
        }
    }
    
    /**
     * Returns Tab path with required extra data.
     * for contact view
     * @return array
     */
    public function get_contact_view_tabs()
    {
        $module_util = new ModuleUtil();
        $business_id = request()->session()->get('user.business_id');
        $is_crm_enabled = (boolean)$module_util->hasThePermissionInSubscription($business_id, 'crm_module');

        if ($is_crm_enabled) {
            //for multiple tab just add another array of tab details and if js is in common file just include once in any array
            return  [
                [
                    'tab_menu_path' => 'crm::contact_login.partial.tab_menu',
                    'tab_content_path' => 'crm::contact_login.partial.tab_content',
                    'tab_data' => [],
                    'module_js_path' => 'crm::contact_login.contact_login_js'
                ],
            ];
        } else {
            return [];
        }
    }

    /**
    * Function to add essential module taxonomies
    * @return array
    */
    public function addTaxonomies()
    {
        $module_util = new ModuleUtil();
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $module_util->hasThePermissionInSubscription($business_id, 'crm_module'))) {
            return [
                'source' => [],
                'life_stage' => []
            ];
        }

        return [
            'source' => [
                'taxonomy_label' =>  __('crm::lang.source'),
                'heading' => __('crm::lang.sources'),
                'sub_heading' => __('crm::lang.manage_source'),
                'enable_taxonomy_code' => false,
                'enable_sub_taxonomy' => false,
                'heading_tooltip' => __('crm::lang.source_are_used_when_lead_is_added'),
                'navbar' => 'crm::layouts.nav'
            ],

            'life_stage' => [
                'taxonomy_label' =>  __('crm::lang.life_stage'),
                'heading' => __('crm::lang.life_stage'),
                'sub_heading' => __('crm::lang.manage_life_stage'),
                'enable_taxonomy_code' => false,
                'enable_sub_taxonomy' => false,
                'heading_tooltip' => __('crm::lang.lifestage_of_leads'),
                'navbar' => 'crm::layouts.nav'
            ]
        ];
    }
}
