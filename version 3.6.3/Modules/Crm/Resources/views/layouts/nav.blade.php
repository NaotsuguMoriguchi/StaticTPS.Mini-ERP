<section class="no-print">
    <nav class="navbar navbar-default bg-white m-4">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="{{action('\Modules\Crm\Http\Controllers\CrmDashboardController@index')}}"><i class="fas fa fa-broadcast-tower"></i> {{__('crm::lang.crm')}}</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <li @if(request()->segment(2) == 'contact-schedule') class="active" @endif><a href="{{action('\Modules\Crm\Http\Controllers\ScheduleController@index')}}">@lang('crm::lang.schedules')</a></li>

                    <li @if(request()->segment(2) == 'contact-leads') class="active" @endif><a href="{{action('\Modules\Crm\Http\Controllers\LeadController@index'). '?lead_view=list_view'}}">@lang('crm::lang.leads')</a></li>

                    @if($__is_admin)
                        <li @if(request()->segment(2) == 'crm-campaigns') class="active" @endif><a href="{{action('\Modules\Crm\Http\Controllers\CampaignController@index')}}">@lang('crm::lang.campaigns')</a></li>

                        <li @if(request()->segment(2) == 'all-contacts-login') class="active" @endif><a href="{{action('\Modules\Crm\Http\Controllers\ContactLoginController@allContactsLoginList')}}">@lang('crm::lang.contacts_login')</a></li>

                        <li @if(request()->get('type') == 'source') class="active" @endif><a href="{{action('TaxonomyController@index') . '?type=source'}}">@lang('crm::lang.sources')</a></li>

                        <li @if(request()->get('type') == 'life_stage') class="active" @endif><a href="{{action('TaxonomyController@index') . '?type=life_stage'}}">@lang('crm::lang.life_stage')</a></li>
                    @endif
                </ul>

            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>