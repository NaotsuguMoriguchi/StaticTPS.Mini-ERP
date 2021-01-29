

@foreach ($data as $row)
    @php 
        $product = $row['product'];
        $variation_id = $row['variation_id']
    @endphp
    <!--  -->
    <tr id="tr_{{ $variation_id }}" data-id="{{ $variation_id }}"
        @if ( $product->f_qty && $product->t_qty)
            style="background-color: #EEEEEE"
        @else
            style="background-color: #FF8080"
        @endif
    >
        <input type="hidden" id="variation_{{ $variation_id }}" value="{{ $variation_id }}">
        <input type="hidden" id="product_{{ $variation_id }}" value="{{ $product->product_id }}">
        <td>
            <span id="product_name_{{ $variation_id }}">{{ $product->product_name }}</span>
            @if( session()->get('business.enable_lot_number') == 1 || session()->get('business.enable_product_expiry') == 1)
                @php
                    $lot_enabled = session()->get('business.enable_lot_number');
                    $exp_enabled = session()->get('business.enable_product_expiry');
                    $lot_no_line_id = '';
                    if(!empty($product->lot_no_line_id)){
                        $lot_no_line_id = $product->lot_no_line_id;
                    }
                @endphp
                @if(!empty($product->lot_numbers))
                <select class="form-control" id="options_{{ $variation_id }}" onchange="change_option(this)">
                    <option value="" data-qty_available="{{ number_format( $product->f_qty, 2 ) }}">@lang('lang_v1.lot_n_expiry')</option>
                    @foreach($product->lot_numbers as $lot_number)
                        @php
                            $selected = "";
                            if($lot_number->purchase_line_id == $lot_no_line_id){
                                $selected = "selected";

                                $max_qty_rule = $lot_number->qty_available;
                                $max_qty_msg = __('lang_v1.quantity_error_msg_in_lot', ['qty'=> $lot_number->qty_formated, 'unit' => $product->unit  ]);
                            }

                            $expiry_text = '';
                            if($exp_enabled == 1 && !empty($lot_number->exp_date)){
                                if( \Carbon::now()->gt(\Carbon::createFromFormat('Y-m-d', $lot_number->exp_date)) ){
                                    $expiry_text = '(' . __('report.expired') . ')';
                                }
                            }
                        @endphp
                        <option value="{{$lot_number->purchase_line_id}}" data-qty_available="{{$lot_number->qty_available}}" data-msg-max="@lang('lang_v1.quantity_error_msg_in_lot', ['qty'=> $lot_number->qty_formated, 'unit' => $product->unit  ])" {{$selected}}>@if(!empty($lot_number->lot_number) && $lot_enabled == 1){{$lot_number->lot_number}} @endif @if($lot_enabled == 1 && $exp_enabled == 1) - @endif @if($exp_enabled == 1 && !empty($lot_number->exp_date)) @lang('product.exp_date'): {{@format_date($lot_number->exp_date)}} @endif {{$expiry_text}}</option>
                    @endforeach
                </select>
                @endif
            @endif
        </td>
        <td id="sku_{{ $variation_id }}">{{ $product->sub_sku }}</td>
        <td><span id="category_{{ $variation_id }}" data-id="{{ $product->category_id }}">{{ $product->category_name ? $product->category_name : '-' }}</span> </td>
        <td> <span id="sub_category_{{ $variation_id }}" data-id="{{ $product->sub_category_id }}">{{ $product->sub_category_name ? $product->sub_category_name : '-' }}</span> </td>
        <td> <span id="brand_{{ $variation_id }}" data-id="{{ $product->brand_id }}">{{ $product->brand_name ? $product->brand_name : '-' }}</span></td>
        <td id="f_qty_{{ $variation_id }}">{{ number_format( $product->f_qty, 2 ) }}</td>
        
        <td id="t_qty_{{ $variation_id }}">{{ number_format( $product->t_qty, 2 ) }}</td>

        <td id="qty1_{{ $variation_id }}">0</td>

        <td><input id="qty_{{ $variation_id }}" onchange="compare(this);" style="width: 100%;" type="number" value="0" onchange="qty_change({{$variation_id}})"></td>
        <td class="col-sm-2 text-center">
            <input onclick="check_stock(this);" type="checkbox" name="stock_check" id="stock_check_{{ $variation_id }}" value="{{ $product->product_id }}" variation_id="{{$variation_id}}">
            &nbsp;&nbsp;&nbsp;&nbsp;<i id="edit_{{ $variation_id }}" onclick="add_notes(this);" style="color: #0000FF" class="fa fa-edit" aria-hidden="true"></i>
            &nbsp;&nbsp;&nbsp;&nbsp;<i style="color: #FF0000" class="fa fa-trash-alt delete_product" id="delete_{{ $variation_id }}" aria-hidden="true"></i>
        </td>
        <input type="hidden" id="additional_notes_{{ $variation_id }}" value="">
        <input type="hidden" id="location_id_{{ $variation_id }}" value="{{ $location_id }}">
        <input type="hidden" id="transfer_location_id_{{ $variation_id }}" value="{{ $transfer_location_id }}">
    </tr>

@endforeach