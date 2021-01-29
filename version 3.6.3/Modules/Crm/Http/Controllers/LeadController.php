<?php

namespace Modules\Crm\Http\Controllers;

use App\Category;
use App\User;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Crm\Entities\CrmContact;
use Yajra\DataTables\Facades\DataTables;

class LeadController extends Controller
{
    protected $commonUtil;
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param Util $commonUtil
     * @return void
     */
    public function __construct(Util $commonUtil, ModuleUtil $moduleUtil)
    {
        $this->commonUtil = $commonUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'crm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $life_stages = Category::forDropdown($business_id, 'life_stage');
        
        if (is_null(request()->get('lead_view'))) {
            $lead_view = 'list_view';
        } else {
            $lead_view = request()->get('lead_view');
        }

        if (request()->ajax()) {
            $leads = CrmContact::with(['Source', 'lifeStage', 'leadUsers'])
                        ->where('business_id', $business_id)
                        ->where('type', 'lead')
                        ->select('contact_id', 'name', 'email', 'mobile', 'tax_number', 'created_at', 'custom_field1', 'custom_field2', 'custom_field3', 'custom_field4', 'id', 'crm_source', 'crm_life_stage');

            if (!empty(request()->get('source'))) {
                $leads->where('crm_source', request()->get('source'));
            }

            if (!empty(request()->get('life_stage'))) {
                $leads->where('crm_life_stage', request()->get('life_stage'));
            }
            
            if ($lead_view == 'list_view') {
                return Datatables::of($leads)
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
                                            <a href="' . action('\Modules\Crm\Http\Controllers\LeadController@show', ['id' => $row->id]) . '" class="cursor-pointer view_lead">
                                                <i class="fa fa-eye"></i>
                                                '.__("messages.view").'
                                            </a>
                                        </li>
                                        <li>
                                            <a data-href="' . action('\Modules\Crm\Http\Controllers\LeadController@edit', ['id' => $row->id]) . '"class="cursor-pointer edit_lead">
                                                <i class="fa fa-edit"></i>
                                                '.__("messages.edit").'
                                            </a>
                                        </li>
                                        <li>
                                            <a data-href="' . action('\Modules\Crm\Http\Controllers\LeadController@convertToCustomer', ['id' => $row->id]) . '" class="cursor-pointer convert_to_customer">
                                                <i class="fas fa-redo"></i>
                                                '.__("crm::lang.convert_to_customer").'
                                            </a>
                                        </li>
                                        <li>
                                            <a data-href="' . action('\Modules\Crm\Http\Controllers\LeadController@destroy', ['id' => $row->id]) . '" class="cursor-pointer delete_a_lead">
                                                <i class="fas fa-trash"></i>
                                                '.__("messages.delete").'
                                            </a>
                                        </li>';

                        $html .= '</ul>
                                </div>';

                        return $html;
                    })
                    ->editColumn('created_at', '
                        {{@format_date($created_at)}}
                    ')
                    ->editColumn('crm_source', function ($row) {
                        return optional($row->Source)->name;
                    })
                    ->editColumn('crm_life_stage', function ($row) {
                        return optional($row->lifeStage)->name;
                    })
                    ->editColumn('leadUsers', function ($row) {
                        $html = '&nbsp;';
                        foreach ($row->leadUsers as $leadUser) {
                            if (isset($leadUser->media->display_url)) {
                                $html .= '<img class="user_avatar" src="'.$leadUser->media->display_url.'" data-toggle="tooltip" title="'.$leadUser->user_full_name.'">';
                            } else {
                                $html .= '<img class="user_avatar" src="https://ui-avatars.com/api/?name='.$leadUser->first_name.'" data-toggle="tooltip" title="'.$leadUser->user_full_name.'">';
                            }
                        }

                        return $html;
                    })
                    ->removeColumn('id')
                    ->rawColumns(['action', 'crm_source', 'crm_life_stage', 'leadUsers', 'created_at'])
                    ->make(true);
            } elseif ($lead_view == 'kanban') {
                $leads = $leads->get()->groupBy('crm_life_stage');
                //sort leads based on life stage
                $crm_leads = [];
                $board_draggable_to = [];
                foreach ($life_stages as $key => $value) {
                    $board_draggable_to[] = strval($key);
                    if (!isset($leads[$key])) {
                        $crm_leads[strval($key)] = [];
                    } else {
                        $crm_leads[strval($key)] = $leads[$key];
                    }
                }

                $leads_html = [];
                foreach ($crm_leads as $key => $leads) {
                    //get all the leads for particular board(life stage)
                    $cards = [];
                    foreach ($leads as $lead) {
                        $edit = action('\Modules\Crm\Http\Controllers\LeadController@edit', ['id' => $lead->id]);
                        
                        $delete = action('\Modules\Crm\Http\Controllers\LeadController@destroy', ['id' => $lead->id]);

                        $view = action('\Modules\Crm\Http\Controllers\LeadController@show', ['id' => $lead->id]);

                        //if member then get their avatar
                        if ($lead->leadUsers->count() > 0) {
                            $assigned_to = [];
                            foreach ($lead->leadUsers as $member) {
                                if (isset($member->media->display_url)) {
                                    $assigned_to[$member->user_full_name] = $member->media->display_url;
                                } else {
                                    $assigned_to[$member->user_full_name] = "https://ui-avatars.com/api/?name=".$member->first_name;
                                }
                            }
                        }

                        $cards[] = [
                                'id' => $lead->id,
                                'title' => $lead->name,
                                'viewUrl' => $view,
                                'editUrl' => $edit,
                                'editUrlClass' => 'edit_lead',
                                'deleteUrl' => $delete,
                                'deleteUrlClass' => 'delete_a_lead',
                                'assigned_to' => $assigned_to,
                                'hasDescription' => false,
                                'tags' => [$lead->Source->name ?? ''],
                                'dragTo' => $board_draggable_to
                            ];
                    }

                    //get all the card & board title for particular board(life stage)
                    $leads_html[] = [
                        'id' => strval($key),
                        'title' => $life_stages[$key],
                        'cards' => $cards
                    ];
                }

                $output = [
                    'success' => true,
                    'leads_html' => $leads_html,
                    'msg' => __('lang_v1.success')
                ];

                return $output;
            }
        }

        $sources = Category::forDropdown($business_id, 'source');

        return view('crm::lead.index')
            ->with(compact('sources', 'life_stages', 'lead_view'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'crm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $users = User::forDropdown($business_id, false);
        $sources = Category::forDropdown($business_id, 'source');
        $life_stages = Category::forDropdown($business_id, 'life_stage');

        $types['lead'] = __('crm::lang.lead');
        $store_action = action('\Modules\Crm\Http\Controllers\LeadController@store');
        return view('contact.create')
            ->with(compact('types', 'store_action', 'sources', 'life_stages', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'crm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['type', 'prefix','first_name', 'middle_name', 'last_name','tax_number', 'mobile', 'landline', 'alternate_number', 'city', 'state', 'country', 'landmark', 'contact_id', 'custom_field1', 'custom_field2', 'custom_field3', 'custom_field4', 'email', 'crm_source', 'crm_life_stage', 'dob', 'address_line_1', 'address_line_2', 'zip_code']);

            $input['name'] = implode(' ', [$input['prefix'], $input['first_name'], $input['middle_name'], $input['last_name']]);

            if (!empty($input['dob'])) {
                $input['dob'] = $this->commonUtil->uf_date($input['dob']);
            }

            $input['business_id'] = $business_id;
            $input['created_by'] = $request->session()->get('user.id');

            $assigned_to = $request->input('user_id');

            //Check Contact id
            $count = 0;
            if (!empty($input['contact_id'])) {
                $count = CrmContact::where('business_id', $input['business_id'])
                            ->where('contact_id', $input['contact_id'])
                            ->count();
            }

            if ($count == 0) {
                //Update reference count
                $ref_count = $this->commonUtil->setAndGetReferenceCount('contacts');

                if (empty($input['contact_id'])) {
                    //Generate reference number
                    $input['contact_id'] = $this->commonUtil->generateReferenceNumber('contacts', $ref_count);
                }


                $contact = CrmContact::create($input);

                $contact->leadUsers()->sync($assigned_to);

                $output = ['success' => true,
                            'msg' => __("contact.added_success")
                        ];
            } else {
                throw new \Exception("Error Processing Request", 1);
            }
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => false,
                            'msg' =>__("messages.something_went_wrong")
                        ];
        }

        return $output;
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'crm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $contact = CrmContact::with('leadUsers', 'Source', 'lifeStage')
                    ->where('business_id', $business_id)
                    ->findOrFail($id);

        $leads = CrmContact::leadsDropdown($business_id, false);

        return view('crm::lead.show')
            ->with(compact('contact', 'leads'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'crm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $contact = CrmContact::with('leadUsers')
                    ->where('business_id', $business_id)
                    ->findOrFail($id);

        $users = User::forDropdown($business_id, false);
        $sources = Category::forDropdown($business_id, 'source');
        $life_stages = Category::forDropdown($business_id, 'life_stage');

        $types['lead'] = __('crm::lang.lead');
        $update_action = action('\Modules\Crm\Http\Controllers\LeadController@update', ['id' => $id]);

        return view('contact.edit')
            ->with(compact('contact', 'types', 'update_action', 'sources', 'life_stages', 'users'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'crm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            
            $input = $request->only(['type', 'prefix','first_name', 'middle_name', 'last_name','tax_number', 'mobile', 'landline', 'alternate_number', 'city', 'state', 'country', 'landmark', 'contact_id', 'custom_field1', 'custom_field2', 'custom_field3', 'custom_field4', 'email', 'crm_source', 'crm_life_stage', 'dob', 'address_line_1', 'address_line_2', 'zip_code']);

            $input['name'] = implode(' ', [$input['prefix'], $input['first_name'], $input['middle_name'], $input['last_name']]);

            if (!empty($input['dob'])) {
                $input['dob'] = $this->commonUtil->uf_date($input['dob']);
            }

            $assigned_to = $request->input('user_id');

            //Check Contact id
            if (!empty($input['contact_id'])) {
                $count = CrmContact::where('business_id', $business_id)
                            ->where('contact_id', $input['contact_id'])
                            ->where('id', '!=', $id)
                            ->count();
            }

            if ($count == 0) {
                $contact = CrmContact::where('business_id', $business_id)->findOrFail($id);
                $contact->update($input);

                $contact->leadUsers()->sync($assigned_to);

                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.success')
                ];
            } else {
                throw new \Exception("Error Processing Request", 1);
            }
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => false,
                            'msg' =>__("messages.something_went_wrong")
                        ];
        }

        return $output;
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'crm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $contact = CrmContact::where('business_id', $business_id)->findOrFail($id);
                $contact->delete();
                
                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.success')
                ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
            }

            return $output;
        }
    }

    public function convertToCustomer($id)
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'crm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $contact = CrmContact::where('business_id', $business_id)->findOrFail($id);
                
                $contact->type = 'customer';
                $contact->save();
                
                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.success')
                ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
            }

            return $output;
        }
    }

    public function postLifeStage($id)
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'crm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $contact = CrmContact::where('business_id', $business_id)->findOrFail($id);
                
                $contact->crm_life_stage = request()->input('crm_life_stage');
                $contact->save();
                
                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.success')
                ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
            }

            return $output;
        }
    }
}
