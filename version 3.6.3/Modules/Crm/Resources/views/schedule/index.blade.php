@extends('layouts.app')

@section('title', __('crm::lang.schedules'))

@section('content')
@include('crm::layouts.nav')
<!-- Content Header (Page header) -->
<section class="content-header no-print">
   <h1>@lang('crm::lang.schedules')</h1>
</section>

<section class="content no-print">
	<div class="row">
		<div class="col-sm-3">
			<div class="row">
				<div class="col-sm-12">
				    <div class="box box-solid">
				        <div class="box-body">
				            <div class="external-event bg-yellow text-center">
				                <small>@lang('crm::lang.scheduled')</small>
				            </div>
				            <div class="external-event bg-blue text-center">
				                <small>@lang('crm::lang.open')</small>
				            </div>
				            <div class="external-event bg-red text-center">
				                <small>@lang('crm::lang.canceled')</small>
				            </div>
				            <div class="external-event bg-green text-center">
				                <small>@lang('crm::lang.completed')</small>
				            </div>
				            <small>
				            	@lang('crm::lang.double_click_on_any_day_to_add_new_schedule')
				            </small>
				        </div>
				        <!-- /.box-body -->
				    </div>
				</div>
				<div class="col-sm-12">
					<div class="box box-solid">
						<div class="box-header with-border">
							<h4 class="box-title">
								@lang('crm::lang.todays_schedule')
							</h4>
						</div>
						<div class="box-body todays_schedule_table">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-9">
			@component('components.widget', ['class' => 'box-primary', 'title' => __('crm::lang.all_schedules')])
		        @slot('tool')
		            <div class="box-tools">
		                <button type="button" class="btn btn-block btn-primary btn-add-schedule">
		                <i class="fa fa-plus"></i> @lang('messages.add')</button>
		            </div>
		            <input type="hidden" name="schedule_create_url" id="schedule_create_url" value="{{action('\Modules\Crm\Http\Controllers\ScheduleController@create')}}">
		        @endslot
		        <div class="col-sm-12">
		            <div id="calendar"></div>
		        </div>
		    @endcomponent
		</div>
	</div>
    <div class="modal fade schedule" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
</section>
@endsection
@section('javascript')
	<script src="{{ asset('modules/crm/js/crm.js?v=' . $asset_v) }}"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			get_todays_schedule();
		});
	</script>
@endsection