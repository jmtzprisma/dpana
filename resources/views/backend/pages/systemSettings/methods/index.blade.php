@extends('backend.layouts.master')

@section('title')
    {{ localize('Methods') }} {{ getSetting('title_separator') }} {{ getSetting('system_title') }}
@endsection

@section('contents')
    <section class="tt-section pt-4">
        <div class="container">
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card tt-page-header">
                        <div class="card-body d-lg-flex align-items-center justify-content-lg-between">
                            <div class="tt-page-title">
                                <h2 class="h5 mb-lg-0">{{ localize('Methods') }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4 g-4">
                <!--left sidebar-->
                <div class="col-xl-9 order-2 order-md-2 order-lg-2 order-xl-1">
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-4" id="section-1">
                                <form class="app-search" action="{{ Request::fullUrl() }}" method="GET">
                                    <div class="card-header border-bottom-0">
                                        <div class="row justify-content-between g-3">
                                            <div class="col-auto flex-grow-1">
                                                <div class="tt-search-box">
                                                    <div class="input-group">
                                                        <span
                                                            class="position-absolute top-50 start-0 translate-middle-y ms-2">
                                                            <i data-feather="search"></i></span>
                                                        <input class="form-control rounded-start w-100" type="text"
                                                            id="search" name="search"
                                                            placeholder="{{ localize('Search') }}"
                                                            @isset($searchKey)
                                                                value="{{ $searchKey }}"
                                                            @endisset>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <button type="submit" class="btn btn-primary">
                                                    <i data-feather="search" width="18"></i>
                                                    {{ localize('Search') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                                <table class="table tt-footable border-top" data-use-parent-width="true">
                                    <thead>
                                        <tr>
                                            <th class="text-center" width="7%">{{ localize('S/L') }}</th>
                                            <th>{{ localize('Name') }}</th>
                                            <th>{{ localize('Description') }}</th>
                                            <th>{{ localize('Required Voucher') }}</th>
                                            <th data-breakpoints="xs sm" class="text-end">{{ localize('Action') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($methods as $key => $method)
                                            <tr>
                                                <td class="text-center">
                                                    {{ $key + 1 + ($methods->currentPage() - 1) * $methods->perPage() }}
                                                </td>

                                                <td class="fw-semibold">
                                                    {{ $method->name }}
                                                </td>
                                                <td class="fw-semibold">
                                                    {{ $method->description }}
                                                </td>
                                                <td class="fw-semibold">
                                                    {{ $method->required_voucher ? 'Requiere comprobante' : 'Sin comprobante' }}
                                                </td>

                                                <td class="text-end">
                                                    <div class="dropdown tt-tb-dropdown">
                                                        <button type="button" class="btn p-0" data-bs-toggle="dropdown"
                                                            aria-expanded="false">
                                                            <i data-feather="more-vertical"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end shadow">

                                                            @can('edit_methods')
                                                                <a class="dropdown-item"
                                                                    href="{{ route('admin.methods.edit', ['id' => $method->id, 'lang_key' => env('DEFAULT_LANGUAGE')]) }}&localize">
                                                                    <i data-feather="edit-3"
                                                                        class="me-2"></i>{{ localize('Edit') }}
                                                                </a>
                                                            @endcan

                                                            {{-- @can('delete_methods')
                                                                <a href="#" class="dropdown-item confirm-delete"
                                                                    data-href="{{ route('admin.methods.delete', $method->id) }}"
                                                                    title="{{ localize('Delete') }}">
                                                                    <i data-feather="trash-2" class="me-2"></i>
                                                                    {{ localize('Delete') }}
                                                                </a>
                                                            @endcan --}}
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <!--pagination start-->
                                <div class="d-flex align-items-center justify-content-between px-4 pb-4">
                                    <span>{{ localize('Showing') }}
                                        {{ $methods->firstItem() }}-{{ $methods->lastItem() }} {{ localize('of') }}
                                        {{ $methods->total() }} {{ localize('results') }}</span>
                                    <nav>
                                        {{ $methods->appends(request()->input())->links() }}
                                    </nav>
                                </div>
                                <!--pagination end-->
                            </div>
                        </div>

                        @can('add_methods')
                            <form action="{{ route('admin.methods.store') }}" class="pb-650" method="POST">
                                @csrf
                                <!-- method info start-->
                                <div class="card mb-4" id="section-2">
                                    <div class="card-body">
                                        <h5 class="mb-4">{{ localize('Add New Method') }}</h5>

                                        <div class="mb-4">
                                            <div class="col-auto">
                                                <div class="input-group">
                                                    <select class="form-select select2" name="location_id" data-minimum-results-for-search="Infinity">
                                                        <option value="">{{ localize('Select Location') }}</option>
                                                        @foreach ($locations as $location)
                                                            <option value="{{$location->id}}">{{ $location->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <label for="name" class="form-label">{{ localize('Method Name') }}</label>
                                            <input class="form-control" type="text" id="name" name="name" placeholder="{{ localize('Method name') }}" required>
                                        </div>
                                        <div class="mb-4">
                                            <label for="name" class="form-label">{{ localize('Method Description') }}</label>
                                            <input class="form-control" type="text" id="description" name="description" placeholder="{{ localize('Method description') }}" required>
                                        </div>
                                        <div class="mb-4">
                                            <label for="telefono" class="form-label">{{ localize('Teléfono') }}</label>
                                            <input class="form-control" type="text" id="telefono" name="telefono" placeholder="{{ localize('Teléfono') }}" required>
                                        </div>
                                        <div class="mb-4">
                                            <label for="rif" class="form-label">{{ localize('RIF') }}</label>
                                            <input class="form-control" type="text" id="rif" name="rif" placeholder="{{ localize('RIF') }}" required>
                                        </div>
                                        <div class="mb-4">
                                            <label for="banco" class="form-label">{{ localize('Banco') }}</label>
                                            <input class="form-control" type="text" id="banco" name="banco" placeholder="{{ localize('Banco') }}" required>
                                        </div>
                                        <div class="mb-4">
                                            <label for="name" class="form-label">{{ localize('Method Required Voucher') }}</label>
                                            <div class="form-check form-switch">
                                                <input type="checkbox" class="form-check-input" name="required_voucher">
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <label for="name" class="form-label">{{ localize('Method Image') }}</label>
                                            <div class="tt-image-drop rounded">
                                                <span class="fw-semibold">{{ localize('Choose Dashboard Logo') }}</span>
                                                <!-- choose media -->
                                                <div class="tt-product-thumb show-selected-files mt-3">
                                                    <div class="avatar avatar-xl cursor-pointer choose-media"
                                                        data-bs-toggle="offcanvas" data-bs-target="#offcanvasBottom"
                                                        onclick="showMediaManager(this)" data-selection="single">
                                                        <input type="hidden" name="logo_method">
                                                        <div class="no-avatar rounded-circle">
                                                            <span><i data-feather="plus"></i></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- choose media -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- method info end-->

                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-4">
                                            <button class="btn btn-primary" type="submit">
                                                <i data-feather="save" class="me-1"></i> {{ localize('Save Method') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        @endcan
                    </div>
                </div>

                <!--right sidebar-->
                <div class="col-xl-3 order-1 order-md-1 order-lg-1 order-xl-2">
                    <div class="card tt-sticky-sidebar">
                        <div class="card-body">
                            <h5 class="mb-4">{{ localize('Method Information') }}</h5>
                            <div class="tt-vertical-step">
                                <ul class="list-unstyled">
                                    <li>
                                        <a href="#section-1" class="active">{{ localize('All Methods') }}</a>
                                    </li>
                                    @can('add_methods')
                                        <li>
                                            <a href="#section-2">{{ localize('Add New Method') }}</a>
                                        </li>
                                    @endcan
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
