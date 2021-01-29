@extends('layouts.app')
@section('title', __('lang_v1.add_stock_transfer'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('Prepare Transfer')</h1>
</section>

<!-- Main content -->
<section class="content no-print">
    {!! Form::open(['url' => action('StockTransferController@store'), 'method' => 'post', 'id' => 'stock_transfer_form'
    ]) !!}

    <div class="box box-solid">
        <div class="col-sm-6 box-header">
        </div>
        <br>
        <div class="row">
            <div class="col-sm-12 ">
                <div class="table-responsive">
                    <table id="dtBasicExample" class="table table-striped table-bordered table-sm" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="col-sm-2 text-center">
                                    Product
                                </th>
                                <th class="col-sm-1 text-center">
                                    Sku
                                </th>
                                <th class="col-sm-2 text-center">
                                    Category
                                </th>
                                <th class="col-sm-2 text-center">
                                    Sub-Category
                                </th>
                                <th class="col-sm-2 text-center">
                                    Brand
                                </th>
                                <th class="col-sm-2 text-center">
                                    From Location
                                </th>
                                <th class="col-sm-2 text-center">
                                    To Location
                                </th>
                                <th style="width:0px; ">Transferred Qty</th>
                                <th class="col-sm-1 text-center">
                                   
                                </th>

                                <th style="font-size: 20px;" class="col-sm-2 text-center">
                                    <input onclick="all_stock(this);" type="checkbox" name="all_ctock_check"
                                        id="all_ctock_check">
                                    
                                    <i style="color: #0000FF" class="fa fa-edit" aria-hidden="true"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="stock_tbody" style="text-align: center;">
                            @foreach ($prepare_data as $data)
                            <tr style="background-color: {{ ($data->checked == 'YES') ? '#FFD700' : '#EEEEEE' }}"
                                id="tr_{{$data->id}}" onclick="row_click(this);">
                                <td>
                                    {{ $data->product->name }}
                                </td>
                                <td>
                                    {{ $data->variation->sub_sku }}
                                </td>
                                <td>
                                    @if ($data->product->category)
                                    {{ $data->product->category->name }}
                                    @else
                                    -
                                    @endif 
                                </td>
                                <td>
                                    @if ($data->product->sub_category)
                                    {{ $data->product->sub_category->name }}
                                    @else
                                    -
                                    @endif 
                                </td>
                                <td>
                                    @if ($data->product->brand)
                                    {{ $data->product->brand->name }}
                                    @else
                                    -
                                    @endif
                                </td>
                                <td>
                                    {{ $data->location->name }}
                                </td>
                                <td>
                                    {{ $data->transfer_location->name }}
                                </td>

                                <td style="width:0px; ">{{ number_format($data->quantity, 2) }}</td>

                                <td>
                                    <input onchange="compare(this);" id="quantity_{{ $data->id }}" style="width: 60px;"
                                        type="text" name="" value="{{ number_format($data->quantity, 2) }}">
                                    <input id="c_quantity_{{ $data->id }}" style="width: 100%;" type="hidden" name=""
                                        value="{{ number_format($data->quantity, 2) }}">
                                </td>
                                <td class="col-sm-2 text-center">
                                    <input type="hidden" name="" value="{{$data->id}}">
                                    <input type="checkbox" name="stock_check" value="{{ $data->id }}"
                                        id="stock_check_{{ $data->id }}">
                                    @if($data->additional_notes=="")
                                    <i id="edit_{{ $data->id }}" onclick="add_notes(this);" style="color: #0000FF"
                                        class="fa fa-edit" aria-hidden="true"></i>
                                    @else
                                    <i id="edit_{{ $data->id }}" onclick="add_notes(this);" style="color: #0000FF"
                                        class="fa fa-edit" aria-hidden="true"></i>
                                    @endif
                                     <i style="color: #FF0000" class="fa fa-trash-alt delete_product" aria-hidden="true"></i>
                                    <input id="additional_notes_{{ $data->id }}" type="hidden" name=""
                                        value="{{$data->additional_notes }}">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="col-sm-2 text-center">
                                    Product
                                </th>
                                <th class="col-sm-1 text-center">
                                    Sku
                                </th>
                                <th class="col-sm-2 text-center">
                                    Category
                                </th>
                                <th class="col-sm-2 text-center">
                                    Sub-Category
                                </th>
                                <th class="col-sm-2 text-center">
                                    Brand
                                </th>
                                <th class="col-sm-2 text-center">
                                    From Location
                                </th>
                                <th class="col-sm-2 text-center">
                                    To Location
                                </th>
                                 <th style="width:0px;">Transferred Qty</th>
                                <th class="col-sm-1 text-center">
                                   
                                </th>

                                <th style="font-size: 20px;" class="col-sm-2 text-center">
                                    <input onclick="all_stock(this);" type="checkbox" name="all_ctock_check"
                                        id="all_ctock_check">
                                    
                                    <i style="color: #0000FF" class="fa fa-edit" aria-hidden="true"></i>
                                </th>
                            </tr>
                        </tfoot>
                    </table>

                </div>
                <div class="row m-5">
                    <div class="col-sm-1"></div>
                    <div class="col-sm-5">
                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#delete_confirm">
                            Empty List
                        </button>

                    </div>
                    <div class="col-sm-5">
                        <a onclick="product_save();" id="product_save_btn" class="btn btn-primary pull-right">Save</a>
                    </div>
                    <div class="col-sm-1"></div>
                </div>

            </div>
        </div>
    </div>
    </div>
    <!--box end-->


    {!! Form::close() !!}

</section>


<!-- Note Modal -->
<div class="modal fade" id="add_notes" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Notes:</h4>
            </div>
            <div class="modal-body">
                <textarea rows=5 cols=75 id="notes_content" name=""></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" onclick="notes_update();" class="btn btn-info">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="delete_confirm" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Delete Confirm:</h4>
            </div>
            <div class="modal-body text-center">
                <h3>Do you want to delete this order list?<br>You can't undo this action</h3>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" onclick="empty_list();" class="btn btn-danger"
                    data-dismiss="modal">Delete</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('javascript')
<script src="{{ asset('js/stock_transfer.js?v=' . $asset_v) }}"></script>
<script type="text/javascript">
    var select_transfer_id = "";
    function compare(obj) {
        var quantity = eval(obj.value)
        var c_quantity = eval($('#' + obj.id).next().val());

        if (quantity >= c_quantity) {
            obj.value = c_quantity;
        } else {
            obj.value = quantity;
        }
    }
    function all_check(obj) {
        var check = obj.checked;
        $("input[name='product_check']").prop('checked', check);
    }
    function all_stock(obj) {
        var check = obj.checked;
        $("input[name='stock_check']").prop('checked', check);
    }
    function row_click(obj) {
        select_transfer_id = $("#" + obj.id).children().last().children().first().val();
        var stat = $("#stock_check_" + select_transfer_id).prop('checked');
        $("#stock_check_" + select_transfer_id).prop('checked', !stat);
    }
    function add_notes(obj) {
        content = $("#" + obj.id).next().val();
        select_transfer_id = $("#" + obj.id).prev().prev().val();

        $("#notes_content").val(content);
        $("#add_notes").modal()
    }
    function notes_update() {
        content = $("#notes_content").val();
        console.log(content);
        console.log(select_transfer_id);
        $("#additional_notes_" + select_transfer_id).val(content);
        if (content != "") {
            $("#edit_" + select_transfer_id).css('color', '#00FF00');
        } else {
            $("#edit_" + select_transfer_id).css('color', '#0000FF');
        }
        $("#add_notes").modal('hide');
    }

    /* products go to next page, receive page **/
    function product_save() {
        
        let transfer_ids = [];
        let quantities = [];
        let notes = [];
        if ($('input[name="stock_check"]:checked').length) {
            
            // prepare to send data, nicolae, 21/11
            $('#product_save_btn').attr('disabled', true);
                $('input[name="stock_check"]:checked').each(function () {
                const cur_transfer_id = this.value;
                transfer_ids.push(cur_transfer_id);
                quantities.push($("#quantity_" + cur_transfer_id).val());
                notes.push($("#additional_notes_" + cur_transfer_id).val());
            });
            
            // call ajax
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: "POST",
                url: '/add_stock_receive',
                data: {
                    transfer_ids: JSON.stringify(transfer_ids),
                    quantities:JSON.stringify(quantities),
                    notes: JSON.stringify(notes)
                },
                success: function (data) {
                    location.assign("/Receive_transfer");
                },
                error: function(err) {
                    console.log(err);
                }
            });
        }//end if
        
        
    }
    
    
    function empty_list() {
        var transfer_ids = [];
        $('input[name="stock_check"]').each(function () {
            transfer_ids.push(this.value);
        });
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: "POST",
            url: '/delete_prepare_stock',
            data: { ids: transfer_ids.join(',') },
            success: function (data) {
                if (data == "success") {
                    location.reload();
                }
            }
        });
    }

    $(document).ready(function () {
        var prepareTable = $('#dtBasicExample').DataTable({
            "columns": [
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    { "width": "0%"},
                    { "width": "15%"},
                    { "width": "15%"}
                ]
        });
        
        $(document).on( 'click', '.delete_product', function () {
           
            prepareTable
                .row( $(this).parents('tr') )
                .remove()
                .draw();
        } );
        
    });// end ready
</script>


@endsection