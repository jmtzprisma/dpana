@extends('backend.layouts.master')

@section('title')
    {{ localize('Manage Stock') }} {{ getSetting('title_separator') }} {{ getSetting('system_title') }}
@endsection

@section('contents')
    <section class="tt-section pt-4">
        <div class="container">
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card tt-page-header">
                        <div class="card-body d-lg-flex align-items-center justify-content-lg-between">
                            <div class="tt-page-title">
                                <h2 class="h5 mb-lg-0">{{ localize('Manage Stock') }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12">
                    <div class="card mb-4" id="section-1">

                        <form class="app-search" action="{{ Request::fullUrl() }}" method="GET">
                            <div class="card-header border-bottom-0">
                                <div class="row justify-content-between g-3">
                                    <div class="col-auto flex-grow-1">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" placeholder="{{ localize('Product') }}"
                                                name="name"
                                                @isset($searchName)
                                                value="{{ $searchName }}"
                                                @endisset>
                                        </div>
                                    </div>
                                    @if (count($locations) > 0)
                                        <div class="col-auto">
                                            <select class="form-select select2" name="location_id"
                                                data-minimum-results-for-search="Infinity" id="location_id">
                                                <option value="">{{ localize('Location') }}</option>
                                                @foreach ($locations as $location)
                                                    <option value="{{ $location->id }}"
                                                        @if (isset($locationId) && $locationId == $location->id) selected @endif>
                                                        {{ $location->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif


                                    <div class="col-auto">
                                        <button type="submit" class="btn btn-primary">
                                            <i data-feather="search" width="18"></i>
                                            {{ localize('Search') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <table class="table tt-footable border-top align-middle" data-use-parent-width="true">
                            <thead>
                                <tr>
                                    <th data-breakpoints="xs sm md">{{ localize('Sku') }}</th>
                                    <th data-breakpoints="xs sm md">{{ localize('Name') }}</th>
                                    <th data-breakpoints="xs sm md">{{ localize('Stocks') }}</th>
                                    <th data-breakpoints="xs sm md">{{ localize('Location') }}</th>
                                    <th data-breakpoints="xs sm" class="text-end">{{ localize('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stocks as $stock)
                                <tr>
                                    <td class="text-center">{{ $stock['variation']->sku ?? 'N/A' }}</td>
                                    <td>{{ $stock['variation']->product->name ?? 'N/A' }}</td>
                                    <td>{{ $stock['stock_qty'] }}</td>
                                    <td class="fs-sm">
                                        <span class="badge rounded-pill text-capitalize bg-secondary">
                                            {{ $stock['location']->name }}
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        <div class="dropdown tt-tb-dropdown">
                                            <button type="button" class="btn p-0" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i data-feather="more-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end shadow">
                                                <a class="dropdown-item stock" data-bs-toggle="modal" data-bs-target="#updateStock"
                                                    data-id="{{ $stock['stock_id'] ?? '' }}"
                                                    data-product_variation_id="{{ $stock['variation']->id ?? '' }}"
                                                    data-location_id="{{ $stock['location']->id ?? '' }}"
                                                    data-product="{{ $stock['variation']->product->name ?? 'N/A' }}"
                                                    data-stock="{{ $stock['stock_qty'] }}">
                                                    <i data-feather="edit-3" class="me-2"></i>{{ localize('Edit') }}
                                                </a>
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
                                {{ $stocks->firstItem() }}-{{ $stocks->lastItem() }} {{ localize('of') }}
                                {{ $stocks->total() }} {{ localize('results') }}</span>
                            <nav>
                                {{ $stocks->appends(request()->input())->links() }}
                            </nav>
                    </div>
                        <!--pagination end-->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="updateStock" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{localize('Stocks')}}</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="updateStockForm" method="POST" action="{{ route('stocks.update') }}">
                        @csrf
                        <input type="hidden" id="stockId" name="stockId">
                        <input type="hidden" id="product_variation_id" name="product_variation_id">
                        <input type="hidden" id="location_id" name="location_id">
                        <div class="mb-3">
                            <label for="product" class="form-label">{{ localize('Product') }}</label>
                            <input type="text" id="product" name="product" class="form-control" readonly />
                        </div>
                        <div class="mb-3">
                            <label for="stock" class="form-label">{{ localize('Amount') }}</label>
                            <input type="number" id="stock" name="stock" class="form-control" required />
                        </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">
                        <i data-feather="save" class="me-1"></i> {{ localize('Save Stock') }}
                    </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection


@section('scripts')
    <script>
          $(function(){
        $(document).on('click','.stock', function(){
            const modal = $('#updateStock');
            modal.find('#stockId').val($(this).data('id'));
            modal.find('#product_variation_id').val($(this).data('product_variation_id'));
            modal.find('#location_id').val($(this).data('location_id'));
            modal.find('#product').val($(this).data('product'));
            modal.find('#stock').val($(this).data('stock'));
            modal.modal('show');
        });
    });
    </script>
@endsection
