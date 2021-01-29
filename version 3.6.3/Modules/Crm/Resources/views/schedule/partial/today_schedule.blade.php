@forelse($schedules as $schedule)
<div class="external-event 
	@if($schedule->status == 'scheduled')
		bg-yellow
	@elseif($schedule->status == 'open')
		bg-blue
	@elseif($schedule->status == 'canceled')
		bg-red
	@elseif($schedule->status == 'completed')
		bg-green
	@else
		bg-yellow
	@endif
	cursor-pointer today_schedule" data-href="{{action('\Modules\Crm\Http\Controllers\ScheduleController@show', [ $schedule->id ])}}">
	{{$schedule->title}}
</div>
@empty
	@lang('crm::lang.no_schedule_for_today')
@endforelse