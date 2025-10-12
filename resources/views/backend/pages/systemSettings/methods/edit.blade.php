@extends('backend.layouts.master')

@section('title')
    {{ localize('Update Method') }} {{ getSetting('title_separator') }} {{ getSetting('system_title') }}
@endsection


@section('contents')
    <section class="tt-section pt-4">
        <div class="container">
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card tt-page-header">
                        <div class="card-body d-lg-flex align-items-center justify-content-lg-between">
                            <div class="tt-page-title">
                                <h2 class="h5 mb-lg-0">{{ localize('Update Method') }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4 g-4">

                <!--left sidebar-->
                <div class="col-xl-9 order-2 order-md-2 order-lg-2 order-xl-1">
                    <form action="{{ route('admin.methods.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $method->id }}">
                        
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
                                                    <option value="{{$location->id}}" {{ $locationSelect && $locationSelect == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label for="name" class="form-label">{{ localize('Method Name') }}</label>
                                    <input class="form-control" type="text" id="name" name="name" placeholder="{{ localize('Method name') }}" value="{{$method->name}}" required>
                                </div>
                                <div class="mb-4">
                                    <label for="description" class="form-label">{{ localize('Method Description') }}</label>
                                    <input class="form-control" type="text" id="description" name="description" placeholder="{{ localize('Method description') }}" value="{{$method->description}}" required>
                                </div>
                                <div class="mb-4">
                                    <label for="telefono" class="form-label">{{ localize('Teléfono') }}</label>
                                    <input class="form-control" type="text" id="telefono" name="telefono" placeholder="{{ localize('Teléfono') }}" value="{{$method->telefono}}" required>
                                </div>
                                <div class="mb-4">
                                    <label for="rif" class="form-label">{{ localize('RIF') }}</label>
                                    <input class="form-control" type="text" id="rif" name="rif" placeholder="{{ localize('RIF') }}" value="{{$method->rif}}" required>
                                </div>
                                <div class="mb-4">
                                    <label for="banco" class="form-label">{{ localize('Banco') }}</label>
                                    <input class="form-control" type="text" id="banco" name="banco" placeholder="{{ localize('Banco') }}" value="{{$method->banco}}" required>
                                </div>
                                <div class="mb-4">
                                    <label for="required_voucher" class="form-label">{{ localize('Method Required Voucher') }}</label>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" name="required_voucher"  {{$method->required_voucher ? 'checked' : ''}}>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label for="logo_method" class="form-label">{{ localize('Method Image') }}</label>
                                    <div class="tt-image-drop rounded">
                                        <span class="fw-semibold">{{ localize('Choose Dashboard Logo') }}</span>
                                        <!-- choose media -->
                                        <div class="tt-product-thumb show-selected-files mt-3">
                                            <div class="avatar avatar-xl cursor-pointer choose-media"
                                                data-bs-toggle="offcanvas" data-bs-target="#offcanvasBottom"
                                                onclick="showMediaManager(this)" data-selection="single">
                                                <input type="hidden" name="logo_method" value="{{$method->image}}">
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

                        <!-- submit button -->
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-4">
                                    <button class="btn btn-primary" type="submit">
                                        <i data-feather="save" class="me-1"></i> {{ localize('Save Changes') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- submit button end -->

                    </form>
                </div>

                <!--right sidebar-->
                <div class="col-xl-3 order-1 order-md-1 order-lg-1 order-xl-2">
                    <div class="card tt-sticky-sidebar d-none d-xl-block">
                        <div class="card-body">
                            <h5 class="mb-4">{{ localize('Method Information') }}</h5>
                            <div class="tt-vertical-step">
                                <ul class="list-unstyled">
                                    <li>
                                        <a href="#section-1" class="active">{{ localize('Basic Information') }}</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        "use strict";

        // runs when the document is ready --> for media files
        $(document).ready(function() {
            getChosenFilesCount();
            showSelectedFilePreviewOnLoad();
        });
    </script>
@endsection