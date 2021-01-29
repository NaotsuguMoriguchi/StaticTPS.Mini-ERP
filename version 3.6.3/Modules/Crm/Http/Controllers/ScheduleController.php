<?php

namespace Modules\Crm\Http\Controllers;

use App\Contact;
use App\Http\Controllers\Controller;
use App\User;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Modules\Crm\Entities\CrmContact;
use Modules\Crm\Entities\Schedule;
use Yajra\DataTables\Facades\DataTables;

class ScheduleController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $commonUtil;
    protected $moduleUtil;
    /**
     * Constructor
     *
     * @param CommonUtil
     * @return void
     */
    public function __construct(Util $commonUtil, ModuleUtil $moduleUtil)
    {
        $this->commonUtil = $commonUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'crm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $start_date = request()->start;
            $end_date = request()->end;

            $schedules = $this->_scheduleQuery($business_id, $start_date, $end_date);

            $events = [];
            foreach ($schedules as $schedule) {
                $backgroundColor = '#FFAD46';
                $borderColor = '#FFAD46';
                if ($schedule->status == 'scheduled') {
                    $backgroundColor = '#FFAD46';
                    $borderColor = '#FFAD46';
                } elseif ($schedule->status == 'open') {
                    $backgroundColor = '#3c8dbc';
                    $borderColor = '#3c8dbc';
                } elseif ($schedule->status == 'canceled') {
                    $backgroundColor = '#f5365c';
                    $borderColor = '#f5365c';
                } elseif ($schedule->status == 'completed') {
                    $backgroundColor = '#2dce89';
                    $borderColor = '#2dce89';
                }

                $events[] = [
                        'title' => $schedule->title,
                        'start' => $schedule->start_datetime,
                        'end' => $schedule->end_datetime,
                        'url' => action('\Modules\Crm\Http\Controllers\ScheduleController@show', [ $schedule->id ]),
                        'backgroundColor' => $backgroundColor,
                        'borderColor'     => $borderColor,
                    ];
            }
            
            return $events;
        }

        return view('crm::schedule.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'crm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $schedule_for = request()->get('schedule_for', 'customer');
        $contact_id = request()->get('contact_id', '');
        if ($schedule_for == 'lead') {
            $customers = CrmContact::leadsDropdown($business_id, false);
        } else {
            $customers = Contact::customersDropdown($business_id, false);
        }

        $users = User::forDropdown($business_id, false);
        $statuses = Schedule::statusDropdown();

        return view('crm::schedule.create')
            ->with(compact('customers', 'users', 'statuses', 'contact_id', 'schedule_for'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'crm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only('title', 'contact_id', 'description', 'schedule_type', 'notify_before', 'notify_type', 'status');

            $input['notify_via'] = [
                            'sms' => !empty($request->input('sms'))? 1: 0,
                            'mail' => !empty($request->input('mail'))? 1: 0
                        ];

            $input['start_datetime'] = $this->commonUtil->uf_date($request->input('start_datetime'), true);
            $input['end_datetime'] = $this->commonUtil->uf_date($request->input('end_datetime'), true);
            $input['allow_notification'] = !empty($request->input('allow_notification')) ? 1 : 0;
            $input['business_id'] = $business_id;
            $input['created_by'] = $request->user()->id;
            
            $assigned_user = $request->input('user_id');

            $schedule = Schedule::create($input);
            $schedule->users()->sync($assigned_user);

            $schedule_for = $request->get('schedule_for', 'customer');
            $output = [
                    'success' => true,
                    'msg' => __('lang_v1.success'),
                    'schedule_for' => $schedule_for
                ];
        } catch (Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong')
                ];
        }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'crm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $schedule = Schedule::with(['customer', 'users'])
                        ->where('business_id', $business_id)
                        ->findOrFail($id);

        return view('crm::schedule.show')
            ->with(compact('schedule'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'crm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $schedule = Schedule::with('users', 'customer')
                        ->where('business_id', $business_id)
                        ->findOrFail($id);

        $schedule_for = request()->get('schedule_for', 'customer');
        if ($schedule_for == 'lead' || $schedule->customer->type == 'lead') {
            $customers = CrmContact::leadsDropdown($business_id, false);
        } elseif ($schedule_for == 'customer' || $schedule->customer->type == 'customer') {
            $customers = Contact::customersDropdown($business_id, false);
        }

        $users = User::forDropdown($business_id, false);
        $statuses = Schedule::statusDropdown();

        return view('crm::schedule.edit')
            ->with(compact('schedule', 'customers', 'users', 'statuses', 'schedule_for'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'crm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only('title', 'contact_id', 'description', 'schedule_type', 'notify_before', 'notify_type', 'status');

            $input['notify_via'] = [
                            'sms' => !empty($request->input('sms'))? 1: 0,
                            'mail' => !empty($request->input('mail'))? 1: 0
                        ];

            $input['start_datetime'] = $this->commonUtil->uf_date($request->input('start_datetime'), true);
            $input['end_datetime'] = $this->commonUtil->uf_date($request->input('end_datetime'), true);
            $input['allow_notification'] = !empty($request->input('allow_notification')) ? 1 : 0;
            $assigned_user = $request->input('user_id');

            $schedule = Schedule::where('business_id', $business_id)
                        ->findOrFail($id);

            $schedule->update($input);
            $schedule->users()->sync($assigned_user);

            $schedule_for = $request->get('schedule_for', 'customer');
            $output = [
                    'success' => true,
                    'msg' => __('lang_v1.success'),
                    'schedule_for' => $schedule_for
                ];
        } catch (Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong')
                ];
        }

        return $output;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'crm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $schedule = Schedule::where('business_id', $business_id)
                            ->findOrFail($id);

                $schedule->delete();

                $view_type = request()->get('view_type', 'schedule');
                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.success'),
                    'action' => action('\Modules\Crm\Http\Controllers\ScheduleController@index'),
                    'view_type' => $view_type
                ];
            } catch (Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong')
                ];
            }
            return $output;
        }
    }

    /**
     * Get today's schedule
     *
     * @return \Illuminate\Http\Response
     */
    public function getTodaysSchedule()
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'crm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $start_date = Carbon::today();

            $schedules = $this->_scheduleQuery($business_id, $start_date);

            $schedule_html = View::make('crm::schedule.partial.today_schedule')
                        ->with(compact('schedules'))
                        ->render();
            $output = [
                'success' => true,
                'msg' => __('lang_v1.success'),
                'todays_schedule' => $schedule_html
            ];
        } catch (Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ];
        }
        return $output;
    }

    protected function _scheduleQuery($business_id, $start_date, $end_date = null)
    {
        $query = Schedule::where('business_id', $business_id);

        if (!empty($start_date) && !empty($end_date)) {
            $query->whereBetween(DB::raw('date(start_datetime)'), [$start_date, $end_date]);
        } else {
            $query->where(DB::raw('date(start_datetime)'), $start_date);
        }

        //if not admin get assigned schedule only
        if (!$this->commonUtil->is_admin(auth()->user(), $business_id)) {
            $user_id = auth()->user()->id;
            $query->whereHas('users', function ($q) use ($user_id) {
                $q->where('user_id', $user_id);
            });
        }

        $schedules = $query->get();

        return $schedules;
    }

    public function getLeadSchedule(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'crm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $lead_id = $request->get('lead_id');
        $schedules = Schedule::with('users')
                        ->where('business_id', $business_id)
                        ->where('contact_id', $lead_id)
                        ->select('*');

        return Datatables::of($schedules)
            ->addColumn('action', function ($row) {
                $html = '<div class="btn-group">
                            <button class="btn btn-info dropdown-toggle btn-xs" type="button"  data-toggle="dropdown" aria-expanded="false">
                                '. __("messages.action").'
                                <span class="caret"></span>
                                <span class="sr-only">'
                                   . __("messages.action").'
                                </span>
                            </button>
                              <ul class="dropdown-menu dropdown-menu-left" role="menu">
                               <li>
                                    <a href="' . action('\Modules\Crm\Http\Controllers\ScheduleController@show', ['id' => $row->id]) . '" class="cursor-pointer view_schedule">
                                        <i class="fa fa-eye"></i>
                                        '.__("messages.view").'
                                    </a>
                                </li>
                                <li>
                                    <a data-href="' . action('\Modules\Crm\Http\Controllers\ScheduleController@edit', ['id' => $row->id]) . '?schedule_for=lead"class="cursor-pointer schedule_edit">
                                        <i class="fa fa-edit"></i>
                                        '.__("messages.edit").'
                                    </a>
                                </li>
                                <li>
                                    <a data-href="' . action('\Modules\Crm\Http\Controllers\ScheduleController@destroy', ['id' => $row->id]) . '" class="cursor-pointer schedule_delete">
                                        <i class="fas fa-trash"></i>
                                        '.__("messages.delete").'
                                    </a>
                                </li>';

                $html .= '</ul>
                        </div>';

                return $html;
            })
            ->editColumn('start_datetime', '
                {{@format_datetime($start_datetime)}}
            ')
            ->editColumn('end_datetime', '
                {{@format_datetime($end_datetime)}}
            ')
            ->editColumn('users', function ($row) {
                $html = '&nbsp;';
                foreach ($row->users as $user) {
                    if (isset($user->media->display_url)) {
                        $html .= '<img class="user_avatar" src="'.$user->media->display_url.'" data-toggle="tooltip" title="'.$user->user_full_name.'">';
                    } else {
                        $html .= '<img class="user_avatar" src="https://ui-avatars.com/api/?name='.$user->first_name.'" data-toggle="tooltip" title="'.$user->user_full_name.'">';
                    }
                }

                return $html;
            })
            ->removeColumn('id')
            ->rawColumns(['action', 'start_datetime', 'end_datetime', 'users'])
            ->make(true);
    }
}
