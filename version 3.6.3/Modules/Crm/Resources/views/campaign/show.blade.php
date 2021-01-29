<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">{{$campaign->name}}</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-sm-6">
                    <b>@lang('crm::lang.campaign_type'):</b>
                    @if($campaign->campaign_type == "sms")
                        {{__("crm::lang.sms")}}
                    @elseif($campaign->campaign_type == "email")
                        {{__("business.email")}}
                    @endif
                </div>
                @if(!empty($campaign->sent_on))
                    <div class="col-sm-6">
                      <p class="pull-right">
                        <b>@lang('crm::lang.sent_on'):</b> 
                            {{ @format_datetime($campaign->sent_on) }}
                        </p>
                    </div>
                @endif
            </div>
            
            @if($campaign->campaign_type == 'email')
                <div class="row mt-5">
                    <div class="col-sm-12">
                        <b>@lang('crm::lang.subject'):</b> {{$campaign->subject}}
                    </div>
                </div>
                <div class="row mt-5">
                    <div class="col-sm-12">
                        <b>@lang('crm::lang.email_body'):</b>
                        {!! $campaign->email_body !!}
                    </div>
                </div>
            @elseif($campaign->campaign_type == 'sms')
                <div class="row">
                    <div class="col-sm-12">
                        <b>@lang('crm::lang.sms_body'):</b>
                       <p>{!! $campaign->sms_body !!}</p>
                    </div>
                </div>
            @endif

            @if(count($notifiable_users)) 
                @php
                    $leads = [];
                    $customers = [];
                @endphp
                @foreach($notifiable_users as $contact) 
                    @php
                        if($contact->type == 'lead') {
                            $leads[] = $contact->name; 
                        } else if($contact->type == 'customer') {
                            $customers[] = $contact->name; 
                        }
                    @endphp
                @endforeach 
            @endif
            <div class="row mt-5">
                <div class="col-sm-6">
                    <strong>@lang('lang_v1.customers'): </strong>
                    <p>
                        {{implode(', ', $customers)}}
                    </p>
                </div>
                <div class="col-sm-6">
                    <strong>@lang('crm::lang.leads'): </strong>
                    <p>
                        {{implode(', ', $leads)}}
                    </p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <span class="pull-left">
                <i class="fas fa-pencil-alt"></i>
                @lang('crm::lang.created_this_campaign_on', [
                    'name' => $campaign->createdBy->user_full_name
                ])
                {{@format_date($campaign->created_at)}}
            </span>
            <button type="button" class="btn btn-default" data-dismiss="modal">
                @lang('messages.close')
            </button>
        </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->