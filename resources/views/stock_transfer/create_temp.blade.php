@extends('layouts.app')
@section('title', __('lang_v1.add_stock_transfer'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('Add Stock Transfer')</h1>
</section>

<!-- Main content -->
<section class="content no-print">
    {!! Form::open(['url' => action('StockTransferController@store'), 'method' => 'post', 'id' => 'stock_transfer_form'
    ]) !!}
    <div class="box box-solid">
        <div class="box-body">
            <div class="row">
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('transaction_date', __('messages.date') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            {!! Form::text('transaction_date', @format_datetime('now'), ['class' => 'form-control',
                            'readonly', 'required']); !!}
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('ref_no', __('purchase.ref_no').':') !!}
                        {!! Form::text('ref_no', null, ['class' => 'form-control']); !!}
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label for="sel1">Location From:</label>
                        <select class="form-control" id="location_id">
                        </select>
                    </div>

                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label for="sel1">Location To: *</label>
                        <select class="form-control" id="transfer_location_id">
                        </select>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!--box end-->
    <div class="box box-solid">
        <div class="col-sm-6 box-header">
        </div>
        <br>
        <div style="position: absolute; left: 25px;">
            <a class="btn btn-danger btn-md" href="<?php echo url("/TransferFix"); ?>" target="_blank">
                <i class="fa fa-history"></i> Refresh DB</a>
            
            <!-- new button, nicolae -->
            <a class="btn  btn-danger btn-md"  onclick="auto_product();" target="_blank" style="margin-left: 3rem;">
                <i class="fa fa-add"  onclick="auto_product();"></i> Auto</a>
        </div>
        <div class="col-sm-6 align-right">
            <a class="btn btn-primary" onclick="add_product();"> Add Products</a>
        </div>
        <div class="box-body">
        </div>
        <div class="row">
            <div class="col-sm-12 ">
                <input type="hidden" id="product_row_index" value="0">
                <input type="hidden" id="total_amount" name="final_total" value="0">
                <div class="table-responsive">
                    <table class="data-table table table-bordered table-striped table-condensed table-hovered add-stock-transfer"
                        id="stock_adjustment_product_table">
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
                                    From Location Stock Qty
                                </th> 
                                <th class="col-sm-2 text-center">
                                    To Location Stock Qty
                                </th>
                                <th class="col-sm-1 text-center">
                                    Transferred Qty
                                </th>
                                <th class="col-sm-1 text-center">
                                   
                                </th>
                                <th style="font-size: 20px;" class="col-sm-2 text-center">
                                    <input disabled type="checkbox" name="all_ctock_check" id="all_ctock_check">
                                    &nbsp
                                    <i style="color: #0000FF" class="fa fa-edit" aria-hidden="true"></i>
                                    &nbsp
                                    <i style="color: #FF0000" class="fa fa-trash-alt" aria-hidden="true"></i>
                                </th>
                            </tr>
                            
                        </thead>
                        <tbody id="stock_tbody" style="text-align: center;">

                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="col-sm-2 text-center" style="width:10%">
                                    Product
                                </th>
                                <th class="col-sm-1 text-center" style="width:6%">
                                    Sku
                                </th>
                                <th class="col-sm-2 text-center" style="width:6%">
                                    Category
                                </th>
                                <th class="col-sm-2 text-center" style="width:12%">
                                    Sub-Category
                                </th>
                                <th class="col-sm-2 text-center"style="width:7%">
                                    Brand
                                </th>
                                <th class="col-sm-2 text-center"style="">
                                    From Location Stock Qty
                                </th>
                                

                                <th class="col-sm-2 text-center" style="width:12%">
                                    To Location Stock Qty
                                </th>

                                <th class="col-sm-1 text-center" style="width:12%">
                                    Transferred Qty
                                </th>
                                <th class="col-sm-1 text-center" style="width:12%">
                                    
                                </th>

                                <th style="font-size: 20px; width:18%" class="col-sm-2 text-center">
                                    <input disabled type="checkbox" name="all_ctock_check" id="all_ctock_check">
                                    &nbsp
                                    <i style="color: #0000FF" class="fa fa-edit" aria-hidden="true"></i>
                                    &nbsp
                                    <i style="color: #FF0000" class="fa fa-trash-alt" aria-hidden="true" class="delete_product"></i>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="row m-5">
                    <div class="col-sm-11">
                        <a onclick="product_save();" id="product_save_btn" class="btn btn-primary pull-right">Save</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <!--box end-->
    {!! Form::close() !!}
</section>

<!-- Notes Modal -->
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
<div class="modal fade" id="auto_products" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h3 class="modal-title">Auto add products to transfe</h3>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="sel1">Product Category</label>
                            <select onchange="auto_change_page_num();" class="form-control" id="auto_category_list"></select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="sel1">Product Sub-Category</label>
                            <select onchange="auto_change_page_num();" class="form-control" id="auto_sub_category_list"></select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="sel1">Product Brand</label>
                            <select onchange="auto_change_page_num();" class="form-control" id="auto_brand_list"></select>
                        </div>
                    </div>
                </div>                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="sel1">Cover period Interval</label>
                            <select class="form-control" id="sel_month">
                            <?php
                                for($i = 1; $i < 13; $i++){
                                    echo "<option value= ".$i." > ".$i." Months</option>";
                                }
                            ?>                                
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="sel1">Stock to cover</label>
                            <select class="form-control" id="sel_day">
                            <?php
                                for($i = 1; $i < 32; $i++){
                                    echo "<option value= ".$i." > ".$i." Days</option>";
                                }
                            ?>                                
                            </select>
                        </div>
                    </div>
                </div>
                <div class="float-right" style="margin: 0;"></div>

                <div class="col-sm-12 align-right"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button id="auto_products_tbl" type="button" class="btn btn-primary hossam">Start calcultaion</button>
            </div>
        </div>
    </div>
</div>
<!-- Select Products Modal -->
<div class="modal fade" id="select_products" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h3 class="modal-title">Select Products</h3>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="sel1">Product Category</label>
                            <select onchange="change_page_num();" class="form-control" id="category_list"></select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="sel1">Product Sub-Category</label>
                            <select onchange="change_page_num();" class="form-control" id="sub_category_list"></select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="sel1">Product Brand</label>
                            <select onchange="change_page_num();" class="form-control" id="brand_list"></select>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="col-sm-12 row">
                                <div class="col-sm-3">
                                    Number per Page :
                                </div>

                                <div class="col-sm-2">
                                    <select onchange="change_page_num();" class="form-control" id="page_count">
                                        <option value="5">5</option>
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="100">100</option>
                                        <option value="1000">1000</option>
                                        <option value="all">all</option>
                                    </select>
                                </div>

                                <div class="col-sm-4 align-right">Keyword : </div>
                                <div class="col-sm-3">
                                    <input type="text" onchange="change()" name="page_search" value="" id="page_search">
                                </div>
                            </div>
                            <br><br>
                            <div class="row col-sm-12">
                                <div class="row col-sm-3">
                                    <div class="col-sm-6 align-right">total_products: </div>
                                    <div id="total_products" class="col-sm-6">0</div>
                                </div>
                                <div class="col-sm-3 align-right">
                                    Current Page: <span id="page_num"></span>
                                    <span>/</span><span id="page_max"></span>
                                </div>
                                <div class="col-sm-6 align-right ">
                                    <ul class="pagination" style="margin: 0 !important;">
                                        <li class="page-item"><a class="page-link" onclick="previous();">Previous</a>
                                        </li>
                                        <li class="page-item"><a class="page-link" onclick="next();">Next</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <table id="product_table" class="table table-bordered table-hover ">
                                <thead>
                                    <tr>
                                        <th><input onclick="all_check(this);" type="checkbox" name="all_product_check"
                                                id="all_product_check"></th>
                                        <th>Product</th>
                                        <th>Sku(s)</th>
                                        <th>Category</th>
                                        <th>Sub-Category</th>
                                        <th>Brand </th>
                                        <th>From Location Stock Qty</th>
                                    </tr>
                                </thead>
                                <tbody id="product_list"></tbody>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>

                <div class="float-right" style="margin: 0;"></div>

                <div class="col-sm-12 align-right"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button id="add_products_tbl" type="button" class="btn btn-primary hossam">Add Products</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirm Modal -->
<div class="modal fade" id="delete_confirm" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Delete Confirm:</h4>
            </div>
            <div class="modal-body">
                <h3>Do you want to delete this order list?<br>You can't undo this action</h3>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" onclick="delete_success();" class="btn btn-danger">Delete</button>
            </div>
        </div>
    </div>
</div>
@stop


@section('javascript')
<script src="{{ asset('js/stock_transfer.js?v=' . $asset_v) }}"></script>
<script type="text/javascript">

    var product_data;

    $(document).ready(function () {
        
        // define datatable
        
        function init_table(){
            // $('.add-stock-transfer thead tr').clone(true).appendTo( '.add-stock-transfer thead' );
            // $('.add-stock-transfer thead tr:eq(1) th').each( function () {
            //     var title = $(this).text().trim();
            //     if(title) {
            //         // $(this).html( '<input class="form-control" style="width:100%; text-align: center; padding:3px;" type="text" placeholder="'+title+'" />' );    
            //     }else {
            //         $(this).html('');
            //     }
            // } );
        
            // DataTable
            var table = $('.add-stock-transfer').DataTable({
                
                "columns": [
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    { "width": "15%"},
                    { "width": "15%"}
                ],
                initComplete: function () {
                    
                    // Apply the search
                    this.api().columns().every( function () {
                        var that = this; 
        
                        $( 'input', this.header() ).on( 'keyup change clear', function () {
                            if ( that.search() !== this.value ) {
                                that.search( this.value ).draw();
                            }
                        });
                    } );
                }
                
            });
        }
        
        init_table();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: "POST",
            url: '/from_location',
            dataType: "JSON",
            success: function (data) {
                var list = "<option value='' selected='selected'>" + "Please Select" + "</option>";
                for (i in data) {
                    list += "<option value=" + data[i].id + ">" + data[i].name + "</option>";
                }
                $("#location_id").html(list);
            }
        });
        $.ajax({
            type: "POST",
            url: '/to_location',
            dataType: "JSON",
            success: function (data) {
                var list = "<option value='' selected='selected'>" + "Please Select" + "</option>";
                for (i in data) {
                    list += "<option value=" + data[i].id + ">" + data[i].name + "</option>";
                }
                $("#transfer_location_id").html(list);
            }
        });
        
        $(document).on('click', '#add_products_tbl', function() {

            
            $('#select_products').modal('hide');
            let variation_ids = [];
            $('input[name="product_check"]:checked').each(function () {
                variation_ids.push($(this).val());
                console.log(location_id, transfer_location_id);
                
            });
          
            $.ajax({
                type: "POST",
                url: '/add_products',
                data: {
                    variation_ids,
                    location_id,
                    transfer_location_id
                },
                success: function (data) {
                    $(".add-stock-transfer").dataTable().fnDestroy();
                    $("#stock_tbody").html(data);
                    // $('.add-stock-transfer')
                    $('.add-stock-transfer').DataTable({});
                }
            });
            
        });
        
        // nicolae, 20/11
        $(document).on('click', '#auto_products_tbl', function() {

            $('#auto_products').modal('hide');
                                   
           
            let variation_ids = [];
            
            for(i = 0; i < product_data.length; i++){            
                
                var row1 = new Object();

                row1.product_id = product_data[i]["product_id"]+"@"+product_data[i]["f_qty"]+"@"+product_data[i]["t_qty"]+"@"+product_data[i]["variation_id"];
                variation_ids.push(row1);                
                
            }         

            sel_month = $("#sel_month").val();
            sel_day = $("#sel_day").val();

            $.ajax({
                type: "POST",
                url: '/auto_products',
                data: {
                    variation_ids,
                   
                    location_id,
                    transfer_location_id,
                    sel_month,
                    sel_day
                },
                success: function (data) {
                                  
                    
                    row = JSON.parse(data);
                    $(".add-stock-transfer").dataTable().fnDestroy();

                    var trTxts;
                    for(i = 0; i < row.length; i++){
                        //var row = res[i];
                        //alert(row[0].product_id); 


                       
    
                        var trTxt = '<tr id="tr_'+row[i].product_id+'" data-id="'+row[i].product_id+'" style="background-color: #EEEEEE">';
                        trTxt += '<input type="hidden" id="variation_'+row[i].product_id+'" value="'+row[i].product_id+'"/><input type="hidden" id="product_'+row[i].product_id+'" value="'+row[i].product_id+'"/>';                       
                        
                       
                        for(j = 0; j < product_data.length; j++){
                            
                            if(product_data[j]["product_id"] == row[i].product_id){                                                                                             
                               
                               
                                var seltxt = '<select class="form-control" id="options_'+row[i].product_id+'" onchange="change_option(this)"  aria-invalid="false">';
                                seltxt += '<option value="" data-qty_available="'+row[i].quantity.toFixed(3)+'">Lot &amp; Expiry</option>';

                                var lots = row[i].lot_info.split("@");
                                for(var k = 0; k < lots.length - 1; k++){
                                    var lot = lots[k].split("&");
                                    seltxt += '<option value="'+lot[2]+'" data-qty_available="'+lot[1]+'" data-msg-max="Only '+lot[1]+'  available in the selected Lot">'+lot[0]+'   -   </option>';
                                }
                                seltxt += '</select>';

                                trTxt += '<td><span id="product_name_'+row[i].product_id+'">'+product_data[j]["name"]+'</span>'+seltxt+'</td>';

                                trTxt += '<td id="sku_'+row[i].product_id+'">'+product_data[j]["sub_sku"]+'</td>';
                                trTxt += '<td><span id="category_'+row[i].product_id+'" data-id="1">'+product_data[j]["category_name"]+'</span> </td>';
                                
                                var sub_category_name = "-"
                                if(product_data[j]["sub_category_name"] != null) sub_category_name = product_data[j]["sub_category_name"];

                                var brand_name = "-"
                                if(product_data[j]["brand_name"] != null) brand_name = product_data[j]["brand_name"];

                                trTxt += '<td> <span id="sub_category_'+row[i].product_id+'" data-id="2">'+sub_category_name+'</span> </td>';
                                trTxt += '<td> <span id="brand_'+row[i].product_id+'" data-id="1">'+brand_name+'</span></td>';

                                
                            }
                        }

                        trTxt += '<td id="f_qty_'+row[i].product_id+'">'+row[i].quantity.toFixed(3)+'</td>';
                        trTxt += '<td id="t_qty_'+row[i].product_id+'">'+row[i].quantity_returned.toFixed(3)+'</td>';
                        trTxt += '<td id="qty1_'+row[i].product_id+'">'+Math.ceil(row[i].qty)+'</td>';

                        trTxt += '<td><input onchange="qty_change('+row[i].product_id+')" id="qty_'+row[i].product_id+'" onchange="compare(this);" style="width: 100%;" type="number" value="'+ Math.ceil(row[i].qty) +'"/></td>';
                        

                        trTxt += '<td class="col-sm-2 text-center">';
                            trTxt += '<input onclick="check_stock(this);" type="checkbox" name="stock_check" id="stock_check_'+row[i].product_id+'" value="'+row[i].product_id+'" variation_id="'+row[i].product_id+'"/>';
                            trTxt += '&nbsp;&nbsp;&nbsp;&nbsp;<i id="edit_'+row[i].product_id+'" onclick="add_notes(this);" style="color: #0000FF" class="fa fa-edit" aria-hidden="true"></i>';
                            trTxt += '&nbsp;&nbsp;&nbsp;&nbsp;<i style="color: #FF0000" class="fa fa-trash-alt delete_product" id="delete_'+row[i].product_id+'" aria-hidden="true"></i>';
                        trTxt += '</td>';
                        trTxt += '<input type="hidden" id="additional_notes_'+row[i].product_id+'" value=""/>';
                        trTxt += '<input type="hidden" id="location_id_'+row[i].product_id+'" value="'+row[i].location_id+'"/>';
                        trTxt += '<input type="hidden" id="transfer_location_id_'+row[i].product_id+'" value="'+row[i].transfer_location_id+'"/>';
                        trTxt += '</tr>';
                        
                        
                        trTxts += trTxt;
                        
                    }

                    $("#stock_tbody").html(trTxts);
                    $('.add-stock-transfer').DataTable({});
                }
            });

        });
        
        $(document).on( 'click', '.delete_product', function () {
            var table = $('.add-stock-transfer').DataTable();
            table
                .row( $(this).parents('tr') )
                .remove()
                .draw();
        } );
        
        
        // nicolae, 20/11
        
        
        
    });// end ready

    var product_row_index = 0;
    var category_id = "";
    var sub_category_id = "";
    var brand_id = "";
    var total_products = 0;
    var transfer_array = [];
    var page_max = 1;
    var page_count = 5;
    var page_search = "";
    var page_num = 1;
    var location_id = "";
    var transfer_location_id = "";
    var total_stock = 0;
    var select_product_id = "";

    function auto_product(){

       
        transfer_location_id = $("#transfer_location_id").val();
        location_id = $("#location_id").val();
        if (location_id == "") {
            toastr.error('Please select Location!');
            return;
        }
        if (transfer_location_id == "") {
            toastr.error('Please select Transfer Location!');
            return;
        }
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: '/show_category',
            dataType: "JSON",
            success: function (data) {
                var category_list = "<option value='' selected='selected'>" + "Please Select" + "</option>";
                for (i in data) {
                    category_list += "<option value=" + data[i].id + ">" + data[i].name + "</option>";
                }
               
                $("#auto_category_list").html(category_list);
            }
        });
        $.ajax({
            url: '/show_sub_category',
            dataType: "JSON",
            success: function (data) {
                console.log(data);
                var sub_category_list = "<option value='' selected='selected'>" + "Please Select" + "</option>";
                for (i in data) {
                    sub_category_list += "<option value=" + data[i].id + ">" + data[i].name + "</option>";
                }
                
                $("#auto_sub_category_list").html(sub_category_list);
            }
        });
        $.ajax({
            url: '/show_brand',
            dataType: "JSON",
            success: function (data) {
                var brand_list = "<option value='' selected='selected'>" + "Please Select" + "</option>";
                for (i in data) {
                    brand_list += "<option value=" + data[i].id + ">" + data[i].name + "</option>";
                }
                
                $("#auto_brand_list").html(brand_list);
            }
        });        
        auto_change_page_num();
        $("#auto_products").modal();
    }

    function add_product() {
        $("#product_list").html(product_list);
        transfer_location_id = $("#transfer_location_id").val();
        location_id = $("#location_id").val();
        if (location_id == "") {
            toastr.error('Please select Location!');
            return;
        }
        if (transfer_location_id == "") {
            toastr.error('Please select Transfer Location!');
            return;
        }
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: '/show_category',
            dataType: "JSON",
            success: function (data) {
                var category_list = "<option value='' selected='selected'>" + "Please Select" + "</option>";
                for (i in data) {
                    category_list += "<option value=" + data[i].id + ">" + data[i].name + "</option>";
                }
                $("#category_list").html(category_list);
                $("#auto_category_list").html(category_list);
            }
        });
        $.ajax({
            url: '/show_sub_category',
            dataType: "JSON",
            success: function (data) {
                console.log(data);
                var sub_category_list = "<option value='' selected='selected'>" + "Please Select" + "</option>";
                for (i in data) {
                    sub_category_list += "<option value=" + data[i].id + ">" + data[i].name + "</option>";
                }
                $("#sub_category_list").html(sub_category_list);
                $("#auto_sub_category_list").html(sub_category_list);
            }
        });
        $.ajax({
            url: '/show_brand',
            dataType: "JSON",
            success: function (data) {
                var brand_list = "<option value='' selected='selected'>" + "Please Select" + "</option>";
                for (i in data) {
                    brand_list += "<option value=" + data[i].id + ">" + data[i].name + "</option>";
                }
                $("#brand_list").html(brand_list);
                $("auto_#brand_list").html(brand_list);
            }
        });
        change();
        $("#select_products").modal();
    }
    function auto_change_page_num() {

        page_count = "all"; // 10, 100, 1000, all
        page_search = "";

        category_id = $("#auto_category_list").val();
        sub_category_id = $("#auto_sub_category_list").val();
        brand_id = $("#auto_brand_list").val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: "POST",
            url: '/show_products',
            data: {
                page_count: page_count,
                page_search: page_search,
                page_num: page_num,
                category_id: category_id,
                sub_category_id: sub_category_id,
                brand_id: brand_id,
                location_id: location_id,
                transfer_location_id: transfer_location_id
            },
            success: function (res) {
                product_data = JSON.parse(res);
                var data = JSON.parse(res);

                data_count = data.length;
                console.log(data);
            }
        });
    }
    function change_page_num() {
        page_num = 1;
        change();
    }
    function change() {
        page_count = $("#page_count").val(); // 10, 100, 1000, all
        page_search = $("#page_search").val();
        category_id = $("#category_list").val();
        sub_category_id = $("#sub_category_list").val();
        brand_id = $("#brand_list").val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: "POST",
            url: '/show_products',
            data: {
                page_count: page_count,
                page_search: page_search,
                page_num: page_num,
                category_id: category_id,
                sub_category_id: sub_category_id,
                brand_id: brand_id,
                location_id: location_id,
                transfer_location_id: transfer_location_id
            },
            success: function (res) {
                var data = JSON.parse(res);
                data_count = data.length;
                if(page_count =='all') {
                    page_count = data_count;
                }
                page_max = data_count / page_count;
                fix_max = page_max.toFixed();
                if (fix_max < page_max) { fix_max++; }
                page_max = fix_max;
                product_start = (page_num - 1) * page_count;
                product_end = page_num * page_count;
                if (product_end > data_count) { product_end = data_count; }
                

                var product_list = "";
                for (var i = product_start; i < product_end; i++) {
                    var qty_num = eval(data[i].f_qty);
                    product_list += "<tr onclick='row_click(this);' id=" + data[i].variation_id + "><td>" +
                        '<input type="checkbox"  id="check_' + data[i].variation_id + '" value="' + data[i].variation_id + '" name="product_check">' +
                        "</td><td>" + data[i].name +
                        "</td><td>" + data[i].sub_sku +
                        "</td><td>" + ((data[i].category_name && data[i].category_name != null) ? data[i].category_name : '-') + 
                        " </td><td> " + ((data[i].sub_category_name && data[i].sub_category_name != null) ? data[i].sub_category_name : '-') + 
                        " </td><td> " + ((data[i].brand_name && data[i].brand_name != null) ? data[i].brand_name : '-') +
                        "</td><td>" + qty_num.toFixed(2) +
                        "</td></tr>";
                }

                $("#product_list").html(product_list);
                $("#total_products").text(data_count);
                $("#page_num").text(page_num);
                $("#page_max").text(page_max);

                console.log(data);
            }
        });
    }
    function qty_change(sel){
        //alert(sel);
        $(".add-stock-transfer").dataTable().fnDestroy();
        $("#qty1_"+sel).html($("#qty_"+sel).val());
        $('.add-stock-transfer').DataTable({});
    }
    function next() {
        if (page_num < page_max) {
            page_num++;
            $("#page_num").text(page_num);
            change();
        }
    }
    function previous() {
        if (page_num > 1) {
            page_num--;
            $("#page_num").text(page_num);
            change();
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
        var stat = $("#check_" + obj.id).prop('checked');
        $("#check_" + obj.id).prop('checked', !stat);
    }

    function add_notes(obj) {
        select_product_id = $("#" + obj.id).parent().parent().children().first().val();
        content = $("#additional_notes_" + select_product_id).val();
        $("#notes_content").val(content);
        $("#add_notes").modal()
    }
    function notes_update() {
        content = $("#notes_content").val();
        $("#additional_notes_" + select_product_id).val(content);
        if (content != "") {
            $("#edit_" + select_product_id).css('color', '#00FF00');
        } else {
            $("#edit_" + select_product_id).css('color', '#0000FF');
        }
        $("#notes_content").val('');
        $("#add_notes").modal('hide');
    }
   
    
    function delete_success() {
        $("#delete_" + select_product_id).parent().parent().remove();
        $("#delete_confirm").modal('hide');
    }
    function check_stock(obj) {
        select_product_id = $("#" + obj.id).parent().parent().children().first().val();
        if (obj.checked) {
            $("#tr_" + select_product_id).css('background-color', '#FFD700');
        } else {
            var f_qty_obj = $("#f_qty_" + select_product_id);
            if (eval(f_qty_obj.text()) != eval(f_qty_obj.prev().children().first().val())) {
                $("#tr_" + select_product_id).css('background-color', '#EEEEEE');
            } else {
                $("#tr_" + select_product_id).css('background-color', '#FF8080');
            }
        }
    }

    function product_save() {
        var create_transfer_count = $('input[name="stock_check"]').length;
        var handled_create_transfer_count = 0;
        $('#product_save_btn').attr('disabled', true);
        var send_transfer = false;

     //   alert( $('input[name="stock_check"]').length);

        var product_id_ary = [];
        var variation_id_ary = [];
        var quantity_ary = [];
        var location_id_ary = [];
        var lot_no_line_id_ary = [];
        var transfer_location_id_ary = [];
        var additional_notes_ary = [];
        var transaction_date_ary = [];
        var ref_no_ary = [];
        var checked_ary = [];
        var send_data = [];
        var index = 0;
        $('input[name="stock_check"]').each(function () {
            
            //alert(this.value);

            var cur_product_id = this.value;
            var cur_product_variation_id = this.getAttribute('variation_id');
            var cur_product_checked = this.checked ? 'YES' : 'NO';
            var cur_product_qty = $("#qty_" + cur_product_id).val();
            if (eval(cur_product_qty) > 0) {
            // Old code
            //     send_transfer = true;
            //     $.ajaxSetup({
            //         headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            //     });
            //     $.ajax({
            //         type: "POST", url: '/add_stock_prepare',
            //         data: {
            //             product_id: cur_product_id, variation_id: $("#variation_" + cur_product_variation_id).val(), quantity: cur_product_qty,
            //             location_id: $("#location_id_" + cur_product_id).val(), lot_no_line_id: $("#options_" + cur_product_id).val(),
            //             transfer_location_id: $("#transfer_location_id_" + cur_product_id).val(), additional_notes: $("#additional_notes_" + cur_product_variation_id).val(),
            //             transaction_date: $("#transaction_date").val(), ref_no: $("#ref_no").val(), checked: cur_product_checked
            //         },
            //         success: function (data) {
            //             if (data == "success") {
            //                 handled_create_transfer_count = handled_create_transfer_count + 1;
            //                 if (handled_create_transfer_count == create_transfer_count) {
            //                 }
            //             }
            //             location.assign("/Prepare_transfer");
            //         }
            //     });
            //  New Code [MoonRider 21/01/08]
                product_id_ary[index] = cur_product_id;
                variation_id_ary[index] = $("#variation_" + cur_product_variation_id).val();
                quantity_ary[index] = cur_product_qty;
                location_id_ary[index] = $("#location_id_" + cur_product_id).val();
                lot_no_line_id_ary[index] = $("#options_" + cur_product_id).val();
                transfer_location_id_ary[index] = $("#transfer_location_id_" + cur_product_id).val();
                additional_notes_ary[index] = $("#additional_notes_" + cur_product_variation_id).val();
                transaction_date_ary[index] = $("#transaction_date").val();
                ref_no_ary[index] = $("#ref_no").val();
                checked_ary[index] = cur_product_checked;           

                send_data[index] = product_id_ary[index]+"@"+variation_id_ary[index]+"@"+quantity_ary[index]+"@"+location_id_ary[index]+"@";
                send_data[index] += lot_no_line_id_ary[index]+"@"+transfer_location_id_ary[index]+"@"+additional_notes_ary[index]+"@"+transaction_date_ary[index]+"@";
                send_data[index] += ref_no_ary[index]+"@"+checked_ary[index];
                index++;
                
            }
            
        });
        
        console.log("send_data", send_data);
        
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });
        $.ajax({
            type: "POST", url: '/add_stock_prepare',
            data: {
                product_id_ary: send_data,
                kind: 1,
                index : index
            },
            success: function (data) {
               
               
                location.assign("/Prepare_transfer");
                send_transfer = true;
            }
        });

        if (!send_transfer) {
            $('#product_save_btn').attr('disabled', false);
        }
    }
    function change_option(obj) {
       
        var selected_val = $(obj).val();
        var selected_variant_id = $("#" + obj.id).parent().parent().children().first().val();
        var product_options = $(obj).find('option');    
        
        for (var poi = 0; poi < product_options.length; poi++) {
            if ($(product_options[poi]).val() == selected_val) {
                $('#f_qty_' + selected_variant_id).text(parseFloat($(product_options[poi]).data('qty_available')).toFixed(2));
            }
        }
    }
</script>
@endsection