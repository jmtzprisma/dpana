<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\MediaManager;
use App\Models\MethodsPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MethodsController extends Controller
{
    # construct
    public function __construct()
    {
        $this->middleware(['permission:methods'])->only('index');
        $this->middleware(['permission:add_methods'])->only(['store']);
        $this->middleware(['permission:edit_methods'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_methods'])->only(['delete']);
    }

    # methods list
    public function index(Request $request)
    {
        $searchKey = null;
        $methods = MethodsPayment::oldest();
        if ($request->search != null) {
            $methods = $methods->where('name', 'like', '%' . $request->search . '%');
            $searchKey = $request->search;
        }

        $methods = $methods->paginate(paginationNumber());
        
        $locations = Location::where('is_published', 1)->orderBy('name')->get();

        return view('backend.pages.systemSettings.methods.index', compact('methods', 'searchKey', 'locations'));
    }

    # method store
    public function store(Request $request)
    {
        $method = new MethodsPayment;
        $method->name = $request->name;
        $method->description = $request->description;
        $method->telefono = $request->telefono;
        $method->rif = $request->rif;
        $method->banco = $request->banco;
        $method->required_voucher = $request->has('required_voucher') ? true : false;

        $logo_image = $request->logo_method;
        if(is_null($logo_image)){
            $logo_image = MediaManager::where('media_name', 'solologo.png')->first()->id;
        }

        $method->image = $logo_image;
        $method->save();


        $location = Location::find($request->location_id);

        $metodos_pago = $location->metodos_pago;
        if(is_array($location->metodos_pago)){
            $metodos_pago[] = $method->id;
        }
        $location->metodos_pago = $metodos_pago;
        $location->save();

        flash(localize('MethodsPayment has been inserted successfully'))->success();
        return redirect()->route('admin.methods.index');
    }

    # edit method
    public function edit(Request $request, $id)
    {
        $method = MethodsPayment::findOrFail($id);
        $locationSelect = null;


        $locations = Location::where('is_published', 1)->orderBy('name')->get();
        foreach($locations as $location){
            if(is_array($location->metodos_pago) && in_array($method->id, $location->metodos_pago)){
                $locationSelect = $location->id;
            }
        }

        return view('backend.pages.systemSettings.methods.edit', compact('method', 'locations', 'locationSelect'));
    }

    # update method
    public function update(Request $request)
    {
        $method = MethodsPayment::findOrFail($request->id);
        $method->description = $request->description;
        $method->name = $request->name;
        $method->telefono = $request->telefono;
        $method->rif = $request->rif;
        $method->banco = $request->banco;
        $method->required_voucher = $request->has('required_voucher') ? true : false;
        
        $logo_image = $request->logo_method;
        if(is_null($logo_image)){
            $logo_image = MediaManager::where('media_name', 'solologo.png')->first()->id;
        }

        $method->image = $logo_image;
        $method->save();
        
        flash(localize('MethodsPayment has been updated successfully'))->success();
        return back();
    }


    # delete method
    public function delete($id)
    {
        $method = MethodsPayment::findOrFail($id);
        $method->delete();
        flash(localize('MethodsPayment has been deleted successfully'))->success();
        return back();
    }
}
