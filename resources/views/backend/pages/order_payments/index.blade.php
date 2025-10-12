@extends('backend.layouts.master')

@section('title')
    {{ localize('Orders') }} {{ getSetting('title_separator') }} {{ getSetting('system_title') }}
@endsection

@section('contents')
    <section class="tt-section pt-4">
        <div class="container">
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card tt-page-header">
                        <div class="card-body d-lg-flex align-items-center justify-content-lg-between">
                            <div class="tt-page-title">
                                <h2 class="h5 mb-lg-0">{{ localize('Orders Payments') }}</h2>
                            </div>
                            <div class="col-auto">
                                <button  class="btn btn-primary addOrder">
                                    <i data-feather="plus" width="18"></i>
                                    {{ localize('Add') }}
                                </button>
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
                                    <div class="col-auto flex-grow-1 d-none">
                                        <div class="tt-search-box">
                                            <div class="input-group">
                                                <span class="position-absolute top-50 start-0 translate-middle-y ms-2"> <i
                                                        data-feather="search"></i></span>
                                                <input class="form-control rounded-start w-100" type="text"
                                                    id="search" name="search"
                                                    placeholder="{{ localize('Search by name/phone') }}"
                                                    @isset($searchKey)
                                                value="{{ $searchKey }}"
                                                @endisset>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto flex-grow-1">
                                        <div class="input-group mb-3">
                                            @if (getSetting('order_code_prefix') != null)
                                                <div class="input-group-prepend">
                                                    <span
                                                        class="input-group-text rounded-end-0">{{ getSetting('order_code_prefix') }}</span>
                                                </div>
                                            @endif
                                            <input type="text" class="form-control" placeholder="{{ localize('code') }}"
                                                name="code"
                                                @isset($searchCode)
                                                value="{{ $searchCode }}"
                                                @endisset>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <select class="form-select select2" name="payment_status"
                                            data-minimum-results-for-search="Infinity" id="payment_status">
                                            <option value="">{{ localize('Payment Status') }}</option>
                                            <option value="{{ paidPaymentStatus() }}"
                                                @if (isset($paymentStatus) && $paymentStatus == paidPaymentStatus()) selected @endif>
                                                {{ localize('Paid') }}</option>
                                            <option value="{{ unpaidPaymentStatus() }}"
                                                @if (isset($paymentStatus) && $paymentStatus == unpaidPaymentStatus()) selected @endif>
                                                {{ localize('Unpaid') }}</option>
                                        </select>
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
                                    <th class="text-center">{{ localize('S/L') }}
                                    <th>{{ localize('Order Code') }}</th>
                                    <th data-breakpoints="xs sm md">{{ localize('Customer') }}</th>
                                    <th>{{ localize('Placed On') }}</th>
                                    @if (count($locations) > 0)
                                        <th data-breakpoints="xs sm">{{ localize('Location') }}</th>
                                    @endif
                                    <th data-breakpoints="xs">{{ localize('Payments') }}</th>
                                    {{-- <th data-breakpoints="xs sm" class="text-end">{{ localize('Action') }}</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @php $orderCode = '' @endphp
                                @foreach ($orderPayments as $key => $order)
                                    @if($orderCode != $order->orderGroup->order_code)
                                    @php $orderCode = $order->orderGroup->order_code @endphp
                                    <tr>
                                        <td class="text-center">{{ $key + 1 + ($orderPayments->currentPage() - 1) * $orderPayments->perPage() }}</td>
                                        <td class="fs-sm">
                                            {{ getSetting('order_code_prefix') }}{{ $order->orderGroup->order_code }}
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-md">
                                                    <img class="rounded-circle"
                                                        src="{{ uploadedAsset(optional($order->orderGroup->user)->avatar) }}"
                                                        alt="avatar"
                                                        onerror="this.onerror=null;this.src='{{ staticAsset('backend/assets/img/placeholder-thumb.png') }}';" />
                                                </div>
                                                <div class="ms-2">
                                                    <h6 class="fs-sm mb-0">{{ optional($order->orderGroup->user)->name }}</h6>
                                                    <span class="text-muted fs-sm">
                                                        {{ optional($order->orderGroup->user)->phone ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fs-sm">{{ date('d M, Y', strtotime($order->created_at)) }}</span>
                                        </td>
                                        @if (count($locations) > 0)
                                            <td>
                                                <span class="badge rounded-pill text-capitalize bg-secondary">
                                                    @php
                                                        $order_group = \App\Models\OrderGroup::find($order->ordergroup_id);
                                                    @endphp
                                                    @if ($location = \App\Models\Location::find($order_group->location_id))
                                                        {{ $location->name }}
                                                    @else
                                                        {{ localize('N/A') }}
                                                    @endif
                                                </span>
                                            </td>
                                        @endif
                                        <td>
                                    @endif
                                    
                                        @if ($order->status == 'pending')
                                            <div class="d-flex p-1">
                                                <div class="tooltipc">
                                                    <span class="badge {{ $order->user_notify ? 'bg-soft-warning' : 'bg-soft-danger' }} rounded-pill text-capitalize me-3">
                                                        {{ $order->status }} &nbsp;&nbsp;&nbsp; {{ formatPrice($order->amount) }} {{ $order->interest > 0 ? '+ ' . formatPrice($order->interest) : 0 }} 
                                                    </span>
                                                    @if ($order->user_notify)
                                                        <span class="tooltiptext">
                                                            <ul>
                                                                <li><b>Telefono:</b> {{$order->phone}}</li>
                                                                <li><b>Banco:</b> {{$order->bank}}</li>
                                                                <li><b>Fecha:</b> {{$order->date}}</li>
                                                                <li><b>Referencia:</b> {{$order->reference}}</li>
                                                                <li><b>Monto:</b> $ {{$order->amountBs}}</li>
                                                            </ul>
                                                        </span>
                                                    @endif
                                                </div>
                                                @if($order->comprobante)
                                                <a target="_blank" href="{{ asset('public/' . \App\Models\MediaManager::find($order->comprobante)->media_file) }}"
                                                    class="btn bg-soft-info btn-sm p-0 tt-view-details mx-2">
                                                    <i data-feather="eye"></i>
                                                </a>
                                                @endif
                                                <a href="{{ route('admin.orderspayments.pay', $order->id) }}"
                                                    class="btn bg-soft-info btn-sm p-0 tt-view-details">
                                                    <i data-feather="check"></i>
                                                </a>
                                            </div>
                                        @else
                                            <span class="badge bg-soft-primary rounded-pill text-capitalize">
                                                {{ $order->status }} &nbsp;&nbsp;&nbsp; {{ formatPrice($order->amount) }}
                                            </span>
                                        @endif
                                    @if(!empty($orderCode) && $orderCode != $order->orderGroup->order_code)
                                        </td>
                                        {{-- <td class="text-end">
                                        </td> --}}
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                        <!--pagination start-->
                        <div class="d-flex align-items-center justify-content-between px-4 pb-4">
                            <span>{{ localize('Showing') }}
                                {{ $orderPayments->firstItem() }}-{{ $orderPayments->lastItem() }} {{ localize('of') }}
                                {{ $orderPayments->total() }} {{ localize('results') }}</span>
                            <nav>
                                {{ $orderPayments->appends(request()->input())->links() }}
                            </nav>
                        </div>
                        <!--pagination end-->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="note" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{localize('Rejection Note')}}</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="note"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{localize('Close')}}</button>
                </div>
            </div>
        </div>
    </div>
    <!--Modal ADD ORDERS-->
    <div class="modal fade" id="addOrder" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{localize('Agregar orden')}}</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.orderspayments.addOrder') }}" class="existing-customer-form"  method="POST">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">{{ localize('Escanee el código QR del usuario') }}</label>
                            <input type="text" class="form-control" id="email_customer" name="email_customer">
                            <input type="hidden" id="order_customer_id" name="order_customer_id">
                            <div id="data_user"></div>
                            {{-- <select class="modalSelect2 w-100" name="order_customer_id" required>
                                <option value="">{{ localize('Select customer from list') }}</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->email }}
                                    </option>
                                @endforeach
                            </select> --}}
                        </div>
                        {{-- <div class="mb-2">
                            <label class="form-label">{{ localize('Select Location') }}</label>
                            <select class="modalSelect2 w-100" name="order_location_id" required>
                                <option value="">{{ localize('Select Location from list') }}</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div> --}}
                        <div class="row">
                            <div class="col-4">
                                <label class="form-label">{{ localize('Total de venta') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" class="form-control onlyNumber" id="total_sale" name="total_sale">
                                </div>
                                <div id="total_sale_error" class="text-danger small"></div>
                            </div>
                            <div class="col-4">
                                <label class="form-label">{{ localize('60 %') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text">60%</span>
                                    <input type="text" class="form-control" id="pago_inicial" readonly name="pago_inicial">
                                </div>
                                <div id="total_sale_error" class="text-danger small"></div>
                            </div>
                            <div class="col-4">
                                <label class="form-label">{{ localize('1 Cuota') }}</label>
                                <input id="order_cuotas" type="text" class="form-control" readonly name="order_cuotas">
                                <div id="order_cuotas_error" class="text-danger small"></div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{{ localize('Factura (opcional)') }}</label>
                            <div class="input-group mb-3">
                                <input type="file" class="form-control" id="noteOrder" name="noteOrder">
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary mt-3" id="btnAdd">{{ localize('Add') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
<script>
    document.getElementById("email_customer").addEventListener("input", function(e) {
        // cada vez que cambia el valor, reemplaza las comillas por @
        e.target.value = e.target.value.replace(/"/g, "@");
    });
</script>
<script>
    var amount_used = 0;
    var limite_credito = 0;
    var orders_pending = false;
    $(function(){
        $(document).on('click','.note', function(){
            const modal = $('#note')

            modal.find('#note').text($(this).data('note'));

            modal.modal('show')
        })

        $(document).on('click','.addOrder', function(){
            const modal = $('#addOrder')

            modal.find('#addOrder').text($(this).data('addOrder'));

            modal.modal('show')
        })
    
        $(".onlyNumber").on("keypress",function(event){
            if(event.which < 48 || event.which >58){
                return false;
            }
        });
        
        $("#email_customer").on("keyup", function(){
            $("#btnAdd").attr("disabled", true);
            $("#data_user").html('');

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                url: "{{route('admin.orderspayments.get_user')}}",
                type: "GET",
                data: {
                    email: $("#email_customer").val(),
                },
                success: function(data) {
                    user = JSON.parse(data.user);
                    $("#order_customer_id").val(user.id);
                    var html = '';
                    html += '<label><b>Nombre : </b>' + user.name + '</label><br>';
                    html += '<label><b>Teléfono : </b>' + user.phone + '</label><br>';
                    html += '<label><b>Cédula : </b>' + user.cedula + '</label><br>';
                    html += '<label><b>Email : </b>' + user.email + '</label>';
                    amount_used = data.amount_used;
                    limite_credito = user.limit_credit;
                    orders_pending = data.orders_pending;

                    $("#data_user").html(html);
                }
            });
        });
        
        $("#total_sale").on("keyup", function(){
            if(parseFloat($(this).val()) > 0) $("#pago_inicial").val((parseFloat($(this).val()) * .6).toFixed(2));
            if(parseFloat($(this).val()) > 0) $("#order_cuotas").val((parseFloat($(this).val()) * .4).toFixed(2));
        });

        $('.modalSelect2').select2({
            dropdownParent: $('#addOrder') // Si está dentro de un modal
            });
        });

        // validation modal new order
        document.addEventListener("DOMContentLoaded", function () {
        const totalSaleInput = document.getElementById("total_sale");
        const cuotasInput = document.getElementById("order_cuotas");
        const totalSaleError = document.getElementById("total_sale_error");
        const cuotasError = document.getElementById("order_cuotas_error");
        const form = document.querySelector(".existing-customer-form");


        // Function to display errors
        function showError(input, errorElement, message) {
            if (message) {
                errorElement.textContent = message;
                input.classList.add("is-invalid");
            } else {
                errorElement.textContent = "";
                input.classList.remove("is-invalid");
            }
        }

        // Real-time validation for "Total Sale"
        totalSaleInput.addEventListener("input", function () {
            this.value = this.value.replace(/[^0-9.]/g, ""); // Allows only numbers and periods

            let value = this.value;
            let decimalIndex = value.indexOf(".");

            if (decimalIndex !== -1) {
                let beforeDecimal = value.substring(0, decimalIndex).replace(/^0+/, "") || "0"; // Avoid unnecessary zeros
                let afterDecimal = value.substring(decimalIndex + 1).replace(/\D/g, "").substring(0, 2); // Limit to 2 decimal places
                value = beforeDecimal + "." + afterDecimal;
            } else {
                value = value.replace(/^0+/, "") || "0"; // Avoid unnecessary zeros
            }

            this.value = value;
            showError(this, totalSaleError, value && parseFloat(value) !== 0 ? "" : "Escribe el total de la venta");

            var validVal = true;
            if(parseFloat(value) > (parseFloat(limite_credito) - parseFloat(amount_used))){
                validVal = false;
                showError(this, totalSaleError, "El monto supera el limite de credito");
            }

            if(orders_pending){
                validVal = false;
                showError(this, totalSaleError, "Hay ordenes pendientes de pago, no es posible generar más ordenes");
            }

            if(validVal) $("#btnAdd").attr("disabled", false);
        });

        // When focus is lost, format with 2 decimal places and validate if it is 0.00
        totalSaleInput.addEventListener("blur", function () {
            if (this.value === "" || isNaN(this.value)) {
                this.value = "0.00";
            } else {
                this.value = parseFloat(this.value).toFixed(2);
            }

            showError(this, totalSaleError, parseFloat(this.value) === 0 ? "Escribe el total de la venta" : "");
            
            var validVal = true;
            if(parseFloat(this.value) > (parseFloat(limite_credito) - parseFloat(amount_used))){
                validVal = false;
                showError(this, totalSaleError, "El monto supera el limite de credito");
            }

            if(orders_pending){
                validVal = false;
                showError(this, totalSaleError, "Hay ordenes pendientes de pago, no es posible generar más ordenes");
            }

            if(validVal) $("#btnAdd").attr("disabled", false);
        });

        // Real-time validation for "Quotas"
        cuotasInput.addEventListener("input", function () {
            this.value = this.value.replace(/\D/g, ""); // Validation before submitting the form
            showError(this, cuotasError, this.value ? "" : "Escribe el numero de cuotas");
        });

        // Validation before submitting the form
        form.addEventListener("submit", function (e) {
            let isValid = true;

            if (totalSaleInput.value.trim() === "" || isNaN(parseFloat(totalSaleInput.value)) || parseFloat(totalSaleInput.value) === 0) {
                showError(totalSaleInput, totalSaleError, "Escribe el total de la venta");
                isValid = false;
            } else {
                showError(totalSaleInput, totalSaleError, "");
            }

            if (cuotasInput.value.trim() === "") {
                showError(cuotasInput, cuotasError, "Escribe el numero de cuotas");
                isValid = false;
            } else {
                showError(cuotasInput, cuotasError, "");
            }

            if (!isValid) {
                e.preventDefault(); // Prevent form submission if there are errors
            }
        });
});



</script>
@endsection
