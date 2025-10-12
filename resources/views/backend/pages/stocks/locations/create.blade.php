@extends('backend.layouts.master')

@section('title')
    {{ localize('Add New Location') }} {{ getSetting('title_separator') }} {{ getSetting('system_title') }}
@endsection


@section('contents')
    <section class="tt-section pt-4">
        <div class="container">
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card tt-page-header">
                        <div class="card-body d-lg-flex align-items-center justify-content-lg-between">
                            <div class="tt-page-title">
                                <h2 class="h5 mb-lg-0">{{ localize('Add Location') }}</h2>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4 g-4">

                <!--left sidebar-->
                <div class="col-xl-9 order-2 order-md-2 order-lg-2 order-xl-1">
                    <form action="{{ route('admin.locations.store') }}" method="POST" class="pb-650">
                        @csrf
                        <!--basic information start-->
                        <div class="card mb-4" id="section-1">
                            <div class="card-body">
                                <h5 class="mb-3">{{ localize('Basic Information') }}</h5>

                                <div class="mb-3">
                                    <label for="name" class="form-label">{{ localize('Name') }}</label>
                                    <input class="form-control" type="text" id="name"
                                        placeholder="{{ localize('Type location name') }}" name="name" required>
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">{{ localize('Address') }}</label>
                                    <textarea class="form-control" id="address" placeholder="{{ localize('Type location address') }}" name="address"
                                        required></textarea>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <label for="horario" class="form-label">{{ localize('Horario de atención') }}</label>
                                        <input class="form-control" type="text" id="horario"
                                            placeholder="{{ localize('Escriba el horario de atención') }}" name="horario" required>
                                    </div>

                                    <div class="col-sm-4">
                                        <label for="lat" class="form-label">{{ localize('Latitud') }}</label>
                                        <input class="form-control" type="text" id="lat"
                                            placeholder="{{ localize('Escriba la latitud de la sucursal') }}" name="lat" required>
                                    </div>

                                    <div class="col-sm-4">
                                        <label for="lng" class="form-label">{{ localize('Longitud') }}</label>
                                        <input class="form-control" type="text" id="lng"
                                            placeholder="{{ localize('Escriba la longitud de la sucursal') }}" name="lng" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <div class="w-100 label-input-field">
                                            <label>{{ localize('Teléfono') }}</label>
                                            <input class="form-control" type="text" id="telephone"
                                                   placeholder="{{ localize('Escriba el teléfono de la sucursal') }}" name="telephone" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="w-100 label-input-field">
                                            <label>{{ localize('State') }}</label>
                                            <select class="form-select select2" required name="state_id">
                                                <option value="">{{ localize('Select State') }}</option>

                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-sm-4">
                                        <div class="w-100 label-input-field">
                                            <label>{{ localize('City') }}</label>
                                            <select class="form-select select2" required name="city_id">
                                                <option value="">{{ localize('Select City') }}</option>

                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <div class="w-100 label-input-field">
                                            <label>{{ localize('Metodos de Pago') }}</label>
                                            <select class="form-select select2" required name="metodos_pago[]" multiple>
                                                <option value="">{{ localize('Seleccione metodos de pago') }}</option>

                                                @foreach (\App\Models\MethodsPayment::all() as $itm)
                                                    <option value="{{ $itm->id }}">
                                                        {{ $itm->name }}</option>
                                                @endforeach

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="w-100 label-input-field">
                                            <label>{{ localize('Cuotas') }}</label>
                                            <select class="form-select select2" required name="cuotas">
                                                <option value="">{{ localize('Seleccione Cuotas') }}</option>

                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="4">4</option>
                                                <option value="5">5</option>
                                                <option value="6">6</option>
                                                <option value="7">7</option>
                                                <option value="8">8</option>

                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <div class="w-100 label-input-field">
                                            <label>{{ localize('Compañia') }}</label>
                                            <select class="form-select select2" required name="company_id">
                                                <option value="">{{ localize('Seleccione la compañia') }}</option>
                                                @foreach (\App\Models\Company::all() as $itm)
                                                    <option value="{{ $itm->id }}">{{ $itm->name }}</option>
                                                @endforeach

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="w-100 label-input-field">
                                            <label>{{ localize('Categorias') }}</label>
                                            <select class="form-select select2" required name="categories[]" multiple>
                                                <option value="">{{ localize('Seleccione las categorias') }}</option>

                                                @foreach (\App\Models\Category::where('parent_id', '>', '0')->get() as $itm)
                                                    <option value="{{ $itm->id }}">
                                                        {{ $itm->name }}</option>
                                                @endforeach

                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <label>{{ localize('Compra Online') }}</label>
                                        <div class="form-check form-switch">
                                            <input name="compra_online" type="checkbox" class="form-check-input" value="1">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label>{{ localize('Compra Qr') }}</label>
                                        <div class="form-check form-switch">
                                            <input name="compra_qr" type="checkbox" class="form-check-input" value="2">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--basic information end-->

                        <!--image start-->
                        <div class="card mb-4" id="section-2">
                            <div class="card-body">
                                <h5 class="mb-3">{{ localize('Images') }}</h5>
                                <div class="mb-3">
                                    <label class="form-label">{{ localize('Banner') }}</label>
                                    <div class="tt-image-drop rounded">
                                        <span class="fw-semibold">{{ localize('Choose Location Banner') }}</span>
                                        <!-- choose media -->
                                        <div class="tt-product-thumb show-selected-files mt-3">
                                            <div class="avatar avatar-xl cursor-pointer choose-media"
                                                data-bs-toggle="offcanvas" data-bs-target="#offcanvasBottom"
                                                onclick="showMediaManager(this)" data-selection="single">
                                                <input type="hidden" name="image">
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
                        <!-- image end-->

                        <!-- submit button -->
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <button class="btn btn-primary" type="submit">
                                        <i data-feather="save" class="me-1"></i> {{ localize('Save Location') }}
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
                            <h5 class="mb-3">{{ localize('Location Information') }}</h5>
                            <div class="tt-vertical-step">
                                <ul class="list-unstyled">
                                    <li>
                                        <a href="#section-1" class="active">{{ localize('Basic Information') }}</a>
                                    </li>
                                    <li>
                                        <a href="#section-2">{{ localize('Banner Image') }}</a>
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
         //  get states
         function getStates(country_id) {
             $.ajax({
                 headers: {
                     'X-CSRF-TOKEN': '{{ csrf_token() }}'
                 },
                 url: "{{ route('address.getStates') }}",
                 type: 'POST',
                 data: {
                     country_id: country_id
                 },
                 success: function(response) {
                     $('[name="state_id"]').html("");
                     $('[name="state_id"]').html(JSON.parse(response));
                 }
             });
         }

         //  get cities on state change
         $(document).on('change', '[name=state_id]', function() {
             var state_id = $(this).val();
             getCities(state_id);
         });

         //  get cities
         function getCities(state_id) {
             $.ajax({
                 headers: {
                     'X-CSRF-TOKEN': '{{ csrf_token() }}'
                 },
                 url: "{{ route('address.getCities') }}",
                 type: 'POST',
                 data: {
                     state_id: state_id
                 },
                 success: function(response) {
                     $('[name="city_id"]').html("");
                     $('[name="city_id"]').html(JSON.parse(response));
                 }
             });
         }

         getStates(237);

</script>
@endsection
