<?php

namespace App\Http\Controllers;

use App\BusinessLocation;

use App\PurchaseLine;
use App\Transaction;
use App\TransactionSellLinesPurchaseLines;

use App\Product;
use App\TempTransferProduct;

use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use Datatables;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class StockTempController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $productUtil;
    protected $transactionUtil;
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(ProductUtil $productUtil, TransactionUtil $transactionUtil, ModuleUtil $moduleUtil)
    {
        $this->productUtil = $productUtil;
        $this->transactionUtil = $transactionUtil;
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

        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(action('StockTransferController@index'));
        }

        $business_locations = BusinessLocation::forDropdown($business_id);

        return view('stock_transfer/create_temp')
            ->with(compact('business_locations'));
    }

    public function transfer_store(){
        $business_id = request()->session()->get('user.business_id');
        $created_by= request()->session()->get('user.id');
        $location_id=Input::post('location_id');
        $transfer_location_id=Input::post('transfer_location_id');
        $f_type='sell_transfer';
        $t_type='purchase_transfer';
        $f_status='final';
        $t_status='received';
        $payment_status='paid';
        $ref_no=Input::post('ref_no');
        $transaction_date=Input::post('transaction_date');
        $total_before_tax=Input::post('price');
        $shipping_charges=Input::post('shipping_charges');
        $additional_notes=Input::post('additional_notes');
        $final_total=Input::post('price');

        DB::table('transactions')->insert(
            ['business_id' => $business_id, 'created_by' => $created_by, 'location_id'=>$location_id, 'type' => $f_type, 'status' => $f_status,
                'payment_status' => $payment_status, 'ref_no' => $ref_no, 'transaction_date' => $transaction_date,
                'total_before_tax'=>$total_before_tax, 'shipping_charges'=>$shipping_charges,'additional_notes'=>$additional_notes,'final_total'=>$final_total ]
        );
    }

    public function from_location(){
        $business_id = request()->session()->get('user.business_id');
        $list = BusinessLocation::all();
        return $list;
    }

    public function to_location(){
        $user_id = request()->session()->get('user.id');
        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $list = BusinessLocation::whereIn('id', $permitted_locations)->get();
        } else {
            $list = BusinessLocation::all();
        }
        return $list;
    }

    public function prepare_transfer(){
        $user_id = request()->session()->get('user.id');
        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(action('StockTransferController@index'));
        }

        $business_locations = BusinessLocation::forDropdown($business_id);
        $permitted_locations = auth()->user()->permitted_locations();
        $prepare_data = TempTransferProduct::with('product.category', 'product.sub_category', 'product.brand', 'variation', 'location', 'transfer_location');
            //->where('user_id', $user_id);
        if ($permitted_locations != 'all')
        {
            $prepare_data = $prepare_data->whereIn('location_id', $permitted_locations);
        }
        $prepare_data = $prepare_data->where('status', 'PREPARE')->get();
        return view('stock_transfer/prepare_temp')
            ->with(compact('business_locations'))
            ->with(compact('prepare_data'));
    }
    
    // 
    public function receive_transfer(){
        $user_id = request()->session()->get('user.id');
        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(action('StockTransferController@index'));
        }

        $business_locations = BusinessLocation::forDropdown($business_id);
        $permitted_locations = auth()->user()->permitted_locations();
        $receive_data = TempTransferProduct::with('product.category', 'product.sub_category', 'product.brand', 'variation', 'location', 'transfer_location');
            //->where('user_id', $user_id);
        if ($permitted_locations != 'all')
        {
            $receive_data = $receive_data->whereIn('transfer_location_id', $permitted_locations);
        }
        $receive_data = $receive_data->where('status', 'RECEIVED')->get();
        return view('stock_transfer/receive_temp')
            ->with(compact('business_locations'))
            ->with(compact('receive_data'));
    }

    public function show_category()
    {
        $categories = DB::table('categories')->where('parent_id',0)->get();
        return $categories;
    }

    public function show_sub_category()
    {
        $sub_categories = DB::table('categories')->where('parent_id','<>',0)->get();
        return $sub_categories;
    }

    public function show_brand(){
        $brands = DB::table('brands')->get();
        return $brands;
    }
    
    // nicolae, 20/11
    public function auto_products(Request $request){
        if (request()->ajax()) {
            
            $data = [];

            $variation_ids = $request->input('variation_ids');
            
            $location_id = $request->input('location_id');
            $transfer_location_id = $request->input('transfer_location_id');
            $sel_day = $request->input('sel_day');
            $sel_month = $request->input('sel_month');
            $business_id = $request->session()->get('user.business_id');
            
            $respons = array();
                    
           
            for($i = 0; $i < count($variation_ids); $i++ ) {

                $vArr = explode("@", $variation_ids[$i]["product_id"]);

                $variation_ids[$i]["product_id"] = $vArr[0];
                $variation_ids[$i]["variation_id"] = $vArr[3];
                $variation_ids[$i]["f_qty"] = $vArr[1];
                $variation_ids[$i]["t_qty"] = $vArr[2];

                if(count($vArr) == 4){

                    $sql =   ' SELECT transaction_id , product_id , variation_id ,quantity , quantity_returned ,updated_at
                                FROM transaction_sell_lines
                                WHERE product_id = "'.$variation_ids[$i]["product_id"].'" AND variation_id = "'.$variation_ids[$i]["variation_id"].'" AND
                                updated_at >= NOW()-INTERVAL '.$sel_month.' MONTH';
                    $res = DB::select($sql);                               
                
                    $product_id = $variation_ids[$i]["product_id"];
                    $variation_id = $variation_ids[$i]["variation_id"];
                    $transaction_id = "";
                    $updated_at = "";
                    $quantity = 0;
                    $quantity_returned = 0;
                    $qty = 0;
                    $lot_info = "";

                    if(count($res) > 0){
                    
                        for( $index = 0; $index < count($res); $index++) {
                            
                            $sql1 = 'SELECT * FROM transactions WHERE id = "'.$res[$index]->transaction_id.'" AND location_id="'.$transfer_location_id.'" AND type = "sell"';
                            $row = DB::select($sql1);
                            
                            for( $j = 0; $j < count($row); $j++) {                      
                            
                        
                                $transaction_id = $res[$index]->transaction_id;
                                $updated_at = $res[$index]->updated_at;

                                $quantity += $res[$index]->quantity;
                                $quantity_returned += $res[$index]->quantity_returned;

                            }
                        }
                        
                        
                        if($product_id > 0){

                            if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
                                
                                $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($variation_id, $business_id, $location_id, $transfer_location_id, true);
                                
                                foreach ($lot_number_obj as $lot_number) {

                                    $arr_lot = $lot_number->toArray();                                    
                                    $lot_info .= $arr_lot["lot_number"]."&".$arr_lot["qty_available"]."&".$arr_lot["purchase_line_id"]."@";

                                }
                            }
                            
                            $flag = 0;
                            $qty = ((($quantity - $quantity_returned) * $sel_day) / (30 * $sel_month));
                            //if($qty < 0) $flag = 1;

                            if($variation_ids[$i]["t_qty"] < $qty && $variation_ids[$i]["f_qty"] >= ($qty - $variation_ids[$i]["t_qty"])){ //case 1:

                                $qty = round($qty - $variation_ids[$i]["t_qty"], 3);
                            
                            }else if($variation_ids[$i]["t_qty"] < $qty && $variation_ids[$i]["f_qty"] < ($qty - $variation_ids[$i]["t_qty"])){//case 2:
                            
                                $qty = round($variation_ids[$i]["f_qty"], 3);
                            
                            }else if($variation_ids[$i]["t_qty"] == $qty && $qty != 0 or $variation_ids[$i]["t_qty"] > $qty){//case 3:
                            
                                $flag = 1;
                            
                            }else if($variation_ids[$i]["t_qty"] == 0 && $qty == 0){//case 4:
                            
                                $qty = 1;
                            
                            }

                            if ($flag == 0){
                                
                                $data = array("location_id"=>$location_id, "transfer_location_id"=>$transfer_location_id, "qty"=>round($qty, 3), "lot_info"=>$lot_info, "transaction_id"=>$transaction_id, "product_id"=>$product_id, "variation_id"=>$variation_id, "updated_at"=>$updated_at, "quantity"=>round($variation_ids[$i]["f_qty"], 3), "quantity_returned"=>round($variation_ids[$i]["t_qty"], 3));

                                array_push($respons, $data);
                            }   
                        }
                    }else{

                        $data = array("location_id"=>$location_id, "transfer_location_id"=>$transfer_location_id, "qty"=>1, "lot_info"=>$lot_info, "transaction_id"=>$transaction_id, "product_id"=>$product_id, "variation_id"=>$variation_id, "updated_at"=>"", "quantity"=>round($variation_ids[$i]["f_qty"], 3), "quantity_returned"=>round($variation_ids[$i]["t_qty"]));

                        array_push($respons, $data);

                    }
                }
             
            }
          
            echo json_encode($respons);

        }
    }
    public function add_products(Request $request) {
        if (request()->ajax()) {

            $data = [];

            $variation_ids = $request->input('variation_ids');
            $location_id = $request->input('location_id');
            $transfer_location_id = $request->input('transfer_location_id');

            $business_id = $request->session()->get('user.business_id');
            $index = 0;
            foreach( $variation_ids as $variation_id ) {
                $product = $this->productUtil->getTransferDetailsFromVariation($variation_id, $business_id, $location_id, $transfer_location_id);
                $data[$index]['product'] = $product;
                $data[$index]['variation_id'] = $variation_id;
                
                $product->formatted_qty_available = $this->productUtil->num_f($product->qty_available);
                
                $lot_numbers = [];
                if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
                    $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($variation_id, $business_id, $location_id, $transfer_location_id, true);
                    foreach ($lot_number_obj as $lot_number) {
                        $lot_number->qty_formated = $this->productUtil->num_f($lot_number->qty_available);
                        $lot_numbers[] = $lot_number;
                    }
                }
                $product->lot_numbers = $lot_numbers;

                $index++;
            }

            return view('stock_transfer.partials.product_transfer_table_row', ['data'=>$data, 'location_id'=>$location_id, 'transfer_location_id'=>$transfer_location_id]);
        
        }
    }

    public function add_stock_prepare(Request $request) {
        
        //product_id_ary[]: 435@435@1@5@@4@123123@01/15/2021 01:05@@NO
        if($request->input('kind') == 1){
            $sendAry = $request->input('product_id_ary');
           
            for($i = 0; $i < $request->input('index'); $i++){
                $send = $sendAry[$i];
                $send = explode("@", $send);

                TempTransferProduct::create([
                    'product_id' => $send[0],
                    'user_id' => session()->get('user.id'),
                    'variation_id' => $send[1],
                    'quantity' => $send[2],
                    'location_id' => $send[3],
                    'transfer_location_id' => $send[5],
                    'lot_no_line_id' => $send[4] ? $send[4] : 0,
                    'additional_notes' => $send[6]? $send[6] : '',
                    'transaction_date' => $send[7],
                    'ref_no' => $send[8],
                    'checked' =>$send[9],
                    'status' => 'PREPARE',
                ]);
            }
          
        }else{
            if (request()->input('product_id', '')) {
                TempTransferProduct::create([
                    'product_id' => request()->input('product_id', ''),
                    'user_id' => session()->get('user.id'),
                    'variation_id' => request()->input('variation_id', 0),
                    'quantity' => request()->input('quantity', 0),
                    'location_id' => request()->input('location_id', 0),
                    'transfer_location_id' => request()->input('transfer_location_id', 0),
                    'lot_no_line_id' => request()->input('lot_no_line_id') ? request()->input('lot_no_line_id', 0) : 0,
                    'additional_notes' => request()->input('additional_notes', ''),
                    'transaction_date' => $this->productUtil->uf_date(request()->input('transaction_date', null), true),
                    'ref_no' => request()->input('ref_no', null),
                    'checked' => request()->input('checked', 'NO'),
                    'status' => 'PREPARE',
                ]);

            }
        }
        // return "success";
    }
    
    // nicolae, 21/11
    public function add_stock_receive() {
        $ids = json_decode(request()->input('transfer_ids'));
        $quantities = json_decode(request()->input('quantities'));
        $notes = json_decode(request()->input('notes'));
        
        // count of products, rows
        $count = count($ids);
        
        for($i=0; $i<$count; $i++) {
            $transfer_product = TempTransferProduct::find($ids[$i]);
            $transfer_product->status = 'RECEIVED';
            $transfer_product->additional_notes = $notes[$i];
            $transfer_product->quantity = $quantities[$i];
            $transfer_product->save();    
        }
        return "success";
        
    }

    public function stock_receive(){
        $transfer_product = TempTransferProduct::find(request()->input('id'));
        $transfer_product->status = 'TRANSFERED';
        $transfer_product->additional_notes = request()->input('additional_notes', '');
        $transfer_product->quantity = request()->input('quantity', 0);
        $transfer_product->save();

        $transfer_product = TempTransferProduct::with('product', 'variation')->find(request()->input('id'));
        $data = [
            'transaction_date' => date('m/d/Y h:m', strtotime($transfer_product->transaction_date)),
            'ref_no' => $transfer_product->ref_no ? $transfer_product->ref_no : '',
            'location_id' => $this->productUtil->num_uf($transfer_product->location_id),
            'transfer_location_id' => $this->productUtil->num_uf($transfer_product->transfer_location_id),
            'final_total' => $transfer_product->quantity * $transfer_product->variation->default_purchase_price,
            'shipping_charges' => 0,
            'additional_notes' => $transfer_product->additional_notes ? $transfer_product->additional_notes : '',
            'products' => [
                [
                    'product_id' => $this->productUtil->num_uf($transfer_product->product_id),
                    'variation_id' => $this->productUtil->num_uf($transfer_product->variation_id),
                    'lot_no_line_id' => $this->productUtil->num_uf($transfer_product->lot_no_line_id ? $transfer_product->lot_no_line_id : 0),
                    'quantity' => $this->productUtil->num_uf($transfer_product->quantity),
                    'enable_stock' => $this->productUtil->num_uf($transfer_product->product->enable_stock),
                    'unit_price' => $this->productUtil->num_uf($transfer_product->variation->default_purchase_price),
                    'price' => $this->productUtil->num_uf($transfer_product->quantity * $transfer_product->variation->default_purchase_price),
                ]
            ]
        ];
        return $data;
    }

    public function stock_count(Request $request){
        $permitted_locations = auth()->user()->permitted_locations();
        $user_id = $request->session()->get('user.id');
        //$stock_prepare=TempTransferProduct::where('user_id', $user_id)->where('status', 'PREPARE');
        $stock_prepare=TempTransferProduct::where('status', 'PREPARE');
        if ($permitted_locations != 'all')
        {
            $stock_prepare = $stock_prepare->whereIn('location_id', $permitted_locations);
        }
        $stock_prepare = $stock_prepare->count();
        //$stock_receive=TempTransferProduct::where('user_id', $user_id)->where('status', 'RECEIVED');
        $stock_receive=TempTransferProduct::where('status', 'RECEIVED');
        if ($permitted_locations != 'all')
        {
            $stock_receive = $stock_receive->whereIn('transfer_location_id', $permitted_locations);
        }
        $stock_receive=$stock_receive->count();
        return  compact('stock_prepare','stock_receive');
    }

    public function show_products(Request $request) {
        if (request()->ajax()) {
            $search_term = request()->input('page_search', '');
            $location_id = request()->input('location_id', null);
            $category_id = request()->input('category_id', null);
            $sub_category_id = request()->input('sub_category_id', null);
            $brand_id = request()->input('brand_id', null);
            $transfer_location_id = request()->input('transfer_location_id', null);
            $business_id = request()->session()->get('user.business_id');

            $search_fields = request()->get('search_fields', ['name', 'sku', 'category', 'sub_category']);
            if (in_array('sku', $search_fields)) {
                $search_fields[] = 'sub_sku';
            }

            $result = $this->productUtil->filterTransferProduct($business_id, $search_term, $location_id, $transfer_location_id, $category_id, $sub_category_id, $brand_id, $search_fields);

            return json_encode($result);
        }
    }

    public function delete_prepare_stock(Request $request) {
        $transfer_ids = explode(',', $request->ids);

        foreach ($transfer_ids as $transfer_id) {
            $transfer_product = TempTransferProduct::find($transfer_id);
            $transfer_product->delete();
        }
        return "success";
    }

    public function delete_receive_stock(Request $request) {
        $transfer_ids = explode(',', $request->ids);

        foreach ($transfer_ids as $transfer_id) {
            $transfer_product = TempTransferProduct::find($transfer_id);
            $transfer_product->delete();
        }
        return "success";
    }
}