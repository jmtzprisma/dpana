<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    # construct
    public function __construct()
    {
        
    }

    # company index
    public function index(Request $request)
    {
        $searchKey = null;
        $is_published = null;

        $companies = Company::latest();
        if ($request->search != null) {
            $companies = $companies->where('name', 'like', '%' . $request->search . '%');
            $searchKey = $request->search;
        }

        if ($request->is_published != null) {
            $companies = $companies->where('is_published', $request->is_published);
            $is_published    = $request->is_published;
        }

        $companies = $companies->paginate(paginationNumber());
        return view('backend.pages.companies.index', compact('companies', 'searchKey', 'is_published'));
    }

    # add company
    public function create()
    {
        return view('backend.pages.companies.create');
    }

    # add company
    public function store(Request $request)
    {
        $company = new Company;
        $company->name = $request->name;
        $company->category_id = $request->category_id;
        $company->banner = $request->image;
        $company->save();

        flash(localize('Company has been added successfully'))->success();
        return redirect()->route('admin.companies.index');
    }


    # edit company
    public function edit($id)
    {
        $company = Company::find((int)$id);
        
        return view('backend.pages.companies.edit', compact('company'));
    }

    # update company
    public function update(Request $request)
    {
        $company = Company::where('id', $request->id)->first();
        $company->name = $request->name;
        $company->category_id = $request->category_id;
        $company->banner = $request->image;
        $company->save();

        flash(localize('Company has been updated successfully'))->success();
        return redirect()->route('admin.companies.index');
    }
}
