<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Session;
use Auth;
use Image;
use App\Category;
use App\Product;

class ProductsController extends Controller
{
    public function addProduct(Request $request){
        if ($request->isMethod('post')) {
          $data = $request->all();
           // echo "<pre>"; print_r($data); die;
        $product = new Product;
        if (empty($data['category_id'])) {
           return redirect()->back()->with('flash_message_error', 'Under category missing');
        }
        $product->category_id = $data['category_id'];
        $product->product_name = $data['product_name'];
        $product->product_code = $data['product_code'];
        $product->product_color = $data['product_color'];
        if (!empty($data['description'])) {
           $product->description = $data['description'];
        }else{
          $product->description = '';
        }
       
        $product->price = $data['price'];


         if ($request->hasFile('image')) {
            $image_tmp = Input::file('image');
           if ($image_tmp->isValid()) {
             $extension = $image_tmp->getClientOriginalExtension();
             $filename = rand(111,99999).'.'.$extension;
             $large_image_path = 'images/backend_images/products/large/'.$filename;
             $medium_image_path = 'images/backend_images/products/medium/'.$filename;
             $small_image_path = 'images/backend_images/products/small/'.$filename;


             Image::make($image_tmp)->save($large_image_path);
             Image::make($image_tmp)->resize(600,600)->save($medium_image_path);
             Image::make($image_tmp)->resize(300,300)->save($small_image_path);
             
             $product->image = $filename;
           }
         }
      
        $product->save();

        return redirect('admin/view-products')->with('flash_message_success', 'Product sucessfully added');
        }


       $categories = Category::where(['parent_id'=>0])->get();
       $categories_dropdown = "<option selected disabled>select</option>";

       foreach ($categories as $cat) {
       	$categories_dropdown .= "<option value = '".$cat->id."'>".$cat->name."</option>";
       	$sub_categories = Category::where(['parent_id'=>$cat->id])->get();
       	foreach ($sub_categories as $sub_cat) {
       		$categories_dropdown .= "<option value = '".$sub_cat->id."' >&nbsp;--&nbsp;".$sub_cat->name."</option>";
       	}
       }


    	return view('admin.products.add_products')->with(compact('categories_dropdown'));
    }

    public function viewProducts(Request $request){
      $products = Product::get();
      $products = json_decode(json_encode($products));
      foreach ($products as $key => $value) {
        $category_name = Category::where(['id' => $value->category_id])->first();
        $products[$key]->category_name = $category_name->name;
      }
      //echo "<pre>"; print_r($products); die;
         return view('admin.products.view_products')->with(compact('products'));
    }
    public function editProduct(Request $request, $id=null){
       if ($request->isMethod('post')) {
         $data = $request->all();
         //echo "<pre>";print_r($data); die;
          if ($request->hasFile('image')) {
            $image_tmp = Input::file('image');
           if ($image_tmp->isValid()) {
             $extension = $image_tmp->getClientOriginalExtension();
             $filename = rand(111,99999).'.'.$extension;
             $large_image_path = 'images/backend_images/products/large/'.$filename;
             $medium_image_path = 'images/backend_images/products/medium/'.$filename;
             $small_image_path = 'images/backend_images/products/small/'.$filename;


             Image::make($image_tmp)->save($large_image_path);
             Image::make($image_tmp)->resize(600,600)->save($medium_image_path);
             Image::make($image_tmp)->resize(300,300)->save($small_image_path);
             
            
           }else{
             $product->image = $filename;
           }
         }
           if (empty($data['description'])) {
            $data['description'] = '';
           }

         Product::where(['id' =>$id])->update(['category_id'=>$data['category_id'],'product_name'=>$data['product_name'],'product_code'=>$data['product_code'],'product_color'=>$data['product_color'],'description'=>$data['description'],'price'=>$data['price'],'image'=>$filename]);

         return redirect()->back()->with('flash_message_success', 'Product have been successfully updated');
       }

      $productDetails = Product::where(['id'=>$id])->first();


       $categories = Category::where(['parent_id'=>0])->get();
       $categories_dropdown = "<option selected disabled>select</option>";

       foreach ($categories as $cat) {
        if ($cat->id== $productDetails->category_id) {
          $selected= "selected";
        }else{
          $selected = " ";
        }
        $categories_dropdown .= "<option value = '".$cat->id."'".$selected.">".$cat->name."</option>";
        $sub_categories = Category::where(['parent_id'=>$cat->id])->get();
        foreach ($sub_categories as $sub_cat) {
           if ($sub_cat->id== $productDetails->category_id) {
          $selected= "selected";
        }else{
          $selected = " ";
        }
          $categories_dropdown .= "<option value = '".$sub_cat->id."' ".$selected.">&nbsp;--&nbsp;".$sub_cat->name."</option>";
        }
       }


      return view('admin.products.edit_products')->with(compact('productDetails', 'categories_dropdown'));
    }
}
