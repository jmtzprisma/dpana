<?php

namespace App\Http\Controllers\Backend\Stocks;

use PDF;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\City;
use App\Models\State;
use App\Models\Location;
use App\Models\LocationCategories;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use QrCode;

class LocationsController extends Controller
{
    # construct
    public function __construct()
    {
        $this->middleware(['permission:show_locations'])->only('index');
        $this->middleware(['permission:add_location'])->only(['create', 'store']);
        $this->middleware(['permission:edit_location'])->only(['edit', 'update']);
        $this->middleware(['permission:publish_locations'])->only(['updatePublishedStatus', 'updateDefaultStatus']);
    }

    # location index
    public function index(Request $request)
    {
        $searchKey = null;
        $is_published = null;

        $locations = Location::latest();
        if ($request->search != null) {
            $locations = $locations->where('name', 'like', '%' . $request->search . '%');
            $searchKey = $request->search;
        }

        if ($request->is_published != null) {
            $locations = $locations->where('is_published', $request->is_published);
            $is_published    = $request->is_published;
        }

        $locations = $locations->paginate(paginationNumber());
        return view('backend.pages.stocks.locations.index', compact('locations', 'searchKey', 'is_published'));
    }

    # change the currency
    public function changeLocation(Request $request)
    {
        $request->session()->put('stock_location_id', $request->location_id);
        return true;
    }

    # add location
    public function create()
    {
        //Crypt::decryptString()
        return view('backend.pages.stocks.locations.create');
    }

    # add location
    public function store(Request $request)
    {

        $location = new Location;
        $location->name = $request->name;
        $location->address = $request->address;

        $location->lat = $request->lat;
        $location->lng = $request->lng;
        $location->phone = $request->telephone;
        $location->horario = $request->horario;
        $location->state_id = $request->state_id;
        $location->city_id = $request->city_id;
        $location->company_id = $request->company_id;
        $location->metodos_pago = $request->metodos_pago;
        $location->cuotas = $request->cuotas;
        $location->compra_online = isset($request->compra_online) ? true : false;
        $location->compra_qr = isset($request->compra_qr) ? true : false;

        $location->banner = $request->image;
        if (Location::count() == 0) {
            $location->is_default = 1;
        }
        $location->save();

        if($request->has('categories'))
        {
            foreach($request->categories as $category_id){
                $loc_cat = new LocationCategories;
                $loc_cat->location_id = $location->id;
                $loc_cat->category_id = $category_id;
                $loc_cat->save();
            }
        }


        if (!Storage::disk('public')->exists('locations')) {
            Storage::disk('public')->makeDirectory('locations');
        }

        //$str_qr = Crypt::encryptString("{$location->id}");
        $str_qr = QrCode::size(300)->generate(Crypt::encryptString("{$location->id}"));
        $pdfSavePath = Storage::disk('public')->path('locations/' . $location->id . '.pdf');
        $pdf = PDF::loadView('backend.pages.stocks.locations.qr', compact('str_qr'), [], [
            'format' => [190, 140]
        ]);
        $pdf->save($pdfSavePath);


        flash(localize('Location has been added successfully'))->success();
        return redirect()->route('admin.locations.index');
    }


    # edit location
    public function edit($id)
    {
        $location = Location::find((int)$id);

        $states         = State::isActive()->where('country_id', 237)->get();
        $cities         = City::isActive()->where('state_id', $location->state_id)->get();

        return view('backend.pages.stocks.locations.edit', compact('location', 'states', 'cities'));
    }

    # update location
    public function update(Request $request)
    {
        $location = Location::where('id', $request->id)->first();
        $location->name = $request->name;
        $location->address = $request->address;

        $location->lat = $request->lat;
        $location->lng = $request->lng;
        $location->phone = $request->telephone;
        $location->horario = $request->horario;
        $location->state_id = $request->state_id;
        $location->city_id = $request->city_id;
        $location->company_id = $request->company_id;
        $location->metodos_pago = $request->metodos_pago;
        $location->cuotas = $request->cuotas;
        $location->compra_online = isset($request->compra_online) ? true : false;
        $location->compra_qr = isset($request->compra_qr) ? true : false;

        $location->banner = $request->image;
        $location->save();

        LocationCategories::where('location_id', $location->id)->delete();
        if($request->has('categories'))
        {
            foreach($request->categories as $category_id){
                $loc_cat = new LocationCategories;
                $loc_cat->location_id = $location->id;
                $loc_cat->category_id = $category_id;
                $loc_cat->save();
            }
        }

        $str_qr = QrCode::size(300)->generate(Crypt::encryptString("{$location->id}"));
        $pdfSavePath = Storage::disk('public')->path('locations/' . $location->id . '.pdf');
        $pdf = PDF::loadView('backend.pages.stocks.locations.qr', compact('str_qr'), [], [
            'format' => [190, 140]
        ]);
        $pdf->save($pdfSavePath);

        flash(localize('Location has been updated successfully'))->success();
        return redirect()->route('admin.locations.index');
    }

    # update published
    public function updatePublishedStatus(Request $request)
    {
        $location = Location::findOrFail($request->id);
        if ($location->is_default == 1) {
            return 3;
        }
        $location->is_published = $request->status;
        if ($location->save()) {
            return 1;
        }
        return 0;
    }

    # update default
    public function updateDefaultStatus(Request $request)
    {
        $location = Location::findOrFail($request->id);
        $default = Location::where('is_default', 1)->first();
        if (!is_null($default)) {
            $default->is_default = 0;
            $default->save();
        }
        $location->is_default = $request->status;
        if ($location->save()) {
            return 1;
        }
        return 0;
    }

    public function downloadPdfQr($id)
    {
        $location = Location::where('id', $id)->first();

        $str_qr = QrCode::size(300)->generate(Crypt::encryptString("{$location->id}"));
        return PDF::loadView('backend.pages.stocks.locations.qr', compact('str_qr'), [], [
                'format' => [190, 140]
            ])->download($location->name . '.pdf');
    }
}
